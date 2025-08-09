@extends('layouts.guest')

@section('slot')
<form method="POST" action="{{ route('register') }}" class="space-y-5" novalidate>
    @csrf

    {{-- Error summary --}}
    @if ($errors->any())
        <div class="rounded-xl px-4 py-3 bg-rose-50 text-rose-800 ring-1 ring-rose-200 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Full Name --}}
    <div class="space-y-2">
        <label for="name" class="text-sm font-medium text-amber-900/80">Full Name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
               class="w-full rounded-xl border-0 ring-1 ring-amber-900/10 focus:ring-2 focus:ring-rose-400/60
                      bg-white/80 backdrop-blur px-3 py-2.5 shadow-inner placeholder:text-amber-900/40">
        @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Email --}}
    <div class="space-y-2">
        <label for="email" class="text-sm font-medium text-amber-900/80">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
               class="w-full rounded-xl border-0 ring-1 ring-amber-900/10 focus:ring-2 focus:ring-rose-400/60
                      bg-white/80 backdrop-blur px-3 py-2.5 shadow-inner placeholder:text-amber-900/40">
        <p class="text-xs text-amber-900/60">
            Use <span class="font-medium">@chokh.e-dekha.com</span> to be auto‑approved as admin.
        </p>
        @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Address --}}
    <div class="space-y-2">
        <label for="address" class="text-sm font-medium text-amber-900/80">Address</label>
        <input id="address" type="text" name="address" value="{{ old('address') }}" required autocomplete="street-address"
               class="w-full rounded-xl border-0 ring-1 ring-amber-900/10 focus:ring-2 focus:ring-rose-400/60
                      bg-white/80 backdrop-blur px-3 py-2.5 shadow-inner placeholder:text-amber-900/40">
        @error('address') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Password --}}
    <div class="space-y-2">
        <label for="password" class="text-sm font-medium text-amber-900/80">Password</label>
        <div class="relative">
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="w-full rounded-xl border-0 ring-1 ring-amber-900/10 focus:ring-2 focus:ring-rose-400/60
                          bg-white/80 backdrop-blur px-3 py-2.5 shadow-inner pr-11 placeholder:text-amber-900/40">
            <button type="button" id="togglePass"
                    class="absolute inset-y-0 right-0 px-3 text-amber-900/60 hover:text-amber-900/90">
                👁️
            </button>
        </div>
        <div id="pwHint" class="text-xs text-amber-900/60">At least 8 characters.</div>
        @error('password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Confirm Password --}}
    <div class="space-y-2">
        <label for="password_confirmation" class="text-sm font-medium text-amber-900/80">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
               class="w-full rounded-xl border-0 ring-1 ring-amber-900/10 focus:ring-2 focus:ring-rose-400/60
                      bg-white/80 backdrop-blur px-3 py-2.5 shadow-inner placeholder:text-amber-900/40">
        <div id="matchHint" class="text-xs text-amber-900/60"></div>
    </div>

    {{-- Terms --}}
    <div class="space-y-1">
        <label class="flex items-start gap-2 text-sm text-amber-900/80">
            <input type="checkbox" name="terms" value="1" id="terms"
                   class="mt-1 rounded-md border-amber-200 text-rose-600 focus:ring-rose-400"
                   {{ old('terms') ? 'checked' : '' }}>
            <span>I agree to the <a href="{{ url('/terms') }}" class="text-rose-700 underline">Terms</a> &amp;
                  <a href="{{ url('/privacy') }}" class="text-rose-700 underline">Privacy Policy</a></span>
        </label>
        @error('terms') <p class="text-xs text-rose-600" id="termsError">{{ $message }}</p> @enderror
        <p class="text-xs text-amber-900/60" id="termsHint" style="display:none;">Please accept the Terms & Privacy Policy to continue.</p>
    </div>

    <button id="submitBtn" class="w-full inline-flex justify-center px-4 py-2.5 rounded-xl
                   bg-gradient-to-r from-amber-600 to-rose-600 text-white font-semibold
                   shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 focus:outline-none
                   focus-visible:ring-2 focus-visible:ring-rose-400/70 disabled:opacity-60 disabled:cursor-not-allowed"
            {{ old('terms') ? '' : 'disabled' }}>
        Create Account
    </button>

    <p class="text-center text-sm text-amber-900/70">
        Already have an account?
        <a href="{{ route('login') }}" class="text-rose-700 hover:underline">Sign in</a>
    </p>
</form>

{{-- tiny helpers --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const pass = document.getElementById('password');
    const pass2 = document.getElementById('password_confirmation');
    const hint = document.getElementById('pwHint');
    const match = document.getElementById('matchHint');
    const toggle = document.getElementById('togglePass');

    const terms = document.getElementById('terms');
    const submitBtn = document.getElementById('submitBtn');
    const termsHint = document.getElementById('termsHint');

    // Show/hide password
    toggle?.addEventListener('click', () => {
        pass.type = pass.type === 'password' ? 'text' : 'password';
        toggle.textContent = pass.type === 'password' ? '👁️' : '🙈';
    });

    // Password hints
    const check = () => {
        const ok = (pass.value || '').length >= 8;
        hint.textContent = ok ? 'Looks good.' : 'At least 8 characters.';
        hint.className = 'text-xs ' + (ok ? 'text-emerald-700' : 'text-amber-900/60');

        const m = pass.value && pass.value === pass2.value;
        match.textContent = pass2.value ? (m ? 'Passwords match.' : 'Passwords do not match.') : '';
        match.className = 'text-xs ' + (m ? 'text-emerald-700' : 'text-amber-900/60');
    };
    pass.addEventListener('input', check);
    pass2.addEventListener('input', check);

    // Terms UX: disable submit until checked
    const syncTerms = () => {
        submitBtn.disabled = !terms.checked;
        termsHint.style.display = terms.checked ? 'none' : 'block';
    };
    terms.addEventListener('change', syncTerms);
    syncTerms(); // init
});
</script>
@endsection
