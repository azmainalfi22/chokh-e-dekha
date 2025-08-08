@extends('layouts.guest')

@section('slot')
<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf
    {{-- Email --}}
    <div class="space-y-2">
        <label for="email" class="text-sm font-medium text-amber-900/80">Email</label>
        <input id="email" type="email" name="email"
               class="w-full rounded-xl border-0 ring-1 ring-amber-900/10 focus:ring-2 focus:ring-rose-400/60
                      bg-white/80 backdrop-blur px-3 py-2.5 shadow-inner placeholder:text-amber-900/40">
    </div>
    {{-- Password --}}
    <div class="space-y-2">
        <label for="password" class="text-sm font-medium text-amber-900/80">Password</label>
        <input id="password" type="password" name="password"
               class="w-full rounded-xl border-0 ring-1 ring-amber-900/10 focus:ring-2 focus:ring-rose-400/60
                      bg-white/80 backdrop-blur px-3 py-2.5 shadow-inner placeholder:text-amber-900/40">
    </div>
    {{-- Button --}}
    <button class="w-full inline-flex justify-center px-4 py-2.5 rounded-xl
                   bg-gradient-to-r from-amber-600 to-rose-600 text-white font-semibold
                   shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5">
        Sign In
    </button>
</form>
@endsection
