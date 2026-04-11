<?php

namespace MrCatz\DataTable\Tests\Fixtures;

use Illuminate\Support\Facades\DB;
use MrCatz\DataTable\MrCatzDataTableFilter;
use MrCatz\DataTable\MrCatzDataTables;
use MrCatz\DataTable\MrCatzDataTablesComponent;

class ProductTableWithDateFilterComponent extends MrCatzDataTablesComponent
{
    public $tableTitle = 'Products with Date Filter';

    public function baseQuery()
    {
        return DB::table('products');
    }

    public function setTable(): MrCatzDataTables
    {
        return $this->CreateMrCatzTable()
            ->withColumnIndex('No')
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category')
            ->setDefaultOrder('name', 'asc');
    }

    public function setFilter(): array
    {
        return [
            // Single date filter on created_at
            MrCatzDataTableFilter::createDate(
                id: 'created_on',
                label: 'Created On',
                key: 'created_at',
                format: 'date',
                condition: '=',
            )->get(),

            // Date range filter on created_at
            MrCatzDataTableFilter::createDateRange(
                id: 'created_period',
                label: 'Created Period',
                key: 'created_at',
                format: 'date',
                minDate: '2020-01-01',
                maxDate: '2030-12-31',
            )->get(),
        ];
    }

    public function configTable()
    {
        return ['table_name' => 'products', 'table_id' => 'id'];
    }
}
