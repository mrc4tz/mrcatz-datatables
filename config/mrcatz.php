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
    | Supported: 'material' (default), 'heroicons', 'fontawesome', 'custom'
    |
    | For 'material': requires Google Material Icons font in your layout
    |   <link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Symbols+Outlined" rel="stylesheet">
    |
    | For 'heroicons': requires Blade Heroicons (composer require blade-ui-kit/blade-heroicons)
    |   No external font needed — uses inline SVG.
    |
    | For 'fontawesome': requires Font Awesome CSS in your layout
    |   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    |
    | For 'custom': define your own icon map in 'custom_icons' below.
    |   Each key maps to raw HTML (SVG, icon font class, etc).
    |
    */
    'icon_set' => 'material',

    /*
    |--------------------------------------------------------------------------
    | Custom Icons
    |--------------------------------------------------------------------------
    |
    | Only used when 'icon_set' is 'custom'.
    | Map each icon name to raw HTML. You can use any icon library.
    |
    | Example with Font Awesome:
    |   'add' => '<i class="fas fa-plus"></i>',
    |   'edit' => '<i class="fas fa-pen"></i>',
    |
    */
    'custom_icons' => [
        // 'add' => '<i class="fas fa-plus"></i>',
    ],

];
