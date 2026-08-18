<?php

namespace App\Support;

class IconCatalog
{
    /**
     * Iconos de Heroicons disponibles para actividades.
     * Se guarda el "key" en base de datos, nunca el SVG.
     *
     * @return list<array{key: string, color: string, label: string}>
     */
    public static function all(): array
    {
        return [
            ['key' => 'sun', 'color' => 'orange', 'label' => 'Sol'],
            ['key' => 'briefcase', 'color' => 'blue', 'label' => 'Trabajo'],
            ['key' => 'home', 'color' => 'green', 'label' => 'Casa'],
            ['key' => 'book-open', 'color' => 'violet', 'label' => 'Lectura'],
            ['key' => 'cake', 'color' => 'amber', 'label' => 'Comida'],
            ['key' => 'fire', 'color' => 'red', 'label' => 'Ejercicio'],
            ['key' => 'academic-cap', 'color' => 'indigo', 'label' => 'Estudio'],
            ['key' => 'heart', 'color' => 'rose', 'label' => 'Personal'],
            ['key' => 'sparkles', 'color' => 'purple', 'label' => 'Iglesia'],
            ['key' => 'truck', 'color' => 'teal', 'label' => 'Viaje'],
            ['key' => 'computer-desktop', 'color' => 'sky', 'label' => 'Computadora'],
            ['key' => 'musical-note', 'color' => 'fuchsia', 'label' => 'Música'],
            ['key' => 'moon', 'color' => 'slate', 'label' => 'Descanso'],
            ['key' => 'users', 'color' => 'cyan', 'label' => 'Social'],
            ['key' => 'shopping-bag', 'color' => 'lime', 'label' => 'Compras'],
        ];
    }

    public static function keys(): array
    {
        return array_column(self::all(), 'key');
    }

    public static function defaultColor(string $icon): string
    {
        foreach (self::all() as $item) {
            if ($item['key'] === $icon) {
                return $item['color'];
            }
        }

        return 'violet';
    }

    /**
     * @return array<int, string>
     */
    public static function durations(): array
    {
        return [
            15 => '15 minutos',
            30 => '30 minutos',
            45 => '45 minutos',
            60 => '1 hora',
            90 => '1 h 30 min',
            120 => '2 horas',
            180 => '3 horas',
            240 => '4 horas',
            480 => '8 horas',
        ];
    }

    public static function durationKeys(): array
    {
        return array_map('intval', array_keys(self::durations()));
    }

    public static function colorKeys(): array
    {
        return array_keys(self::colorClasses());
    }

    /**
     * Clases Tailwind fijas para el círculo del icono.
     * Tailwind 4 solo incluye clases que aparecen en el código fuente.
     *
     * @return array<string, string>
     */
    public static function colorClasses(): array
    {
        return [
            'orange' => 'bg-orange-100 text-orange-600',
            'blue' => 'bg-blue-100 text-blue-600',
            'green' => 'bg-green-100 text-green-600',
            'violet' => 'bg-violet-100 text-violet-600',
            'amber' => 'bg-amber-100 text-amber-600',
            'red' => 'bg-red-100 text-red-600',
            'indigo' => 'bg-indigo-100 text-indigo-600',
            'rose' => 'bg-rose-100 text-rose-600',
            'purple' => 'bg-purple-100 text-purple-600',
            'teal' => 'bg-teal-100 text-teal-600',
            'sky' => 'bg-sky-100 text-sky-600',
            'fuchsia' => 'bg-fuchsia-100 text-fuchsia-600',
            'slate' => 'bg-slate-100 text-slate-600',
            'cyan' => 'bg-cyan-100 text-cyan-600',
            'lime' => 'bg-lime-100 text-lime-700',
        ];
    }
}
