<p align="center">
  <a href="https://packagist.org/packages/mrcatz/datatable"><img src="https://img.shields.io/packagist/v/mrcatz/datatable" alt="Packagist Version"></a>
  <a href="https://packagist.org/packages/mrcatz/datatable"><img src="https://img.shields.io/packagist/dt/mrcatz/datatable" alt="Packagist Downloads"></a>
  <a href="LICENSE"><img src="https://img.shields.io/packagist/l/mrcatz/datatable" alt="License"></a>
  <a href="https://datatable.catzoid.tech"><img src="https://img.shields.io/badge/docs-datatable.catzoid.tech-F57927" alt="Docs"></a>
  <img src="https://img.shields.io/badge/Laravel-11%2B-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Livewire-3%2B-FB70A9?style=flat-square&logo=livewire&logoColor=white" alt="Livewire">
  <img src="https://img.shields.io/badge/DaisyUI-5-5A0EF8?style=flat-square&logo=daisyui&logoColor=white" alt="DaisyUI">
</p>

# MrCatz DataTable

**DataTable + Form Builder** for **Laravel Livewire** — build complete admin pages in minutes.

📖 **[Full documentation →](https://datatable.catzoid.tech)**
🎮 **[Live demo →](https://datatable.catzoid.tech/demo)**

---

## What is this?

A complete, opinionated DataTable + CRUD framework for Laravel + Livewire applications. Bundles everything you typically rebuild from scratch on every admin page — pagination, sorting, filtering, search, inline editing, bulk actions, expandable rows, exports, and a programmatic Form Builder — into a single composable API.

Think of it as **"Filament's CRUD productivity, but you keep full control over your stack."** You choose your Tailwind version, your DaisyUI theme, your Livewire flavor — MrCatz slots into your existing Laravel app instead of replacing it.

## Highlights

- ⚡ **CRUD in minutes** — `php artisan mrcatz:make Product --path=Admin` scaffolds everything
- 🔍 **Smart search** — multi-keyword highlighting, per-column relevance scoring, typo tolerance, optional Meilisearch driver (beta)
- 🎯 **Powerful filters** — select, callback, dependent, and full date / date-range filters with operator support
- ✏️ **Inline editing** — click any cell to edit, with validation rules, keyboard navigation, per-row gating
- 📊 **PDF, CSV & Excel export** — built-in styling, fully customizable layouts
- 🧱 **Form Builder** — define add/edit forms in PHP with chainable modifiers, sections, conditional fields
- ☑️ **Bulk actions** & **expandable rows**
- 📱 **Fully responsive** — adapts cleanly from desktop tables to mobile card layouts out of the box
- 🎨 **Themed via DaisyUI** — works with any DaisyUI theme, full control over colors

## Quick install

```bash
composer require mrcatz/datatable
```

Wire your base layout. In your app's base layout (usually `layouts/app.blade.php`), add these three directives before `</body>`. All three are required — without them the datatable, form modal, and toast notifications silently fail.

```blade
{{-- resources/views/layouts/app.blade.php --}}
<body>
    {{-- ... your app content ... --}}

    @include('mrcatz::components.ui.notification')

    @livewireScripts
    @stack('scripts')
</body>
```

> Using Jetstream, Breeze, or Filament? Your layout likely already has `@livewireScripts` and `@stack('scripts')` — you only need to add the notification include.

Add the package's blade path to your Tailwind content scan:

```css
/* resources/css/app.css (Tailwind v4) */
@source '../../vendor/mrcatz/**/*.blade.php';
```

Then generate your first CRUD page:

```bash
php artisan mrcatz:make Product --path=Admin
```

Add a route and you're done:

```php
Route::get('/admin/products', \App\Livewire\Admin\Product\ProductPage::class);
```

For the full setup walkthrough — including optional Excel/PDF export, Meilisearch, Docker, and theming — see the **[Quick Start guide](https://datatable.catzoid.tech/docs/quick-start)**.

## Documentation

The complete reference, with code samples and live demos for every feature, is hosted at **[datatable.catzoid.tech](https://datatable.catzoid.tech)**.

| Section | What's covered |
|---|---|
| [Getting Started](https://datatable.catzoid.tech/docs/introduction) | Introduction, installation, quick-start |
| [Core Features](https://datatable.catzoid.tech/docs/columns) | Columns, filters, search, sorting, pagination |
| [Editing & Actions](https://datatable.catzoid.tech/docs/inline-editing) | Inline editing, bulk actions, expandable rows |
| [Form Builder](https://datatable.catzoid.tech/docs/form-builder) | Programmatic forms with chainable modifiers |
| [Export](https://datatable.catzoid.tech/docs/export) | PDF, CSV & Excel with custom layouts |
| [Advanced Search](https://datatable.catzoid.tech/docs/advanced/scoring) | Per-column scoring, typo tolerance, **Meilisearch (beta)** |
| [Customization](https://datatable.catzoid.tech/docs/customization/theming) | Icons, localization, theming |
| [Deployment](https://datatable.catzoid.tech/docs/deployment/docker) | Docker patterns for vendor blade scanning |

## Requirements

- PHP 8.1+
- Laravel 11.x / 12.x / 13.x
- Livewire 3.x / 4.x
- Tailwind CSS v3 or v4
- DaisyUI v4 or v5

## Optional dependencies

```bash
composer require maatwebsite/excel        # Excel export
composer require barryvdh/laravel-dompdf  # PDF export
composer require laravel/scout meilisearch/meilisearch-php  # Meilisearch search driver (beta)
```

## Built with Claude

A large portion of this package — and its documentation site — was built collaboratively with [Anthropic's Claude](https://claude.com). The maintainer focused on architecture decisions, requirements, and design trade-offs; Claude handled most of the implementation, tests, and docs writing. A reminder that AI-assisted development, when guided well, multiplies what one developer can ship.

## Issues & contributions

Bug reports and feature requests welcome at [github.com/mrc4tz/mrcatz-datatables/issues](https://github.com/mrc4tz/mrcatz-datatables/issues).

## License

[MIT](LICENSE)
