<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is an admin
        if (auth()->user() && auth()->user()->role != 'admin') {
            // Redirect if user is not an admin
            return redirect()->route('home');
        }

        return $next($request);
    }
}
