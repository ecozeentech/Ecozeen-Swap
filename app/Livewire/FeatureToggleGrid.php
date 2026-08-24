<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Support\Features;
use Livewire\Component;

class FeatureToggleGrid extends Component
{
    public array $flags = [];

    public function mount(): void
    {
        foreach (array_keys(Features::all()) as $key) {
            $this->flags[$key] = Features::isEnabled($key);
        }
    }

    public function toggle(string $key): void
    {
        if (! array_key_exists($key, Features::all())) {
            return;
        }

        $this->flags[$key] = ! ($this->flags[$key] ?? true);

        SystemSetting::set($key, $this->flags[$key], 'boolean', 'features');

        ActivityLog::record(auth()->id(), 'admin_toggled_feature', [
            'feature' => $key,
            'enabled' => $this->flags[$key],
        ]);

        $this->dispatch('feature-toggled', feature: $key, enabled: $this->flags[$key]);
    }

    public function render()
    {
        return view('livewire.feature-toggle-grid', ['features' => Features::all()]);
    }
}
