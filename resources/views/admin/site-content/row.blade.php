@php
    $rowId = $row['_id'] ?? '';
    $storedRow = ($rowId !== '' && isset($storedById[$rowId])) ? $storedById[$rowId] : [];
@endphp
<div data-row class="rounded-xl border border-charcoal-100 dark:border-charcoal-800 p-4 space-y-3">
    <input type="hidden" name="sections[{{ $sectionKey }}][{{ $index }}][_id]" value="{{ $rowId }}">
    @foreach ($section['item'] as $field => $meta)
        <div>
            <x-input-label :value="$meta['label']" />
            @if ($meta['type'] === 'textarea')
                <textarea name="sections[{{ $sectionKey }}][{{ $index }}][{{ $field }}]" rows="3" maxlength="{{ $meta['max'] }}" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white text-sm">{{ $row[$field] ?? '' }}</textarea>
            @elseif ($meta['type'] === 'image')
                @php $preview = \App\Support\SiteContent::imageUrl($storedRow[$field] ?? ($row[$field] ?? null)); @endphp
                @if ($preview)
                    <div class="mt-2 h-20 w-20 rounded-xl bg-charcoal-50 dark:bg-charcoal-800 flex items-center justify-center overflow-hidden">
                        <img src="{{ $preview }}" alt="" class="max-h-full max-w-full object-contain">
                    </div>
                    <label class="mt-2 flex items-center gap-2 text-sm text-charcoal-500">
                        <input type="checkbox" name="remove[{{ $sectionKey }}][{{ $index }}][{{ $field }}]" value="1" class="rounded border-charcoal-300 text-brand-600 focus:ring-brand-500">
                        Remove image
                    </label>
                @endif
                <input type="file" name="images[{{ $sectionKey }}][{{ $index }}][{{ $field }}]" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml" class="mt-2 w-full text-sm">
            @else
                <x-text-input name="sections[{{ $sectionKey }}][{{ $index }}][{{ $field }}]" value="{{ $row[$field] ?? '' }}" maxlength="{{ $meta['max'] }}" class="mt-1 w-full" />
            @endif
        </div>
    @endforeach
    <button type="button" data-remove-row class="text-sm font-semibold text-red-600 hover:underline">Remove</button>
</div>
