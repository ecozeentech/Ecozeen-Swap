<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\UserTrustedIp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IpVerificationController extends Controller
{
    public function verify(Request $request, UserTrustedIp $trustedIp): RedirectResponse
    {
        $code = $request->input('code');

        if ($trustedIp->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($trustedIp->verification_code !== $code) {
            return redirect()->back()->withErrors(['code' => 'The verification code is incorrect.']);
        }

        $trustedIp->update(['verified_at' => now()]);

        ActivityLog::record($trustedIp->user_id, 'ip_verified', ['ip' => $trustedIp->ip_address]);

        return redirect()->route('dashboard')->with('status', 'ip-verified');
    }
}
