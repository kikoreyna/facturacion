@extends('layouts.app')
@section('title', 'Tabuladores')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Tabuladores</h1>
        <a href="{{ route('tariff-lists.create') }}" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">+ Nuevo tabulador</a>
    </div>
    <div class="rounded-xl bg-white p-5 shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="text-slate-500"><tr><th class="py-2">Nombre</th><th>Descripción</th><th>Servicios con tarifa</th><th>Clientes</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse ($tariffLists as $list)
                    <tr class="border-t">
                        <td class="py-2 font-medium">{{ $list->name }}</td>
                        <td>{{ $list->description }}</td>
                        <td>{{ $list->services_count }}</td>
                        <td>{{ $list->clients_count }}</td>
                        <td>{{ $list->is_active ? 'Activo' : 'Inactivo' }}</td>
                        <td class="text-right"><a class="text-sky-600 hover:underline" href="{{ route('tariff-lists.edit', $list) }}">Editar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-slate-400">Sin tabuladores registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $tariffLists->links() }}</div>
    </div>
@endsection
