<?php

namespace MrCatz\DataTable\Concerns;

use Livewire\Attributes\On;
use MrCatz\DataTable\MrCatzEvent;
use MrCatz\DataTable\MrCatzFormField;

/**
 * Page-side trait: renders the bulk action modal, holds form state,
 * runs validation, and invokes the user's processBulkActionData()
 * hook. See HasCustomBulkActions on the table for the button half.
 */
trait HasCustomBulkActionModal
{
    /** Currently active bulk action id. Null = no modal. */
    public ?string $activeBulkActionId = null;

    /** Snapshot of the action metadata (mode, title, etc.) for rendering. */
    public array $activeBulkAction = [];

    /** Row ids captured at the moment the modal was opened. */
    public array $bulkSelectedRows = [];

    #[On(MrCatzEvent::BULK_ACTION_OPEN)]
    public function onBulkActionOpen($action, $selectedRows): void
    {
        $action = (array) $action;
        $this->activeBulkActionId = $action['id'] ?? null;
        $this->activeBulkAction = $action;
        $this->bulkSelectedRows = array_values((array) $selectedRows);

        if (property_exists($this, 'bulkFormData')) {
            $this->bulkFormData = [];
        }

        $this->resetErrorBag();
        $this->resetValidation();
    }

    /**
     * Submit handler for both confirmation and form modes.
     */
    public function processBulkAction(): void
    {
        if (!$this->activeBulkActionId) return;
        if (empty($this->bulkSelectedRows)) { $this->cancelBulkAction(); return; }

        $id = $this->activeBulkActionId;
        $mode = $this->activeBulkAction['mode'] ?? 'confirmation';
        $formData = [];

        if ($mode === 'form') {
            $definition = $this->setBulkForm($id);

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

        $rows = $this->bulkSelectedRows;

        // Hand off to the user. Reset modal state first so any refresh
        // inside the hook sees a clean slate.
        $this->resetBulkModalState();
        $this->processBulkActionData($id, $rows, $formData);

        // Tell the table component to clear its selection now that
        // the action has been applied. Safe to fire even if the user
        // hook short-circuited — the table just resets $selectedRows.
        $this->dispatch(MrCatzEvent::BULK_ACTION_DONE);
    }

    public function cancelBulkAction(): void
    {
        $this->resetBulkModalState();
    }

    /**
     * Resolved form fields for the active bulk action, wired to bulkFormData.*.
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
            // Validation errors live under the namespaced key, so
            // partials can `@error($errorKey)` regardless of mode.
            $field['errorKey'] = $field['id'] ? "bulkFormData.{$field['id']}" : null;
            $fields[] = $field;
        }
        return $fields;
    }

    /**
     * Blade @yield section name when setBulkForm() returns a string.
     */
    public function getBulkFormSection(): ?string
    {
        $id = $this->activeBulkActionId;
        if (!$id) return null;

        $definition = $this->setBulkForm($id);
        return is_string($definition) && $definition !== '' ? $definition : null;
    }

    private function resetBulkModalState(): void
    {
        $this->activeBulkActionId = null;
        $this->activeBulkAction = [];
        $this->bulkSelectedRows = [];
        if (property_exists($this, 'bulkFormData')) {
            $this->bulkFormData = [];
        }
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
