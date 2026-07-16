@extends('layouts.app')
@section('title', 'RTI Request Wizard')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  {{-- Header --}}
  <div class="mb-8">
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-2">
      <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition-colors">Dashboard</a>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      <span>RTI Wizard</span>
    </div>
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Right to Information (RTI) Request</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Generate a legally compliant RTI application in Bangla or English. Takes 2 minutes.</p>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main form --}}
    <div class="lg:col-span-2">
      <form method="POST" action="{{ route('legal.rti.generate') }}" id="rtiForm">
        @csrf

        {{-- Step 1: Template --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-5">
          <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold mr-2">1</span>
            Choose a Template
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="templateGrid">
            @php
            $templates = [
              ['id'=>'road', 'icon'=>'M8 17l4 4 4-4m-4-5v9M20.88 18.09A5 5 0 0018 9h-1.26A8 8 0 103 16.29', 'label'=>'Road & Pothole', 'subject'=>'Information regarding road maintenance and repair status in [ward/area]', 'facts'=>'I am a resident of [your area]. The road/footpath at [location] has been in poor condition for [duration], causing accidents and disruption to daily life. Despite repeated complaints, no action has been taken.', 'reliefs'=>"1. Total budget allocated for road repair in this ward for the current fiscal year.\n2. Names and contact of the responsible engineer.\n3. Planned repair schedule and timeline.\n4. Records of previous complaints filed about this road."],
              ['id'=>'water', 'icon'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'label'=>'Water & Drainage', 'subject'=>'Information regarding water supply and drainage infrastructure at [location]', 'facts'=>'The water supply / drainage system at [your area] has been non-functional for [duration]. This has caused waterlogging, health hazards, and inconvenience to residents.', 'reliefs'=>"1. Status of the water/drainage project at [location].\n2. Contractor details and work order number.\n3. Completion deadline and current progress percentage.\n4. Budget sanctioned and amount spent to date."],
              ['id'=>'corruption', 'icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label'=>'Corruption / Bribe', 'subject'=>'Information regarding alleged corrupt practices at [office/department]', 'facts'=>'I or others have been asked to pay unofficial fees/bribes at [department/office] while seeking [service]. This is a violation of the service rules and anti-corruption laws of Bangladesh.', 'reliefs'=>"1. Service delivery SLA and official fee structure for [service].\n2. Records of complaints received by the department.\n3. Action taken against officers accused of misconduct.\n4. Name of the designated Complaint Officer."],
              ['id'=>'permit', 'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label'=>'Permits & Licenses', 'subject'=>'Information regarding status of application/permit no. [number] submitted on [date]', 'facts'=>'I submitted an application for [permit/license] on [date] with reference number [number]. Despite the prescribed timeline, no decision has been communicated.', 'reliefs'=>"1. Current status of my application.\n2. Name of the officer responsible for processing.\n3. Any deficiencies or objections pending from my side.\n4. Expected decision date."],
              ['id'=>'environment', 'icon'=>'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label'=>'Environment / Pollution', 'subject'=>'Information regarding environmental clearance and pollution control measures at [factory/site]', 'facts'=>'A factory/construction site at [location] is causing [noise/air/water] pollution affecting residents. I wish to know whether proper environmental clearance has been obtained.', 'reliefs'=>"1. Copy of environmental clearance certificate for the facility.\n2. Last inspection date and findings.\n3. Complaints received and action taken.\n4. Contact details of the responsible environmental officer."],
              ['id'=>'custom', 'icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'label'=>'Custom Request', 'subject'=>'', 'facts'=>'', 'reliefs'=>''],
            ];
            @endphp
            @foreach($templates as $t)
              <button type="button"
                      class="template-card text-left p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-emerald-400 dark:hover:border-emerald-600 transition-all group"
                      data-id="{{ $t['id'] }}"
                      data-subject="{{ $t['subject'] }}"
                      data-facts="{{ $t['facts'] }}"
                      data-reliefs="{{ $t['reliefs'] }}">
                <div class="flex items-start gap-3">
                  <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-700 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/40 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-slate-500 dark:text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/></svg>
                  </div>
                  <div>
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-emerald-700 dark:group-hover:text-emerald-400">{{ $t['label'] }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ $t['id'] === 'custom' ? 'Write your own from scratch' : 'Pre-filled template' }}</div>
                  </div>
                </div>
              </button>
            @endforeach
          </div>
        </div>

        {{-- Step 2: Your Details --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-5">
          <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold mr-2">2</span>
            Applicant Details
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Full Name <span class="text-red-500">*</span></label>
              <input type="text" name="applicant_name"
                     value="{{ old('applicant_name', auth()->user()->name ?? '') }}"
                     class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition"
                     required>
              @error('applicant_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">NID / Passport No. <span class="text-slate-400 font-normal">(optional)</span></label>
              <input type="text" name="nid"
                     value="{{ old('nid') }}"
                     placeholder="e.g., 1234567890123"
                     class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
            </div>
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Address <span class="text-red-500">*</span></label>
              <textarea name="address" rows="2" required
                        class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition resize-none">{{ old('address', auth()->user()->address ?? '') }}</textarea>
              @error('address')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
          </div>
        </div>

        {{-- Step 3: Authority & Subject --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-5">
          <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold mr-2">3</span>
            Authority & Subject
          </h2>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Public Authority <span class="text-red-500">*</span></label>
            <div class="relative">
              <input type="text" name="authority" id="authority"
                     value="{{ old('authority') }}"
                     placeholder="e.g., Dhaka North City Corporation" required
                     class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition"
                     autocomplete="off">
              @error('authority')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            {{-- Common authority presets --}}
            <div class="mt-2 flex flex-wrap gap-1.5">
              @foreach(['Dhaka North City Corporation','Dhaka South City Corporation','Rajuk','WASA','BRTC','Department of Environment','Bangladesh Police','Ministry of LGRD'] as $auth)
                <button type="button" onclick="document.getElementById('authority').value='{{ $auth }}'"
                        class="px-2.5 py-1 text-xs rounded-full border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 transition-colors">
                  {{ $auth }}
                </button>
              @endforeach
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Subject of Request <span class="text-red-500">*</span></label>
            <input type="text" name="subject" id="subject"
                   value="{{ old('subject') }}"
                   placeholder="Brief subject line for your RTI request" required
                   class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
            @error('subject')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
          </div>
        </div>

        {{-- Step 4: Facts & Reliefs --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-5">
          <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold mr-2">4</span>
            Facts & Information Requested
          </h2>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
              Statement of Facts <span class="text-red-500">*</span>
              <span class="text-xs font-normal text-slate-400 ml-1">— Describe the issue clearly</span>
            </label>
            <textarea name="facts" id="facts" rows="5" required
                      placeholder="Describe the situation, what happened, and any relevant dates or reference numbers..."
                      class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">{{ old('facts') }}</textarea>
            @error('facts')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
              Information / Reliefs Requested <span class="text-red-500">*</span>
              <span class="text-xs font-normal text-slate-400 ml-1">— Be specific and numbered</span>
            </label>
            <textarea name="reliefs" id="reliefs" rows="5" required
                      placeholder="1. Please provide...\n2. Please disclose...\n3. Please share..."
                      class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">{{ old('reliefs') }}</textarea>
            @error('reliefs')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
              Supporting Documents <span class="text-slate-400 font-normal">(optional)</span>
            </label>
            <input type="text" name="attachments" value="{{ old('attachments') }}"
                   placeholder="e.g., Photos of the damaged road, previous complaint reference no."
                   class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
          </div>
        </div>

        {{-- Step 5: Language & Submit --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
          <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold mr-2">5</span>
            Language & Generate
          </h2>

          <div class="flex gap-3 mb-5">
            <label class="flex-1 flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 dark:has-[:checked]:bg-emerald-900/20 transition-all">
              <input type="radio" name="language" value="en" class="accent-emerald-600" checked>
              <div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">English</div>
                <div class="text-xs text-slate-400">Standard RTI application</div>
              </div>
            </label>
            <label class="flex-1 flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 dark:has-[:checked]:bg-emerald-900/20 transition-all">
              <input type="radio" name="language" value="bn" class="accent-emerald-600">
              <div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">বাংলা</div>
                <div class="text-xs text-slate-400">সরকারি দফতরের জন্য</div>
              </div>
            </label>
          </div>

          <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              Generate RTI Letter
            </button>
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center justify-center px-5 py-3 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
              Cancel
            </a>
          </div>
        </div>

      </form>
    </div>

    {{-- Sidebar guide --}}
    <div class="space-y-5">

      {{-- What is RTI --}}
      <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800 p-5">
        <div class="flex items-center gap-2 mb-3">
          <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <h3 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">What is RTI?</h3>
        </div>
        <p class="text-xs text-emerald-700 dark:text-emerald-400 leading-relaxed">
          Under the <strong>Right to Information Act, 2009</strong> (তথ্য অধিকার আইন), every citizen has the right to request information from any public authority. Authorities must respond within <strong>20 working days</strong>.
        </p>
      </div>

      {{-- Tips --}}
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">Writing Tips</h3>
        <ul class="space-y-2">
          @foreach([
            'Be specific — mention dates, locations, and reference numbers',
            'Number each piece of information you request',
            'Do not ask for interpretation, only facts and documents',
            'Keep it concise — 1-2 pages is ideal',
            'You may attach photo evidence or prior complaint receipts',
          ] as $tip)
            <li class="flex items-start gap-2 text-xs text-slate-500 dark:text-slate-400">
              <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              {{ $tip }}
            </li>
          @endforeach
        </ul>
      </div>

      {{-- Next steps --}}
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">After You Generate</h3>
        <ol class="space-y-3">
          @foreach([
            ['n'=>'1','t'=>'Print or save as PDF','d'=>'Use the print button on the preview page'],
            ['n'=>'2','t'=>'Submit in person or by post','d'=>'Deliver to the Designated Information Officer of the authority'],
            ['n'=>'3','t'=>'Keep the receipt','d'=>'Ask for an acknowledgment slip with date stamp'],
            ['n'=>'4','t'=>'Follow up','d'=>'If no response in 20 days, file an appeal to the Info Commission'],
          ] as $step)
            <li class="flex items-start gap-3">
              <span class="flex-shrink-0 flex items-center justify-center w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-700 text-xs font-bold text-slate-500 dark:text-slate-400">{{ $step['n'] }}</span>
              <div>
                <div class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $step['t'] }}</div>
                <div class="text-xs text-slate-400">{{ $step['d'] }}</div>
              </div>
            </li>
          @endforeach
        </ol>
      </div>

      {{-- Info Commission --}}
      <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 p-4 text-xs text-slate-500 dark:text-slate-400">
        <strong class="text-slate-700 dark:text-slate-300">Bangladesh Information Commission</strong><br>
        📞 16136 (Toll-free)<br>
        🌐 <a href="http://www.infocom.gov.bd" target="_blank" rel="noopener" class="text-emerald-600 hover:underline">infocom.gov.bd</a>
      </div>

    </div>
  </div>
</div>

@push('scripts')
<script>
(function () {
  const cards   = document.querySelectorAll('.template-card');
  const subject = document.getElementById('subject');
  const facts   = document.getElementById('facts');
  const reliefs = document.getElementById('reliefs');

  cards.forEach(card => {
    card.addEventListener('click', () => {
      // Deselect all
      cards.forEach(c => {
        c.classList.remove('border-emerald-500','bg-emerald-50','dark:bg-emerald-900/20');
        c.classList.add('border-slate-200','dark:border-slate-700');
      });
      // Select clicked
      card.classList.remove('border-slate-200','dark:border-slate-700');
      card.classList.add('border-emerald-500','bg-emerald-50');

      // Fill fields
      if (subject) subject.value = card.dataset.subject || '';
      if (facts)   facts.value   = card.dataset.facts   || '';
      if (reliefs) reliefs.value = card.dataset.reliefs  || '';

      // Scroll to step 3
      document.querySelector('[name="authority"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  });
})();
</script>
@endpush

@endsection
