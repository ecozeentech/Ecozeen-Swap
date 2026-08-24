<?php

namespace App\Http\Middleware;

use App\Support\Features;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureEnabled
{
    /**
     * Blocks access to a route when the given feature flag is disabled and
     * instead renders the "Coming Soon" page.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! Features::isEnabled($feature)) {
            return response()->view('coming-soon', [
                'feature' => Features::all()[$feature] ?? 'This feature',
                'message' => Features::comingSoonMessage(),
            ], 200);
        }

        return $next($request);
    }
}
