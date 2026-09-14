<x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
    <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10M9 21h6" /></svg></x-slot:icon>
    Dashboard
</x-sidebar-link>
<x-sidebar-link :href="route('wallet.index')" :active="request()->routeIs('wallet.*')">
    <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2zm11 8h2" /></svg></x-slot:icon>
    Wallet
</x-sidebar-link>
<x-sidebar-link :href="route('buy.index')" :active="request()->routeIs('buy.*')">
    <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-5-5m5 5l5-5" /></svg></x-slot:icon>
    Buy Crypto
</x-sidebar-link>
<x-sidebar-link :href="route('sell.index')" :active="request()->routeIs('sell.*')">
    <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20V4m0 0l5 5m-5-5l-5 5" /></svg></x-slot:icon>
    Sell Crypto
</x-sidebar-link>
<x-sidebar-link :href="route('swap.index')" :active="request()->routeIs('swap.*')">
    <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" /></svg></x-slot:icon>
    Swap
</x-sidebar-link>
<x-sidebar-link :href="route('giftcards.index')" :active="request()->routeIs('giftcards.*')">
    <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 10-2 2h2zm0 0a2 2 0 102-2h-2zm-8 5h16M5 8h14a1 1 0 011 1v3H4V9a1 1 0 011-1zm-1 4h16v7a1 1 0 01-1 1H5a1 1 0 01-1-1v-7z" /></svg></x-slot:icon>
    Gift Cards
</x-sidebar-link>
<x-sidebar-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')">
    <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg></x-slot:icon>
    Invoicing
</x-sidebar-link>
@if (\App\Support\Features::isEnabled(\App\Support\Features::REFERRALS))
    <x-sidebar-link :href="route('referrals.index')" :active="request()->routeIs('referrals.*')">
        <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4" /></svg></x-slot:icon>
        Referrals
    </x-sidebar-link>
@endif

<div class="pt-4 mt-4 border-t border-charcoal-100 dark:border-charcoal-800 space-y-1">
    <x-sidebar-link :href="route('bank-accounts.index')" :active="request()->routeIs('bank-accounts.*')">
        <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 10h16M4 10V6l8-4 8 4v4M6 10v11M10 10v11M14 10v11M18 10v11" /></svg></x-slot:icon>
        Bank Accounts
    </x-sidebar-link>
    <x-sidebar-link :href="route('security.two-factor')" :active="request()->routeIs('security.*')">
        <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg></x-slot:icon>
        Security &amp; KYC
    </x-sidebar-link>
    <x-sidebar-link :href="route('support.index')" :active="request()->routeIs('support.*')">
        <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg></x-slot:icon>
        Support
    </x-sidebar-link>
    @if (auth()->user()->hasAnyRole(['admin', 'super-admin']))
        <x-sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
            <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></x-slot:icon>
            Admin Panel
        </x-sidebar-link>
    @endif
</div>
