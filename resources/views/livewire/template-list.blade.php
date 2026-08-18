<div class="flex min-h-dvh flex-col">
    <header class="sticky top-0 z-20 flex items-center justify-between bg-slate-50/95 px-4 py-4 backdrop-blur">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Plantillas</h1>
            <p class="text-sm text-slate-500">Copia un día tipo a cualquier fecha.</p>
        </div>
        <a
            href="{{ route('templates.create', ['fecha' => $applyDate]) }}"
            wire:navigate
            class="flex h-10 w-10 items-center justify-center rounded-full bg-violet-600 text-white"
            aria-label="Nueva plantilla"
        >
            <x-heroicon-o-plus class="h-5 w-5" />
        </a>
    </header>

    <main class="flex-1 px-4 pb-28">
        @if ($templates->isEmpty())
            <div class="mt-16 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-violet-50 text-violet-600">
                    <x-heroicon-o-squares-2x2 class="h-8 w-8" />
                </div>
                <p class="font-semibold text-slate-800">Todavía no hay plantillas.</p>
                <p class="mt-1 text-sm text-slate-500">Crea una para reutilizar tus días más comunes.</p>
            </div>
        @else
            <div class="space-y-2.5">
                @foreach ($templates as $template)
                    <a
                        href="{{ route('templates.show', ['template' => $template, 'fecha' => $applyDate]) }}"
                        wire:navigate
                        class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-3 shadow-sm"
                    >
                        <x-activity-icon :name="$template->icon" :color="$template->color" />
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold text-slate-800">{{ $template->name }}</span>
                            <span class="text-sm text-slate-400">{{ $template->activities_count }} {{ $template->activities_count === 1 ? 'actividad' : 'actividades' }}</span>
                        </span>
                        <x-heroicon-o-chevron-right class="h-5 w-5 text-slate-300" />
                    </a>
                @endforeach
            </div>
        @endif
    </main>

    <x-bottom-nav />
</div>
