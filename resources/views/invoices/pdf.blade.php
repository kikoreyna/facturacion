<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>{{ $invoice->number }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
    h1 { font-size: 22px; margin: 0; color: #1d4ed8; }
    .muted { color: #6b7280; }
    .header, .meta { width: 100%; margin-bottom: 18px; }
    .right { text-align: right; }
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    table.items th { background: #1d4ed8; color: #fff; padding: 6px; text-align: left; }
    table.items td { padding: 5px 6px; border-bottom: 1px solid #e5e7eb; }
    .order { background: #eff6ff; font-weight: bold; padding: 6px; margin-top: 12px; }
    .log { font-size: 10px; color: #4b5563; padding: 2px 6px; }
    table.totals { width: 40%; margin-left: 60%; margin-top: 18px; border-collapse: collapse; }
    table.totals td { padding: 4px 6px; }
    .grand td { font-size: 14px; font-weight: bold; border-top: 2px solid #1d4ed8; }
</style>
</head>
<body>
<table class="header"><tr>
    <td><h1>XISA Tech</h1><div class="muted">Servicios Tecnológicos</div></td>
    <td class="right"><strong>FACTURA {{ $invoice->number }}</strong><br>
        Emisión: {{ $invoice->issued_at->format('d/m/Y') }}<br>
        @if($invoice->due_at) Vence: {{ $invoice->due_at->format('d/m/Y') }} @endif
    </td>
</tr></table>

<table class="meta"><tr><td>
    <strong>Cliente:</strong> {{ $invoice->client->name }}<br>
    @if($invoice->client->tax_id) RFC/ID: {{ $invoice->client->tax_id }}<br> @endif
    @if($invoice->client->address) {{ $invoice->client->address }}<br> @endif
    <span class="muted">Tabulador: {{ $invoice->client->tariffList->name }}</span>
</td></tr></table>

@foreach($invoice->serviceOrders as $order)
    <div class="order">{{ $order->number }} — {{ $order->title }}
        @if($order->completed_at) <span class="muted">({{ $order->completed_at->format('d/m/Y') }})</span> @endif
    </div>
    <table class="items">
        <thead><tr><th>Servicio</th><th class="right">Cant.</th><th class="right">P. unitario</th><th class="right">Importe</th></tr></thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>{{ $item->service->name }} @if($item->description)<br><span class="muted">{{ $item->description }}</span>@endif</td>
                <td class="right">{{ number_format($item->quantity, 2) }}</td>
                <td class="right">{{ \App\Support\Currency::format($item->unit_price, $item->currency) }}</td>
                <td class="right">{{ \App\Support\Currency::format($item->subtotal, $item->currency) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @foreach($order->logs as $log)
        <div class="log">• {{ $log->created_at->format('d/m/Y H:i') }}: {{ $log->body }}</div>
    @endforeach
@endforeach

<table class="totals">
    <tr><td>Subtotal</td><td class="right">{{ \App\Support\Currency::format($invoice->subtotal, $invoice->currency) }}</td></tr>
    <tr><td>IVA ({{ rtrim(rtrim($invoice->tax_rate, '0'), '.') }}%)</td><td class="right">{{ \App\Support\Currency::format($invoice->tax, $invoice->currency) }}</td></tr>
    <tr class="grand"><td>Total</td><td class="right">{{ \App\Support\Currency::format($invoice->total, $invoice->currency) }}</td></tr>
</table>

@if($invoice->notes)<p class="muted"><strong>Notas:</strong> {{ $invoice->notes }}</p>@endif
</body>
</html>
