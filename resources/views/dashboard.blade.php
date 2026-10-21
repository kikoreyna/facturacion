@extends('layouts.app')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">Dashboard</h1>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Clientes activos', $stats['clients'], 'text-sky-600'],
            ['Órdenes abiertas', $stats['open_orders'], 'text-amber-600'],
            ['Pendientes de facturar', $stats['pending_invoice'], 'text-rose-600'],
            ['Facturado este mes', $stats['invoiced_month']->isEmpty() ? '$0.00' : $stats['invoiced_month']->map(fn ($t, $c) => \App\Support\Currency::format($t, $c))->implode(' · '), 'text-emerald-600'],
        ] as [$label, $value, $color])
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="text-sm text-slate-500">{{ $label }}</div>
                <div class="mt-1 text-2xl font-bold {{ $color }}">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 rounded-xl bg-white p-5 shadow-sm">
        <h2 class="mb-3 font-semibold">Últimas órdenes</h2>
        <table class="w-full text-left text-sm">
            <thead class="text-slate-500">
                <tr><th class="py-2">Número</th><th>Cliente</th><th>Título</th><th>Estado</th></tr>
            </thead>
            <tbody>
                @forelse ($recentOrders as $order)
                    <tr class="border-t">
                        <td class="py-2 font-medium">{{ $order->number }}</td>
                        <td>{{ $order->client->name }}</td>
                        <td>{{ $order->title }}</td>
                        <td><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ $order->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-slate-400">Sin órdenes registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
