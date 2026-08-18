<div>
    @if ($open)
        <div class="fixed inset-0 z-50 mx-auto flex max-w-xl flex-col bg-slate-50">
            <header class="flex items-center justify-between px-3 py-3">
                <button type="button" wire:click="close" class="px-2 py-1 text-sm font-medium text-slate-500">
                    Cancelar
                </button>
                <h2 class="text-sm font-semibold text-slate-800">Copiar día</h2>
                <button type="button" wire:click="copy" class="px-2 py-1 text-sm font-semibold text-violet-600">
                    Copiar
                </button>
            </header>

            <div class="flex-1 overflow-y-auto px-4 pb-8">
                <p class="mb-4 text-sm text-slate-500">
                    Elige un día con actividades. Se copiarán a
                    <span class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($targetDate)->locale('es')->isoFormat('D [de] MMMM') }}</span>
                    como pendientes.
                </p>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <button type="button" wire:click="previousMonth" class="rounded-full p-1 text-slate-400">
                            <x-heroicon-o-chevron-left class="h-5 w-5" />
                        </button>
                        <h3 class="text-sm font-semibold capitalize text-slate-800">{{ $monthLabel }}</h3>
                        <button type="button" wire:click="nextMonth" class="rounded-full p-1 text-slate-400">
                            <x-heroicon-o-chevron-right class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="mb-2 grid grid-cols-7 text-center text-[11px] font-medium text-slate-400">
                        <span>L</span><span>M</span><span>M</span><span>J</span><span>V</span><span>S</span><span>D</span>
                    </div>

                    <div class="space-y-1">
                        @foreach ($weeks as $week)
                            <div class="grid grid-cols-7">
                                @foreach ($week as $day)
                                    <button
                                        type="button"
                                        wire:click="selectSource('{{ $day['date'] }}')"
                                        @disabled($day['is_target'])
                                        class="relative mx-auto flex h-9 w-9 items-center justify-center rounded-full text-sm
                                            {{ $day['selected'] ? 'bg-violet-600 text-white' : '' }}
                                            {{ ! $day['in_month'] ? 'text-slate-300' : 'text-slate-700' }}
                                            {{ $day['is_target'] ? 'opacity-40' : '' }}"
                                    >
                                        {{ $day['number'] }}
                                        @if ($day['has_activities'] && ! $day['selected'])
                                            <span class="absolute bottom-1 h-1 w-1 rounded-full bg-violet-400"></span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                @error('sourceDate')
                    <p class="mt-3 text-sm text-red-500">{{ $message }}</p>
                @enderror

                <div class="mt-4 space-y-3">
                    @if ($previousDate)
                        <button
                            type="button"
                            wire:click="copyPreviousDay"
                            class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left shadow-sm"
                        >
                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-teal-100 text-teal-600">
                                <x-heroicon-o-arrow-uturn-left class="h-5 w-5" />
                            </span>
                            <span>
                                <span class="block font-semibold text-slate-800">Copiar día anterior</span>
                                <span class="text-sm text-slate-400 capitalize">{{ $previousDate->locale('es')->isoFormat('dddd D [de] MMMM') }}</span>
                            </span>
                        </button>
                    @endif

                    @foreach ($recentDays as $day)
                        <button
                            type="button"
                            wire:click="selectSource('{{ $day->date->toDateString() }}')"
                            class="flex w-full items-center justify-between rounded-2xl border px-4 py-3 text-left shadow-sm {{ $sourceDate === $day->date->toDateString() ? 'border-violet-300 bg-violet-50' : 'border-slate-200 bg-white' }}"
                        >
                            <span>
                                <span class="block font-semibold capitalize text-slate-800">{{ $day->date->locale('es')->isoFormat('dddd D [de] MMMM') }}</span>
                                <span class="text-sm text-slate-400">{{ $day->activities_count }} actividades</span>
                            </span>
                            @if ($sourceDate === $day->date->toDateString())
                                <x-heroicon-s-check-circle class="h-5 w-5 text-violet-600" />
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
