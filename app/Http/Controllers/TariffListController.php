<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTariffListRequest;
use App\Models\Service;
use App\Models\TariffList;
use App\Support\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TariffListController extends Controller
{
    public function index(): View
    {
        return view('tariff-lists.index', [
            'tariffLists' => TariffList::withCount(['services', 'clients'])->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return $this->form(new TariffList(['is_active' => true]));
    }

    public function store(StoreTariffListRequest $request): RedirectResponse
    {
        $list = TariffList::create($this->data($request));
        $this->syncPrices($list, $request->input('prices', []), $request->input('currencies', []));

        return redirect()->route('tariff-lists.index')->with('status', 'Tabulador registrado.');
    }

    public function edit(TariffList $tariffList): View
    {
        return $this->form($tariffList);
    }

    public function update(StoreTariffListRequest $request, TariffList $tariffList): RedirectResponse
    {
        $tariffList->update($this->data($request));
        $this->syncPrices($tariffList, $request->input('prices', []), $request->input('currencies', []));

        return redirect()->route('tariff-lists.index')->with('status', 'Tabulador actualizado.');
    }

    private function form(TariffList $list): View
    {
        return view('tariff-lists.form', [
            'tariffList' => $list,
            'services' => Service::orderBy('name')->get(),
            'prices' => $list->exists ? $list->services->pluck('pivot.price', 'id') : collect(),
            'currencies' => $list->exists ? $list->services->pluck('pivot.currency', 'id') : collect(),
        ]);
    }

    private function data(StoreTariffListRequest $request): array
    {
        return $request->safe()->only(['name', 'description']) + ['is_active' => $request->boolean('is_active')];
    }

    /** Campos vacíos significan "sin tarifa para ese servicio". */
    private function syncPrices(TariffList $list, array $prices, array $currencies): void
    {
        $valid = Service::pluck('id')->all();
        $sync = [];

        foreach ($prices as $serviceId => $price) {
            if ($price !== null && $price !== '' && in_array((int) $serviceId, $valid, true)) {
                $sync[(int) $serviceId] = ['price' => $price, 'currency' => $currencies[$serviceId] ?? Currency::DEFAULT];
            }
        }

        $list->services()->sync($sync);
    }
}
