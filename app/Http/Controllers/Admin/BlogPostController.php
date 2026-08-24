<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function __construct(protected MediaUploadService $media) {}

    public function index(Request $request): View
    {
        $posts = BlogPost::query()
            ->with('category')
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->input('search'), fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.blog.posts.index', ['posts' => $posts]);
    }

    public function create(): View
    {
        return view('admin.blog.posts.edit', ['post' => new BlogPost, 'categories' => BlogCategory::query()->orderBy('name')->get()]);
    }

    public function edit(BlogPost $post): View
    {
        return view('admin.blog.posts.edit', ['post' => $post, 'categories' => BlogCategory::query()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);
        $data['author_id'] = $request->user()->id;
        $data['slug'] = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($data['title']).'-'.Str::random(4);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->media->store($request->file('featured_image'), 'blog');
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = BlogPost::create($data);

        ActivityLog::record(auth()->id(), 'admin_created_blog_post', ['title' => $post->title]);

        return redirect()->route('admin.blog.posts.edit', $post)->with('status', 'post-created');
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $data = $this->validated($request, $post->id);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->media->replace($request->file('featured_image'), 'blog', $post->featured_image);
        }

        if ($data['status'] === 'published' && ! $post->published_at && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post->update($data);

        ActivityLog::record(auth()->id(), 'admin_updated_blog_post', ['title' => $post->title]);

        return back()->with('status', 'post-updated');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $this->media->forget($post->featured_image);
        $title = $post->title;
        $post->delete();

        ActivityLog::record(auth()->id(), 'admin_deleted_blog_post', ['title' => $title]);

        return redirect()->route('admin.blog.posts.index')->with('status', 'post-deleted');
    }

    protected function validated(Request $request, ?int $ignoreId): array
    {
        $validated = $request->validate([
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('blog_posts', 'slug')->ignore($ignoreId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
        ]);

        unset($validated['featured_image']);

        return $validated;
    }
}
