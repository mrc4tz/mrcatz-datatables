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

    /**
     * Whether clicking outside the add/edit modal (backdrop click) closes it.
     *
     * Default is `false` so users don't lose in-progress edits when they
     * accidentally click off the modal. They can still close via the X
     * button, the Cancel button, or pressing Escape. Override on your child
     * component to enable backdrop dismissal:
     *
     *     public $modalDismissOnClickOutside = true;
     */
    public $modalDismissOnClickOutside = false;

    /**
     * Whether clicking outside the delete confirmation modal closes it.
     * Default `true` because the delete dialog is small and users almost
     * always click outside it as a "nope, abort" gesture.
     */
    public $deleteModalDismissOnClickOutside = true;

    /**
     * Render the add/edit form as a FULL-PAGE VIEW that replaces the
     * datatable, instead of opening it as a dialog. Best for long-form
     * content (articles, product descriptions, rich editors) where the
     * dialog feels cramped and "tab-switch" UX reads more naturally.
     *
     * When enabled:
     *   - Clicking Add / Edit swaps the datatable for the form page.
     *   - Save success or Back button returns to the datatable.
     *   - No backdrop, no ESC-to-close, no click-outside dismiss.
     *
     *     public $modalFullScreen = true;
     */
    public $modalFullScreen = false;

    /**
     * Internal flag: whether the full-page form is currently visible.
     * Flipped on by listenAddData / listenEditData when $modalFullScreen
     * is true, and back off by closeFormPage() on save or cancel.
     */
    public $formPageVisible = false;

    /**
     * Close the full-page form and return to the datatable view.
     * Called by the Cancel button and by JS after a successful save.
     */
    public function closeFormPage(): void
    {
        $this->formPageVisible = false;
    }

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
        if ($this->modalFullScreen) {
            $this->formPageVisible = true;
        }
    }

    public function prepareAddData() {}

    public function selectChangeValue($value, $id) {}

    #[On(MrCatzEvent::PREPARE_EDIT)]
    public function listenEditData($data): void
    {
        $this->isEdit = true;
        $this->prepareEditData($data);
        if ($this->modalFullScreen) {
            $this->formPageVisible = true;
        }
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
