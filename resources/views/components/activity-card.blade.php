@php
    use App\Enums\ActivityStatus;
    $visual = $activity->visualStatus();
@endphp

<div
    data-id="{{ $activity->id }}"
    wire:key="activity-{{ $activity->id }}"
    class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-2.5 py-2.5 shadow-sm {{ $visual === ActivityStatus::Skipped ? 'opacity-60' : '' }}"
>
    <button
        type="button"
        class="drag-handle shrink-0 cursor-grab touch-none px-1 text-slate-300 active:cursor-grabbing"
        aria-label="Arrastrar"
    >
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <circle cx="7" cy="5" r="1.4" />
            <circle cx="13" cy="5" r="1.4" />
            <circle cx="7" cy="10" r="1.4" />
            <circle cx="13" cy="10" r="1.4" />
            <circle cx="7" cy="15" r="1.4" />
            <circle cx="13" cy="15" r="1.4" />
        </svg>
    </button>

    <button
        type="button"
        wire:click="openEdit({{ $activity->id }})"
        class="flex min-w-0 flex-1 items-center gap-3 text-left"
    >
        <x-activity-icon :name="$activity->icon" :color="$activity->color" />

        <span class="min-w-0">
            <span class="block truncate font-semibold text-slate-800 {{ $visual === ActivityStatus::Completed || $visual === ActivityStatus::Skipped ? 'line-through text-slate-400' : '' }}">
                {{ $activity->name }}
            </span>
            @if ($activity->timeRange())
                <span class="block text-sm text-slate-400">{{ $activity->timeRange() }}</span>
            @endif
        </span>
    </button>

    <button
        type="button"
        wire:click.stop="toggleComplete({{ $activity->id }})"
        class="flex h-10 w-16 shrink-0 items-center justify-center"
        aria-label="Cambiar estado"
    >
        @if ($visual === ActivityStatus::Completed)
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 text-white">
                <x-heroicon-s-check class="h-4 w-4" />
            </span>
        @elseif ($visual === ActivityStatus::InProgress)
            <span class="rounded-full bg-sky-100 px-2 py-1 text-[11px] font-semibold text-sky-700">
                En curso
            </span>
        @elseif ($visual === ActivityStatus::Skipped)
            <span class="text-[11px] font-semibold text-slate-400">Omitido</span>
        @else
            <span class="h-6 w-6 rounded-full border-2 border-slate-300"></span>
        @endif
    </button>
</div>
