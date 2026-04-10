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

if (!function_exists('mrcatz_icon_svg')) {
    /**
     * Get an inline SVG icon — zero dependencies fallback.
     * All SVGs are 24x24 viewBox, stroke-based, matching Heroicons outline style.
     */
    function mrcatz_icon_svg(string $name, string $class = ''): string
    {
        static $svgMap = null;
        if ($svgMap === null) {
            $svgMap = [
                'add'                     => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>',
                'add_circle'              => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
                'edit'                    => '<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>',
                'edit_note'               => '<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>',
                'delete'                  => '<path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>',
                'delete_forever'          => '<path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>',
                'delete_sweep'            => '<path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>',
                'close'                   => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>',
                'cancel'                  => '<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
                'check_circle'            => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
                'check_box'               => '<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>',
                'check_box_outline_blank'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 0 1 7.5 5.25h9a2.25 2.25 0 0 1 2.25 2.25v9a2.25 2.25 0 0 1-2.25 2.25h-9a2.25 2.25 0 0 1-2.25-2.25v-9Z"/>',
                'search'                  => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>',
                'search_off'              => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>',
                'save'                    => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>',
                'download'                => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>',
                'tune'                    => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/>',
                'filter_alt'              => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/>',
                'bookmarks'               => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z"/>',
                'restart_alt'             => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/>',
                'info'                    => '<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>',
                'warning'                 => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>',
                'error'                   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>',
                'inbox'                   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3"/>',
                'home'                    => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
                'chevron_left'            => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>',
                'chevron_right'           => '<path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>',
                'keyboard_arrow_up'       => '<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/>',
                'keyboard_arrow_down'     => '<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>',
                'unfold_more'             => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"/>',
                'table_view'              => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-12.75m0 0A1.125 1.125 0 0 1 3.375 4.5h17.25c.621 0 1.125.504 1.125 1.125m-20.625 0v12.75m20.625-12.75v12.75"/>',
                'picture_as_pdf'          => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>',
                'select_all'              => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>',
                'view_column'             => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z"/>',
                'visibility'              => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>',
                'visibility_off'          => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.244 7.244L19.5 19.5m-3.122-3.122-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>',
            ];
        }

        // Check form_icons config before fallback
        if (!isset($svgMap[$name])) {
            try {
                $formIcons = config('mrcatz.form_icons', []);
                if (isset($formIcons[$name])) {
                    return mrcatz_resolve_form_icon($formIcons[$name], $class);
                }
            } catch (\Throwable $e) {}
        }

        $path = $svgMap[$name] ?? '<circle cx="12" cy="12" r="1.5"/>';
        $cls = 'inline-block w-5 h-5' . ($class ? ' ' . $class : '');
        return '<svg class="' . $cls . '" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">' . $path . '</svg>';
    }
}

if (!function_exists('mrcatz_icon')) {
    /**
     * Render an icon for MrCatz DataTable.
     *
     * Supports 'default', 'heroicons', 'material', 'fontawesome', and 'custom'.
     * 'default' uses built-in inline SVG — zero dependencies, zero CDN.
     *
     * @param string $name   Icon name (e.g. 'add', 'edit', 'close')
     * @param string $class  Additional CSS classes
     * @return string        Raw HTML
     */
    function mrcatz_icon(string $name, string $class = ''): string
    {
        static $heroMap = null;
        static $iconSet = null;
        static $validSets = ['default', 'heroicons', 'material', 'fontawesome', 'custom'];

        if ($iconSet === null) {
            try {
                $iconSet = config('mrcatz.icon_set', 'default');
            } catch (\Throwable $e) {
                $iconSet = 'default';
            }
            // Invalid icon_set value — use default
            if (!in_array($iconSet, $validSets)) {
                $iconSet = 'default';
            }
        }

        // Default — built-in inline SVG, zero dependencies
        if ($iconSet === 'default') {
            return mrcatz_icon_svg($name, $class);
        }

        // Heroicons — inline SVG via blade-heroicons package
        if ($iconSet === 'heroicons') {
            if ($heroMap === null) {
                $heroMap = [
                    'add' => 'plus', 'edit' => 'pencil-square', 'edit_note' => 'pencil-square',
                    'delete' => 'trash', 'delete_forever' => 'trash', 'delete_sweep' => 'trash',
                    'close' => 'x-mark', 'cancel' => 'x-circle', 'check_circle' => 'check-circle',
                    'check_box' => 'check', 'check_box_outline_blank' => 'stop',
                    'search' => 'magnifying-glass', 'search_off' => 'magnifying-glass',
                    'save' => 'arrow-down-tray', 'download' => 'arrow-down-tray',
                    'tune' => 'adjustments-horizontal', 'filter_alt' => 'funnel',
                    'bookmarks' => 'bookmark', 'restart_alt' => 'arrow-path',
                    'info' => 'information-circle', 'warning' => 'exclamation-triangle',
                    'error' => 'exclamation-circle', 'inbox' => 'inbox', 'home' => 'home',
                    'chevron_left' => 'chevron-left', 'chevron_right' => 'chevron-right',
                    'keyboard_arrow_up' => 'chevron-up', 'keyboard_arrow_down' => 'chevron-down',
                    'unfold_more' => 'arrows-up-down', 'add_circle' => 'plus-circle',
                    'table_view' => 'table-cells', 'picture_as_pdf' => 'document',
                    'select_all' => 'squares-2x2', 'view_column' => 'view-columns',
                    'visibility' => 'eye', 'visibility_off' => 'eye-slash',
                ];
            }
            $mapped = $heroMap[$name] ?? $name;
            $svgClass = 'inline-block w-5 h-5' . ($class ? ' ' . $class : '');
            try {
                return svg('heroicon-o-' . $mapped, $svgClass)->toHtml();
            } catch (\Throwable $e) {
                // blade-heroicons not installed — fallback to inline SVG
                return mrcatz_icon_svg($name, $class);
            }
        }

        // Material Icons
        if ($iconSet === 'material') {
            $tag = in_array($name, ['home', 'unfold_more']) ? 'material-symbols-outlined' : 'material-icons';

            // Convert Tailwind w-*/h-* size classes to font-size for font-based icons
            $sizeStyle = '';
            $filteredClass = $class;
            if (preg_match('/\bw-(\d+)\b/', $class, $m)) {
                $sizeMap = ['3' => '12px', '4' => '16px', '5' => '20px', '6' => '24px', '7' => '28px', '8' => '32px'];
                $sizeStyle = 'font-size:' . ($sizeMap[$m[1]] ?? ($m[1] * 4) . 'px') . ';line-height:1;';
                $filteredClass = preg_replace('/\b[wh]-\d+\b/', '', $filteredClass);
                $filteredClass = trim(preg_replace('/\s+/', ' ', $filteredClass));
            }

            $styleAttr = $sizeStyle ? ' style="' . $sizeStyle . '"' : '';
            return '<span class="' . $tag . ($filteredClass ? ' ' . $filteredClass : '') . '"' . $styleAttr . '>' . $name . '</span>';
        }

        // Font Awesome 6
        if ($iconSet === 'fontawesome') {
            static $faMap = null;
            if ($faMap === null) {
                $faMap = [
                    'add' => 'fa-solid fa-plus', 'edit' => 'fa-solid fa-pen-to-square',
                    'edit_note' => 'fa-solid fa-pen-to-square', 'delete' => 'fa-solid fa-trash',
                    'delete_forever' => 'fa-solid fa-trash-can', 'delete_sweep' => 'fa-solid fa-trash-can',
                    'close' => 'fa-solid fa-xmark', 'cancel' => 'fa-solid fa-circle-xmark',
                    'check_circle' => 'fa-solid fa-circle-check', 'check_box' => 'fa-regular fa-square-check',
                    'check_box_outline_blank' => 'fa-regular fa-square',
                    'search' => 'fa-solid fa-magnifying-glass', 'search_off' => 'fa-solid fa-magnifying-glass',
                    'save' => 'fa-solid fa-floppy-disk', 'download' => 'fa-solid fa-download',
                    'tune' => 'fa-solid fa-sliders', 'filter_alt' => 'fa-solid fa-filter',
                    'bookmarks' => 'fa-solid fa-bookmark', 'restart_alt' => 'fa-solid fa-rotate',
                    'info' => 'fa-solid fa-circle-info', 'warning' => 'fa-solid fa-triangle-exclamation',
                    'error' => 'fa-solid fa-circle-exclamation', 'inbox' => 'fa-solid fa-inbox',
                    'home' => 'fa-solid fa-house', 'chevron_left' => 'fa-solid fa-chevron-left',
                    'chevron_right' => 'fa-solid fa-chevron-right', 'keyboard_arrow_up' => 'fa-solid fa-chevron-up',
                    'keyboard_arrow_down' => 'fa-solid fa-chevron-down', 'unfold_more' => 'fa-solid fa-arrows-up-down',
                    'add_circle' => 'fa-solid fa-circle-plus', 'table_view' => 'fa-solid fa-table',
                    'picture_as_pdf' => 'fa-solid fa-file-pdf', 'select_all' => 'fa-solid fa-border-all',
                    'view_column' => 'fa-solid fa-table-columns',
                    'visibility' => 'fa-solid fa-eye', 'visibility_off' => 'fa-solid fa-eye-slash',
                ];
            }
            $mapped = $faMap[$name] ?? 'fa-solid fa-' . str_replace('_', '-', $name);
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
            // Fallback to inline SVG
            return mrcatz_icon_svg($name, $class);
        }

        // Unknown icon set — fallback to inline SVG
        return mrcatz_icon_svg($name, $class);
    }
}

if (!function_exists('mrcatz_resolve_form_icon')) {
    /**
     * Resolve a form_icons config value to HTML.
     *
     * If the value starts with an SVG element tag (<path, <circle, <g, <rect, <line, <polygon, <polyline),
     * it's wrapped in an <svg> tag. Otherwise, rendered as raw HTML.
     */
    function mrcatz_resolve_form_icon(string $value, string $class = ''): string
    {
        $svgTags = ['<path', '<circle', '<g', '<rect', '<line', '<polygon', '<polyline'];
        $isSvgPath = false;
        foreach ($svgTags as $tag) {
            if (str_starts_with(trim($value), $tag)) {
                $isSvgPath = true;
                break;
            }
        }

        if ($isSvgPath) {
            $cls = 'inline-block w-5 h-5' . ($class ? ' ' . $class : '');
            return '<svg class="' . $cls . '" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">' . $value . '</svg>';
        }

        // Raw HTML — render as-is
        return $value;
    }
}

if (!function_exists('mrcatz_form_icon')) {
    /**
     * Resolve a form field icon to HTML.
     *
     * Supports 3 modes:
     * 1. Raw HTML (starts with '<') — rendered as-is
     * 2. Name found in config('mrcatz.form_icons') — resolved via mrcatz_resolve_form_icon()
     * 3. Name passed to mrcatz_icon() — uses the active icon_set
     */
    function mrcatz_form_icon(?string $icon, string $class = ''): string
    {
        if (!$icon) return '';

        // Mode 1: Raw HTML
        if (str_starts_with(trim($icon), '<')) {
            return $icon;
        }

        // Mode 2: Check config form_icons
        try {
            $formIcons = config('mrcatz.form_icons', []);
            if (isset($formIcons[$icon])) {
                return mrcatz_resolve_form_icon($formIcons[$icon], $class);
            }
        } catch (\Throwable $e) {}

        // Mode 3: Standard mrcatz_icon()
        return mrcatz_icon($icon, $class);
    }
}
