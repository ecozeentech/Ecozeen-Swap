<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\GiftCardProduct;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GiftCardProductController extends Controller
{
    public function __construct(protected MediaUploadService $media) {}

    public function index(): View
    {
        return view('admin.giftcard-products.index', [
            'products' => GiftCardProduct::query()->orderBy('sort_order')->orderBy('name')->get(),
            'countries' => config('countries'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['logo'] = $request->hasFile('logo') ? $this->media->store($request->file('logo'), 'giftcard-logos') : null;
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);

        $product = GiftCardProduct::create($data);

        ActivityLog::record(auth()->id(), 'admin_created_giftcard_product', ['name' => $product->name]);

        return back()->with('status', 'giftcard-product-created');
    }

    public function update(Request $request, GiftCardProduct $giftCardProduct): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->media->replace($request->file('logo'), 'giftcard-logos', $giftCardProduct->logo);
        }

        $giftCardProduct->update($data);

        ActivityLog::record(auth()->id(), 'admin_updated_giftcard_product', ['name' => $giftCardProduct->name]);

        return back()->with('status', 'giftcard-product-updated');
    }

    public function toggleActive(GiftCardProduct $giftCardProduct): RedirectResponse
    {
        $giftCardProduct->update(['is_active' => ! $giftCardProduct->is_active]);

        return back()->with('status', $giftCardProduct->is_active ? 'giftcard-product-activated' : 'giftcard-product-deactivated');
    }

    public function destroy(GiftCardProduct $giftCardProduct): RedirectResponse
    {
        if ($giftCardProduct->giftCards()->exists()) {
            return back()->withErrors(['product' => "{$giftCardProduct->name} has existing user submissions and can't be deleted. Deactivate it instead."]);
        }

        $this->media->forget($giftCardProduct->logo);
        $name = $giftCardProduct->name;
        $giftCardProduct->delete();

        ActivityLog::record(auth()->id(), 'admin_deleted_giftcard_product', ['name' => $name]);

        return back()->with('status', 'giftcard-product-deleted');
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'currency' => ['required', 'string', 'max:6'],
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['required', 'numeric', 'gte:min_amount'],
            'rate_override' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'size:2'],
            'logo' => MediaUploadService::logoRules(),
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['countries'] = $request->input('countries') ?: null;

        unset($validated['logo']);

        return $validated;
    }
}
