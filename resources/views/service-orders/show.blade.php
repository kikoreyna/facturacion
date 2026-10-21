@extends('layouts.app')
@section('title', $order->number)

@section('content')
    @php($editable = ! in_array($order->status, ['invoiced', 'cancelled']))
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">{{ $order->number }} — {{ $order->title }}</h1>
            <p class="text-sm text-slate-500">{{ $order->client->name }} · Tabulador: {{ $order->client->tariffList->name }}
                · Estado: <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs">{{ $order->status }}</span>
                @if ($order->invoice) · <a class="text-sky-600 hover:underline" href="{{ route('invoices.show', $order->invoice) }}">{{ $order->invoice->number }}</a> @endif
            </p>
        </div>
        @if ($order->status !== 'invoiced')
            <div class="flex gap-2">
            @if ($editable)
                <a href="{{ route('service-orders.edit', $order) }}" class="rounded-md bg-amber-500 px-3 py-2 text-sm text-white">Editar</a>
            @endif
            <form method="POST" action="{{ route('service-orders.status', $order) }}" class="flex gap-2">
                @csrf @method('PATCH')
                <select name="status" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach (['scheduled' => 'Agendada', 'in_progress' => 'En curso', 'completed' => 'Completada', 'cancelled' => 'Cancelada'] as $v => $l)
                        <option value="{{ $v }}" @selected($order->status === $v)>{{ $l }}</option>
                    @endforeach
                </select>
                <button class="rounded-md bg-slate-800 px-3 py-2 text-sm text-white">Cambiar estado</button>
            </form>
            </div>
        @endif
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-rose-50 p-3 text-sm text-rose-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    @if ($order->description)<p class="mb-4 rounded-xl bg-white p-4 text-sm shadow-sm">{{ $order->description }}</p>@endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="mb-3 font-semibold">Servicios</h2>
            <table class="w-full text-left text-sm">
                <thead class="text-slate-500"><tr><th class="py-1">Servicio</th><th class="text-right">Cant.</th><th class="text-right">P. unit.</th><th class="text-right">Importe</th></tr></thead>
                <tbody>
                @forelse ($order->items as $item)
                    <tr class="border-t">
                        <td class="py-1">{{ $item->service->name }}</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ \App\Support\Currency::format($item->unit_price, $item->currency) }}</td>
                        <td class="text-right">{{ \App\Support\Currency::format($item->subtotal, $item->currency) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-3 text-center text-slate-400">Sin servicios.</td></tr>
                @endforelse
                </tbody>
                <tfoot><tr class="border-t font-semibold"><td colspan="3" class="py-2 text-right">Total</td><td class="text-right">{{ \App\Support\Currency::format($order->items->sum('subtotal'), $order->currency) }}</td></tr></tfoot>
            </table>

            @if ($editable)
                <form method="POST" action="{{ route('service-orders.items.store', $order) }}" class="mt-4 flex gap-2">
                    @csrf
                    <select name="service_id" class="flex-1 rounded-md border border-slate-300 px-2 py-2 text-sm">
                        @foreach ($services as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                    <input name="quantity" type="number" step="0.01" min="0.01" value="1" class="w-20 rounded-md border border-slate-300 px-2 py-2 text-sm">
                    <button class="rounded-md bg-sky-600 px-3 py-2 text-sm text-white">Agregar</button>
                </form>
            @endif
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="mb-3 font-semibold">Bitácora</h2>
            @if ($editable)
                <form method="POST" action="{{ route('service-orders.logs.store', $order) }}" class="mb-4 space-y-2">
                    @csrf
                    <textarea name="body" rows="3" required placeholder="Avance, incidencias, trabajo realizado…" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"></textarea>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="show_on_invoice" value="1"> Mostrar en la factura</label>
                        <button class="rounded-md bg-sky-600 px-3 py-2 text-sm text-white">Registrar</button>
                    </div>
                </form>
            @endif
            <ul class="space-y-3">
                @forelse ($order->logs->sortByDesc('created_at') as $log)
                    <li class="border-l-2 border-sky-400 pl-3 text-sm">
                        <div class="text-xs text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }} @if($log->user) · {{ $log->user->name }} @endif @if($log->show_on_invoice) · <em>en factura</em> @endif</div>
                        <div class="whitespace-pre-line">{{ $log->body }}</div>
                    </li>
                @empty
                    <li class="text-sm text-slate-400">Sin observaciones.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
