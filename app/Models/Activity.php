<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    public const MAX_PER_DAY = 30;

    protected $fillable = [
        'day_id',
        'name',
        'description',
        'icon',
        'color',
        'start_time',
        'end_time',
        'duration_minutes',
        'position',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ActivityStatus::class,
            'duration_minutes' => 'integer',
            'position' => 'integer',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(Day::class);
    }

    public function formattedStart(): ?string
    {
        return $this->formatTime($this->attributes['start_time'] ?? null);
    }

    public function formattedEnd(): ?string
    {
        return $this->formatTime($this->attributes['end_time'] ?? null);
    }

    public function timeRange(): ?string
    {
        $start = $this->formattedStart();

        if (! $start) {
            return $this->duration_minutes ? $this->duration_minutes.' min' : null;
        }

        $end = $this->formattedEnd();

        return $end ? $start.' - '.$end : $start;
    }

    /**
     * Estado visual: si está pendiente y ahora cae en su horario, se muestra "En curso".
     */
    public function visualStatus(): ActivityStatus
    {
        if ($this->status !== ActivityStatus::Pending) {
            return $this->status;
        }

        if (! $this->isHappeningNow()) {
            return ActivityStatus::Pending;
        }

        return ActivityStatus::InProgress;
    }

    public function isHappeningNow(): bool
    {
        $day = $this->relationLoaded('day') ? $this->day : $this->day()->first();

        if (! $day?->date?->isToday()) {
            return false;
        }

        $start = $this->formattedStart();
        $end = $this->formattedEnd();

        if (! $start || ! $end) {
            return false;
        }

        $now = now();

        try {
            $startAt = $day->date->copy()->setTimeFromTimeString($start.':00');
            $endAt = $day->date->copy()->setTimeFromTimeString($end.':00');
        } catch (\Throwable) {
            return false;
        }

        return $now->between($startAt, $endAt);
    }

    public static function nextPosition(int $dayId): int
    {
        return (int) static::query()->where('day_id', $dayId)->max('position') + 1;
    }

    private function formatTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('H:i');
        }

        return substr((string) $value, 0, 5);
    }

    public static function computeEndTime(?string $startTime, ?int $durationMinutes): ?string
    {
        if (! $startTime || ! $durationMinutes) {
            return null;
        }

        return Carbon::parse($startTime)->addMinutes($durationMinutes)->format('H:i');
    }
}
