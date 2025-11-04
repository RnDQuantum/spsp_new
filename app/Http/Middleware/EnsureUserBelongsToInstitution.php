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

        // Check if user has institution_id
        if (! $user->institution_id) {
            abort(403, 'User is not assigned to any institution.');
        }

        return $next($request);
    }
}
