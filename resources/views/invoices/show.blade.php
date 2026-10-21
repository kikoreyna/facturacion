@extends('layouts.app')
@section('title', $invoice->number)

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Factura {{ $invoice->number }}</h1>
            <p class="text-sm text-slate-500">{{ $invoice->client->name }} · Emitida {{ $invoice->issued_at->format('d/m/Y') }}
                @if ($invoice->due_at) · Vence {{ $invoice->due_at->format('d/m/Y') }}                 @endif
                               · Estado: <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs">{{ \App\Models\Invoice::STATUS_LABELS[$invoice->status] ?? $invoice->status }}</span>
                               @if ($invoice->sent_at) · Enviada {{ $invoice->sent_at->format('d/m/Y') }} @endif
                               @if ($invoice->paid_at) · Pagada {{ $invoice->paid_at->format('d/m/Y') }} @endif</p>
                       </div>
                       <div class="flex flex-wrap gap-2">
                           @if ($invoice->status === 'issued')
                               <form method="POST" action="{{ route('invoices.status', $invoice) }}">
                                   @csrf @method('PATCH')
                                   <input type="hidden" name="status" value="sent">
                                   <button class="rounded-md bg-amber-500 px-4 py-2 text-sm text-white">Marcar enviada a cobro</button>
                               </form>
                           @endif
                           @if ($invoice->status !== 'paid')
                               <form method="POST" action="{{ route('invoices.status', $invoice) }}">
                                   @csrf @method('PATCH')
                                   <input type="hidden" name="status" value="paid">
                                   <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm text-white">Marcar pagada</button>
                               </form>
                           @endif
            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white">Ver PDF</a>
            <a href="{{ route('invoices.pdf', [$invoice, 'download' => 1]) }}" class="rounded-md bg-sky-600 px-4 py-2 text-sm text-white">Descargar PDF</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-rose-50 p-3 text-sm text-rose-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    @foreach ($invoice->serviceOrders as $order)
        <div class="mb-4 rounded-xl bg-white p-5 shadow-sm">
            <h2 class="mb-2 font-semibold"><a class="text-sky-600 hover:underline" href="{{ route('service-orders.show', $order) }}">{{ $order->number }}</a> — {{ $order->title }}</h2>
            <table class="w-full text-left text-sm">
                <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-t">
                        <td class="py-1">{{ $item->service->name }}</td>
                        <td class="text-right">{{ $item->quantity }} × {{ \App\Support\Currency::format($item->unit_price, $item->currency) }}</td>
                        <td class="text-right">{{ \App\Support\Currency::format($item->subtotal, $item->currency) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="ml-auto max-w-xs rounded-xl bg-white p-5 text-sm shadow-sm">
        <div class="flex justify-between"><span>Subtotal</span><span>{{ \App\Support\Currency::format($invoice->subtotal, $invoice->currency) }}</span></div>
        <div class="flex justify-between"><span>IVA ({{ (float) $invoice->tax_rate }}%)</span><span>{{ \App\Support\Currency::format($invoice->tax, $invoice->currency) }}</span></div>
        <div class="mt-2 flex justify-between border-t pt-2 text-lg font-bold"><span>Total</span><span>{{ \App\Support\Currency::format($invoice->total, $invoice->currency) }}</span></div>
    </div>
@endsection
