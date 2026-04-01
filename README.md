# MrCatz DataTable

Livewire DataTable base class untuk Laravel — menyediakan CRUD, bulk actions, export, keyboard navigation, expandable rows, URL persistence, dan banyak lagi. Didesain untuk digunakan bersama **Tailwind CSS**, **DaisyUI**, dan **Material Icons** (Google Fonts).

## Instalasi

```bash
composer require mrcatz/datatable
```

Laravel akan otomatis mendaftarkan service provider via package discovery.

### Publish Views (Opsional)

Jika ingin meng-customize tampilan blade:

```bash
php artisan vendor:publish --tag=mrcatz-views
```

Views akan di-copy ke `resources/views/vendor/mrcatz/`.

### Tailwind Content Scan

Tambahkan path package ke konfigurasi Tailwind agar class dari blade views ter-compile:

```js
// tailwind.config.js atau di @source pada app.css (Tailwind v4)
'./vendor/mrcatz/**/*.blade.php'
```

### Notifikasi Toast

Tambahkan include berikut di layout utama (misalnya `admin_layout.blade.php`), sebelum `</body>`:

```blade
@include('mrcatz::components.ui.notification')
```

Ini menyediakan toast notification yang digunakan oleh `show_notif()` dan `dispatch_to_view()`.

### Breadcrumbs (Opsional)

Package menyediakan breadcrumbs component yang terintegrasi dengan property `$breadcrumbs` dan `$title` dari `MrCatzComponent`. Tambahkan di blade page view:

```blade
@include('mrcatz::components.ui.breadcrumbs')
```

Breadcrumbs otomatis ter-render berdasarkan data yang di-set di `mount()`:

```php
public function mount()
{
    $this->setTitle('User');
    $this->breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'User Management', 'url' => null],  // null = tidak ada link (halaman aktif)
    ];
}
```

Jika `$breadcrumbs` kosong, component tidak me-render apapun.

### Page Title (Opsional)

`setTitle()` menyimpan judul halaman ke property `$title`. Gunakan di blade view untuk tag `<title>` atau heading:

```blade
{{-- Di dalam @push('title') atau langsung di view --}}
<title>{{ $title ?? 'Default' }} - {{ config('app.name') }}</title>

{{-- Atau sebagai heading halaman --}}
<h1 class="text-2xl font-bold">{{ $title }}</h1>
```

Property `$title` juga digunakan secara internal oleh `dispatch_to_view()` untuk pesan notifikasi, misalnya: *"User berhasil ditambahkan!"*.

Breadcrumbs, `setTitle()`, dan page title bersifat **opsional** — halaman tetap berfungsi normal tanpa ketiganya.

### Dependensi Opsional

```bash
# Untuk fitur export Excel
composer require maatwebsite/excel

# Untuk fitur export PDF
composer require barryvdh/laravel-dompdf
```

---

## Quick Start

### 1. Buat Page Component (CRUD Logic)

```php
<?php

namespace App\Livewire\Admin\User;

use App\Models\User;
use MrCatz\DataTable\MrCatzComponent;
use Illuminate\Support\Facades\Auth;
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
        $this->validate([
            'name' => 'required|max:255',
            'email' => 'required|email',
        ]);

        if ($this->isEdit) {
            $user = User::find($this->id);
            $user->update(['name' => $this->name, 'email' => $this->email]);
            $this->dispatch_to_view(true, 'update');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $this->dispatch_to_view($user, 'insert');
        }
    }

    public function dropData()
    {
        $user = User::find($this->id);
        $delete = $user->delete();
        $this->dispatch_to_view($delete, 'delete');
    }

    public function dropBulkData($selectedRows)
    {
        $count = User::whereIn('id', $selectedRows)->delete();
        $this->dispatch('refresh-data', [
            'status' => true,
            'text' => $count . ' user berhasil dihapus!'
        ]);
    }
}
```

### 2. Buat Table Component (DataTable Config)

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
                    'Diperbarui' => 'updated_at',
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

> **Note:** `getActionView()` dan `getExpandView()` adalah static helper yang me-render blade view. Pola penggunaannya sama — return di dalam callback `withCustomColumn()` atau `enableExpand()`.

### 3. Buat Blade View

```blade
{{-- resources/views/livewire/admin/user/user-page.blade.php --}}
<div>
    <livewire:admin.user.user-table />

    @include('livewire.admin.user.user-form')
    @include('mrcatz::components.ui.datatable-scripts')
</div>
```

```blade
{{-- resources/views/livewire/admin/user/user-form.blade.php --}}
@extends('mrcatz::components.ui.datatable-form')

@section('form-body')
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
```

---

## Fitur Lengkap

### Kolom

```php
public function setTable()
{
    return $this->CreateMrCatzTable()
        // Kolom nomor urut otomatis
        ->withColumnIndex('No')

        // Kolom data biasa
        ->withColumn('Nama', 'name')

        // Kolom dengan opsi: uppercase, th, sortable, gravity (left/center/right)
        ->withColumn('Email', 'email', uppercase: false, th: false, sort: true, gravity: 'left')

        // Kolom custom dengan callback (return HTML)
        ->withCustomColumn('Status', function ($data, $i) {
            $color = $data->active ? 'badge-success' : 'badge-error';
            return '<span class="badge ' . $color . ' badge-sm">' . ($data->active ? 'Aktif' : 'Nonaktif') . '</span>';
        }, 'active', false)  // parameter: key (untuk search), sortable

        // Kolom aksi (edit/delete)
        ->withCustomColumn('Aksi', function ($data, $i) {
            return MrCatzDataTables::getActionView($data, $i, editable: true, deletable: true);
        });
}
```

### Filter

Data option filter bisa berupa **array manual** atau **query builder** dari database:

```php
public function setFilter()
{
    // ──────────────────────────────────────────────
    // Contoh 1: Data option dari array manual
    // ──────────────────────────────────────────────
    $roles = [
        ['value' => 'admin', 'label' => 'Admin'],
        ['value' => 'user', 'label' => 'User'],
    ];

    $roleFilter = MrCatzDataTableFilter::create(
        'filter_role',    // id unik
        'Role',           // label
        $roles,           // data array
        'value',          // key untuk value option
        'label',          // key untuk label option
        'role',           // kolom database
        true,             // show (default true)
        '='               // condition (default '=')
    )->get();

    // ──────────────────────────────────────────────
    // Contoh 2: Data option dari query builder
    // ──────────────────────────────────────────────
    $categories = DB::table('categories')
        ->select('id as value', 'name as label')
        ->orderBy('name')
        ->get()
        ->toArray();

    // convert stdClass ke array
    $categories = json_decode(json_encode($categories), true);

    $categoryFilter = MrCatzDataTableFilter::create(
        'filter_category',
        'Kategori',
        $categories,
        'value',
        'label',
        'category_id'
    )->get();

    // ──────────────────────────────────────────────
    // Contoh 3: Filter dengan callback (logic custom)
    // ──────────────────────────────────────────────
    $dateFilter = MrCatzDataTableFilter::createWithCallback(
        'filter_date',
        'Tanggal',
        [
            ['value' => 'today', 'label' => 'Hari Ini'],
            ['value' => 'week', 'label' => 'Minggu Ini'],
            ['value' => 'month', 'label' => 'Bulan Ini'],
        ],
        'value',
        'label',
        function ($query, $value) {
            return match ($value) {
                'today' => $query->whereDate('created_at', today()),
                'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                default => $query,
            };
        }
    )->get();

    return [$roleFilter, $categoryFilter, $dateFilter];
}
```

### Relevance Search

Aktifkan relevance scoring dengan `configTable()`:

```php
public function configTable()
{
    return [
        'table_name' => 'users',   // nama tabel database
        'table_id' => 'id',        // primary key
    ];
}
```

Saat user mencari "Ryan admin", hasil yang mengandung kedua keyword akan muncul di atas.

### Export (Excel & PDF)

Fitur export otomatis tersedia. Kontrol via property:

```php
public $showExportButton = true;   // tampilkan tombol export
public $exportTitle = 'Data User'; // judul file export
```

User bisa memilih:
- **Format**: Excel (.xlsx) atau PDF (.pdf)
- **Scope**: Semua data atau sesuai filter aktif
- Preview jumlah data sebelum export

### URL Persistence

Search, sort, filter, dan rows per page otomatis tersimpan di URL:

```
/admin/users?search=ryan&sort=name&dir=asc&per_page=20&filter[filter_role]=admin&page=2
```

User bisa copy-paste URL, bookmark, atau share — state ter-restore otomatis.

### Filter Presets

User bisa menyimpan kombinasi filter sebagai preset (disimpan di localStorage):

1. Setup filter + search yang diinginkan
2. Klik tombol **bookmarks** di toolbar
3. Ketik nama preset → simpan
4. Klik nama preset untuk load kembali

### Bulk Actions

Aktifkan bulk select dan bulk delete:

```php
// Di Table Component
public $bulkPrimaryKey = 'id';    // null = off, 'id' = on
public $showBulkButton = true;     // tampilkan tombol toggle "Pilih"
```

Kontrol per baris mana yang bisa di-select:

```php
public function setTable()
{
    return $this->CreateMrCatzTable()
        ->enableBulk(function ($data, $i) {
            // Contoh: tidak bisa select akun sendiri
            return Auth::id() !== $data->id;
        })
        ->withColumnIndex('No')
        // ...
}
```

Implementasi bulk delete di **Page Component**:

```php
// Di Page Component
public function dropBulkData($selectedRows)
{
    if (empty($selectedRows)) return;

    // Validasi...

    $count = User::whereIn('id', $selectedRows)->delete();

    $this->dispatch('refresh-data', [
        'status' => true,
        'text' => $count . ' user berhasil dihapus!'
    ]);
}
```

### Keyboard Navigation

Aktif secara default (`$enableKeyboardNav = true`):

| Key | Aksi |
|---|---|
| `Arrow Up/Down` | Navigasi antar baris |
| `Enter` | Buka modal edit |
| `Delete` / `Backspace` | Buka modal hapus |
| `Escape` | Batalkan fokus |

Hint shortcut otomatis tampil di bawah tabel.

### Column Resize

Aktif secara default (`$enableColumnResize = true`). Hover di border kanan header kolom untuk melihat resize handle, lalu drag untuk resize.

### Column Reorder (Drag & Drop)

Drag header kolom ke posisi baru. Urutan tersimpan selama session Livewire aktif.

### Expandable Rows

Tampilkan detail tambahan tanpa membuka modal:

```php
// Di Table Component
public $expandableRows = true;

public function setTable()
{
    return $this->CreateMrCatzTable()
        ->enableExpand(function ($data, $i) {
            return MrCatzDataTables::getExpandView($data, [
                'Email' => 'email',
                'Username' => 'username',
                'Dibuat' => 'created_at',
                'Diperbarui' => 'updated_at',
            ]);
        })
        ->withColumnIndex('No')
        // ...
}
```

`getExpandView()` menerima associative array `['Label' => 'database_column']` dan render dalam grid responsive.

Untuk custom expand content, return HTML string langsung:

```php
->enableExpand(function ($data, $i) {
    return view('partials.user-detail', ['user' => $data])->render();
})
```

### Loading Overlay

```php
public $withLoading = true;  // tampilkan fullscreen loading overlay
```

### Notifikasi

Dari Page Component:

```php
// Notifikasi success/error/warning
$this->show_notif('error', 'Terjadi kesalahan!');

// Notifikasi setelah CRUD (otomatis refresh tabel)
$this->dispatch_to_view($success, 'insert');  // 'insert', 'update', 'delete'
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
| `$typeSearch` | bool | `false` | Search realtime (tanpa tekan Enter) |
| `$typeSearchWithDelay` | bool | `false` | Search realtime dengan delay |
| `$typeSearchDelay` | string | `'500ms'` | Delay search realtime |
| `$bulkPrimaryKey` | string\|null | `null` | Primary key untuk bulk select, null = off |
| `$showBulkButton` | bool | `false` | Tombol toggle bulk select |
| `$enableKeyboardNav` | bool | `true` | Keyboard navigation |
| `$enableColumnResize` | bool | `true` | Column resize |
| `$enableColumnReorder` | bool | `true` | Column reorder (drag & drop header) |
| `$enableColumnSorting` | bool | `true` | Column sorting (klik header untuk sort) |
| `$expandableRows` | bool | `false` | Expandable row detail |

---

## Method Reference

### MrCatzComponent (Page) — Override Methods

| Method | Keterangan |
|---|---|
| `prepareAddData()` | Dipanggil saat tombol tambah diklik |
| `prepareEditData($data)` | Dipanggil saat tombol edit diklik |
| `prepareDeleteData($data)` | Dipanggil saat tombol delete diklik |
| `saveData()` | Dipanggil saat form submit |
| `dropData()` | Dipanggil saat konfirmasi delete |
| `dropBulkData($selectedRows)` | Dipanggil saat bulk delete dikonfirmasi |

### MrCatzDataTablesComponent (Table) — Override Methods

| Method | Keterangan |
|---|---|
| `baseQuery()` | Return query builder (DB::table atau Model::query) |
| `setTable()` | Return MrCatzDataTables instance dengan kolom |
| `configTable()` | Return `['table_name' => '...', 'table_id' => '...']` untuk relevance search |
| `setFilter()` | Return array MrCatzDataTableFilter |
| `getRowPerPageOption()` | Return array opsi rows per page, misal `[10, 15, 20]` |
| `setView()` | Override untuk custom blade view |
| `setPageName()` | Override jika ada multiple paginator di satu halaman |
| `onDataLoaded($builder, $data)` | Hook setelah data di-load |

### MrCatzDataTables (Engine) — Fluent API

| Method | Keterangan |
|---|---|
| `withColumnIndex($head)` | Kolom nomor urut |
| `withColumn($head, $key, ...)` | Kolom data |
| `withCustomColumn($head, $callback, ...)` | Kolom custom HTML |
| `enableBulk($callback)` | Aktifkan bulk select per baris |
| `enableExpand($callback)` | Aktifkan expandable row |
| `setDefaultOrder($key, $dir)` | Default sorting |
| `addOrderBy($key, $dir)` | Tambahan ordering |
| `getActionView($data, $i, ...)` | Static: render tombol aksi |
| `getExpandView($data, $fields)` | Static: render expand content |

---

## Requirements

- PHP >= 8.1
- Laravel >= 11.0
- Livewire >= 3.0
- Tailwind CSS
- DaisyUI
- Material Icons (Google Fonts)

## License

MIT
