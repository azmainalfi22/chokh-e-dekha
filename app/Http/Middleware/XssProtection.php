<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XssProtection
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sanitize all input data
        $input = $request->all();
        
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                // Remove potentially dangerous characters
                $value = $this->sanitize($value);
            }
        });
        
        $request->merge($input);
        
        return $next($request);
    }

    /**
     * Sanitize string value
     */
    private function sanitize(string $value): string
    {
        // Don't sanitize passwords or hashed values
        if (strlen($value) > 50 && preg_match('/^[a-f0-9]{32,}$/i', $value)) {
            return $value;
        }

        // Remove null bytes
        $value = str_replace(chr(0), '', $value);
        
        // Remove control characters except newlines and tabs
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
        
        // Decode HTML entities that might be used for XSS
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $value;
    }
}
