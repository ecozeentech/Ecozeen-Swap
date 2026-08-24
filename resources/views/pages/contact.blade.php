<x-public-layout :title="$page?->meta_title ?: ($page?->title ?: 'Contact Us')" :description="$page?->meta_description">
    <section class="max-w-5xl mx-auto px-6 py-16 grid lg:grid-cols-2 gap-12">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 dark:bg-charcoal-800 text-brand-700 dark:text-brand-400 text-xs font-semibold px-3 py-1">
                Get in Touch
            </span>
            <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold text-charcoal-900 dark:text-white">{{ $page?->title ?: 'Contact Us' }}</h1>

            @if ($page?->content)
                <div class="prose prose-charcoal dark:prose-invert max-w-none mt-6 text-charcoal-600 dark:text-charcoal-300 [&_p]:mb-4">
                    {!! $page->content !!}
                </div>
            @else
                <p class="mt-6 text-charcoal-500 dark:text-charcoal-400">Have a question about your account, a trade, or a partnership opportunity? Send us a message and our team will get back to you.</p>
            @endif

            <div class="mt-8 space-y-3 text-sm text-charcoal-500 dark:text-charcoal-400">
                <p><strong class="text-charcoal-900 dark:text-white">Ecozeen Tech Ltd</strong></p>
                <p>Nigeria: RC 1835204 &middot; United Kingdom: 16582062</p>
            </div>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            @if (session('status') === 'message-sent')
                <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
                    Thanks for reaching out! We'll get back to you shortly.
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label value="Name" />
                    <x-text-input name="name" value="{{ old('name') }}" required class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="Email" />
                    <x-text-input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="Subject" />
                    <x-text-input name="subject" value="{{ old('subject') }}" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="Message" />
                    <textarea name="message" rows="5" required class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">{{ old('message') }}</textarea>
                </div>
                <x-primary-button class="w-full justify-center py-3">Send Message</x-primary-button>
            </form>
        </div>
    </section>
</x-public-layout>
