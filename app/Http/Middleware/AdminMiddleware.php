<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            // Option 1: Show 403 error
            //abort(403, 'Unauthorized access');

            // Option 2: Redirect to dashboard (uncomment to use)
             return redirect()->route('dashboard')->with('error', 'You are not authorized to access admin pages.');
        }

        return $next($request);
    }
}
