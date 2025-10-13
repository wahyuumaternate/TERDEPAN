<?php

return [
    'name' => 'PerjanjianKinerja',

    /*
    |--------------------------------------------------------------------------
    | Default Template Settings
    |--------------------------------------------------------------------------
    */
    'template' => [
        'default_page_size' => 'A4',
        'default_orientation' => 'Portrait',
        'default_tempat_ttd' => 'Sofifi',
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Generation Settings
    |--------------------------------------------------------------------------
    */
    'dokumen' => [
        'storage_path' => 'perjanjian-kinerja',
        'allowed_versions' => 10, // Maximum versions to keep
        'auto_lock_on_sign' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Anggaran Settings
    |--------------------------------------------------------------------------
    */
    'anggaran' => [
        'auto_calculate' => true,
        'currency_format' => 'IDR',
        'decimal_places' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'min_sasaran' => 1,
        'max_sasaran' => 10,
        'min_indikator_per_sasaran' => 1,
        'max_program_per_indikator' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Dokumen
    |--------------------------------------------------------------------------
    */
    'status' => [
        'draft' => 'Draft',
        'generated' => 'Generated',
        'menunggu_ttd' => 'Menunggu_TTD',
        'aktif' => 'Aktif',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Jenis Dokumen
    |--------------------------------------------------------------------------
    */
    'jenis_dokumen' => [
        'pernyataan' => 'Pernyataan',
        'formulir' => 'Formulir',
        'lampiran' => 'Lampiran',
    ],
];
