<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Day extends Model
{
    protected $fillable = [
        'date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderBy('position')->orderBy('id');
    }

    public static function forDate(string $date): self
    {
        $normalized = Carbon::parse($date)->toDateString();

        return self::firstOrCreate(['date' => $normalized]);
    }
}
