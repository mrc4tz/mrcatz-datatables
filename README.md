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
| Filter state lost on reload | **URL persistence** — all state in URL, shareable & bookmarkable |
| Export requires manual coding | **Excel & PDF export** built-in with preview |
| No bulk delete | **Bulk actions** with per-row control |
| Editing requires opening a modal | **Inline editing** — double-click to edit cells |
| No keyboard navigation | **Keyboard nav** — Arrow, Enter, Delete, Escape |
| Too many columns cluttering the view | **Column visibility** — hide/show columns |
| Column headers disappear on scroll | **Sticky header** — always visible |
| Can only sort by one column | **Multi-sort** — Shift+click for secondary sort |

## Features

**CRUD & Data**
- CRUD lifecycle hooks — prepareAdd, prepareEdit, save, delete, bulk delete
- Inline editing — double-click cells to edit, Enter to save
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
- Sticky header — keeps thead visible on scroll
- Loading skeleton — placeholder rows during data fetch
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
- Configurable icon set — Material Icons, Heroicons, Font Awesome, or custom
- Search debounce validation — auto-corrects invalid format
- Backward compatible — no strict types on public properties/methods
- Test suite — 78 tests, 195 assertions
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
        ->withCustomColumn('Status', function ($data, $i) {
            return '<span class="badge badge-sm">' . $data->status . '</span>';
        }, 'status', false)
        ->withCustomColumn('Actions', function ($data, $i) {
            return MrCatzDataTables::getActionView($data, $i);
        });
}
```

**`withColumn` options:** `$uppercase`, `$th`, `$sort`, `$gravity` (`'left'`/`'center'`/`'right'`), `$editable`, `$visible`

**`withCustomColumn` options:** `$key` (for search), `$sort`, `$visible`

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
// Table: mark columns as editable
->withColumn('Name', 'name', editable: true)
->withColumn('Price', 'price', editable: true)

// Page: handle the update
public function onInlineUpdate($rowData, $columnKey, $newValue)
{
    DB::table('products')->where('id', $rowData['id'])->update([$columnKey => $newValue]);
    $this->dispatch_to_view(true, 'update');
}
```

Double-click to edit, **Enter** to save, **Escape** to cancel.

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
public $expandableRows = true;

->enableExpand(function ($data, $i) {
    return MrCatzDataTables::getExpandView($data, [
        'Email' => 'email', 'Created' => 'created_at',
    ]);
})
```

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

### Icon Set

MrCatz DataTable supports configurable icon sets. Set in `config/mrcatz.php`:

```php
'icon_set' => 'material',  // 'material' (default), 'heroicons', 'fontawesome', 'custom'
```

**Material Icons** (default) — requires Google Fonts link in your layout:

```html
<link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Symbols+Outlined" rel="stylesheet">
```

**Heroicons** — requires Blade Heroicons package, no external font needed:

```bash
composer require blade-ui-kit/blade-heroicons
```

```php
'icon_set' => 'heroicons',
```

**Font Awesome** — requires Font Awesome CSS in your layout:

```html
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
```

```php
'icon_set' => 'fontawesome',
```

**Custom** — define your own icon map with any icon library:

```php
'icon_set' => 'custom',
'custom_icons' => [
    'add' => '<i class="fas fa-plus"></i>',
    'edit' => '<i class="fas fa-pen"></i>',
    'delete' => '<i class="fas fa-trash"></i>',
    'close' => '<i class="fas fa-times"></i>',
    'search' => '<i class="fas fa-search"></i>',
    'download' => '<i class="fas fa-download"></i>',
    // ... define all icons you need
],
```

Icons not defined in `custom_icons` fallback to Material Icons.

All 30 icon names used: `add`, `edit`, `edit_note`, `delete`, `delete_forever`, `delete_sweep`, `close`, `cancel`, `check_circle`, `check_box`, `check_box_outline_blank`, `search`, `search_off`, `save`, `download`, `tune`, `filter_alt`, `bookmarks`, `restart_alt`, `info`, `warning`, `error`, `inbox`, `home`, `chevron_left`, `chevron_right`, `keyboard_arrow_up`, `keyboard_arrow_down`, `unfold_more`, `add_circle`, `table_view`, `picture_as_pdf`, `select_all`, `view_column`.

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
| `withColumn($head, $key, ...)` | Data column (`$uppercase`, `$th`, `$sort`, `$gravity`, `$editable`, `$visible`) |
| `withCustomColumn($head, $callback, ...)` | Custom column (`$key`, `$sort`, `$visible`) |
| `enableBulk($callback)` | Bulk select with per-row callback |
| `enableExpand($callback)` | Expandable row content |
| `setDefaultOrder($key, $dir)` | Default sort |
| `addOrderBy($key, $dir)` | Additional sort |
| `getActionView($data, $i, $editable, $deletable)` | Render edit/delete buttons |
| `getExpandView($data, $fields)` | Render expand grid |

### Filter Factory

| Method | Description |
|---|---|
| `MrCatzDataTableFilter::create($id, $label, $data, $value, $option, $key, $show, $condition)` | Standard filter |
| `MrCatzDataTableFilter::createWithCallback($id, $label, $data, $value, $option, $callback, $show)` | Callback filter |
| `->get()` | Finalize (required) |

---

## Troubleshooting

### Search tidak bekerja pada query JOIN

Gunakan prefix tabel di `withColumn()` dan tambahkan `configTable()`:

```php
->withColumn('Name', 'products.name')  // bukan 'name'
```

### Filter tidak muncul

Pastikan `->get()` dipanggil di setiap filter:

```php
MrCatzDataTableFilter::create(...)->get();  // jangan lupa ->get()
```

### Export error: Class not found

```bash
composer require maatwebsite/excel        # Excel
composer require barryvdh/laravel-dompdf  # PDF
```

### Data tidak refresh setelah save/delete

Pastikan memanggil `dispatch_to_view()` di akhir `saveData()` / `dropData()`.

### Override method error "Declaration must be compatible"

Jangan tambahkan return type pada method yang di-override:

```php
public function saveData() { ... }         // benar
public function saveData(): void { ... }   // salah
```

---

## Requirements

- PHP >= 8.1
- Laravel >= 11.0
- Livewire >= 3.0
- Tailwind CSS + DaisyUI
- Icon set: Material Icons (default), Heroicons, Font Awesome, or custom (configurable)

## License

MIT
