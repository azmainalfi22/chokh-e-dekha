@extends('layouts.app')
@section('title', 'RTI Draft — Preview')

@push('styles')
<style>
  @media print {
    header, nav, footer, .no-print { display: none !important; }
    body { background: white !important; }
    .print-page { margin: 0; padding: 0; box-shadow: none !important; border: none !important; border-radius: 0 !important; }
    .print-only { display: block !important; }
  }
  .print-only { display: none; }
  @page { margin: 2cm; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  {{-- Toolbar (no-print) --}}
  <div class="no-print flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
      <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-1">
        <a href="{{ route('legal.rti.form') }}" class="hover:text-emerald-600 transition-colors">RTI Wizard</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span>Preview</span>
      </div>
      <h1 class="text-xl font-bold text-slate-900 dark:text-white">Your RTI Letter — Ready to Print</h1>
    </div>
    <div class="flex flex-wrap gap-2">
      <button onclick="window.print()"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Print / Save PDF
      </button>
      <a href="{{ route('legal.rti.form') }}"
         class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
        Edit Letter
      </a>
    </div>
  </div>

  {{-- Print notice (no-print) --}}
  <div class="no-print mb-5 flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 text-sm text-amber-800 dark:text-amber-300">
    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div>
      <strong>Review before printing:</strong> Replace any placeholder text in <span class="font-mono bg-amber-100 dark:bg-amber-900/40 px-1 rounded">[brackets]</span> with your actual details. Sign the letter by hand after printing.
    </div>
  </div>

  {{-- The Letter --}}
  <div class="print-page bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">

    {{-- Letter header --}}
    <div class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-8 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-lg">চ</div>
        <div>
          <div class="text-sm font-bold text-slate-800 dark:text-slate-200">Chokh-e-Dekha — RTI Draft</div>
          <div class="text-xs text-slate-400">Generated {{ now()->format('d M Y, g:i A') }}</div>
        </div>
      </div>
      <div class="text-xs px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 font-semibold">
        {{ strtoupper($form['language'] === 'bn' ? 'Bengali' : 'English') }}
      </div>
    </div>

    {{-- Letter body --}}
    <div class="px-10 py-10 text-slate-800 dark:text-slate-200" style="font-family: 'Noto Serif', 'Noto Serif Bengali', Georgia, serif; line-height: 1.9; font-size: 15px;">

      @if($form['language'] === 'bn')
        {{-- BANGLA LETTER --}}
        <div class="text-right mb-8 text-sm">
          <p>তারিখ: {{ now()->format('d/m/Y') }}</p>
          <p class="mt-1 text-slate-600 dark:text-slate-400">{{ $form['applicant_name'] }}</p>
          <p class="text-slate-500 text-xs" style="white-space:pre-line">{{ $form['address'] }}</p>
          @if(!empty($form['nid']))<p class="text-slate-500 text-xs">জাতীয় পরিচয়পত্র নং: {{ $form['nid'] }}</p>@endif
        </div>

        <p class="mb-4">
          <strong>প্রাপক:</strong><br>
          তথ্য প্রদানকারী কর্মকর্তা<br>
          {{ $form['authority'] }}
        </p>

        <p class="mb-6 font-semibold">
          বিষয়: তথ্য অধিকার আইন, ২০০৯ এর অধীনে তথ্য প্রাপ্তির আবেদন — {{ $form['subject'] }}
        </p>

        <p class="mb-4">মহোদয়,</p>

        <p class="mb-4">
          আমি, <strong>{{ $form['applicant_name'] }}</strong>, ঠিকানা: {{ $form['address'] }}, বাংলাদেশের একজন নাগরিক হিসেবে তথ্য অধিকার আইন, ২০০৯ এর ধারা ৮ অনুযায়ী নিম্নলিখিত তথ্য প্রদানের জন্য আবেদন করছি।
        </p>

        <p class="mb-2 font-semibold">ঘটনার বিবরণ:</p>
        <p class="mb-6" style="white-space:pre-line">{{ $form['facts'] }}</p>

        <p class="mb-2 font-semibold">চাহিদাকৃত তথ্য:</p>
        <p class="mb-6" style="white-space:pre-line">{{ $form['reliefs'] }}</p>

        @if(!empty($form['attachments']))
          <p class="mb-4"><strong>সংযুক্তি:</strong> {{ $form['attachments'] }}</p>
        @endif

        <p class="mb-4">
          আইন অনুযায়ী নির্ধারিত সময়সীমার (২০ কার্যদিবস) মধ্যে উল্লিখিত তথ্য সরবরাহের জন্য বিনীত অনুরোধ জানাচ্ছি। প্রযোজ্য ফি পরিশোধে আমি প্রস্তুত আছি।
        </p>

        <div class="mt-12">
          <p>আপনার বিশ্বস্ত,</p>
          <div class="mt-8 border-t border-slate-300 dark:border-slate-600 pt-2 w-48">
            <p class="text-sm">{{ $form['applicant_name'] }}</p>
            <p class="text-xs text-slate-500">তারিখ: ___________</p>
          </div>
        </div>

      @else
        {{-- ENGLISH LETTER --}}
        <div class="text-right mb-8 text-sm">
          <p>Date: {{ now()->format('d F Y') }}</p>
          <p class="mt-1 text-slate-700 dark:text-slate-300"><strong>{{ $form['applicant_name'] }}</strong></p>
          <p class="text-slate-500 text-xs" style="white-space:pre-line">{{ $form['address'] }}</p>
          @if(!empty($form['nid']))<p class="text-slate-500 text-xs">NID No.: {{ $form['nid'] }}</p>@endif
        </div>

        <p class="mb-4">
          <strong>To:</strong><br>
          The Designated Information Officer<br>
          <strong>{{ $form['authority'] }}</strong>
        </p>

        <p class="mb-6 font-semibold border-l-4 border-emerald-500 pl-4">
          Subject: Application for Information under the Right to Information Act, 2009 — {{ $form['subject'] }}
        </p>

        <p class="mb-4">Respected Information Officer,</p>

        <p class="mb-4">
          I, <strong>{{ $form['applicant_name'] }}</strong>, a citizen of Bangladesh residing at {{ $form['address'] }}, hereby submit this application under Section 8 of the Right to Information Act, 2009, requesting the following information from your office.
        </p>

        <p class="mb-2 font-semibold">Statement of Facts:</p>
        <p class="mb-6" style="white-space:pre-line">{{ $form['facts'] }}</p>

        <p class="mb-2 font-semibold">Information / Reliefs Requested:</p>
        <p class="mb-6" style="white-space:pre-line">{{ $form['reliefs'] }}</p>

        @if(!empty($form['attachments']))
          <p class="mb-4"><strong>Supporting Documents Enclosed:</strong> {{ $form['attachments'] }}</p>
        @endif

        <p class="mb-4">
          I respectfully request that the above information be provided within the statutory period of <strong>20 working days</strong> as mandated under the Act. I am willing to pay any applicable fees as prescribed by your authority.
        </p>

        <p class="mb-1">
          If my application is rejected, I request a written statement of grounds in accordance with Section 9 of the Act.
        </p>

        <div class="mt-12">
          <p>Yours faithfully,</p>
          <div class="mt-8 border-t border-slate-300 dark:border-slate-600 pt-2 w-56">
            <p class="text-sm font-semibold">{{ $form['applicant_name'] }}</p>
            <p class="text-xs text-slate-500">Signature & Date: ___________</p>
          </div>
        </div>
      @endif

    </div>

    {{-- Footer watermark --}}
    <div class="px-8 py-3 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 text-xs text-slate-400 flex items-center justify-between">
      <span>Generated by Chokh-e-Dekha RTI Wizard — chokhedekha.gov.bd</span>
      <span>Ref: RTI-{{ strtoupper(substr(md5($form['applicant_name'].now()->format('Ymd')), 0, 8)) }}</span>
    </div>
  </div>

  {{-- Next steps (no-print) --}}
  <div class="no-print mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
    @foreach([
      ['icon'=>'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z', 'title'=>'Print & Submit', 'desc'=>'Print the letter and hand-deliver or post it to the authority. Ask for an acknowledgment stamp.'],
      ['icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'title'=>'Track 20-Day Deadline', 'desc'=>"If no response within 20 working days, you can appeal to the Information Commission at 16136."],
      ['icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title'=>'Also File a Report', 'desc'=>'Track the issue publicly on Chokh-e-Dekha to apply community pressure alongside your RTI.'],
    ] as $step)
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <svg class="w-8 h-8 text-emerald-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/></svg>
        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1">{{ $step['title'] }}</h4>
        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $step['desc'] }}</p>
      </div>
    @endforeach
  </div>

  <div class="no-print mt-4 text-center">
    <a href="{{ route('reports.create') }}" class="inline-flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 font-medium">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Also file a public report about this issue
    </a>
  </div>

</div>
@endsection
