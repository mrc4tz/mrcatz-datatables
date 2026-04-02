<?php

namespace MrCatz\DataTable\Concerns;

use MrCatz\DataTable\Exceptions\MrCatzException;
use MrCatz\DataTable\MrCatzEvent;

trait HasFilters
{
    public $dataFilters = [];
    public $activeFilters = [];
    public $filterShow = [];
    public $filterData = [];

    public $default_filter_value = '';
    private array $filterChangeStack = [];

    protected function bootFilters(): void
    {
        foreach ($this->setFilter() as $f => $filter) {
            $this->filterShow[$f] = $filter->getDataFilter()['show'];
            $this->filterData[$f] = $filter->getDataFilter()['data'];
        }

        if (!empty($this->filterUrlParams)) {
            // Snapshot URL params before any mutation by onFilterChanged/resetFilter
            $savedUrlParams = $this->filterUrlParams;

            foreach ($savedUrlParams as $id => $value) {
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

            // Trigger onFilterChanged so dependent filters are initialized
            // (e.g. show/hide, load dropdown data)
            foreach ($savedUrlParams as $id => $value) {
                if (!empty($value)) {
                    $this->onFilterChanged($id, $value);
                }
            }

            // Restore values that were cleared by resetFilter() inside onFilterChanged()
            foreach ($savedUrlParams as $id => $value) {
                if (!empty($value)) {
                    foreach ($this->activeFilters as $i => $af) {
                        if ($af['id'] === $id) {
                            $this->activeFilters[$i]['value'] = $value;
                            break;
                        }
                    }
                }
            }

            // Re-sync URL params so they match the restored activeFilters
            $this->syncFilterUrl();
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
        throw MrCatzException::filterNotFound($id);
    }

    public function resetFilter(string $id): void
    {
        $found = false;
        foreach ($this->activeFilters as $i => $af) {
            if ($af['id'] === $id) {
                $this->activeFilters[$i]['value'] = null;
                $found = true;
                break;
            }
        }
        if (!$found) {
            // Filter may not be active yet — not an error, just no-op
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
        throw MrCatzException::filterNotFound($id);
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
        $this->safeFilterChanged($id, $filterValue);
    }

    private function safeFilterChanged(string $id, mixed $value): void
    {
        if (in_array($id, $this->filterChangeStack)) {
            $this->filterChangeStack = [];
            return; // Circular dependency detected — break the loop
        }
        $this->filterChangeStack[] = $id;
        $this->onFilterChanged($id, $value);
        array_pop($this->filterChangeStack);
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
        throw MrCatzException::filterNotFound($id);
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
