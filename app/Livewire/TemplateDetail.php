<?php

namespace App\Livewire;

use App\Models\Template;
use App\Services\RoutineCopyService;
use App\Support\ScheduleTimes;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Plantilla')]
class TemplateDetail extends Component
{
    public Template $template;

    #[Url]
    public ?string $fecha = null;

    public function mount(Template $template): void
    {
        $this->template = $template->load([
            'activities' => fn ($query) => $query->orderBy('position')->orderBy('id'),
        ]);
        $this->fecha ??= now()->toDateString();
    }

    public function reorder(array $order): void
    {
        $this->template->load([
            'activities' => fn ($query) => $query->orderBy('position')->orderBy('id'),
        ]);

        $validIds = $this->template->activities->pluck('id')->map(fn ($id) => (int) $id)->all();
        $order = array_values(array_unique(array_map('intval', $order)));

        $sortedValid = $validIds;
        $sortedOrder = $order;
        sort($sortedValid);
        sort($sortedOrder);

        if ($sortedOrder !== $sortedValid) {
            $this->dispatch('notify', message: 'No se pudo actualizar el orden.');

            return;
        }

        $byId = $this->template->activities->keyBy('id');
        $ordered = [];

        foreach ($order as $id) {
            $ordered[] = $byId[$id];
        }

        DB::transaction(function () use ($ordered) {
            ScheduleTimes::redistributeByOrder($ordered);
        });

        $this->template->refresh()->load([
            'activities' => fn ($query) => $query->orderBy('position')->orderBy('id'),
        ]);

        $this->dispatch('notify', message: 'Orden actualizado');
    }

    public function apply(): void
    {
        $this->validate([
            'fecha' => [
                'required',
                'date',
                'after_or_equal:2000-01-01',
                'before_or_equal:'.now()->addYears(2)->toDateString(),
            ],
        ], [
            'fecha.required' => 'Selecciona una fecha para aplicar la plantilla.',
            'fecha.before_or_equal' => 'La fecha no puede ser más de 2 años en el futuro.',
        ], [
            'fecha' => 'fecha',
        ]);

        if ($this->template->activities->isEmpty()) {
            $this->addError('fecha', 'Esta plantilla no tiene actividades para copiar.');

            return;
        }

        try {
            app(RoutineCopyService::class)->applyTemplate($this->template, $this->fecha);
        } catch (\InvalidArgumentException $exception) {
            $this->addError('fecha', $exception->getMessage());

            return;
        }

        session()->flash('notify', 'Plantilla aplicada');

        $this->redirectRoute('home', ['fecha' => $this->fecha], navigate: true);
    }

    public function duplicate(): void
    {
        $copy = app(RoutineCopyService::class)->duplicateTemplate($this->template);

        session()->flash('notify', 'Plantilla duplicada');

        $this->redirectRoute('templates.show', [
            'template' => $copy,
            'fecha' => $this->fecha,
        ], navigate: true);
    }

    public function delete(): void
    {
        $this->template->delete();

        session()->flash('notify', 'Plantilla eliminada');

        $this->redirectRoute('templates.index', ['fecha' => $this->fecha], navigate: true);
    }

    public function render()
    {
        return view('livewire.template-detail');
    }
}
