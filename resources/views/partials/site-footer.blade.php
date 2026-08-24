<footer class="border-t border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-950">
    <div class="max-w-6xl mx-auto px-6 py-12 grid grid-cols-2 sm:grid-cols-4 gap-8">
        <div class="col-span-2 sm:col-span-1">
            <x-application-logo :with-text="true" />
            <p class="mt-3 text-sm text-charcoal-500 dark:text-charcoal-400">The single trusted vendor for buying, selling, and swapping crypto.</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-400 mb-3">Company</p>
            <ul class="space-y-2 text-sm text-charcoal-500 dark:text-charcoal-400">
                <li><a href="{{ route('about.show') }}" class="hover:text-brand-600">About Us</a></li>
                <li><a href="{{ route('contact.show') }}" class="hover:text-brand-600">Contact Us</a></li>
                <li><a href="{{ route('blog.index') }}" class="hover:text-brand-600">Blog</a></li>
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-400 mb-3">Legal</p>
            <ul class="space-y-2 text-sm text-charcoal-500 dark:text-charcoal-400">
                <li><a href="{{ route('policy.privacy') }}" class="hover:text-brand-600">Privacy Policy</a></li>
                <li><a href="{{ route('policy.terms') }}" class="hover:text-brand-600">Terms of Service</a></li>
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-400 mb-3">Get Started</p>
            <ul class="space-y-2 text-sm text-charcoal-500 dark:text-charcoal-400">
                @auth
                    <li><a href="{{ route('dashboard') }}" class="hover:text-brand-600">Dashboard</a></li>
                @else
                    <li><a href="{{ route('register') }}" class="hover:text-brand-600">Create Account</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-brand-600">Log In</a></li>
                @endauth
            </ul>
        </div>
    </div>
    <div class="border-t border-charcoal-100 dark:border-charcoal-800 py-6 text-center text-xs text-charcoal-400">
        &copy; {{ now()->year }} Ecozeen Tech Ltd. Registered in Nigeria (RC 1835204) and the United Kingdom (16582062). All rights reserved.
    </div>
</footer>
