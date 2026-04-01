<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    |
    | Set the language for all MrCatz DataTable UI strings.
    | Supported: 'en', 'id'
    |
    */
    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | English Strings
    |--------------------------------------------------------------------------
    */
    'en' => [
        // Buttons
        'btn_add' => 'Add',
        'btn_save' => 'Save',
        'btn_cancel' => 'Cancel',
        'btn_delete' => 'Delete',
        'btn_reset' => 'Reset',
        'btn_export' => 'Export',
        'btn_select' => 'Select',
        'btn_yes_reset' => 'Yes, Reset',
        'btn_yes_delete' => 'Yes, Delete',

        // CRUD
        'default_form_title' => 'Add Data',
        'added' => 'added!',
        'updated' => 'updated!',
        'deleted' => 'deleted!',
        'success' => 'successfully',
        'failed' => 'failed to process!',

        // Search & Filter
        'search_placeholder' => 'Search...',
        'filter_all' => 'All',
        'filter_active' => 'filter active',
        'filter_preset' => 'Filter Preset',
        'filter_no_preset' => 'No presets saved',
        'filter_preset_placeholder' => 'Preset name...',
        'filter_no_available' => 'No filters available for this table',

        // Empty states
        'no_results_for' => "No results found for ':query'",
        'no_results' => 'No results found',
        'no_results_hint' => 'Try changing keywords or search filters',
        'no_results_filter_hint' => 'Try changing active filters',
        'no_data' => 'No data yet',
        'no_data_hint' => 'Data will appear here after being added',

        // Pagination
        'rows_per_page' => 'Rows per page',
        'of' => 'of',

        // Bulk
        'data_selected' => 'data selected',

        // Export modal
        'export_title' => 'Export Data',
        'export_format' => 'File Format',
        'export_scope' => 'Data Scope',
        'export_all' => 'All Data',
        'export_all_desc' => 'Without filter',
        'export_filtered' => 'Filtered Data',
        'export_filtered_desc' => 'With filter',
        'export_settings' => 'Export Filter Settings',
        'export_search' => 'Search',
        'export_search_placeholder' => 'Search keyword...',
        'export_count' => 'data will be exported',

        // Reset modal
        'reset_title' => 'Reset Search & Filter?',
        'reset_desc' => 'All search keywords and active filters will be removed.',

        // Bulk delete modal
        'bulk_delete_title' => 'Delete Selected Data?',
        'bulk_delete_desc' => 'Selected data will be permanently deleted.',

        // Delete modal
        'confirm_delete' => 'Confirm Delete',
        'confirm_delete_text' => 'Are you sure you want to delete:',

        // Keyboard hints
        'key_navigate' => 'navigate',
        'key_edit' => 'edit',
        'key_delete' => 'delete',
        'key_cancel' => 'cancel',

        // Tooltips
        'tooltip_edit' => 'Edit',
        'tooltip_delete' => 'Delete',

        // Loading
        'loading' => 'Loading data...',
        'processing' => 'Processing...',
    ],

    /*
    |--------------------------------------------------------------------------
    | Indonesian Strings
    |--------------------------------------------------------------------------
    */
    'id' => [
        // Buttons
        'btn_add' => 'Tambahkan',
        'btn_save' => 'Simpan',
        'btn_cancel' => 'Batal',
        'btn_delete' => 'Hapus',
        'btn_reset' => 'Reset',
        'btn_export' => 'Export',
        'btn_select' => 'Pilih',
        'btn_yes_reset' => 'Ya, Reset',
        'btn_yes_delete' => 'Ya, Hapus',

        // CRUD
        'default_form_title' => 'Tambah Data',
        'added' => 'ditambahkan!',
        'updated' => 'diupdate!',
        'deleted' => 'dihapus!',
        'success' => 'berhasil',
        'failed' => 'gagal diproses!',

        // Search & Filter
        'search_placeholder' => 'Cari data...',
        'filter_all' => 'Semua',
        'filter_active' => 'filter aktif',
        'filter_preset' => 'Filter Preset',
        'filter_no_preset' => 'Belum ada preset tersimpan',
        'filter_preset_placeholder' => 'Nama preset...',
        'filter_no_available' => 'Tidak ada filter tersedia untuk tabel ini',

        // Empty states
        'no_results_for' => "Tidak ada hasil ditemukan untuk pencarian ':query'",
        'no_results' => 'Tidak ada hasil ditemukan',
        'no_results_hint' => 'Coba ubah kata kunci atau filter pencarian',
        'no_results_filter_hint' => 'Coba ubah filter yang sedang aktif',
        'no_data' => 'Belum ada data',
        'no_data_hint' => 'Data akan muncul di sini setelah ditambahkan',

        // Pagination
        'rows_per_page' => 'Baris per halaman',
        'of' => 'dari',

        // Bulk
        'data_selected' => 'data dipilih',

        // Export modal
        'export_title' => 'Export Data',
        'export_format' => 'Format File',
        'export_scope' => 'Cakupan Data',
        'export_all' => 'Semua Data',
        'export_all_desc' => 'Tanpa filter',
        'export_filtered' => 'Sesuai Filter',
        'export_filtered_desc' => 'Dengan filter',
        'export_settings' => 'Atur Filter Export',
        'export_search' => 'Pencarian',
        'export_search_placeholder' => 'Kata kunci pencarian...',
        'export_count' => 'data akan di-export',

        // Reset modal
        'reset_title' => 'Reset Pencarian & Filter?',
        'reset_desc' => 'Semua kata kunci pencarian dan filter yang aktif akan dihapus.',

        // Bulk delete modal
        'bulk_delete_title' => 'Hapus Data Terpilih?',
        'bulk_delete_desc' => 'Data yang dipilih akan dihapus secara permanen.',

        // Delete modal
        'confirm_delete' => 'Konfirmasi Hapus',
        'confirm_delete_text' => 'Apakah Anda yakin ingin menghapus data:',

        // Keyboard hints
        'key_navigate' => 'navigasi',
        'key_edit' => 'edit',
        'key_delete' => 'hapus',
        'key_cancel' => 'batal',

        // Tooltips
        'tooltip_edit' => 'Edit',
        'tooltip_delete' => 'Hapus',

        // Loading
        'loading' => 'Memuat data...',
        'processing' => 'Memproses...',
    ],

];
