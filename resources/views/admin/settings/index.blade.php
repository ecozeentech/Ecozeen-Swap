<x-admin-layout>
    <x-slot name="title">Settings</x-slot>

    <div class="max-w-2xl rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.settings.update') }}" x-data="{ widgetType: '{{ $settings->get('support_widget_type')?->value ?? 'tawkto' }}' }" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <x-input-label value="Coming Soon Message" />
                <textarea name="coming_soon_message" rows="2" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">{{ $settings->get('coming_soon_message')?->value }}</textarea>
            </div>

            <div>
                <x-input-label value="Bank Transfer Memo Notice" />
                <textarea name="bank_transfer_memo_notice" rows="2" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">{{ $settings->get('bank_transfer_memo_notice')?->value }}</textarea>
            </div>

            <div class="rounded-xl border border-charcoal-100 dark:border-charcoal-800 p-4 space-y-4">
                <div>
                    <h3 class="font-semibold text-sm text-charcoal-900 dark:text-white">Contact Page Information</h3>
                    <p class="text-xs text-charcoal-400">Shown on the public Contact Us page. Leave blank to hide a field.</p>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Support Phone Number" />
                        <x-text-input name="contact_phone" value="{{ $settings->get('contact_phone')?->value }}" class="mt-1 w-full" placeholder="+234 800 000 0000" />
                    </div>
                    <div>
                        <x-input-label value="Support Email" />
                        <x-text-input name="contact_email" type="email" value="{{ $settings->get('contact_email')?->value }}" class="mt-1 w-full" placeholder="support@ecozeenswap.com" />
                    </div>
                </div>
                <div>
                    <x-input-label value="Office Address" />
                    <x-text-input name="contact_address" value="{{ $settings->get('contact_address')?->value }}" class="mt-1 w-full" placeholder="150 Crypto Blvd, Suite 400, New York, NY 10005" />
                </div>
            </div>

            <div>
                <x-input-label value="Default Display Currency" />
                <p class="text-xs text-charcoal-400 mb-1">Used on the user dashboard for anyone who hasn't picked their own preferred currency yet.</p>
                <select name="default_display_currency" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                    @foreach ($fiatCurrencies as $fiat)
                        <option value="{{ $fiat->code }}" @selected(($settings->get('default_display_currency')?->value ?: 'USD') === $fiat->code)>{{ $fiat->name }} ({{ $fiat->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label value="Gift Card Buyback Rate (% of face value)" />
                <x-text-input name="giftcard_buyback_rate" type="number" min="0" max="100" value="{{ $settings->get('giftcard_buyback_rate')?->value ?? 75 }}" class="mt-1 w-full" />
            </div>

            <div>
                <x-input-label value="Support Widget" />
                <select name="support_widget_type" x-model="widgetType" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                    <option value="tawkto">Tawk.to</option>
                    <option value="custom_url">Custom Chat URL</option>
                    <option value="none">Disabled</option>
                </select>
            </div>

            <div x-show="widgetType === 'tawkto'">
                <x-input-label value="Tawk.to Embed Script URL" />
                <x-text-input name="tawkto_embed_url" value="{{ $settings->get('tawkto_embed_url')?->value }}" class="mt-1 w-full" placeholder="https://embed.tawk.to/xxxxx/default" />
            </div>

            <div x-show="widgetType === 'custom_url'">
                <x-input-label value="Custom Chat URL" />
                <x-text-input name="custom_chat_url" value="{{ $settings->get('custom_chat_url')?->value }}" class="mt-1 w-full" placeholder="https://wa.me/..." />
            </div>

            <x-primary-button class="w-full justify-center py-2.5">Save Settings</x-primary-button>
        </form>
    </div>

    <div class="max-w-2xl mt-6 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm text-sm text-charcoal-500 dark:text-charcoal-400">
        <h3 class="font-semibold text-charcoal-900 dark:text-white mb-2">Company Information</h3>
        <p>Ecozeen Tech Ltd</p>
        <p>Nigeria: RC 1835204</p>
        <p>United Kingdom: 16582062</p>
    </div>
</x-admin-layout>
