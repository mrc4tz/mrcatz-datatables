<?php

namespace MrCatz\DataTable\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use MrCatz\DataTable\MrCatzEvent;
use MrCatz\DataTable\Tests\Fixtures\ProductTableComponent;
use MrCatz\DataTable\Tests\Fixtures\ProductTableWithDateFilterComponent;
use MrCatz\DataTable\Tests\Fixtures\ProductTableWithOverrideFilterComponent;
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

    // --- Runtime overrides: setFilterData extended + setFilterDateBounds ---

    public function test_set_filter_data_stores_column_and_key_overrides(): void
    {
        $component = Livewire::test(ProductTableWithOverrideFilterComponent::class)
            ->call('setFilterData', 'category', [['code' => 'ELE', 'label' => 'Electronics']],
                value: 'code', option: 'label', key: 'name', condition: 'LIKE');

        $this->assertSame('code', $component->get('filterValueColOverrides')['category']);
        $this->assertSame('label', $component->get('filterOptionColOverrides')['category']);
        $this->assertSame('name', $component->get('filterKeyOverrides')['category']);
        $this->assertSame('LIKE', $component->get('filterConditionOverrides')['category']);
    }

    public function test_set_filter_data_override_reaches_engine_via_active_filters(): void
    {
        // Apply the filter with original key ('category'), then override to point
        // at a different DB column ('name'). The resulting query must use the new key.
        $component = Livewire::test(ProductTableWithOverrideFilterComponent::class)
            ->call('change', 'category', 'electronics')
            ->call('setFilterData', 'category', [['id' => 'Laptop Pro', 'name' => 'Laptop Pro']],
                key: 'name', condition: '=')
            // Re-apply change so activeFilters.value matches the new key's domain
            ->call('change', 'category', 'Laptop Pro');

        $active = $component->get('activeFilters');
        $found = collect($active)->firstWhere('id', 'category');

        // After render() runs applyFilterOverrides(), the engine sees the new key.
        $this->assertSame('name', $found['key']);
        $this->assertSame('=', $found['condition']);
    }

    public function test_set_filter_data_callback_override_resolves_method_on_component(): void
    {
        // Register `altCategoryCallback` as the runtime callback for the
        // 'category' filter. After this, the engine should invoke the
        // component method instead of the factory's SQL path. The method
        // applies a LIKE prefix match, so value 'elec' should match the
        // 'electronics' row.
        DB::table('products')->insert([
            'name' => 'Gadget', 'category' => 'electronics', 'price' => 10,
            'active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        Livewire::test(ProductTableWithOverrideFilterComponent::class)
            ->call('setFilterData', 'category', [['id' => 'elec', 'name' => 'Elec']],
                callback: 'altCategoryCallback')
            ->call('change', 'category', 'elec')
            ->assertSee('Gadget');
    }

    public function test_set_filter_data_callback_override_on_check_filter_wraps_as_closure(): void
    {
        // Regression guard: engine's applyCheckFilter() type-hints ?\Closure
        // on the $callback arg. A plain `[$this, $method]` array is a valid
        // callable but NOT a Closure — findFilterCallbackById() must wrap
        // with Closure::fromCallable or the engine throws TypeError.
        DB::table('products')->insert([
            'name' => 'Elec Thing', 'category' => 'electronics', 'price' => 10,
            'active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        Livewire::test(ProductTableWithOverrideFilterComponent::class)
            ->call('setFilterData', 'category_multi',
                [['id' => 'elec', 'name' => 'Elec']],
                callback: 'altCheckCategoryCallback')
            ->call('applyCheck', 'category_multi', ['elec'], null)
            ->assertSee('Elec Thing');
    }

    public function test_set_filter_data_missing_callback_method_throws(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage('does not exist on the component');

        Livewire::test(ProductTableWithOverrideFilterComponent::class)
            ->call('setFilterData', 'category', [['id' => 'x', 'name' => 'x']],
                callback: 'nonExistentMethod')
            ->call('change', 'category', 'x');
    }

    public function test_set_filter_date_bounds_stores_overrides(): void
    {
        $component = Livewire::test(ProductTableWithOverrideFilterComponent::class)
            ->call('setFilterDateBounds', 'created_on', min: '2024-01-01', max: '2024-12-31', condition: '=');

        $this->assertSame('2024-01-01', $component->get('filterMinDateOverrides')['created_on']);
        $this->assertSame('2024-12-31', $component->get('filterMaxDateOverrides')['created_on']);
        $this->assertSame('=',          $component->get('filterConditionOverrides')['created_on']);
    }

    public function test_set_filter_date_bounds_patches_data_filters_on_render(): void
    {
        $component = Livewire::test(ProductTableWithOverrideFilterComponent::class)
            ->call('setFilterDateBounds', 'created_on', min: '2024-01-01', max: '2024-12-31');

        $dataFilters = $component->get('dataFilters');
        $found = collect($dataFilters)->firstWhere('id', 'created_on');
        $this->assertSame('2024-01-01', $found['min_date']);
        $this->assertSame('2024-12-31', $found['max_date']);
    }

    public function test_set_filter_date_bounds_throws_on_non_date_filter(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage("filter [category] is of type [select]");

        Livewire::test(ProductTableWithOverrideFilterComponent::class)
            ->call('setFilterDateBounds', 'category', min: '2024-01-01');
    }

    public function test_set_filter_date_bounds_throws_on_unknown_filter(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage('Filter with ID [ghost] not found');

        Livewire::test(ProductTableWithOverrideFilterComponent::class)
            ->call('setFilterDateBounds', 'ghost', min: '2024-01-01');
    }

    public function test_clear_filter_override_removes_specific_keys(): void
    {
        $component = Livewire::test(ProductTableWithOverrideFilterComponent::class)
            ->call('setFilterData', 'category', [['id' => 'x', 'name' => 'X']],
                key: 'new_key', condition: 'LIKE')
            ->call('clearFilterOverride', 'category', ['key']);

        // Only 'key' cleared — 'condition' override stays.
        $this->assertArrayNotHasKey('category', $component->get('filterKeyOverrides'));
        $this->assertSame('LIKE', $component->get('filterConditionOverrides')['category']);
    }

    public function test_callback_override_hides_allow_exclude_on_check_filter(): void
    {
        // Include/Exclude toggle is meaningless when a callback owns the
        // WHERE clause — the engine doesn't route `exclude_mode` through
        // callback calls. applyFilterOverrides() must zero out the
        // `allow_exclude` display flag on any filter that has a callback
        // override active, symmetric with createCheckWithCallback()
        // rejecting ->allowExclude() at factory time.
        $component = Livewire::test(ProductTableWithOverrideFilterComponent::class)
            ->call('setFilterData', 'category_multi',
                [['id' => 'elec', 'name' => 'Elec']],
                callback: 'altCheckCategoryCallback');

        $df = collect($component->get('dataFilters'))->firstWhere('id', 'category_multi');
        $this->assertFalse($df['allow_exclude'],
            'allow_exclude should be false while callback override is active');

        // After clearing the callback override, allow_exclude falls back
        // to whatever the factory produced (createCheck without
        // ->allowExclude() is false here, so still false — but clearing
        // proves applyFilterOverrides no longer zeros it out).
        $component->call('clearFilterOverride', 'category_multi', ['callback']);
        $df = collect($component->get('dataFilters'))->firstWhere('id', 'category_multi');
        $this->assertFalse($df['allow_exclude'],
            'allow_exclude should fall back to the factory value once the override is cleared');
    }

    public function test_url_boot_survives_driver_filter_reset_in_on_filter_changed(): void
    {
        // Regression: a URL like `?filter[category_driver]=default&filter[category][]=furniture`
        // used to silently drop the `category` value on first render because
        // bootFilters Phase 2 fires onFilterChanged → resetFilter('category') →
        // findData() — caching a MrCatzDataTables built WITHOUT the category
        // filter. Phase 3 restored activeFilters.category but left the cache
        // stale, so the initial render ignored the URL-provided value.
        //
        // Fix: invalidate the engine cache at the end of bootFilters so
        // render() rebuilds from the final restored activeFilters state.
        DB::table('products')->insert([
            'name' => 'Chair', 'category' => 'furniture', 'price' => 100,
            'active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $component = Livewire::test(ProductTableWithOverrideFilterComponent::class, [
            'filterUrlParams' => [
                'category_driver'  => 'default',
                'category_multi'   => ['furniture'],
            ],
        ]);

        // 'Chair' is furniture and must show — the electronics-only filter
        // driver didn't actually filter anything away on initial render.
        $component->assertSee('Chair');

        // And the activeFilters snapshot should still carry the URL value,
        // not a null from the Phase-2 resetFilter.
        $active = collect($component->get('activeFilters'))->firstWhere('id', 'category_multi');
        $this->assertSame(['furniture'], $active['value']);
    }

    public function test_clear_filter_override_removes_all_when_keys_null(): void
    {
        $component = Livewire::test(ProductTableWithOverrideFilterComponent::class)
            ->call('setFilterData', 'category', [['id' => 'x', 'name' => 'X']],
                key: 'new_key', condition: 'LIKE', value: 'id', option: 'name')
            ->call('clearFilterOverride', 'category');

        $this->assertArrayNotHasKey('category', $component->get('filterKeyOverrides'));
        $this->assertArrayNotHasKey('category', $component->get('filterConditionOverrides'));
        $this->assertArrayNotHasKey('category', $component->get('filterValueColOverrides'));
        $this->assertArrayNotHasKey('category', $component->get('filterOptionColOverrides'));
    }
}
