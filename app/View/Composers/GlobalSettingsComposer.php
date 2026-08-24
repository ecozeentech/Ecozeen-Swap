<?php

namespace App\View\Composers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class GlobalSettingsComposer
{
    public function compose(View $view): void
    {
        if (! Schema::hasTable('system_settings')) {
            $view->with('siteSettings', collect());

            return;
        }

        $view->with('siteSettings', SystemSetting::allSettings());
    }
}
