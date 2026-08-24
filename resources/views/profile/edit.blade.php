<x-app-layout>
    <x-slot name="title">Profile</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="p-6 bg-white dark:bg-charcoal-900 border border-charcoal-100 dark:border-charcoal-800 shadow-sm rounded-2xl">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="p-6 bg-white dark:bg-charcoal-900 border border-charcoal-100 dark:border-charcoal-800 shadow-sm rounded-2xl">
            @include('profile.partials.update-password-form')
        </div>

        <div class="p-6 bg-white dark:bg-charcoal-900 border border-red-100 dark:border-red-900/40 shadow-sm rounded-2xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
