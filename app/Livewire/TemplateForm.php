<?php

namespace App\Livewire;

use App\Http\Requests\StoreTemplateRequest;
use App\Models\Activity;
use App\Models\Template;
use App\Support\IconCatalog;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class TemplateForm extends Component
{
    public ?int $templateId = null;

    public string $name = '';

    public string $description = '';

    public string $icon = 'briefcase';

    public string $color = 'blue';

    public array $items = [];

    #[Url]
    public ?string $fecha = null;

    public function mount(?Template $template = null): void
    {
        if ($this->fecha === null) {
            $this->fecha = request('fecha');
        }

        if ($template?->exists) {
            $this->templateId = $template->id;
            $this->name = $template->name;
            $this->description = $template->description ?? '';
            $this->icon = $template->icon ?: 'briefcase';
            $this->color = $template->color ?: IconCatalog::defaultColor($this->icon);
            $this->items = $template->activities()
                ->orderBy('position')
                ->get()
                ->map(fn ($activity) => [
                    'name' => $activity->name,
                    'description' => $activity->description ?? '',
                    'icon' => $activity->icon ?: 'sparkles',
                    'start_time' => $activity->formattedStart() ?? '08:00',
                    'duration_minutes' => $activity->duration_minutes ?: 30,
                ])
                ->all();

            return;
        }

        $this->addItem();
    }

    public function selectIcon(string $icon): void
    {
        if (! in_array($icon, IconCatalog::keys(), true)) {
            return;
        }

        $this->icon = $icon;
        $this->color = IconCatalog::defaultColor($icon);
    }

    public function selectItemIcon(int $index, string $icon): void
    {
        if (! isset($this->items[$index]) || ! in_array($icon, IconCatalog::keys(), true)) {
            return;
        }

        $this->items[$index]['icon'] = $icon;
    }

    public function addItem(): void
    {
        if (count($this->items) >= 20) {
            $this->addError('items', 'Una plantilla no puede tener más de 20 actividades.');

            return;
        }

        $this->items[] = [
            'name' => '',
            'description' => '',
            'icon' => 'sparkles',
            'start_time' => '08:00',
            'duration_minutes' => 30,
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1) {
            $this->addError('items', 'La plantilla debe tener al menos una actividad.');

            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function reorderItems(array $order): void
    {
        $reordered = [];

        foreach ($order as $index) {
            if (isset($this->items[(int) $index])) {
                $reordered[] = $this->items[(int) $index];
            }
        }

        if (count($reordered) === count($this->items)) {
            $this->items = $reordered;
        }
    }

    public function save(): void
    {
        $data = $this->validate(
            StoreTemplateRequest::ruleList($this->templateId),
            StoreTemplateRequest::messageList(),
            StoreTemplateRequest::attributeList(),
        );

        $template = DB::transaction(function () use ($data) {
            $template = $this->templateId
                ? Template::query()->findOrFail($this->templateId)
                : new Template;

            $template->fill([
                'name' => $data['name'],
                'description' => $data['description'] ?: null,
                'icon' => $data['icon'] ?: 'briefcase',
                'color' => $data['color'] ?: IconCatalog::defaultColor($data['icon'] ?: 'briefcase'),
            ])->save();

            $template->activities()->delete();

            foreach ($data['items'] as $index => $item) {
                $template->activities()->create([
                    'name' => $item['name'],
                    'description' => $item['description'] ?: null,
                    'icon' => $item['icon'] ?: 'sparkles',
                    'color' => IconCatalog::defaultColor($item['icon'] ?: 'sparkles'),
                    'start_time' => $item['start_time'] ?: null,
                    'duration_minutes' => $item['duration_minutes'] ?: null,
                    'end_time' => Activity::computeEndTime($item['start_time'] ?? null, $item['duration_minutes'] ?? null),
                    'position' => $index + 1,
                ]);
            }

            return $template;
        });

        session()->flash('notify', 'Plantilla guardada');

        $this->redirectRoute('templates.show', [
            'template' => $template,
            'fecha' => $this->fecha,
        ], navigate: true);
    }

    public function render()
    {
        return view('livewire.template-form', [
            'icons' => IconCatalog::all(),
            'durations' => IconCatalog::durations(),
            'isEditing' => (bool) $this->templateId,
        ])->title($this->templateId ? 'Editar plantilla' : 'Nueva plantilla');
    }
}
