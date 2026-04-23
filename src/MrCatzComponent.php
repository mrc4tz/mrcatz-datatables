<?php

namespace MrCatz\DataTable;

use Livewire\Component;
use Livewire\Attributes\On;
use MrCatz\DataTable\Concerns\HasCustomBulkActionModal;
use MrCatz\DataTable\Concerns\HasFormBuilder;

class MrCatzComponent extends Component
{
    use HasFormBuilder;
    use HasCustomBulkActionModal;
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
     * Full-page form container styling. Defaults mirror the datatable
     * child component's $cardContainer / $borderContainer so the form
     * panel reads as a natural extension of the table's look. Override
     * on a child page to match a non-default datatable styling:
     *
     *     public $formPageCard   = false;
     *     public $formPageBorder = true;
     */
    public $formPageCard   = true;
    public $formPageBorder = false;

    /**
     * Bulk action form state. Populated from setBulkForm($id) fields when
     * an action runs in 'form' mode. Lives here (on the page component)
     * so user's processBulkActionData() hook can read the submitted
     * values directly. Always wire:model your bulk fields as
     * "bulkFormData.fieldId" — the Form Builder handles this for you
     * when setBulkForm() returns MrCatzFormField[]; for blade-mode bulk
     * forms you bind inputs manually.
     */
    public $bulkFormData = [];

    /**
     * Which `setPageName()` scope a CRUD flow is currently running under.
     * Set by the `listen*Data()` wrappers below before user hooks fire, and
     * read by `HasFormBuilder::setForm($pageName)` so one page component can
     * host multiple CRUDs (each datatable has its own setPageName). Stays
     * null when the consumer only has a single CRUD on the page — backward
     * compatible with every pre-v1.29.22 page component.
     */
    public $currentCrudPageName = null;

    /**
     * Close the full-page form and return to the datatable view.
     * When `$scroll` is true, dispatches `mrcatz-form-page-closed` so
     * the client can restore scroll position to the top of the
     * datatable. Used by Cancel / successful-save paths. The top-right
     * close (×) passes `false` because its visual affordance is "get
     * out now" — preserving the user's current scroll feels more like
     * a true dismissal than a return-to-top.
     */
    public function closeFormPage(bool $scroll = true): void
    {
        $this->formPageVisible = false;
        // Always dispatch the closed event so the datatable can show
        // itself again (it hid itself on 'opened'). The scroll-to-top
        // behaviour is the separate 'mrcatz-form-page-scroll' event so
        // the × button can close without scrolling.
        $this->dispatch('mrcatz-form-page-closed');
        if ($scroll) {
            $this->dispatch('mrcatz-form-page-scroll');
        }
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    // Override-able methods — no return type to preserve backward compatibility
    public function saveData() {}
    public function dropData() {}

    #[On(MrCatzEvent::PREPARE_ADD)]
    public function listenAddData($pageName = null): void
    {
        $this->currentCrudPageName = $pageName;
        $this->index = -1;
        $this->isEdit = false;
        // Forward $pageName positionally. The parent stub signature stays at
        // 0 params so existing overrides `prepareAddData()` remain LSP-valid
        // (a child is NOT allowed to declare FEWER parameters than its parent).
        // PHP silently discards the extra positional argument when the child
        // declares no param for it; new consumers can widen their override
        // with `$pageName = null` (LSP allows adding optional parameters on
        // a child) to receive the value explicitly. Either way also works
        // via `$this->currentCrudPageName` set above.
        $this->prepareAddData($pageName);
        if ($this->modalFullScreen) {
            $this->formPageVisible = true;
            $this->dispatch('mrcatz-form-page-opened');
        }
    }

    public function prepareAddData() {}

    public function selectChangeValue($value, $id) {}

    #[On(MrCatzEvent::PREPARE_EDIT)]
    public function listenEditData($data, $pageName = null): void
    {
        $this->currentCrudPageName = $pageName;
        $this->isEdit = true;
        $this->prepareEditData($data, $pageName);
        if ($this->modalFullScreen) {
            $this->formPageVisible = true;
            $this->dispatch('mrcatz-form-page-opened');
        }
    }

    public function prepareEditData($data) {}

    #[On(MrCatzEvent::PREPARE_DELETE)]
    public function listenDeleteData($data, $pageName = null): void
    {
        $this->currentCrudPageName = $pageName;
        $this->prepareDeleteData($data, $pageName);
    }

    public function prepareDeleteData($data) {}

    #[On(MrCatzEvent::BULK_DELETE)]
    public function listenBulkDeleteData($selectedRows, $pageName = null): void
    {
        $this->currentCrudPageName = $pageName;
        // Pass $pageName positionally — old `dropBulkData($selectedRows)`
        // overrides discard it, new overrides can widen to
        // `dropBulkData($selectedRows, $pageName = null)` to receive it.
        $this->dropBulkData($selectedRows, $pageName);
    }

    public function dropBulkData($selectedRows) {}

    /**
     * Override to define the form rendered inside a bulk action modal
     * for the given action id.
     *
     * Return MrCatzFormField[] to use the Form Builder (fields are
     * auto-bound to $bulkFormData and validated). Return a string to
     * use a blade @yield section you define in your page blade — you
     * are responsible for wire:model binding in that case (bind to
     * "bulkFormData.your_field_id" so processBulkActionData receives
     * the values).
     *
     * v1.29.22+: the active datatable's `setPageName()` is available via
     * `$this->currentCrudPageName` so overrides can branch per datatable on
     * multi-CRUD pages without changing their method signature.
     *
     * @return array|string
     */
    public function setBulkForm(string $id)
    {
        return [];
    }

    /**
     * Override to handle a custom bulk action submission.
     *
     * @param string $id            The MrCatzBulkAction id that fired.
     * @param array  $selectedRows  IDs of the currently selected rows.
     * @param array  $bulkFormData  Form values (only populated for mode=form).
     *
     * v1.29.22+: `$this->currentCrudPageName` holds the originating datatable's
     * setPageName() so multi-CRUD pages can branch on it.
     */
    public function processBulkActionData(string $id, array $selectedRows, array $bulkFormData): void {}

    #[On(MrCatzEvent::INLINE_UPDATE)]
    public function listenInlineUpdate($rowData, $columnKey, $newValue, $pageName = null): void
    {
        $this->currentCrudPageName = $pageName;
        // Pass $pageName positionally — old 3-arg overrides discard it,
        // new overrides can widen to accept it.
        $this->onInlineUpdate($rowData, $columnKey, $newValue, $pageName);
    }

    public function onInlineUpdate($rowData, $columnKey, $newValue) {}

    #[On(MrCatzEvent::ROW_CLICK)]
    public function listenRowClick($data, $pageName = null): void
    {
        $this->currentCrudPageName = $pageName;
        $this->onRowClick($data, $pageName);
    }

    public function onRowClick($data) {}

    public function dispatch_to_view(bool $condition, string $type): void
    {
        // Emit the currently-open CRUD's pageName so datatable-scripts can
        // close the correct namespaced modal on success (`modal-data-<name>`
        // / `modal-data-delete-<name>`). Falls back to null → '' suffix for
        // single-CRUD pages, byte-identical to pre-v1.29.22.
        $pageName = $this->currentCrudPageName ?? null;

        if (!$condition) {
            $this->dispatch(MrCatzEvent::REFRESH_DATA, [
                'status'   => false,
                'text'     => $this->title . ' ' . mrcatz_lang('failed'),
                'pageName' => $pageName,
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
            'status'   => true,
            'text'     => $this->title . ' ' . mrcatz_lang('success') . ' ' . $text,
            'pageName' => $pageName,
        ]);
    }

    public function show_notif(string $type, string $text): void
    {
        $this->dispatch(MrCatzEvent::SHOW_NOTIF, [
            'type'     => $type,
            'text'     => $text,
            'pageName' => $this->currentCrudPageName ?? null,
        ]);
    }

    public function notice(string $type, string $text): void
    {
        $this->dispatch(MrCatzEvent::NOTICE, type: $type, text: $text);
    }
}
