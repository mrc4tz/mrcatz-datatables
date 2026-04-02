<?php

namespace MrCatz\DataTable\Tests\Unit;

use MrCatz\DataTable\MrCatzDataTableFilter;
use PHPUnit\Framework\TestCase;

class MrCatzDataTableFilterTest extends TestCase
{
    public function test_create_filter_with_array_data(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Active'],
            ['id' => 2, 'name' => 'Inactive'],
        ];

        $filter = MrCatzDataTableFilter::create(
            id: 'status',
            label: 'Status',
            data: $data,
            value: 'id',
            option: 'name',
            key: 'status_id'
        )->get();

        $df = $filter->getDataFilter();

        $this->assertEquals('status', $df['id']);
        $this->assertEquals('Status', $df['label']);
        $this->assertEquals('id', $df['value']);
        $this->assertEquals('name', $df['option']);
        $this->assertEquals('status_id', $df['key']);
        $this->assertEquals('=', $df['condition']);
        $this->assertTrue($df['show']);
        $this->assertCount(2, $df['data']);
        $this->assertNull($filter->getCallback());
    }

    public function test_create_filter_with_custom_condition(): void
    {
        $filter = MrCatzDataTableFilter::create(
            id: 'price',
            label: 'Min Price',
            data: [['id' => 100, 'name' => '$100+']],
            value: 'id',
            option: 'name',
            key: 'price',
            show: true,
            condition: '>='
        )->get();

        $df = $filter->getDataFilter();
        $this->assertEquals('>=', $df['condition']);
    }

    public function test_create_filter_hidden_by_default(): void
    {
        $filter = MrCatzDataTableFilter::create(
            id: 'sub',
            label: 'Subcategory',
            data: [],
            value: 'id',
            option: 'name',
            key: 'subcategory_id',
            show: false
        )->get();

        $df = $filter->getDataFilter();
        $this->assertFalse($df['show']);
    }

    public function test_create_with_callback(): void
    {
        $callback = function ($query, $value) {
            return $query->where('type', $value);
        };

        $filter = MrCatzDataTableFilter::createWithCallback(
            id: 'type',
            label: 'Type',
            data: [['id' => 'a', 'name' => 'Type A']],
            value: 'id',
            option: 'name',
            callback: $callback
        )->get();

        $df = $filter->getDataFilter();

        $this->assertEquals('type', $df['id']);
        $this->assertEquals('-', $df['key']);
        $this->assertEquals('-', $df['condition']);
        $this->assertTrue($df['show']);
        $this->assertNotNull($filter->getCallback());
        $this->assertIsCallable($filter->getCallback());
    }

    public function test_create_with_callback_hidden(): void
    {
        $filter = MrCatzDataTableFilter::createWithCallback(
            id: 'custom',
            label: 'Custom',
            data: [],
            value: 'id',
            option: 'name',
            callback: fn($q, $v) => $q,
            show: false
        )->get();

        $df = $filter->getDataFilter();
        $this->assertFalse($df['show']);
    }

    public function test_create_filter_with_json_string_data(): void
    {
        $jsonData = json_encode([
            ['id' => 1, 'name' => 'One'],
            ['id' => 2, 'name' => 'Two'],
        ]);

        $filter = MrCatzDataTableFilter::create(
            id: 'test',
            label: 'Test',
            data: $jsonData,
            value: 'id',
            option: 'name',
            key: 'test_id'
        )->get();

        $df = $filter->getDataFilter();
        $this->assertIsArray($df['data']);
        $this->assertCount(2, $df['data']);
    }

    public function test_get_data_filter_returns_null_before_get(): void
    {
        $filter = MrCatzDataTableFilter::create(
            id: 'test',
            label: 'Test',
            data: [],
            value: 'id',
            option: 'name',
            key: 'key'
        );

        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage('not initialized');
        $filter->getDataFilter();
    }
}
