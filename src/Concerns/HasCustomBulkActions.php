<?php

namespace MrCatz\DataTable\Concerns;

use MrCatz\DataTable\MrCatzBulkAction;
use MrCatz\DataTable\MrCatzEvent;
use MrCatz\DataTable\MrCatzFormField;

/**
 * Adds custom bulk action support on top of HasBulkActions.
 *
 * Flow:
 *   1. Table component overrides setBulkAction() returning MrCatzBulkAction[].
 *   2. User clicks the toolbar button for action X → openBulkAction('X').
 *      - mode=confirmation: opens confirm dialog.
 *      - mode=form:         opens a modal rendering setBulkForm('X').
 *   3. Submit → processBulkAction() validates (form mode only) and
 *      dispatches MrCatzEvent::BULK_ACTION to the page component, which
 *      calls the user's processBulkActionData($id, $selectedRows, $bulkFormData).
 */
trait HasCustomBulkActions
{
    /** Toggle for the built-in bulk delete button. */
    public bool $showBulkDeleteAction = true;

    /** Tracks which bulk action modal is currently active. */
    public ?string $activeBulkActionId = null;

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
     * Resolve a single bulk action by id.
     */
    public function getBulkActionById(?string $id): ?array
    {
        if (!$id) return null;
        foreach ($this->getBulkActions() as $a) {
            if ($a['id'] === $id) return $a;
        }
        return null;
    }

    /**
     * Opens the modal for the given bulk action id.
     * Resets $bulkFormData before showing the form modal.
     */
    public function openBulkAction(string $id): void
    {
        $action = $this->getBulkActionById($id);
        if (!$action) return;
        if (empty($this->selectedRows)) return;

        $this->activeBulkActionId = $id;

        if ($action['mode'] === 'form' && property_exists($this, 'bulkFormData')) {
            $this->bulkFormData = [];
        }

        $this->dispatch('open-bulk-action-modal', id: $id, mode: $action['mode']);
    }

    /**
     * Submit handler for both confirmation and form modes.
     * Validates form fields when mode=form and setBulkForm() returns MrCatzFormField[].
     */
    public function processBulkAction(): void
    {
        $action = $this->getBulkActionById($this->activeBulkActionId);
        if (!$action) return;
        if (empty($this->selectedRows)) return;

        $id = $action['id'];
        $formData = [];

        if ($action['mode'] === 'form') {
            $definition = $this->setBulkForm($id);

            // Only auto-validate when the user returns a MrCatzFormField array.
            // Blade @section escape hatch is user's own responsibility.
            if (is_array($definition) && !empty($definition)) {
                $rules = [];
                $messages = [];
                foreach ($definition as $fieldObj) {
                    if (!$fieldObj instanceof MrCatzFormField) continue;
                    $fid = $fieldObj->getId();
                    $frules = $fieldObj->getRules();
                    if ($fid && $frules) {
                        $rules["bulkFormData.{$fid}"] = $frules;
                    }
                    $fmessages = $fieldObj->getMessages();
                    if ($fid && $fmessages) {
                        foreach ($fmessages as $rule => $msg) {
                            $messages["bulkFormData.{$fid}.{$rule}"] = $msg;
                        }
                    }
                }
                if (!empty($rules)) {
                    $this->validate($rules, $messages);
                }
            }

            $formData = property_exists($this, 'bulkFormData') ? (array) $this->bulkFormData : [];
        }

        $rows = $this->selectedRows;
        $activeId = $this->activeBulkActionId;

        // Reset local state before dispatching — page component may refresh the table.
        $this->clearSelection();
        $this->activeBulkActionId = null;

        $this->dispatch(
            MrCatzEvent::BULK_ACTION,
            id: $activeId,
            selectedRows: $rows,
            bulkFormData: $formData,
        );

        $this->dispatch('close-bulk-action-modal');
    }

    /**
     * Cancel / close the active bulk action modal without dispatching.
     */
    public function cancelBulkAction(): void
    {
        $this->activeBulkActionId = null;
        $this->dispatch('close-bulk-action-modal');
    }

    /**
     * Resolved form fields for the active bulk action, namespaced to bulkFormData.
     * Returns an empty array when setBulkForm() returns a string (blade mode).
     */
    public function getBulkFormFields(): array
    {
        $id = $this->activeBulkActionId;
        if (!$id) return [];

        $definition = $this->setBulkForm($id);
        if (!is_array($definition)) return [];

        $fields = [];
        foreach ($definition as $fieldObj) {
            if (!$fieldObj instanceof MrCatzFormField) continue;
            $field = $fieldObj->toArray();
            $field['disabled'] = $field['disabled'] instanceof \Closure ? (bool) ($field['disabled'])() : (bool) $field['disabled'];
            $field['hidden']   = $field['hidden']   instanceof \Closure ? (bool) ($field['hidden'])()   : (bool) $field['hidden'];
            $field['wireDirective'] = $this->buildBulkWireDirective($field);
            $fields[] = $field;
        }
        return $fields;
    }

    /**
     * Resolve the blade @yield section name when setBulkForm() returns a string.
     */
    public function getBulkFormSection(): ?string
    {
        $id = $this->activeBulkActionId;
        if (!$id) return null;

        $definition = $this->setBulkForm($id);
        return is_string($definition) && $definition !== '' ? $definition : null;
    }

    private function buildBulkWireDirective(array $field): string
    {
        $id = $field['id'] ?? null;
        if (!$id) return '';
        $target = "bulkFormData.{$id}";
        return match ($field['wireMode'] ?? 'defer') {
            'live'     => "wire:model.live=\"{$target}\"",
            'blur'     => "wire:model.blur=\"{$target}\"",
            'debounce' => "wire:model.live.debounce.{$field['debounceMs']}ms=\"{$target}\"",
            default    => "wire:model=\"{$target}\"",
        };
    }
}
