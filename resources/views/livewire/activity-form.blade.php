<div>
    @if ($open)
        <div class="fixed inset-0 z-50 mx-auto flex max-w-xl flex-col bg-slate-50">
            <header class="flex items-center justify-between px-3 py-3">
                <button type="button" wire:click="close" class="px-2 py-1 text-sm font-medium text-slate-500">
                    Cancelar
                </button>
                <h2 class="text-sm font-semibold text-slate-800">
                    {{ $isEditing ? 'Editar actividad' : 'Agregar actividad' }}
                </h2>
                <button type="button" wire:click="save" class="px-2 py-1 text-sm font-semibold text-violet-600">
                    Guardar
                </button>
            </header>

            <div class="flex-1 overflow-y-auto px-4 pb-8">
                <div class="mb-5 flex justify-center">
                    <x-activity-icon :name="$icon" :color="$color" size="lg" />
                </div>

                <label class="mb-4 block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Nombre</span>
                    <input
                        type="text"
                        wire:model="name"
                        maxlength="80"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-violet-400"
                        placeholder="Ej. Ejercicio"
                    >
                    @error('name') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </label>

                <label class="mb-4 block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Descripción</span>
                    <input
                        type="text"
                        wire:model="description"
                        maxlength="255"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-violet-400"
                        placeholder="Opcional"
                    >
                    @error('description') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </label>

                <div class="mb-4 grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold text-slate-500">Fecha</span>
                        <input
                            type="date"
                            wire:model="date"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm outline-none focus:border-violet-400"
                        >
                        @error('date') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold text-slate-500">Hora inicio</span>
                        <input
                            type="time"
                            wire:model="start_time"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm outline-none focus:border-violet-400"
                        >
                        @error('start_time') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </label>
                </div>

                @if ($isFirst)
                    <p class="mb-4 -mt-1 text-xs text-slate-500">
                        Al cambiar la hora de inicio, las actividades de debajo se recorren y conservan su duración.
                    </p>
                @elseif ($isEditing)
                    <p class="mb-4 -mt-1 text-xs text-slate-500">
                        Al cambiar la hora o la duración, las actividades de debajo se recorren.
                    </p>
                @endif

                <label class="mb-4 block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Duración</span>
                    <select
                        wire:model="duration_minutes"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm outline-none focus:border-violet-400"
                    >
                        @foreach ($durations as $minutes => $label)
                            <option value="{{ $minutes }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('duration_minutes') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </label>

                <label class="mb-4 block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Estado inicial</span>
                    <select
                        wire:model="status"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm outline-none focus:border-violet-400"
                    >
                        @foreach ($statuses as $item)
                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="mb-6">
                    <span class="mb-2 block text-xs font-semibold text-slate-500">Icono</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($icons as $item)
                            <button
                                type="button"
                                wire:click="selectIcon('{{ $item['key'] }}')"
                                class="rounded-full {{ $icon === $item['key'] ? 'ring-2 ring-violet-500 ring-offset-2' : '' }}"
                            >
                                <x-activity-icon :name="$item['key']" :color="$item['color']" size="sm" />
                            </button>
                        @endforeach
                    </div>
                </div>

                @if ($isEditing)
                    <button
                        type="button"
                        wire:click="delete"
                        wire:confirm="¿Eliminar esta actividad?"
                        class="w-full rounded-2xl bg-red-50 py-3 text-sm font-semibold text-red-600"
                    >
                        Eliminar actividad
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>
