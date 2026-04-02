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
