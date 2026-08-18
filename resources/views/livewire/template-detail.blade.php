<div class="flex min-h-dvh flex-col">
    <header class="sticky top-0 z-20 flex items-center justify-between bg-slate-50/95 px-2 py-3 backdrop-blur">
        <a href="{{ route('templates.index', ['fecha' => $fecha]) }}" wire:navigate class="rounded-full p-2 text-slate-500">
            <x-heroicon-o-arrow-left class="h-5 w-5" />
        </a>
        <h1 class="text-sm font-semibold text-slate-800">{{ $template->name }}</h1>
        <div class="flex items-center">
            <a href="{{ route('templates.edit', ['template' => $template, 'fecha' => $fecha]) }}" wire:navigate class="rounded-full p-2 text-slate-500">
                <x-heroicon-o-pencil-square class="h-5 w-5" />
            </a>
            <button type="button" wire:click="duplicate" class="rounded-full p-2 text-slate-500" aria-label="Duplicar">
                <x-heroicon-o-document-duplicate class="h-5 w-5" />
            </button>
            <button
                type="button"
                wire:click="delete"
                wire:confirm="¿Eliminar esta plantilla?"
                class="rounded-full p-2 text-slate-500"
                aria-label="Eliminar"
            >
                <x-heroicon-o-trash class="h-5 w-5" />
            </button>
        </div>
    </header>

    <main class="flex-1 px-4 pb-36">
        <div class="mb-6 flex flex-col items-center text-center">
            <x-activity-icon :name="$template->icon" :color="$template->color" size="lg" />
            <h2 class="mt-3 text-xl font-bold text-slate-900">{{ $template->name }}</h2>
            @if ($template->description)
                <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $template->description }}</p>
            @endif
        </div>

        <div class="space-y-2.5">
            @forelse ($template->activities as $activity)
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-3">
                    <x-activity-icon :name="$activity->icon" :color="$activity->color" />
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800">{{ $activity->name }}</p>
                        @if ($activity->timeRange())
                            <p class="text-sm text-slate-400">{{ $activity->timeRange() }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-slate-400">Esta plantilla no tiene actividades.</p>
            @endforelse
        </div>
    </main>

    <div class="fixed inset-x-0 bottom-16 z-30 mx-auto max-w-xl px-4">
        <button
            type="button"
            wire:click="apply"
            class="w-full rounded-2xl bg-violet-600 py-3.5 text-sm font-semibold text-white shadow-lg shadow-violet-600/30"
        >
            Usar esta plantilla
        </button>
        @error('fecha')
            <p class="mt-2 text-center text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <x-bottom-nav />
</div>
