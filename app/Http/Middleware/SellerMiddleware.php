<?php
// app/Http/Middleware/SellerMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Load role relationship jika belum dimuat
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        // Check apakah user adalah seller atau admin
        if (!$user->canManageProducts()) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda harus menjadi seller untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}