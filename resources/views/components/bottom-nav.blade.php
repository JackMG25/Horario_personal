@php
    $current = request()->routeIs('home') ? 'home' : 'templates';
@endphp

<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto grid max-w-xl grid-cols-2">
        <a
            href="{{ route('home') }}"
            wire:navigate
            class="flex flex-col items-center gap-0.5 py-2.5 {{ $current === 'home' ? 'text-violet-600' : 'text-slate-400' }}"
        >
            <x-heroicon-o-calendar-days class="h-6 w-6" />
            <span class="text-[11px] font-semibold">Hoy</span>
        </a>
        <a
            href="{{ route('templates.index') }}"
            wire:navigate
            class="flex flex-col items-center gap-0.5 py-2.5 {{ $current === 'templates' ? 'text-violet-600' : 'text-slate-400' }}"
        >
            <x-heroicon-o-squares-2x2 class="h-6 w-6" />
            <span class="text-[11px] font-semibold">Plantillas</span>
        </a>
    </div>
</nav>
