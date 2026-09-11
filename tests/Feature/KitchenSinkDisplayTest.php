<?php

use App\Filament\Resources\KitchenSinks\Pages\ListKitchenSinks;
use App\Filament\Resources\KitchenSinks\Pages\ViewKitchenSink;
use App\Models\KitchenSink;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Infolists\Components\Entry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->create());
    $this->record = (new class extends Factory
    {
        protected $model = KitchenSink::class;

        public function definition(): array
        {
            return [
                'name' => 'Complete dataset',
                'price' => 1234.56,
                'progress' => 75,
                'reminder_at' => '2026-09-12 14:30:00',
                'attachments' => ['kitchen-sinks/attachments/report.pdf'],
                'metadata' => ['region' => 'North', 'enabled' => false],
                'stats' => [['label' => 'Revenue', 'value' => 42, 'trend' => 'up']],
                'faq_items' => [['question' => 'Available?', 'answer' => 'Every day', 'highlight' => true]],
                'content' => '<p>Rich content</p>',
                'markdown_content' => '**Markdown content**',
                'code_snippet' => '<?php echo "Hello";',
                'event_time' => '14:30:00',
                'audiences' => ['staff', 'partners'],
                'review_groups' => ['legal'],
            ];
        }
    })->create();
});

test('the detail page includes every stored field and formats price', function () {
    $page = Livewire::test(ViewKitchenSink::class, ['record' => $this->record->getKey()])
        ->assertSuccessful()
        ->assertSee('$1,234.56')
        ->assertSee('75%')
        ->assertSee('report.pdf')
        ->assertSee('Reminder at')
        ->assertSee('Created at')
        ->assertSee('Updated at')
        ->assertSee('Every day');

    $entries = collect($page->instance()->getSchema('infolist')->getFlatComponents())
        ->filter(fn ($component): bool => $component instanceof Entry)
        ->map(fn ($entry): string => $entry->getName())->values()->all();

    foreach (array_merge((new KitchenSink)->getFillable(), ['id', 'created_at', 'updated_at']) as $field) {
        expect($entries)->toContain($field);
    }
});

test('every stored field is selectable and renders when enabled in the listing', function () {
    $page = Livewire::test(ListKitchenSinks::class)->assertSuccessful();
    $columns = $page->instance()->getTable()->getColumns();

    foreach (array_merge((new KitchenSink)->getFillable(), ['id', 'created_at', 'updated_at']) as $field) {
        expect($columns)->toHaveKey($field);
        expect($columns[$field]->isToggleable())->toBeTrue();
    }

    $state = array_map(fn (array $column): array => array_replace($column, ['isToggled' => true]), $page->instance()->tableColumns);

    $page->call('applyTableColumnManager', $state)
        ->assertSuccessful()
        ->assertSee('$1,234.56')
        ->assertSee('report.pdf')
        ->assertSee('Revenue: 42 (up)')
        ->assertSee('Available?: Every day (Highlighted: Yes)')
        ->assertSee('enabled: false');

    foreach (array_keys($columns) as $field) {
        $page->assertCanRenderTableColumn($field);
    }
});
