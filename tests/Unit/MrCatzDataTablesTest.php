<?php

namespace MrCatz\DataTable\Tests\Unit;

use MrCatz\DataTable\MrCatzDataTables;
use PHPUnit\Framework\TestCase;

class MrCatzDataTablesTest extends TestCase
{
    private function createTable(): MrCatzDataTables
    {
        // Use a mock/empty array as data builder for unit tests
        // Full query tests require Laravel app context
        return MrCatzDataTables::with(
            data: [],
            paginateOptions: [5, 10, 15, 20],
            paginate: 10,
            usePagination: true,
            pageName: 'page'
        );
    }

    public function test_with_creates_instance(): void
    {
        $dt = $this->createTable();
        $this->assertInstanceOf(MrCatzDataTables::class, $dt);
    }

    public function test_default_paginate_options(): void
    {
        $dt = $this->createTable();
        $this->assertEquals([5, 10, 15, 20], $dt->paginateOptions);
    }

    public function test_custom_paginate_options(): void
    {
        $dt = MrCatzDataTables::with([], [25, 50, 100], 25);
        $this->assertEquals([25, 50, 100], $dt->paginateOptions);
        $this->assertEquals(25, $dt->getPaginate());
    }

    public function test_default_paginate_from_first_option(): void
    {
        $dt = MrCatzDataTables::with([], [15, 30, 60]);
        $this->assertEquals(15, $dt->getPaginate());
    }

    public function test_get_page_name(): void
    {
        $dt = MrCatzDataTables::with([], pageName: 'custom_page');
        $this->assertEquals('custom_page', $dt->getPageName());
    }

    public function test_set_paginate(): void
    {
        $dt = $this->createTable();
        $dt->setPaginate(25);
        $this->assertEquals(25, $dt->getPaginate());
    }

    public function test_with_column(): void
    {
        $dt = $this->createTable();
        $result = $dt->withColumn('Name', 'name');

        $this->assertSame($dt, $result, 'withColumn should return self for chaining');
        $this->assertEquals(1, $dt->countColumn());
        $this->assertEquals('Name', $dt->getHead(0));
        $this->assertEquals('name', $dt->getKey(0));
        $this->assertNull($dt->getIndex(0));
        $this->assertTrue($dt->getSort(0));
        $this->assertNull($dt->getOrder(0));
        $this->assertFalse($dt->isUppercase(0));
        $this->assertFalse($dt->isTH(0));
        $this->assertEquals('left', $dt->gravity(0));
    }

    public function test_with_column_options(): void
    {
        $dt = $this->createTable();
        $dt->withColumn('Price', 'price', uppercase: true, th: true, sort: false, gravity: 'right');

        $this->assertTrue($dt->isUppercase(0));
        $this->assertTrue($dt->isTH(0));
        $this->assertFalse($dt->getSort(0));
        $this->assertEquals('right', $dt->gravity(0));
    }

    public function test_with_custom_column(): void
    {
        $dt = $this->createTable();
        $callback = fn($data, $i) => 'custom_' . $i;
        $result = $dt->withCustomColumn('Actions', $callback, 'action_key', false);

        $this->assertSame($dt, $result);
        $this->assertEquals(1, $dt->countColumn());
        $this->assertEquals('Actions', $dt->getHead(0));
        $this->assertEquals('action_key', $dt->getKey(0));
        $this->assertFalse($dt->getSort(0));
    }

    public function test_with_column_index(): void
    {
        $dt = $this->createTable();
        $result = $dt->withColumnIndex('No');

        $this->assertSame($dt, $result);
        $this->assertEquals(1, $dt->countColumn());
        $this->assertEquals('No', $dt->getHead(0));
        $this->assertNull($dt->getKey(0));
        $this->assertEquals('index', $dt->getIndex(0));
        $this->assertFalse($dt->getSort(0));
    }

    public function test_multiple_columns(): void
    {
        $dt = $this->createTable();
        $dt->withColumnIndex('No')
           ->withColumn('Name', 'name')
           ->withColumn('Email', 'email')
           ->withCustomColumn('Actions', fn($d, $i) => 'act');

        $this->assertEquals(4, $dt->countColumn());
        $this->assertEquals('No', $dt->getHead(0));
        $this->assertEquals('Name', $dt->getHead(1));
        $this->assertEquals('Email', $dt->getHead(2));
        $this->assertEquals('Actions', $dt->getHead(3));
    }

    public function test_set_default_order(): void
    {
        $dt = $this->createTable();
        $result = $dt->setDefaultOrder('name', 'asc');
        $this->assertSame($dt, $result);
    }

    public function test_add_order_by(): void
    {
        $dt = $this->createTable();
        $result = $dt->addOrderBy('name', 'asc');
        $this->assertSame($dt, $result);
    }

    public function test_set_order_by_key(): void
    {
        $dt = $this->createTable();
        $dt->withColumn('Name', 'name')
           ->withColumn('Email', 'email');

        $dt->setOrderByKey('name', 'asc');

        $this->assertEquals('asc', $dt->getOrder(0));
        $this->assertNull($dt->getOrder(1));
    }

    public function test_set_search(): void
    {
        $dt = $this->createTable();
        $result = $dt->setSearch('test query');
        $this->assertSame($dt, $result);
    }

    public function test_set_config(): void
    {
        $dt = $this->createTable();
        $result = $dt->setConfig(['table_name' => 'users', 'table_id' => 'id']);
        $this->assertSame($dt, $result);
    }

    public function test_set_config_null_no_effect(): void
    {
        $dt = $this->createTable();
        $result = $dt->setConfig(null);
        $this->assertSame($dt, $result);
    }

    public function test_set_current_page(): void
    {
        $dt = $this->createTable();
        $result = $dt->setCurrentPage(3);
        $this->assertSame($dt, $result);
    }

    public function test_enable_bulk_default(): void
    {
        $dt = $this->createTable();
        $result = $dt->enableBulk();
        $this->assertSame($dt, $result);
    }

    public function test_enable_expand(): void
    {
        $dt = $this->createTable();
        $this->assertFalse($dt->hasExpand());

        $dt->enableExpand(fn($data, $i) => '<div>Details</div>');
        $this->assertTrue($dt->hasExpand());
    }

    public function test_has_data_empty(): void
    {
        $dt = $this->createTable();
        $this->assertFalse($dt->hasData());
    }

    public function test_use_pagination(): void
    {
        $dt = MrCatzDataTables::with([], usePagination: false);
        $this->assertFalse($dt->usePagination);
    }

    public function test_get_data_table_set(): void
    {
        $dt = $this->createTable();
        $dt->withColumn('Name', 'name');

        $set = $dt->getDataTableSet();
        $this->assertIsArray($set);
        $this->assertCount(1, $set);
        $this->assertEquals('Name', $set[0]['head']);
        $this->assertEquals('name', $set[0]['key']);
    }

    public function test_search_word_highlighting(): void
    {
        $dt = $this->createTable();
        $dt->setSearch('john');

        $result = $dt->setSearchWord('John Doe');
        $this->assertStringContainsString("<span class='font-extrabold'>", $result);
        $this->assertStringContainsString('JOHN', $result);
        $this->assertStringContainsString('</span>', $result);
    }

    public function test_search_word_no_search(): void
    {
        $dt = $this->createTable();
        $dt->setSearch('');

        $result = $dt->setSearchWord('John Doe');
        $this->assertEquals('John Doe', $result);
    }

    public function test_search_word_escapes_html(): void
    {
        $dt = $this->createTable();
        $dt->setSearch('script');

        $result = $dt->setSearchWord('<script>alert("xss")</script>');
        $this->assertStringNotContainsString('<script>', $result);
    }

    public function test_search_word_multi_keyword(): void
    {
        $dt = $this->createTable();
        $dt->setSearch('john doe');

        $result = $dt->setSearchWord('John Doe Smith');
        $this->assertStringContainsString('JOHN', $result);
        $this->assertStringContainsString('DOE', $result);
    }

    public function test_fluent_chaining(): void
    {
        $dt = $this->createTable();

        $result = $dt->withColumnIndex('No')
                     ->withColumn('Name', 'name')
                     ->withCustomColumn('Actions', fn($d, $i) => 'act')
                     ->setDefaultOrder('name', 'asc')
                     ->addOrderBy('created_at', 'desc')
                     ->enableBulk()
                     ->enableExpand(fn($d, $i) => '<div></div>');

        $this->assertSame($dt, $result);
        $this->assertEquals(3, $dt->countColumn());
        $this->assertTrue($dt->hasExpand());
    }

    public function test_set_filters(): void
    {
        $dt = $this->createTable();
        $keyValue = [
            ['id' => 'status', 'key' => 'status', 'value' => 'active', 'condition' => '='],
        ];
        $callbacks = [null];

        $result = $dt->setFilters($keyValue, $callbacks);
        $this->assertSame($dt, $result);
    }
}
