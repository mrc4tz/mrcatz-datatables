<?php

namespace MrCatz\DataTable;

use Livewire\Component;
use Livewire\Attributes\On;

class MrCatzComponent extends Component
{
    public $title = '';
    public $form_title = '';
    public $deleted_text = '';
    public $isEdit = false;
    public $id = null;
    public $breadcrumbs = [];
    public $index = -1;

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function saveData() {}
    public function dropData() {}

    #[On('prepareAddData')]
    public function listenAddData()
    {
        $this->index = -1;
        $this->isEdit = false;
        $this->prepareAddData();
    }

    public function prepareAddData() {}

    public function selectChangeValue($value, $id) {}

    #[On('prepareEditData')]
    public function listenEditData($data)
    {
        $this->isEdit = true;
        $this->prepareEditData($data);
    }

    public function prepareEditData($data) {}

    #[On('prepareDeleteData')]
    public function listenDeleteData($data)
    {
        $this->prepareDeleteData($data);
    }

    public function prepareDeleteData($data) {}

    #[On('bulkDeleteData')]
    public function listenBulkDeleteData($selectedRows)
    {
        $this->dropBulkData($selectedRows);
    }

    public function dropBulkData($selectedRows) {}

    public function dispatch_to_view($condition, $type)
    {
        if (!$condition) {
            $this->dispatch('refresh-data', [
                'status' => false,
                'text' => $this->title . ' ' . mrcatz_lang('failed')
            ]);
            return;
        }

        $text = match ($type) {
            'insert' => mrcatz_lang('added'),
            'update' => mrcatz_lang('updated'),
            'delete' => mrcatz_lang('deleted'),
            default => '',
        };

        $this->dispatch('refresh-data', [
            'status' => true,
            'text' => $this->title . ' ' . mrcatz_lang('success') . ' ' . $text
        ]);
    }

    public function show_notif($type, $text)
    {
        $this->dispatch('show-notif', [
            'type' => $type,
            'text' => $text
        ]);
    }

    public function notice($type, $text)
    {
        $this->dispatch('notice', type: $type, text: $text);
    }
}
