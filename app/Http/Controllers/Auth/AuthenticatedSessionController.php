<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $request->authenticate();

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('2fa.user.id', $user->id);
            $request->session()->put('2fa.remember', $request->boolean('remember'));

            return redirect()->route('two-factor.login');
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        $this->recordLogin($user, $request);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function recordLogin($user, Request $request): void
    {
        $user->forceFill([
            'last_login_ip' => $request->ip(),
            'last_login_at' => now(),
        ])->save();

        ActivityLog::record($user->id, 'login', ['ip' => $request->ip()]);
    }
}
