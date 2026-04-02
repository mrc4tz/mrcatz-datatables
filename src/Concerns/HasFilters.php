<?php

namespace MrCatz\DataTable\Concerns;

use MrCatz\DataTable\MrCatzEvent;

trait HasFilters
{
    public $dataFilters = [];
    public $activeFilters = [];
    public $filterShow = [];
    public $filterData = [];

    public $default_filter_value = '';

    protected function bootFilters(): void
    {
        foreach ($this->setFilter() as $f => $filter) {
            $this->filterShow[$f] = $filter->getDataFilter()['show'];
            $this->filterData[$f] = $filter->getDataFilter()['data'];
        }

        if (!empty($this->filterUrlParams)) {
            foreach ($this->filterUrlParams as $id => $value) {
                $config = $this->findFilterConfigById($id);
                if ($config) {
                    $this->activeFilters[] = [
                        'id' => $id,
                        'key' => $config['key'],
                        'value' => $value,
                        'condition' => $config['condition'],
                    ];
                }
            }

            // Trigger onFilterChanged for each active filter from URL
            // so dependent filters (show/hide, data loading) are initialized
            foreach ($this->filterUrlParams as $id => $value) {
                if (!empty($value)) {
                    $this->onFilterChanged($id, $value);
                }
            }
        }
    }

    // Override-able — no strict types for backward compatibility
    public function setFilter() { return []; }

    public function setFilterShow(string $id, bool $show): void
    {
        foreach ($this->setFilter() as $f => $filter) {
            if ($filter->getDataFilter()['id'] == $id) {
                $this->filterShow[$f] = $show;
                return;
            }
        }
    }

    public function resetFilter(string $id): void
    {
        foreach ($this->activeFilters as $i => $af) {
            if ($af['id'] === $id) {
                $this->activeFilters[$i]['value'] = null;
                break;
            }
        }
        $this->syncFilterUrl();
        $this->findData();
    }

    public function setFilterData(string $id, array|object $data): void
    {
        foreach ($this->setFilter() as $f => $filter) {
            if ($filter->getDataFilter()['id'] == $id) {
                $this->filterData[$f] = is_array($data) ? $data : json_decode(json_encode($data), true);
                return;
            }
        }
    }

    public function change(string $id, mixed $value): void
    {
        $filter = $this->findFilterById($id);
        $filterValue = $value === '' ? null : $value;

        $found = false;
        foreach ($this->activeFilters as $i => $af) {
            if ($af['id'] === $id) {
                $this->activeFilters[$i]['value'] = $filterValue;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->activeFilters[] = [
                'id' => $id,
                'key' => $filter['key'],
                'value' => $filterValue,
                'condition' => $filter['condition'],
            ];
        }

        $this->syncFilterUrl();
        $this->setPage(1);
        $this->clearSelection();
        $this->findData();
        $this->onFilterChanged($id, $filterValue);
    }

    public function onFilterChanged($id, $value) {}

    private function findFilterConfigById(string $id): ?array
    {
        foreach ($this->setFilter() as $filter) {
            $df = $filter->getDataFilter();
            if ($df['id'] == $id) return $df;
        }
        return null;
    }

    private function syncFilterUrl(): void
    {
        $this->filterUrlParams = [];
        foreach ($this->activeFilters as $af) {
            if (!empty($af['value'])) {
                $this->filterUrlParams[$af['id']] = $af['value'];
            }
        }
    }

    private function getDataFilter(): array
    {
        $df = [];
        foreach ($this->setFilter() as $filter) {
            array_push($df, $filter->getDataFilter());
        }
        return $df;
    }

    private function findFilterById(string $id): array
    {
        foreach ($this->dataFilters as $filter) {
            if ($filter['id'] == $id) return $filter;
        }
        return [];
    }

    private function findFilterCallbackById(string $id): ?callable
    {
        foreach ($this->setFilter() as $filter) {
            if ($filter->getDataFilter()['id'] == $id) return $filter->getCallback();
        }
        return null;
    }

    private function buildKeyValue(): array { return array_values($this->activeFilters); }

    private function buildFilterCallbacks(): array
    {
        $callbacks = [];
        foreach ($this->activeFilters as $filter) {
            $callbacks[] = $this->findFilterCallbackById($filter['id']);
        }
        return $callbacks;
    }
}
