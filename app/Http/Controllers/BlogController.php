<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $posts = BlogPost::query()
            ->published()
            ->with('category', 'author')
            ->when($request->input('search'), fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', [
            'posts' => $posts,
            'categories' => BlogCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function category(Request $request, string $slug): View
    {
        $category = BlogCategory::query()->where('slug', $slug)->firstOrFail();

        $posts = BlogPost::query()
            ->published()
            ->where('blog_category_id', $category->id)
            ->with('category', 'author')
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', [
            'posts' => $posts,
            'categories' => BlogCategory::query()->orderBy('name')->get(),
            'activeCategory' => $category,
        ]);
    }

    public function show(string $slug): View
    {
        $post = BlogPost::query()->published()->where('slug', $slug)->with('category', 'author')->firstOrFail();

        $post->increment('views');

        $related = BlogPost::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', ['post' => $post, 'related' => $related]);
    }
}
