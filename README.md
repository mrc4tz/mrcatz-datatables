<p align="center">
  <img src="https://img.shields.io/packagist/v/mrcatz/datatable?style=flat-square&color=1B3A5C" alt="Version">
  <img src="https://img.shields.io/packagist/dt/mrcatz/datatable?style=flat-square&color=C5A55A" alt="Downloads">
  <img src="https://img.shields.io/github/license/mrcatz/datatable?style=flat-square" alt="License">
  <img src="https://img.shields.io/badge/Laravel-11%2B-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Livewire-3%2B-FB70A9?style=flat-square&logo=livewire&logoColor=white" alt="Livewire">
  <img src="https://img.shields.io/badge/DaisyUI-5-5A0EF8?style=flat-square&logo=daisyui&logoColor=white" alt="DaisyUI">
</p>

# MrCatz DataTable

Full-featured DataTable + CRUD base class for **Laravel Livewire** — from install to a complete admin page in minutes.

**[View Live Demo](https://mrcatz-datatables-demo.xo.je)** | **[Demo Source Code](https://github.com/mrc4tz/mrcatz-datatable-demo)**

One command, four files, ready to go:

```bash
php artisan mrcatz:make Product --path=Admin
```

## Why MrCatz DataTable?

| Problem | MrCatz Solution |
|---|---|
| Building CRUD pages over and over | `mrcatz:make` generates 4 files at once |
| Search is just basic LIKE | Multi-keyword search with **relevance scoring** |
| Filter state lost on reload | **URL persistence** — search, sort, filter, page all in URL |
| Export requires manual coding | **Excel & PDF export** built-in with count preview |
| No bulk delete | **Bulk actions** with per-row control and confirmation modal |
| No keyboard navigation for tables | **Keyboard nav** — Arrow, Enter, Delete, Escape |
| Viewing details requires opening a modal | **Expandable rows** — click chevron, details appear inline |
| UI only in one language | **Multi-language** — English (default) & Indonesian, configurable |

## Features

- **CRUD Lifecycle** — prepareAdd, prepareEdit, save, delete hooks
- **Fluent DataTable API** — `->withColumn()`, `->withCustomColumn()`, `->enableExpand()`
- **Multi-keyword Search** with relevance scoring and highlight
- **Filters** — simple, callback, dependent (parent-child), dynamic show/hide
- **Export** — Excel (.xlsx) & PDF (.pdf) with filter scope
- **URL Persistence** — search, sort, filter, per_page, page
- **Filter Presets** — save/load filter combinations (localStorage)
- **Bulk Actions** — select all, per-row control, confirmation modal
- **Keyboard Navigation** — Arrow Up/Down, Enter, Delete/Backspace, Escape
- **Column Resize** — drag handle on column headers
- **Column Reorder** — drag & drop column headers
- **Column Sorting** — click header to sort, visual indicator
- **Expandable Rows** — inline detail without modal
- **Zebra Table** — alternating row colors
- **Breadcrumbs & Page Title** — optional, built-in
- **Toast Notifications** — success, error, warning, info
- **Loading Overlay** — fullscreen loading state
- **Multi-language** — English (default) & Indonesian via config
- **Artisan Generator** — `mrcatz:make` and `mrcatz:remove`

---

## Installation

```bash
composer require mrcatz/datatable
```

Laravel will automatically register the service provider via package discovery.

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

**3. Optional Dependencies:**

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
app/Livewire/Admin/Product/ProductPage.php       ← CRUD logic
app/Livewire/Admin/Product/ProductTable.php       ← DataTable config
resources/views/livewire/admin/product/product-page.blade.php
resources/views/livewire/admin/product/product_form.blade.php
```

Add a route, edit columns and form — done.

```bash
# Without path
php artisan mrcatz:make Product

# Custom database table name
php artisan mrcatz:make Product --path=Admin --table=my_products

# Overwrite existing files
php artisan mrcatz:make Product --path=Admin --force

# Remove generated files
php artisan mrcatz:remove Product --path=Admin
php artisan mrcatz:remove Product --path=Admin --force
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

## Feature Documentation

### Columns

```php
public function setTable()
{
    return $this->CreateMrCatzTable()
        // Auto-incrementing row number
        ->withColumnIndex('No')

        // Simple data column
        ->withColumn('Name', 'name')

        // Column with full options
        ->withColumn('Email', 'email', uppercase: false, th: false, sort: true, gravity: 'left')

        // Custom column (return HTML)
        ->withCustomColumn('Status', function ($data, $i) {
            $color = $data->active ? 'badge-success' : 'badge-error';
            return '<span class="badge ' . $color . ' badge-sm">' . ($data->active ? 'Active' : 'Inactive') . '</span>';
        }, 'active', false)  // key (for search), sortable

        // Action column
        ->withCustomColumn('Actions', function ($data, $i) {
            return MrCatzDataTables::getActionView($data, $i, editable: true, deletable: true);
        });
}
```

#### Search Highlight on Custom Columns

`withColumn()` automatically highlights search keywords in the displayed text. For `withCustomColumn()`, you need to call `$this->setSearchWord()` manually to apply the highlight:

```php
->withCustomColumn('Category', function ($data, $i) {
    $category = $this->setSearchWord($data->category_name);
    $sub = $this->setSearchWord($data->subcategory_name ?? '');
    return '<span>' . $category . ' / ' . $sub . '</span>';
}, 'categories.name', true)
```

`setSearchWord($text)` escapes HTML and wraps matching keywords in `<span class="font-extrabold">`. Without it, search keywords won't be highlighted in custom columns.

#### Table-Prefixed Column Keys (JOIN queries)

When using JOIN queries, column names like `name` can be ambiguous. Use the full table-prefixed key:

```php
// withColumn — prefix for search/sort, display auto-resolves
->withColumn('Product', 'products.name')

// withCustomColumn — prefix in the 3rd parameter (search key)
->withCustomColumn('Category', function ($data, $i) {
    return $this->setSearchWord($data->category_name);
}, 'categories.name', true)
```

### Filters

Filter options can come from **arrays**, **query builder**, or **custom callbacks**:

```php
public function setFilter()
{
    // Manual array
    $roleFilter = MrCatzDataTableFilter::create(
        'filter_role', 'Role',
        [['value' => 'admin', 'label' => 'Admin'], ['value' => 'user', 'label' => 'User']],
        'value', 'label', 'role'
    )->get();

    // Query builder
    $categories = json_decode(json_encode(
        DB::table('categories')->orderBy('name')->get()->toArray()
    ), true);

    $categoryFilter = MrCatzDataTableFilter::create(
        'filter_category', 'Category', $categories,
        'category_id', 'category_name', 'category_id'
    )->get();

    // Custom callback
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

    return [$roleFilter, $categoryFilter, $dateFilter];
}
```

### Dependent Filters (Parent-Child)

Override `onFilterChanged()` to create filters that depend on each other:

```php
public function setFilter()
{
    $categoryFilter = MrCatzDataTableFilter::create(
        'filter_category', 'Category', $categories, 'value', 'label', 'category_id'
    )->get();

    // Hidden by default
    $subcategoryFilter = MrCatzDataTableFilter::create(
        'filter_subcategory', 'Subcategory', [], 'value', 'label', 'subcategory_id', false
    )->get();

    return [$categoryFilter, $subcategoryFilter];
}

public function onFilterChanged($id, $value)
{
    if ($id === 'filter_category') {
        $this->resetFilter('filter_subcategory');

        if (!empty($value)) {
            $subs = json_decode(json_encode(
                DB::table('subcategories')->where('category_id', $value)->get()->toArray()
            ), true);
            $this->setFilterData('filter_subcategory', $subs);
            $this->setFilterShow('filter_subcategory', true);
        } else {
            $this->setFilterShow('filter_subcategory', false);
        }
    }
}
```

> **Important:** Always call `resetFilter()` before `setFilterData()` when the parent changes — this ensures the child filter resets to "All" so it doesn't display a stale value.

### Relevance Search

Enable with `configTable()` — search results are ranked by keyword match count:

```php
public function configTable()
{
    return ['table_name' => 'users', 'table_id' => 'id'];
}
```

### Export (Excel & PDF)

```php
public $showExportButton = true;
public $exportTitle = 'User Data';
```

Users can choose format, scope (all/filtered), and see a data count preview before exporting.

### Bulk Actions

```php
// Table Component
public $bulkPrimaryKey = 'id';   // null = off
public $showBulkButton = true;

public function setTable()
{
    return $this->CreateMrCatzTable()
        ->enableBulk(function ($data, $i) {
            return Auth::id() !== $data->id; // can't select own account
        })
        // ...
}
```

```php
// Page Component
public function dropBulkData($selectedRows)
{
    $count = User::whereIn('id', $selectedRows)->delete();
    $this->dispatch('refresh-data', [
        'status' => true, 'text' => $count . ' users deleted!'
    ]);
}
```

### Expandable Rows

```php
public $expandableRows = true;

public function setTable()
{
    return $this->CreateMrCatzTable()
        ->enableExpand(function ($data, $i) {
            // Built-in helper — responsive grid
            return MrCatzDataTables::getExpandView($data, [
                'Email' => 'email',
                'Created' => 'created_at',
            ]);
        })
        // ...
}
```

Or use a custom blade view:

```php
->enableExpand(function ($data, $i) {
    return view('partials.user-detail', ['user' => $data])->render();
})
```

### Keyboard Navigation

| Key | Action |
|---|---|
| `Arrow Up/Down` | Navigate between rows |
| `Enter` | Open edit modal |
| `Delete` / `Backspace` | Open delete modal |
| `Escape` | Cancel focus |

### URL Persistence

All state is automatically saved to the URL:

```
/users?search=ryan&sort=name&dir=asc&per_page=20&filter[filter_role]=admin&page=2
```

### Breadcrumbs & Page Title (Optional)

```blade
@include('mrcatz::components.ui.breadcrumbs')
```

```php
public function mount()
{
    $this->setTitle('User');  // used in <title> and notifications
    $this->breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'User', 'url' => null],
    ];
}
```

### Notifications

```php
$this->show_notif('success', 'Success!');
$this->show_notif('error', 'Something went wrong!');
$this->dispatch_to_view($success, 'insert'); // auto: "User successfully added!"
```

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
| `$expandableRows` | `false` | Enable expandable row detail |
| `$stickyHeader` | `false` | Sticky header on scroll |
| `$bulkPrimaryKey` | `null` | Primary key for bulk select, `null` = disabled |
| `$showBulkButton` | `false` | Show bulk select toggle button |

## Method Reference

### Page — Override Methods

| Method | Description |
|---|---|
| `prepareAddData()` | Called when add button is clicked |
| `prepareEditData($data)` | Called when edit button is clicked |
| `prepareDeleteData($data)` | Called when delete button is clicked |
| `saveData()` | Called on form submit |
| `dropData()` | Called on delete confirmation |
| `dropBulkData($selectedRows)` | Called on bulk delete confirmation |
| `onInlineUpdate($rowData, $columnKey, $newValue)` | Called when inline cell edit is saved |
| `dispatch_to_view($condition, $type)` | Dispatch success/failure notification (`$type`: `'insert'`, `'update'`, `'delete'`) |
| `show_notif($type, $text)` | Show notification (`$type`: `'success'`, `'error'`, `'warning'`, `'info'`) |

### Table — Override Methods

| Method | Description |
|---|---|
| `baseQuery()` | Return query builder |
| `setTable()` | Return `MrCatzDataTables` instance with column definitions |
| `configTable()` | Return config for relevance search, e.g. `['table_name' => 'x', 'table_id' => 'id']` |
| `setFilter()` | Return array of `MrCatzDataTableFilter` |
| `getRowPerPageOption()` | Return rows per page options (default: `[5, 10, 15, 20]`) |
| `setView()` | Return custom blade view path |
| `setPageName()` | Return page name for multiple paginators |
| `onDataLoaded($builder, $data)` | Hook after data is loaded |
| `onFilterChanged($id, $value)` | Hook after filter changes (for dependent filters) |
| `onRowClick($data)` | Hook when a row is clicked |
| `beforeExport($headers, $rows, $format, $scope)` | Hook before export — return `['headers' => ..., 'rows' => ...]` |
| `afterExport($format, $scope)` | Hook after export completes |
| `setFilterShow($id, $show)` | Show/hide a filter dynamically |
| `setFilterData($id, $data)` | Update filter dropdown options dynamically |
| `resetFilter($id)` | Reset a filter value to "All" |

### Engine — Fluent API (`MrCatzDataTables`)

| Method | Description |
|---|---|
| `withColumnIndex($head)` | Auto-numbered row column |
| `withColumn($head, $key, ...)` | Data column. Options: `$uppercase`, `$th`, `$sort`, `$gravity`, `$editable`, `$visible` |
| `withCustomColumn($head, $callback, ...)` | Custom rendered column. Options: `$key`, `$sort`, `$visible` |
| `enableBulk($callback)` | Enable bulk select with optional per-row callback |
| `enableExpand($callback)` | Enable expandable row with content callback |
| `setDefaultOrder($key, $dir)` | Set default sort column and direction |
| `addOrderBy($key, $dir)` | Add additional sort order |
| `getActionView($data, $i, $editable, $deletable)` | Static: render edit/delete action buttons |
| `getExpandView($data, $fields)` | Static: render expandable row grid |

### Filter Factory (`MrCatzDataTableFilter`)

| Method | Description |
|---|---|
| `create($id, $label, $data, $value, $option, $key, $show, $condition)` | Create standard filter |
| `createWithCallback($id, $label, $data, $value, $option, $callback, $show)` | Create filter with custom query callback |
| `->get()` | Finalize filter (must call before returning) |

---

## Localization

MrCatz DataTable supports two localization methods: **Laravel lang files** (recommended) and **config fallback**.

### Method 1: Laravel Lang Files (Recommended)

Publish the lang files:

```bash
php artisan vendor:publish --tag=mrcatz-lang
```

This creates `lang/vendor/mrcatz/en/mrcatz.php` and `lang/vendor/mrcatz/id/mrcatz.php`. Edit these files to customize strings or add new languages.

Set the app locale in `config/app.php`:

```php
'locale' => 'id',  // uses lang/vendor/mrcatz/id/mrcatz.php
```

To add a new language (e.g. Japanese), create `lang/vendor/mrcatz/ja/mrcatz.php` and translate all keys.

### Method 2: Config Fallback

Publish the config file:

```bash
php artisan vendor:publish --tag=mrcatz-config
```

This creates `config/mrcatz.php`. Change the locale:

```php
// config/mrcatz.php
'locale' => 'id',  // 'en' (default) or 'id' (Indonesian)
```

Customize individual strings:

```php
'en' => [
    'btn_add' => 'Create New',          // default: 'Add'
    'no_data' => 'Nothing here yet',    // default: 'No data yet'
    // ...
],
```

Or add a new language by adding a new key alongside `'en'` and `'id'`.

> **Priority:** Lang files take precedence over config. If a key exists in `lang/vendor/mrcatz/`, it will be used. Otherwise, falls back to `config/mrcatz.php`.

---

## Export Hooks

Override `beforeExport()` to manipulate headers/rows before export (e.g., format currency, add summary):

```php
public function beforeExport($headers, $rows, $format, $scope)
{
    // Format price column
    foreach ($rows as &$row) {
        $row[2] = 'Rp ' . number_format($row[2], 0, ',', '.');
    }

    // Add summary row
    $rows[] = ['', 'Total', 'Rp 1.000.000'];

    return ['headers' => $headers, 'rows' => $rows];
}
```

Override `afterExport()` to run logic after export (e.g., log, notify):

```php
public function afterExport($format, $scope)
{
    logger("Exported {$format} with scope: {$scope}");
}
```

---

## Column Reorder Persistence

Column reorder is automatically persisted in the URL via `#[Url]` (query parameter `col_order`). When users drag-and-drop column headers, the new order is reflected in the URL — shareable, bookmarkable, and survives page refresh just like search, sort, and filter state.

---

## PDF Export

PDF export uses a built-in template. To customize, publish the view:

```bash
php artisan vendor:publish --tag=mrcatz-views
```

Then edit `resources/views/vendor/mrcatz/exports/datatable-pdf.blade.php`. If you already have `resources/views/exports/datatable-pdf.blade.php`, that will be used instead.

---

## Accessibility

MrCatz DataTable includes built-in accessibility support:

- **`aria-sort`** on sortable column headers (ascending/descending/none)
- **`aria-modal`** and **`aria-labelledby`** on all modals
- **Focus trap** (`x-trap`) on modals to prevent keyboard users from tabbing outside
- **`aria-label`** on bulk selection checkboxes (header + per-row)
- **`aria-live="polite"`** on toast notification container
- **`role="alert"`** on individual toast notifications
- **`role="grid"`** and **`aria-label`** on the data table

---

## Loading Skeleton

When data is loading (search, filter, pagination, sort), skeleton placeholder rows are shown instead of a spinner. This reduces layout shift and provides a more responsive feel.

---

## Column Visibility

Column visibility toggle is **enabled by default** (`$enableColumnVisibility = true`). A "Columns" button appears in the toolbar with checkboxes for each column. Hidden columns are persisted in the URL (`col_hidden` parameter) — shareable and bookmarkable.

To disable:

```php
public $enableColumnVisibility = false; // all columns always visible, button hidden
```

### Default Visibility per Column

Set default visibility via the `visible` parameter on `withColumn()` or `withCustomColumn()`:

```php
->withColumn('Name', 'name')                     // visible by default
->withColumn('Email', 'email', visible: false)    // hidden by default
->withColumn('Phone', 'phone', visible: false)    // hidden by default
->withCustomColumn('Actions', fn(...) => ..., visible: true)
```

Columns with `visible: false` are hidden on first load. Users can toggle them back via the Columns dropdown. URL params (`col_hidden`) always take precedence over defaults — so if a user reveals a hidden column, it stays visible on refresh.

| Setting | Behavior |
|---|---|
| `$enableColumnVisibility = true` (default) | Columns button shown, user can hide/show |
| `$enableColumnVisibility = false` | Button hidden, all columns always visible |
| `visible: false` on column | Column hidden by default on first load |
| URL `?col_hidden[0]=3` | Overrides defaults — URL always wins |

---

## Inline Editing

Mark columns as editable in `setTable()`:

```php
->withColumn('Name', 'name', editable: true)
->withColumn('Price', 'price', editable: true)
```

Users can **double-click** a cell to edit. Press **Enter** to save, **Escape** to cancel. The `inlineUpdateData` event is dispatched to the Page component.

Handle the update in your Page component:

```php
public function onInlineUpdate($rowData, $columnKey, $newValue)
{
    DB::table('products')
        ->where('id', $rowData['id'])
        ->update([$columnKey => $newValue]);

    $this->dispatch_to_view(true, 'update');
}
```

---

## Multi-Sort

Click a column header to sort by that column (single sort). **Shift+click** to add additional sort columns. Each sorted column shows a numbered badge indicating sort priority.

Multi-sort state is persisted in the URL (`sort_multi` parameter).

---

## Sticky Header

Enable sticky header to keep column headers visible when scrolling long tables:

```php
public $stickyHeader = true;
```

The table container gets a max height of 70vh with the header pinned at the top.

---

## Row Click Hook

Override `onRowClick()` to handle row click events:

```php
public function onRowClick($data)
{
    // Navigate to detail page
    return redirect()->route('product.show', $data['id']);
}
```

---

## Troubleshooting / FAQ

### Search tidak bekerja pada query JOIN

Jika menggunakan `JOIN` di `baseQuery()`, pastikan kolom di `withColumn()` menggunakan prefix tabel:

```php
->withColumn('Name', 'products.name')  // ✅ dengan prefix tabel
->withColumn('Name', 'name')           // ❌ ambiguous column
```

Dan tambahkan `configTable()` untuk relevance search:

```php
public function configTable() {
    return ['table_name' => 'products', 'table_id' => 'id'];
}
```

### Filter tidak muncul / tidak terlihat

1. Pastikan method `setFilter()` return array yang sudah memanggil `->get()`:
```php
public function setFilter() {
    return [
        MrCatzDataTableFilter::create('status', 'Status', $data, 'id', 'name', 'status')->get(),
        //                                                                                ^^^^
    ];
}
```

2. Untuk dependent filter yang hidden, pastikan `$show` parameter diset `false`:
```php
MrCatzDataTableFilter::create('sub', 'Sub', [], 'id', 'name', 'sub_id', false)->get()
//                                                                        ^^^^^
```

### Export error: Class not found

**Excel export** membutuhkan `maatwebsite/excel`:
```bash
composer require maatwebsite/excel
```

**PDF export** membutuhkan `barryvdh/laravel-dompdf`:
```bash
composer require barryvdh/laravel-dompdf
```
Package sudah menyediakan template PDF bawaan. Untuk customize, publish views lalu edit `exports/datatable-pdf.blade.php`.

Untuk **Excel export**, package menyediakan `MrCatzExport` class bawaan. Jika sebelumnya menggunakan `App\Exports\DatatableExport`, class tersebut tetap akan digunakan (fallback otomatis).

### Pagination menampilkan data yang sama di halaman berbeda

Pastikan `baseQuery()` memiliki ordering yang konsisten. Tambahkan `setDefaultOrder()` di `setTable()`:

```php
public function setTable() {
    return $this->CreateMrCatzTable()
        ->withColumn('Name', 'name')
        ->setDefaultOrder('id', 'desc');  // order yang konsisten
}
```

### Keyboard navigation tidak berfungsi

Pastikan `$enableKeyboardNav = true` di Table component (default sudah `true`). Keyboard navigation hanya aktif saat tabel dalam fokus — klik pada tabel terlebih dahulu.

### Data tidak refresh setelah save/delete

Pastikan memanggil `dispatch_to_view()` di akhir `saveData()` / `dropData()`:

```php
public function saveData() {
    $result = DB::table('products')->insert([...]);
    $this->dispatch_to_view($result, 'insert');
}
```

### Override method menyebabkan "Declaration must be compatible" error

Jangan tambahkan return type atau parameter type yang lebih strict dari parent class:

```php
public function saveData() { ... }          // ✅ tanpa return type
public function saveData(): void { ... }    // ❌ akan error
```

### Cara publish dan override lokalisasi

```bash
php artisan vendor:publish --tag=mrcatz-lang
```

File akan di-copy ke `lang/vendor/mrcatz/`. Edit file tersebut untuk override atau tambah bahasa baru.

---

## Requirements

- PHP >= 8.1
- Laravel >= 11.0
- Livewire >= 3.0
- Tailwind CSS + DaisyUI
- Material Icons (Google Fonts)

## License

MIT
