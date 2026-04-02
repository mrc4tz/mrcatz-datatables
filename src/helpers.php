<?php

if (!function_exists('mrcatz_lang')) {
    /**
     * Get a MrCatz DataTable translated string.
     *
     * Uses Laravel lang files (lang/vendor/mrcatz/) with locale from
     * config('mrcatz.locale').
     *
     * Replacement keys accept both ':key' and 'key' formats.
     *
     * @param string $key
     * @param array  $replace  e.g. [':query' => 'ryan'] or ['query' => 'ryan']
     * @return string
     */
    function mrcatz_lang(string $key, array $replace = []): string
    {
        // Normalize replacement keys: strip leading colon for trans() compatibility
        $normalized = [];
        foreach ($replace as $k => $v) {
            $normalized[ltrim($k, ':')] = $v;
        }

        try {
            $locale = config('mrcatz.locale', 'en');
            $langKey = "mrcatz::mrcatz.{$key}";
            $translated = trans($langKey, $normalized, $locale);

            if ($translated !== $langKey) {
                return $translated;
            }
        } catch (\Throwable $e) {
            // translator not available (e.g. unit tests without full app)
        }

        return $key;
    }
}

if (!function_exists('mrcatz_icon')) {
    /**
     * Render an icon for MrCatz DataTable.
     *
     * Supports 'material' (default), 'heroicons', and 'custom' icon sets
     * via config('mrcatz.icon_set').
     *
     * @param string $name   Icon name (e.g. 'add', 'edit', 'close')
     * @param string $class  Additional CSS classes
     * @return string        Raw HTML
     */
    function mrcatz_icon(string $name, string $class = ''): string
    {
        static $heroMap = null;
        static $iconSet = null;

        if ($iconSet === null) {
            try {
                $iconSet = config('mrcatz.icon_set', 'material');
            } catch (\Throwable $e) {
                $iconSet = 'material';
            }
        }

        // Material Icons (default)
        if ($iconSet === 'material') {
            $tag = in_array($name, ['home', 'unfold_more']) ? 'material-symbols-outlined' : 'material-icons';
            return '<span class="' . $tag . ($class ? ' ' . $class : '') . '">' . $name . '</span>';
        }

        // Heroicons (Blade SVG)
        if ($iconSet === 'heroicons') {
            if ($heroMap === null) {
                $heroMap = [
                    'add' => 'plus',
                    'edit' => 'pencil-square',
                    'edit_note' => 'pencil-square',
                    'delete' => 'trash',
                    'delete_forever' => 'trash',
                    'delete_sweep' => 'trash',
                    'close' => 'x-mark',
                    'cancel' => 'x-circle',
                    'check_circle' => 'check-circle',
                    'check_box' => 'check',
                    'check_box_outline_blank' => 'stop',
                    'search' => 'magnifying-glass',
                    'search_off' => 'magnifying-glass',
                    'save' => 'arrow-down-tray',
                    'download' => 'arrow-down-tray',
                    'tune' => 'adjustments-horizontal',
                    'filter_alt' => 'funnel',
                    'bookmarks' => 'bookmark',
                    'restart_alt' => 'arrow-path',
                    'info' => 'information-circle',
                    'warning' => 'exclamation-triangle',
                    'error' => 'exclamation-circle',
                    'inbox' => 'inbox',
                    'home' => 'home',
                    'chevron_left' => 'chevron-left',
                    'chevron_right' => 'chevron-right',
                    'keyboard_arrow_up' => 'chevron-up',
                    'keyboard_arrow_down' => 'chevron-down',
                    'unfold_more' => 'arrows-up-down',
                    'add_circle' => 'plus-circle',
                    'table_view' => 'table-cells',
                    'picture_as_pdf' => 'document',
                    'select_all' => 'squares-2x2',
                    'view_column' => 'view-columns',
                ];
            }
            $mapped = $heroMap[$name] ?? $name;
            $svgClass = 'inline-block w-5 h-5' . ($class ? ' ' . $class : '');
            return svg('heroicon-o-' . $mapped, $svgClass)->toHtml();
        }

        // Font Awesome
        if ($iconSet === 'fontawesome') {
            static $faMap = null;
            if ($faMap === null) {
                $faMap = [
                    'add' => 'fa-solid fa-plus',
                    'edit' => 'fa-solid fa-pen-to-square',
                    'edit_note' => 'fa-solid fa-pen-to-square',
                    'delete' => 'fa-solid fa-trash',
                    'delete_forever' => 'fa-solid fa-trash-can',
                    'delete_sweep' => 'fa-solid fa-trash-can',
                    'close' => 'fa-solid fa-xmark',
                    'cancel' => 'fa-solid fa-circle-xmark',
                    'check_circle' => 'fa-solid fa-circle-check',
                    'check_box' => 'fa-regular fa-square-check',
                    'check_box_outline_blank' => 'fa-regular fa-square',
                    'search' => 'fa-solid fa-magnifying-glass',
                    'search_off' => 'fa-solid fa-magnifying-glass',
                    'save' => 'fa-solid fa-floppy-disk',
                    'download' => 'fa-solid fa-download',
                    'tune' => 'fa-solid fa-sliders',
                    'filter_alt' => 'fa-solid fa-filter',
                    'bookmarks' => 'fa-solid fa-bookmark',
                    'restart_alt' => 'fa-solid fa-rotate',
                    'info' => 'fa-solid fa-circle-info',
                    'warning' => 'fa-solid fa-triangle-exclamation',
                    'error' => 'fa-solid fa-circle-exclamation',
                    'inbox' => 'fa-solid fa-inbox',
                    'home' => 'fa-solid fa-house',
                    'chevron_left' => 'fa-solid fa-chevron-left',
                    'chevron_right' => 'fa-solid fa-chevron-right',
                    'keyboard_arrow_up' => 'fa-solid fa-chevron-up',
                    'keyboard_arrow_down' => 'fa-solid fa-chevron-down',
                    'unfold_more' => 'fa-solid fa-arrows-up-down',
                    'add_circle' => 'fa-solid fa-circle-plus',
                    'table_view' => 'fa-solid fa-table',
                    'picture_as_pdf' => 'fa-solid fa-file-pdf',
                    'select_all' => 'fa-solid fa-border-all',
                    'view_column' => 'fa-solid fa-table-columns',
                ];
            }
            $mapped = $faMap[$name] ?? 'fa-solid fa-circle-question';
            return '<i class="' . $mapped . ($class ? ' ' . $class : '') . '"></i>';
        }

        // Custom icons
        if ($iconSet === 'custom') {
            try {
                $custom = config('mrcatz.custom_icons', []);
                if (isset($custom[$name])) {
                    return $custom[$name];
                }
            } catch (\Throwable $e) {}
            // Fallback to material if custom not defined
            $tag = in_array($name, ['home', 'unfold_more']) ? 'material-symbols-outlined' : 'material-icons';
            return '<span class="' . $tag . ($class ? ' ' . $class : '') . '">' . $name . '</span>';
        }

        return $name;
    }
}
