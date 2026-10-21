<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Models\TariffList;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        return view('clients.index', [
            'clients' => Client::with('tariffList:id,name')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('clients.form', ['client' => new Client(['is_active' => true]), 'tariffLists' => TariffList::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        Client::create($this->data($request));

        return redirect()->route('clients.index')->with('status', 'Cliente registrado.');
    }

    public function edit(Client $client): View
    {
        return view('clients.form', ['client' => $client, 'tariffLists' => TariffList::where('is_active', true)->orderBy('name')->get()]);
    }

    public function update(StoreClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($this->data($request));

        return redirect()->route('clients.index')->with('status', 'Cliente actualizado.');
    }

    private function data(StoreClientRequest $request): array
    {
        return $request->safe()->except('is_active') + ['is_active' => $request->boolean('is_active')];
    }
}
