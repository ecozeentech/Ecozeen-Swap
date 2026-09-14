<nav class="hidden md:flex items-center gap-6 text-sm font-medium text-charcoal-600 dark:text-charcoal-300">
    <a href="{{ route('buy.index') }}" class="hover:text-brand-600">Buy</a>
    <a href="{{ route('sell.index') }}" class="hover:text-brand-600">Sell</a>
    <a href="{{ route('swap.index') }}" class="hover:text-brand-600">Swap</a>
    <a href="{{ route('blog.index') }}" class="hover:text-brand-600 {{ request()->routeIs('blog.*') ? 'text-brand-600' : '' }}">Blog</a>
    <a href="{{ route('about.show') }}" class="hover:text-brand-600 {{ request()->routeIs('about.show') ? 'text-brand-600' : '' }}">About</a>
    <a href="{{ route('contact.show') }}" class="hover:text-brand-600 {{ request()->routeIs('contact.show') ? 'text-brand-600' : '' }}">Contact</a>
</nav>
