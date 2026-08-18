<?php

namespace App\Http\Requests;

use App\Enums\ActivityStatus;
use App\Rules\DurationFitsInDay;
use App\Support\IconCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::ruleList();
    }

    public function attributes(): array
    {
        return self::attributeList();
    }

    public function messages(): array
    {
        return self::messageList();
    }

    public static function ruleList(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => [
                'required',
                'date',
                'after_or_equal:2000-01-01',
                'before_or_equal:'.now()->addYears(2)->toDateString(),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => [
                'required',
                'integer',
                Rule::in(IconCatalog::durationKeys()),
                new DurationFitsInDay,
            ],
            'icon' => ['required', 'string', Rule::in(IconCatalog::keys())],
            'color' => ['nullable', 'string', Rule::in(IconCatalog::colorKeys())],
            'status' => ['required', Rule::enum(ActivityStatus::class)],
        ];
    }

    public static function attributeList(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'date' => 'fecha',
            'start_time' => 'hora de inicio',
            'duration_minutes' => 'duración',
            'icon' => 'icono',
            'status' => 'estado',
        ];
    }

    public static function messageList(): array
    {
        return [
            'name.required' => 'Escribe un nombre para la actividad.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre no puede superar los 80 caracteres.',
            'date.required' => 'Selecciona una fecha.',
            'date.after_or_equal' => 'La fecha es demasiado antigua.',
            'date.before_or_equal' => 'La fecha no puede ser más de 2 años en el futuro.',
            'start_time.required' => 'Indica la hora de inicio.',
            'start_time.date_format' => 'La hora de inicio no es válida.',
            'duration_minutes.required' => 'Selecciona una duración.',
            'duration_minutes.in' => 'La duración seleccionada no es válida.',
            'icon.required' => 'Selecciona un icono.',
            'icon.in' => 'El icono seleccionado no es válido.',
            'status.required' => 'Selecciona un estado.',
            'status.Illuminate\Validation\Rules\Enum' => 'El estado seleccionado no es válido.',
        ];
    }
}
