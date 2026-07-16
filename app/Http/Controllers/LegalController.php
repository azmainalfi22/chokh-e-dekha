<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalController extends Controller
{
    public function rtiForm()
    {
        return view('legal.rti');
    }

    public function rtiGenerate(Request $request)
    {
        $data = $request->validate([
            'applicant_name' => ['required','string','max:255'],
            'address' => ['required','string','max:500'],
            'authority' => ['required','string','max:255'],
            'subject' => ['required','string','max:255'],
            'facts' => ['required','string'],
            'reliefs' => ['required','string'],
            'attachments' => ['nullable','string','max:500'],
            'language' => ['required','in:bn,en'],
        ]);

        return view('legal.rti_preview', ['form' => $data]);
    }
}


