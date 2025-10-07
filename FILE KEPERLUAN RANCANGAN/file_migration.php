<?php

// File: database/migrations/2024_10_01_000001_create_terdepan_system_tables.php
// Migration lengkap untuk sistem TERDEPAN
// Berisi semua tabel untuk subsistem:
// - Master Data (shared)
// - Document Management System
// - Performance Management System

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ==============================================================
        // 1. MASTER DATA (SHARED TABLES)
        // ==============================================================

        // Jabatan
        Schema::create('master_jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique()->comment('Kode jabatan: KB,SB,KBD,STF,JF,TT');
            $table->string('nama', 100)->comment('Nama lengkap: Kaban, Sekban, Kabid, Staff, Jabatan Fungsional, Tenaga Teknis');
            $table->integer('level')->comment('Level hierarki 1-6 untuk organizational structure');
            $table->boolean('is_struktural')->default(false)->comment('TRUE jika jabatan struktural, FALSE untuk fungsional');
            $table->boolean('bebas_nilai_kinerja')->default(false)->comment('TRUE untuk Tenaga Teknis yang tidak dinilai');
            $table->boolean('is_active')->default(true)->comment('Status aktif, untuk soft disable');
            $table->timestamps();
            
            $table->index('level');
        });

        // Bidang
        Schema::create('master_bidang', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique()->comment('Kode unik: PLAN, EVAL, DATA, SEKRET');
            $table->string('nama', 100)->comment('Nama lengkap bidang untuk display');
            $table->text('deskripsi')->nullable()->comment('Deskripsi tugas dan fungsi bidang');
            $table->string('warna', 7)->nullable()->comment('Hex color untuk UI');
            $table->boolean('is_active')->default(true)->comment('TRUE=aktif, FALSE=tidak bisa assign pegawai baru');
            $table->timestamps();
            $table->softDeletes();
        });

        // Pegawai
        Schema::create('master_pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_identitas', 18)->unique()->comment('NIP 18 digit atau NIK 16 digit, untuk login');
            $table->enum('tipe_identitas', ['NIP', 'NIK'])->comment('NIP atau NIK');
            $table->string('nama', 100)->comment('Nama lengkap tanpa gelar');
            $table->foreignId('jabatan_id')->constrained('master_jabatan');
            $table->foreignId('bidang_id')->constrained('master_bidang');
            $table->string('email')->unique()->comment('Email unik untuk notifikasi');
            $table->string('password')->comment('Hashed password bcrypt/argon2');
            $table->string('no_telepon', 20)->nullable()->comment('Nomor telepon untuk SMS/WhatsApp');
            $table->string('pangkat', 50)->nullable()->comment('Pangkat PNS: Penata, Pembina, dll');
            $table->string('golongan', 10)->nullable()->comment('Golongan: III/a, III/b, IV/a, dll');
            $table->string('gelar_depan', 20)->nullable()->comment('Dr., Ir., Drs., dll');
            $table->string('gelar_belakang', 20)->nullable()->comment('S.T., M.T., M.Si., dll');
            $table->date('tanggal_lahir')->nullable()->comment('Untuk perhitungan usia dan pensiun');
            $table->enum('jenis_kelamin', ['L', 'P'])->comment('L atau P');
            $table->text('alamat')->nullable()->comment('Alamat lengkap');
            $table->enum('status_kepegawaian', ['PNS', 'PPPK', 'Kontrak'])->comment('PNS, PPPK, Kontrak');
            $table->enum('status_aktif', ['Aktif', 'Nonaktif', 'Cuti', 'Pensiun'])->default('Aktif')->comment('Aktif, Nonaktif, Cuti, Pensiun');
            $table->date('tanggal_masuk')->nullable()->comment('Tanggal mulai bekerja');
            $table->date('tanggal_keluar')->nullable()->comment('Tanggal berhenti, NULL jika masih aktif');
            $table->foreignId('atasan_langsung_id')->nullable()->constrained('master_pegawai')->comment('Self-reference untuk hierarchy');
            $table->string('foto_profile_path')->nullable()->comment('Path file foto profil');
            $table->timestamp('last_login_at')->nullable()->comment('Timestamp login terakhir untuk audit');
            $table->string('last_login_ip', 45)->nullable()->comment('IP address login terakhir');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('bidang_id');
            $table->index('jabatan_id');
            $table->index('status_aktif');
            $table->index('atasan_langsung_id');
        });

        // Tanda Tangan Digital
        Schema::create('master_ttd_digital', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai');
            $table->string('file_path')->comment('Path file gambar TTD PNG transparent');
            $table->string('file_hash')->comment('SHA256 hash untuk integrity check');
            $table->integer('image_width')->nullable()->comment('Lebar gambar dalam pixel');
            $table->integer('image_height')->nullable()->comment('Tinggi gambar dalam pixel');
            $table->date('valid_from')->comment('Tanggal mulai berlaku');
            $table->date('valid_until')->nullable()->comment('Tanggal expired, NULL=permanent');
            $table->boolean('is_active')->default(false)->comment('TRUE=aktif, hanya 1 aktif per pegawai');
            $table->boolean('is_verified')->default(false)->comment('TRUE jika sudah diverifikasi admin');
            $table->timestamps();
            $table->softDeletes();
            
            // Hanya satu TTD aktif per pegawai
            $table->unique(['pegawai_id', 'is_active'], 'uq_pegawai_active_ttd');
        });

        // ==============================================================
        // 2. DOCUMENT MANAGEMENT SYSTEM
        // ==============================================================

        // Folder Structure
        Schema::create('doc_folder', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('doc_folder')->comment('Parent folder, NULL=root');
            $table->foreignId('bidang_id')->nullable()->constrained('master_bidang')->comment('Milik bidang, NULL=shared');
            $table->string('nama', 100)->comment('Nama folder');
            $table->string('path')->unique()->comment('Full path: /PLAN/Surat_Masuk/2024/');
            $table->tinyInteger('level')->default(0)->comment('Level: 0=root, 1=category, 2=year, 3=month');
            $table->boolean('is_auto')->default(false)->comment('TRUE=auto-created (Year/Month folder)');
            $table->integer('total_files')->default(0)->comment('Counter file dalam folder');
            $table->foreignId('created_by')->constrained('master_pegawai')->comment('User yang buat');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('bidang_id');
            $table->index('parent_id');
            $table->index('level');
        });

        // Document Category
        Schema::create('doc_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->comment('Surat, Laporan, Spasial, Legal, Kinerja');
            $table->string('icon', 30)->nullable()->comment('Icon untuk UI');
            $table->string('warna', 7)->nullable()->comment('Hex color');
            $table->tinyInteger('urutan')->default(0)->comment('Sort order');
            $table->timestamp('created_at')->useCurrent();
        });

        // Document Type
        Schema::create('doc_jenis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('doc_kategori');
            $table->string('kode', 10)->unique()->comment('SM, SK, SHP, LAP, PK');
            $table->string('nama', 50)->comment('Surat Masuk, Surat Keluar, Shapefile, Laporan, Perjanjian Kinerja');
            $table->string('folder_pattern')->comment('/{bidang}/{jenis}/{year}/{month}/');
            $table->string('nomor_format')->comment('SM/BAPPEDA/{bidang}/{year}/{seq}');
            $table->string('allowed_ext')->comment('pdf,docx,xlsx atau shp,shx,dbf');
            $table->integer('max_size_mb')->default(10)->comment('Max ukuran file MB');
            $table->boolean('perlu_nomor')->default(true)->comment('TRUE=auto generate nomor');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('kategori_id');
        });

        // Document Counter
        Schema::create('doc_nomor_counter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_id')->constrained('doc_jenis');
            $table->foreignId('bidang_id')->constrained('master_bidang');
            $table->year('tahun')->comment('Tahun');
            $table->integer('counter')->default(0)->comment('Current counter');
            $table->timestamp('updated_at')->nullable();
            
            $table->unique(['jenis_id', 'bidang_id', 'tahun'], 'uq_dokumen_counter');
        });

        // Document
        Schema::create('doc_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique()->comment('Nomor dokumen: SM/BAPPEDA/PLAN/2024/0001');
            $table->foreignId('folder_id')->constrained('doc_folder');
            $table->foreignId('jenis_id')->constrained('doc_jenis');
            $table->string('judul')->comment('Judul dokumen');
            $table->text('deskripsi')->nullable()->comment('Deskripsi');
            $table->date('tanggal_dokumen')->comment('Tanggal dokumen');
            $table->string('nomor_surat')->nullable()->comment('Nomor surat jika ada');
            $table->enum('status', ['Draft', 'Final', 'Archived'])->default('Draft')->comment('Draft, Final, Archived');
            $table->integer('version')->default(1)->comment('Current version');
            $table->integer('views')->default(0)->comment('Counter view');
            $table->integer('downloads')->default(0)->comment('Counter download');
            $table->foreignId('uploaded_by')->constrained('master_pegawai')->comment('User upload');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            
            $table->index('jenis_id');
            $table->index('folder_id');
            $table->index('uploaded_by');
            $table->index('tanggal_dokumen');
        });

        // Document File
        Schema::create('doc_file', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('doc_dokumen');
            $table->integer('version')->default(1)->comment('Version number 1,2,3');
            $table->string('nama_file')->comment('Nama file original');
            $table->string('file_path')->comment('Path: storage/docs/plan/sm/2024/10/file.pdf');
            $table->string('extension', 10)->comment('pdf, docx, xlsx, shp, dll');
            $table->integer('size_kb')->comment('Ukuran file KB');
            $table->string('hash')->comment('SHA256 hash');
            $table->text('keterangan')->nullable()->comment('Catatan versi');
            $table->boolean('is_current')->default(true)->comment('TRUE=versi aktif');
            $table->foreignId('uploaded_by')->constrained('master_pegawai')->comment('User yang upload');
            $table->timestamp('uploaded_at')->useCurrent();
            
            $table->index(['dokumen_id', 'version']);
            $table->index(['dokumen_id', 'is_current']);
        });

        // Document Metadata
        Schema::create('doc_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('doc_dokumen');
            $table->string('key', 50)->comment('Key metadata');
            $table->text('value')->comment('Value metadata');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['dokumen_id', 'key']);
        });

        // Document Activity Log
        Schema::create('doc_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('doc_dokumen');
            $table->foreignId('user_id')->constrained('master_pegawai')->comment('FK ke MASTER_PEGAWAI');
            $table->enum('action', ['View', 'Download', 'Upload', 'Edit', 'Delete'])->comment('View, Download, Upload, Edit, Delete');
            $table->string('ip_address', 45)->nullable()->comment('IP address');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['dokumen_id', 'action']);
            $table->index(['user_id', 'created_at']);
        });

        // ==============================================================
        // 3. PERJANJIAN KINERJA MODULE
        // ==============================================================

        // Template Perjanjian Kinerja
        Schema::create('pk_template', function (Blueprint $table) {
            $table->id();
            $table->string('kode_template')->unique()->comment('Kode unik: TPK-KB-2024, TPK-SB-2024');
            $table->string('nama_template')->comment('Nama deskriptif: PK Eselon II 2024');
            $table->foreignId('jabatan_id')->constrained('master_jabatan')->comment('Template untuk jabatan ini');
            $table->year('tahun')->comment('Tahun berlaku template');
            $table->text('kop_surat_html')->comment('HTML kop surat dengan logo');
            $table->text('header_template')->comment('Template header dokumen');
            $table->text('pernyataan_pembuka')->comment('Template pembuka perjanjian');
            $table->text('pernyataan_penutup')->comment('Template penutup');
            $table->text('footer_template')->comment('Template footer dengan area TTD');
            $table->enum('page_size', ['A4', 'Legal', 'Letter'])->default('A4');
            $table->enum('orientation', ['Portrait', 'Landscape'])->default('Portrait');
            $table->integer('versi')->default(1)->comment('Version number, increment untuk changes');
            $table->boolean('is_active')->default(true)->comment('TRUE=aktif, hanya 1 aktif per jabatan+tahun');
            $table->timestamps();
            
            $table->index(['jabatan_id', 'tahun', 'is_active']);
        });

        // Template Section
        Schema::create('pk_template_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('pk_template');
            $table->string('section_code', 30)->comment('Kode: SASARAN, INDIKATOR, PROGRAM');
            $table->string('section_name', 50)->comment('Nama section untuk display');
            $table->enum('section_type', ['static', 'dynamic', 'table', 'repeatable'])->default('static');
            $table->text('content_template')->comment('HTML template section');
            $table->integer('urutan')->comment('Urutan section dalam dokumen 1,2,3...');
            $table->boolean('is_required')->default(true)->comment('TRUE=wajib, FALSE=optional');
            $table->timestamps();
            
            $table->unique(['template_id', 'section_code']);
            $table->index(['template_id', 'urutan']);
        });

        // Perjanjian Kinerja
        Schema::create('pk_perjanjian_kinerja', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_perjanjian')->unique()->comment('Nomor unik dari counter: PK/BAPPEDA/2024/001');
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Pihak pertama pegawai');
            $table->foreignId('atasan_id')->constrained('master_pegawai')->comment('Pihak kedua atasan');
            $table->foreignId('template_id')->constrained('pk_template')->comment('Template yang digunakan');
            $table->year('tahun')->comment('Tahun perjanjian');
            $table->date('periode_mulai')->comment('Tanggal mulai biasanya 1 Jan');
            $table->date('periode_selesai')->comment('Tanggal akhir biasanya 31 Des');
            $table->string('tempat_ttd', 50)->default('Kuningan')->comment('Tempat TTD default Kuningan');
            $table->date('tanggal_ttd')->nullable()->comment('Tanggal TTD, NULL jika belum');
            $table->decimal('total_anggaran', 15, 2)->default(0)->comment('Total anggaran dari sum program, auto-calculate');
            $table->enum('status_dokumen', ['Draft', 'Generated', 'Menunggu_TTD', 'Aktif', 'Selesai', 'Dibatalkan'])->default('Draft');
            $table->text('catatan')->nullable()->comment('Catatan tambahan');
            $table->boolean('is_locked')->default(false)->comment('TRUE=tidak bisa edit setelah TTD');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['pegawai_id', 'tahun']);
            $table->index('atasan_id');
            $table->index('status_dokumen');
        });

        // Dokumen Perjanjian
        Schema::create('pk_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perjanjian_kinerja_id')->constrained('pk_perjanjian_kinerja');
            $table->enum('jenis_dokumen', ['Pernyataan', 'Formulir', 'Lampiran'])->default('Formulir');
            $table->string('nomor_dokumen')->comment('Sama dengan nomor_perjanjian');
            $table->string('file_name')->comment('Format: PK_NIP_2024_v1.pdf');
            $table->string('file_path')->comment('Full path: storage/perjanjian/2024/...');
            $table->string('file_hash')->comment('SHA256 untuk integrity check');
            $table->integer('file_size_kb')->comment('Ukuran file dalam KB');
            $table->integer('versi')->default(1)->comment('Version number, increment saat re-generate');
            $table->integer('total_pages')->default(1)->comment('Total halaman PDF');
            $table->foreignId('generated_by')->constrained('master_pegawai')->comment('User yang trigger generate');
            $table->timestamp('generated_at')->useCurrent()->comment('Waktu generate');
            $table->boolean('is_latest')->default(true)->comment('TRUE=versi terbaru');
            $table->foreignId('dokumen_id')->nullable()->constrained('doc_dokumen')->comment('Foreign key ke DOC_DOKUMEN untuk integrasi');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['perjanjian_kinerja_id', 'is_latest']);
            $table->index('versi');
            $table->index('dokumen_id');
        });

        // Sasaran Strategis
        Schema::create('pk_sasaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perjanjian_kinerja_id')->constrained('pk_perjanjian_kinerja');
            $table->integer('urutan')->comment('Urutan sasaran: 1, 2, 3...');
            $table->text('sasaran_strategis')->comment('Deskripsi sasaran strategis');
            $table->timestamps();
            
            $table->unique(['perjanjian_kinerja_id', 'urutan']);
        });

        // Indikator Kinerja
        Schema::create('pk_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sasaran_id')->constrained('pk_sasaran');
            $table->text('indikator_sasaran')->comment('Deskripsi indikator');
            $table->string('satuan', 30)->comment('Satuan: persen, dokumen, kegiatan');
            $table->decimal('target_value', 10, 2)->comment('Nilai target yang harus dicapai');
            $table->text('keterangan')->nullable()->comment('Keterangan tambahan');
            $table->timestamps();
            
            $table->index('sasaran_id');
        });

        // Program
        Schema::create('pk_program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('pk_indikator');
            $table->integer('urutan')->comment('Urutan program');
            $table->string('kode_program', 30)->comment('Kode dari SIPD/e-budgeting');
            $table->string('nama_program')->comment('Nama lengkap program');
            $table->decimal('anggaran', 15, 2)->default(0)->comment('Anggaran dalam rupiah');
            $table->timestamps();
            
            $table->index('indikator_id');
        });

        // Kegiatan
        Schema::create('pk_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('pk_program');
            $table->integer('urutan')->comment('Urutan kegiatan');
            $table->string('kode_kegiatan', 30)->unique()->comment('Kode kegiatan unik');
            $table->string('nama_kegiatan')->comment('Nama lengkap kegiatan');
            $table->decimal('anggaran', 15, 2)->default(0)->comment('Anggaran kegiatan');
            $table->timestamps();
            
            $table->index('program_id');
        });

        // Sub Kegiatan
        Schema::create('pk_sub_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('pk_kegiatan');
            $table->integer('urutan')->comment('Urutan sub kegiatan');
            $table->string('kode_sub_kegiatan', 30)->unique()->comment('Kode sub kegiatan unik');
            $table->string('nama_sub_kegiatan')->comment('Nama lengkap sub kegiatan');
            $table->decimal('anggaran', 15, 2)->default(0)->comment('Anggaran sub kegiatan');
            $table->integer('target_value')->comment('Target output/outcome');
            $table->string('satuan', 30)->comment('Satuan: Dokumen, Kegiatan, Orang');
            $table->timestamps();
            
            $table->index('kegiatan_id');
        });

        // ==============================================================
        // 4. MANAJEMEN KINERJA MODULE
        // ==============================================================

        // Tugas Pokok
        Schema::create('knj_tugas_pokok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perjanjian_kinerja_id')->constrained('pk_perjanjian_kinerja')->comment('Source dari PK');
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Penerima tugas');
            $table->foreignId('pemberi_tugas_id')->constrained('master_pegawai')->comment('Atasan Kaban/Kabid');
            $table->string('nama_tugas')->comment('Nama/judul tugas');
            $table->text('deskripsi')->comment('Deskripsi lengkap tugas');
            $table->decimal('bobot_persen', 5, 2)->comment('Bobot 60-70%, CHECK >=60 AND <=70');
            $table->date('periode_mulai')->comment('Tanggal mulai');
            $table->date('periode_selesai')->comment('Tanggal target selesai');
            $table->decimal('target_value', 10, 2)->comment('Target yang harus dicapai');
            $table->string('satuan', 30)->comment('Satuan target');
            $table->enum('status', ['Pending', 'Diterima', 'Dikerjakan', 'Selesai', 'Tidak_Selesai', 'Divalidasi'])->default('Pending');
            $table->decimal('progress_persen', 5, 2)->default(0)->comment('Progress 0-100%, auto-calc');
            $table->timestamp('diterima_at')->nullable()->comment('Waktu pegawai terima');
            $table->foreignId('dokumen_lampiran_id')->nullable()->constrained('doc_dokumen')->comment('Foreign key ke DOC_DOKUMEN');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['pegawai_id', 'status']);
            $table->index('perjanjian_kinerja_id');
            $table->index('pemberi_tugas_id');
            $table->index('periode_selesai');

            // Check constraint for bobot_persen >= 60 and <= 70 akan diimplementasikan di level aplikasi
        });

        // Indikator Tugas Pokok
        Schema::create('knj_indikator_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_pokok_id')->constrained('knj_tugas_pokok');
            $table->string('nama_indikator')->comment('Nama indikator');
            $table->string('satuan', 30)->comment('Satuan pengukuran');
            $table->decimal('target', 10, 2)->comment('Target value');
            $table->decimal('realisasi', 10, 2)->default(0)->comment('Realisasi actual, update dari progress');
            $table->timestamps();
            
            $table->index('tugas_pokok_id');
        });

        // Tugas Harian
        Schema::create('knj_tugas_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_pokok_id')->constrained('knj_tugas_pokok')->comment('Wajib terkait KNJ_TUGAS_POKOK');
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Penerima tugas');
            $table->foreignId('pemberi_tugas_id')->constrained('master_pegawai')->comment('Atasan yang assign');
            $table->string('nama_tugas')->comment('Nama tugas');
            $table->text('deskripsi')->comment('Deskripsi tugas');
            $table->enum('periode_type', ['Harian', 'Mingguan'])->default('Harian');
            $table->date('tanggal_mulai')->comment('Tanggal mulai');
            $table->date('deadline')->comment('Deadline tugas');
            $table->decimal('bobot_persen', 5, 2)->comment('Bobot 20-30%');
            $table->decimal('target_value', 10, 2)->comment('Target');
            $table->string('satuan', 30)->comment('Satuan target');
            $table->enum('status', ['Assigned', 'In_Progress', 'Submitted', 'Validated', 'Rejected'])->default('Assigned');
            $table->foreignId('dokumen_lampiran_id')->nullable()->constrained('doc_dokumen')->comment('Foreign key ke DOC_DOKUMEN');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['pegawai_id', 'status']);
            $table->index('tugas_pokok_id');
            $table->index('deadline');

            // Check constraint for bobot_persen >= 20 and <= 30 akan diimplementasikan di level aplikasi
        });

        // Tugas Tambahan
        Schema::create('knj_tugas_tambahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Penerima tugas');
            $table->foreignId('pemberi_tugas_id')->constrained('master_pegawai')->comment('Atasan yang assign');
            $table->string('nama_tugas')->comment('Nama tugas');
            $table->text('deskripsi')->comment('Deskripsi tugas');
            $table->text('alasan_penugasan')->comment('Mandatory: kenapa pegawai ini');
            $table->date('deadline')->comment('Deadline tugas');
            $table->decimal('bobot_persen', 5, 2)->comment('Bobot, CHECK total <=20%');
            $table->decimal('target_value', 10, 2)->comment('Target');
            $table->string('satuan', 30)->comment('Satuan');
            $table->enum('status', ['Assigned', 'In_Progress', 'Submitted', 'Validated', 'Rejected'])->default('Assigned');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['pegawai_id', 'status']);
            $table->index('pemberi_tugas_id');
            $table->index('deadline');

            // Check constraint for total bobot_persen <= 20% akan diimplementasikan di level aplikasi
        });

        // Penugasan Mandiri
        Schema::create('knj_penugasan_mandiri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Pegawai yang buat');
            $table->foreignId('tugas_pokok_id')->constrained('knj_tugas_pokok')->comment('Tugas pokok yang didukung');
            $table->string('nama_tugas')->comment('Nama tugas mandiri');
            $table->text('deskripsi')->comment('Deskripsi lengkap');
            $table->decimal('target_value', 10, 2)->comment('Target');
            $table->string('satuan', 30)->comment('Satuan');
            $table->date('target_selesai')->comment('Target selesai');
            $table->enum('status', ['Pending_Approval', 'Approved', 'Rejected', 'In_Progress', 'Completed'])->default('Pending_Approval');
            $table->foreignId('approved_by')->nullable()->constrained('master_pegawai')->comment('Atasan yang approve');
            $table->text('alasan_reject')->nullable()->comment('Wajib jika rejected');
            $table->timestamp('approved_at')->nullable()->comment('Waktu approval');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['pegawai_id', 'status']);
            $table->index('tugas_pokok_id');
            $table->index('target_selesai');
        });

        // Progress Tugas
        Schema::create('knj_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_pokok_id')->nullable()->constrained('knj_tugas_pokok')->comment('Link ke KNJ_TUGAS_POKOK if applicable');
            $table->foreignId('tugas_harian_id')->nullable()->constrained('knj_tugas_harian')->comment('Link ke KNJ_TUGAS_HARIAN if applicable');
            $table->foreignId('tugas_tambahan_id')->nullable()->constrained('knj_tugas_tambahan')->comment('Link ke KNJ_TUGAS_TAMBAHAN if applicable');
            $table->foreignId('penugasan_mandiri_id')->nullable()->constrained('knj_penugasan_mandiri')->comment('Link ke KNJ_PENUGASAN_MANDIRI if applicable');
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Pegawai yang update');
            $table->date('tanggal')->comment('Tanggal progress');
            $table->decimal('progress_persen', 5, 2)->comment('Progress hari ini cumulative 0-100%');
            $table->text('deskripsi_kegiatan')->comment('Apa yang dikerjakan hari ini');
            $table->text('kendala')->nullable()->comment('Kendala yang dihadapi optional');
            $table->foreignId('dokumen_bukti_id')->nullable()->constrained('doc_dokumen')->comment('Foreign key ke DOC_DOKUMEN untuk bukti');
            $table->timestamps();
            
            $table->index(['pegawai_id', 'tanggal']);
            $table->index('tugas_pokok_id');
            $table->index('tugas_harian_id');
            $table->index('tugas_tambahan_id');
            $table->index('penugasan_mandiri_id');
        });

        // Foto Bukti
        Schema::create('knj_foto_bukti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_id')->constrained('knj_progress');
            $table->string('file_path')->comment('Path: storage/bukti/2024/10/...');
            $table->string('file_name')->comment('Original filename');
            $table->integer('file_size_kb')->comment('Ukuran dalam KB, max 5MB');
            $table->string('mime_type')->comment('image/jpeg, image/png only');
            $table->integer('urutan')->default(1)->comment('Urutan foto jika multiple');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['progress_id', 'urutan']);
        });

        // Validasi Tugas
        Schema::create('knj_validasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_pokok_id')->nullable()->constrained('knj_tugas_pokok')->comment('Validasi tugas pokok if applicable');
            $table->foreignId('tugas_harian_id')->nullable()->constrained('knj_tugas_harian')->comment('Validasi tugas harian if applicable');
            $table->foreignId('tugas_tambahan_id')->nullable()->constrained('knj_tugas_tambahan')->comment('Validasi tugas tambahan if applicable');
            $table->foreignId('penugasan_mandiri_id')->nullable()->constrained('knj_penugasan_mandiri')->comment('Validasi penugasan mandiri if applicable');
            $table->foreignId('validator_id')->constrained('master_pegawai')->comment('Atasan yang validasi');
            $table->enum('hasil_validasi', ['Terima', 'Minta_Revisi', 'Tolak'])->default('Terima');
            $table->text('catatan')->nullable()->comment('Catatan dari validator');
            $table->tinyInteger('penilaian_kualitas')->comment('Score 1-5, CHECK >=1 AND <=5');
            $table->boolean('kesesuaian_target')->default(true)->comment('TRUE jika sesuai target');
            $table->decimal('nilai_akhir', 5, 2)->comment('Nilai hasil kalkulasi sistem');
            $table->date('tanggal_validasi')->comment('Tanggal validasi');
            $table->timestamps();
            
            $table->index(['validator_id', 'tanggal_validasi']);
            $table->index('tugas_pokok_id');
            $table->index('tugas_harian_id');
            $table->index('tugas_tambahan_id');
            $table->index('penugasan_mandiri_id');

            // Check constraint for penilaian_kualitas >= 1 and <= 5 akan diimplementasikan di level aplikasi
        });

        // Revisi Tugas
        Schema::create('knj_revisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validasi_id')->constrained('knj_validasi')->comment('Foreign key ke KNJ_VALIDASI');
            $table->text('bagian_revisi')->comment('Mandatory: bagian yang perlu diperbaiki');
            $table->text('catatan_detail')->comment('Catatan detail');
            $table->text('panduan_perbaikan')->nullable()->comment('Panduan perbaikan');
            $table->timestamp('deadline_revisi')->comment('Auto +24 jam dari request');
            $table->boolean('is_terlambat')->default(false)->comment('TRUE jika lewat deadline');
            $table->decimal('penalty_nilai', 5, 2)->default(0)->comment('Penalty jika terlambat');
            $table->timestamp('submitted_at')->nullable()->comment('Waktu submit revisi');
            $table->timestamps();
            
            $table->index('validasi_id');
            $table->index('deadline_revisi');
        });

        // Nilai Kinerja Bulanan
        Schema::create('knj_nilai_bulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai');
            $table->year('tahun')->comment('Tahun penilaian');
            $table->tinyInteger('bulan')->comment('Bulan penilaian 1-12');
            $table->decimal('nilai_tugas_pokok', 5, 2)->default(0)->comment('Nilai tugas pokok 60-70%');
            $table->decimal('nilai_tugas_harian', 5, 2)->default(0)->comment('Nilai tugas harian 20-30%');
            $table->decimal('nilai_tugas_tambahan', 5, 2)->default(0)->comment('Nilai tugas tambahan max 20%');
            $table->decimal('total_penalti', 5, 2)->default(0)->comment('Total penalty');
            $table->decimal('total_bonus', 5, 2)->default(0)->comment('Total bonus');
            $table->decimal('nilai_total', 5, 2)->default(0)->comment('Nilai total 0-100');
            $table->enum('kategori_nilai', ['Sangat_Baik', 'Baik', 'Cukup', 'Kurang', 'Sangat_Kurang'])->nullable();
            $table->boolean('is_approved')->default(false)->comment('TRUE jika sudah approved');
            $table->foreignId('approved_by')->nullable()->constrained('master_pegawai')->comment('Atasan yang approve');
            $table->text('catatan_atasan')->nullable()->comment('Catatan dari atasan');
            $table->timestamp('approved_at')->nullable()->comment('Waktu approval');
            $table->timestamps();
            
            $table->unique(['pegawai_id', 'tahun', 'bulan']);
            $table->index('kategori_nilai');
        });

        // Nilai Kinerja Tahunan
        Schema::create('knj_nilai_tahunan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai');
            $table->year('tahun')->comment('Tahun penilaian');
            $table->decimal('rata_rata_bulanan', 5, 2)->default(0)->comment('Rata-rata dari 12 bulan');
            $table->decimal('nilai_tahunan', 5, 2)->default(0)->comment('Nilai tahunan final');
            $table->enum('kategori_nilai', ['Sangat_Baik', 'Baik', 'Cukup', 'Kurang', 'Sangat_Kurang'])->nullable();
            $table->text('catatan')->nullable()->comment('Catatan evaluasi tahunan');
            $table->timestamps();
            
            $table->unique(['pegawai_id', 'tahun']);
            $table->index('kategori_nilai');
        });

        // Delegasi Tugas
        Schema::create('knj_delegasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_pokok_id')->nullable()->constrained('knj_tugas_pokok')->comment('Delegasi tugas pokok if applicable');
            $table->foreignId('tugas_harian_id')->nullable()->constrained('knj_tugas_harian')->comment('Delegasi tugas harian if applicable');
            $table->foreignId('tugas_tambahan_id')->nullable()->constrained('knj_tugas_tambahan')->comment('Delegasi tugas tambahan if applicable');
            $table->foreignId('pegawai_asal_id')->constrained('master_pegawai')->comment('Pegawai asal');
            $table->foreignId('pegawai_tujuan_id')->constrained('master_pegawai')->comment('Pegawai tujuan');
            $table->foreignId('delegator_id')->constrained('master_pegawai')->comment('Atasan yang approve delegasi');
            $table->text('alasan_delegasi')->comment('Alasan delegasi');
            $table->decimal('progress_saat_delegasi', 5, 2)->default(0)->comment('Progress saat delegasi untuk hitung penalty');
            $table->decimal('penalty_nilai', 5, 2)->default(0)->comment('Penalty berdasarkan progress');
            $table->text('scope_delegasi')->nullable()->comment('Scope yang didelegasi');
            $table->date('deadline_baru')->comment('Deadline baru setelah delegasi');
            $table->enum('status', ['Pending', 'Accepted', 'In_Progress', 'Completed', 'Rejected'])->default('Pending');
            $table->timestamp('delegated_at')->useCurrent()->comment('Waktu delegasi');
            $table->timestamps();
            
            $table->index('pegawai_asal_id');
            $table->index('pegawai_tujuan_id');
            $table->index('tugas_pokok_id');
            $table->index('tugas_harian_id');
            $table->index('tugas_tambahan_id');
            $table->index('deadline_baru');
        });

        // Workload History
        Schema::create('knj_workload', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai');
            $table->date('tanggal')->comment('Tanggal snapshot');
            $table->integer('jumlah_tugas_aktif')->default(0)->comment('Jumlah tugas aktif');
            $table->decimal('total_bobot_persen', 5, 2)->default(0)->comment('Total bobot semua tugas');
            $table->integer('deadline_overlap_count')->default(0)->comment('Jumlah deadline yang overlap');
            $table->enum('kategori_beban', ['Normal', 'Moderate', 'Heavy', 'Overload'])->default('Normal');
            $table->decimal('skor_beban', 5, 2)->default(0)->comment('Skor beban 0-100+');
            $table->json('detail_breakdown')->nullable()->comment('Detail breakdown per jenis tugas');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['pegawai_id', 'tanggal']);
            $table->index('kategori_beban');
        });

        // ==============================================================
        // 5. SYSTEM & INTEGRATION
        // ==============================================================

        // Notifikasi
        Schema::create('sys_notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Penerima notifikasi');
            $table->string('judul')->comment('Judul notifikasi');
            $table->text('pesan')->comment('Isi pesan notifikasi');
            $table->enum('tipe', ['Tugas_Baru', 'Approval', 'Revisi', 'Validasi', 'Alert', 'Reminder', 'Nilai', 'Dokumen'])->comment('Tipe notifikasi');
            $table->string('entity_type', 30)->nullable()->comment('TugasPokok, PerjanjianKinerja, Dokumen');
            $table->unsignedBigInteger('entity_id')->nullable()->comment('ID dari entity terkait');
            $table->string('action_url')->nullable()->comment('Deep link untuk action');
            $table->boolean('is_read')->default(false)->comment('TRUE jika sudah dibaca');
            $table->timestamp('read_at')->nullable()->comment('Waktu dibaca');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['pegawai_id', 'is_read']);
            $table->index('tipe');
            $table->index('entity_type');
            $table->index('created_at');
        });

        // Preferensi Notifikasi
        Schema::create('sys_preferensi_notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->unique();
            $table->boolean('push_enabled')->default(true)->comment('TRUE jika push notif aktif');
            $table->boolean('email_enabled')->default(true)->comment('TRUE jika email notif aktif');
            $table->boolean('sound_enabled')->default(true)->comment('TRUE jika sound aktif');
            $table->time('quiet_hours_start')->nullable()->comment('Jam mulai quiet hours');
            $table->time('quiet_hours_end')->nullable()->comment('Jam akhir quiet hours');
            $table->json('notification_type_settings')->nullable()->comment('Settings per tipe notifikasi');
            $table->timestamps();
        });

        // Audit Log
        Schema::create('sys_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('master_pegawai')->comment('User yang melakukan action');
            $table->string('table_name', 50)->comment('Nama table yang affected');
            $table->unsignedBigInteger('record_id')->nullable()->comment('ID record yang affected');
            $table->enum('action', ['CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'EXPORT'])->comment('Jenis aksi');
            $table->json('old_values')->nullable()->comment('Snapshot sebelum perubahan');
            $table->json('new_values')->nullable()->comment('Snapshot setelah perubahan');
            $table->string('ip_address', 45)->nullable()->comment('IP address user');
            $table->string('user_agent')->nullable()->comment('Browser user agent');
            $table->text('description')->nullable()->comment('Deskripsi action');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['table_name', 'action']);
            $table->index(['table_name', 'record_id']);
        });

        // System Config
        Schema::create('sys_config', function (Blueprint $table) {
            $table->id();
            $table->string('config_key', 50)->unique()->comment('Key unik: max_tugas_tambahan');
            $table->string('config_group', 30)->comment('Group: General, Notification, Document, Kinerja');
            $table->text('config_value')->comment('Value konfigurasi');
            $table->enum('value_type', ['string', 'integer', 'boolean', 'json'])->default('string');
            $table->text('description')->nullable()->comment('Deskripsi konfigurasi');
            $table->boolean('is_public')->default(false)->comment('TRUE jika bisa diakses public');
            $table->timestamps();
            
            $table->index('config_group');
        });
    }

    public function down(): void
    {
        // 5. System & Integration
        Schema::dropIfExists('sys_config');
        Schema::dropIfExists('sys_audit_log');
        Schema::dropIfExists('sys_preferensi_notifikasi');
        Schema::dropIfExists('sys_notifikasi');

        // 4. Manajemen Kinerja
        Schema::dropIfExists('knj_workload');
        Schema::dropIfExists('knj_delegasi');
        Schema::dropIfExists('knj_nilai_tahunan');
        Schema::dropIfExists('knj_nilai_bulanan');
        Schema::dropIfExists('knj_revisi');
        Schema::dropIfExists('knj_validasi');
        Schema::dropIfExists('knj_foto_bukti');
        Schema::dropIfExists('knj_progress');
        Schema::dropIfExists('knj_penugasan_mandiri');
        Schema::dropIfExists('knj_tugas_tambahan');
        Schema::dropIfExists('knj_tugas_harian');
        Schema::dropIfExists('knj_indikator_tugas');
        Schema::dropIfExists('knj_tugas_pokok');

        // 3. Perjanjian Kinerja
        Schema::dropIfExists('pk_sub_kegiatan');
        Schema::dropIfExists('pk_kegiatan');
        Schema::dropIfExists('pk_program');
        Schema::dropIfExists('pk_indikator');
        Schema::dropIfExists('pk_sasaran');
        Schema::dropIfExists('pk_dokumen');
        Schema::dropIfExists('pk_perjanjian_kinerja');
        Schema::dropIfExists('pk_template_section');
        Schema::dropIfExists('pk_template');

        // 2. Document Management
        Schema::dropIfExists('doc_log');
        Schema::dropIfExists('doc_metadata');
        Schema::dropIfExists('doc_file');
        Schema::dropIfExists('doc_dokumen');
        Schema::dropIfExists('doc_nomor_counter');
        Schema::dropIfExists('doc_jenis');
        Schema::dropIfExists('doc_kategori');
        Schema::dropIfExists('doc_folder');

        // 1. Master Data
        Schema::dropIfExists('master_ttd_digital');
        Schema::dropIfExists('master_pegawai');
        Schema::dropIfExists('master_bidang');
        Schema::dropIfExists('master_jabatan');
    }
};