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
}
