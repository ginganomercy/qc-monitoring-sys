<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Filament Navigation Labels
    |--------------------------------------------------------------------------
    */
    'navigation' => [
        'groups' => [
            'master_data' => 'Data Master',
            'qc_management' => 'Manajemen QC',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Labels
    |--------------------------------------------------------------------------
    */
    'resources' => [
        'inspection' => [
            'label' => 'Inspeksi',
            'plural_label' => 'Inspeksi',
            'navigation_label' => 'Inspeksi',
        ],
        'product' => [
            'label' => 'Produk',
            'plural_label' => 'Produk',
            'navigation_label' => 'Produk',
        ],
        'line' => [
            'label' => 'Line Produksi',
            'plural_label' => 'Line Produksi',
            'navigation_label' => 'Line Produksi',
        ],
        'defect_type' => [
            'label' => 'Jenis Defect',
            'plural_label' => 'Jenis Defect',
            'navigation_label' => 'Jenis Defect',
        ],
        'component' => [
            'label' => 'Komponen',
            'plural_label' => 'Komponen',
            'navigation_label' => 'Komponen',
        ],
        'daily_target' => [
            'label' => 'Target Harian',
            'plural_label' => 'Target Harian',
            'navigation_label' => 'Target Harian',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Form Section Labels
    |--------------------------------------------------------------------------
    */
    'sections' => [
        'inspection_details' => 'Detail Inspeksi',
        'defect_information' => 'Informasi Defect',
        'product_information' => 'Informasi Produk',
        'line_information' => 'Informasi Line',
        'target_information' => 'Informasi Target',
    ],

    /*
    |--------------------------------------------------------------------------
    | Form Field Labels
    |--------------------------------------------------------------------------
    */
    'fields' => [
        // Common fields
        'created_at' => 'Dibuat Pada',
        'updated_at' => 'Diperbarui Pada',
        'is_active' => 'Aktif',

        // Inspection fields
        'inspection_date' => 'Tanggal Inspeksi',
        'product' => 'Produk',
        'line' => 'Line',
        'status' => 'Status',
        'pass' => 'Lolos',
        'reject' => 'Ditolak',
        'inspector' => 'Inspector',
        'defect_type' => 'Jenis Defect',
        'component' => 'Komponen',
        'notes' => 'Catatan',

        // Product fields
        'style_number' => 'Nomor Style',
        'description' => 'Deskripsi',

        // Line fields
        'code' => 'Kode',
        'name' => 'Nama',

        // Defect Type fields
        'severity' => 'Tingkat Keparahan',
        'severity_low' => 'Rendah',
        'severity_medium' => 'Sedang',
        'severity_high' => 'Tinggi',
        'severity_critical' => 'Kritis',

        // Daily Target fields
        'target_date' => 'Tanggal Target',
        'target_quantity' => 'Jumlah Target',
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Column Labels
    |--------------------------------------------------------------------------
    */
    'columns' => [
        'inspections' => 'Jumlah Inspeksi',
        'inspections_count' => 'Inspeksi',
        'daily_targets' => 'Target Harian',
        'daily_targets_count' => 'Jumlah Target',
        'product_style' => 'Style Produk',
        'line_code' => 'Kode Line',
        'defect' => 'Defect',
        'date' => 'Tanggal',
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Labels
    |--------------------------------------------------------------------------
    */
    'actions' => [
        'create' => 'Buat',
        'edit' => 'Edit',
        'view' => 'Lihat',
        'delete' => 'Hapus',
        'save' => 'Simpan',
        'cancel' => 'Batal',
        'create_new' => 'Buat Baru',
        'create_another' => 'Buat & Buat Lagi',
    ],

    /*
    |--------------------------------------------------------------------------
    | Filter Labels
    |--------------------------------------------------------------------------
    */
    'filters' => [
        'active' => 'Status Aktif',
        'only_active' => 'Hanya Aktif',
        'only_inactive' => 'Hanya Tidak Aktif',
        'select_status' => 'Pilih Status',
        'select_product' => 'Pilih Produk',
        'select_line' => 'Pilih Line',
        'from' => 'Dari',
        'until' => 'Sampai',
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Labels
    |--------------------------------------------------------------------------
    */
    'widgets' => [
        'inspections_today' => 'Inspeksi Hari Ini',
        'passed' => 'Lolos',
        'rejected' => 'Ditolak',
        'pass_rate' => 'Tingkat Kelulusan',
        'top_defects' => 'Top 5 Defect (7 Hari Terakhir)',
        'inspections_chart' => 'Inspeksi (7 Hari Terakhir)',
        'recent_inspections' => 'Inspeksi Terbaru',
        'defects_count' => 'Jumlah Defect',
    ],

    /*
    |--------------------------------------------------------------------------
    | Messages & Notifications
    |--------------------------------------------------------------------------
    */
    'messages' => [
        'created' => 'Data berhasil dibuat.',
        'updated' => 'Data berhasil diperbarui.',
        'deleted' => 'Data berhasil dihapus.',
        'saved' => 'Perubahan berhasil disimpan.',
        'error' => 'Terjadi kesalahan. Silakan coba lagi.',
        'no_records' => 'Tidak ada data.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Placeholders
    |--------------------------------------------------------------------------
    */
    'placeholders' => [
        'search' => 'Cari...',
        'select' => 'Pilih...',
        'no_data' => 'Tidak ada data',
        'empty' => '—',
    ],
];
