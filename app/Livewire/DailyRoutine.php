<?php

namespace App\Livewire;

use App\Enums\ActivityStatus;
use App\Models\Activity;
use App\Models\Day;
use App\Support\ScheduleTimes;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Hoy')]
class DailyRoutine extends Component
{
    #[Url(as: 'fecha', except: '')]
    public string $date = '';

    public string $weekStart = '';

    public function mount(): void
    {
        if ($this->date === '' || ! $this->isValidDate($this->date)) {
            $this->date = now()->toDateString();
        }

        $this->alignWeekToDate();
    }

    public function selectDate(string $date): void
    {
        if (! $this->isValidDate($date)) {
            return;
        }

        $this->date = $date;
        $this->alignWeekToDate();
    }

    public function updatedDate(string $value): void
    {
        if (! $this->isValidDate($value)) {
            $this->date = now()->toDateString();
        }

        $this->alignWeekToDate();
    }

    public function previousDay(): void
    {
        $this->selectDate(Carbon::parse($this->date)->subDay()->toDateString());
    }

    public function nextDay(): void
    {
        $this->selectDate(Carbon::parse($this->date)->addDay()->toDateString());
    }

    public function previousWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->toDateString();
    }

    public function nextWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->addWeek()->toDateString();
    }

    public function goToToday(): void
    {
        $this->selectDate(now()->toDateString());
    }

    public function openCreate(): void
    {
        $this->dispatch('open-activity-form', date: $this->date);
    }

    public function openEdit(int $activityId): void
    {
        $this->dispatch('open-activity-form', date: $this->date, activityId: $activityId);
    }

    public function openCopyDay(): void
    {
        $this->dispatch('open-copy-day', date: $this->date);
    }

    public function toggleComplete(int $activityId): void
    {
        $activity = $this->activityForCurrentDay($activityId);

        $activity->update([
            'status' => $activity->status === ActivityStatus::Completed
                ? ActivityStatus::Pending
                : ActivityStatus::Completed,
        ]);
    }

    public function reorder(array $order): void
    {
        $day = $this->day;

        if (! $day) {
            return;
        }

        $validIds = $day->activities->pluck('id')->map(fn ($id) => (int) $id)->all();
        $order = array_values(array_unique(array_map('intval', $order)));

        sort($validIds);
        $sortedOrder = $order;
        sort($sortedOrder);

        if ($sortedOrder !== $validIds) {
            $this->dispatch('notify', message: 'No se pudo actualizar el orden.');

            return;
        }

        $byId = $day->activities->keyBy('id');
        $ordered = [];

        foreach ($order as $id) {
            $ordered[] = $byId[$id];
        }

        DB::transaction(function () use ($ordered) {
            // Las horas siguen el nuevo orden: la primera empieza más temprano
            // y las siguientes se encadenan según su duración.
            ScheduleTimes::redistributeByOrder($ordered);
        });

        unset($this->day, $this->activities);

        $this->dispatch('notify', message: 'Orden actualizado');
    }

    #[On('activity-saved')]
    #[On('day-copied')]
    #[On('template-applied')]
    public function refreshDay(?string $date = null): void
    {
        if ($date) {
            $this->selectDate($date);
        }

        unset($this->day, $this->activities);
    }

    #[Computed]
    public function selected(): Carbon
    {
        return Carbon::parse($this->date)->locale('es');
    }

    #[Computed]
    public function isToday(): bool
    {
        return $this->selected->isToday();
    }

    #[Computed]
    public function weekDays(): Collection
    {
        $start = Carbon::parse($this->weekStart)->locale('es');

        $labels = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];

        return collect(range(0, 6))->map(function (int $offset) use ($start, $labels) {
            $day = $start->copy()->addDays($offset);

            return [
                'date' => $day->toDateString(),
                'label' => $labels[$day->dayOfWeekIso],
                'number' => $day->format('j'),
                'is_today' => $day->isToday(),
                'selected' => $day->toDateString() === $this->date,
            ];
        });
    }

    #[Computed]
    public function day(): ?Day
    {
        $day = Day::query()
            ->with(['activities' => fn ($query) => $query->orderBy('position')->orderBy('id')])
            ->whereDate('date', $this->date)
            ->first();

        // Evita N+1 al calcular "en curso": cada actividad ya conoce su día sin otra consulta.
        $day?->activities->each(fn (Activity $activity) => $activity->setRelation('day', $day));

        return $day;
    }

    #[Computed]
    public function activities(): Collection
    {
        return $this->day?->activities ?? collect();
    }

    public function render()
    {
        return view('livewire.daily-routine');
    }

    private function alignWeekToDate(): void
    {
        $this->weekStart = Carbon::parse($this->date)
            ->startOfWeek(Carbon::MONDAY)
            ->toDateString();
    }

    private function isValidDate(string $date): bool
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            return false;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));

        return checkdate($month, $day, $year);
    }

    private function activityForCurrentDay(int $activityId): Activity
    {
        return Activity::query()
            ->whereHas('day', fn ($query) => $query->whereDate('date', $this->date))
            ->findOrFail($activityId);
    }
}
