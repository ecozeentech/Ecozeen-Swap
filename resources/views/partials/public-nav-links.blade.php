@php($navLabels = \App\Support\SiteContent::data('navigation', 'labels'))
<nav class="hidden md:flex items-center gap-6 text-sm font-medium text-charcoal-600 dark:text-charcoal-300">
    <a href="{{ route('buy.index') }}" class="hover:text-brand-600">{{ $navLabels['buy'] }}</a>
    <a href="{{ route('sell.index') }}" class="hover:text-brand-600">{{ $navLabels['sell'] }}</a>
    <a href="{{ route('swap.index') }}" class="hover:text-brand-600">{{ $navLabels['swap'] }}</a>
    <a href="{{ route('blog.index') }}" class="hover:text-brand-600 {{ request()->routeIs('blog.*') ? 'text-brand-600' : '' }}">{{ $navLabels['blog'] }}</a>
    <a href="{{ route('about.show') }}" class="hover:text-brand-600 {{ request()->routeIs('about.show') ? 'text-brand-600' : '' }}">{{ $navLabels['about'] }}</a>
    <a href="{{ route('contact.show') }}" class="hover:text-brand-600 {{ request()->routeIs('contact.show') ? 'text-brand-600' : '' }}">{{ $navLabels['contact'] }}</a>
</nav>
