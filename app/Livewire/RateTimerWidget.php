<?php

namespace App\Livewire;

use App\Services\RateService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RateTimerWidget extends Component
{
    public function render()
    {
        return view('livewire.rate-timer-widget', [
            'rates' => $this->rates,
        ]);
    }

    #[Computed]
    public function rates()
    {
        return app(RateService::class)->allActiveRates();
    }
}
