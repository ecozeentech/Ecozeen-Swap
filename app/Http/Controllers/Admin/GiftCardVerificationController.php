<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\GiftCard;
use App\Models\Transaction;
use App\Notifications\AccountNotification;
use App\Services\WalletService;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class GiftCardVerificationController extends Controller
{
    public function __construct(protected WalletService $wallets) {}

    public function index(Request $request): View
    {
        $giftCards = GiftCard::query()
            ->with('user')
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status), fn ($q) => $q->whereIn('status', ['pending', 'reviewing']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.giftcards.index', ['giftCards' => $giftCards]);
    }

    public function approve(GiftCard $giftCard): RedirectResponse
    {
        try {
            DB::transaction(function () use ($giftCard) {
                $locked = GiftCard::query()->lockForUpdate()->findOrFail($giftCard->id);

                if (! in_array($locked->status, ['pending', 'reviewing'], true)) {
                    throw new RuntimeException('This gift card has already been processed.');
                }

                $wallet = $this->wallets->getOrCreateWallet($locked->user, 'fiat', $locked->payout_currency ?? 'USD');

                $this->wallets->credit($wallet, $locked->selling_price, [
                    'type' => 'giftcard',
                    'currency_code' => $wallet->currency_code,
                    'status' => 'completed',
                    'reference' => Transaction::generateReference('GFT'),
                    'metadata' => ['gift_card_id' => $locked->id, 'card_type' => $locked->card_type],
                ]);

                $locked->update([
                    'status' => 'paid',
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['gift_card' => $e->getMessage()]);
        }

        Notify::send($giftCard->user, new AccountNotification(
            'Gift Card Approved',
            "Your {$giftCard->card_type} gift card was verified and ".number_format((float) $giftCard->selling_price, 2)." {$giftCard->payout_currency} has been credited to your wallet.",
            'success',
            route('giftcards.index')
        ));

        ActivityLog::record(auth()->id(), 'admin_approved_giftcard', ['gift_card_id' => $giftCard->id]);

        return back()->with('status', 'giftcard-approved');
    }

    public function reject(Request $request, GiftCard $giftCard): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $giftCard->update([
            'status' => 'rejected',
            'admin_note' => $request->input('reason'),
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        Notify::send($giftCard->user, new AccountNotification(
            'Gift Card Rejected',
            "Your {$giftCard->card_type} gift card submission was rejected. Reason: {$request->input('reason')}",
            'danger',
            route('giftcards.index')
        ));

        ActivityLog::record(auth()->id(), 'admin_rejected_giftcard', ['gift_card_id' => $giftCard->id]);

        return back()->with('status', 'giftcard-rejected');
    }
}
