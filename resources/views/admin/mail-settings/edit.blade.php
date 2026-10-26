<x-admin-layout>
    <x-slot name="title">Mail (SMTP) Settings</x-slot>

    <div class="max-w-2xl space-y-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold">Outgoing Email</h3>
                <span @class([
                    'text-xs font-medium px-2 py-0.5 rounded-full',
                    'bg-green-100 text-green-700' => $effectiveMailer === 'smtp',
                    'bg-amber-100 text-amber-700' => $effectiveMailer !== 'smtp',
                ])>{{ $effectiveMailer === 'smtp' ? 'SMTP active' : 'Not sending real emails ('.$effectiveMailer.')' }}</span>
            </div>
            <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-6">
                Configure the credentials used for every outgoing email — order and withdrawal alerts to admins, KYC
                decisions, account notifications. Changes apply immediately, no code changes or redeploys needed.
            </p>

            <form method="POST" action="{{ route('admin.mail-settings.update') }}" x-data="{ mailer: '{{ $settings->get('mail_mailer')?->value ?: 'log' }}' }" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label value="Mailer" />
                    <select name="mail_mailer" x-model="mailer" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                        <option value="smtp">SMTP (send real emails)</option>
                        <option value="log">Log only (testing — no real emails sent)</option>
                    </select>
                </div>

                <div x-show="mailer === 'smtp'" class="space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="SMTP Host" />
                            <x-text-input name="mail_host" value="{{ $settings->get('mail_host')?->value }}" class="mt-1 w-full" placeholder="smtp.yourprovider.com" />
                            <x-input-error :messages="$errors->get('mail_host')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label value="SMTP Port" />
                            <x-text-input name="mail_port" type="number" value="{{ $settings->get('mail_port')?->value ?: 587 }}" class="mt-1 w-full" placeholder="587" />
                            <x-input-error :messages="$errors->get('mail_port')" class="mt-2" />
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="SMTP Username" />
                            <x-text-input name="mail_username" value="{{ $settings->get('mail_username')?->value }}" class="mt-1 w-full" autocomplete="off" />
                        </div>
                        <div>
                            <x-input-label value="SMTP Password" />
                            <x-password-input name="mail_password" autocomplete="new-password" placeholder="{{ $hasStoredPassword ? '•••••••• (leave blank to keep current)' : '' }}" />
                            <p class="text-xs text-charcoal-400 mt-1">{{ $hasStoredPassword ? 'A password is already saved — leave this blank to keep it.' : 'Not set yet.' }}</p>
                        </div>
                    </div>
                    <div>
                        <x-input-label value="Encryption" />
                        <select name="mail_encryption" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                            <option value="tls" @selected(($settings->get('mail_encryption')?->value ?: 'tls') === 'tls')>STARTTLS (recommended — typically port 587)</option>
                            <option value="ssl" @selected($settings->get('mail_encryption')?->value === 'ssl')>SSL/TLS (typically port 465)</option>
                            <option value="none" @selected($settings->get('mail_encryption')?->value === 'none')>None</option>
                        </select>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="From Address" />
                        <x-text-input name="mail_from_address" type="email" value="{{ $settings->get('mail_from_address')?->value ?: 'hello@'.request()->getHost() }}" class="mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('mail_from_address')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label value="From Name" />
                        <x-text-input name="mail_from_name" value="{{ $settings->get('mail_from_name')?->value ?: config('app.name') }}" class="mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('mail_from_name')" class="mt-2" />
                    </div>
                </div>

                <x-primary-button class="w-full justify-center py-2.5">Save Mail Settings</x-primary-button>
            </form>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <h3 class="font-semibold mb-1">Send a Test Email</h3>
            <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-4">Verify the settings above actually work by sending yourself a test message.</p>
            <form method="POST" action="{{ route('admin.mail-settings.test') }}" class="flex gap-3">
                @csrf
                <x-text-input name="test_email" type="email" value="{{ auth()->user()->email }}" class="flex-1" placeholder="you@example.com" required />
                <x-secondary-button type="submit" class="whitespace-nowrap">Send Test</x-secondary-button>
            </form>
            <x-input-error :messages="$errors->get('test_email')" class="mt-2" />
        </div>

        <div class="rounded-2xl border border-dashed border-charcoal-200 dark:border-charcoal-700 p-4 text-xs text-charcoal-400">
            Common causes of a failed test: wrong host/port, wrong username/password, the port blocked by your host's
            firewall, or the encryption setting not matching what your provider expects (try STARTTLS on port 587,
            or SSL/TLS on port 465).
        </div>
    </div>
</x-admin-layout>
