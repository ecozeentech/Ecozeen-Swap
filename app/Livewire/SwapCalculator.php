<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\CryptoAsset;
use App\Services\TradeService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use RuntimeException;

class SwapCalculator extends Component
{
    public ?int $fromAssetId = null;

    public ?int $toAssetId = null;

    public string $amount = '';

    public ?array $lockedQuote = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    #[Computed]
    public function cryptoAssets()
    {
        return CryptoAsset::query()->active()->orderBy('name')->get();
    }

    public function updated($property): void
    {
        if (in_array($property, ['fromAssetId', 'toAssetId', 'amount'])) {
            $this->lockedQuote = null;
            $this->errorMessage = null;
        }
    }

    public function getQuote(): void
    {
        $this->errorMessage = null;

        if (! $this->fromAssetId || ! $this->toAssetId || $this->fromAssetId === $this->toAssetId) {
            $this->errorMessage = 'Please select two different assets to swap.';

            return;
        }

        if (! is_numeric($this->amount) || (float) $this->amount <= 0) {
            $this->errorMessage = 'Enter a valid amount to swap.';

            return;
        }

        try {
            $quote = app(TradeService::class)->quoteSwap(
                auth()->user(),
                CryptoAsset::findOrFail($this->fromAssetId),
                CryptoAsset::findOrFail($this->toAssetId),
                (float) $this->amount
            );

            $this->lockedQuote = [
                'token' => $quote['token'],
                'from_amount' => (string) $quote['from_amount'],
                'to_amount' => (string) $quote['to_amount'],
                'from_symbol' => CryptoAsset::find($this->fromAssetId)->symbol,
                'to_symbol' => CryptoAsset::find($this->toAssetId)->symbol,
                'expires_at' => $quote['expires_at']->toIso8601String(),
            ];
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function confirmSwap(): void
    {
        if (! $this->lockedQuote) {
            $this->errorMessage = 'Please get a quote first.';

            return;
        }

        try {
            $transaction = app(TradeService::class)->executeSwap(auth()->user(), $this->lockedQuote['token']);

            ActivityLog::record(auth()->id(), 'swap_executed', ['reference' => $transaction->reference]);

            $this->successMessage = "Swap complete! {$this->lockedQuote['from_amount']} {$this->lockedQuote['from_symbol']} converted to {$this->lockedQuote['to_amount']} {$this->lockedQuote['to_symbol']}.";
            $this->lockedQuote = null;
            $this->amount = '';
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.swap-calculator');
    }
}
