<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Dashboard') • Chokh-e-Dekha</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    body { background: linear-gradient(135deg,#f9fafb,#fff7ed); min-height: 100vh; }
    .shell { background: #fff; box-shadow: 0 20px 48px rgba(0,0,0,.08) }
    .link { transition: background .15s ease, transform .15s ease, box-shadow .15s ease }
    .link:hover { background: #f3f4f6; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,.08) }
  </style>
</head>
<body class="text-slate-800">
  <div class="min-h-screen">
    {{-- Top Nav --}}
    <nav class="shell sticky top-0 z-40 px-4 md:px-8 py-3 backdrop-blur">
      <div class="max-w-6xl mx-auto flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="font-bold text-indigo-800">Chokh-e-Dekha</a>

        <div class="hidden md:flex items-center gap-2 text-sm font-medium">
          {{-- Do NOT show admin links here --}}
          <a href="{{ route('reports.index') }}" class="px-3 py-2 rounded-md link {{ request()->routeIs('reports.index') ? 'bg-indigo-50 text-indigo-700' : '' }}">All Reports</a>
          <a href="{{ route('reports.my') }}"    class="px-3 py-2 rounded-md link {{ request()->routeIs('reports.my') ? 'bg-indigo-50 text-indigo-700' : '' }}">My Reports</a>
          <a href="{{ route('report.create') }}" class="px-3 py-2 rounded-md link {{ request()->routeIs('report.create') ? 'bg-indigo-50 text-indigo-700' : '' }}">Create</a>
          <a href="{{ route('profile.edit') }}"  class="px-3 py-2 rounded-md link {{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-700' : '' }}">Profile</a>

          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="ml-2 px-3 py-2 rounded-md bg-rose-50 text-rose-700 hover:bg-rose-100">Logout</button>
          </form>
        </div>
      </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 md:px-8 py-8">
      @yield('content')
    </main>
  </div>
</body>
</html>
@if(auth()->check() && !auth()->user()->is_admin)
   ...user nav links...
@endif
