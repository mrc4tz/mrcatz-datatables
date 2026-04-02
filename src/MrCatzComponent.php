<?php

namespace MrCatz\DataTable;

use Livewire\Component;
use Livewire\Attributes\On;

class MrCatzComponent extends Component
{
    public string $title = '';
    public string $form_title = '';
    public string $deleted_text = '';
    public bool $isEdit = false;
    public mixed $id = null;
    public array $breadcrumbs = [];
    public int $index = -1;

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function saveData(): void {}
    public function dropData(): void {}

    #[On(MrCatzEvent::PREPARE_ADD)]
    public function listenAddData(): void
    {
        $this->index = -1;
        $this->isEdit = false;
        $this->prepareAddData();
    }

    public function prepareAddData(): void {}

    public function selectChangeValue(mixed $value, mixed $id): void {}

    #[On(MrCatzEvent::PREPARE_EDIT)]
    public function listenEditData(array $data): void
    {
        $this->isEdit = true;
        $this->prepareEditData($data);
    }

    public function prepareEditData(array $data): void {}

    #[On(MrCatzEvent::PREPARE_DELETE)]
    public function listenDeleteData(array $data): void
    {
        $this->prepareDeleteData($data);
    }

    public function prepareDeleteData(array $data): void {}

    #[On(MrCatzEvent::BULK_DELETE)]
    public function listenBulkDeleteData(array $selectedRows): void
    {
        $this->dropBulkData($selectedRows);
    }

    public function dropBulkData(array $selectedRows): void {}

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
