<?php

namespace App\Livewire;

use App\Models\Day;
use App\Services\RoutineCopyService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class CopyDay extends Component
{
    public bool $open = false;

    public string $targetDate = '';

    public string $sourceDate = '';

    public string $calendarMonth = '';

    #[On('open-copy-day')]
    public function open(string $date): void
    {
        $this->open = true;
        $this->targetDate = $date;
        $this->sourceDate = Carbon::parse($date)->subDay()->toDateString();
        $this->calendarMonth = Carbon::parse($date)->startOfMonth()->toDateString();
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function previousMonth(): void
    {
        $this->calendarMonth = Carbon::parse($this->calendarMonth)->subMonth()->startOfMonth()->toDateString();
    }

    public function nextMonth(): void
    {
        $this->calendarMonth = Carbon::parse($this->calendarMonth)->addMonth()->startOfMonth()->toDateString();
    }

    public function selectSource(string $date): void
    {
        if ($date === $this->targetDate) {
            return;
        }

        $this->sourceDate = $date;
    }

    public function copyPreviousDay(): void
    {
        $this->sourceDate = Carbon::parse($this->targetDate)->subDay()->toDateString();
        $this->copy();
    }

    public function copy(): void
    {
        $this->validate([
            'sourceDate' => ['required', 'date', 'different:targetDate'],
            'targetDate' => [
                'required',
                'date',
                'after_or_equal:2000-01-01',
                'before_or_equal:'.now()->addYears(2)->toDateString(),
            ],
        ], [
            'sourceDate.different' => 'Elige un día distinto al que estás viendo.',
            'targetDate.before_or_equal' => 'La fecha destino no puede ser más de 2 años en el futuro.',
        ], [
            'sourceDate' => 'fecha origen',
            'targetDate' => 'fecha destino',
        ]);

        try {
            app(RoutineCopyService::class)->copyDay($this->sourceDate, $this->targetDate);
        } catch (\InvalidArgumentException $exception) {
            $this->addError('sourceDate', $exception->getMessage());

            return;
        }

        $this->close();
        $this->dispatch('day-copied');
        $this->dispatch('notify', message: 'Día copiado correctamente');
    }

    public function render()
    {
        return view('livewire.copy-day', [
            'monthLabel' => $this->calendarMonth
                ? ucfirst(Carbon::parse($this->calendarMonth)->locale('es')->isoFormat('MMMM YYYY'))
                : '',
            'weeks' => $this->calendarWeeks(),
            'recentDays' => $this->recentDays(),
            'previousDate' => $this->targetDate
                ? Carbon::parse($this->targetDate)->subDay()
                : null,
        ]);
    }

    private function calendarWeeks(): Collection
    {
        if ($this->calendarMonth === '') {
            return collect();
        }

        $month = Carbon::parse($this->calendarMonth);
        $start = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $busyDates = Day::query()
            ->whereHas('activities')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString());

        $days = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $days->push([
                'date' => $cursor->toDateString(),
                'number' => $cursor->format('j'),
                'in_month' => $cursor->isSameMonth($month),
                'has_activities' => $busyDates->contains($cursor->toDateString()),
                'is_target' => $cursor->toDateString() === $this->targetDate,
                'selected' => $cursor->toDateString() === $this->sourceDate,
            ]);
            $cursor->addDay();
        }

        return $days->chunk(7);
    }

    private function recentDays(): Collection
    {
        if ($this->targetDate === '') {
            return collect();
        }

        return Day::query()
            ->withCount('activities')
            ->whereDate('date', '!=', $this->targetDate)
            ->whereHas('activities')
            ->orderByDesc('date')
            ->limit(5)
            ->get();
    }
}
