<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Day;

class ScheduleTimes
{
    public static function toMinutes(string $time): int
    {
        $parts = explode(':', substr($time, 0, 5));
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);

        return ($hours * 60) + $minutes;
    }

    public static function fromMinutes(int $minutes): string
    {
        $minutes = max(0, min(24 * 60, $minutes));

        if ($minutes === 24 * 60) {
            return '00:00';
        }

        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    public static function endsWithinDay(string $start, int $duration): bool
    {
        return self::toMinutes($start) + $duration <= 24 * 60;
    }

    public static function durationOf(object $activity): int
    {
        if ($activity->duration_minutes) {
            return (int) $activity->duration_minutes;
        }

        $start = $activity->formattedStart();
        $end = $activity->formattedEnd();

        if ($start && $end) {
            $diff = self::toMinutes($end) - self::toMinutes($start);

            return $diff > 0 ? $diff : 30;
        }

        return 30;
    }

    /**
     * Encadena horas sin cambiar el orden: cada actividad empieza
     * cuando termina la anterior, conservando su duración.
     *
     * @param  list<Activity|TemplateActivity>  $activitiesInOrder
     */
    public static function rechainTimes(array $activitiesInOrder, ?string $anchorStart = null): void
    {
        if ($activitiesInOrder === []) {
            return;
        }

        $first = $activitiesInOrder[0];
        $cursor = $anchorStart
            ? self::toMinutes($anchorStart)
            : ($first->formattedStart() ? self::toMinutes($first->formattedStart()) : self::toMinutes('08:00'));

        foreach ($activitiesInOrder as $activity) {
            $duration = self::durationOf($activity);
            $endMinutes = $cursor + $duration;

            if ($endMinutes > 24 * 60) {
                $endMinutes = 24 * 60;
                $duration = max(5, $endMinutes - $cursor);
            }

            $activity->update([
                'start_time' => self::fromMinutes($cursor),
                'end_time' => self::fromMinutes($endMinutes),
                'duration_minutes' => $duration,
            ]);

            $cursor = $endMinutes;
        }
    }

    /**
     * Recalcula horas según el nuevo orden: la primera empieza a la hora
     * más temprana del día y las demás se encadenan por su duración.
     *
     * @param  list<Activity>  $activitiesInNewOrder
     */
    public static function redistributeByOrder(array $activitiesInNewOrder): void
    {
        foreach ($activitiesInNewOrder as $index => $activity) {
            $activity->position = $index + 1;
            $activity->save();
        }

        $starts = [];

        foreach ($activitiesInNewOrder as $activity) {
            if ($activity->formattedStart()) {
                $starts[] = self::toMinutes($activity->formattedStart());
            }
        }

        $anchor = $starts !== [] ? self::fromMinutes(min($starts)) : '08:00';

        self::rechainTimes($activitiesInNewOrder, $anchor);
    }

    /**
     * Actualiza la actividad editada y todas las que van debajo.
     */
    public static function rechainFromActivity(Activity $activity): void
    {
        $day = $activity->day()
            ->with(['activities' => fn ($query) => $query->orderBy('position')->orderBy('id')])
            ->first();

        if (! $day) {
            return;
        }

        $ordered = $day->activities;
        $index = $ordered->search(fn (Activity $item) => (int) $item->id === (int) $activity->id);

        if ($index === false) {
            return;
        }

        $fromHere = $ordered->slice($index)->values()->all();
        $fromHere[0] = $activity->fresh() ?? $activity;

        self::rechainTimes($fromHere, $activity->formattedStart());
    }

    public static function rechainDay(Day $day): void
    {
        $activities = $day->activities()
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->all();

        self::rechainTimes($activities);
    }

    public static function endTime(string $start, int $duration): string
    {
        $start = substr($start, 0, 5);
        $endMinutes = self::toMinutes($start !== '' ? $start : '08:00') + max(0, $duration);

        return self::fromMinutes($endMinutes);
    }

    /**
     * Encadena horas de un arreglo (formulario de plantillas).
     * Si la primera empieza a las 06:00 y dura 1 h, la siguiente pasa a las 07:00.
     *
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    public static function rechainItemTimes(array $items, int $fromIndex = 0, ?string $anchorStart = null): array
    {
        if ($items === [] || ! isset($items[$fromIndex])) {
            return $items;
        }

        $start = $anchorStart
            ?? (isset($items[$fromIndex]['start_time']) ? substr((string) $items[$fromIndex]['start_time'], 0, 5) : null)
            ?? '08:00';

        $cursor = self::toMinutes($start !== '' ? $start : '08:00');

        for ($index = $fromIndex, $total = count($items); $index < $total; $index++) {
            $duration = (int) ($items[$index]['duration_minutes'] ?? 30);

            if ($duration <= 0) {
                $duration = 30;
            }

            $endMinutes = $cursor + $duration;

            if ($endMinutes > 24 * 60) {
                $endMinutes = 24 * 60;
                $duration = max(5, $endMinutes - $cursor);
            }

            $items[$index]['start_time'] = self::fromMinutes($cursor);
            $items[$index]['duration_minutes'] = $duration;
            $cursor = $endMinutes;
        }

        return $items;
    }

    /**
     * Al reordenar, mantiene la hora más temprana y encadena el resto.
     *
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    public static function redistributeItemTimes(array $items): array
    {
        $starts = [];

        foreach ($items as $item) {
            $start = substr((string) ($item['start_time'] ?? ''), 0, 5);

            if ($start !== '') {
                $starts[] = self::toMinutes($start);
            }
        }

        $anchor = $starts !== [] ? self::fromMinutes(min($starts)) : '08:00';

        return self::rechainItemTimes($items, 0, $anchor);
    }
}
