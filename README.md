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
| Editing requires opening a modal | **Inline editing** — double-click to edit cells, with validation |
| Table unusable on mobile | **Responsive card view** — auto card layout on small screens |
| No keyboard navigation | **Keyboard nav** — Arrow, Enter, Delete, Escape |
| Too many columns cluttering the view | **Column visibility** — hide/show columns |
| Column headers disappear on scroll | **Sticky header** — always visible |
| Can only sort by one column | **Multi-sort** — Shift+click for secondary sort |

## Features

**CRUD & Data**
- CRUD lifecycle hooks — prepareAdd, prepareEdit, save, delete, bulk delete
- Inline editing — double-click cells to edit, Enter to save, with server-side validation
- Row click hook — custom action when row is clicked
- Fluent DataTable API — `->withColumn()`, `->withCustomColumn()`, `->enableExpand()`
- **Form Builder** — define form fields in PHP, no Blade form file needed

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
    // Return null to disable expand for this specific row
    if (!$data->has_details) return null;

    return MrCatzDataTables::getExpandView($data, [
        'Email' => 'email', 'Created' => 'created_at',
    ]);
})
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

Define form fields programmatically in your Page component — no separate Blade form file needed. **Fully optional** — existing Blade forms (`@yield('forms')`) continue to work.

### Basic Usage

```php
use MrCatz\DataTable\MrCatzFormField;

class UserPage extends MrCatzComponent
{
    public $name, $email, $role, $password, $password_confirmation;

    public function setForm(): array
    {
        return [
            MrCatzFormField::text('name', label: 'Name', rules: 'required|max:255', icon: 'person'),
            MrCatzFormField::email('email', label: 'Email', rules: 'required|email', icon: 'mail'),
            MrCatzFormField::select('role', label: 'Role', data: [
                ['id' => 'admin', 'name' => 'Admin'],
                ['id' => 'user', 'name' => 'User'],
            ], value: 'id', option: 'name'),
            MrCatzFormField::password('password', label: 'Password', rules: 'required|min:8')
                ->withConfirmation(),
        ];
    }

    public function saveData()
    {
        $this->validate(
            $this->getFormValidationRules(),
            $this->getFormValidationMessages()
        );
        // ... save logic
    }
}
```

When `setForm()` returns fields, the form modal auto-renders them. If `setForm()` is not overridden (returns `[]`), the Blade `@yield('forms')` approach is used — **backward compatible**.

### Field Types

| Method | Description |
|---|---|
| `text(id, label, ...)` | Text input |
| `email(id, label, ...)` | Email input |
| `password(id, label, ...)` | Password input |
| `number(id, label, ...)` | Number input with `step`, `min`, `max` |
| `select(id, label, data, value, option, ...)` | Dropdown select |
| `textarea(id, label, ...)` | Multi-line text |
| `file(id, label, ...)` | File upload with optional `accept` |
| `toggle(id, label)` | Checkbox toggle |
| `chooser(id, label, data, value, option)` | Multi-checkbox buttons |
| `radio(id, label, options)` | Radio buttons (`options: ['val' => 'Label']`) |
| `hidden(id)` | Hidden input |

**Static content elements:**

| Method | Description |
|---|---|
| `section(title)` | Section heading with border |
| `note(text)` | Small muted text |
| `alert(text, type)` | Alert box: `'info'`, `'warning'`, `'success'`, `'error'` |
| `html(content)` | Raw HTML block |

### Modifiers (Chainable)

```php
MrCatzFormField::text('name', label: 'Name')
    ->span(6)                    // Grid column span (1-12, default: 12)
    ->hint('Max 255 characters') // Helper text below field
    ->prefix('Rp')               // Text before input
    ->suffix('kg')               // Text after input
    ->live()                     // wire:model.live (realtime)
    ->lazy()                     // wire:model.blur (on blur)
    ->debounce(300)              // wire:model.live.debounce.300ms
    ->disabled()                 // Disable field
    ->disabled(fn() => ...)      // Conditional disable
    ->hideWhen(fn() => ...)      // Conditional hide
    ->onChange('methodName')     // Call Livewire method on change
    ->dependsOn('other_field')  // Re-render when parent field changes
    ->visibleWhen('field', 'value')     // Show when field == value
    ->visibleWhen('field', ['a', 'b'])  // Show when field is 'a' or 'b'
    ->visibleWhenAll(['type' => 'x', 'role' => 'admin'])  // Multiple conditions
```

**File-specific:**

```php
MrCatzFormField::file('avatar', label: 'Avatar')
    ->preview($this->isEdit ? $this->avatarUrl : null)  // Show current file
```

**Password-specific:**

```php
MrCatzFormField::password('password', label: 'Password', rules: 'required|min:8')
    ->withConfirmation(label: 'Confirm Password')  // Auto-generates confirmation field
```

### Grid Layout

Form uses a 12-column grid. Use `->span()` for multi-column layouts:

```php
MrCatzFormField::text('first_name', label: 'First Name')->span(6),
MrCatzFormField::text('last_name', label: 'Last Name')->span(6),
MrCatzFormField::textarea('bio', label: 'Bio'),  // full width (default: span 12)
```

### Dynamic / Dependent Fields

```php
// Radio toggle — show/hide fields based on selection
MrCatzFormField::radio('type', label: 'Type', options: ['url' => 'URL', 'file' => 'FILE']),
MrCatzFormField::text('file_url', label: 'URL', icon: 'link')
    ->visibleWhen('type', 'url'),
MrCatzFormField::file('file_file', label: 'File')
    ->visibleWhen('type', 'file'),

// Cascade select — parent updates child data
MrCatzFormField::select('province_id', label: 'Province', data: $this->provinces, value: 'id', option: 'name')
    ->live()->onChange('loadCities'),
MrCatzFormField::select('city_id', label: 'City', data: $this->cities, value: 'id', option: 'name')
    ->dependsOn('province_id'),
```

In your component:

```php
public function loadCities($value)
{
    $this->cities = City::where('province_id', $value)->get()->toArray();
    $this->city_id = null;
}
```

### Validation

Rules are defined on each field — error messages render automatically:

```php
MrCatzFormField::text('name', label: 'Name',
    rules: 'required|max:255',
    messages: [
        'required' => 'Name is required!',
        'max' => 'Name must be under :max characters',
    ]
),
```

In `saveData()`:

```php
$this->validate(
    $this->getFormValidationRules(),     // ['name' => 'required|max:255', ...]
    $this->getFormValidationMessages()   // ['name.required' => 'Name is required!', ...]
);
```

### Icons

Form field icons support 3 modes:

```php
// 1. Icon name — uses mrcatz_icon() / config form_icons
MrCatzFormField::text('name', icon: 'person'),

// 2. Raw HTML — rendered as-is
MrCatzFormField::text('name', icon: '<i class="bi bi-person"></i>'),

// 3. Registered in config/mrcatz.php form_icons
// 'form_icons' => ['person' => '<path d="..."/>' ]
MrCatzFormField::text('name', icon: 'person'),
```

Config `form_icons` values can be SVG paths (auto-wrapped in `<svg>`) or raw HTML:

```php
// config/mrcatz.php
'form_icons' => [
    'person' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75..."/>',
    'mail'   => '<i class="bi bi-envelope"></i>',
],
```

### Full Example

```php
public function setForm(): array
{
    return [
        MrCatzFormField::section('Account Information'),
        MrCatzFormField::text('first_name', label: 'First Name', rules: 'required')->span(6),
        MrCatzFormField::text('last_name', label: 'Last Name', rules: 'required')->span(6),
        MrCatzFormField::email('email', label: 'Email', rules: 'required|email', icon: 'mail'),

        MrCatzFormField::section('Pricing'),
        MrCatzFormField::number('price', label: 'Price', rules: 'required|numeric')
            ->prefix('Rp')->suffix('/unit'),

        MrCatzFormField::section('Document'),
        MrCatzFormField::alert('Max upload 2MB, JPG/PNG only.', type: 'info'),
        MrCatzFormField::radio('doc_type', label: 'Document Type', options: ['url' => 'URL', 'file' => 'FILE']),
        MrCatzFormField::text('doc_url', label: 'URL')->visibleWhen('doc_type', 'url'),
        MrCatzFormField::file('doc_file', label: 'File', accept: 'image/*')
            ->visibleWhen('doc_type', 'file')
            ->preview($this->isEdit ? $this->existingFileUrl : null),

        MrCatzFormField::note('Changes will take effect immediately.'),
    ];
}
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

## Requirements

- PHP >= 8.1
- Laravel >= 11.0
- Livewire >= 3.0
- Tailwind CSS + DaisyUI
- Icon set: Default (inline SVG), Heroicons, Material Icons, Font Awesome, or custom

## License

MIT
