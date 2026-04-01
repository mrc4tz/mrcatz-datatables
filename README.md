<p align="center">
  <img src="https://img.shields.io/packagist/v/mrcatz/datatable?style=flat-square&color=1B3A5C" alt="Version">
  <img src="https://img.shields.io/packagist/dt/mrcatz/datatable?style=flat-square&color=C5A55A" alt="Downloads">
  <img src="https://img.shields.io/github/license/mrcatz/datatable?style=flat-square" alt="License">
  <img src="https://img.shields.io/badge/Laravel-11%2B-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Livewire-3%2B-FB70A9?style=flat-square&logo=livewire&logoColor=white" alt="Livewire">
  <img src="https://img.shields.io/badge/DaisyUI-5-5A0EF8?style=flat-square&logo=daisyui&logoColor=white" alt="DaisyUI">
</p>

# MrCatz DataTable

Full-featured DataTable + CRUD base class untuk **Laravel Livewire** — dari install sampai halaman admin lengkap dalam hitungan menit.

Satu perintah, empat file, langsung jalan:

```bash
php artisan mrcatz:make Product --path=Admin
```

## Kenapa MrCatz DataTable?

| Masalah | Solusi MrCatz |
|---|---|
| Bikin halaman CRUD berulang-ulang | `mrcatz:make` generate 4 file sekaligus |
| Search cuma LIKE biasa | Multi-keyword search dengan **relevance scoring** |
| Filter state hilang saat reload | **URL persistence** — search, sort, filter, page semua di URL |
| Export harus coding manual | **Excel & PDF export** built-in dengan preview count |
| Bulk delete tidak ada | **Bulk actions** dengan per-row control dan modal konfirmasi |
| Tabel tidak bisa di-navigate keyboard | **Keyboard navigation** — Arrow, Enter, Delete, Escape |
| Detail data harus buka modal | **Expandable rows** — klik chevron, detail muncul inline |

## Fitur

- **CRUD Lifecycle** — prepareAdd, prepareEdit, save, delete hooks
- **Fluent DataTable API** — `->withColumn()`, `->withCustomColumn()`, `->enableExpand()`
- **Multi-keyword Search** dengan relevance scoring dan highlight
- **Filter** — simple, callback, dependent (parent-child), show/hide dinamis
- **Export** — Excel (.xlsx) & PDF (.pdf) dengan scope filter
- **URL Persistence** — search, sort, filter, per_page, page
- **Filter Presets** — simpan/load kombinasi filter (localStorage)
- **Bulk Actions** — select all, per-row control, modal konfirmasi
- **Keyboard Navigation** — Arrow Up/Down, Enter, Delete/Backspace, Escape
- **Column Resize** — drag handle di header kolom
- **Column Reorder** — drag & drop header kolom
- **Column Sorting** — klik header untuk sort, visual indicator
- **Expandable Rows** — detail inline tanpa modal
- **Zebra Table** — baris genap/ganjil beda warna
- **Breadcrumbs & Page Title** — opsional, built-in
- **Toast Notification** — success, error, warning, info
- **Loading Overlay** — fullscreen loading state
- **Artisan Generator** — `mrcatz:make` dan `mrcatz:remove`

---

## Instalasi

```bash
composer require mrcatz/datatable
```

Laravel otomatis mendaftarkan service provider via package discovery.

### Setup

**1. Notifikasi Toast** — tambahkan di layout utama sebelum `</body>`:

```blade
@include('mrcatz::components.ui.notification')
```

**2. Tailwind Content Scan** — tambahkan path package:

```css
/* app.css (Tailwind v4) */
@source '../../vendor/mrcatz/**/*.blade.php';
```

```js
// tailwind.config.js (Tailwind v3)
content: ['./vendor/mrcatz/**/*.blade.php']
```

**3. Dependensi Opsional:**

```bash
composer require maatwebsite/excel      # Export Excel
composer require barryvdh/laravel-dompdf # Export PDF
```

### Publish Views (Opsional)

```bash
php artisan vendor:publish --tag=mrcatz-views
```

---

## Quick Start

### Cara Cepat: Artisan Generator

```bash
php artisan mrcatz:make Product --path=Admin
```

Generate 4 file siap pakai:

```
app/Livewire/Admin/Product/ProductPage.php       ← CRUD logic
app/Livewire/Admin/Product/ProductTable.php       ← DataTable config
resources/views/livewire/admin/product/product-page.blade.php
resources/views/livewire/admin/product/product_form.blade.php
```

Tambahkan route, edit kolom dan form — selesai.

```bash
# Tanpa path
php artisan mrcatz:make Product

# Custom nama tabel
php artisan mrcatz:make Product --path=Admin --table=my_products

# Overwrite file yang sudah ada
php artisan mrcatz:make Product --path=Admin --force

# Hapus file yang di-generate
php artisan mrcatz:remove Product --path=Admin
php artisan mrcatz:remove Product --path=Admin --force
```

### Cara Manual

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
        $this->form_title = 'Tambah User';
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
            'status' => true, 'text' => $count . ' user berhasil dihapus!'
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
    public $exportTitle = 'Data User';

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
                    'Dibuat' => 'created_at',
                ]);
            })
            ->withColumnIndex('No')
            ->withColumn('Nama', 'name')
            ->withColumn('Email', 'email')
            ->withCustomColumn('Aksi', function ($data, $i) {
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
                <div class="label"><span class="label-text">Nama</span></div>
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

## Dokumentasi Fitur

### Kolom

```php
public function setTable()
{
    return $this->CreateMrCatzTable()
        // Kolom nomor urut otomatis
        ->withColumnIndex('No')

        // Kolom data biasa
        ->withColumn('Nama', 'name')

        // Kolom dengan opsi lengkap
        ->withColumn('Email', 'email', uppercase: false, th: false, sort: true, gravity: 'left')

        // Kolom custom (return HTML)
        ->withCustomColumn('Status', function ($data, $i) {
            $color = $data->active ? 'badge-success' : 'badge-error';
            return '<span class="badge ' . $color . ' badge-sm">' . ($data->active ? 'Aktif' : 'Nonaktif') . '</span>';
        }, 'active', false)  // key (untuk search), sortable

        // Kolom aksi
        ->withCustomColumn('Aksi', function ($data, $i) {
            return MrCatzDataTables::getActionView($data, $i, editable: true, deletable: true);
        });
}
```

### Filter

Data option bisa dari **array**, **query builder**, atau **callback custom**:

```php
public function setFilter()
{
    // Array manual
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
        'filter_category', 'Kategori', $categories,
        'category_id', 'category_name', 'category_id'
    )->get();

    // Callback custom
    $dateFilter = MrCatzDataTableFilter::createWithCallback(
        'filter_date', 'Tanggal',
        [['value' => 'today', 'label' => 'Hari Ini'], ['value' => 'week', 'label' => 'Minggu Ini']],
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

### Dependent Filter (Parent-Child)

Override `onFilterChanged()` untuk membuat filter yang saling bergantung:

```php
public function setFilter()
{
    $categoryFilter = MrCatzDataTableFilter::create(
        'filter_category', 'Kategori', $categories, 'value', 'label', 'category_id'
    )->get();

    // Default hidden
    $subcategoryFilter = MrCatzDataTableFilter::create(
        'filter_subcategory', 'Sub Kategori', [], 'value', 'label', 'subcategory_id', false
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

> **Penting:** Selalu panggil `resetFilter()` sebelum `setFilterData()` saat parent berubah agar child filter kembali ke "Semua".

### Relevance Search

Aktifkan dengan `configTable()` — hasil pencarian diurutkan berdasarkan jumlah keyword yang cocok:

```php
public function configTable()
{
    return ['table_name' => 'users', 'table_id' => 'id'];
}
```

### Export (Excel & PDF)

```php
public $showExportButton = true;
public $exportTitle = 'Data User';
```

User memilih format, scope (semua/filtered), dan melihat preview jumlah data sebelum export.

### Bulk Actions

```php
// Table Component
public $bulkPrimaryKey = 'id';   // null = off
public $showBulkButton = true;

public function setTable()
{
    return $this->CreateMrCatzTable()
        ->enableBulk(function ($data, $i) {
            return Auth::id() !== $data->id; // tidak bisa select diri sendiri
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
        'status' => true, 'text' => $count . ' user berhasil dihapus!'
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
            // Helper bawaan — grid responsive
            return MrCatzDataTables::getExpandView($data, [
                'Email' => 'email',
                'Dibuat' => 'created_at',
            ]);
        })
        // ...
}
```

Atau custom blade view:

```php
->enableExpand(function ($data, $i) {
    return view('partials.user-detail', ['user' => $data])->render();
})
```

### Keyboard Navigation

| Key | Aksi |
|---|---|
| `Arrow Up/Down` | Navigasi antar baris |
| `Enter` | Buka modal edit |
| `Delete` / `Backspace` | Buka modal hapus |
| `Escape` | Batalkan fokus |

### URL Persistence

Semua state tersimpan di URL secara otomatis:

```
/users?search=ryan&sort=name&dir=asc&per_page=20&filter[filter_role]=admin&page=2
```

### Breadcrumbs & Page Title (Opsional)

```blade
@include('mrcatz::components.ui.breadcrumbs')
```

```php
public function mount()
{
    $this->setTitle('User');  // digunakan di <title> dan notifikasi
    $this->breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'User', 'url' => null],
    ];
}
```

### Notifikasi

```php
$this->show_notif('success', 'Berhasil!');
$this->show_notif('error', 'Terjadi kesalahan!');
$this->dispatch_to_view($success, 'insert'); // otomatis: "User berhasil ditambahkan!"
```

---

## Property Reference

### MrCatzComponent (Page)

| Property | Type | Default | Keterangan |
|---|---|---|---|
| `$title` | string | `''` | Judul halaman, digunakan di notifikasi |
| `$form_title` | string | `'Tambah Data'` | Judul modal form |
| `$deleted_text` | string | `''` | Teks konfirmasi delete |
| `$isEdit` | bool | `false` | Mode edit atau tambah |
| `$breadcrumbs` | array | `[]` | Data breadcrumb |

### MrCatzDataTablesComponent (Table)

| Property | Type | Default | Keterangan |
|---|---|---|---|
| `$showSearch` | bool | `true` | Tampilkan search input |
| `$showAddButton` | bool | `true` | Tampilkan tombol tambah |
| `$showExportButton` | bool | `true` | Tampilkan tombol export |
| `$exportTitle` | string | `'Data Export'` | Judul file export |
| `$usePagination` | bool | `true` | Aktifkan pagination |
| `$cardContainer` | bool | `true` | Tabel dalam card |
| `$borderContainer` | bool | `false` | Tabel dengan border |
| `$withLoading` | bool | `false` | Loading overlay fullscreen |
| `$typeSearch` | bool | `false` | Search realtime |
| `$typeSearchWithDelay` | bool | `false` | Search realtime dengan delay |
| `$typeSearchDelay` | string | `'500ms'` | Delay search realtime |
| `$bulkPrimaryKey` | string\|null | `null` | Primary key untuk bulk, null = off |
| `$showBulkButton` | bool | `false` | Tombol toggle bulk select |
| `$enableKeyboardNav` | bool | `true` | Keyboard navigation |
| `$enableColumnResize` | bool | `true` | Column resize |
| `$enableColumnReorder` | bool | `true` | Column reorder (drag & drop) |
| `$enableColumnSorting` | bool | `true` | Column sorting |
| `$showKeyboardNavNote` | bool | `false` | Catatan shortcut di bawah tabel |
| `$tableZebraStyle` | bool | `true` | Zebra stripe |
| `$expandableRows` | bool | `false` | Expandable row detail |

## Method Reference

### Page — Override Methods

| Method | Keterangan |
|---|---|
| `prepareAddData()` | Saat tombol tambah diklik |
| `prepareEditData($data)` | Saat tombol edit diklik |
| `prepareDeleteData($data)` | Saat tombol delete diklik |
| `saveData()` | Saat form submit |
| `dropData()` | Saat konfirmasi delete |
| `dropBulkData($selectedRows)` | Saat bulk delete dikonfirmasi |

### Table — Override Methods

| Method | Keterangan |
|---|---|
| `baseQuery()` | Return query builder |
| `setTable()` | Return MrCatzDataTables instance |
| `configTable()` | Config untuk relevance search |
| `setFilter()` | Return array filter |
| `getRowPerPageOption()` | Opsi rows per page |
| `setView()` | Custom blade view |
| `setPageName()` | Custom page name (multiple paginator) |
| `onDataLoaded($builder, $data)` | Hook setelah data di-load |
| `onFilterChanged($id, $value)` | Hook setelah filter berubah |
| `setFilterShow($id, $show)` | Show/hide filter by ID |
| `setFilterData($id, $data)` | Update data option filter by ID |
| `resetFilter($id)` | Reset filter value ke "Semua" |

### Engine — Fluent API

| Method | Keterangan |
|---|---|
| `withColumnIndex($head)` | Kolom nomor urut |
| `withColumn($head, $key, ...)` | Kolom data |
| `withCustomColumn($head, $callback, ...)` | Kolom custom |
| `enableBulk($callback)` | Bulk select per baris |
| `enableExpand($callback)` | Expandable row |
| `setDefaultOrder($key, $dir)` | Default sorting |
| `addOrderBy($key, $dir)` | Tambahan ordering |
| `getActionView($data, $i, ...)` | Static: tombol aksi |
| `getExpandView($data, $fields)` | Static: expand content |

---

## Requirements

- PHP >= 8.1
- Laravel >= 11.0
- Livewire >= 3.0
- Tailwind CSS + DaisyUI
- Material Icons (Google Fonts)

## License

MIT
