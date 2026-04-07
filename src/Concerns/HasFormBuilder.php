<?php

namespace MrCatz\DataTable\Concerns;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Storage;

trait HasFormBuilder
{
    /**
     * Gap between form field rows. CSS value: '1rem', '0.5rem', '1.5rem', etc.
     * Default: '1rem' (Tailwind gap-4).
     */
    public string $formGap = '1rem';

    /**
     * Gap between grid columns (e.g. between image and form fields side by side).
     * Default: '1.5rem' (Tailwind gap-6).
     */
    public string $formColumnGap = '1.5rem';

    /**
     * Override this method to define form fields using MrCatzFormField.
     * Return empty array to use traditional Blade @yield('forms') instead.
     */
    public function setForm(): array
    {
        return [];
    }

    /**
     * Check if form builder is active (setForm() returns non-empty).
     */
    public function hasFormBuilder(): bool
    {
        return !empty($this->setForm());
    }

    /**
     * Process setForm() fields: evaluate closures, resolve disabled/hidden states.
     * Returns array of field arrays ready for Blade rendering.
     */
    public function getFormFields(): array
    {
        $fields = [];

        foreach ($this->setForm() as $fieldObj) {
            $field = $fieldObj->toArray();

            // Resolve disabled Closure
            if ($field['disabled'] instanceof Closure) {
                $field['disabled'] = (bool) ($field['disabled'])();
            } else {
                $field['disabled'] = (bool) $field['disabled'];
            }

            // Resolve hidden Closure
            if ($field['hidden'] instanceof Closure) {
                $field['hidden'] = (bool) ($field['hidden'])();
            } else {
                $field['hidden'] = (bool) $field['hidden'];
            }

            // Build wire:model directive string
            $field['wireDirective'] = $this->buildWireDirective($field);

            $fields[] = $field;

            // Auto-generate confirmation field for password with withConfirmation
            if ($field['type'] === 'password' && $field['confirmation']) {
                $fields[] = [
                    'type' => 'password',
                    'id' => $field['id'] . '_confirmation',
                    'label' => $field['confirmation'],
                    'rules' => null,
                    'messages' => null,
                    'icon' => $field['icon'],
                    'placeholder' => null,
                    'disabled' => $field['disabled'],
                    'hidden' => $field['hidden'],
                    'data' => null,
                    'valueKey' => null,
                    'optionKey' => null,
                    'options' => null,
                    'accept' => null,
                    'step' => null,
                    'min' => null,
                    'max' => null,
                    'span' => $field['span'],
                    'hint' => null,
                    'prefix' => null,
                    'suffix' => null,
                    'preview' => null,
                    'confirmation' => null,
                    'visibleWhenField' => $field['visibleWhenField'],
                    'visibleWhenValue' => $field['visibleWhenValue'],
                    'visibleWhenAll' => $field['visibleWhenAll'],
                    'onChange' => null,
                    'dependsOn' => null,
                    'wireMode' => $field['wireMode'],
                    'debounceMs' => $field['debounceMs'],
                    'wireDirective' => str_replace($field['id'], $field['id'] . '_confirmation', $field['wireDirective']),
                    'content' => null,
                    'alertType' => null,
                ];
            }
        }

        return $fields;
    }

    /**
     * Extract validation rules from all form fields.
     * Returns ['field_id' => 'rules'] for $this->validate().
     */
    public function getFormValidationRules(): array
    {
        $rules = [];
        foreach ($this->setForm() as $fieldObj) {
            $fieldRules = $fieldObj->getRules();
            $fieldId = $fieldObj->getId();
            if ($fieldRules && $fieldId) {
                $rules[$fieldId] = $fieldRules;

                // Add 'confirmed' rule if password has withConfirmation
                $arr = $fieldObj->toArray();
                if ($arr['type'] === 'password' && $arr['confirmation']) {
                    if (!str_contains($fieldRules, 'confirmed')) {
                        $rules[$fieldId] .= '|confirmed';
                    }
                }
            }
        }
        return $rules;
    }

    /**
     * Extract custom validation messages from all form fields.
     * Returns ['field_id.rule' => 'message'] for $this->validate().
     */
    public function getFormValidationMessages(): array
    {
        $messages = [];
        foreach ($this->setForm() as $fieldObj) {
            $fieldMessages = $fieldObj->getMessages();
            $fieldId = $fieldObj->getId();
            if ($fieldMessages && $fieldId) {
                foreach ($fieldMessages as $rule => $message) {
                    $messages["{$fieldId}.{$rule}"] = $message;
                }
            }
        }
        return $messages;
    }

    /**
     * Check if a form field should be visible based on visibleWhen/visibleWhenAll conditions.
     */
    public function shouldShowField(array $field): bool
    {
        // Check hidden condition
        if ($field['hidden']) {
            return false;
        }

        // Check visibleWhen (single condition)
        if ($field['visibleWhenField'] !== null) {
            $currentValue = $this->{$field['visibleWhenField']} ?? null;
            $expected = $field['visibleWhenValue'];

            if (is_array($expected)) {
                if (!in_array($currentValue, $expected)) {
                    return false;
                }
            } else {
                if ($currentValue != $expected) {
                    return false;
                }
            }
        }

        // Check visibleWhenAll (multiple conditions, all must match)
        if ($field['visibleWhenAll'] !== null) {
            foreach ($field['visibleWhenAll'] as $prop => $expected) {
                $currentValue = $this->{$prop} ?? null;
                if ($currentValue != $expected) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Handle field change events from form builder.
     * Calls the user-defined onChange method if it exists.
     */
    public function formFieldChanged(string $fieldId, mixed $value): void
    {
        foreach ($this->setForm() as $fieldObj) {
            $field = $fieldObj->toArray();
            if ($field['id'] === $fieldId && $field['onChange']) {
                $method = $field['onChange'];
                if (method_exists($this, $method)) {
                    $this->{$method}($value);
                }
                break;
            }
        }
    }

    /**
     * Process editor HTML content: move uploaded images from tmp/ to permanent path.
     * Call this in your save method before persisting the HTML to database.
     *
     * Returns the updated HTML with permanent image URLs.
     *
     * Usage:
     *   $this->content = $this->processEditorImages($this->content);
     *   $model->update(['content' => $this->content]);
     *
     * Optionally pass old HTML to auto-delete removed images:
     *   $this->content = $this->processEditorImages($this->content, $model->content);
     *
     * Custom path (must match ->uploadPath() used on the field):
     *   $this->content = $this->processEditorImages($this->content, path: 'posts/images');
     */
    public function processEditorImages(string $html, ?string $oldHtml = null, ?string $path = null): string
    {
        $config = config('mrcatz.editor_image', []);

        if (($config['mode'] ?? 'base64') !== 'upload') {
            return $html;
        }

        $disk = $config['disk'] ?? 'public';
        $path = $path ?? $config['path'] ?? 'editor-images';
        $storage = Storage::disk($disk);
        $tmpPath = $path . '/tmp';

        // Move tmp images to permanent path
        preg_match_all('/src=["\']([^"\']*\/tmp\/[^"\']+)["\']/', $html, $matches);

        foreach ($matches[1] as $tmpUrl) {
            $baseUrl = $storage->url('');
            $relativePath = str_replace($baseUrl, '', $tmpUrl);
            $relativePath = ltrim($relativePath, '/');

            if (!$storage->exists($relativePath)) {
                continue;
            }

            $filename = basename($relativePath);
            $permanentPath = $path . '/' . $filename;

            $storage->move($relativePath, $permanentPath);

            $permanentUrl = $storage->url($permanentPath);
            $html = str_replace($tmpUrl, $permanentUrl, $html);
        }

        // Delete removed images (compare old vs new HTML)
        if ($oldHtml) {
            preg_match_all('/src=["\']([^"\']+)["\']/', $oldHtml, $oldMatches);
            $oldImages = $oldMatches[1] ?? [];

            foreach ($oldImages as $oldUrl) {
                if (str_contains($html, $oldUrl)) {
                    continue;
                }

                $baseUrl = $storage->url('');
                $relativePath = str_replace($baseUrl, '', $oldUrl);
                $relativePath = ltrim($relativePath, '/');

                // Only delete images within our editor path
                if (str_starts_with($relativePath, $path . '/') && $storage->exists($relativePath)) {
                    $storage->delete($relativePath);
                }
            }
        }

        // Auto-cleanup expired temp files
        $this->cleanupExpiredEditorImages($storage, $tmpPath, $config['tmp_lifetime'] ?? 24);

        return $html;
    }

    /**
     * Delete expired temporary editor images.
     */
    private function cleanupExpiredEditorImages($storage, string $tmpPath, int $lifetimeHours): void
    {
        if (!$storage->exists($tmpPath)) {
            return;
        }

        $cutoff = Carbon::now()->subHours($lifetimeHours);

        foreach ($storage->files($tmpPath) as $file) {
            $lastModified = Carbon::createFromTimestamp($storage->lastModified($file));

            if ($lastModified->lt($cutoff)) {
                $storage->delete($file);
            }
        }
    }

    /**
     * Build the wire:model directive string based on field wire mode.
     */
    private function buildWireDirective(array $field): string
    {
        $id = $field['id'];
        if (!$id) return '';

        return match ($field['wireMode']) {
            'live' => "wire:model.live=\"{$id}\"",
            'blur' => "wire:model.blur=\"{$id}\"",
            'debounce' => "wire:model.live.debounce.{$field['debounceMs']}ms=\"{$id}\"",
            default => "wire:model=\"{$id}\"",
        };
    }
}
