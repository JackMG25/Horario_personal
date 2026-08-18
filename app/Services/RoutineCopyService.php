<?php

namespace App\Services;

use App\Enums\ActivityStatus;
use App\Models\Activity;
use App\Models\Day;
use App\Models\Template;
use Illuminate\Support\Facades\DB;

class RoutineCopyService
{
    /**
     * Copia las actividades de una plantilla a una fecha concreta.
     * Las copias quedan independientes: editar el día no cambia la plantilla.
     */
    public function applyTemplate(Template $template, string $date): Day
    {
        $template->load('activities');

        if ($template->activities->isEmpty()) {
            throw new \InvalidArgumentException('Esta plantilla no tiene actividades para copiar.');
        }

        return DB::transaction(function () use ($template, $date) {
            $day = Day::forDate($date);

            if ($day->activities()->count() + $template->activities->count() > Activity::MAX_PER_DAY) {
                throw new \InvalidArgumentException('Este día no tiene espacio para tantas actividades.');
            }

            $position = Activity::nextPosition($day->id);

            foreach ($template->activities as $item) {
                $day->activities()->create([
                    'name' => $item->name,
                    'description' => $item->description,
                    'icon' => $item->icon,
                    'color' => $item->color,
                    'start_time' => $item->formattedStart(),
                    'end_time' => $item->formattedEnd(),
                    'duration_minutes' => $item->duration_minutes,
                    'position' => $position++,
                    'status' => ActivityStatus::Pending,
                ]);
            }

            return $day;
        });
    }

    /**
     * Duplica las actividades de un día hacia otro, siempre en estado pendiente.
     */
    public function copyDay(string $fromDate, string $toDate): Day
    {
        $source = Day::query()
            ->with(['activities' => fn ($query) => $query->orderBy('position')->orderBy('id')])
            ->whereDate('date', $fromDate)
            ->first();

        if (! $source || $source->activities->isEmpty()) {
            throw new \InvalidArgumentException('El día origen no tiene actividades para copiar.');
        }

        return DB::transaction(function () use ($source, $toDate) {
            $day = Day::forDate($toDate);

            if ($day->activities()->count() + $source->activities->count() > Activity::MAX_PER_DAY) {
                throw new \InvalidArgumentException('Este día no tiene espacio para tantas actividades.');
            }

            $position = Activity::nextPosition($day->id);

            foreach ($source->activities as $activity) {
                $day->activities()->create([
                    'name' => $activity->name,
                    'description' => $activity->description,
                    'icon' => $activity->icon,
                    'color' => $activity->color,
                    'start_time' => $activity->formattedStart(),
                    'end_time' => $activity->formattedEnd(),
                    'duration_minutes' => $activity->duration_minutes,
                    'position' => $position++,
                    'status' => ActivityStatus::Pending,
                ]);
            }

            return $day;
        });
    }

    public function duplicateTemplate(Template $template): Template
    {
        $template->load('activities');

        return DB::transaction(function () use ($template) {
            $copy = $template->replicate();
            $copy->name = $template->name.' (copia)';
            $copy->save();

            foreach ($template->activities as $activity) {
                $copy->activities()->create($activity->only([
                    'name',
                    'description',
                    'icon',
                    'color',
                    'start_time',
                    'end_time',
                    'duration_minutes',
                    'position',
                ]));
            }

            return $copy;
        });
    }
}
