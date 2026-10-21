@extends('layouts.app')
@section('title', 'Nueva factura')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">Generar factura</h1>

    <form method="GET" action="{{ route('invoices.create') }}" class="mb-6 flex max-w-xl gap-2">
        <select name="client_id" class="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">Selecciona un cliente…</option>
            @foreach ($clients as $c)<option value="{{ $c->id }}" @selected($clientId == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
    </form>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-rose-50 p-3 text-sm text-rose-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    @if ($clientId)
        <form method="POST" action="{{ route('invoices.store') }}" class="space-y-4 rounded-xl bg-white p-6 shadow-sm">
            @csrf
            <input type="hidden" name="client_id" value="{{ $clientId }}">
            <h2 class="font-semibold">Órdenes completadas pendientes de facturar</h2>
            <table class="w-full text-left text-sm">
                <thead class="text-slate-500"><tr><th class="w-8"></th><th>Orden</th><th>Título</th><th>Completada</th><th class="text-right">Total</th></tr></thead>
                <tbody>
                @forelse ($orders as $o)
                    <tr class="border-t">
                        <td class="py-2"><input type="checkbox" name="order_ids[]" value="{{ $o->id }}" checked></td>
                        <td class="font-medium"><a class="text-sky-600 hover:underline" href="{{ route('service-orders.show', $o) }}">{{ $o->number }}</a></td>
                        <td>{{ $o->title }}</td>
                        <td>{{ $o->completed_at?->format('d/m/Y') }}</td>
                        <td class="text-right">{{ \App\Support\Currency::format($o->items->sum('subtotal'), $o->currency) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-slate-400">Este cliente no tiene órdenes completadas por facturar.</td></tr>
                @endforelse
                </tbody>
            </table>

            @if ($orders->isNotEmpty())
                <div class="grid max-w-xl gap-4 sm:grid-cols-2">
                    <div><label class="mb-1 block text-sm font-medium">IVA (%)</label>
                        <input name="tax_rate" type="number" step="0.01" value="{{ old('tax_rate', 16) }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="mb-1 block text-sm font-medium">Días de vencimiento</label>
                        <input name="due_days" type="number" value="{{ old('due_days', 30) }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"></div>
                </div>
                <div><label class="mb-1 block text-sm font-medium">Notas</label>
                    <textarea name="notes" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('notes') }}</textarea></div>
                <button class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">Generar factura</button>
            @endif
        </form>
    @endif
@endsection
