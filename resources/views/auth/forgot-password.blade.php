@extends('layouts.guest')

@section('slot')
<form method="POST" action="{{ route('password.email') }}" class="space-y-5">
    @csrf

    <div class="space-y-2">
        <label for="email" class="text-sm font-medium text-amber-900/80">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
               class="w-full rounded-xl border-0 ring-1 ring-amber-900/10 focus:ring-2 focus:ring-rose-400/60
                      bg-white/80 backdrop-blur px-3 py-2.5 shadow-inner placeholder:text-amber-900/40" />
        @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    @if (session('status'))
        <div class="rounded-xl px-4 py-3 text-sm bg-amber-50 text-amber-900 ring-1 ring-amber-200">
            {{ session('status') }}
        </div>
    @endif

    <button class="w-full inline-flex justify-center px-4 py-2.5 rounded-xl
                   bg-gradient-to-r from-amber-600 to-rose-600 text-white font-semibold
                   shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 focus:outline-none
                   focus-visible:ring-2 focus-visible:ring-rose-400/70">
        Email Password Reset Link
    </button>

    <p class="text-center text-sm text-amber-900/70">
        <a href="{{ route('login') }}" class="text-rose-700 hover:underline">Back to sign in</a>
    </p>
</form>
@endsection
