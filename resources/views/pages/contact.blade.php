@php
    $contactPhone = \App\Models\SystemSetting::get('contact_phone');
    $contactEmail = \App\Models\SystemSetting::get('contact_email') ?: 'support@ecozeenswap.com';
    $contactAddress = \App\Models\SystemSetting::get('contact_address') ?: 'Ecozeen Tech Ltd · Registered in Nigeria (RC 1835204) & the United Kingdom (16582062)';
@endphp
<x-public-layout :title="$page?->meta_title ?: ($page?->title ?: 'Contact Us')" :description="$page?->meta_description">
    <section class="max-w-5xl mx-auto px-6 py-16">
        <div class="text-center max-w-2xl mx-auto">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-charcoal-900 dark:text-white">{{ $page?->title ?: 'Get in Touch' }}</h1>
            <p class="mt-3 text-charcoal-500 dark:text-charcoal-400">
                @if ($page?->content)
                    {{ \Illuminate\Support\Str::limit(strip_tags($page->content), 180) }}
                @else
                    Our institutional support team is available 24/7. Reach out for technical assistance, partnership inquiries, or general support.
                @endif
            </p>
        </div>

        <div class="mt-10 grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                <h2 class="font-bold text-charcoal-900 dark:text-white mb-4">Send a Message</h2>

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
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Full Name" />
                            <x-text-input name="name" value="{{ old('name') }}" required class="mt-1 w-full" placeholder="Jane Doe" />
                        </div>
                        <div>
                            <x-input-label value="Email" />
                            <x-text-input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full" placeholder="jane@company.com" />
                        </div>
                    </div>
                    <div>
                        <x-input-label value="Subject" />
                        <select name="subject" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                            <option>Technical Support</option>
                            <option>Account Issue</option>
                            <option>Partnership Inquiry</option>
                            <option>Compliance / KYC</option>
                            <option>General Inquiry</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Message" />
                        <textarea name="message" rows="6" required class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500" placeholder="How can we assist you today?">{{ old('message') }}</textarea>
                    </div>
                    <x-primary-button class="px-6 py-3">Submit Inquiry</x-primary-button>
                </form>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                    <h2 class="font-bold text-charcoal-900 dark:text-white mb-4">Contact Information</h2>
                    <div class="space-y-4 text-sm">
                        <div class="flex gap-3">
                            <svg class="h-5 w-5 text-brand-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <div>
                                <p class="font-semibold text-charcoal-900 dark:text-white">Global Headquarters</p>
                                <p class="text-charcoal-500 dark:text-charcoal-400">{{ $contactAddress }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <svg class="h-5 w-5 text-brand-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            <div>
                                <p class="font-semibold text-charcoal-900 dark:text-white">Support Email</p>
                                <a href="mailto:{{ $contactEmail }}" class="text-brand-600 hover:underline">{{ $contactEmail }}</a>
                            </div>
                        </div>
                        @if ($contactPhone)
                            <div class="flex gap-3">
                                <svg class="h-5 w-5 text-brand-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                <div>
                                    <p class="font-semibold text-charcoal-900 dark:text-white">Phone</p>
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" class="text-brand-600 hover:underline">{{ $contactPhone }}</a>
                                </div>
                            </div>
                        @endif
                        <div class="flex gap-3">
                            <svg class="h-5 w-5 text-brand-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            <div>
                                <p class="font-semibold text-charcoal-900 dark:text-white">Live Support</p>
                                <p class="text-charcoal-500 dark:text-charcoal-400">Need immediate assistance? Use the live chat widget in the corner of your screen for real-time support.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-charcoal-50 dark:bg-charcoal-800 p-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-400 mb-3">Connect</p>
                    <div class="flex gap-3">
                        <a href="mailto:{{ $contactEmail }}" class="h-9 w-9 rounded-full bg-white dark:bg-charcoal-900 flex items-center justify-center text-brand-600 hover:bg-brand-50" aria-label="Email">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </a>
                        <a href="{{ route('support.index') }}" class="h-9 w-9 rounded-full bg-white dark:bg-charcoal-900 flex items-center justify-center text-brand-600 hover:bg-brand-50" aria-label="Live Chat">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </a>
                        <a href="{{ route('home') }}" class="h-9 w-9 rounded-full bg-white dark:bg-charcoal-900 flex items-center justify-center text-brand-600 hover:bg-brand-50" aria-label="Website">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM3.6 9h16.8M3.6 15h16.8M11.5 3a17 17 0 000 18M12.5 3a17 17 0 010 18" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
