<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch the application language
     */
    public function switch(Request $request)
    {
        $locale = $request->input('locale', 'en');
        
        // Validate locale
        if (!in_array($locale, ['en', 'bn'])) {
            $locale = 'en';
        }

        // Store in session
        Session::put('locale', $locale);

        // Return to previous page or dashboard
        return redirect()->back()->cookie('locale', $locale, 525600); // 1 year
    }
}

