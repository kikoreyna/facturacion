@extends('layouts.app')
@section('title', 'Nueva orden')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">Nueva orden de servicio</h1>
    @if ($errors->any())
        <div class="mb-4 max-w-3xl rounded-md bg-rose-50 p-3 text-sm text-rose-700">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="POST" action="{{ route('service-orders.store') }}" class="max-w-3xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-medium">Cliente</label>
            <select name="client_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Selecciona…</option>
                @foreach ($clients as $c)<option value="{{ $c->id }}" @selected(old('client_id') == $c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Título</label>
            <input name="title" value="{{ old('title') }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Descripción</label>
            <textarea name="description" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Fecha agendada</label>
            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>

        <div>
            <div class="mb-1 flex items-center justify-between">
                <label class="text-sm font-medium">Servicios (la tarifa se toma del tabulador del cliente)</label>
                <button type="button" id="add-item" class="text-sm text-sky-600 hover:underline">+ Agregar servicio</button>
            </div>
            <div id="items" class="space-y-2"></div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Observación inicial (bitácora)</label>
            <textarea name="observation" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('observation') }}</textarea>
        </div>

        <div class="flex gap-3">
            <button class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">Crear orden</button>
            <a href="{{ route('service-orders.index') }}" class="px-4 py-2 text-sm text-slate-600">Cancelar</a>
        </div>
    </form>

    <template id="item-row">
        <div class="flex gap-2">
            <select data-name="service_id" required class="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm">
                @foreach ($services as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->unit }})</option>@endforeach
            </select>
            <input data-name="quantity" type="number" step="0.01" min="0.01" value="1" required class="w-24 rounded-md border border-slate-300 px-3 py-2 text-sm">
            <button type="button" class="remove px-2 text-rose-600">✕</button>
        </div>
    </template>

    <script>
        const items = document.getElementById('items');
        const tpl = document.getElementById('item-row');
        const renumber = () => items.querySelectorAll(':scope > div').forEach((row, i) =>
            row.querySelectorAll('[data-name]').forEach(el => el.name = `items[${i}][${el.dataset.name}]`));
        document.getElementById('add-item').addEventListener('click', () => {
            items.appendChild(tpl.content.cloneNode(true));
            renumber();
        });
        items.addEventListener('click', e => {
            if (e.target.classList.contains('remove')) { e.target.closest('div').remove(); renumber(); }
        });
    </script>
@endsection
