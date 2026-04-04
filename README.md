<p align="center">
  <img src="https://img.shields.io/packagist/v/mrcatz/datatable?style=flat-square&color=1B3A5C" alt="Version">
  <img src="https://img.shields.io/packagist/dt/mrcatz/datatable?style=flat-square&color=C5A55A" alt="Downloads">
  <img src="https://img.shields.io/github/license/mrcatz/datatable?style=flat-square" alt="License">
  <img src="https://img.shields.io/badge/Laravel-11%2B-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Livewire-3%2B-FB70A9?style=flat-square&logo=livewire&logoColor=white" alt="Livewire">
  <img src="https://img.shields.io/badge/DaisyUI-5-5A0EF8?style=flat-square&logo=daisyui&logoColor=white" alt="DaisyUI">
</p>

# MrCatz DataTable

**DataTable + Form Builder** for **Laravel Livewire** — build complete admin pages in minutes.

**[View Live Demo](https://mrcatz-datatables-demo.xo.je)** | **[Demo Source Code](https://github.com/mrc4tz/mrcatz-datatable-demo)**

```bash
# Generate a full CRUD page (table + form) with one command
php artisan mrcatz:make Product --path=Admin
```

```php
// Or define forms in PHP — no Blade needed
public function setForm(): array
{
    return [
        MrCatzFormField::text('name', label: 'Name', rules: 'required', icon: 'person'),
        MrCatzFormField::email('email', label: 'Email', rules: 'required|email'),
        MrCatzFormField::select('role', label: 'Role', data: $roles, value: 'id', option: 'name'),
        MrCatzFormField::button('Check', onClick: 'checkAvail', style: 'info')->span(4),
    ];
}
```

## Why MrCatz?

| Problem | MrCatz Solution |
|---|---|
| Building CRUD pages over and over | `mrcatz:make` generates 4 files at once |
| Writing Blade forms for every model | **Form Builder** — define fields in PHP, auto-render with validation |
| Forms only work inside DataTable modal | **Standalone forms** — use Form Builder on any page (profile, settings, etc.) |
| Search is just basic LIKE | Multi-keyword search with **relevance scoring** |
| Filter state lost on reload | **URL persistence** — all state in URL, shareable & bookmarkable |
| Export requires manual coding | **Excel & PDF export** built-in with preview |
| No bulk delete | **Bulk actions** with per-row control |
| Editing requires opening a modal | **Inline editing** — double-click to edit cells, with validation |
| Table unusable on mobile | **Responsive card view** — auto card layout on small screens |
| No keyboard navigation | **Keyboard nav** — Arrow, Enter, Delete, Escape |
| Too many columns cluttering the view | **Column visibility** — hide/show columns |
| Column headers disappear on scroll | **Sticky header** — always visible |
| Can only sort by one column | **Multi-sort** — Shift+click for secondary sort |

## Features

**Form Builder**
- Define form fields in PHP — no Blade form file needed
- 25+ field types — text, email, password, number, select, textarea, file, toggle, checkbox, chooser, radio, date, time, datetime, color, range, url, tel, search, rating, hidden
- Button with Livewire hook — `onClick`, `withLoading()` for action buttons inside form
- Static elements — section, note, alert, divider, raw HTML
- DaisyUI style & size — `->style('primary')`, `->size('lg')` on any field
- Grid layout — 12-column grid with `->span()` for multi-column forms
- Dynamic/dependent fields — `->visibleWhen()`, `->visibleWhenAll()`, `->onChange()`, `->dependsOn()`
- Wire model modes — `->live()`, `->lazy()`, `->debounce()`
- Prefix, suffix, hint (with color), file preview, password confirmation
- Validation — auto-extracted rules & custom messages from field definitions
- Icon system — built-in SVG, raw HTML, or custom config `form_icons`
- Works in DataTable modal AND standalone pages (profile, settings, any Livewire component)

**CRUD & Data**
- CRUD lifecycle hooks — prepareAdd, prepareEdit, save, delete, bulk delete
- Inline editing — double-click cells to edit, Enter to save, with server-side validation
- Row click hook — custom action when row is clicked
- Fluent DataTable API — `->withColumn()`, `->withCustomColumn()`, `->enableExpand()`

**Search & Filter**
- Multi-keyword search with relevance scoring and highlight
- Filters — simple, callback, dependent (parent-child), dynamic show/hide
- Filter presets — save/load filter combinations (localStorage)
- Dependent filters auto-initialize from URL/presets

**Sorting & Columns**
- Column sorting — click header, visual indicator
- Multi-sort — Shift+click for multiple sort columns with numbered badges
- Column visibility toggle — hide/show columns, persistent in URL
- Column resize — drag handle on headers
- Column reorder — drag & drop headers, persistent in URL
- Default column visibility — `visible: false` to hide columns by default

**Export**
- Excel (.xlsx) & PDF (.pdf) with filter scope and preview count
- Built-in PDF template and Excel export class
- Export hooks — `beforeExport()` / `afterExport()` for data manipulation

**UX & Display**
- Responsive mobile view — auto card layout on small screens, tap-to-edit
- Sticky header — keeps thead visible on scroll
- Loading skeleton — placeholder rows during data fetch (responsive)
- Expandable rows — inline detail without modal
- Keyboard navigation — Arrow Up/Down, Enter, Delete/Backspace, Escape
- Zebra table styling
- Toast notifications — success, error, warning, info
- Loading overlay — fullscreen loading state
- URL persistence — search, sort, multi-sort, filter, pagination, column order, hidden columns

**Accessibility**
- `aria-sort` on sortable headers, `aria-modal` + `aria-labelledby` on modals
- Focus trap on all modals, `aria-label` on checkboxes
- `aria-live` on toast container, `role="grid"` on table

**Developer Experience**
- Artisan generator — `mrcatz:make` and `mrcatz:remove`
- Modular traits — HasFilters, HasExport, HasBulkActions
- Event constants — `MrCatzEvent::REFRESH_DATA` etc.
- Multi-language — English & Indonesian via Laravel lang files
- Configurable icon set — Default (inline SVG), Heroicons, Material Icons, Font Awesome, or custom
- Search debounce validation — auto-corrects invalid format
- Backward compatible — no strict types on public properties/methods
- Test suite — 103 tests, 243 assertions (incl. Livewire render tests)
- CI/CD — GitHub Actions (PHP 8.1–8.4)

---

## Installation

```bash
composer require mrcatz/datatable
```

### Setup

**1. Toast Notifications** — add to your main layout before `</body>`:

```blade
@include('mrcatz::components.ui.notification')
```

**2. Tailwind Content Scan** — add the package path:

```css
/* app.css (Tailwind v4) */
@source '../../vendor/mrcatz/**/*.blade.php';
```

```js
// tailwind.config.js (Tailwind v3)
content: ['./vendor/mrcatz/**/*.blade.php']
```

**3. Icons** — works out of the box with built-in SVG icons. No setup needed.

Optionally switch to Heroicons, Material Icons, or Font Awesome — see [Icon Set](#icon-set) section.

**4. Optional Dependencies:**

```bash
composer require maatwebsite/excel       # Excel export
composer require barryvdh/laravel-dompdf # PDF export
```

### Publish (Optional)

```bash
php artisan vendor:publish --tag=mrcatz-views   # Customize blade views
php artisan vendor:publish --tag=mrcatz-lang    # Customize translations
php artisan vendor:publish --tag=mrcatz-config  # Customize config
```

---

## Quick Start

### Fast Way: Artisan Generator

```bash
php artisan mrcatz:make Product --path=Admin
```

Generates 4 ready-to-use files:

```
app/Livewire/Admin/Product/ProductPage.php       <- CRUD logic
app/Livewire/Admin/Product/ProductTable.php       <- DataTable config
resources/views/livewire/admin/product/product-page.blade.php
resources/views/livewire/admin/product/product_form.blade.php
```

Add a route, edit columns and form — done.

```bash
php artisan mrcatz:make Product                           # Without path
php artisan mrcatz:make Product --path=Admin --table=my_products  # Custom table
php artisan mrcatz:make Product --path=Admin --force       # Overwrite existing
php artisan mrcatz:remove Product --path=Admin             # Remove generated files
```

### Manual Way

#### 1. Page Component (CRUD Logic)

```php
<?php

namespace App\Livewire\Admin\User;

use App\Models\User;
use MrCatz\DataTable\MrCatzComponent;
use Illuminate\Support\Facades\Hash;

class UserPage extends MrCatzComponent
{
    public $name, $email, $password;

    public function mount()
    {
        $this->setTitle('User');
        $this->breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'User', 'url' => null],
        ];
    }

    public function render()
    {
        return view('livewire.admin.user.user-page')
            ->layout('components.layouts.admin_layout');
    }

    public function prepareAddData()
    {
        $this->form_title = 'Add User';
        $this->reset(['name', 'email', 'password']);
    }

    public function prepareEditData($data)
    {
        $this->form_title = 'Edit User';
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->email = $data['email'];
    }

    public function prepareDeleteData($data)
    {
        $this->id = $data['id'];
        $this->deleted_text = $data['name'];
    }

    public function saveData()
    {
        $this->validate(['name' => 'required|max:255', 'email' => 'required|email']);

        if ($this->isEdit) {
            User::find($this->id)->update(['name' => $this->name, 'email' => $this->email]);
            $this->dispatch_to_view(true, 'update');
        } else {
            $user = User::create([
                'name' => $this->name, 'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $this->dispatch_to_view($user, 'insert');
        }
    }

    public function dropData()
    {
        $delete = User::find($this->id)->delete();
        $this->dispatch_to_view($delete, 'delete');
    }

    public function dropBulkData($selectedRows)
    {
        $count = User::whereIn('id', $selectedRows)->delete();
        $this->dispatch('refresh-data', [
            'status' => true, 'text' => $count . ' users deleted!'
        ]);
    }
}
```

#### 2. Table Component (DataTable Config)

```php
<?php

namespace App\Livewire\Admin\User;

use MrCatz\DataTable\MrCatzDataTableFilter;
use MrCatz\DataTable\MrCatzDataTables;
use MrCatz\DataTable\MrCatzDataTablesComponent;
use Illuminate\Support\Facades\DB;

class UserTable extends MrCatzDataTablesComponent
{
    public $showSearch = true;
    public $showAddButton = true;
    public $exportTitle = 'User Data';

    public function baseQuery()
    {
        return DB::table('users');
    }

    public function configTable()
    {
        return ['table_name' => 'users', 'table_id' => 'id'];
    }

    public function setTable()
    {
        return $this->CreateMrCatzTable()
            ->enableExpand(function ($data, $i) {
                return MrCatzDataTables::getExpandView($data, [
                    'Email' => 'email',
                    'Created' => 'created_at',
                ]);
            })
            ->withColumnIndex('No')
            ->withColumn('Name', 'name')
            ->withColumn('Email', 'email')
            ->withCustomColumn('Actions', function ($data, $i) {
                return MrCatzDataTables::getActionView($data, $i);
            });
    }

    public function getRowPerPageOption()
    {
        return [10, 15, 20, 30];
    }
}
```

#### 3. Blade Views

```blade
{{-- resources/views/livewire/admin/user/user-page.blade.php --}}
@push('title')
    <title>{{ $title ?? 'User' }} || {{ config('app.name') }}</title>
@endpush

<div class="p-6">
    @include('mrcatz::components.ui.breadcrumbs')
    <livewire:admin.user.user-table />
    @include('livewire.admin.user.user_form')
</div>

@include('mrcatz::components.ui.datatable-scripts')
```

```blade
{{-- resources/views/livewire/admin/user/user_form.blade.php --}}
<div>
    @extends('mrcatz::components.ui.datatable-form')

    @section('forms')
        <div class="space-y-4">
            <label class="form-control">
                <div class="label"><span class="label-text">Name</span></div>
                <input type="text" class="input input-bordered" wire:model="name" />
                @error('name') <span class="text-error text-xs">{{ $message }}</span> @enderror
            </label>
            <label class="form-control">
                <div class="label"><span class="label-text">Email</span></div>
                <input type="email" class="input input-bordered" wire:model="email" />
                @error('email') <span class="text-error text-xs">{{ $message }}</span> @enderror
            </label>
        </div>
    @endsection
</div>
```

---

## Feature Guide

### Columns

```php
public function setTable()
{
    return $this->CreateMrCatzTable()
        ->withColumnIndex('No')
        ->withColumn('Name', 'name')
        ->withColumn('Email', 'email', visible: false)        // hidden by default
        ->withColumn('Price', 'price', editable: true)         // inline editable
        ->withColumn('Code', 'code', uppercase: true, gravity: 'center')
        ->withColumn('Phone', 'phone', showOn: 'mobile')      // mobile card only
        ->withCustomColumn('Status', function ($data, $i) {
            return '<span class="badge badge-sm">' . $data->status . '</span>';
        }, 'status', false)
        ->withCustomColumn('Actions', function ($data, $i) {
            return MrCatzDataTables::getActionView($data, $i);
        }, showOn: 'desktop');                                 // desktop table only
}
```

**`withColumn` options:** `$uppercase`, `$th`, `$sort`, `$gravity` (`'left'`/`'center'`/`'right'`), `$editable`, `$visible`, `$rules`, `$showOn`

**`withCustomColumn` options:** `$key` (for search), `$sort`, `$visible`, `$showOn`

#### Image Column (`withColumnImage`)

Display images in table cells with clickable lightbox (scroll zoom, click to reset/close):

```php
// Storage (default) — asset('storage/' . value)
->withColumnImage('Avatar', 'avatar',
    width: 40, height: 40,
    previewClass: 'rounded-full ring ring-primary',
    fallback: 'name',
)

// Public directory — asset(value)
->withColumnImage('Image', 'image', 36, 36, 'rounded-lg', 'name', urlPrefix: 'public')

// External CDN — prefix + '/' + value
->withColumnImage('Photo', 'photo', 40, 40, 'rounded-full', urlPrefix: 'https://cdn.example.com')

// DB value is already full URL — use as-is
->withColumnImage('Photo', 'photo_url', 40, 40, 'rounded-full', urlPrefix: null)
```

| Parameter | Default | Description |
|---|---|---|
| `$width` | `40` | Preview width in pixels |
| `$height` | `40` | Preview height in pixels |
| `$previewClass` | `'rounded-full'` | Tailwind classes for shape/border/shadow |
| `$fallback` | `null` | DB column name — shows first letter when no image |
| `$urlPrefix` | `config('mrcatz.url_prefix')` | URL resolution mode (see below) |
| `$sort` | `false` | Sortable column |
| `$visible` | `true` | Column visibility |
| `$showOn` | `'both'` | Responsive visibility |

**`urlPrefix` modes:**

| Value | DB Value | Resolved URL |
|---|---|---|
| `'storage'` | `users/avatar.jpg` | `asset('storage/users/avatar.jpg')` |
| `'public'` | `uploads/img.jpg` | `asset('uploads/img.jpg')` |
| `'https://cdn.ex.com'` | `photos/1.jpg` | `https://cdn.ex.com/photos/1.jpg` |
| `null` | `https://full-url.com/img.jpg` | `https://full-url.com/img.jpg` |

If the DB value already starts with `http://`, `https://`, or `/`, it's used as-is regardless of prefix.

The same `urlPrefix` option is available in expand view image type:

```php
'Photo' => ['type' => 'image', 'key' => 'avatar', 'urlPrefix' => 'public', ...]
```

Clicking the image opens a lightbox with scroll zoom, click to reset/close.

#### Standalone Lightbox

The global lightbox is available on any page that includes the notification component. Use it on any `<img>` element:

```blade
{{-- Alpine.js --}}
<img src="{{ asset('photos/product.jpg') }}"
     class="w-32 h-32 rounded-lg object-cover cursor-zoom-in hover:opacity-80 transition-opacity"
     x-data
     @click="$dispatch('mrcatz-lightbox', { url: '{{ asset('photos/product.jpg') }}' })" />

{{-- Gallery --}}
<div class="flex gap-3">
    @foreach($photos as $photo)
        <img src="{{ asset('storage/' . $photo->path) }}"
             class="w-24 h-24 rounded-lg object-cover cursor-zoom-in hover:opacity-80 transition-opacity"
             x-data
             @click="$dispatch('mrcatz-lightbox', { url: '{{ asset('storage/' . $photo->path) }}' })" />
    @endforeach
</div>

{{-- Vanilla JS (no Alpine) --}}
<img src="/img/banner.jpg"
     class="cursor-zoom-in"
     onclick="window.dispatchEvent(new CustomEvent('mrcatz-lightbox', { detail: { url: '/img/banner.jpg' } }))" />
```

Just dispatch the `mrcatz-lightbox` event with `{ url: '...' }` — the global lightbox handles zoom, reset, and close automatically.

#### Responsive Column Visibility (`showOn`)

Control which columns appear on mobile (card view) vs desktop (table view):

```php
->withColumn('Name', 'name')                              // both (default)
->withColumn('Email', 'email', showOn: 'desktop')         // desktop table only
->withColumn('Phone', 'phone', showOn: 'mobile')          // mobile card only
->withCustomColumn('Actions', fn($d, $i) => ..., showOn: 'desktop')
```

| Value | Mobile Card | Desktop Table |
|---|---|---|
| `'both'` | Shown | Shown |
| `'mobile'` | Shown | Hidden |
| `'desktop'` | Hidden | Shown |

This is independent of `$visible` (column visibility toggle) — `showOn` controls responsive layout, `visible` controls user-togglable visibility.

#### Search Highlight on Custom Columns

`withColumn()` highlights search keywords automatically. For `withCustomColumn()`, call `$this->setSearchWord()`:

```php
->withCustomColumn('Category', function ($data, $i) {
    return $this->setSearchWord($data->category_name);
}, 'categories.name', true)
```

#### Table-Prefixed Keys (JOIN queries)

```php
->withColumn('Product', 'products.name')
->withCustomColumn('Category', fn($data, $i) => $this->setSearchWord($data->category_name), 'categories.name', true)
```

### Filters

```php
public function setFilter()
{
    // Simple array filter
    $roleFilter = MrCatzDataTableFilter::create(
        'filter_role', 'Role',
        [['value' => 'admin', 'label' => 'Admin'], ['value' => 'user', 'label' => 'User']],
        'value', 'label', 'role'
    )->get();

    // Custom callback filter
    $dateFilter = MrCatzDataTableFilter::createWithCallback(
        'filter_date', 'Date',
        [['value' => 'today', 'label' => 'Today'], ['value' => 'week', 'label' => 'This Week']],
        'value', 'label',
        function ($query, $value) {
            return match ($value) {
                'today' => $query->whereDate('created_at', today()),
                'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                default => $query,
            };
        }
    )->get();

    return [$roleFilter, $dateFilter];
}
```

### Dependent Filters

```php
public function setFilter()
{
    $categoryFilter = MrCatzDataTableFilter::create(
        'filter_category', 'Category', $categories, 'value', 'label', 'category_id'
    )->get();

    // Hidden by default (last param = false)
    $subFilter = MrCatzDataTableFilter::create(
        'filter_sub', 'Subcategory', [], 'value', 'label', 'subcategory_id', false
    )->get();

    return [$categoryFilter, $subFilter];
}

public function onFilterChanged($id, $value)
{
    if ($id === 'filter_category') {
        $this->resetFilter('filter_sub');
        if (!empty($value)) {
            $subs = DB::table('subcategories')->where('category_id', $value)->get()->toArray();
            $this->setFilterData('filter_sub', json_decode(json_encode($subs), true));
            $this->setFilterShow('filter_sub', true);
        } else {
            $this->setFilterShow('filter_sub', false);
        }
    }
}
```

### Inline Editing

```php
// Table: mark columns as editable, with optional validation rules
->withColumn('Name', 'name', editable: true, rules: 'required|max:255')
->withColumn('Price', 'price', editable: true, rules: 'required|numeric|min:0')

// Page: handle the update
public function onInlineUpdate($rowData, $columnKey, $newValue)
{
    DB::table('products')->where('id', $rowData['id'])->update([$columnKey => $newValue]);
    $this->dispatch_to_view(true, 'update');
}
```

Double-click to edit (tap on mobile), **Enter** to save, **Escape** to cancel.

Validation uses standard Laravel rules. If validation fails, the input shows a red border with the error message — no data is saved until the value is valid.

#### Per-Row Editable Control (`enableEditable`)

Use `enableEditable()` to control which rows and columns are editable via a callback:

```php
->withColumn('Name', 'name', editable: true)
->withColumn('Email', 'email', editable: true)
->withColumn('Price', 'price', editable: true, rules: 'required|numeric|min:0')
->enableEditable(function ($data, $i, $column_key) {
    // Prevent editing name column for super-admin rows
    if ($column_key == 'name' && $data->role === 'super-admin') {
        return false;
    }
    return true;
})
```

The callback receives three parameters:

| Parameter | Description |
|---|---|
| `$data` | Row data object |
| `$i` | Row index |
| `$column_key` | The column key being checked (e.g. `'name'`, `'email'`, `'price'`) |

Return `true` to allow editing, `false` to disable editing for that specific row + column combination.

Without a callback, all editable columns are editable on all rows:

```php
->enableEditable()  // all editable columns enabled on all rows
```

Without calling `enableEditable()` at all, the behavior is unchanged — column-level `editable: true` applies to all rows (backward compatible).

### Column Visibility

Enabled by default. Set default visibility per column:

```php
->withColumn('Name', 'name')                    // visible
->withColumn('Email', 'email', visible: false)   // hidden by default
```

Users toggle columns via the "Columns" button. State persisted in URL (`col_hidden`). Disable with `$enableColumnVisibility = false`.

### Multi-Sort

Click header = single sort. **Shift+click** = add secondary sort. Numbered badges show sort priority. State persisted in URL (`sort_multi`).

### Export Hooks

```php
// Table component
public function beforeExport($headers, $rows, $format, $scope)
{
    foreach ($rows as &$row) {
        $row[2] = 'Rp ' . number_format($row[2], 0, ',', '.');
    }
    return ['headers' => $headers, 'rows' => $rows];
}

public function afterExport($format, $scope)
{
    logger("Exported {$format} with scope: {$scope}");
}
```

### Row Click Hook

Enable in Table component, handle in Page component:

```php
// Table component
public $enableRowClick = true;

// Page component
public function onRowClick($data)
{
    return redirect()->route('product.show', $data['id']);
}
```

### Empty State Customization

Override `emptyStateView()` in your Table component to use a custom blade view:

```php
public function emptyStateView()
{
    return 'partials.custom-empty-state';  // your custom blade view
}
```

The view receives `$search` and `$activeFilterCount` variables. Return `null` (default) to use the built-in empty state.

### Sticky Header

```php
public $stickyHeader = true;  // thead stays visible on scroll
```

### Bulk Actions

```php
// Table
public $bulkPrimaryKey = 'id';
public $showBulkButton = true;

->enableBulk(function ($data, $i) {
    return Auth::id() !== $data->id; // can't select own account
})

// Page
public function dropBulkData($selectedRows)
{
    $count = User::whereIn('id', $selectedRows)->delete();
    $this->dispatch('refresh-data', ['status' => true, 'text' => $count . ' deleted!']);
}
```

### Expandable Rows

```php
public $expandableRows = true; // or 'both', 'mobile', 'desktop'

->enableExpand(function ($data, $i) {
    if (!$data->has_details) return null; // null = disable for this row

    return MrCatzDataTables::getExpandView($data, [
        // Text fields
        'Email' => 'email',
        'Created' => 'created_at',

        // Image with lightbox (click to zoom)
        'Photo' => [
            'type' => 'image',
            'key' => 'avatar',
            'width' => 64, 'height' => 64,
            'previewClass' => 'rounded-lg shadow-sm',
            'fallback' => 'name',
        ],

        // Download link (attachment style)
        'Document' => [
            'type' => 'button',
            'label' => 'Download PDF',
            'url' => fn($d) => asset('storage/' . $d->file_path),
            'icon' => 'download',
            'download' => true,
            'newTab' => true,
        ],

        // Action button (navigate)
        'Profile' => [
            'type' => 'link',
            'label' => 'View Profile',
            'url' => fn($d) => route('profile.show', $d->id),
            'icon' => 'person',
            'style' => 'info',
            'newTab' => true,
        ],
    ]);
})
```

#### Expand Field Types

| Type | Format | Description |
|---|---|---|
| text | `'Label' => 'db_key'` | Simple text display (default) |
| image | `'Label' => ['type' => 'image', 'key' => '...', ...]` | Image with clickable lightbox zoom |
| button | `'Label' => ['type' => 'button', 'label' => '...', ...]` | Attachment link (download) |
| link | `'Label' => ['type' => 'link', 'label' => '...', ...]` | Action button (navigate) |
| html | `'Label' => ['type' => 'html', 'content' => '...']` | Custom HTML content |

**Image options:** `key`, `width` (default: 64), `height` (default: 64), `previewClass` (default: `'rounded-lg'`), `fallback` (DB column for initial letter), `urlPrefix` (default: `config('mrcatz.url_prefix')`)

**Button/Link options:** `label`, `url` (string or Closure), `icon`, `style` (DaisyUI), `download` (bool), `newTab` (bool), `target`

**HTML type** — render custom content. `content` can be a string or Closure receiving `$data`:

```php
// Static HTML
'Badge' => [
    'type' => 'html',
    'content' => '<span class="badge badge-primary">Premium</span>',
],

// Dynamic HTML via Closure
'Status' => [
    'type' => 'html',
    'content' => fn($d) => '<div class="flex items-center gap-2">
        <div class="w-2 h-2 rounded-full ' . ($d->status === 'active' ? 'bg-success' : 'bg-error') . '"></div>
        ' . ucfirst($d->status) . '
    </div>',
],

// Progress bar
'Progress' => [
    'type' => 'html',
    'content' => fn($d) => '<progress class="progress progress-primary w-full" value="' . $d->progress . '" max="100"></progress>',
],
```

Return `null` from the callback to disable expand for a specific row — the chevron (desktop) and Details button (mobile) will be hidden for that row.

Control where expand is available:

| Value | Mobile | Desktop |
|---|---|---|
| `false` | Disabled | Disabled |
| `true` / `'both'` | Bottom-sheet modal | Inline expand |
| `'mobile'` | Bottom-sheet modal | Disabled |
| `'desktop'` | Disabled | Inline expand |

On mobile, expand content opens in a bottom-sheet modal instead of inline — better UX for small screens.

### Relevance Search

```php
public function configTable()
{
    return ['table_name' => 'users', 'table_id' => 'id'];
}
```

### URL Persistence

All state is automatically saved to the URL:

```
/users?search=ryan&sort=name&dir=asc&per_page=20&filter[role]=admin&col_hidden[0]=3&sort_multi[0][key]=name&sort_multi[0][dir]=asc
```

### Notifications

```php
$this->dispatch_to_view($success, 'insert');  // auto: "User successfully added!"
$this->show_notif('success', 'Custom message');
$this->show_notif('error', 'Something went wrong');
```

### Localization

Publish translations:

```bash
php artisan vendor:publish --tag=mrcatz-lang
```

Set locale in `config/mrcatz.php`:

```php
'locale' => 'id',  // 'en' (default) or 'id'
```

Add new languages by creating `lang/vendor/mrcatz/{locale}/mrcatz.php`.

### URL Prefix

Set the default URL prefix for image columns (`withColumnImage`) and expand view image fields globally in `config/mrcatz.php`:

```php
'url_prefix' => 'storage',  // 'storage' (default), 'public', 'https://cdn.example.com', or null
```

This eliminates the need to pass `urlPrefix` on every image column. You can still override per-column:

```php
// Uses global config value (no urlPrefix needed)
->withColumnImage('Avatar', 'avatar', 40, 40)

// Override for this specific column
->withColumnImage('Photo', 'photo', 40, 40, urlPrefix: 'public')
->withColumnImage('CDN Image', 'cdn_photo', 40, 40, urlPrefix: 'https://cdn.example.com')
->withColumnImage('Full URL', 'photo_url', 40, 40, urlPrefix: null)
```

The same applies to expand view image fields — omit `urlPrefix` to use the global config:

```php
'Photo' => ['type' => 'image', 'key' => 'avatar']  // uses config value
'Photo' => ['type' => 'image', 'key' => 'avatar', 'urlPrefix' => 'public']  // override
```

### Icon Set

MrCatz DataTable supports 5 icon sets. Set in `config/mrcatz.php`:

```php
'icon_set' => 'default',  // 'default', 'heroicons', 'material', 'fontawesome', 'custom'
```

| Icon Set | Size | Setup | Best For |
|---|---|---|---|
| **Default** | ~0KB (inline SVG) | No setup needed | Works out of the box |
| **Heroicons** | ~0KB (inline SVG) | `composer require blade-ui-kit/blade-heroicons` | Higher quality SVG |
| **Material Icons** | ~150KB | `<link>` Google Fonts in layout | Familiar, many icons |
| **Font Awesome** | ~300KB | `<link>` CDN in layout | Popular, many icons |
| **Custom** | Varies | Define map in config | Full control |

**Default** — built-in inline SVG, zero dependencies, zero CDN. Works immediately after install.

**Heroicons** — higher quality SVG via Blade Heroicons package:

```bash
composer require blade-ui-kit/blade-heroicons
```

```php
'icon_set' => 'heroicons',
```

> If `blade-heroicons` is not installed, automatically falls back to default (inline SVG).

**Material Icons** — add link to your layout:

```html
<link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Symbols+Outlined" rel="stylesheet">
```

```php
'icon_set' => 'material',
```

**Font Awesome 6** — add link to your layout:

```html
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
```

```php
'icon_set' => 'fontawesome',
```

**Custom** — define your own icon map. All 30 keys are pre-filled as comments in config (just uncomment and edit):

```php
'icon_set' => 'custom',
```

Icons not defined in `custom_icons` fallback to Default (inline SVG).

---

## Form Builder

Define form fields in PHP — no Blade form needed. Works in DataTable modal and standalone pages.

**[Full Form Builder Documentation](FORMBUILDER.md)**

Quick example:

```php
public function setForm(): array
{
    return [
        MrCatzFormField::text('name', label: 'Name', rules: 'required', icon: 'person'),
        MrCatzFormField::email('email', label: 'Email', rules: 'required|email'),
        MrCatzFormField::select('role', label: 'Role', data: $roles, value: 'id', option: 'name'),
        MrCatzFormField::image('avatar', label: 'Photo')
            ->preview($this->avatarUrl)
            ->previewClass('w-32 h-32 rounded-full ring ring-primary')
            ->onUpload('uploadPhoto')->onDelete('deletePhoto', 'Delete?'),
        MrCatzFormField::button('Check', onClick: 'check', style: 'info')->withLoading()->span(4),
        MrCatzFormField::password('password', label: 'Password')->withConfirmation(),
    ];
}
```

**Highlights:**
- **26+ field types** — text, email, select, textarea, file, image, toggle, radio, date, time, color, range, rating, button, and more
- **Image upload** — preview with `previewClass()` for full Tailwind control (circle, square, mask, any size/border/shadow)
- **Grid layout** — `->span()` + `->rowSpan()` for multi-column and side-by-side layouts
- **Dynamic fields** — `->visibleWhen()`, `->onChange()`, `->dependsOn()` for dependent/conditional fields
- **DaisyUI styling** — `->style('primary')`, `->size('lg')` on any field
- **Validation** — auto-extracted rules & custom messages
- **Standalone** — works on any Livewire component via `HasFormBuilder` trait, not just DataTable

---

## Property Reference

### Page Properties

| Property | Default | Description |
|---|---|---|
| `$title` | `''` | Page title, used in notifications |
| `$form_title` | `''` | Modal form title |
| `$deleted_text` | `''` | Delete confirmation text |
| `$breadcrumbs` | `[]` | Breadcrumb navigation data |

### Table Properties

| Property | Default | Description |
|---|---|---|
| `$showSearch` | `true` | Show search input |
| `$showAddButton` | `true` | Show add button |
| `$showExportButton` | `true` | Show export button |
| `$exportTitle` | `'Data Export'` | Export file/sheet title |
| `$usePagination` | `true` | Enable pagination |
| `$cardContainer` | `true` | Table inside card |
| `$borderContainer` | `false` | Table with border |
| `$withLoading` | `false` | Fullscreen loading overlay |
| `$tableZebraStyle` | `true` | Zebra stripe rows |
| `$typeSearch` | `false` | Realtime search on typing |
| `$typeSearchWithDelay` | `false` | Realtime search with debounce |
| `$typeSearchDelay` | `'500ms'` | Debounce delay (e.g. `'500ms'`, `'1s'`) |
| `$enableColumnSorting` | `true` | Enable column sorting |
| `$enableColumnVisibility` | `true` | Show column visibility toggle |
| `$enableColumnResize` | `true` | Enable column resize |
| `$enableColumnReorder` | `true` | Enable column drag & drop reorder |
| `$enableKeyboardNav` | `true` | Keyboard navigation |
| `$showKeyboardNavNote` | `false` | Show keyboard shortcut hints |
| `$expandableRows` | `false` | Expandable rows: `false`, `true`/`'both'`, `'mobile'`, `'desktop'` |
| `$stickyHeader` | `false` | Sticky header on scroll |
| `$enableRowClick` | `false` | Enable row click dispatch to Page |
| `$bulkPrimaryKey` | `null` | Primary key for bulk select, `null` = disabled |
| `$showBulkButton` | `false` | Show bulk select toggle button |

## Method Reference

### Page — Override Methods

| Method | Description |
|---|---|
| `prepareAddData()` | Prepare add form |
| `prepareEditData($data)` | Prepare edit form |
| `prepareDeleteData($data)` | Prepare delete confirmation |
| `saveData()` | Handle form submit |
| `dropData()` | Handle delete |
| `dropBulkData($selectedRows)` | Handle bulk delete |
| `onInlineUpdate($rowData, $columnKey, $newValue)` | Handle inline cell edit |
| `onRowClick($data)` | Handle row click |
| `dispatch_to_view($condition, $type)` | Send success/failure notification |
| `show_notif($type, $text)` | Show custom notification |
| `setForm()` | Define form fields (Form Builder) |
| `getFormValidationRules()` | Extract validation rules from form fields |
| `getFormValidationMessages()` | Extract custom validation messages |
| `hasFormBuilder()` | Check if Form Builder is active |
| `formFieldChanged($id, $value)` | Handle field change events |

### Table — Override Methods

| Method | Description |
|---|---|
| `baseQuery()` | Return query builder |
| `setTable()` | Define columns via fluent API |
| `configTable()` | Config for relevance search |
| `setFilter()` | Define filters |
| `getRowPerPageOption()` | Rows per page options |
| `setView()` | Custom blade view |
| `setPageName()` | Custom page name |
| `onDataLoaded($builder, $data)` | Hook after data loaded |
| `onFilterChanged($id, $value)` | Hook for dependent filters |
| `beforeExport($headers, $rows, $format, $scope)` | Manipulate export data |
| `afterExport($format, $scope)` | Post-export logic |
| `emptyStateView()` | Custom blade view for empty state (`null` = default) |
| `setFilterShow($id, $show)` | Show/hide filter dynamically |
| `setFilterData($id, $data)` | Update filter options dynamically |
| `resetFilter($id)` | Reset filter to "All" |

### Engine — Fluent API

| Method | Description |
|---|---|
| `withColumnIndex($head)` | Auto-numbered row column |
| `withColumn($head, $key, ...)` | Data column (`$uppercase`, `$th`, `$sort`, `$gravity`, `$editable`, `$visible`, `$rules`, `$showOn`) |
| `withCustomColumn($head, $callback, ...)` | Custom column (`$key`, `$sort`, `$visible`, `$showOn`) |
| `enableBulk($callback)` | Bulk select with per-row callback |
| `enableEditable($callback)` | Per-row/column editable control (`$data`, `$i`, `$column_key`) → `bool` |
| `enableExpand($callback)` | Expandable row content |
| `setDefaultOrder($key, $dir)` | Default sort |
| `addOrderBy($key, $dir)` | Additional sort |
| `getActionView($data, $i, $editable, $deletable)` | Render edit/delete buttons |
| `getExpandView($data, $fields)` | Render expand grid |
| `withColumnImage($head, $key, ...)` | Image column with lightbox (`$width`, `$height`, `$previewClass`, `$fallback`, `$sort`, `$visible`, `$showOn`) |

### Filter Factory

| Method | Description |
|---|---|
| `MrCatzDataTableFilter::create($id, $label, $data, $value, $option, $key, $show, $condition)` | Standard filter |
| `MrCatzDataTableFilter::createWithCallback($id, $label, $data, $value, $option, $callback, $show)` | Callback filter |
| `->get()` | Finalize (required) |

---

## Troubleshooting

### Search not working on JOIN queries

Use table-prefixed column keys in `withColumn()` and add `configTable()`:

```php
->withColumn('Name', 'products.name')  // not just 'name'
```

### Filter not showing

Make sure `->get()` is called on every filter:

```php
MrCatzDataTableFilter::create(...)->get();  // don't forget ->get()
```

### Export error: Class not found

```bash
composer require maatwebsite/excel        # Excel
composer require barryvdh/laravel-dompdf  # PDF
```

### Data not refreshing after save/delete

Make sure to call `dispatch_to_view()` at the end of `saveData()` / `dropData()`.

### Override method error "Declaration must be compatible"

Do not add return types on overridden methods:

```php
public function saveData() { ... }         // correct
public function saveData(): void { ... }   // wrong
```

---

## Comparison with Other Admin Panels

| Feature | **MrCatz** | **Filament** | **Backpack** | **Nova** |
|---|---|---|---|---|
| **Price** | Free (MIT) | Free (MIT) | Paid ($69-499) | Paid ($99/site) |
| **Stack** | Livewire + DaisyUI | Livewire + Custom UI | jQuery + Bootstrap | Vue + Tailwind |
| **Learning Curve** | Low — plain Livewire | Medium — custom ecosystem | Medium — CRUD DSL | Medium — Vue + API |
| **Setup** | `composer require` + go | `composer require` + panels setup | `composer require` + config | `composer require` + license |
| **Scaffolding** | `mrcatz:make` (4 files) | `make:filament-resource` | `backpack:crud` | `nova:resource` |
| **DataTable** | Built-in (search, sort, filter, export, inline edit, bulk, expand) | Built-in | Built-in | Built-in |
| **Form Builder** | `setForm()` PHP API | Resource form schema | `addField()` in controller | Field classes in resource |
| **Standalone Forms** | Yes — any Livewire component via `HasFormBuilder` trait | Yes — via Form Builder package | No — tied to CRUD | No — tied to Resource |
| **Image Column** | `withColumnImage()` + lightbox zoom | `ImageColumn` | `image` column type | `Image` field |
| **Image Lightbox** | Built-in (scroll zoom, click reset) | No (needs plugin) | No | No |
| **Expand Rows** | Built-in (text, image, button, link, html) | No (needs custom) | `details_row` (basic) | No |
| **Inline Editing** | Built-in (double-click, validation) | Needs plugin | `InlineCreate` (limited) | `inline` (basic) |
| **Multi-sort** | Built-in (Shift+click) | No | No | No |
| **URL Persistence** | Full (search, sort, filter, columns, page) | Partial | Partial | Partial |
| **Column Visibility** | Built-in toggle | Via layout | No | No |
| **Column Resize** | Built-in drag | No | No | No |
| **Column Reorder** | Built-in drag | No | No | No |
| **Keyboard Nav** | Built-in (Arrow, Enter, Delete, Escape) | No | No | No |
| **Export** | Excel + PDF built-in | Needs plugin | Needs plugin | No |
| **Mobile View** | Auto card layout | Responsive table | Responsive table | Responsive table |
| **Dynamic Fields** | `visibleWhen`, `dependsOn`, `onChange` | `visible()`, `reactive()` | `fake`, JS callbacks | `dependsOn` |
| **Grid Layout** | `span()`, `rowSpan()`, `mobileOrder()` | `columns()`, `columnSpan()` | No | No |
| **File Upload** | `file()`, `fileupload()`, `image()` with smart preview | `FileUpload` | `upload`, `image` | `File`, `Image` |
| **Icon System** | 5 sets (SVG, Heroicons, Material, FA, custom) | Heroicons (Blade Icons) | Font Awesome | Heroicons |
| **Theme** | Any DaisyUI theme | Filament themes | AdminLTE / CoreUI | Nova UI |
| **i18n** | Built-in (en, id, custom) | Built-in (50+ locales) | Built-in (40+ locales) | Built-in |
| **UI Framework** | Tailwind + DaisyUI | Tailwind + Custom | Bootstrap | Tailwind + Vue |
| **Bundle Size** | Minimal (zero CDN default) | Medium | Heavy (jQuery + Bootstrap) | Medium (Vue) |

### When to Choose MrCatz

**Choose MrCatz if you:**
- Want a lightweight DataTable + Form Builder without a full admin panel framework
- Need advanced table features (inline edit, multi-sort, column resize/reorder, keyboard nav, image lightbox)
- Prefer DaisyUI theming over custom component systems
- Want Form Builder usable on any page, not just CRUD resources
- Need expand rows with rich content (image, button, link, html)
- Want zero-cost, zero-dependency icons out of the box
- Are building with plain Livewire components (not a panel framework)

**Choose Filament if you:**
- Want a full admin panel with dashboard, widgets, notifications, user management
- Need the largest plugin ecosystem
- Prefer an opinionated, batteries-included approach

**Choose Backpack if you:**
- Have existing jQuery/Bootstrap projects
- Want point-and-click CRUD generation
- Need commercial support

**Choose Nova if you:**
- Are deeply invested in the Laravel ecosystem (first-party)
- Prefer Vue.js frontend
- Need official Laravel team support

---

## Requirements

- PHP >= 8.1
- Laravel >= 11.0
- Livewire >= 3.0
- Tailwind CSS + DaisyUI
- Icon set: Default (inline SVG), Heroicons, Material Icons, Font Awesome, or custom

## License

MIT
