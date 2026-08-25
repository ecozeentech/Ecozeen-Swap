<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\GiftCard;
use App\Models\GiftCardProduct;
use App\Models\SystemSetting;
use App\Notifications\AdminAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GiftCardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $giftCards = $user->giftCards()->with('product')->latest()->paginate(10);

        $products = GiftCardProduct::query()->active()->orderBy('sort_order')->orderBy('name')->get()
            ->filter(fn (GiftCardProduct $product) => $product->isAvailableIn($user->country))
            ->values();

        return view('giftcards.index', [
            'giftCards' => $giftCards,
            'products' => $products,
            'defaultBuybackRate' => (float) SystemSetting::get('giftcard_buyback_rate', 75),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'gift_card_product_id' => ['required', 'exists:gift_card_products,id'],
            'card_number' => ['required', 'string', 'max:100'],
            'pin' => ['nullable', 'string', 'max:50'],
            'face_value' => ['required', 'numeric', 'min:1'],
            'card_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $product = GiftCardProduct::query()->active()->findOrFail($request->input('gift_card_product_id'));

        if (! $product->isAvailableIn($user->country)) {
            return back()->withErrors(['gift_card_product_id' => 'This gift card is not available in your country.']);
        }

        $rate = (float) ($product->rate_override ?? SystemSetting::get('giftcard_buyback_rate', 75));
        $sellingPrice = round($request->input('face_value') * ($rate / 100), 2);

        $imagePath = $request->hasFile('card_image')
            ? $request->file('card_image')->store('giftcards/'.$user->id, 'local')
            : null;

        $giftCard = GiftCard::create([
            'user_id' => $user->id,
            'gift_card_product_id' => $product->id,
            'card_type' => $product->name,
            'card_number_hashed' => $request->input('card_number'),
            'pin' => $request->input('pin'),
            'card_image' => $imagePath,
            'face_value' => $request->input('face_value'),
            'face_value_currency' => $product->currency,
            'rate_applied' => $rate,
            'selling_price' => $sellingPrice,
            'payout_currency' => $product->currency,
            'status' => 'pending',
        ]);

        ActivityLog::record($user->id, 'giftcard_submitted', ['card_type' => $giftCard->card_type]);

        AdminAlert::broadcast(
            'Gift Card Submitted',
            "{$user->name} (@{$user->username}) submitted a {$giftCard->card_type} gift card worth ".number_format((float) $giftCard->face_value, 2)." {$giftCard->face_value_currency}.",
            'info',
            route('admin.giftcards.index')
        );

        return redirect()->route('giftcards.index')->with('status', 'giftcard-submitted');
    }
}
