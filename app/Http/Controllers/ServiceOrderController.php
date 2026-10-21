<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceOrderLogRequest;
use App\Http\Requests\StoreServiceOrderRequest;
use App\Http\Requests\UpdateServiceOrderRequest;
use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Services\ServiceOrderService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ServiceOrderController extends Controller
{
    public function __construct(private ServiceOrderService $orders) {}

    public function index(Request $request): JsonResponse|View
    {
        $orders = ServiceOrder::with('client:id,name')
            ->when($request->query('client_id'), fn ($q, $id) => $q->where('client_id', $id))
            ->when($request->boolean('pending_invoice'), fn ($q) => $q->pendingInvoice())
            ->latest()->paginate(20);

        return $request->wantsJson() ? response()->json($orders) : view('service-orders.index', compact('orders'));
    }

    public function create(): View
    {
        return view('service-orders.create', [
            'clients' => Client::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'services' => Service::where('is_active', true)->orderBy('name')->get(['id', 'name', 'unit']),
        ]);
    }

    public function store(StoreServiceOrderRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $order = $this->orders->create($request->validated(), $request->user());
        } catch (DomainException $e) {
            throw ValidationException::withMessages(['items' => $e->getMessage()]);
        }

        return $request->wantsJson()
            ? response()->json($order->load('items', 'logs'), 201)
            : redirect()->route('service-orders.show', $order)->with('status', 'Orden creada.');
    }

    public function show(Request $request, ServiceOrder $serviceOrder): JsonResponse|View
    {
        $serviceOrder->load('client.tariffList', 'items.service', 'logs.user');

        if ($request->wantsJson()) {
            return response()->json($serviceOrder);
        }

        return view('service-orders.show', [
            'order' => $serviceOrder,
            'services' => Service::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(ServiceOrder $serviceOrder): View|RedirectResponse
    {
        if (in_array($serviceOrder->status, [ServiceOrder::INVOICED, ServiceOrder::CANCELLED], true)) {
            return redirect()->route('service-orders.show', $serviceOrder)
                ->withErrors(['status' => 'La orden ya no admite modificaciones.']);
        }

        return view('service-orders.edit', ['order' => $serviceOrder->load('client', 'items.service')]);
    }

    public function update(UpdateServiceOrderRequest $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $serviceOrder) {
                $this->orders->update($serviceOrder, $request->validated());

                foreach ($request->input('items', []) as $id => $row) {
                    $item = $serviceOrder->items()->findOrFail($id);

                    if (! empty($row['remove'])) {
                        $this->orders->removeItem($item);
                    } else {
                        $quantity = (float) ($row['quantity'] ?? 0);
                        if ($quantity <= 0) {
                            throw new DomainException('La cantidad debe ser mayor a 0.');
                        }
                        $this->orders->updateItem($item, $quantity, $row['description'] ?? null);
                    }
                }
            });
        } catch (DomainException $e) {
            throw ValidationException::withMessages(['items' => $e->getMessage()]);
        }

        return redirect()->route('service-orders.show', $serviceOrder)->with('status', 'Orden actualizada.');
    }

    public function storeItem(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->orders->addItem($serviceOrder, (int) $data['service_id'], (float) $data['quantity'], $data['description'] ?? null);
        } catch (DomainException $e) {
            throw ValidationException::withMessages(['service_id' => $e->getMessage()]);
        }

        return back()->with('status', 'Servicio agregado.');
    }

    public function storeLog(StoreServiceOrderLogRequest $request, ServiceOrder $serviceOrder): JsonResponse|RedirectResponse
    {
        $log = $this->orders->addLog(
            $serviceOrder,
            $request->validated('body'),
            $request->user(),
            $request->boolean('show_on_invoice'),
        );

        return $request->wantsJson() ? response()->json($log, 201) : back()->with('status', 'Observación registrada.');
    }

    public function updateStatus(Request $request, ServiceOrder $serviceOrder): JsonResponse|RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', 'in:scheduled,in_progress,completed,cancelled'],
        ])['status'];

        try {
            $order = $this->orders->changeStatus($serviceOrder, $status, $request->user());
        } catch (DomainException $e) {
            throw ValidationException::withMessages(['status' => $e->getMessage()]);
        }

        return $request->wantsJson() ? response()->json($order) : back()->with('status', 'Estado actualizado.');
    }
}
