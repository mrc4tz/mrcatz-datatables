<?php

namespace MrCatz\DataTable\Tests\Unit;

use MrCatz\DataTable\Exceptions\MrCatzException;
use MrCatz\DataTable\MrCatzDataTables;
use PHPUnit\Framework\TestCase;

class MrCatzExceptionTest extends TestCase
{
    public function test_column_not_found_throws(): void
    {
        $dt = MrCatzDataTables::with([]);
        $dt->withColumn('Name', 'name');

        $this->expectException(MrCatzException::class);
        $this->expectExceptionMessage('Column index [5] out of range');
        $dt->getHead(5);
    }

    public function test_column_not_found_on_get_key(): void
    {
        $dt = MrCatzDataTables::with([]);
        $dt->withColumn('Name', 'name');

        $this->expectException(MrCatzException::class);
        $dt->getKey(99);
    }

    public function test_valid_column_does_not_throw(): void
    {
        $dt = MrCatzDataTables::with([]);
        $dt->withColumn('Name', 'name');

        $this->assertEquals('Name', $dt->getHead(0));
        $this->assertEquals('name', $dt->getKey(0));
    }

    public function test_exception_messages(): void
    {
        $e = MrCatzException::columnNotFound(3, 2);
        $this->assertStringContainsString('Column index [3]', $e->getMessage());
        $this->assertStringContainsString('2 columns', $e->getMessage());

        $e = MrCatzException::filterNotFound('xyz');
        $this->assertStringContainsString('Filter with ID [xyz]', $e->getMessage());

        $e = MrCatzException::rowNotFound(10, 5);
        $this->assertStringContainsString('Row index [10]', $e->getMessage());
        $this->assertStringContainsString('5 rows', $e->getMessage());
    }
}
