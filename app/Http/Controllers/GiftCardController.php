<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\GiftCard;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GiftCardController extends Controller
{
    public function index(Request $request): View
    {
        $giftCards = $request->user()->giftCards()->latest()->paginate(10);

        return view('giftcards.index', [
            'giftCards' => $giftCards,
            'cardTypes' => ['Amazon', 'iTunes', 'Steam', 'Google Play', 'Walmart', 'eBay', 'Razer Gold', 'Vanilla Visa', 'Other'],
            'buybackRate' => (float) SystemSetting::get('giftcard_buyback_rate', 75), // % of face value
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'card_type' => ['required', 'string', 'max:100'],
            'card_number' => ['required', 'string', 'max:100'],
            'pin' => ['nullable', 'string', 'max:50'],
            'face_value' => ['required', 'numeric', 'min:1'],
            'face_value_currency' => ['required', 'string', 'max:6'],
            'card_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $rate = (float) SystemSetting::get('giftcard_buyback_rate', 75);
        $sellingPrice = round($request->input('face_value') * ($rate / 100), 2);

        $imagePath = $request->hasFile('card_image')
            ? $request->file('card_image')->store('giftcards/'.$user->id, 'local')
            : null;

        $giftCard = GiftCard::create([
            'user_id' => $user->id,
            'card_type' => $request->input('card_type'),
            'card_number_hashed' => $request->input('card_number'),
            'pin' => $request->input('pin'),
            'card_image' => $imagePath,
            'face_value' => $request->input('face_value'),
            'face_value_currency' => $request->input('face_value_currency'),
            'rate_applied' => $rate,
            'selling_price' => $sellingPrice,
            'payout_currency' => $request->input('face_value_currency'),
            'status' => 'pending',
        ]);

        ActivityLog::record($user->id, 'giftcard_submitted', ['card_type' => $giftCard->card_type]);

        return redirect()->route('giftcards.index')->with('status', 'giftcard-submitted');
    }
}
