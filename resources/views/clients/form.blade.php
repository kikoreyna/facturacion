@extends('layouts.app')
@section('title', $client->exists ? 'Editar cliente' : 'Nuevo cliente')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ $client->exists ? 'Editar cliente' : 'Nuevo cliente' }}</h1>
    <form method="POST" action="{{ $client->exists ? route('clients.update', $client) : route('clients.store') }}"
          class="max-w-2xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
        @csrf
        @if ($client->exists) @method('PUT') @endif

        @foreach ([['name', 'Nombre / Razón social', 'text'], ['tax_id', 'RFC / ID fiscal', 'text'], ['email', 'Correo', 'email'], ['phone', 'Teléfono', 'text']] as [$field, $label, $type])
            <div>
                <label class="mb-1 block text-sm font-medium" for="{{ $field }}">{{ $label }}</label>
                <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field, $client->$field) }}"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                @error($field)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        @endforeach

        <div>
            <label class="mb-1 block text-sm font-medium" for="address">Dirección</label>
            <textarea id="address" name="address" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('address', $client->address) }}</textarea>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium" for="tariff_list_id">Tabulador de tarifas</label>
            <select id="tariff_list_id" name="tariff_list_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Selecciona…</option>
                @foreach ($tariffLists as $list)
                    <option value="{{ $list->id }}" @selected(old('tariff_list_id', $client->tariff_list_id) == $list->id)>{{ $list->name }}</option>
                @endforeach
            </select>
            @error('tariff_list_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $client->is_active))> Activo
        </label>

        <div class="flex gap-3">
            <button class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">Guardar</button>
            <a href="{{ route('clients.index') }}" class="px-4 py-2 text-sm text-slate-600">Cancelar</a>
        </div>
    </form>
@endsection
