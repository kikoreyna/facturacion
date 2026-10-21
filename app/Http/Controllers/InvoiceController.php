<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Services\InvoicePdfService;
use App\Services\InvoicingService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(): View
    {
        return view('invoices.index', [
            'invoices' => Invoice::with('client:id,name')->latest()->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        $clientId = $request->query('client_id');

        return view('invoices.create', [
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'clientId' => $clientId,
            'orders' => $clientId
                ? ServiceOrder::pendingInvoice()->where('client_id', $clientId)->with('items')->oldest('completed_at')->get()
                : collect(),
        ]);
    }

    public function store(StoreInvoiceRequest $request, InvoicingService $invoicing): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        try {
            $invoice = $invoicing->createFromOrders(
                Client::findOrFail($data['client_id']),
                $data['order_ids'],
                (float) ($data['tax_rate'] ?? 16),
                $data['notes'] ?? null,
                $data['due_days'] ?? 30,
            );
        } catch (DomainException $e) {
            throw ValidationException::withMessages(['order_ids' => $e->getMessage()]);
        }

        return $request->wantsJson()
            ? response()->json($invoice->load('serviceOrders'), 201)
            : redirect()->route('invoices.show', $invoice)->with('status', 'Factura generada.');
    }

    public function show(Request $request, Invoice $invoice): JsonResponse|View
    {
        $invoice->load('client.tariffList', 'serviceOrders.items.service');

        return $request->wantsJson() ? response()->json($invoice) : view('invoices.show', compact('invoice'));
    }

    public function updateStatus(Request $request, Invoice $invoice): JsonResponse|RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', 'in:'.Invoice::SENT.','.Invoice::PAID],
        ])['status'];

        if ($invoice->status === Invoice::PAID) {
            throw ValidationException::withMessages(['status' => 'La factura ya está pagada.']);
        }

        if ($invoice->status === Invoice::SENT && $status === Invoice::SENT) {
            throw ValidationException::withMessages(['status' => 'La factura ya fue enviada a cobro.']);
        }

        $invoice->update([
            'status' => $status,
            'sent_at' => $invoice->sent_at ?? now(),
            'paid_at' => $status === Invoice::PAID ? now() : null,
        ]);

        return $request->wantsJson() ? response()->json($invoice) : back()->with('status', 'Estado de la factura actualizado.');
    }

    public function pdf(Request $request, Invoice $invoice, InvoicePdfService $pdf): Response
    {
        $document = $pdf->render($invoice);

        return $request->boolean('download')
            ? $document->download("{$invoice->number}.pdf")
            : $document->stream("{$invoice->number}.pdf");
    }
}
