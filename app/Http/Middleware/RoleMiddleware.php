<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  ...$roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Check if user has any of the required roles
        if (!$this->hasAnyRole($user, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient privileges. Required roles: ' . implode(', ', $roles)
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Access denied. Required role: ' . implode(' or ', $roles));
        }

        return $next($request);
    }

    /**
     * Check if user has any of the required roles
     *
     * @param  mixed  $user
     * @param  array  $roles
     * @return bool
     */
    private function hasAnyRole($user, array $roles): bool
    {
        if (!$user->role) {
            return false;
        }

        return in_array($user->role->name, $roles);
    }
}