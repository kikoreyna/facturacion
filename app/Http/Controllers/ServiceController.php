<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use App\Models\TariffList;
use App\Support\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('services.index', [
            'services' => Service::withCount('tariffLists')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Service(['is_active' => true, 'unit' => 'hora']));
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $service = Service::create($this->data($request));
        $this->syncPrices($service, $request->input('prices', []), $request->input('currencies', []));

        return redirect()->route('services.index')->with('status', 'Servicio registrado.');
    }

    public function edit(Service $service): View
    {
        return $this->form($service);
    }

    public function update(StoreServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($this->data($request));
        $this->syncPrices($service, $request->input('prices', []), $request->input('currencies', []));

        return redirect()->route('services.index')->with('status', 'Servicio actualizado.');
    }

    private function form(Service $service): View
    {
        return view('services.form', [
            'service' => $service,
            'tariffLists' => TariffList::orderBy('name')->get(),
            'prices' => $service->exists ? $service->tariffLists->pluck('pivot.price', 'id') : collect(),
            'currencies' => $service->exists ? $service->tariffLists->pluck('pivot.currency', 'id') : collect(),
        ]);
    }

    private function data(StoreServiceRequest $request): array
    {
        return $request->safe()->only(['code', 'name', 'description', 'unit']) + ['is_active' => $request->boolean('is_active')];
    }

    /** Campos vacíos significan "sin tarifa en ese tabulador". */
    private function syncPrices(Service $service, array $prices, array $currencies): void
    {
        $valid = TariffList::pluck('id')->all();
        $sync = [];

        foreach ($prices as $tariffId => $price) {
            if ($price !== null && $price !== '' && in_array((int) $tariffId, $valid, true)) {
                $sync[(int) $tariffId] = ['price' => $price, 'currency' => $currencies[$tariffId] ?? Currency::DEFAULT];
            }
        }

        $service->tariffLists()->sync($sync);
    }
}
