<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(TemplateActivity::class)->orderBy('position')->orderBy('id');
    }
}
