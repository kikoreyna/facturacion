@extends('layouts.app')
@section('title', $service->exists ? 'Editar servicio' : 'Nuevo servicio')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ $service->exists ? 'Editar servicio' : 'Nuevo servicio' }}</h1>
    <form method="POST" action="{{ $service->exists ? route('services.update', $service) : route('services.store') }}"
          class="max-w-2xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
        @csrf
        @if ($service->exists) @method('PUT') @endif

        @foreach ([['code', 'Código'], ['name', 'Nombre'], ['unit', 'Unidad de cobro (hora, servicio, mes…)']] as [$field, $label])
            <div>
                <label class="mb-1 block text-sm font-medium" for="{{ $field }}">{{ $label }}</label>
                <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $service->$field) }}" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                @error($field)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        @endforeach

        <div>
            <label class="mb-1 block text-sm font-medium" for="description">Descripción</label>
            <textarea id="description" name="description" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('description', $service->description) }}</textarea>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active))> Activo
        </label>

        <div>
            <h2 class="mb-2 text-sm font-semibold">Tarifa por tabulador (vacío = sin tarifa)</h2>
            @forelse ($tariffLists as $list)
                <div class="mb-2 flex items-center gap-3">
                    <label class="w-48 text-sm" for="price-{{ $list->id }}">{{ $list->name }}</label>
                    <span class="text-slate-500">$</span>
                    <input id="price-{{ $list->id }}" name="prices[{{ $list->id }}]" type="number" step="0.01" min="0"
                           value="{{ old('prices.'.$list->id, $prices[$list->id] ?? '') }}"
                           class="w-40 rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <select name="currencies[{{ $list->id }}]" class="rounded-md border border-slate-300 px-2 py-2 text-sm">
                        @foreach (\App\Support\Currency::LABELS as $code => $label)
                            <option value="{{ $code }}" @selected(old('currencies.'.$list->id, $currencies[$list->id] ?? 'MXN') === $code)>{{ $code }}</option>
                        @endforeach
                    </select>
                    @error('prices.'.$list->id)<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                </div>
            @empty
                <p class="text-sm text-slate-400">Primero crea un <a class="text-sky-600 underline" href="{{ route('tariff-lists.create') }}">tabulador</a>.</p>
            @endforelse
        </div>

        <div class="flex gap-3">
            <button class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">Guardar</button>
            <a href="{{ route('services.index') }}" class="px-4 py-2 text-sm text-slate-600">Cancelar</a>
        </div>
    </form>
@endsection
