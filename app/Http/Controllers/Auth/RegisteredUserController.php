<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
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
    public function create(): View|RedirectResponse
    {
        if (! Features::isEnabled(Features::REGISTRATION)) {
            return response()->view('coming-soon-guest', [
                'feature' => 'New Registrations',
                'message' => Features::comingSoonMessage(),
            ]);
        }

        return view('auth.register');
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

        $user->assignRole('user');

        $this->onboarding->provisionWallets($user);

        ActivityLog::record($user->id, 'registered', ['username' => $user->username]);

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect(route('dashboard', absolute: false));
    }
}
