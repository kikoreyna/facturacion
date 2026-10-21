@extends('layouts.app')
@section('title', 'Servicios')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Servicios</h1>
        <a href="{{ route('services.create') }}" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">+ Nuevo servicio</a>
    </div>
    <div class="rounded-xl bg-white p-5 shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="text-slate-500"><tr><th class="py-2">Código</th><th>Nombre</th><th>Unidad</th><th>Tabuladores con tarifa</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse ($services as $service)
                    <tr class="border-t">
                        <td class="py-2 font-medium">{{ $service->code }}</td>
                        <td>{{ $service->name }}</td>
                        <td>{{ $service->unit }}</td>
                        <td>{{ $service->tariff_lists_count }}</td>
                        <td>{{ $service->is_active ? 'Activo' : 'Inactivo' }}</td>
                        <td class="text-right"><a class="text-sky-600 hover:underline" href="{{ route('services.edit', $service) }}">Editar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-slate-400">Sin servicios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $services->links() }}</div>
    </div>
@endsection
