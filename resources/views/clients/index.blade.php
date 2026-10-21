@extends('layouts.app')
@section('title', 'Clientes')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Clientes</h1>
        <a href="{{ route('clients.create') }}" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">+ Nuevo cliente</a>
    </div>
    <div class="rounded-xl bg-white p-5 shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="text-slate-500"><tr><th class="py-2">Nombre</th><th>RFC/ID</th><th>Correo</th><th>Tabulador</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr class="border-t">
                        <td class="py-2 font-medium">{{ $client->name }}</td>
                        <td>{{ $client->tax_id }}</td>
                        <td>{{ $client->email }}</td>
                        <td>{{ $client->tariffList->name }}</td>
                        <td>{{ $client->is_active ? 'Activo' : 'Inactivo' }}</td>
                        <td class="text-right"><a class="text-sky-600 hover:underline" href="{{ route('clients.edit', $client) }}">Editar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-slate-400">Sin clientes registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $clients->links() }}</div>
    </div>
@endsection
