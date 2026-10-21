@extends('layouts.app')
@section('title', 'Órdenes')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Órdenes de servicio</h1>
        <a href="{{ route('service-orders.create') }}" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">+ Nueva orden</a>
    </div>
    <div class="rounded-xl bg-white p-5 shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="text-slate-500"><tr><th class="py-2">Número</th><th>Cliente</th><th>Título</th><th>Estado</th></tr></thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t">
                        <td class="py-2 font-medium"><a class="text-sky-600 hover:underline" href="{{ route('service-orders.show', $order) }}">{{ $order->number }}</a></td>
                        <td>{{ $order->client->name }}</td>
                        <td>{{ $order->title }}</td>
                        <td><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ $order->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-slate-400">Sin órdenes registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>
@endsection
