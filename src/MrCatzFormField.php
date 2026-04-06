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
    private mixed $min = null;
    private mixed $max = null;
    private int $span = 12;
    private ?string $hint = null;
    private ?string $hintColor = null; // success, error, warning, info
    private ?string $prefixText = null;
    private ?string $suffixText = null;
    private ?string $previewUrl = null;
    private ?string $confirmationLabel = null;

    // Spacing
    private ?string $margin = null;
    private ?string $padding = null;

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

    // Button
    private ?string $onClickMethod = null;
    private ?string $buttonStyle = null; // btn style for button type

    // Style & Size (DaisyUI)
    private ?string $style = null;  // primary, secondary, accent, info, success, warning, error, ghost, neutral
    private ?string $size = null;   // xs, sm, md, lg, xl

    // Additional field-specific
    private bool $loading = false; // for button: show loading spinner
    private ?string $target = null; // for button: wire:target

    // Image field
    private ?int $previewWidth = null;
    private ?int $previewHeight = null;
    private ?string $onUploadMethod = null;
    private ?string $onDeleteMethod = null;
    private ?string $fallbackText = null;
    private ?string $deleteConfirmText = null;
    private ?string $previewClass = null;

    // Grid row span & mobile order
    private ?int $rowSpan = null;
    private ?int $mobileOrder = null;

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
        mixed $min = null,
        mixed $max = null,
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

    /**
     * Image upload field with circular preview, fallback initial, upload/delete buttons.
     */
    /**
     * File upload with inline image preview — standard form field layout.
     * Looks consistent with other fields (text, select, etc.).
     * Use for product images, document uploads, etc.
     *
     * For avatar/profile style upload, use image() instead.
     */
    public static function fileupload(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $accept = null,
        mixed $disabled = false,
    ): static {
        $field = new static('fileupload', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->accept = $accept;
        $field->disabled = $disabled;
        return $field;
    }

    public static function image(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $accept = 'image/jpg,image/jpeg,image/png,image/webp',
        mixed $disabled = false,
    ): static {
        $field = new static('image', $id, $label);
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

    public static function checkbox(
        string $id,
        string $label,
        mixed $disabled = false,
    ): static {
        $field = new static('checkbox', $id, $label);
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

    public static function date(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $icon = null,
        mixed $disabled = false,
    ): static {
        $field = new static('date', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->icon = $icon;
        $field->disabled = $disabled;
        return $field;
    }

    public static function time(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $icon = null,
        mixed $disabled = false,
    ): static {
        $field = new static('time', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->icon = $icon;
        $field->disabled = $disabled;
        return $field;
    }

    public static function datetime(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $icon = null,
        mixed $disabled = false,
    ): static {
        $field = new static('datetime-local', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->icon = $icon;
        $field->disabled = $disabled;
        return $field;
    }

    public static function color(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        mixed $disabled = false,
    ): static {
        $field = new static('color', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->disabled = $disabled;
        return $field;
    }

    public static function range(
        string $id,
        string $label,
        mixed $min = 0,
        mixed $max = 100,
        ?int $step = null,
        ?string $rules = null,
        ?array $messages = null,
        mixed $disabled = false,
    ): static {
        $field = new static('range', $id, $label);
        $field->min = $min;
        $field->max = $max;
        $field->step = $step;
        $field->rules = $rules;
        $field->messages = $messages;
        $field->disabled = $disabled;
        return $field;
    }

    public static function url(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $icon = null,
        ?string $placeholder = null,
        mixed $disabled = false,
    ): static {
        $field = new static('url', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->icon = $icon;
        $field->placeholder = $placeholder;
        $field->disabled = $disabled;
        return $field;
    }

    public static function tel(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $icon = null,
        ?string $placeholder = null,
        mixed $disabled = false,
    ): static {
        $field = new static('tel', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->icon = $icon;
        $field->placeholder = $placeholder;
        $field->disabled = $disabled;
        return $field;
    }

    public static function search(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $icon = null,
        ?string $placeholder = null,
        mixed $disabled = false,
    ): static {
        $field = new static('search', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->icon = $icon ?? 'search';
        $field->placeholder = $placeholder;
        $field->disabled = $disabled;
        return $field;
    }

    public static function rating(
        string $id,
        string $label,
        int $max = 5,
        mixed $disabled = false,
    ): static {
        $field = new static('rating', $id, $label);
        $field->max = $max;
        $field->disabled = $disabled;
        return $field;
    }

    // ─── Rich Text Editor ─────────────────────────────────────────

    /**
     * Create a rich text editor field using Quill.js.
     *
     * @param string $id         Livewire property name
     * @param string $label      Field label
     * @param string|null $rules      Validation rules
     * @param array|null $messages   Custom validation messages
     * @param string|null $placeholder  Editor placeholder
     * @param mixed $disabled    Disabled state
     */
    public static function editor(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $placeholder = null,
        mixed $disabled = false,
    ): static {
        $field = new static('editor', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->placeholder = $placeholder;
        $field->disabled = $disabled;
        return $field;
    }

    // ─── Tag Input ────────────────────────────────────────────────

    /**
     * Create a tag input field where users can type and add tags as an array of strings.
     *
     * The bound Livewire property should be an array. Tags are added by pressing Enter or comma.
     *
     * @param string $id         Livewire property name (must be array)
     * @param string $label      Field label
     * @param string|null $rules      Validation rules
     * @param array|null $messages   Custom validation messages
     * @param string|null $placeholder  Input placeholder
     * @param mixed $disabled    Disabled state
     */
    public static function taginput(
        string $id,
        string $label,
        ?string $rules = null,
        ?array $messages = null,
        ?string $placeholder = 'Ketik lalu tekan Enter',
        mixed $disabled = false,
    ): static {
        $field = new static('taginput', $id, $label);
        $field->rules = $rules;
        $field->messages = $messages;
        $field->placeholder = $placeholder;
        $field->disabled = $disabled;
        return $field;
    }

    // ─── Button ───────────────────────────────────────────────────

    /**
     * Create a button element inside the form.
     *
     * @param string $label  Button text
     * @param string $onClick  Livewire method to call on click
     * @param string|null $icon  Optional icon
     * @param string $style  DaisyUI button style: primary, secondary, accent, info, success, warning, error, ghost, neutral, outline
     */
    public static function button(
        string $label,
        string $onClick,
        ?string $icon = null,
        string $style = 'primary',
    ): static {
        $field = new static('button', 'btn_' . $onClick, $label);
        $field->onClickMethod = $onClick;
        $field->icon = $icon;
        $field->buttonStyle = $style;
        return $field;
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

    public static function divider(?string $text = null): static
    {
        $field = new static('divider');
        $field->content = $text;
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

    /**
     * Set margin using Tailwind values.
     * Examples: 'mt-4', 'mb-2', 'mx-auto', 'ml-4 mr-2', 'my-6', 'm-4'
     */
    public function margin(string $class): static
    {
        $this->margin = $class;
        return $this;
    }

    /**
     * Set padding using Tailwind values.
     * Examples: 'pt-4', 'pb-2', 'px-6', 'pl-4 pr-2', 'py-3', 'p-4'
     */
    public function padding(string $class): static
    {
        $this->padding = $class;
        return $this;
    }

    /**
     * Span multiple grid rows. Use with span() for 2-column layouts.
     * E.g. ->span(4)->rowSpan(10) pins field to the side while others fill beside it.
     */
    public function rowSpan(int $rows): static
    {
        $this->rowSpan = $rows;
        return $this;
    }

    /**
     * Set visual order on mobile (< 640px). Lower numbers appear first.
     * Use negative values (e.g. -1) to move a field to the top on mobile.
     * Does not affect desktop layout.
     */
    public function mobileOrder(int $order): static
    {
        $this->mobileOrder = $order;
        return $this;
    }

    public function hint(?string $text, ?string $color = null): static
    {
        $this->hint = $text;
        if ($color) $this->hintColor = $color;
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

    public function preview(?string $url, ?int $width = null, ?int $height = null): static
    {
        $this->previewUrl = $url;
        if ($width) $this->previewWidth = $width;
        if ($height) $this->previewHeight = $height;
        return $this;
    }

    public function onUpload(string $method): static
    {
        $this->onUploadMethod = $method;
        return $this;
    }

    public function onDelete(string $method, ?string $confirmText = null): static
    {
        $this->onDeleteMethod = $method;
        $this->deleteConfirmText = $confirmText;
        return $this;
    }

    public function fallback(?string $text): static
    {
        $this->fallbackText = $text;
        return $this;
    }

    /**
     * Set Tailwind/DaisyUI classes for image preview shape & decoration.
     * Size is controlled by preview(url, width, height) via inline style.
     *
     * Examples:
     *   ->previewClass('rounded-full ring ring-primary ring-offset-2')
     *   ->previewClass('rounded-lg border-2 border-primary shadow-xl')
     *   ->previewClass('rounded-lg shadow-md')
     *   ->previewClass('mask mask-squircle')
     *   ->previewClass('rounded-none border border-base-300')
     *
     * If not set, defaults to: rounded-full ring ring-primary ring-offset-base-100 ring-offset-2
     */
    public function previewClass(string $class): static
    {
        $this->previewClass = $class;
        return $this;
    }

    public function withConfirmation(?string $label = null): static
    {
        $this->confirmationLabel = $label ?? mrcatz_lang('confirm_password', []) ?: 'Konfirmasi Password';
        return $this;
    }

    /**
     * Set DaisyUI style variant for the field.
     * Values: primary, secondary, accent, info, success, warning, error, ghost, neutral
     */
    public function style(string $style): static
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Set DaisyUI size for the field.
     * Values: xs, sm, md, lg, xl
     */
    public function size(string $size): static
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Show loading spinner on button while Livewire method runs.
     * Optionally specify wire:target for targeted loading.
     */
    public function withLoading(?string $target = null): static
    {
        $this->loading = true;
        $this->target = $target;
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
            'margin' => $this->margin,
            'padding' => $this->padding,
            'rowSpan' => $this->rowSpan,
            'mobileOrder' => $this->mobileOrder,
            'hint' => $this->hint,
            'hintColor' => $this->hintColor,
            'prefix' => $this->prefixText,
            'suffix' => $this->suffixText,
            'preview' => $this->previewUrl,
            'previewWidth' => $this->previewWidth,
            'previewHeight' => $this->previewHeight,
            'onUpload' => $this->onUploadMethod,
            'onDelete' => $this->onDeleteMethod,
            'fallback' => $this->fallbackText,
            'deleteConfirm' => $this->deleteConfirmText,
            'previewClass' => $this->previewClass,
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
            'onClick' => $this->onClickMethod,
            'buttonStyle' => $this->buttonStyle,
            'style' => $this->style,
            'size' => $this->size,
            'loading' => $this->loading,
            'target' => $this->target,
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
