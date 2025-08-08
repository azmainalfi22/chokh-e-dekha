<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chokh-e-Dekha</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-stone-50 to-amber-50">
    <div class="max-w-4xl mx-auto p-6">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-2xl px-8 py-10">
            <h1 class="text-3xl font-bold text-stone-800">Chokh-e-Dekha</h1>
            <p class="text-stone-500 mt-1">Please sign in to continue</p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center px-5 py-3 rounded-lg bg-indigo-600 text-white font-medium shadow-lg hover:shadow-xl hover:bg-indigo-700 transition">
                   Login
                </a>

                @if (Route::has('register'))
                <a href="{{ route('register') }}"
                   class="inline-flex items-center justify-center px-5 py-3 rounded-lg border border-stone-200 text-stone-700 font-medium bg-white hover:bg-stone-50 shadow hover:shadow-md transition">
                   Register
                </a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
