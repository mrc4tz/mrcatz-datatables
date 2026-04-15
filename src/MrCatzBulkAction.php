<?php

namespace MrCatz\DataTable;

/**
 * Defines a custom bulk action rendered alongside the built-in bulk delete.
 *
 * Two modes:
 *   - 'confirmation' → simple confirm dialog, then dispatches the action.
 *   - 'form'         → opens a modal with either a Form Builder form
 *                      (setBulkForm($id) returns MrCatzFormField[]) or a
 *                      blade @section escape hatch (returns string name).
 *
 * Users define actions by overriding setBulkAction() on the table
 * component, then handle the payload in processBulkActionData() on the
 * page component.
 */
class MrCatzBulkAction
{
    private string $id;
    private string $mode;
    private string $buttonText;
    private ?string $formTitle;
    private ?string $formSubtitle;
    private ?string $icon;
    private string $buttonColor;

    private const VALID_MODES = ['form', 'confirmation'];
    private const VALID_COLORS = ['primary', 'secondary', 'accent', 'neutral', 'info', 'success', 'warning', 'error', 'ghost'];

    private function __construct() {}

    /**
     * @param string      $id            Unique identifier, passed to processBulkActionData().
     * @param string      $mode          'form' | 'confirmation'
     * @param string      $buttonText    Label on the toolbar button + modal title.
     * @param string|null $formTitle     Optional modal headline (defaults to $buttonText).
     * @param string|null $formSubtitle  Optional subtitle below the title.
     * @param string|null $icon          Icon name; resolved via mrcatz_icon().
     *                                   Defaults to 'edit' for both modes.
     * @param string      $buttonColor   DaisyUI theme color used on the toolbar
     *                                   button and the modal submit button.
     *                                   One of: primary, secondary, accent,
     *                                   neutral, info, success, warning, error,
     *                                   ghost. Defaults to 'primary'.
     */
    public static function create(
        string $id,
        string $mode,
        string $buttonText,
        ?string $formTitle = null,
        ?string $formSubtitle = null,
        ?string $icon = null,
        string $buttonColor = 'primary',
    ): self {
        if (!in_array($mode, self::VALID_MODES, true)) {
            throw new \InvalidArgumentException(
                "MrCatzBulkAction mode must be one of: " . implode(', ', self::VALID_MODES) . ". Got '{$mode}'."
            );
        }
        if (!in_array($buttonColor, self::VALID_COLORS, true)) {
            throw new \InvalidArgumentException(
                "MrCatzBulkAction buttonColor must be one of: " . implode(', ', self::VALID_COLORS) . ". Got '{$buttonColor}'."
            );
        }

        $action = new self();
        $action->id = $id;
        $action->mode = $mode;
        $action->buttonText = $buttonText;
        $action->formTitle = $formTitle;
        $action->formSubtitle = $formSubtitle;
        $action->icon = $icon ?? 'edit';
        $action->buttonColor = $buttonColor;
        return $action;
    }

    public function getId(): string { return $this->id; }
    public function getMode(): string { return $this->mode; }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'mode'         => $this->mode,
            'buttonText'   => $this->buttonText,
            'formTitle'    => $this->formTitle ?? $this->buttonText,
            'formSubtitle' => $this->formSubtitle,
            'icon'         => $this->icon,
            'buttonColor'  => $this->buttonColor,
        ];
    }
}
