<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToInstitution
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Allow if no user (handled by auth middleware)
        if (! $user) {
            return $next($request);
        }

        // Admin bypass
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Whitelist logout
        if ($request->is('logout') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Check if user has institution_id
        if (! $user->institution_id) {
            abort(403, 'User is not assigned to any institution.');
        }

        // Validate eventCode parameter (e.g. string)
        $eventCode = $request->route('eventCode');
        if ($eventCode) {
            $eventExists = \App\Models\AssessmentEvent::where('code', $eventCode)->exists();
            if (! $eventExists) {
                abort(403, 'Unauthorized access to this event.');
            }
        }

        // Validate event parameter (can be code string or model binding)
        $eventParam = $request->route('event');
        if ($eventParam) {
            $code = $eventParam instanceof \App\Models\AssessmentEvent ? $eventParam->code : $eventParam;
            if (is_string($code)) {
                $eventExists = \App\Models\AssessmentEvent::where('code', $code)->exists();
                if (! $eventExists) {
                    abort(403, 'Unauthorized access to this event.');
                }
            }
        }

        return $next($request);
    }
}
