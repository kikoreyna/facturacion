<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DompdfPdf;

class InvoicePdfService
{
    public function render(Invoice $invoice): DompdfPdf
    {
        $invoice->load([
            'client.tariffList',
            'serviceOrders.items.service',
            'serviceOrders.logs' => fn ($q) => $q->where('show_on_invoice', true),
        ]);

        return Pdf::loadView('invoices.pdf', ['invoice' => $invoice])->setPaper('a4');
    }
}
