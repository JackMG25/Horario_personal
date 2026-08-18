<?php

namespace App\Http\Requests;

use App\Rules\DurationFitsInDay;
use App\Support\IconCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTemplateRequest extends FormRequest
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

    public static function ruleList(?int $templateId = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:80',
                Rule::unique('templates', 'name')->ignore($templateId),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['required', 'string', Rule::in(IconCatalog::keys())],
            'color' => ['nullable', 'string', Rule::in(IconCatalog::colorKeys())],
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.name' => ['required', 'string', 'min:2', 'max:80'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.icon' => ['required', 'string', Rule::in(IconCatalog::keys())],
            'items.*.start_time' => ['required', 'date_format:H:i'],
            'items.*.duration_minutes' => [
                'required',
                'integer',
                Rule::in(IconCatalog::durationKeys()),
                new DurationFitsInDay,
            ],
        ];
    }

    public static function attributeList(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'icon' => 'icono',
            'items' => 'actividades',
            'items.*.name' => 'nombre de la actividad',
            'items.*.icon' => 'icono',
            'items.*.start_time' => 'hora de inicio',
            'items.*.duration_minutes' => 'duración',
        ];
    }

    public static function messageList(): array
    {
        return [
            'name.required' => 'Escribe un nombre para la plantilla.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.unique' => 'Ya existe una plantilla con ese nombre.',
            'items.required' => 'Agrega al menos una actividad.',
            'items.min' => 'Agrega al menos una actividad.',
            'items.max' => 'Una plantilla no puede tener más de 20 actividades.',
            'items.*.name.required' => 'Cada actividad necesita un nombre.',
            'items.*.name.min' => 'El nombre de la actividad debe tener al menos 2 caracteres.',
            'items.*.start_time.required' => 'Cada actividad necesita una hora de inicio.',
            'items.*.duration_minutes.required' => 'Cada actividad necesita una duración.',
            'items.*.duration_minutes.in' => 'Hay una duración que no es válida.',
            'items.*.icon.required' => 'Cada actividad necesita un icono.',
        ];
    }
}
