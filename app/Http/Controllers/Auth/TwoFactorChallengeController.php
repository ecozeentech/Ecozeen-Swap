<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('2fa.user.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = $request->session()->get('2fa.user.id');
        $user = User::find($userId);

        if (! $user) {
            return redirect()->route('login');
        }

        $google2fa = new Google2FA;
        $code = str_replace(' ', '', $request->input('code'));

        $valid = $google2fa->verifyKey($user->two_factor_secret, $code)
            || $this->isValidRecoveryCode($user, $code);

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => 'The verification code you entered is invalid.',
            ]);
        }

        Auth::login($user, (bool) $request->session()->get('2fa.remember', false));

        $request->session()->forget(['2fa.user.id', '2fa.remember']);
        $request->session()->regenerate();

        $user->forceFill([
            'last_login_ip' => $request->ip(),
            'last_login_at' => now(),
        ])->save();

        ActivityLog::record($user->id, 'login_2fa', ['ip' => $request->ip()]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    protected function isValidRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];

        if (! in_array($code, $codes, true)) {
            return false;
        }

        $user->update([
            'two_factor_recovery_codes' => array_values(array_diff($codes, [$code])),
        ]);

        return true;
    }
}
