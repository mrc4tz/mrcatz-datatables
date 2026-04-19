<?php

namespace MrCatz\DataTable\Tests\Fixtures;

use Illuminate\Support\Facades\DB;
use MrCatz\DataTable\MrCatzDataTableFilter;
use MrCatz\DataTable\MrCatzDataTables;
use MrCatz\DataTable\MrCatzDataTablesComponent;

/**
 * Fixture for setFilterData / setFilterDateBounds runtime-override tests.
 *
 * Defines one select filter (`category`) and one date filter (`created_on`)
 * plus a public `altCategoryCallback()` method that the tests can register
 * via the callback-override mechanism.
 */
class ProductTableWithOverrideFilterComponent extends MrCatzDataTablesComponent
{
    public $tableTitle = 'Products with Overridable Filters';

    public function baseQuery()
    {
        return DB::table('products');
    }

    public function setTable(): MrCatzDataTables
    {
        return $this->CreateMrCatzTable()
            ->withColumn('Name', 'name')
            ->withColumn('Category', 'category')
            ->setDefaultOrder('name', 'asc');
    }

    public function configTable()
    {
        return ['table_name' => 'products', 'table_id' => 'id'];
    }

    public function setFilter(): array
    {
        return [
            MrCatzDataTableFilter::create(
                id: 'category',
                label: 'Category',
                data: [
                    ['id' => 'electronics', 'name' => 'Electronics'],
                    ['id' => 'furniture',   'name' => 'Furniture'],
                ],
                value: 'id',
                option: 'name',
                key: 'category',
            )->get(),

            MrCatzDataTableFilter::createDate(
                id: 'created_on',
                label: 'Created On',
                key: 'created_at',
                format: 'date',
                condition: '>=',
            )->get(),

            // Check filter exercised by the callback-override-on-check test
            // (catches the `?Closure` type-hint mismatch regression).
            MrCatzDataTableFilter::createCheck(
                id: 'category_multi',
                label: 'Category (multi)',
                data: [
                    ['id' => 'electronics', 'name' => 'Electronics'],
                    ['id' => 'furniture',   'name' => 'Furniture'],
                ],
                value: 'id',
                option: 'name',
                key: 'category',
            )->get(),

            // Standalone "driver" select — its onFilterChanged resets + re-seeds
            // the `category` filter. Used by the URL-boot regression test to
            // prove that Phase-2 resetFilter() doesn't clobber Phase-3
            // restoration of a URL-provided category value.
            MrCatzDataTableFilter::create(
                id: 'category_driver',
                label: 'Category Driver',
                data: [['id' => 'default', 'name' => 'Default']],
                value: 'id',
                option: 'name',
                key: '-',
            )->get(),
        ];
    }

    /**
     * Public callable registered as a callback-override target in the
     * setFilterData test on a SELECT filter — scalar $value signature.
     */
    public function altCategoryCallback($query, $value)
    {
        return $query->where('category', 'LIKE', $value . '%');
    }

    /**
     * Callback-override target for the CHECK filter branch of the test suite.
     * Check filters invoke the callback with an array of selected values.
     */
    public function altCheckCategoryCallback($query, array $values)
    {
        if (empty($values)) return $query;
        return $query->where(function ($q) use ($values) {
            foreach ($values as $v) {
                $q->orWhere('category', 'LIKE', $v . '%');
            }
        });
    }

    /**
     * Simulates a "driver" filter whose onFilterChanged resets + re-seeds the
     * `category` filter — same shape the demo uses to switch between
     * category / status / sku sources. Used by the URL-boot regression test
     * to verify that activeFilters restoration survives a Phase-2 resetFilter.
     */
    public function onFilterChanged($id, $value)
    {
        if ($id === 'category_driver') {
            // Same shape as the demo's `category_source` handler — reset
            // the target filter before re-seeding so stale picks from a
            // different source can't linger. The URL-boot regression test
            // verifies that this reset doesn't clobber a Phase-3-restored
            // URL value.
            $this->clearFilterOverride('category_multi');
            $this->resetFilter('category_multi');
            $this->setFilterData(
                'category_multi',
                data: [
                    ['id' => 'electronics', 'name' => 'Electronics'],
                    ['id' => 'furniture',   'name' => 'Furniture'],
                ],
            );
        }
    }
}
