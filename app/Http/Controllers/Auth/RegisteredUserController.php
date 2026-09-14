<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\AdminAlert;
use App\Services\UserOnboardingService;
use App\Support\Features;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(protected UserOnboardingService $onboarding) {}

    /**
     * Display the registration view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (! Features::isEnabled(Features::REGISTRATION)) {
            return response()->view('coming-soon-guest', [
                'feature' => 'New Registrations',
                'message' => Features::comingSoonMessage(),
            ]);
        }

        // Remember a valid referral code across the whole guest session so
        // it still applies even if the user browses around before actually
        // submitting the registration form.
        if ($request->filled('ref') && Features::isEnabled(Features::REFERRALS)) {
            $code = strtoupper((string) $request->query('ref'));

            if (User::where('referral_code', $code)->exists()) {
                $request->session()->put('referral_code', $code);
            }
        }

        return view('auth.register', ['referrer' => $this->pendingReferrer($request)]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'min:3', 'max:20', 'alpha_dash',
                Rule::unique(User::class, 'username'),
            ],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        if ($referrer = $this->pendingReferrer($request)) {
            $user->update(['referred_by' => $referrer->id]);
            $request->session()->forget('referral_code');
        }

        $user->assignRole('user');

        $this->onboarding->provisionWallets($user);

        ActivityLog::record($user->id, 'registered', ['username' => $user->username]);

        AdminAlert::broadcast(
            'New User Registered',
            "{$user->name} (@{$user->username}) just created an account.",
            'info',
            route('admin.users.show', $user)
        );

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect(route('dashboard', absolute: false));
    }

    protected function pendingReferrer(Request $request): ?User
    {
        if (! Features::isEnabled(Features::REFERRALS)) {
            return null;
        }

        $code = $request->session()->get('referral_code');

        return $code ? User::where('referral_code', $code)->first() : null;
    }
}
