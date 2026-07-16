<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request and set the application locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for locale in session, query string, or default to 'en'
        $locale = $request->query('lang') 
            ?? Session::get('locale') 
            ?? $request->cookie('locale') 
            ?? 'en';

        // Validate locale
        if (!in_array($locale, ['en', 'bn'])) {
            $locale = 'en';
        }

        // Set the locale
        App::setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }
}

