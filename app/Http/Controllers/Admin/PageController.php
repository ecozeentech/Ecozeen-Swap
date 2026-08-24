<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', ['pages' => Page::query()->orderBy('title')->get()]);
    }

    public function create(): View
    {
        return view('admin.pages.edit', ['page' => new Page]);
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', ['page' => $page]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);
        $data['slug'] = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($data['title']);

        $page = Page::create($data);

        ActivityLog::record(auth()->id(), 'admin_created_page', ['slug' => $page->slug]);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'page-created');
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validated($request, $page->id);

        $page->update($data);

        ActivityLog::record(auth()->id(), 'admin_updated_page', ['slug' => $page->slug]);

        return back()->with('status', 'page-updated');
    }

    public function destroy(Page $page): RedirectResponse
    {
        if (in_array($page->slug, ['about-us', 'contact-us', 'privacy-policy', 'terms-of-service'], true)) {
            return back()->withErrors(['page' => 'Core pages cannot be deleted, only edited.']);
        }

        $page->delete();

        ActivityLog::record(auth()->id(), 'admin_deleted_page', ['slug' => $page->slug]);

        return redirect()->route('admin.pages.index')->with('status', 'page-deleted');
    }

    protected function validated(Request $request, ?int $ignoreId): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('pages', 'slug')->ignore($ignoreId)],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        return $validated;
    }
}
