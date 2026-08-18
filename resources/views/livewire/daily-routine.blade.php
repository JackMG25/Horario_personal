<div>
    <header class="sticky top-0 z-20 bg-slate-50/95 px-4 pb-3 pt-4 backdrop-blur">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $this->isToday ? 'Hoy' : ucfirst($this->selected->isoFormat('dddd')) }}
                </h1>
                <p class="text-sm text-slate-500">
                    {{ ucfirst($this->selected->isoFormat('dddd, D [de] MMMM')) }}
                </p>
            </div>
            <div class="flex items-center gap-1 pt-1">
                <button type="button" wire:click="previousDay" class="rounded-full p-2 text-slate-500 hover:bg-white" aria-label="Día anterior">
                    <x-heroicon-o-chevron-left class="h-5 w-5" />
                </button>
                <button type="button" wire:click="nextDay" class="rounded-full p-2 text-slate-500 hover:bg-white" aria-label="Día siguiente">
                    <x-heroicon-o-chevron-right class="h-5 w-5" />
                </button>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-1">
            <button type="button" wire:click="previousWeek" class="rounded-full p-1.5 text-slate-400 hover:bg-white" aria-label="Semana anterior">
                <x-heroicon-o-chevron-left class="h-4 w-4" />
            </button>

            <div class="flex flex-1 justify-between">
                @foreach ($this->weekDays as $day)
                    <button
                        type="button"
                        wire:click="selectDate('{{ $day['date'] }}')"
                        class="flex w-10 flex-col items-center gap-1"
                    >
                        <span class="text-[11px] font-medium {{ $day['selected'] ? 'text-violet-600' : 'text-slate-400' }}">
                            {{ $day['label'] }}
                        </span>
                        <span @class([
                            'flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold',
                            'bg-violet-600 text-white' => $day['selected'],
                            'text-slate-800' => ! $day['selected'] && $day['is_today'],
                            'text-slate-600' => ! $day['selected'] && ! $day['is_today'],
                        ])>
                            {{ $day['number'] }}
                        </span>
                    </button>
                @endforeach
            </div>

            <button type="button" wire:click="nextWeek" class="rounded-full p-1.5 text-slate-400 hover:bg-white" aria-label="Semana siguiente">
                <x-heroicon-o-chevron-right class="h-4 w-4" />
            </button>
        </div>
    </header>

    <main class="flex-1 px-4 pb-32 pt-2">
        @if ($this->activities->isEmpty())
            <section class="mt-6 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-violet-50 text-violet-600">
                    <x-heroicon-o-calendar-days class="h-8 w-8" />
                </div>
                <h2 class="text-lg font-semibold text-slate-800">Aún no has organizado este día.</h2>
                <p class="mt-1 text-sm text-slate-500">Usa una plantilla, copia otro día o crea desde cero.</p>

                <div class="mt-6 space-y-3 text-left">
                    <a
                        href="{{ route('templates.index', ['fecha' => $date]) }}"
                        wire:navigate
                        class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm"
                    >
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-violet-100 text-violet-600">
                            <x-heroicon-o-squares-2x2 class="h-5 w-5" />
                        </span>
                        <span>
                            <span class="block font-semibold text-slate-800">Usar plantilla</span>
                            <span class="text-sm text-slate-400">Copiar un día tipo</span>
                        </span>
                    </a>

                    <button
                        type="button"
                        wire:click="openCopyDay"
                        class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left shadow-sm"
                    >
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-teal-100 text-teal-600">
                            <x-heroicon-o-document-duplicate class="h-5 w-5" />
                        </span>
                        <span>
                            <span class="block font-semibold text-slate-800">Copiar otro día</span>
                            <span class="text-sm text-slate-400">Reutilizar un día anterior</span>
                        </span>
                    </button>

                    <button
                        type="button"
                        wire:click="openCreate"
                        class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left shadow-sm"
                    >
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            <x-heroicon-o-plus class="h-5 w-5" />
                        </span>
                        <span>
                            <span class="block font-semibold text-slate-800">Crear desde cero</span>
                            <span class="text-sm text-slate-400">Agregar la primera actividad</span>
                        </span>
                    </button>
                </div>
            </section>
        @else
            <div
                data-sortable
                data-sortable-method="reorder"
                class="space-y-2.5"
            >
                @foreach ($this->activities as $activity)
                    <x-activity-card :activity="$activity" />
                @endforeach
            </div>
            <p class="mt-4 text-center text-xs text-slate-400">Arrastra para cambiar el orden. Las horas se recorren según la nueva posición.</p>
        @endif
    </main>

    <button
        type="button"
        wire:click="openCreate"
        class="fixed bottom-20 right-[max(1rem,calc(50%-11.5rem))] z-40 flex h-14 w-14 items-center justify-center rounded-full bg-violet-600 text-white shadow-lg shadow-violet-600/30"
        aria-label="Agregar actividad"
    >
        <x-heroicon-o-plus class="h-7 w-7" />
    </button>

    <x-bottom-nav />

    <livewire:activity-form />
    <livewire:copy-day />
</div>
