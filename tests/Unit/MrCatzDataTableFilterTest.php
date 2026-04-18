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

    // --- Date filter factories ---

    public function test_create_date_filter(): void
    {
        $filter = MrCatzDataTableFilter::createDate(
            id: 'order_date',
            label: 'Order Date',
            key: 'orders.order_date',
            format: 'date',
            condition: '>=',
            minDate: '2020-01-01',
            maxDate: '2030-12-31'
        )->get();

        $df = $filter->getDataFilter();

        $this->assertEquals('order_date', $df['id']);
        $this->assertEquals('Order Date', $df['label']);
        $this->assertEquals('orders.order_date', $df['key']);
        $this->assertEquals('>=', $df['condition']);
        $this->assertEquals('date', $df['type']);
        $this->assertEquals('date', $df['format']);
        $this->assertEquals('2020-01-01', $df['min_date']);
        $this->assertEquals('2030-12-31', $df['max_date']);
        $this->assertTrue($df['show']);
        $this->assertNull($filter->getCallback());
    }

    public function test_create_date_filter_with_callback(): void
    {
        $cb = fn($q, $v) => $q->whereDate('orders.created_at', $v);

        $filter = MrCatzDataTableFilter::createDateWithCallback(
            id: 'order_date',
            label: 'Order Date',
            callback: $cb,
            format: 'datetime'
        )->get();

        $df = $filter->getDataFilter();

        $this->assertEquals('date', $df['type']);
        $this->assertEquals('datetime', $df['format']);
        $this->assertEquals('-', $df['key']);
        $this->assertNotNull($filter->getCallback());
    }

    public function test_create_date_range_filter(): void
    {
        $filter = MrCatzDataTableFilter::createDateRange(
            id: 'period',
            label: 'Period',
            key: 'orders.order_date',
            format: 'month_year'
        )->get();

        $df = $filter->getDataFilter();

        $this->assertEquals('date_range', $df['type']);
        $this->assertEquals('month_year', $df['format']);
        $this->assertEquals('orders.order_date', $df['key']);
        $this->assertNull($filter->getCallback());
    }

    public function test_create_date_range_filter_with_callback(): void
    {
        $cb = fn($q, $v) => $q->whereBetween('orders.created_at', [$v['from'], $v['to']]);

        $filter = MrCatzDataTableFilter::createDateRangeWithCallback(
            id: 'period',
            label: 'Period',
            callback: $cb,
            format: 'date'
        )->get();

        $df = $filter->getDataFilter();

        $this->assertEquals('date_range', $df['type']);
        $this->assertEquals('date', $df['format']);
        $this->assertEquals('-', $df['key']);
        $this->assertNotNull($filter->getCallback());
    }

    public function test_create_date_filter_invalid_format_throws(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage("Invalid date filter format [unix]");

        MrCatzDataTableFilter::createDate(
            id: 'd',
            label: 'D',
            key: 'd',
            format: 'unix'
        );
    }

    public function test_create_date_filter_invalid_condition_throws(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage("Invalid date filter condition [LIKE]");

        MrCatzDataTableFilter::createDate(
            id: 'd',
            label: 'D',
            key: 'd',
            condition: 'LIKE'
        );
    }

    public function test_select_filter_defaults_to_select_type(): void
    {
        // Backward compat: existing factories should produce type='select'
        $filter = MrCatzDataTableFilter::create(
            id: 'cat',
            label: 'Cat',
            data: [],
            value: 'id',
            option: 'name',
            key: 'cat_id'
        )->get();

        $df = $filter->getDataFilter();
        $this->assertEquals('select', $df['type']);
        $this->assertEquals('', $df['format']);
        $this->assertNull($df['min_date']);
        $this->assertNull($df['max_date']);
    }

    public function test_create_check_filter(): void
    {
        $filter = MrCatzDataTableFilter::createCheck(
            id: 'status',
            label: 'Status',
            data: [['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'B']],
            value: 'id',
            option: 'name',
            key: 'status_id'
        )->get();

        $df = $filter->getDataFilter();
        $this->assertEquals('check', $df['type']);
        $this->assertEquals('whereIn', $df['condition']);
        $this->assertFalse($df['allow_exclude']);
        $this->assertSame(5, $df['search_threshold']);
        $this->assertNull($filter->getCallback());
    }

    public function test_create_check_filter_with_custom_condition(): void
    {
        $filter = MrCatzDataTableFilter::createCheck(
            id: 'status',
            label: 'Status',
            data: [],
            value: 'id',
            option: 'name',
            key: 'status_id',
            condition: 'whereNotIn'
        )->get();

        $this->assertEquals('whereNotIn', $filter->getDataFilter()['condition']);
    }

    public function test_create_check_invalid_condition_throws(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage('Invalid check filter condition [LIKE]');

        MrCatzDataTableFilter::createCheck(
            id: 'status',
            label: 'Status',
            data: [],
            value: 'id',
            option: 'name',
            key: 'status_id',
            condition: 'LIKE'
        );
    }

    public function test_create_check_with_allow_exclude(): void
    {
        $filter = MrCatzDataTableFilter::createCheck(
            id: 'status', label: 'Status', data: [], value: 'id', option: 'name', key: 'status_id'
        )->allowExclude()->get();

        $this->assertTrue($filter->getDataFilter()['allow_exclude']);
    }

    public function test_create_check_with_search_threshold(): void
    {
        $filter = MrCatzDataTableFilter::createCheck(
            id: 'status', label: 'Status', data: [], value: 'id', option: 'name', key: 'status_id'
        )->allowSearchWhen(20)->get();

        $this->assertSame(20, $filter->getDataFilter()['search_threshold']);

        // Null disables search entirely regardless of option count
        $filter2 = MrCatzDataTableFilter::createCheck(
            id: 'status', label: 'Status', data: [], value: 'id', option: 'name', key: 'status_id'
        )->allowSearchWhen(null)->get();

        $this->assertNull($filter2->getDataFilter()['search_threshold']);
    }

    public function test_create_check_with_callback(): void
    {
        $cb = fn($q, array $values) => $q->whereIn('status', $values);

        $filter = MrCatzDataTableFilter::createCheckWithCallback(
            id: 'status',
            label: 'Status',
            data: [],
            value: 'id',
            option: 'name',
            callback: $cb
        )->get();

        $df = $filter->getDataFilter();
        $this->assertEquals('check', $df['type']);
        $this->assertEquals('-', $df['key']);
        $this->assertEquals('-', $df['condition']);
        $this->assertIsCallable($filter->getCallback());
    }

    public function test_allow_exclude_rejected_on_callback_variant(): void
    {
        $this->expectException(\MrCatz\DataTable\Exceptions\MrCatzException::class);
        $this->expectExceptionMessage('allowExclude() is not supported on createCheckWithCallback');

        MrCatzDataTableFilter::createCheckWithCallback(
            id: 'status',
            label: 'Status',
            data: [],
            value: 'id',
            option: 'name',
            callback: fn($q, $v) => $q
        )->allowExclude();
    }
}
