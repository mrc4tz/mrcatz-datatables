<?php

namespace MrCatz\DataTable;

class MrCatzDataTableFilter
{
    private string $id;
    private array|string $data;
    private string $value;
    private string $option;
    private string $key;
    private string $label;
    private string $condition;
    private ?\Closure $callback = null;
    private ?array $dataFilter = null;
    private bool $show;

    public static function create(
        string $id,
        string $label,
        array|string $data,
        string $value,
        string $option,
        string $key,
        bool $show = true,
        string $condition = '='
    ): self {
        $dataFilter = new self();
        $dataFilter->id = $id;
        $dataFilter->data = $data;
        $dataFilter->value = $value;
        $dataFilter->option = $option;
        $dataFilter->key = $key;
        $dataFilter->label = $label;
        $dataFilter->condition = $condition;
        $dataFilter->callback = null;
        $dataFilter->show = $show;
        return $dataFilter;
    }

    public static function createWithCallback(
        string $id,
        string $label,
        array|string $data,
        string $value,
        string $option,
        callable $callback,
        bool $show = true
    ): self {
        $dataFilter = new self();
        $dataFilter->id = $id;
        $dataFilter->data = $data;
        $dataFilter->value = $value;
        $dataFilter->option = $option;
        $dataFilter->key = '-';
        $dataFilter->label = $label;
        $dataFilter->condition = '-';
        $dataFilter->callback = \Closure::fromCallable($callback);
        $dataFilter->show = $show;
        return $dataFilter;
    }

    public function get(): self
    {
        if (is_array($this->data)) {
            $data = $this->data;
        } else {
            $data = json_decode($this->data, true);
        }
        $this->dataFilter = [
            'id' => $this->id,
            'label' => $this->label,
            'value' => $this->value,
            'option' => $this->option,
            'key' => $this->key,
            'data' => $data,
            'condition' => $this->condition,
            'show' => $this->show,
        ];
        return $this;
    }

    public function getDataFilter(): ?array
    {
        if ($this->dataFilter === null) {
            throw new \MrCatz\DataTable\Exceptions\MrCatzException(
                "Filter [{$this->id}] not initialized. Did you forget to call ->get()?"
            );
        }
        return $this->dataFilter;
    }
    public function getCallback(): ?callable { return $this->callback; }
}
