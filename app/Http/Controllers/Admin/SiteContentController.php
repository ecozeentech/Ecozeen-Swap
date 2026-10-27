<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Services\MediaUploadService;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteContentController extends Controller
{
    public function __construct(protected MediaUploadService $media) {}

    public function edit(?string $group = null): View
    {
        $group = $group ?: 'homepage';
        abort_unless(isset(SiteContent::groups()[$group]), 404);

        $values = [];

        foreach (SiteContent::sections($group) as $key => $definition) {
            $values[$key] = SiteContent::data($group, $key);
        }

        return view('admin.site-content.edit', [
            'group' => $group,
            'groups' => SiteContent::groups(),
            'sections' => SiteContent::sections($group),
            'values' => $values,
        ]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        $definitions = SiteContent::sections($group);
        abort_if($definitions === [], 404);

        $rules = [
            'present' => ['required', 'array', 'min:1'],
            'present.*' => ['string', Rule::in(array_keys($definitions))],
        ];

        foreach ($definitions as $key => $definition) {
            if (SiteContent::isRepeat($definition)) {
                $rules['sections.'.$key] = ['nullable', 'array', 'max:'.($definition['max'] ?? 12)];

                foreach ($definition['item'] as $field => $meta) {
                    if ($meta['type'] === 'image') {
                        $rules['images.'.$key.'.*.'.$field] = MediaUploadService::logoRules(4096);
                    } else {
                        $rules['sections.'.$key.'.*.'.$field] = ['nullable', 'string', 'max:'.$meta['max']];
                    }
                }

                $rules['sections.'.$key.'.*._id'] = ['nullable', 'string', 'max:40'];
            } else {
                foreach ($definition['fields'] as $field => $meta) {
                    if ($meta['type'] === 'image') {
                        $rules['images.'.$key.'.'.$field] = MediaUploadService::logoRules(4096);
                    } else {
                        $rules['sections.'.$key.'.'.$field] = ['nullable', 'string', 'max:'.$meta['max']];
                    }
                }
            }
        }

        $validated = $request->validate($rules);
        $present = $validated['present'];

        foreach ($definitions as $key => $definition) {
            if (! in_array($key, $present, true)) {
                continue;
            }

            $current = SiteContent::data($group, $key);
            $previousPaths = $this->imagePaths($definition, $current);

            if (SiteContent::isRepeat($definition)) {
                $payload = $this->repeatPayload($request, $key, $definition, $current);
            } else {
                $payload = $this->blockPayload($request, $key, $definition, $current);
            }

            $keptPaths = $this->imagePaths($definition, $payload);

            foreach ($previousPaths as $path) {
                if (! in_array($path, $keptPaths, true)) {
                    $this->media->forget($path);
                }
            }

            SystemSetting::set(SiteContent::storageKey($group, $key), $payload, 'json', SiteContent::GROUP);
        }

        ActivityLog::record(auth()->id(), 'admin_updated_site_content', ['group' => $group]);

        return redirect()
            ->route('admin.site-content.edit', $group)
            ->with('status', 'site-content-updated');
    }

    public function reset(string $group): RedirectResponse
    {
        $definitions = SiteContent::sections($group);
        abort_if($definitions === [], 404);

        foreach ($definitions as $key => $definition) {
            $current = SystemSetting::get(SiteContent::storageKey($group, $key), null);

            if (is_array($current)) {
                foreach ($this->imagePaths($definition, $current) as $path) {
                    $this->media->forget($path);
                }
            }

            SystemSetting::query()->where('key', SiteContent::storageKey($group, $key))->get()->each->delete();
        }

        ActivityLog::record(auth()->id(), 'admin_reset_site_content', ['group' => $group]);

        return redirect()
            ->route('admin.site-content.edit', $group)
            ->with('status', 'site-content-reset');
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $current
     * @return array<string, string>
     */
    protected function blockPayload(Request $request, string $key, array $definition, array $current): array
    {
        $submitted = $request->input('sections.'.$key, []);
        $submitted = is_array($submitted) ? $submitted : [];
        $payload = [];

        foreach ($definition['fields'] as $field => $meta) {
            if ($meta['type'] === 'image') {
                $payload[$field] = $this->resolveImage(
                    $request,
                    'images.'.$key.'.'.$field,
                    'remove.'.$key.'.'.$field,
                    (string) ($current[$field] ?? '')
                );

                continue;
            }

            if (! array_key_exists($field, $submitted)) {
                $payload[$field] = (string) ($current[$field] ?? $meta['default'] ?? '');

                continue;
            }

            $payload[$field] = $this->cleanText((string) $submitted[$field], $meta['type'] === 'textarea');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<int, mixed>  $current
     * @return array<int, array<string, string>>
     */
    protected function repeatPayload(Request $request, string $key, array $definition, array $current): array
    {
        $submitted = $request->input('sections.'.$key, []);
        $submitted = is_array($submitted) ? $submitted : [];
        $existingById = [];

        foreach ($current as $row) {
            if (is_array($row) && ! empty($row['_id'])) {
                $existingById[(string) $row['_id']] = $row;
            }
        }

        $usedIds = [];
        $clean = [];
        $max = (int) ($definition['max'] ?? 12);

        foreach ($submitted as $index => $row) {
            if (! is_array($row) || count($clean) >= $max) {
                continue;
            }

            $id = trim((string) ($row['_id'] ?? ''));

            if ($id === '' || isset($usedIds[$id]) || ! isset($existingById[$id])) {
                $previous = [];
                $id = (string) Str::uuid();
            } else {
                $previous = $existingById[$id];
            }

            $usedIds[$id] = true;
            $item = ['_id' => $id];
            $hasContent = false;

            foreach ($definition['item'] as $field => $meta) {
                if ($meta['type'] === 'image') {
                    $path = $this->resolveImage(
                        $request,
                        'images.'.$key.'.'.$index.'.'.$field,
                        'remove.'.$key.'.'.$index.'.'.$field,
                        (string) ($previous[$field] ?? '')
                    );
                    $item[$field] = $path;
                    $hasContent = $hasContent || $path !== '';

                    continue;
                }

                $value = array_key_exists($field, $row)
                    ? $this->cleanText((string) $row[$field], $meta['type'] === 'textarea')
                    : '';
                $item[$field] = $value;
                $hasContent = $hasContent || $value !== '';
            }

            if ($hasContent) {
                $clean[] = $item;
            }
        }

        if ($clean === [] && ($definition['fallback_when_empty'] ?? false)) {
            return SiteContent::defaultData($definition);
        }

        return $clean;
    }

    protected function resolveImage(Request $request, string $fileKey, string $removeKey, string $previous): string
    {
        if ($request->hasFile($fileKey)) {
            return $this->media->store($request->file($fileKey), 'site-content');
        }

        if ($request->boolean($removeKey)) {
            return '';
        }

        return $previous;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<int|string, mixed>  $data
     * @return array<int, string>
     */
    protected function imagePaths(array $definition, array $data): array
    {
        $paths = [];

        if (SiteContent::isRepeat($definition)) {
            foreach ($data as $row) {
                if (! is_array($row)) {
                    continue;
                }

                foreach ($definition['item'] as $field => $meta) {
                    if ($meta['type'] === 'image' && ! empty($row[$field]) && is_string($row[$field])) {
                        $paths[] = $row[$field];
                    }
                }
            }

            return $paths;
        }

        foreach ($definition['fields'] as $field => $meta) {
            if ($meta['type'] === 'image' && ! empty($data[$field]) && is_string($data[$field])) {
                $paths[] = $data[$field];
            }
        }

        return $paths;
    }

    protected function cleanText(string $value, bool $multiline): string
    {
        $value = trim(strip_tags($value));

        if ($multiline) {
            return trim(preg_replace("/\r\n?/", "\n", $value) ?? $value);
        }

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }
}
