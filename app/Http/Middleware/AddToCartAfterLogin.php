<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Cart;

class AddToCartAfterLogin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && session()->has('add_to_cart')) {
            $productId = session('add_to_cart');

            Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $productId,
                'quantity' => 1
            ]);

            session()->forget('add_to_cart');
        }

        return $next($request);
    }
}

