<?php

namespace Tests\Feature;

use App\Enums\ActivityStatus;
use App\Livewire\ActivityForm;
use App\Livewire\CopyDay;
use App\Livewire\DailyRoutine;
use App\Livewire\TemplateDetail;
use App\Livewire\TemplateForm;
use App\Models\Activity;
use App\Models\Day;
use App\Models\Template;
use App\Services\RoutineCopyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoutineTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_today(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Hoy');
    }

    public function test_can_create_an_activity_for_a_date(): void
    {
        Livewire::test(ActivityForm::class)
            ->call('open', '2026-08-17')
            ->set('name', 'Ejercicio')
            ->set('start_time', '18:00')
            ->set('duration_minutes', 45)
            ->set('icon', 'fire')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Day::query()->whereDate('date', '2026-08-17')->exists());
        $this->assertDatabaseHas('activities', [
            'name' => 'Ejercicio',
            'status' => ActivityStatus::Pending->value,
            'duration_minutes' => 45,
        ]);
    }

    public function test_reordering_only_affects_the_selected_date(): void
    {
        $day = Day::forDate('2026-08-17');
        $first = $day->activities()->create(['name' => 'Trabajo', 'position' => 1, 'status' => ActivityStatus::Pending]);
        $second = $day->activities()->create(['name' => 'Estudiar', 'position' => 2, 'status' => ActivityStatus::Pending]);
        $third = $day->activities()->create(['name' => 'Ejercicio', 'position' => 3, 'status' => ActivityStatus::Pending]);

        $other = Day::forDate('2026-08-18');
        $otherActivity = $other->activities()->create(['name' => 'Otro', 'position' => 1, 'status' => ActivityStatus::Pending]);

        Livewire::test(DailyRoutine::class, ['date' => '2026-08-17'])
            ->call('reorder', [$first->id, $third->id, $second->id]);

        $this->assertSame(1, $first->fresh()->position);
        $this->assertSame(2, $third->fresh()->position);
        $this->assertSame(3, $second->fresh()->position);
        $this->assertSame(1, $otherActivity->fresh()->position);
    }

    public function test_reordering_recalculates_times_by_new_order(): void
    {
        $day = Day::forDate('2026-08-17');
        $first = $day->activities()->create([
            'name' => 'Devocional',
            'position' => 1,
            'status' => ActivityStatus::Pending,
            'start_time' => '07:00',
            'end_time' => '07:30',
            'duration_minutes' => 30,
        ]);
        $second = $day->activities()->create([
            'name' => 'Trabajo',
            'position' => 2,
            'status' => ActivityStatus::Pending,
            'start_time' => '08:00',
            'end_time' => '16:00',
            'duration_minutes' => 480,
        ]);
        $third = $day->activities()->create([
            'name' => 'Ejercicio',
            'position' => 3,
            'status' => ActivityStatus::Pending,
            'start_time' => '18:00',
            'end_time' => '18:45',
            'duration_minutes' => 45,
        ]);

        Livewire::test(DailyRoutine::class, ['date' => '2026-08-17'])
            ->call('reorder', [$third->id, $first->id, $second->id]);

        $this->assertSame('07:00', $third->fresh()->formattedStart());
        $this->assertSame('07:45', $third->fresh()->formattedEnd());
        $this->assertSame(45, $third->fresh()->duration_minutes);

        $this->assertSame('07:45', $first->fresh()->formattedStart());
        $this->assertSame('08:15', $first->fresh()->formattedEnd());

        $this->assertSame('08:15', $second->fresh()->formattedStart());
        $this->assertSame('16:15', $second->fresh()->formattedEnd());
        $this->assertSame(480, $second->fresh()->duration_minutes);
    }

    public function test_editing_the_first_activity_time_updates_the_ones_below(): void
    {
        $day = Day::forDate('2026-08-17');
        $first = $day->activities()->create([
            'name' => 'Devocional',
            'position' => 1,
            'status' => ActivityStatus::Pending,
            'start_time' => '07:00',
            'end_time' => '07:30',
            'duration_minutes' => 30,
            'icon' => 'sun',
        ]);
        $second = $day->activities()->create([
            'name' => 'Trabajo',
            'position' => 2,
            'status' => ActivityStatus::Pending,
            'start_time' => '07:30',
            'end_time' => '15:30',
            'duration_minutes' => 480,
            'icon' => 'briefcase',
        ]);
        $third = $day->activities()->create([
            'name' => 'Ejercicio',
            'position' => 3,
            'status' => ActivityStatus::Pending,
            'start_time' => '15:30',
            'end_time' => '16:15',
            'duration_minutes' => 45,
            'icon' => 'fire',
        ]);

        Livewire::test(ActivityForm::class)
            ->call('open', '2026-08-17', $first->id)
            ->set('start_time', '08:00')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('08:00', $first->fresh()->formattedStart());
        $this->assertSame('08:30', $first->fresh()->formattedEnd());
        $this->assertSame('08:30', $second->fresh()->formattedStart());
        $this->assertSame('16:30', $second->fresh()->formattedEnd());
        $this->assertSame('16:30', $third->fresh()->formattedStart());
        $this->assertSame('17:15', $third->fresh()->formattedEnd());
    }

    public function test_activity_form_requires_name_and_valid_time(): void
    {
        Livewire::test(ActivityForm::class)
            ->call('open', '2026-08-17')
            ->set('name', '')
            ->set('start_time', '25:00')
            ->set('duration_minutes', 30)
            ->call('save')
            ->assertHasErrors(['name', 'start_time']);
    }

    public function test_activity_cannot_end_the_next_day(): void
    {
        Livewire::test(ActivityForm::class)
            ->call('open', '2026-08-17')
            ->set('name', 'Noche')
            ->set('start_time', '23:00')
            ->set('duration_minutes', 180)
            ->set('icon', 'moon')
            ->call('save')
            ->assertHasErrors(['duration_minutes']);
    }

    public function test_applying_a_template_creates_independent_activities(): void
    {
        $this->seed();

        $template = Template::query()->where('name', 'Día de trabajo')->firstOrFail();
        $originalName = $template->activities()->orderBy('position')->first()->name;

        Livewire::test(TemplateDetail::class, ['template' => $template, 'fecha' => '2026-08-17'])
            ->call('apply');

        $copied = Activity::query()
            ->whereHas('day', fn ($query) => $query->whereDate('date', '2026-08-17'))
            ->orderBy('position')
            ->first();

        $this->assertSame($originalName, $copied->name);
        $this->assertSame(ActivityStatus::Pending, $copied->status);

        $copied->update(['name' => 'Trabajo modificado']);
        $this->assertSame($originalName, $template->activities()->orderBy('position')->first()->fresh()->name);
    }

    public function test_copying_a_day_resets_statuses_to_pending(): void
    {
        $source = Day::forDate('2026-08-16');
        $source->activities()->create([
            'name' => 'Devocional',
            'position' => 1,
            'status' => ActivityStatus::Completed,
            'start_time' => '07:00',
            'end_time' => '07:30',
            'duration_minutes' => 30,
        ]);

        Livewire::test(CopyDay::class)
            ->call('open', '2026-08-17')
            ->set('sourceDate', '2026-08-16')
            ->call('copy')
            ->assertHasNoErrors();

        $copied = Activity::query()
            ->whereHas('day', fn ($query) => $query->whereDate('date', '2026-08-17'))
            ->first();

        $this->assertSame('Devocional', $copied->name);
        $this->assertSame(ActivityStatus::Pending, $copied->status);
        $this->assertSame('07:00', $copied->formattedStart());
    }

    public function test_duplicate_template_does_not_change_the_original(): void
    {
        $this->seed();
        $template = Template::query()->where('name', 'Día libre')->firstOrFail();
        $copy = app(RoutineCopyService::class)->duplicateTemplate($template);

        $this->assertSame('Día libre (copia)', $copy->name);
        $this->assertSame($template->activities()->count(), $copy->activities()->count());
        $this->assertNotSame($template->id, $copy->id);
    }

    public function test_template_form_chains_times_from_the_first_activity(): void
    {
        $component = Livewire::test(TemplateForm::class)
            ->set('items.0.name', 'Devocional')
            ->set('items.0.icon', 'sun')
            ->set('items.0.start_time', '06:00')
            ->set('items.0.duration_minutes', 60)
            ->call('addItem');

        $items = $component->get('items');

        $this->assertSame('06:00', $items[0]['start_time']);
        $this->assertSame('07:00', $items[1]['start_time']);
    }

    public function test_template_form_reorders_times_with_the_list(): void
    {
        $component = Livewire::test(TemplateForm::class)
            ->set('items.0.name', 'Devocional')
            ->set('items.0.icon', 'sun')
            ->set('items.0.start_time', '06:00')
            ->set('items.0.duration_minutes', 60)
            ->call('addItem')
            ->set('items.1.name', 'Ejercicio')
            ->set('items.1.icon', 'fire')
            ->set('items.1.duration_minutes', 30)
            ->call('reorderItems', [1, 0]);

        $items = $component->get('items');

        $this->assertSame('Ejercicio', $items[0]['name']);
        $this->assertSame('06:00', $items[0]['start_time']);
        $this->assertSame(30, (int) $items[0]['duration_minutes']);
        $this->assertSame('Devocional', $items[1]['name']);
        $this->assertSame('06:30', $items[1]['start_time']);
        $this->assertSame(60, (int) $items[1]['duration_minutes']);
    }
}
