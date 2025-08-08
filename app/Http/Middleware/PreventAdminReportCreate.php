<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventAdminReportCreate
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->is_admin) {
            abort(403, 'Admins cannot submit reports.');
            // or: return redirect()->route('admin.dashboard')->with('error','Admins cannot submit reports.');
        }
        return $next($request);
    }
}
