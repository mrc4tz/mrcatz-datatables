<?php

namespace MrCatz\DataTable\Tests\Fixtures;

use Illuminate\Support\Facades\DB;
use MrCatz\DataTable\MrCatzDataTables;
use MrCatz\DataTable\MrCatzDataTablesComponent;

class ProductTableComponent extends MrCatzDataTablesComponent
{
    public $tableTitle = 'Products';
    public $showAddButton = true;
    public $enableKeyboardNav = true;
    public $enableColumnResize = false;
    public $enableColumnVisibility = true;
    public $enableColumnReorder = false;
    public $tableZebraStyle = true;

    public function baseQuery()
    {
        return DB::table('products');
    }

    public function setTable(): MrCatzDataTables
    {
        return $this->CreateMrCatzTable()
            ->withColumnIndex('No')
            ->withColumn('Name', 'name', editable: true, rules: 'required|max:100')
            ->withColumn('Category', 'category')
            ->withColumn('Price', 'price', gravity: 'right', editable: true, rules: 'required|numeric|min:0')
            ->setDefaultOrder('name', 'asc');
    }

    public function configTable()
    {
        return ['table_name' => 'products', 'table_id' => 'id'];
    }

    public function onInlineUpdate($rowData, $columnKey, $newValue)
    {
        $id = is_array($rowData) ? $rowData['id'] : $rowData->id;
        DB::table('products')->where('id', $id)->update([$columnKey => $newValue]);
    }
}
