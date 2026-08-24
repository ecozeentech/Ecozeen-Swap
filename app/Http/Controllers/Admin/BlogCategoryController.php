<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.blog.categories.index', ['categories' => BlogCategory::query()->withCount('posts')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category = BlogCategory::create($data);

        ActivityLog::record(auth()->id(), 'admin_created_blog_category', ['name' => $category->name]);

        return back()->with('status', 'category-created');
    }

    public function update(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', Rule::unique('blog_categories', 'slug')->ignore($blogCategory->id)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['slug'] = Str::slug($data['slug']);

        $blogCategory->update($data);

        return back()->with('status', 'category-updated');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->posts()->update(['blog_category_id' => null]);
        $blogCategory->delete();

        ActivityLog::record(auth()->id(), 'admin_deleted_blog_category', ['name' => $blogCategory->name]);

        return back()->with('status', 'category-deleted');
    }
}
