@extends('layouts.app')
@section('title', 'Facturas')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Facturas</h1>
        <a href="{{ route('invoices.create') }}" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">+ Nueva factura</a>
    </div>
    <div class="rounded-xl bg-white p-5 shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="text-slate-500"><tr><th class="py-2">Número</th><th>Cliente</th><th>Emisión</th><th class="text-right">Total</th><th></th></tr></thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr class="border-t">
                        <td class="py-2 font-medium"><a class="text-sky-600 hover:underline" href="{{ route('invoices.show', $invoice) }}">{{ $invoice->number }}</a></td>
                        <td>{{ $invoice->client->name }}</td>
                        <td>{{ $invoice->issued_at->format('d/m/Y') }}</td>
                        <td class="text-right">{{ \App\Support\Currency::format($invoice->total, $invoice->currency) }}</td>
                        <td class="text-right"><a class="text-sky-600 hover:underline" href="{{ route('invoices.pdf', $invoice) }}" target="_blank">PDF</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-slate-400">Sin facturas.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $invoices->links() }}</div>
    </div>
@endsection
