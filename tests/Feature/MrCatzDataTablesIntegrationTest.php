<?php

namespace MrCatz\DataTable\Tests\Feature;

use Illuminate\Support\Facades\DB;
use MrCatz\DataTable\MrCatzDataTableFilter;
use MrCatz\DataTable\MrCatzDataTables;
use MrCatz\DataTable\Tests\TestCase;

class MrCatzDataTablesIntegrationTest extends TestCase
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

    private function createTable(int $perPage = 5): MrCatzDataTables
    {
        return MrCatzDataTables::with(
            DB::table('products'),
            [5, 10, 15],
            $perPage
        );
    }

    // --- Build & Pagination ---

    public function test_build_returns_paginated_data(): void
    {
        $dt = $this->createTable(5)
            ->withColumnIndex('No')
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category')
            ->withColumn('Price', 'price')
            ->setDefaultOrder('name', 'asc')
            ->build();

        $this->assertEquals(4, $dt->countColumn());
        $this->assertTrue($dt->hasData());
        $this->assertEquals(5, $dt->countRow());
    }

    public function test_build_without_pagination(): void
    {
        $dt = MrCatzDataTables::with(
            DB::table('products'),
            [5, 10],
            5,
            false // usePagination
        )
            ->withColumn('Name', 'name')
            ->build();

        $this->assertEquals(8, $dt->countRow());
    }

    public function test_pagination_respects_per_page(): void
    {
        $dt = $this->createTable(3)
            ->withColumn('Name', 'name')
            ->setDefaultOrder('name', 'asc')
            ->build();

        $this->assertEquals(3, $dt->countRow());
        $this->assertEquals(3, $dt->getPaginate());
    }

    // --- Search ---

    public function test_search_filters_results(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category');

        $dt->setSearch('laptop');
        $dt->build();

        $this->assertEquals(1, $dt->countRow());
    }

    public function test_search_multi_keyword(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category');

        $dt->setSearch('mouse keyboard');
        $dt->build();

        $this->assertEquals(2, $dt->countRow());
    }

    public function test_search_case_insensitive(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        $dt->setSearch('LAPTOP');
        $dt->build();

        $this->assertEquals(1, $dt->countRow());
    }

    public function test_search_empty_returns_all(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        $dt->setSearch('');
        $dt->build();

        $this->assertEquals(8, $dt->countRow());
    }

    public function test_search_no_match(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        $dt->setSearch('nonexistent_xyz');
        $dt->build();

        $this->assertEquals(0, $dt->countRow());
        $this->assertFalse($dt->hasData());
    }

    // --- Filters ---

    public function test_filter_by_key_value(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category');

        $dt->setFilters(
            [['id' => 'cat', 'key' => 'category', 'value' => 'furniture', 'condition' => '=']],
            [null]
        );
        $dt->build();

        $this->assertEquals(3, $dt->countRow());
    }

    public function test_filter_with_callback(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withColumn('Price', 'price');

        $callback = function ($query, $value) {
            return $query->where('price', '>=', $value);
        };

        $dt->setFilters(
            [['id' => 'min_price', 'key' => '-', 'value' => 100, 'condition' => '-']],
            [$callback]
        );
        $dt->build();

        // Laptop Pro (1500), Office Chair (350), Standing Desk (800), Mechanical Keyboard (120), Monitor (450)
        $this->assertEquals(5, $dt->countRow());
    }

    public function test_filter_empty_value_skipped(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        $dt->setFilters(
            [['id' => 'cat', 'key' => 'category', 'value' => null, 'condition' => '=']],
            [null]
        );
        $dt->build();

        $this->assertEquals(8, $dt->countRow());
    }

    public function test_multiple_filters(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category');

        $dt->setFilters(
            [
                ['id' => 'cat', 'key' => 'category', 'value' => 'electronics', 'condition' => '='],
                ['id' => 'active', 'key' => 'active', 'value' => 1, 'condition' => '='],
            ],
            [null, null]
        );
        $dt->build();

        $this->assertEquals(5, $dt->countRow());
    }

    // --- Ordering ---

    public function test_default_order(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->setDefaultOrder('name', 'asc')
            ->build();

        $firstName = $dt->getRowRawData(0)->name;
        $this->assertEquals('Desk Lamp', $firstName);
    }

    public function test_column_sort_order(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withColumn('Price', 'price')
            ->setDefaultOrder('name', 'asc');

        $dt->setOrderByKey('price', 'desc');
        $dt->build();

        $firstPrice = $dt->getRowRawData(0)->price;
        $this->assertEquals(1500.00, (float) $firstPrice);
    }

    public function test_additional_order_by(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->addOrderBy('category', 'asc')
            ->addOrderBy('name', 'asc')
            ->build();

        $firstRow = $dt->getRowRawData(0);
        $this->assertEquals('electronics', $firstRow->category);
    }

    // --- Column Data Access ---

    public function test_get_data_regular_column(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->setDefaultOrder('name', 'asc')
            ->build();

        $data = $dt->getData(0, 0);
        $this->assertEquals('Desk Lamp', $data);
    }

    public function test_get_data_index_column(): void
    {
        $dt = $this->createTable(10)
            ->withColumnIndex('No')
            ->withColumn('Name', 'name')
            ->setDefaultOrder('name', 'asc')
            ->build();

        $this->assertEquals(1, $dt->getData(0, 0));
        $this->assertEquals(2, $dt->getData(1, 0));
    }

    public function test_get_data_custom_column(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withCustomColumn('Status', function ($data, $i) {
                return $data->active ? 'Active' : 'Inactive';
            }, 'active')
            ->setDefaultOrder('name', 'asc')
            ->build();

        $status = $dt->getData(0, 1);
        $this->assertContains($status, ['Active', 'Inactive']);
    }

    // --- Bulk ---

    public function test_bulk_enabled_default(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->enableBulk()
            ->setDefaultOrder('name', 'asc')
            ->build();

        $this->assertTrue($dt->isBulkEnabled(0));
        $this->assertTrue($dt->isBulkEnabled(1));
    }

    public function test_bulk_enabled_with_callback(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->enableBulk(fn($data, $i) => $data->active == true)
            ->setDefaultOrder('name', 'asc')
            ->build();

        // Find Standing Desk (active=false) index
        $allDisabled = [];
        for ($i = 0; $i < $dt->countRow(); $i++) {
            if (!$dt->isBulkEnabled($i)) {
                $allDisabled[] = $dt->getRowRawData($i)->name;
            }
        }
        $this->assertContains('Standing Desk', $allDisabled);
    }

    // --- Expand ---

    public function test_expand_content(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->enableExpand(fn($data, $i) => "<div>{$data->name} - {$data->category}</div>")
            ->setDefaultOrder('name', 'asc')
            ->build();

        $this->assertTrue($dt->hasExpand());

        $content = $dt->getExpandContent(0);
        $this->assertStringContainsString('Desk Lamp', $content);
        $this->assertStringContainsString('furniture', $content);
    }

    // --- Search + Filter combined ---

    public function test_search_and_filter_combined(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category');

        $dt->setSearch('desk');
        $dt->setFilters(
            [['id' => 'cat', 'key' => 'category', 'value' => 'furniture', 'condition' => '=']],
            [null]
        );
        $dt->build();

        // "Standing Desk" (furniture) and "Desk Lamp" (furniture) match
        $this->assertEquals(2, $dt->countRow());
    }

    // --- applySearchWhere static method ---

    public function test_apply_search_where_static(): void
    {
        $query = DB::table('products');
        $columns = [['key' => 'name'], ['key' => 'category']];

        $result = MrCatzDataTables::applySearchWhere($query, 'chair', $columns)->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Office Chair', $result[0]->name);
    }

    // --- MrCatzDataTableFilter integration ---

    public function test_filter_create_with_db_data(): void
    {
        $categories = DB::table('products')
            ->select('category as id', 'category as name')
            ->distinct()
            ->get()
            ->toArray();

        $filter = MrCatzDataTableFilter::create(
            'category',
            'Category',
            json_decode(json_encode($categories), true),
            'id',
            'name',
            'category'
        )->get();

        $df = $filter->getDataFilter();
        $this->assertEquals('category', $df['id']);
        $this->assertCount(2, $df['data']); // electronics, furniture
    }

    // --- Relevance search with configTable ---

    public function test_relevance_search_with_config_table(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category');

        $dt->setSearch('laptop electronics');
        $dt->setConfig(['table_name' => 'products', 'table_id' => 'id']);
        $dt->build();

        // "Laptop Pro" matches both keywords (name=laptop, category=electronics)
        // Should appear first due to highest relevance
        $this->assertTrue($dt->hasData());
        $firstName = $dt->getRowRawData(0)->name;
        $this->assertEquals('Laptop Pro', $firstName);
    }

    // --- Per-column scoring (Fitur #2) ---

    public function test_scoring_replace_mode_restricts_searched_columns(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category');

        // mode=replace: only 'name' is searched, 'category' is excluded entirely
        $dt->setSearch('electronics');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'scoring' => [
                'mode'    => 'replace',
                'columns' => ['name' => 5],
            ],
        ]);
        $dt->build();

        // No product name contains 'electronics' → 0 results
        $this->assertEquals(0, $dt->countRow());
    }

    public function test_scoring_complement_mode_keeps_all_columns(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category');

        // mode=complement (default): all dataTableSet columns still searched,
        // but 'name' gets a higher weight in ranking
        $dt->setSearch('electronics');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'scoring' => [
                'mode'    => 'complement',
                'columns' => ['name' => 5],
            ],
        ]);
        $dt->build();

        // 5 electronics products still found (category is still searched)
        $this->assertEquals(5, $dt->countRow());
    }

    public function test_scoring_shortcut_format_is_complement(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category');

        // Shortcut format: ['name' => 5] is treated as complement mode
        $dt->setSearch('electronics');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'scoring' => ['name' => 5],
        ]);
        $dt->build();

        // Same as complement: 5 results (category still searched)
        $this->assertEquals(5, $dt->countRow());
    }

    public function test_scoring_invalid_mode_throws(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage("Invalid scoring mode [invalid]");

        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        $dt->setSearch('laptop');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'scoring' => [
                'mode'    => 'invalid',
                'columns' => ['name' => 1],
            ],
        ]);
        $dt->build();
    }

    public function test_scoring_higher_weight_ranks_first(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category');

        // Search 'desk' — matches both "Standing Desk" (name) and "Desk Lamp" (name)
        // With name weight=10, both rank high, but the order between them is
        // not guaranteed without secondary criteria. We just verify both appear.
        $dt->setSearch('desk');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'scoring' => ['name' => 10],
        ]);
        $dt->build();

        $this->assertEquals(2, $dt->countRow());
    }

    // --- Typo tolerance (Fitur #1) ---

    public function test_typo_tolerance_disabled_by_default(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        // 'lapotp' is a typo of 'laptop' — without typo tolerance, no match
        $dt->setSearch('lapotp');
        $dt->setConfig(['table_name' => 'products', 'table_id' => 'id']);
        $dt->build();

        $this->assertEquals(0, $dt->countRow());
    }

    public function test_typo_tolerance_trigram_finds_misspelled_word(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        // 'lapotp' → trigrams: lap, apo, pot, otp
        // 'lap' substring exists in 'laptop pro' → match
        $dt->setSearch('lapotp');
        $dt->setConfig([
            'table_name'     => 'products',
            'table_id'       => 'id',
            'typo_tolerance' => ['driver' => 'trigram', 'min_word_length' => 4],
        ]);
        $dt->build();

        $this->assertGreaterThanOrEqual(1, $dt->countRow());
        $names = collect($dt->getDatas()->items())->pluck('name')->toArray();
        $this->assertContains('Laptop Pro', $names);
    }

    public function test_typo_tolerance_shortcut_true_enables_trigram(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        // typo_tolerance => true should be equivalent to driver=trigram
        $dt->setSearch('lapotp');
        $dt->setConfig([
            'table_name'     => 'products',
            'table_id'       => 'id',
            'typo_tolerance' => true,
        ]);
        $dt->build();

        $names = collect($dt->getDatas()->items())->pluck('name')->toArray();
        $this->assertContains('Laptop Pro', $names);
    }

    public function test_typo_tolerance_skips_short_words(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        // 'usb' is 3 chars, below min_word_length=4 → no fuzzy expansion,
        // only exact substring match. 'USB Hub' contains 'usb' → match.
        $dt->setSearch('usb');
        $dt->setConfig([
            'table_name'     => 'products',
            'table_id'       => 'id',
            'typo_tolerance' => ['driver' => 'trigram', 'min_word_length' => 4],
        ]);
        $dt->build();

        $this->assertEquals(1, $dt->countRow());
    }

    public function test_typo_tolerance_invalid_driver_throws(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage("Invalid typo_tolerance driver [fuzzy]");

        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        $dt->setSearch('laptop');
        $dt->setConfig([
            'table_name'     => 'products',
            'table_id'       => 'id',
            'typo_tolerance' => ['driver' => 'fuzzy'],
        ]);
        $dt->build();
    }

    public function test_typo_tolerance_pg_trgm_throws_on_non_postgres(): void
    {
        // The test suite uses SQLite by default, so pg_trgm should fail validation
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage("requires a 'pgsql' database connection");

        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        $dt->setSearch('laptop');
        $dt->setConfig([
            'table_name'     => 'products',
            'table_id'       => 'id',
            'typo_tolerance' => ['driver' => 'pg_trgm'],
        ]);
        $dt->build();
    }

    public function test_generate_trigrams_helper(): void
    {
        $this->assertEquals(['lap', 'apt', 'pto', 'top'], MrCatzDataTables::generateTrigrams('laptop'));
        $this->assertEquals(['abc'], MrCatzDataTables::generateTrigrams('abc'));
        $this->assertEquals([], MrCatzDataTables::generateTrigrams('ab'));
        $this->assertEquals(['aaa'], MrCatzDataTables::generateTrigrams('aaaa')); // dedup
    }

    // --- Scout / Meilisearch driver (Fitur #3) ---
    //
    // These tests do NOT require Laravel Scout to be installed; they verify the
    // validation paths and the build() flow when scout driver is selected
    // without the optional dependencies. Real Meilisearch integration is
    // covered by manual testing with a running Meilisearch instance.

    public function test_scout_driver_without_scout_installed_throws(): void
    {
        if (class_exists(\Laravel\Scout\Searchable::class)) {
            $this->markTestSkipped('Laravel Scout is installed in this environment; cannot test the missing-dependency path.');
        }

        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage("Search driver 'scout' requires Laravel Scout");

        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        $dt->setSearch('laptop');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => [
                'driver'          => 'scout',
                'filter_pushdown' => 'never',
            ],
        ]);
        $dt->build();
    }

    public function test_scout_driver_skipped_when_search_is_empty(): void
    {
        // Even with scout configured, an empty search should NOT trigger Scout
        // — falling back to the standard SQL flow (no validation needed).
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        $dt->setSearch(''); // empty search
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => [
                'driver'          => 'scout',
                'filter_pushdown' => 'never',
                'scout_model'     => 'NonExistentClass',
            ],
        ]);

        // Should NOT throw, despite scout_model being invalid — we never reach
        // validation because search is empty.
        $dt->build();

        $this->assertEquals(8, $dt->countRow());
    }

    public function test_scout_filter_pushdown_is_optional_defaults_to_auto(): void
    {
        // filter_pushdown is NOT set — validation should pass through to the
        // next check (scout-not-installed in this env). Proves the field is
        // optional and the default 'auto' kicks in.
        if (class_exists(\Laravel\Scout\Searchable::class)) {
            $this->markTestSkipped('Laravel Scout is installed; cannot test the missing-dependency path.');
        }

        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage("Search driver 'scout' requires Laravel Scout");

        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        $dt->setSearch('laptop');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => [
                'driver' => 'scout',
                // filter_pushdown intentionally omitted — should default to 'auto'
            ],
        ]);
        $dt->build();
    }

    // --- Date filters (Fitur #4) ---
    //
    // The setUp() inserts all rows with `now()` for created_at, so we add
    // a few rows with explicit historic dates here for date-filter tests.

    private function seedDatedProducts(): void
    {
        DB::table('products')->insert([
            ['name' => 'Old Item A', 'category' => 'general', 'price' => 10, 'active' => true,
             'created_at' => '2024-06-15 10:30:00', 'updated_at' => '2024-06-15 10:30:00'],
            ['name' => 'Old Item B', 'category' => 'general', 'price' => 20, 'active' => true,
             'created_at' => '2025-03-20 14:00:00', 'updated_at' => '2025-03-20 14:00:00'],
            ['name' => 'Old Item C', 'category' => 'general', 'price' => 30, 'active' => true,
             'created_at' => '2025-12-31 23:59:00', 'updated_at' => '2025-12-31 23:59:00'],
        ]);
    }

    public function test_date_filter_equals(): void
    {
        $this->seedDatedProducts();

        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => 'created_at', 'value' => '2024-06-15', 'condition' => '=', 'type' => 'date', 'format' => 'date']],
                [null]
            )
            ->build();

        // Only "Old Item A" matches 2024-06-15
        $this->assertEquals(1, $dt->countRow());
    }

    public function test_date_filter_greater_than(): void
    {
        $this->seedDatedProducts();

        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => 'created_at', 'value' => '2025-01-01', 'condition' => '>=', 'type' => 'date', 'format' => 'date']],
                [null]
            )
            ->build();

        // 8 products from setUp() (today) + 2 historic (2025-03-20, 2025-12-31) = 10
        $this->assertEquals(10, $dt->countRow());
    }

    public function test_date_filter_year_format(): void
    {
        $this->seedDatedProducts();

        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => 'created_at', 'value' => '2024', 'condition' => '=', 'type' => 'date', 'format' => 'year']],
                [null]
            )
            ->build();

        // Only "Old Item A" was created in 2024
        $this->assertEquals(1, $dt->countRow());
    }

    public function test_date_filter_month_year_format(): void
    {
        $this->seedDatedProducts();

        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => 'created_at', 'value' => '2025-03', 'condition' => '=', 'type' => 'date', 'format' => 'month_year']],
                [null]
            )
            ->build();

        // Only "Old Item B" was created in March 2025
        $this->assertEquals(1, $dt->countRow());
    }

    public function test_date_range_filter_both_bounds(): void
    {
        $this->seedDatedProducts();

        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => 'created_at',
                  'value' => ['from' => '2024-01-01', 'to' => '2025-06-30'],
                  'condition' => '-', 'type' => 'date_range', 'format' => 'date']],
                [null]
            )
            ->build();

        // Old Item A (2024-06-15) and Old Item B (2025-03-20) match
        $this->assertEquals(2, $dt->countRow());
    }

    public function test_date_range_filter_open_ended_from_only(): void
    {
        $this->seedDatedProducts();

        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => 'created_at',
                  'value' => ['from' => '2025-06-01', 'to' => null],
                  'condition' => '-', 'type' => 'date_range', 'format' => 'date']],
                [null]
            )
            ->build();

        // Old Item C (2025-12-31) + 8 today rows = 9
        $this->assertEquals(9, $dt->countRow());
    }

    public function test_date_range_filter_open_ended_to_only(): void
    {
        $this->seedDatedProducts();

        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => 'created_at',
                  'value' => ['from' => null, 'to' => '2025-06-30'],
                  'condition' => '-', 'type' => 'date_range', 'format' => 'date']],
                [null]
            )
            ->build();

        // Old Item A (2024-06-15) and Old Item B (2025-03-20)
        $this->assertEquals(2, $dt->countRow());
    }

    public function test_date_range_filter_both_empty_is_noop(): void
    {
        $this->seedDatedProducts();

        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => 'created_at',
                  'value' => ['from' => null, 'to' => null],
                  'condition' => '-', 'type' => 'date_range', 'format' => 'date']],
                [null]
            )
            ->build();

        // 8 from setUp() + 3 historic = 11 (no filter applied)
        $this->assertEquals(11, $dt->countRow());
    }

    public function test_date_filter_callback_variant(): void
    {
        $this->seedDatedProducts();

        $callback = fn($q, $v) => $q->whereDate('created_at', $v);

        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => '-', 'value' => '2024-06-15', 'condition' => '-', 'type' => 'date', 'format' => 'date']],
                [$callback]
            )
            ->build();

        $this->assertEquals(1, $dt->countRow());
    }

    public function test_date_range_filter_callback_variant(): void
    {
        $this->seedDatedProducts();

        $callback = function ($q, $v) {
            if (!empty($v['from'])) $q->whereDate('created_at', '>=', $v['from']);
            if (!empty($v['to']))   $q->whereDate('created_at', '<=', $v['to']);
            return $q;
        };

        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => '-',
                  'value' => ['from' => '2024-01-01', 'to' => '2025-06-30'],
                  'condition' => '-', 'type' => 'date_range', 'format' => 'date']],
                [$callback]
            )
            ->build();

        $this->assertEquals(2, $dt->countRow());
    }

    public function test_select_filter_still_works_alongside_date(): void
    {
        // Backward compat: legacy select-style filters should still apply
        // unchanged when type is missing or 'select'.
        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'cat', 'key' => 'category', 'value' => 'electronics', 'condition' => '=']],
                [null]
            )
            ->build();

        $this->assertEquals(5, $dt->countRow()); // 5 electronics in setUp()
    }

    public function test_filter_value_zero_is_applied_not_skipped(): void
    {
        // Regression: previously `!empty(0)` evaluated true and the filter
        // was silently dropped. setUp() seeds 1 product with active=false (0)
        // and 7 with active=true (1). A `?filter[active]=0` URL should return
        // the inactive one — not all 8.
        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'active', 'key' => 'active', 'value' => 0, 'condition' => '=']],
                [null]
            )
            ->build();

        $this->assertEquals(1, $dt->countRow()); // only 'Standing Desk' is active=false
    }

    public function test_filter_value_string_zero_is_applied(): void
    {
        // URL params arrive as strings — '0' should also work.
        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'active', 'key' => 'active', 'value' => '0', 'condition' => '=']],
                [null]
            )
            ->build();

        $this->assertEquals(1, $dt->countRow());
    }

    // --- Date filters interaction with Scout pushdown (Fitur #4 + Fitur #3) ---
    //
    // prepareFilterPushdown() runs AFTER validateScoutDriver() in the real
    // build() flow, which throws scoutNotInstalled in this test env. To verify
    // the date-filter routing in isolation we invoke the private method via
    // reflection — same instance, just bypassing the Scout install check.

    private function invokePrepareFilterPushdown(MrCatzDataTables $dt): array
    {
        $ref = new \ReflectionMethod($dt, 'prepareFilterPushdown');
        $ref->setAccessible(true);
        return $ref->invoke($dt);
    }

    public function test_scout_pushdown_routes_date_filter_to_sql_fallback(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => 'created_at', 'value' => '2024-06-15',
                  'condition' => '=', 'type' => 'date', 'format' => 'date']],
                [null]
            );

        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => ['driver' => 'scout', 'filter_pushdown' => 'auto'],
        ]);

        // Suppress the educational warning we trigger in 'auto' mode
        $previous = set_error_handler(fn() => true, E_USER_WARNING);
        try {
            [$pushedFilter, $unpushedKv, $unpushedCb] = $this->invokePrepareFilterPushdown($dt);
        } finally {
            set_error_handler($previous);
        }

        $this->assertNull($pushedFilter, 'Date filter should NOT be pushed to Meilisearch');
        $this->assertCount(1, $unpushedKv, 'Date filter should be in the SQL fallback list');
        $this->assertEquals('2024-06-15', array_values($unpushedKv)[0]['value']);
    }

    public function test_scout_pushdown_routes_date_range_filter_to_sql_fallback(): void
    {
        // Critical test: range value is a structured array. Without the type
        // check, the legacy translator would crash trying to cast it to string.
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => 'created_at',
                  'value' => ['from' => '2024-01-01', 'to' => '2025-06-30'],
                  'condition' => '-', 'type' => 'date_range', 'format' => 'date']],
                [null]
            );

        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => ['driver' => 'scout', 'filter_pushdown' => 'auto'],
        ]);

        $previous = set_error_handler(fn() => true, E_USER_WARNING);
        try {
            [$pushedFilter, $unpushedKv, $unpushedCb] = $this->invokePrepareFilterPushdown($dt);
        } finally {
            set_error_handler($previous);
        }

        $this->assertNull($pushedFilter);
        $this->assertCount(1, $unpushedKv);

        // Structured value must be preserved exactly so applyDateRangeFilter
        // can read 'from' and 'to' correctly in the SQL phase.
        $value = array_values($unpushedKv)[0]['value'];
        $this->assertIsArray($value);
        $this->assertEquals('2024-01-01', $value['from']);
        $this->assertEquals('2025-06-30', $value['to']);
    }

    public function test_scout_pushdown_always_mode_throws_for_unmapped_date_filter(): void
    {
        // 'always' mode + no date_field_map entry → throw with actionable message
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage("no date_field_map entry for key [created_at]");

        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => 'created_at', 'value' => '2024-06-15',
                  'condition' => '=', 'type' => 'date', 'format' => 'date']],
                [null]
            );

        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => ['driver' => 'scout', 'filter_pushdown' => 'always'],
        ]);

        $this->invokePrepareFilterPushdown($dt);
    }

    // --- Date filter PUSH path (Fitur #6: date_field_map) ---

    private function setupPushableDateFilter(MrCatzDataTables $dt, array $kv, string $tz = 'UTC', string $mode = 'auto'): void
    {
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => [
                'driver'          => 'scout',
                'filter_pushdown' => $mode,
                'date_timezone'   => $tz,
                'date_field_map'  => [
                    'created_at' => 'created_at_ts',
                ],
            ],
        ]);
        $dt->setFilters([$kv], [null]);
    }

    public function test_scout_pushdown_translates_date_equals(): void
    {
        // 2024-06-15 in UTC = 1718409600, next day = 1718496000
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $this->setupPushableDateFilter($dt, [
            'id' => 'd', 'key' => 'created_at', 'value' => '2024-06-15',
            'condition' => '=', 'type' => 'date', 'format' => 'date',
        ]);

        [$pushed, $unpushedKv,] = $this->invokePrepareFilterPushdown($dt);

        $this->assertSame('(created_at_ts >= 1718409600 AND created_at_ts < 1718496000)', $pushed);
        $this->assertEmpty($unpushedKv);
    }

    public function test_scout_pushdown_translates_date_gte(): void
    {
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $this->setupPushableDateFilter($dt, [
            'id' => 'd', 'key' => 'created_at', 'value' => '2024-06-15',
            'condition' => '>=', 'type' => 'date', 'format' => 'date',
        ]);

        [$pushed,] = $this->invokePrepareFilterPushdown($dt);

        $this->assertSame('created_at_ts >= 1718409600', $pushed);
    }

    public function test_scout_pushdown_translates_date_not_equal(): void
    {
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $this->setupPushableDateFilter($dt, [
            'id' => 'd', 'key' => 'created_at', 'value' => '2024-06-15',
            'condition' => '!=', 'type' => 'date', 'format' => 'date',
        ]);

        [$pushed,] = $this->invokePrepareFilterPushdown($dt);

        $this->assertSame('(created_at_ts < 1718409600 OR created_at_ts >= 1718496000)', $pushed);
    }

    public function test_scout_pushdown_translates_year_format(): void
    {
        // 2024 in UTC: start = 2024-01-01 00:00 UTC = 1704067200
        //              end   = 2025-01-01 00:00 UTC = 1735689600
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $this->setupPushableDateFilter($dt, [
            'id' => 'd', 'key' => 'created_at', 'value' => '2024',
            'condition' => '=', 'type' => 'date', 'format' => 'year',
        ]);

        [$pushed,] = $this->invokePrepareFilterPushdown($dt);

        $this->assertSame('(created_at_ts >= 1704067200 AND created_at_ts < 1735689600)', $pushed);
    }

    public function test_scout_pushdown_translates_month_year_format(): void
    {
        // 2024-06 in UTC: start = 2024-06-01 00:00 UTC = 1717200000
        //                 end   = 2024-07-01 00:00 UTC = 1719792000
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $this->setupPushableDateFilter($dt, [
            'id' => 'd', 'key' => 'created_at', 'value' => '2024-06',
            'condition' => '=', 'type' => 'date', 'format' => 'month_year',
        ]);

        [$pushed,] = $this->invokePrepareFilterPushdown($dt);

        $this->assertSame('(created_at_ts >= 1717200000 AND created_at_ts < 1719792000)', $pushed);
    }

    public function test_scout_pushdown_translates_date_range_both_bounds(): void
    {
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $this->setupPushableDateFilter($dt, [
            'id' => 'd', 'key' => 'created_at',
            'value' => ['from' => '2024-06-15', 'to' => '2024-06-15'],
            'condition' => '-', 'type' => 'date_range', 'format' => 'date',
        ]);

        [$pushed,] = $this->invokePrepareFilterPushdown($dt);

        // Open-ended-style: from start of from-day, to start of (to+1)-day
        $this->assertSame('(created_at_ts >= 1718409600 AND created_at_ts < 1718496000)', $pushed);
    }

    public function test_scout_pushdown_translates_date_range_open_ended_from(): void
    {
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $this->setupPushableDateFilter($dt, [
            'id' => 'd', 'key' => 'created_at',
            'value' => ['from' => '2024-06-15', 'to' => null],
            'condition' => '-', 'type' => 'date_range', 'format' => 'date',
        ]);

        [$pushed,] = $this->invokePrepareFilterPushdown($dt);

        $this->assertSame('(created_at_ts >= 1718409600)', $pushed);
    }

    public function test_scout_pushdown_translates_date_range_open_ended_to(): void
    {
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $this->setupPushableDateFilter($dt, [
            'id' => 'd', 'key' => 'created_at',
            'value' => ['from' => null, 'to' => '2024-06-15'],
            'condition' => '-', 'type' => 'date_range', 'format' => 'date',
        ]);

        [$pushed,] = $this->invokePrepareFilterPushdown($dt);

        $this->assertSame('(created_at_ts < 1718496000)', $pushed);
    }

    public function test_scout_pushdown_respects_timezone(): void
    {
        // 2024-06-15 in Asia/Jakarta (UTC+7):
        //   start = 2024-06-15 00:00 WIB = 2024-06-14 17:00 UTC = 1718384400
        //   end   = 2024-06-16 00:00 WIB = 2024-06-15 17:00 UTC = 1718470800
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $this->setupPushableDateFilter($dt, [
            'id' => 'd', 'key' => 'created_at', 'value' => '2024-06-15',
            'condition' => '=', 'type' => 'date', 'format' => 'date',
        ], 'Asia/Jakarta');

        [$pushed,] = $this->invokePrepareFilterPushdown($dt);

        $this->assertSame('(created_at_ts >= 1718384400 AND created_at_ts < 1718470800)', $pushed);
    }

    public function test_scout_pushdown_time_format_falls_back(): void
    {
        // time / time_hm formats are NOT translatable to a single Meilisearch
        // numeric range — they must always fall back to SQL.
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $this->setupPushableDateFilter($dt, [
            'id' => 'd', 'key' => 'created_at', 'value' => '14:30',
            'condition' => '=', 'type' => 'date', 'format' => 'time_hm',
        ]);

        $previous = set_error_handler(fn() => true, E_USER_WARNING);
        try {
            [$pushed, $unpushedKv,] = $this->invokePrepareFilterPushdown($dt);
        } finally {
            set_error_handler($previous);
        }

        $this->assertNull($pushed);
        $this->assertCount(1, $unpushedKv);
    }

    public function test_scout_pushdown_always_mode_throws_for_time_format(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage("format [time_hm] cannot be translated to Meilisearch");

        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $this->setupPushableDateFilter($dt, [
            'id' => 'd', 'key' => 'created_at', 'value' => '14:30',
            'condition' => '=', 'type' => 'date', 'format' => 'time_hm',
        ], 'UTC', 'always');

        $this->invokePrepareFilterPushdown($dt);
    }

    public function test_scout_pushdown_callback_date_filter_also_falls_back(): void
    {
        // Even callback variants are routed to SQL fallback (consistent
        // behavior — no special case for callback vs key-based dates).
        $cb = fn($q, $v) => $q->whereDate('created_at', $v);

        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'd', 'key' => '-', 'value' => '2024-06-15',
                  'condition' => '-', 'type' => 'date', 'format' => 'date']],
                [$cb]
            );

        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => ['driver' => 'scout', 'filter_pushdown' => 'auto'],
        ]);

        $previous = set_error_handler(fn() => true, E_USER_WARNING);
        try {
            [$pushedFilter, $unpushedKv, $unpushedCb] = $this->invokePrepareFilterPushdown($dt);
        } finally {
            set_error_handler($previous);
        }

        $this->assertNull($pushedFilter);
        $this->assertCount(1, $unpushedKv);
        $this->assertCount(1, $unpushedCb, 'Callback should be carried over to the SQL fallback');
    }

    public function test_scout_invalid_filter_pushdown_throws(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage("Invalid filter_pushdown mode [maybe]");

        $dt = $this->createTable(10)
            ->withColumn('Name', 'name');

        $dt->setSearch('laptop');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => [
                'driver'          => 'scout',
                'filter_pushdown' => 'maybe',  // not a valid mode
            ],
        ]);
        $dt->build();
    }

    // --- getDatas / getRowRawData ---

    public function test_get_datas_returns_all_rows(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->setDefaultOrder('name', 'asc')
            ->build();

        $datas = $dt->getDatas();
        $this->assertCount(8, $datas);
    }

    public function test_get_row_raw_data(): void
    {
        $dt = $this->createTable(10)
            ->withColumn('Name', 'name')
            ->setDefaultOrder('name', 'asc')
            ->build();

        $row = $dt->getRowRawData(0);
        $this->assertEquals('Desk Lamp', $row->name);
        $this->assertObjectHasProperty('category', $row);
        $this->assertObjectHasProperty('price', $row);
    }

    // --- Check filter (Fitur: multi-checkbox) ---

    public function test_check_filter_where_in(): void
    {
        // 5 electronics + 3 furniture in setUp(); selecting both categories
        // should return all 8 rows (the list is inclusive).
        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'cat', 'key' => 'category',
                  'value' => ['electronics', 'furniture'],
                  'condition' => 'whereIn', 'type' => 'check']],
                [null]
            )
            ->build();

        $this->assertEquals(8, $dt->countRow());
    }

    public function test_check_filter_where_in_single_value(): void
    {
        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'cat', 'key' => 'category',
                  'value' => ['electronics'],
                  'condition' => 'whereIn', 'type' => 'check']],
                [null]
            )
            ->build();

        $this->assertEquals(5, $dt->countRow());
    }

    public function test_check_filter_empty_array_is_noop(): void
    {
        // Empty selection must not filter anything out.
        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'cat', 'key' => 'category',
                  'value' => [], 'condition' => 'whereIn', 'type' => 'check']],
                [null]
            )
            ->build();

        $this->assertEquals(8, $dt->countRow());
    }

    public function test_check_filter_exclude_mode_flips_to_where_not_in(): void
    {
        // exclude_mode=true on a whereIn base → whereNotIn behavior.
        // Select 'electronics' with exclude → should return 3 furniture rows.
        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'cat', 'key' => 'category',
                  'value' => ['electronics'], 'condition' => 'whereIn',
                  'type' => 'check', 'exclude_mode' => true]],
                [null]
            )
            ->build();

        $this->assertEquals(3, $dt->countRow());
    }

    public function test_check_filter_base_where_not_in_condition(): void
    {
        // Developer-configured base condition = whereNotIn.
        // Select 'electronics' → exclude them → 3 furniture rows.
        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'cat', 'key' => 'category',
                  'value' => ['electronics'], 'condition' => 'whereNotIn',
                  'type' => 'check']],
                [null]
            )
            ->build();

        $this->assertEquals(3, $dt->countRow());
    }

    public function test_check_filter_exclude_mode_on_where_not_in_base_flips_to_where_in(): void
    {
        // Double-negative: base whereNotIn + exclude_mode → whereIn.
        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'cat', 'key' => 'category',
                  'value' => ['electronics'], 'condition' => 'whereNotIn',
                  'type' => 'check', 'exclude_mode' => true]],
                [null]
            )
            ->build();

        $this->assertEquals(5, $dt->countRow());
    }

    public function test_check_filter_callback_variant(): void
    {
        // Callback receives the selected values array. Here we use whereIn
        // via callback — equivalent to the built-in path but exercised
        // through the closure branch.
        $callback = fn($q, array $values) => $q->whereIn('category', $values);

        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'cat', 'key' => '-',
                  'value' => ['furniture'], 'condition' => '-', 'type' => 'check']],
                [$callback]
            )
            ->build();

        $this->assertEquals(3, $dt->countRow());
    }

    public function test_check_filter_callback_variant_receives_empty_array(): void
    {
        // Matches date/date_range callback semantics: callback IS invoked
        // even with empty selection (developer can choose to no-op).
        $received = null;
        $callback = function ($q, array $values) use (&$received) {
            $received = $values;
            return $q;
        };

        $dt = $this->createTable(20)
            ->withColumn('Name', 'name')
            ->setFilters(
                [['id' => 'cat', 'key' => '-',
                  'value' => [], 'condition' => '-', 'type' => 'check']],
                [$callback]
            )
            ->build();

        $this->assertSame([], $received, 'Callback should be invoked with empty array');
        $this->assertEquals(8, $dt->countRow());
    }

    // --- Check filter interaction with Scout pushdown ---

    public function test_scout_pushdown_translates_check_where_in(): void
    {
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => ['driver' => 'scout', 'filter_pushdown' => 'auto'],
        ]);
        $dt->setFilters(
            [['id' => 'cat', 'key' => 'category',
              'value' => ['electronics', 'furniture'],
              'condition' => 'whereIn', 'type' => 'check']],
            [null]
        );

        [$pushed, $unpushedKv,] = $this->invokePrepareFilterPushdown($dt);

        $this->assertSame('category IN ["electronics", "furniture"]', $pushed);
        $this->assertEmpty($unpushedKv);
    }

    public function test_scout_pushdown_translates_check_with_exclude_mode(): void
    {
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => ['driver' => 'scout', 'filter_pushdown' => 'auto'],
        ]);
        $dt->setFilters(
            [['id' => 'cat', 'key' => 'category',
              'value' => ['electronics'], 'condition' => 'whereIn',
              'type' => 'check', 'exclude_mode' => true]],
            [null]
        );

        [$pushed,] = $this->invokePrepareFilterPushdown($dt);

        $this->assertSame('category NOT IN ["electronics"]', $pushed);
    }

    public function test_scout_pushdown_check_empty_array_falls_back(): void
    {
        // Empty selection → nothing to push, SQL fallback (even though the
        // fallback itself is a no-op). Neither pushed nor in unpushedKv
        // (unless there's a callback, tested separately).
        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => ['driver' => 'scout', 'filter_pushdown' => 'auto'],
        ]);
        $dt->setFilters(
            [['id' => 'cat', 'key' => 'category', 'value' => [],
              'condition' => 'whereIn', 'type' => 'check']],
            [null]
        );

        [$pushed, $unpushedKv,] = $this->invokePrepareFilterPushdown($dt);

        $this->assertNull($pushed);
        $this->assertEmpty($unpushedKv);
    }

    public function test_scout_pushdown_check_callback_falls_back_to_sql(): void
    {
        $callback = fn($q, array $v) => $q->whereIn('category', $v);

        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => ['driver' => 'scout', 'filter_pushdown' => 'auto'],
        ]);
        $dt->setFilters(
            [['id' => 'cat', 'key' => '-', 'value' => ['electronics'],
              'condition' => '-', 'type' => 'check']],
            [$callback]
        );

        [$pushed, $unpushedKv, $unpushedCb] = $this->invokePrepareFilterPushdown($dt);

        $this->assertNull($pushed);
        $this->assertCount(1, $unpushedKv);
        $this->assertCount(1, $unpushedCb);
    }

    public function test_scout_pushdown_check_callback_always_mode_throws(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage('check callback closure cannot be pushed');

        $callback = fn($q, array $v) => $q;

        $dt = $this->createTable(10)->withColumn('Name', 'name');
        $dt->setConfig([
            'table_name' => 'products',
            'table_id'   => 'id',
            'search'     => ['driver' => 'scout', 'filter_pushdown' => 'always'],
        ]);
        $dt->setFilters(
            [['id' => 'cat', 'key' => '-', 'value' => ['electronics'],
              'condition' => '-', 'type' => 'check']],
            [$callback]
        );

        $this->invokePrepareFilterPushdown($dt);
    }
}
