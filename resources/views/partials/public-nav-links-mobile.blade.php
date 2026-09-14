{{-- Mobile hamburger menu contents shared by the public layout and the landing page --}}
<a href="{{ route('buy.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-700 dark:text-charcoal-200 hover:bg-charcoal-50 dark:hover:bg-charcoal-800">Buy</a>
<a href="{{ route('sell.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-700 dark:text-charcoal-200 hover:bg-charcoal-50 dark:hover:bg-charcoal-800">Sell</a>
<a href="{{ route('swap.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-700 dark:text-charcoal-200 hover:bg-charcoal-50 dark:hover:bg-charcoal-800">Swap</a>
<a href="{{ route('blog.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-700 dark:text-charcoal-200 hover:bg-charcoal-50 dark:hover:bg-charcoal-800">Blog</a>
<a href="{{ route('about.show') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-700 dark:text-charcoal-200 hover:bg-charcoal-50 dark:hover:bg-charcoal-800">About</a>
<a href="{{ route('contact.show') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-700 dark:text-charcoal-200 hover:bg-charcoal-50 dark:hover:bg-charcoal-800">Contact</a>

<div class="pt-2 mt-2 border-t border-charcoal-100 dark:border-charcoal-800 space-y-1">
    @auth
        <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2.5 text-sm font-semibold text-brand-600 hover:bg-brand-50 dark:hover:bg-charcoal-800">Dashboard</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-700 dark:text-charcoal-200 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                Log Out
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-700 dark:text-charcoal-200 hover:bg-charcoal-50 dark:hover:bg-charcoal-800">Log in</a>
        <a href="{{ route('register') }}" class="block rounded-lg px-3 py-2.5 text-sm font-semibold bg-brand-500 text-white text-center hover:bg-brand-600">Get Started</a>
    @endauth
</div>
