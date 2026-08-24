<x-admin-layout>
    <x-slot name="title">{{ $post->exists ? 'Edit Post' : 'New Post' }}</x-slot>

    <form id="post-form" method="POST" action="{{ $post->exists ? route('admin.blog.posts.update', $post) : route('admin.blog.posts.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($post->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm space-y-4">
                <div>
                    <x-input-label value="Title" />
                    <x-text-input name="title" value="{{ old('title', $post->title) }}" required class="mt-1 w-full" />
                </div>

                <div>
                    <x-input-label value="URL Slug (optional — auto-generated if left blank)" />
                    <x-text-input name="slug" value="{{ old('slug', $post->slug) }}" class="mt-1 w-full" />
                </div>

                <div>
                    <x-input-label value="Excerpt" />
                    <textarea name="excerpt" rows="2" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>

                <div>
                    <x-input-label value="Content" />
                    <textarea name="content" rows="18" required class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white font-mono text-sm">{{ old('content', $post->content) }}</textarea>
                    <p class="text-xs text-charcoal-400 mt-1">Basic HTML is supported (headings, paragraphs, lists, links, images).</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <x-input-label value="Meta Title" />
                        <x-text-input name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="mt-1 w-full" />
                    </div>
                    <div>
                        <x-input-label value="Meta Description" />
                        <x-text-input name="meta_description" value="{{ old('meta_description', $post->meta_description) }}" class="mt-1 w-full" />
                    </div>
                    <div>
                        <x-input-label value="Meta Keywords" />
                        <x-text-input name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}" class="mt-1 w-full" />
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm space-y-4">
                    <h3 class="font-semibold">Publishing</h3>

                    <div>
                        <x-input-label value="Status" />
                        <select name="status" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">
                            <option value="draft" @selected(old('status', $post->status) === 'draft')>Draft</option>
                            <option value="published" @selected(old('status', $post->status) === 'published')>Published</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label value="Publish Date" />
                        <x-text-input name="published_at" type="datetime-local" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full" />
                        <p class="text-xs text-charcoal-400 mt-1">Defaults to now if left blank and status is Published.</p>
                    </div>

                    <div>
                        <x-input-label value="Category" />
                        <select name="blog_category_id" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">
                            <option value="">Uncategorized</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label value="Featured Image" />
                        @if ($post->featured_image)
                            <img src="{{ $post->featuredImageUrl() }}" class="mt-2 mb-2 h-32 w-full object-cover rounded-lg" alt="">
                        @endif
                        <input type="file" name="featured_image" accept="image/*" class="mt-1 w-full text-sm">
                    </div>
                </div>

                <x-primary-button class="w-full justify-center py-3">Save Post</x-primary-button>
                <a href="{{ route('admin.blog.posts.index') }}" class="block text-center text-sm font-semibold text-charcoal-500 hover:text-brand-600">Back to posts</a>
            </div>
        </div>
    </form>
</x-admin-layout>
