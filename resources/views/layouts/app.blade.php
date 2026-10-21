<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · XISA Tech</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 font-sans antialiased">
    @php
        $links = [
            ['Dashboard', 'dashboard', 'dashboard'],
            ['Clientes', 'clients.index', 'clients.*'],
            ['Servicios', 'services.index', 'services.*'],
            ['Tabuladores', 'tariff-lists.index', 'tariff-lists.*'],
            ['Órdenes', 'service-orders.index', 'service-orders.*'],
            ['Facturas', 'invoices.index', 'invoices.*'],
        ];
    @endphp

    <nav class="bg-slate-900 text-white shadow">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
            <a href="{{ route('dashboard') }}" class="text-lg font-bold tracking-tight">
                XISA <span class="text-sky-400">Tech</span>
            </a>

            <div class="hidden gap-1 md:flex">
                @foreach ($links as [$label, $route, $pattern])
                    <a href="{{ route($route) }}"
                       class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($pattern) ? 'bg-sky-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <button type="button" class="md:hidden rounded p-2 hover:bg-slate-800" aria-label="Menú"
                    onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <div id="mobile-menu" class="hidden border-t border-slate-800 px-4 py-2 md:hidden">
            @foreach ($links as [$label, $route, $pattern])
                <a href="{{ route($route) }}"
                   class="block rounded-md px-3 py-2 text-sm {{ request()->routeIs($pattern) ? 'bg-sky-600' : 'text-slate-300 hover:bg-slate-800' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>

