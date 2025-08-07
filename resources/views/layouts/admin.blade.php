<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Chokh-e-Dekha</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 text-gray-800">
    <!-- Navigation -->
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <div class="text-lg font-semibold text-blue-600">🛠 Admin Dashboard</div>
        <div class="flex items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline">User Dashboard</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-red-500 hover:underline">Logout</button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow h-screen p-4 hidden md:block">
            <ul class="space-y-4">
                <li><a href="{{ route('admin.dashboard') }}" class="text-blue-700 font-semibold">Dashboard</a></li>
                <li><a href="{{ route('admin.reports.index') }}" class="text-gray-600 hover:text-blue-500">All Reports</a></li>
                <li><a href="{{ route('reports.index') }}" class="text-gray-600 hover:text-blue-500">User View</a></li>
            </ul>
        </aside>

        <!-- Dashboard Content -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
