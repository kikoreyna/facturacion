@extends('layouts.app')
@section('title', 'Editar '.$order->number)

@section('content')
    <h1 class="mb-6 text-2xl font-bold">Editar orden {{ $order->number }}</h1>
    @if ($errors->any())
        <div class="mb-4 max-w-3xl rounded-md bg-rose-50 p-3 text-sm text-rose-700">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="POST" action="{{ route('service-orders.update', $order) }}" class="max-w-3xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
        @csrf @method('PUT')
        <p class="text-sm text-slate-500">Cliente: {{ $order->client->name }}</p>
        <div>
            <label class="mb-1 block text-sm font-medium">Título</label>
            <input name="title" value="{{ old('title', $order->title) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Descripción</label>
            <textarea name="description" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('description', $order->description) }}</textarea>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Fecha agendada</label>
            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $order->scheduled_at?->format('Y-m-d\TH:i')) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Servicios</label>
            @forelse ($order->items as $item)
                <div class="mb-2 flex items-center gap-2 text-sm">
                    <span class="flex-1">{{ $item->service->name }}
                        <span class="text-slate-400">({{ \App\Support\Currency::format($item->unit_price, $item->currency) }} c/u)</span></span>
                    <input name="items[{{ $item->id }}][quantity]" type="number" step="0.01" min="0.01" value="{{ old("items.{$item->id}.quantity", $item->quantity) }}" class="w-24 rounded-md border border-slate-300 px-2 py-2 text-sm">
                    <label class="flex items-center gap-1 text-rose-600"><input type="checkbox" name="items[{{ $item->id }}][remove]" value="1"> Quitar</label>
                </div>
            @empty
                <p class="text-sm text-slate-400">Sin servicios. Agrégalos desde el detalle de la orden.</p>
            @endforelse
        </div>

        <div class="flex gap-3">
            <button class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">Guardar cambios</button>
            <a href="{{ route('service-orders.show', $order) }}" class="px-4 py-2 text-sm text-slate-600">Cancelar</a>
        </div>
    </form>
@endsection
