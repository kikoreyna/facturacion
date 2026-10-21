@extends('layouts.app')
@section('title', $tariffList->exists ? 'Editar tabulador' : 'Nuevo tabulador')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ $tariffList->exists ? 'Editar tabulador' : 'Nuevo tabulador' }}</h1>
    <form method="POST" action="{{ $tariffList->exists ? route('tariff-lists.update', $tariffList) : route('tariff-lists.store') }}"
          class="max-w-2xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
        @csrf
        @if ($tariffList->exists) @method('PUT') @endif

        <div>
            <label class="mb-1 block text-sm font-medium" for="name">Nombre</label>
            <input id="name" name="name" value="{{ old('name', $tariffList->name) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium" for="description">Descripción</label>
            <textarea id="description" name="description" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('description', $tariffList->description) }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $tariffList->is_active))> Activo
        </label>

        <div>
            <h2 class="mb-2 text-sm font-semibold">Tarifa por servicio (vacío = sin tarifa)</h2>
            @forelse ($services as $service)
                <div class="mb-2 flex items-center gap-3">
                    <label class="w-64 text-sm" for="price-{{ $service->id }}">{{ $service->name }} <span class="text-slate-400">/ {{ $service->unit }}</span></label>
                    <span class="text-slate-500">$</span>
                    <input id="price-{{ $service->id }}" name="prices[{{ $service->id }}]" type="number" step="0.01" min="0"
                           value="{{ old('prices.'.$service->id, $prices[$service->id] ?? '') }}"
                           class="w-40 rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <select name="currencies[{{ $service->id }}]" class="rounded-md border border-slate-300 px-2 py-2 text-sm">
                        @foreach (\App\Support\Currency::LABELS as $code => $label)
                            <option value="{{ $code }}" @selected(old('currencies.'.$service->id, $currencies[$service->id] ?? 'MXN') === $code)>{{ $code }}</option>
                        @endforeach
                    </select>
                    @error('prices.'.$service->id)<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                </div>
            @empty
                <p class="text-sm text-slate-400">Aún no hay servicios; créalos en <a class="text-sky-600 underline" href="{{ route('services.create') }}">Servicios</a>.</p>
            @endforelse
        </div>

        <div class="flex gap-3">
            <button class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">Guardar</button>
            <a href="{{ route('tariff-lists.index') }}" class="px-4 py-2 text-sm text-slate-600">Cancelar</a>
        </div>
    </form>
@endsection
