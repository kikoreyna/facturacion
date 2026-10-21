<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'stats' => [
                'clients' => Client::where('is_active', true)->count(),
                'open_orders' => ServiceOrder::whereIn('status', [ServiceOrder::SCHEDULED, ServiceOrder::IN_PROGRESS])->count(),
                'pending_invoice' => ServiceOrder::pendingInvoice()->count(),
                'invoiced_month' => Invoice::where('status', '!=', 'cancelled')
                    ->whereBetween('issued_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->selectRaw('currency, SUM(total) as total')->groupBy('currency')->pluck('total', 'currency'),
            ],
            'recentOrders' => ServiceOrder::with('client:id,name')->latest()->limit(8)->get(),
        ]);
    }
}
