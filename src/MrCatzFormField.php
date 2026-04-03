<?php

namespace MrCatz\DataTable;

use Closure;

class MrCatzFormField
{
    private string $type;
    private ?string $id;
    private ?string $label;
    private ?string $rules = null;
    private ?array $messages = null;
    private ?string $icon = null;
    private ?string $placeholder = null;
    private mixed $disabled = false;
    private mixed $hiddenCondition = false;
    private ?array $data = null;
    private ?string $valueKey = null;
    private ?string $optionKey = null;
    private ?array $options = null; // for radio
    private ?string $accept = null; // for file
    private ?int $step = null;
    private ?int $min = null;
    private ?int $max = null;
    private int $span = 12;
    private ?string $hint = null;
    private ?string $prefixText = null;
    private ?string $suffixText = null;
    private ?string $previewUrl = null;
    private ?string $confirmationLabel = null;

    // Dynamic/dependency
    private ?string $visibleWhenField = null;
    private mixed $visibleWhenValue = null;
    private ?array $visibleWhenAllConditions = null;
    private ?string $onChangeMethod = null;
    private ?string $dependsOnField = null;

    // Wire model mode
    private string $wireMode = ''; // '', 'live', 'blur', 'debounce'
    private ?int $debounceMs = null;

    // Static content
    private ?string $content = null; // for section, note, alert, html
    private ?string $alertType = null; // for alert: info, warning, success, error

    private function __construct(string $type, ?string $id = null, ?string $label = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->label = $label;
    }

    // ─── Static Factory Methods ───────────────────────────────────

    public static function text(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $icon = null,
        ?string $placeholder = null,
        mixed $disabled = false,
    ): static {
        $field = new static('text', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->icon = $icon;
        $field->placeholder = $placeholder;
        $field->disabled = $disabled;
        return $field;
    }

    public static function email(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $icon = null,
        mixed $disabled = false,
    ): static {
        $field = new static('email', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->icon = $icon;
        $field->disabled = $disabled;
        return $field;
    }

    public static function password(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $icon = null,
        mixed $disabled = false,
    ): static {
        $field = new static('password', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->icon = $icon;
        $field->disabled = $disabled;
        return $field;
    }

    public static function number(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?int $step = null,
        ?int $min = null,
        ?int $max = null,
        ?string $icon = null,
        mixed $disabled = false,
    ): static {
        $field = new static('number', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->step = $step;
        $field->min = $min;
        $field->max = $max;
        $field->icon = $icon;
        $field->disabled = $disabled;
        return $field;
    }

    public static function select(
        string $id,
        string $label,
        array $data,
        string $value,
        string $option,
        ?string $rules = null,
        ?array $messages = null,
        mixed $disabled = false,
    ): static {
        $field = new static('select', $id, $label);
        $field->data = $data;
        $field->valueKey = $value;
        $field->optionKey = $option;
        $field->rules = $rules;
        $field->messages = $messages;
        $field->disabled = $disabled;
        return $field;
    }

    public static function textarea(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $placeholder = null,
        mixed $disabled = false,
    ): static {
        $field = new static('textarea', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->placeholder = $placeholder;
        $field->disabled = $disabled;
        return $field;
    }

    public static function file(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $accept = null,
        mixed $disabled = false,
    ): static {
        $field = new static('file', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->accept = $accept;
        $field->disabled = $disabled;
        return $field;
    }

    public static function toggle(
        string $id,
        string $label,
        mixed $disabled = false,
    ): static {
        $field = new static('toggle', $id, $label);
        $field->disabled = $disabled;
        return $field;
    }

    public static function chooser(
        string $id,
        string $label,
        array $data,
        string $value,
        string $option,
        ?string $rules = null,
        ?array $messages = null,
        mixed $disabled = false,
    ): static {
        $field = new static('chooser', $id, $label);
        $field->data = $data;
        $field->valueKey = $value;
        $field->optionKey = $option;
        $field->rules = $rules;
        $field->messages = $messages;
        $field->disabled = $disabled;
        return $field;
    }

    public static function radio(
        string $id,
        string $label,
        array $options,
        mixed $disabled = false,
    ): static {
        $field = new static('radio', $id, $label);
        $field->options = $options;
        $field->disabled = $disabled;
        return $field;
    }

    public static function hidden(string $id): static
    {
        return new static('hidden', $id);
    }

    // ─── Static Content Elements ──────────────────────────────────

    public static function section(string $title): static
    {
        $field = new static('section');
        $field->content = $title;
        return $field;
    }

    public static function note(string $text): static
    {
        $field = new static('note');
        $field->content = $text;
        return $field;
    }

    public static function alert(string $text, string $type = 'info'): static
    {
        $field = new static('alert');
        $field->content = $text;
        $field->alertType = $type;
        return $field;
    }

    public static function html(string $content): static
    {
        $field = new static('html');
        $field->content = $content;
        return $field;
    }

    // ─── Chainable Modifiers ──────────────────────────────────────

    public function visibleWhen(string $field, mixed $value): static
    {
        $this->visibleWhenField = $field;
        $this->visibleWhenValue = $value;
        return $this;
    }

    public function visibleWhenAll(array $conditions): static
    {
        $this->visibleWhenAllConditions = $conditions;
        return $this;
    }

    public function onChange(string $method): static
    {
        $this->onChangeMethod = $method;
        return $this;
    }

    public function dependsOn(string $field): static
    {
        $this->dependsOnField = $field;
        return $this;
    }

    public function disabled(mixed $condition = true): static
    {
        $this->disabled = $condition;
        return $this;
    }

    public function hideWhen(mixed $condition = true): static
    {
        $this->hiddenCondition = $condition;
        return $this;
    }

    public function span(int $cols): static
    {
        $this->span = max(1, min(12, $cols));
        return $this;
    }

    public function hint(string $text): static
    {
        $this->hint = $text;
        return $this;
    }

    public function live(): static
    {
        $this->wireMode = 'live';
        return $this;
    }

    public function lazy(): static
    {
        $this->wireMode = 'blur';
        return $this;
    }

    public function debounce(int $ms): static
    {
        $this->wireMode = 'debounce';
        $this->debounceMs = $ms;
        return $this;
    }

    public function prefix(string $text): static
    {
        $this->prefixText = $text;
        return $this;
    }

    public function suffix(string $text): static
    {
        $this->suffixText = $text;
        return $this;
    }

    public function preview(?string $url): static
    {
        $this->previewUrl = $url;
        return $this;
    }

    public function withConfirmation(?string $label = null): static
    {
        $this->confirmationLabel = $label ?? mrcatz_lang('confirm_password', []) ?: 'Konfirmasi Password';
        return $this;
    }

    // ─── Output ───────────────────────────────────────────────────

    /**
     * Convert field definition to array for Blade rendering.
     * Closures are NOT evaluated here — use HasFormBuilder::getFormFields() to resolve them.
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'label' => $this->label,
            'rules' => $this->rules,
            'messages' => $this->messages,
            'icon' => $this->icon,
            'placeholder' => $this->placeholder,
            'disabled' => $this->disabled,
            'hidden' => $this->hiddenCondition,
            'data' => $this->data,
            'valueKey' => $this->valueKey,
            'optionKey' => $this->optionKey,
            'options' => $this->options,
            'accept' => $this->accept,
            'step' => $this->step,
            'min' => $this->min,
            'max' => $this->max,
            'span' => $this->span,
            'hint' => $this->hint,
            'prefix' => $this->prefixText,
            'suffix' => $this->suffixText,
            'preview' => $this->previewUrl,
            'confirmation' => $this->confirmationLabel,
            'visibleWhenField' => $this->visibleWhenField,
            'visibleWhenValue' => $this->visibleWhenValue,
            'visibleWhenAll' => $this->visibleWhenAllConditions,
            'onChange' => $this->onChangeMethod,
            'dependsOn' => $this->dependsOnField,
            'wireMode' => $this->wireMode,
            'debounceMs' => $this->debounceMs,
            'content' => $this->content,
            'alertType' => $this->alertType,
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getRules(): ?string
    {
        return $this->rules;
    }

    public function getMessages(): ?array
    {
        return $this->messages;
    }
}
