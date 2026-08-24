<x-admin-layout>
    <x-slot name="title">Blog Posts</x-slot>

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts" class="rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white text-sm">
            <select name="status" class="rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white text-sm">
                <option value="">All Statuses</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="published" @selected(request('status') === 'published')>Published</option>
            </select>
            <x-secondary-button type="submit">Filter</x-secondary-button>
        </form>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.blog.categories.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">Manage Categories</a>
            <a href="{{ route('admin.blog.posts.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold">New Post</a>
        </div>
    </div>

    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-charcoal-400 text-xs uppercase bg-charcoal-50 dark:bg-charcoal-800/50">
                <tr>
                    <th class="px-5 py-3">Title</th>
                    <th class="px-5 py-3">Category</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Views</th>
                    <th class="px-5 py-3">Published</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @forelse ($posts as $post)
                    <tr>
                        <td class="px-5 py-3 font-semibold">{{ $post->title }}</td>
                        <td class="px-5 py-3 text-charcoal-400">{{ $post->category->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $post->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600' }}">{{ ucfirst($post->status) }}</span>
                        </td>
                        <td class="px-5 py-3 text-charcoal-400">{{ $post->views }}</td>
                        <td class="px-5 py-3 text-charcoal-400">{{ $post->published_at?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-5 py-3 text-right space-x-2">
                            <a href="{{ route('admin.blog.posts.edit', $post) }}" class="text-xs font-semibold text-brand-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.blog.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-6 text-center text-charcoal-400">No blog posts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $posts->links() }}</div>
</x-admin-layout>
