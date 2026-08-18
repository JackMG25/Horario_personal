<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateActivity extends Model
{
    protected $fillable = [
        'template_id',
        'name',
        'description',
        'icon',
        'color',
        'start_time',
        'end_time',
        'duration_minutes',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'position' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
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
}
