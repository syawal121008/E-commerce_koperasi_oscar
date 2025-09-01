<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to access this page.');
        }

        $user = auth()->user();
        $userRole = strtolower($user->role);
        
        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($userRole === strtolower($role)) {
                return $next($request);
            }
        }

        // If user doesn't have required role
        $requiredRoles = implode(', ', array_map('ucfirst', $roles));
        
        if ($request->expectsJson()) {
            return response()->json([
                'paid' => false,
                'message' => "Unauthorized. Required roles: {$requiredRoles}"
            ], 403);
        }

        abort(403, "Unauthorized. You need to be: {$requiredRoles}");
    }
}