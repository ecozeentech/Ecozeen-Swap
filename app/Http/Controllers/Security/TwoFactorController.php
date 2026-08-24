<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        if (! $user->two_factor_secret) {
            $user->forceFill([
                'two_factor_secret' => (new Google2FA)->generateSecretKey(),
            ])->save();
            $user->refresh();
        }

        $qrCodeSvg = null;

        if (! $user->hasTwoFactorEnabled()) {
            $google2fa = new Google2FA;
            $qrUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $user->two_factor_secret
            );

            $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd);
            $qrCodeSvg = (new Writer($renderer))->writeString($qrUrl);
        }

        return view('security.two-factor', [
            'enabled' => $user->hasTwoFactorEnabled(),
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $user->hasTwoFactorEnabled() ? null : $user->two_factor_secret,
            'recoveryCodes' => $user->two_factor_recovery_codes,
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();
        $google2fa = new Google2FA;

        if (! $google2fa->verifyKey($user->two_factor_secret, str_replace(' ', '', $request->input('code')))) {
            throw ValidationException::withMessages(['code' => 'The verification code is incorrect.']);
        }

        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => Str::random(4).'-'.Str::random(4).'-'.Str::random(4))
            ->all();

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();

        ActivityLog::record($user->id, 'two_factor_enabled');

        return redirect()->route('security.two-factor')->with('status', 'two-factor-enabled');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        ActivityLog::record($request->user()->id, 'two_factor_disabled');

        return redirect()->route('security.two-factor')->with('status', 'two-factor-disabled');
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => Str::random(4).'-'.Str::random(4).'-'.Str::random(4))
            ->all();

        $request->user()->forceFill(['two_factor_recovery_codes' => $recoveryCodes])->save();

        ActivityLog::record($request->user()->id, 'two_factor_recovery_codes_regenerated');

        return redirect()->route('security.two-factor')->with('status', 'recovery-codes-regenerated');
    }
}
