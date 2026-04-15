<?php

namespace MrCatz\DataTable\Concerns;

use MrCatz\DataTable\MrCatzBulkAction;
use MrCatz\DataTable\MrCatzEvent;

/**
 * Table-side trait: exposes setBulkAction() and the toolbar click
 * handler. The actual modal (form/confirmation) lives on the PAGE
 * component via HasCustomBulkActionModal, because the form fields and
 * the processBulkActionData() hook are defined on the page.
 *
 * Flow:
 *   1. User clicks a bulk button in the table toolbar.
 *   2. openBulkAction() on the table dispatches MrCatzEvent::BULK_ACTION_OPEN
 *      carrying { id, selectedRows } to the page component.
 *   3. The page shows the modal, renders setBulkForm($id), captures
 *      $bulkFormData, and on submit calls processBulkActionData().
 *   4. Page dispatches REFRESH_TABLE → table clears its selection.
 */
trait HasCustomBulkActions
{
    /** Toggle for the built-in bulk delete button. */
    public bool $showBulkDeleteAction = true;

    /**
     * Override to define custom bulk actions.
     * @return MrCatzBulkAction[]
     */
    public function setBulkAction(): array
    {
        return [];
    }

    /**
     * Normalized bulk actions for blade consumption.
     */
    public function getBulkActions(): array
    {
        $out = [];
        foreach ($this->setBulkAction() as $action) {
            if ($action instanceof MrCatzBulkAction) {
                $out[] = $action->toArray();
            }
        }
        return $out;
    }

    /**
     * Toolbar click handler. Dispatches to the page component, which
     * owns the modal + form state.
     *
     * Note: selection is NOT cleared here. The user may cancel the
     * modal, and losing their selection every time they peek inside
     * would be frustrating. Selection is cleared by the BULK_ACTION_DONE
     * listener below, only after a successful submit.
     */
    public function openBulkAction(string $id): void
    {
        if (empty($this->selectedRows)) return;

        // Find the action — validate it exists before dispatching.
        $found = null;
        foreach ($this->getBulkActions() as $a) {
            if ($a['id'] === $id) { $found = $a; break; }
        }
        if (!$found) return;

        $this->dispatch(
            MrCatzEvent::BULK_ACTION_OPEN,
            action: $found,
            selectedRows: $this->selectedRows,
        );
    }

    /**
     * Page component dispatches this once the bulk action has been
     * processed successfully. We clear the selection then so the
     * toolbar closes and checkboxes reset.
     */
    #[\Livewire\Attributes\On(MrCatzEvent::BULK_ACTION_DONE)]
    public function onBulkActionDone(): void
    {
        $this->clearSelection();
    }
}
