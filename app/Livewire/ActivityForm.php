<?php

namespace App\Livewire;

use App\Enums\ActivityStatus;
use App\Http\Requests\StoreActivityRequest;
use App\Models\Activity;
use App\Models\Day;
use App\Support\IconCatalog;
use App\Support\ScheduleTimes;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class ActivityForm extends Component
{
    public bool $open = false;

    public ?int $activityId = null;

    public string $name = '';

    public string $description = '';

    public string $date = '';

    public string $start_time = '08:00';

    public int $duration_minutes = 30;

    public string $icon = 'book-open';

    public string $color = 'violet';

    public string $status = 'pending';

    #[On('open-activity-form')]
    public function open(string $date, ?int $activityId = null): void
    {
        $this->resetValidation();
        $this->open = true;
        $this->activityId = $activityId;
        $this->date = $date;

        if ($activityId) {
            $this->fillFromActivity($activityId);

            return;
        }

        $this->name = '';
        $this->description = '';
        $this->start_time = '08:00';
        $this->duration_minutes = 30;
        $this->icon = 'book-open';
        $this->color = IconCatalog::defaultColor('book-open');
        $this->status = ActivityStatus::Pending->value;
    }

    public function close(): void
    {
        $this->open = false;
        $this->activityId = null;
    }

    public function selectIcon(string $icon): void
    {
        if (! in_array($icon, IconCatalog::keys(), true)) {
            return;
        }

        $this->icon = $icon;
        $this->color = IconCatalog::defaultColor($icon);
    }

    public function save(): void
    {
        $this->start_time = substr($this->start_time, 0, 5);
        $this->duration_minutes = (int) $this->duration_minutes;

        $data = $this->validate(
            StoreActivityRequest::ruleList(),
            StoreActivityRequest::messageList(),
            StoreActivityRequest::attributeList(),
        );

        $day = Day::forDate($data['date']);

        $alreadyOnDay = Activity::query()
            ->where('day_id', $day->id)
            ->when($this->activityId, fn ($query) => $query->where('id', '!=', $this->activityId))
            ->count();

        if ($alreadyOnDay >= Activity::MAX_PER_DAY) {
            $this->addError('name', 'Este día ya tiene el máximo de '.Activity::MAX_PER_DAY.' actividades.');

            return;
        }

        $payload = [
            'day_id' => $day->id,
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'icon' => $data['icon'] ?: 'sparkles',
            'color' => $data['color'] ?: IconCatalog::defaultColor($data['icon'] ?: 'sparkles'),
            'start_time' => $data['start_time'],
            'duration_minutes' => (int) $data['duration_minutes'],
            'end_time' => Activity::computeEndTime($data['start_time'], (int) $data['duration_minutes']),
            'status' => $data['status'],
        ];

        $oldDayId = null;

        if ($this->activityId) {
            $oldDayId = Activity::query()->whereKey($this->activityId)->value('day_id');
        }

        $activity = DB::transaction(function () use ($day, $payload, $oldDayId) {
            if ($this->activityId) {
                $activity = Activity::query()->findOrFail($this->activityId);
                $activity->update($payload);
            } else {
                $payload['position'] = Activity::nextPosition($day->id);
                $activity = $day->activities()->create($payload);
            }

            // Si cambia la hora o duración, las actividades de debajo se recorren.
            ScheduleTimes::rechainFromActivity($activity->fresh());

            if ($oldDayId && (int) $oldDayId !== (int) $activity->day_id) {
                $previousDay = Day::query()->find($oldDayId);

                if ($previousDay) {
                    ScheduleTimes::rechainDay($previousDay);
                }
            }

            return $activity;
        });

        $message = $this->activityId ? 'Actividad actualizada' : 'Actividad guardada';

        $this->close();
        $this->dispatch('activity-saved', date: $data['date']);
        $this->dispatch('notify', message: $message);
    }

    public function delete(): void
    {
        if (! $this->activityId) {
            return;
        }

        $activity = Activity::query()
            ->whereHas('day', fn ($query) => $query->whereDate('date', $this->date))
            ->findOrFail($this->activityId);

        $day = $activity->day;

        DB::transaction(function () use ($activity, $day) {
            $activity->delete();
            ScheduleTimes::rechainDay($day);
        });

        $this->close();
        $this->dispatch('activity-saved');
        $this->dispatch('notify', message: 'Actividad eliminada');
    }

    public function render()
    {
        return view('livewire.activity-form', [
            'icons' => IconCatalog::all(),
            'durations' => IconCatalog::durations(),
            'statuses' => ActivityStatus::cases(),
            'isEditing' => (bool) $this->activityId,
            'isFirst' => $this->isFirstActivity(),
        ]);
    }

    private function fillFromActivity(int $activityId): void
    {
        $activity = Activity::with('day')->findOrFail($activityId);

        $this->activityId = $activity->id;
        $this->name = $activity->name;
        $this->description = $activity->description ?? '';
        $this->date = $activity->day->date->toDateString();
        $this->start_time = $activity->formattedStart() ?? '08:00';
        $this->duration_minutes = $activity->duration_minutes ?: 30;
        $this->icon = $activity->icon ?: 'book-open';
        $this->color = $activity->color ?: IconCatalog::defaultColor($this->icon);
        $this->status = $activity->status->value;
    }

    private function isFirstActivity(): bool
    {
        if (! $this->activityId) {
            return false;
        }

        return (int) Activity::query()->whereKey($this->activityId)->value('position') === 1;
    }
}
