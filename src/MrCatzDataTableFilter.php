<?php

namespace MrCatz\DataTable;

class MrCatzDataTableFilter
{
    private $id;
    private $data;
    private $value;
    private $option;
    private $key;
    private $label;
    private $condition;
    private $callback;
    private $dataFilter;
    private $show;

    public static function create($id, $label, $data, $value, $option, $key, $show = true, $condition = '=')
    {
        $dataFilter = new MrCatzDataTableFilter();
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

    public static function createWithCallback($id, $label, $data, $value, $option, callable $callback, $show = true)
    {
        $dataFilter = new MrCatzDataTableFilter();
        $dataFilter->id = $id;
        $dataFilter->data = $data;
        $dataFilter->value = $value;
        $dataFilter->option = $option;
        $dataFilter->key = '-';
        $dataFilter->label = $label;
        $dataFilter->condition = '-';
        $dataFilter->callback = $callback;
        $dataFilter->show = $show;
        return $dataFilter;
    }

    public function get()
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

    public function getDataFilter() { return $this->dataFilter; }
    public function getCallback() { return $this->callback; }
}
