<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Template;
use App\Support\IconCatalog;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Día de trabajo',
                'description' => 'Plantilla ideal para días laborales con horario completo.',
                'icon' => 'briefcase',
                'activities' => [
                    ['name' => 'Devocional', 'icon' => 'sun', 'start_time' => '07:00', 'duration_minutes' => 30],
                    ['name' => 'Trabajo', 'icon' => 'briefcase', 'start_time' => '08:00', 'duration_minutes' => 480],
                    ['name' => 'Almuerzo', 'icon' => 'cake', 'start_time' => '13:00', 'duration_minutes' => 60],
                    ['name' => 'Ejercicio', 'icon' => 'fire', 'start_time' => '18:00', 'duration_minutes' => 45],
                    ['name' => 'Estudiar', 'icon' => 'academic-cap', 'start_time' => '19:00', 'duration_minutes' => 90],
                    ['name' => 'Tiempo personal', 'icon' => 'heart', 'start_time' => '21:00', 'duration_minutes' => 60],
                ],
            ],
            [
                'name' => 'Día libre',
                'description' => 'Un día más relajado, con espacio para casa y descanso.',
                'icon' => 'home',
                'activities' => [
                    ['name' => 'Devocional', 'icon' => 'sun', 'start_time' => '08:00', 'duration_minutes' => 30],
                    ['name' => 'Desayuno', 'icon' => 'cake', 'start_time' => '08:30', 'duration_minutes' => 45],
                    ['name' => 'Tareas de casa', 'icon' => 'home', 'start_time' => '10:00', 'duration_minutes' => 120],
                    ['name' => 'Ejercicio', 'icon' => 'fire', 'start_time' => '17:00', 'duration_minutes' => 45],
                    ['name' => 'Tiempo personal', 'icon' => 'heart', 'start_time' => '20:00', 'duration_minutes' => 90],
                ],
            ],
            [
                'name' => 'Día de estudio',
                'description' => 'Enfoque en lectura, clases y práctica.',
                'icon' => 'academic-cap',
                'activities' => [
                    ['name' => 'Devocional', 'icon' => 'sun', 'start_time' => '07:00', 'duration_minutes' => 30],
                    ['name' => 'Estudio profundo', 'icon' => 'book-open', 'start_time' => '08:00', 'duration_minutes' => 180],
                    ['name' => 'Almuerzo', 'icon' => 'cake', 'start_time' => '13:00', 'duration_minutes' => 60],
                    ['name' => 'Práctica', 'icon' => 'computer-desktop', 'start_time' => '15:00', 'duration_minutes' => 180],
                    ['name' => 'Repaso', 'icon' => 'academic-cap', 'start_time' => '20:00', 'duration_minutes' => 60],
                ],
            ],
            [
                'name' => 'Domingo / Iglesia',
                'description' => 'Rutina de domingo con servicio y descanso.',
                'icon' => 'sparkles',
                'activities' => [
                    ['name' => 'Devocional', 'icon' => 'sun', 'start_time' => '07:30', 'duration_minutes' => 30],
                    ['name' => 'Iglesia', 'icon' => 'sparkles', 'start_time' => '09:00', 'duration_minutes' => 180],
                    ['name' => 'Almuerzo familiar', 'icon' => 'cake', 'start_time' => '13:00', 'duration_minutes' => 90],
                    ['name' => 'Descanso', 'icon' => 'moon', 'start_time' => '16:00', 'duration_minutes' => 120],
                    ['name' => 'Tiempo personal', 'icon' => 'heart', 'start_time' => '19:00', 'duration_minutes' => 60],
                ],
            ],
            [
                'name' => 'Viaje',
                'description' => 'Estructura ligera para días fuera de casa.',
                'icon' => 'truck',
                'activities' => [
                    ['name' => 'Preparar maleta', 'icon' => 'shopping-bag', 'start_time' => '07:00', 'duration_minutes' => 60],
                    ['name' => 'Traslado', 'icon' => 'truck', 'start_time' => '08:30', 'duration_minutes' => 180],
                    ['name' => 'Comida', 'icon' => 'cake', 'start_time' => '13:00', 'duration_minutes' => 60],
                    ['name' => 'Explorar', 'icon' => 'sparkles', 'start_time' => '15:00', 'duration_minutes' => 180],
                    ['name' => 'Descanso', 'icon' => 'moon', 'start_time' => '21:00', 'duration_minutes' => 60],
                ],
            ],
        ];

        foreach ($templates as $data) {
            $activities = $data['activities'];
            unset($data['activities']);

            $data['color'] = IconCatalog::defaultColor($data['icon']);

            $template = Template::query()->updateOrCreate(
                ['name' => $data['name']],
                $data,
            );

            $template->activities()->delete();

            foreach ($activities as $index => $activity) {
                $template->activities()->create([
                    'name' => $activity['name'],
                    'icon' => $activity['icon'],
                    'color' => IconCatalog::defaultColor($activity['icon']),
                    'start_time' => $activity['start_time'],
                    'duration_minutes' => $activity['duration_minutes'],
                    'end_time' => Activity::computeEndTime($activity['start_time'], $activity['duration_minutes']),
                    'position' => $index + 1,
                ]);
            }
        }
    }
}
