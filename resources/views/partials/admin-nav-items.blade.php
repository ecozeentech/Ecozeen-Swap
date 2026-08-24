@php
$adminLink = function ($route, $label, $svgPath) {
    $active = request()->routeIs($route.'*');
    $classes = $active
        ? 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold bg-brand-500 text-white'
        : 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-300 hover:bg-charcoal-800 hover:text-white';

    return "<a href=\"".route($route)."\" class=\"{$classes}\"><svg class=\"h-5 w-5 flex-shrink-0\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\" stroke-width=\"2\">{$svgPath}</svg><span>{$label}</span></a>";
};
@endphp

{!! $adminLink('admin.dashboard', 'Dashboard', '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10M9 21h6" />') !!}
{!! $adminLink('admin.users.index', 'Users', '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4" />') !!}
{!! $adminLink('admin.crypto.index', 'Crypto Assets', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 8v2m9-4a9 9 0 11-18 0 9 9 0 0118 0z" />') !!}
{!! $adminLink('admin.fiat.index', 'Fiat Currencies', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 8v2m9-4a9 9 0 11-18 0 9 9 0 0118 0z" />') !!}
{!! $adminLink('admin.rates.index', 'Daily Rates', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />') !!}
{!! $adminLink('admin.features.index', 'Feature Toggles', '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" />') !!}
{!! $adminLink('admin.transactions.index', 'Transactions', '<path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z" />') !!}
{!! $adminLink('admin.giftcards.index', 'Gift Cards', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 10-2 2h2zm0 0a2 2 0 102-2h-2zm-8 5h16M5 8h14a1 1 0 011 1v3H4V9a1 1 0 011-1zm-1 4h16v7a1 1 0 01-1 1H5a1 1 0 01-1-1v-7z" />') !!}
{!! $adminLink('admin.gateways.index', 'Payment Gateways', '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2zm11 8h2" />') !!}
{!! $adminLink('admin.logs.index', 'System Logs', '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />') !!}
{!! $adminLink('admin.settings.index', 'Settings', '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />') !!}
