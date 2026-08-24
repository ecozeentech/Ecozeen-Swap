<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Models\UserTrustedIp;
use App\Notifications\NewIpDetected;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Suspicious-activity guard: trading routes are blocked until the user
 * confirms, via a link emailed to them, that a new IP address is trusted.
 */
class EnsureIpIsTrusted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $ip = $request->ip();

        $trusted = UserTrustedIp::query()
            ->where('user_id', $user->id)
            ->where('ip_address', $ip)
            ->first();

        if ($trusted && $trusted->isVerified()) {
            return $next($request);
        }

        if (! $trusted) {
            $trusted = UserTrustedIp::create([
                'user_id' => $user->id,
                'ip_address' => $ip,
                'verification_code' => (string) random_int(100000, 999999),
            ]);

            $user->notify(new NewIpDetected($trusted));

            ActivityLog::record($user->id, 'new_ip_detected', ['ip' => $ip]);
        }

        return response()->view('security.verify-ip', [
            'ip' => $ip,
            'trustedIp' => $trusted,
        ], 200);
    }
}
