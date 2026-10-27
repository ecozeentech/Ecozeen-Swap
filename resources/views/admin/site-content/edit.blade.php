<x-admin-layout>
    <x-slot name="title">Site Content</x-slot>

    <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-4 max-w-3xl">
        Edit the public homepage, About page sections, blog header, navigation labels, footer, and contact page labels.
        The About story, contact intro, privacy policy, terms, and any extra pages are edited under
        <a href="{{ route('admin.pages.index') }}" class="font-semibold text-brand-600 hover:underline">Pages</a>.
        Phone, email, and address are under
        <a href="{{ route('admin.settings.index') }}" class="font-semibold text-brand-600 hover:underline">Settings</a>.
        Logos and the favicon are under
        <a href="{{ route('admin.branding.edit') }}" class="font-semibold text-brand-600 hover:underline">Branding</a>.
        Saving here updates the live site immediately.
    </p>

    <div class="flex flex-wrap gap-2 mb-6">
        @foreach ($groups as $key => $meta)
            <a href="{{ route('admin.site-content.edit', $key) }}" class="px-3 py-1.5 rounded-full text-sm font-semibold {{ $group === $key ? 'bg-brand-500 text-white' : 'bg-white dark:bg-charcoal-900 border border-charcoal-200 dark:border-charcoal-700 text-charcoal-600 dark:text-charcoal-300' }}">{{ $meta['label'] }}</a>
        @endforeach
    </div>

    <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-6 max-w-3xl">{{ $groups[$group]['description'] }}</p>

    <form method="POST" action="{{ route('admin.site-content.update', $group) }}" enctype="multipart/form-data" class="space-y-6 max-w-3xl">
        @csrf
        @method('PUT')

        @foreach ($sections as $sectionKey => $section)
            @php
                $stored = $values[$sectionKey] ?? [];
                $isRepeat = ! empty($section['repeat']);
                $rows = $isRepeat ? old('sections.'.$sectionKey, $stored) : [];
                $rows = is_array($rows) ? array_values(array_filter($rows, 'is_array')) : [];
                $block = $isRepeat ? [] : (is_array(old('sections.'.$sectionKey)) ? old('sections.'.$sectionKey) : $stored);
                $storedById = [];
                if ($isRepeat) {
                    foreach ($stored as $storedRow) {
                        if (is_array($storedRow) && ! empty($storedRow['_id'])) {
                            $storedById[$storedRow['_id']] = $storedRow;
                        }
                    }
                }
            @endphp

            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm space-y-4">
                <div>
                    <h2 class="font-semibold text-charcoal-900 dark:text-white">{{ $section['label'] }}</h2>
                    @if (! empty($section['help']))
                        <p class="text-xs text-charcoal-400 mt-1">{{ $section['help'] }}</p>
                    @endif
                </div>

                <input type="hidden" name="present[]" value="{{ $sectionKey }}">

                @if ($isRepeat)
                    <div data-repeater data-next-index="{{ count($rows) }}" data-max="{{ $section['max'] ?? 12 }}">
                        <div data-rows class="space-y-4">
                            @foreach ($rows as $i => $row)
                                @include('admin.site-content.row', [
                                    'sectionKey' => $sectionKey,
                                    'section' => $section,
                                    'index' => $i,
                                    'row' => $row,
                                    'storedById' => $storedById,
                                ])
                            @endforeach
                        </div>
                        <template>
                            @include('admin.site-content.row', [
                                'sectionKey' => $sectionKey,
                                'section' => $section,
                                'index' => '__INDEX__',
                                'row' => [],
                                'storedById' => [],
                            ])
                        </template>
                        <button type="button" data-add-row class="mt-4 inline-flex items-center px-3 py-1.5 rounded-lg border border-charcoal-200 dark:border-charcoal-700 text-sm font-semibold">Add item</button>
                    </div>
                @else
                    @foreach ($section['fields'] as $field => $meta)
                        <div>
                            <x-input-label :value="$meta['label']" />
                            @if ($meta['type'] === 'textarea')
                                <textarea name="sections[{{ $sectionKey }}][{{ $field }}]" rows="3" maxlength="{{ $meta['max'] }}" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white text-sm">{{ $block[$field] ?? '' }}</textarea>
                            @elseif ($meta['type'] === 'image')
                                @php $preview = \App\Support\SiteContent::imageUrl($stored[$field] ?? null); @endphp
                                @if ($preview)
                                    <div class="mt-2 h-24 w-24 rounded-xl bg-charcoal-50 dark:bg-charcoal-800 flex items-center justify-center overflow-hidden">
                                        <img src="{{ $preview }}" alt="" class="max-h-full max-w-full object-contain">
                                    </div>
                                    <label class="mt-2 flex items-center gap-2 text-sm text-charcoal-500">
                                        <input type="checkbox" name="remove[{{ $sectionKey }}][{{ $field }}]" value="1" class="rounded border-charcoal-300 text-brand-600 focus:ring-brand-500">
                                        Remove image
                                    </label>
                                @endif
                                <input type="file" name="images[{{ $sectionKey }}][{{ $field }}]" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml" class="mt-2 w-full text-sm">
                                <p class="text-xs text-charcoal-400 mt-1">JPG, PNG, WEBP, or SVG. Max 4 MB. Leave empty to keep the current image.</p>
                            @else
                                <x-text-input name="sections[{{ $sectionKey }}][{{ $field }}]" value="{{ $block[$field] ?? '' }}" maxlength="{{ $meta['max'] }}" class="mt-1 w-full" />
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach

        <div class="flex justify-end">
            <x-primary-button class="px-6">Save changes</x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.site-content.reset', $group) }}" class="max-w-3xl mt-4" onsubmit="return confirm('Reset this tab to the original text and remove its uploaded images?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm font-semibold text-red-600 hover:underline">Reset this tab to defaults</button>
    </form>

    <script>
        document.addEventListener('click', function (event) {
            var add = event.target.closest('[data-add-row]');
            if (add) {
                var wrap = add.closest('[data-repeater]');
                var rows = wrap.querySelector('[data-rows]');
                var max = parseInt(wrap.getAttribute('data-max'), 10) || 12;
                if (rows.querySelectorAll('[data-row]').length >= max) {
                    return;
                }
                var next = parseInt(wrap.getAttribute('data-next-index'), 10) || 0;
                var html = wrap.querySelector('template').innerHTML.replaceAll('__INDEX__', String(next));
                rows.insertAdjacentHTML('beforeend', html);
                wrap.setAttribute('data-next-index', String(next + 1));
                return;
            }

            var remove = event.target.closest('[data-remove-row]');
            if (remove) {
                var row = remove.closest('[data-row]');
                if (row) {
                    row.remove();
                }
            }
        });
    </script>
</x-admin-layout>
