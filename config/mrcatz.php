<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    |
    | Set the language for all MrCatz DataTable UI strings.
    | Supported: 'en', 'id', or any locale you add to lang/vendor/mrcatz/
    |
    */
    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | URL Prefix
    |--------------------------------------------------------------------------
    |
    | Default URL prefix for image columns (withColumnImage) and expand view
    | image fields (getExpandableView). This value is used when urlPrefix
    | is not explicitly passed.
    |
    | Supported: 'storage', 'public', 'https://...', or '' (empty string)
    |
    | - 'storage': asset('storage/' . $value)
    | - 'public':  asset($value)
    | - 'https://cdn.example.com': prefix + '/' + $value
    | - '' (empty string): use DB value as-is (already full URL)
    |
    */
    'url_prefix' => 'storage',

    /*
    |--------------------------------------------------------------------------
    | Icon Set
    |--------------------------------------------------------------------------
    |
    | Choose the icon set for all MrCatz DataTable UI icons.
    | Supported: 'default', 'heroicons', 'material', 'fontawesome', 'custom'
    |
    | For 'default': built-in inline SVG icons — zero dependencies, zero CDN.
    |   Works out of the box, no setup needed.
    |
    | For 'heroicons': requires Blade Heroicons (composer require blade-ui-kit/blade-heroicons)
    |   Higher quality SVG, but requires package install.
    |
    | For 'material': requires Google Material Icons font in your layout
    |   <link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Symbols+Outlined" rel="stylesheet">
    |
    | For 'fontawesome': requires Font Awesome CSS in your layout
    |   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    |
    | For 'custom': define your own icon map in 'custom_icons' below.
    |   Each key maps to raw HTML (SVG, icon font class, etc).
    |
    */
    'icon_set' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Custom Icons
    |--------------------------------------------------------------------------
    |
    | Only used when 'icon_set' is 'custom'.
    | Map each icon name to raw HTML. You can use any icon library.
    | Uncomment and edit the values below to use your own icons.
    | Icons not defined here will fallback to Material Icons.
    |
    */
    /*
    |--------------------------------------------------------------------------
    | Form Icons
    |--------------------------------------------------------------------------
    |
    | Register additional icons for the Form Builder fields.
    | Used when you pass icon: 'name' in MrCatzFormField and the icon
    | is not in the built-in SVG map.
    |
    | Key = icon name used in MrCatzFormField (e.g. icon: 'person')
    |
    | Value supports 2 formats:
    | 1. SVG path — auto-wrapped in <svg> tag (24x24 viewBox, stroke-based)
    |    Detected when value starts with <path, <circle, <g, <rect, <line, <polygon, <polyline
    |    Example: 'person' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75..."/>',
    |
    | 2. Raw HTML — rendered as-is (any other value)
    |    Example: 'mail' => '<i class="bi bi-envelope"></i>',
    |    Example: 'star' => '<span class="material-symbols-outlined">star</span>',
    |
    */
    'form_icons' => [
        // 'person' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>',
        // 'mail'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>',
        // 'link'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>',
    ],

    'custom_icons' => [
        // -- Toolbar --
        // 'add'                      => '<i class="fas fa-plus"></i>',
        // 'search'                   => '<i class="fas fa-search"></i>',
        // 'tune'                     => '<i class="fas fa-sliders-h"></i>',
        // 'filter_alt'               => '<i class="fas fa-filter"></i>',
        // 'bookmarks'                => '<i class="fas fa-bookmark"></i>',
        // 'save'                     => '<i class="fas fa-save"></i>',
        // 'download'                 => '<i class="fas fa-download"></i>',
        // 'view_column'              => '<i class="fas fa-columns"></i>',
        // 'restart_alt'              => '<i class="fas fa-redo"></i>',

        // -- Bulk & Selection --
        // 'check_box'                => '<i class="far fa-check-square"></i>',
        // 'check_box_outline_blank'  => '<i class="far fa-square"></i>',
        // 'check_circle'             => '<i class="fas fa-check-circle"></i>',
        // 'delete'                   => '<i class="fas fa-trash"></i>',
        // 'delete_forever'           => '<i class="fas fa-trash-alt"></i>',
        // 'delete_sweep'             => '<i class="fas fa-trash-alt"></i>',
        // 'close'                    => '<i class="fas fa-times"></i>',
        // 'cancel'                   => '<i class="fas fa-times-circle"></i>',
        // 'select_all'               => '<i class="fas fa-border-all"></i>',

        // -- Sort & Navigation --
        // 'keyboard_arrow_up'        => '<i class="fas fa-chevron-up"></i>',
        // 'keyboard_arrow_down'      => '<i class="fas fa-chevron-down"></i>',
        // 'unfold_more'              => '<i class="fas fa-arrows-alt-v"></i>',
        // 'chevron_left'             => '<i class="fas fa-chevron-left"></i>',
        // 'chevron_right'            => '<i class="fas fa-chevron-right"></i>',

        // -- Form & CRUD --
        // 'edit'                     => '<i class="fas fa-pen"></i>',
        // 'edit_note'                => '<i class="fas fa-pen-square"></i>',
        // 'add_circle'               => '<i class="fas fa-plus-circle"></i>',
        // 'warning'                  => '<i class="fas fa-exclamation-triangle"></i>',

        // -- Export Modal --
        // 'table_view'               => '<i class="fas fa-table"></i>',
        // 'picture_as_pdf'           => '<i class="fas fa-file-pdf"></i>',
        // 'info'                     => '<i class="fas fa-info-circle"></i>',

        // -- Empty State --
        // 'inbox'                    => '<i class="fas fa-inbox"></i>',
        // 'search_off'               => '<i class="fas fa-search"></i>',

        // -- Notification --
        // 'error'                    => '<i class="fas fa-exclamation-circle"></i>',

        // -- Breadcrumb --
        // 'home'                     => '<i class="fas fa-home"></i>',
    ],

];
