<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login'));
        }

        $user = Auth::user();

        // If 'staff' is passed, allow any staff role
        if (in_array('staff', $roles, true) && $user->isStaff()) {
            return $next($request);
        }

        // Admin always has bypass privilege across all ERP routes
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user has any of the requested roles
        if ($user->hasRole($roles)) {
            return $next($request);
        }

        abort(403, 'Access denied: You do not have permission to access this area.');
    }
}
