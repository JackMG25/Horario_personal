<div class="flex min-h-dvh flex-col">
    <header class="sticky top-0 z-20 flex items-center justify-between bg-slate-50/95 px-3 py-3 backdrop-blur">
        <a href="{{ $templateId ? route('templates.show', ['template' => $templateId, 'fecha' => $fecha]) : route('templates.index', ['fecha' => $fecha]) }}" wire:navigate class="px-2 py-1 text-sm font-medium text-slate-500">
            Cancelar
        </a>
        <h1 class="text-sm font-semibold text-slate-800">
            {{ $isEditing ? 'Editar plantilla' : 'Nueva plantilla' }}
        </h1>
        <button type="button" wire:click="save" class="px-2 py-1 text-sm font-semibold text-violet-600">
            Guardar
        </button>
    </header>

    <main class="flex-1 px-4 pb-10">
        <div class="mb-5 flex justify-center">
            <x-activity-icon :name="$icon" :color="$color" size="lg" />
        </div>

        <label class="mb-4 block">
            <span class="mb-1.5 block text-xs font-semibold text-slate-500">Nombre</span>
            <input type="text" wire:model="name" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-violet-400" placeholder="Día de trabajo">
            @error('name') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
        </label>

        <label class="mb-4 block">
            <span class="mb-1.5 block text-xs font-semibold text-slate-500">Descripción</span>
            <input type="text" wire:model="description" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-violet-400" placeholder="Opcional">
        </label>

        <div class="mb-6">
            <span class="mb-2 block text-xs font-semibold text-slate-500">Icono</span>
            <div class="flex flex-wrap gap-2">
                @foreach ($icons as $item)
                    <button type="button" wire:click="selectIcon('{{ $item['key'] }}')" class="rounded-full {{ $icon === $item['key'] ? 'ring-2 ring-violet-500 ring-offset-2' : '' }}">
                        <x-activity-icon :name="$item['key']" :color="$item['color']" size="sm" />
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Actividades</h2>
            <button type="button" wire:click="addItem" class="text-sm font-semibold text-violet-600">Añadir</button>
        </div>
        <p class="mb-3 text-xs text-slate-400">Si la primera empieza a las 06:00 y dura 1 hora, la siguiente pasa a las 07:00. Al arrastrar, las horas se recorren.</p>
        @error('items') <p class="mb-2 text-xs text-red-500">{{ $message }}</p> @enderror

        <div data-sortable data-sortable-method="reorderItems" class="space-y-3">
            @foreach ($items as $index => $item)
                @php
                    $endsAt = \App\Support\ScheduleTimes::endTime(
                        (string) ($item['start_time'] ?? '08:00'),
                        (int) ($item['duration_minutes'] ?? 30),
                    );
                @endphp
                <div data-id="{{ $index }}" wire:key="item-{{ $index }}" class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="drag-handle cursor-grab touch-none text-slate-300">
                            <x-heroicon-o-bars-3 class="h-5 w-5" />
                        </span>
                        <button type="button" wire:click="removeItem({{ $index }})" class="text-slate-400">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                    </div>
                    <input type="text" wire:model="items.{{ $index }}.name" class="mb-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none" placeholder="Nombre">
                    @error('items.'.$index.'.name') <span class="mb-2 block text-xs text-red-500">{{ $message }}</span> @enderror
                    <div class="mb-2 grid grid-cols-2 gap-2">
                        <div>
                            <input type="time" wire:model.live="items.{{ $index }}.start_time" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            @error('items.'.$index.'.start_time') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <select wire:model.live="items.{{ $index }}.duration_minutes" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                @foreach ($durations as $minutes => $label)
                                    <option value="{{ $minutes }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('items.'.$index.'.duration_minutes') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <p class="mb-2 text-xs text-slate-400">Termina a las {{ $endsAt }}</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($icons as $iconOption)
                            <button
                                type="button"
                                wire:click="selectItemIcon({{ $index }}, '{{ $iconOption['key'] }}')"
                                class="rounded-full {{ ($item['icon'] ?? '') === $iconOption['key'] ? 'ring-2 ring-violet-500' : '' }}"
                            >
                                <x-activity-icon :name="$iconOption['key']" :color="$iconOption['color']" size="sm" class="!h-8 !w-8" />
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </main>
</div>
