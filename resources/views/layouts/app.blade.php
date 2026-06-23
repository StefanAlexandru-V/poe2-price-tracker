<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PoE2 Price Tracker')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { poe: { gold: '#af6025', dark: '#0c0c0e', darker: '#060607' } } } }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    @stack('head')
</head>
<body class="bg-poe-dark text-gray-200 min-h-screen">
    <nav class="border-b border-gray-800 px-4 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="/" class="text-poe-gold font-bold text-lg">PoE2 Prices</a>
            <div class="flex items-center gap-4 text-sm">
                @if(isset($leagues))
                <form method="GET" action="/" class="flex items-center gap-2">
                    <select name="league" onchange="this.form.submit()" class="bg-gray-900 border border-gray-700 rounded px-2 py-1 text-sm">
                        @foreach($leagues as $l)
                        <option value="{{ $l->slug }}" @selected($l->id === $league->id)>{{ $l->name }}</option>
                        @endforeach
                    </select>
                </form>
                @endif
                @auth
                    <span class="text-gray-400">{{ auth()->user()->name }}</span>
                    <a href="{{ route('alerts.index') }}" class="text-gray-400 hover:text-white">Alerts</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button class="text-gray-500 hover:text-white">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-400 hover:text-white">Login</a>
                    <a href="{{ route('register') }}" class="text-gray-400 hover:text-white">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    <footer class="border-t border-gray-800 mt-12 py-4 text-center text-xs text-gray-600">
        Data from <a href="https://poe.ninja" class="text-gray-500 hover:text-gray-300">poe.ninja</a>. Not affiliated with GGG.
    </footer>

    @stack('scripts')
</body>
</html>
