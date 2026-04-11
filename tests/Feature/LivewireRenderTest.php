<?php

namespace MrCatz\DataTable\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use MrCatz\DataTable\MrCatzEvent;
use MrCatz\DataTable\Tests\Fixtures\ProductTableComponent;
use MrCatz\DataTable\Tests\Fixtures\ProductTableWithDateFilterComponent;
use MrCatz\DataTable\Tests\TestCase;

class LivewireRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('products')->insert([
            ['name' => 'Laptop Pro', 'category' => 'electronics', 'price' => 1500.00, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wireless Mouse', 'category' => 'electronics', 'price' => 25.00, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Office Chair', 'category' => 'furniture', 'price' => 350.00, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Standing Desk', 'category' => 'furniture', 'price' => 800.00, 'active' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mechanical Keyboard', 'category' => 'electronics', 'price' => 120.00, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Monitor 27 inch', 'category' => 'electronics', 'price' => 450.00, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Desk Lamp', 'category' => 'furniture', 'price' => 45.00, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'USB Hub', 'category' => 'electronics', 'price' => 30.00, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    // --- Render ---

    public function test_component_renders_successfully(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->assertStatus(200)
            ->assertSee('Products');
    }

    public function test_component_renders_table_data(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->assertSee('Desk Lamp')
            ->assertSee('Laptop Pro')
            ->assertSee('Name')
            ->assertSee('Category')
            ->assertSee('Price');
    }

    public function test_component_renders_with_pagination(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->assertSee('Desk Lamp')       // page 1 (sorted by name asc)
            ->assertDontSee('USB Hub');     // page 2
    }

    // --- Search ---

    public function test_search_filters_rendered_data(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->set('search', 'laptop')
            ->call('searchData')
            ->assertSee('Laptop Pro')
            ->assertDontSee('Office Chair');
    }

    public function test_search_empty_shows_all(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->set('search', '')
            ->call('searchData')
            ->assertSee('Desk Lamp');
    }

    // --- Sorting ---

    public function test_sort_ascending_by_price(): void
    {
        // orderData('price', 'desc') → toggles to 'asc'
        Livewire::test(ProductTableComponent::class)
            ->call('orderData', 'price', 'desc')
            ->assertSee('Wireless Mouse');
    }

    public function test_sort_descending_by_price(): void
    {
        // orderData('price', '') → toggles to 'desc' ('' != 'desc')
        Livewire::test(ProductTableComponent::class)
            ->call('orderData', 'price', '')
            ->assertSee('Laptop Pro');
    }

    // --- Pagination ---

    public function test_paginate_changes_per_page(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->call('paginate', 10)
            ->assertSee('Desk Lamp')
            ->assertSee('USB Hub')
            ->assertSee('Wireless Mouse');
    }

    // --- Column Visibility ---

    public function test_toggle_column_hides_column(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->call('toggleColumn', 2)
            ->assertSet('hiddenColumns', [2]);
    }

    public function test_toggle_column_shows_hidden_column(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->set('hiddenColumns', [2])
            ->call('toggleColumn', 2)
            ->assertSet('hiddenColumns', []);
    }

    // --- Inline Update ---

    public function test_inline_update_dispatches_event(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->call('inlineUpdate', ['id' => 1, 'name' => 'Laptop Pro'], 'name', 'Laptop Ultra')
            ->assertDispatched(MrCatzEvent::INLINE_UPDATE);
    }

    public function test_inline_update_validation_rejects_empty_name(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->call('inlineUpdate', ['id' => 1, 'name' => 'Laptop Pro'], 'name', '')
            ->assertNotDispatched(MrCatzEvent::INLINE_UPDATE)
            ->assertDispatched('inline-validation-error');
    }

    public function test_inline_update_validation_rejects_invalid_price(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->call('inlineUpdate', ['id' => 1, 'name' => 'Laptop Pro'], 'price', 'not-a-number')
            ->assertNotDispatched(MrCatzEvent::INLINE_UPDATE)
            ->assertDispatched('inline-validation-error');
    }

    public function test_inline_update_validation_rejects_negative_price(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->call('inlineUpdate', ['id' => 1, 'name' => 'Laptop Pro'], 'price', '-50')
            ->assertNotDispatched(MrCatzEvent::INLINE_UPDATE)
            ->assertDispatched('inline-validation-error');
    }

    public function test_inline_update_validation_passes_valid_data(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->call('inlineUpdate', ['id' => 1, 'name' => 'Laptop Pro'], 'name', 'Laptop Ultra')
            ->assertDispatched(MrCatzEvent::INLINE_UPDATE);
    }

    // --- Reset ---

    public function test_reset_clears_search_and_sort(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->set('search', 'laptop')
            ->set('key', 'price')
            ->set('value', 'desc')
            ->call('resetData')
            ->assertSet('search', '')
            ->assertSet('key', '')
            ->assertSet('value', '');
    }

    // --- Add/Edit/Delete dispatch ---

    public function test_add_data_dispatches_event(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->call('addData')
            ->assertDispatched(MrCatzEvent::ADD_DATA);
    }

    public function test_edit_data_dispatches_event(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->call('editData', ['id' => 1])
            ->assertDispatched(MrCatzEvent::EDIT_DATA);
    }

    public function test_delete_data_dispatches_event(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->call('deleteData', ['id' => 1])
            ->assertDispatched(MrCatzEvent::DELETE_DATA);
    }

    // --- Multi Sort ---

    public function test_multi_sort_adds_sort_entries(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->set('key', 'name')
            ->set('value', 'asc')
            ->call('addSort', 'price', '')
            ->assertSet('key', '');
    }

    // --- Row click ---

    public function test_row_clicked_dispatches_event(): void
    {
        Livewire::test(ProductTableComponent::class)
            ->call('rowClicked', ['id' => 1])
            ->assertDispatched(MrCatzEvent::ROW_CLICK);
    }

    // --- Date filters (Fitur #4) ---

    public function test_date_filter_component_renders(): void
    {
        Livewire::test(ProductTableWithDateFilterComponent::class)
            ->assertStatus(200)
            ->assertSee('Created On')        // single date filter label
            ->assertSee('Created Period');   // date range filter label
    }

    public function test_date_filter_change_updates_active_filters(): void
    {
        // Insert a known historic row so the equality test has something to match
        DB::table('products')->insert([
            'name' => 'Vintage Item', 'category' => 'general', 'price' => 99, 'active' => true,
            'created_at' => '2024-06-15 10:00:00', 'updated_at' => '2024-06-15 10:00:00',
        ]);

        Livewire::test(ProductTableWithDateFilterComponent::class)
            ->call('change', 'created_on', '2024-06-15')
            ->assertSee('Vintage Item')
            ->assertDontSee('Laptop Pro');
    }

    public function test_date_range_change_updates_active_filters(): void
    {
        DB::table('products')->insert([
            ['name' => 'Item 2024', 'category' => 'general', 'price' => 10, 'active' => true,
             'created_at' => '2024-06-15', 'updated_at' => '2024-06-15'],
            ['name' => 'Item 2025', 'category' => 'general', 'price' => 20, 'active' => true,
             'created_at' => '2025-03-20', 'updated_at' => '2025-03-20'],
        ]);

        $component = Livewire::test(ProductTableWithDateFilterComponent::class)
            ->call('changeDateRange', 'created_period', 'from', '2024-01-01')
            ->call('changeDateRange', 'created_period', 'to', '2025-06-30')
            ->assertSee('Item 2024')
            ->assertSee('Item 2025')
            ->assertDontSee('Laptop Pro');

        // Verify the active filter holds the structured value
        $active = $component->get('activeFilters');
        $found = collect($active)->firstWhere('id', 'created_period');
        $this->assertNotNull($found);
        $this->assertEquals('2024-01-01', $found['value']['from']);
        $this->assertEquals('2025-06-30', $found['value']['to']);
    }

    public function test_date_range_auto_swaps_when_to_before_from(): void
    {
        $component = Livewire::test(ProductTableWithDateFilterComponent::class)
            ->call('changeDateRange', 'created_period', 'from', '2025-12-31')
            ->call('changeDateRange', 'created_period', 'to', '2024-01-01');

        $active = $component->get('activeFilters');
        $found = collect($active)->firstWhere('id', 'created_period');

        // After swap: from = earlier, to = later
        $this->assertEquals('2024-01-01', $found['value']['from']);
        $this->assertEquals('2025-12-31', $found['value']['to']);
    }

    public function test_date_range_invalid_part_throws(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage("Invalid date range part [middle]");

        Livewire::test(ProductTableWithDateFilterComponent::class)
            ->call('changeDateRange', 'created_period', 'middle', '2025-01-01');
    }

    public function test_date_filter_clamps_to_max_constraint(): void
    {
        // The filter has max_date = '2030-12-31'. Picking 2099 should clamp.
        $component = Livewire::test(ProductTableWithDateFilterComponent::class)
            ->call('changeDateRange', 'created_period', 'from', '2099-01-01');

        $active = $component->get('activeFilters');
        $found = collect($active)->firstWhere('id', 'created_period');
        $this->assertEquals('2030-12-31', $found['value']['from']);
    }
}
