<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            abort(403, 'Unauthorized.');
        }

        $user = Auth::user();

        $userRole = $user->role;

        // Convert the role string to the enum instance if needed for strict comparison
        $userRoleEnum = UserRole::tryFrom($userRole);

        // Check if the user's role is in the list of allowed roles
        if (!in_array($userRoleEnum?->value, $roles)) {
            abort(403, 'Insufficient permissions.');
        }

        return $next($request);
    }
}
