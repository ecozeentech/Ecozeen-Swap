@php
    $footerBrand = \App\Support\SiteContent::data('footer', 'brand');
    $footerLinks = \App\Support\SiteContent::data('footer', 'links');
@endphp
<footer class="border-t border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-950">
    <div class="max-w-6xl mx-auto px-6 py-12 grid grid-cols-2 sm:grid-cols-4 gap-8">
        <div class="col-span-2 sm:col-span-1">
            <x-application-logo class="h-10 w-10" />
            @if ($footerBrand['tagline'] !== '')
                <p class="mt-3 text-sm text-charcoal-500 dark:text-charcoal-400">{!! nl2br(e($footerBrand['tagline'])) !!}</p>
            @endif
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-400 mb-3">{{ $footerLinks['company_heading'] }}</p>
            <ul class="space-y-2 text-sm text-charcoal-500 dark:text-charcoal-400">
                <li><a href="{{ route('about.show') }}" class="hover:text-brand-600">{{ $footerLinks['about_label'] }}</a></li>
                <li><a href="{{ route('contact.show') }}" class="hover:text-brand-600">{{ $footerLinks['contact_label'] }}</a></li>
                <li><a href="{{ route('blog.index') }}" class="hover:text-brand-600">{{ $footerLinks['blog_label'] }}</a></li>
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-400 mb-3">{{ $footerLinks['legal_heading'] }}</p>
            <ul class="space-y-2 text-sm text-charcoal-500 dark:text-charcoal-400">
                <li><a href="{{ route('policy.privacy') }}" class="hover:text-brand-600">{{ $footerLinks['privacy_label'] }}</a></li>
                <li><a href="{{ route('policy.terms') }}" class="hover:text-brand-600">{{ $footerLinks['terms_label'] }}</a></li>
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-400 mb-3">{{ $footerLinks['start_heading'] }}</p>
            <ul class="space-y-2 text-sm text-charcoal-500 dark:text-charcoal-400">
                @auth
                    <li><a href="{{ route('dashboard') }}" class="hover:text-brand-600">{{ $footerLinks['dashboard_label'] }}</a></li>
                @else
                    <li><a href="{{ route('register') }}" class="hover:text-brand-600">{{ $footerLinks['register_label'] }}</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-brand-600">{{ $footerLinks['login_label'] }}</a></li>
                @endauth
            </ul>
        </div>
    </div>
    @if ($footerBrand['copyright'] !== '')
        <div class="border-t border-charcoal-100 dark:border-charcoal-800 py-6 text-center text-xs text-charcoal-400">
            &copy; {{ now()->year }} {!! nl2br(e($footerBrand['copyright'])) !!}
        </div>
    @endif
</footer>
