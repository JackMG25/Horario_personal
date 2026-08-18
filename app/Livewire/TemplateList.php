<?php

namespace App\Livewire;

use App\Models\Template;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Plantillas')]
class TemplateList extends Component
{
    #[Url]
    public ?string $fecha = null;

    public function delete(int $templateId): void
    {
        Template::query()->findOrFail($templateId)->delete();
        $this->dispatch('notify', message: 'Plantilla eliminada');
    }

    public function render()
    {
        return view('livewire.template-list', [
            'templates' => Template::query()
                ->withCount('activities')
                ->orderBy('name')
                ->get(),
            'applyDate' => $this->fecha,
        ]);
    }
}
