<?php

namespace MrCatz\DataTable;

use Livewire\Component;
use Livewire\Attributes\On;
use MrCatz\DataTable\Concerns\HasFormBuilder;

class MrCatzComponent extends Component
{
    use HasFormBuilder;
    // Public properties — no strict types to allow child class override without type declaration
    public $title = '';
    public $form_title = '';
    public $deleted_text = '';
    public $isEdit = false;
    public $id = null;
    public $breadcrumbs = [];
    public $index = -1;

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    // Override-able methods — no return type to preserve backward compatibility
    public function saveData() {}
    public function dropData() {}

    #[On(MrCatzEvent::PREPARE_ADD)]
    public function listenAddData(): void
    {
        $this->index = -1;
        $this->isEdit = false;
        $this->prepareAddData();
    }

    public function prepareAddData() {}

    public function selectChangeValue($value, $id) {}

    #[On(MrCatzEvent::PREPARE_EDIT)]
    public function listenEditData($data): void
    {
        $this->isEdit = true;
        $this->prepareEditData($data);
    }

    public function prepareEditData($data) {}

    #[On(MrCatzEvent::PREPARE_DELETE)]
    public function listenDeleteData($data): void
    {
        $this->prepareDeleteData($data);
    }

    public function prepareDeleteData($data) {}

    #[On(MrCatzEvent::BULK_DELETE)]
    public function listenBulkDeleteData($selectedRows): void
    {
        $this->dropBulkData($selectedRows);
    }

    public function dropBulkData($selectedRows) {}

    #[On(MrCatzEvent::INLINE_UPDATE)]
    public function listenInlineUpdate($rowData, $columnKey, $newValue): void
    {
        $this->onInlineUpdate($rowData, $columnKey, $newValue);
    }

    public function onInlineUpdate($rowData, $columnKey, $newValue) {}

    #[On(MrCatzEvent::ROW_CLICK)]
    public function listenRowClick($data): void
    {
        $this->onRowClick($data);
    }

    public function onRowClick($data) {}

    public function dispatch_to_view(bool $condition, string $type): void
    {
        if (!$condition) {
            $this->dispatch(MrCatzEvent::REFRESH_DATA, [
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

        $this->dispatch(MrCatzEvent::REFRESH_DATA, [
            'status' => true,
            'text' => $this->title . ' ' . mrcatz_lang('success') . ' ' . $text
        ]);
    }

    public function show_notif(string $type, string $text): void
    {
        $this->dispatch(MrCatzEvent::SHOW_NOTIF, [
            'type' => $type,
            'text' => $text
        ]);
    }

    public function notice(string $type, string $text): void
    {
        $this->dispatch(MrCatzEvent::NOTICE, type: $type, text: $text);
    }
}
