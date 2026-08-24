<x-admin-layout>
    <x-slot name="title">Blog Categories</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <h3 class="font-semibold mb-4">Add Category</h3>
            <form method="POST" action="{{ route('admin.blog.categories.store') }}" class="space-y-3">
                @csrf
                <div>
                    <x-input-label value="Name" />
                    <x-text-input name="name" required class="mt-1 w-full" placeholder="Market Insights" />
                </div>
                <div>
                    <x-input-label value="Description" />
                    <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white"></textarea>
                </div>
                <x-primary-button class="w-full justify-center py-2.5">Add Category</x-primary-button>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm divide-y divide-charcoal-100 dark:divide-charcoal-800">
            @forelse ($categories as $category)
                <div x-data="{ editing: false }" class="p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold">{{ $category->name }}</p>
                            <p class="text-xs text-charcoal-400">/blog/category/{{ $category->slug }} &middot; {{ $category->posts_count }} posts</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="editing = !editing" class="text-xs font-semibold text-brand-600 hover:underline">Edit</button>
                            <form method="POST" action="{{ route('admin.blog.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Posts will be uncategorized.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                    <div x-show="editing" x-transition style="display:none" class="mt-3 rounded-lg bg-charcoal-50 dark:bg-charcoal-800/40 p-4">
                        <form method="POST" action="{{ route('admin.blog.categories.update', $category) }}" class="space-y-3">
                            @csrf @method('PUT')
                            <div>
                                <x-input-label value="Name" />
                                <x-text-input name="name" value="{{ $category->name }}" required class="mt-1 w-full" />
                            </div>
                            <div>
                                <x-input-label value="Slug" />
                                <x-text-input name="slug" value="{{ $category->slug }}" required class="mt-1 w-full" />
                            </div>
                            <div>
                                <x-input-label value="Description" />
                                <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">{{ $category->description }}</textarea>
                            </div>
                            <div class="flex justify-end gap-2">
                                <x-secondary-button type="button" @click="editing = false">Cancel</x-secondary-button>
                                <x-primary-button type="submit">Save</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <p class="p-6 text-sm text-charcoal-400 text-center">No categories yet.</p>
            @endforelse
        </div>
    </div>
</x-admin-layout>
