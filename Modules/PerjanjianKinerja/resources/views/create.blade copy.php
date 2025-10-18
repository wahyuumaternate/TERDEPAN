@extends('layouts.main')

@push('styles')
@endpush
<!-- Load Rupiah Formatter -->
<script src="{{ asset('assets/js/rupiah-formatter.js') }}"></script>
@section('main')
    <div class="pagetitle">
        <h1>Buat Perjanjian Kinerja Baru</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.index') }}">Perjanjian Kinerja</a></li>
                <li class="breadcrumb-item active">Buat Baru</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <form action="{{ route('perjanjian-kinerja.store') }}" method="POST" id="formPerjanjianKinerja">
            @csrf
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Informasi Dasar -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                                Informasi Dasar
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pegawai_id" class="form-label">
                                        Pegawai <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select select2" id="pegawai_id" name="pegawai_id" required>
                                        <option value="">-- Pilih Pegawai --</option>
                                        @foreach ($pegawai as $p)
                                            <option value="{{ $p->id }}"
                                                data-jabatan="{{ $p->jabatan->nama ?? '-' }}"
                                                data-bidang="{{ $p->bidang->nama ?? '-' }}"
                                                data-nip="{{ $p->nomor_identitas }}">
                                                {{ $p->nama }} - {{ $p->nomor_identitas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Pilih pegawai terlebih dahulu</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="atasan_id" class="form-label">
                                        Atasan Langsung <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="atasan_display" readonly
                                        placeholder="Otomatis terisi saat memilih pegawai"
                                        style="background-color: #f8f9fa;">
                                    <input type="hidden" id="atasan_id" name="atasan_id" required>
                                    <div class="invalid-feedback" id="atasan-error">Atasan belum tersedia</div>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle"></i>
                                        Atasan akan otomatis terisi berdasarkan pegawai yang dipilih
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="template_id" class="form-label">
                                        Template Dokumen <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="template_id" name="template_id" required>
                                        <option value="">-- Pilih Template --</option>
                                        @foreach ($templates as $t)
                                            <option value="{{ $t->id }}" {{ $t->is_active ? 'selected' : '' }}>
                                                {{ $t->nama_template }} (v{{ $t->versi }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Pilih template terlebih dahulu</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="tahun" class="form-label">
                                        Tahun Perjanjian <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="tahun" name="tahun"
                                        value="{{ date('Y') }}" min="2020" max="2100" required>
                                    <div class="invalid-feedback">Masukkan tahun yang valid</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="periode_mulai" class="form-label">
                                        Periode Mulai <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" id="periode_mulai" name="periode_mulai"
                                        value="{{ date('Y') }}-01-01" required>
                                    <div class="invalid-feedback">Pilih tanggal mulai</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="periode_selesai" class="form-label">
                                        Periode Selesai <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" id="periode_selesai" name="periode_selesai"
                                        value="{{ date('Y') }}-12-31" required>
                                    <div class="invalid-feedback">Pilih tanggal selesai</div>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="catatan" class="form-label">
                                        <i class="bi bi-sticky"></i> Catatan Tambahan
                                    </label>
                                    <textarea class="form-control" id="catatan" name="catatan" rows="3"
                                        placeholder="Tambahkan catatan jika diperlukan..." style="resize: vertical;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sasaran Strategis -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-bullseye text-primary"></i>
                                    Sasaran Strategis & Indikator Kinerja
                                </h5>
                                <button type="button" class="btn btn-primary btn-sm" onclick="addSasaran()">
                                    <i class="bi bi-plus-circle"></i> Tambah Sasaran
                                </button>
                            </div>

                            <div id="sasaran-container">
                                <div class="text-center py-5" id="empty-sasaran">
                                    <i class="bi bi-inbox display-1 text-muted opacity-50"></i>
                                    <p class="text-muted mt-3 mb-0">Belum ada sasaran strategis</p>
                                    <small class="text-muted">Klik tombol "Tambah Sasaran" untuk memulai</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Info Preview -->
                    <div class="card info-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-eye text-white"></i>
                                Preview Informasi
                            </h5>

                            <div class="preview-section">
                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-person-badge"></i> Pegawai
                                    </label>
                                    <div id="preview-pegawai" class="preview-value">-</div>
                                    <small id="preview-pegawai-detail" class="preview-detail"></small>
                                </div>

                                <hr class="my-3">

                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-person-check"></i> Atasan Langsung
                                    </label>
                                    <div id="preview-atasan" class="preview-value">-</div>
                                    <small id="preview-atasan-detail" class="preview-detail"></small>
                                </div>

                                <hr class="my-3">

                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-calendar-range"></i> Periode
                                    </label>
                                    <div id="preview-periode" class="preview-value">-</div>
                                </div>

                                <hr class="my-3">

                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-diagram-3"></i> Total Sasaran
                                    </label>
                                    <h3 id="preview-total-sasaran" class="preview-count mb-0">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-save"></i> Simpan Perjanjian Kinerja
                            </button>
                            <a href="{{ route('perjanjian-kinerja.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <!-- Tips Card -->
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="card-title text-info">
                                <i class="bi bi-lightbulb"></i> Tips
                            </h6>
                            <ul class="small mb-0 ps-3">
                                <li class="mb-2">Kode akan di-generate otomatis oleh sistem</li>
                                <li class="mb-2">Pastikan semua data sudah benar sebelum menyimpan</li>
                                <li class="mb-2">Dokumen masih dapat diedit sebelum ditandatangani</li>
                                <li>Tambahkan minimal 1 sasaran strategis dengan indikator</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>

    <!-- Template Sasaran Item -->
    <template id="template-sasaran">
        <div class="sasaran-item card border-start border-primary border-4 mb-3" data-index="0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-primary rounded-pill sasaran-number">1</span>
                        <strong class="ms-2">Sasaran Strategis</strong>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSasaran(this)"
                        title="Hapus Sasaran">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-card-text"></i> Deskripsi Sasaran
                        <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control sasaran-desc" name="sasaran[0][sasaran_strategis]" rows="3" required
                        placeholder="Contoh: Meningkatkan kualitas pelayanan publik..."></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Urutan</label>
                        <input type="number" class="form-control" name="sasaran[0][urutan]" value="1"
                            min="1" required>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="form-label fw-semibold mb-0">
                        <i class="bi bi-bar-chart"></i> Indikator Kinerja
                    </label>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addIndikator(this)">
                        <i class="bi bi-plus"></i> Tambah Indikator
                    </button>
                </div>

                <div class="indikator-container"></div>
            </div>
        </div>
    </template>

    <!-- Template Indikator Item -->

    <template id="template-indikator">
        <div class="indikator-item card bg-light border mb-2">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-muted fw-semibold">
                        <i class="bi bi-check2-circle"></i> Indikator <span class="indikator-number">1</span>
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeIndikator(this)"
                        title="Hapus Indikator">
                        <i class="bi bi-x"></i>
                    </button>
                </div>

                <div class="row g-2 mb-3">
                    <!-- FIELD WAJIB 1: Nama Indikator -->
                    <div class="col-12">
                        <input type="text" class="form-control form-control-sm"
                            name="sasaran[0][indikator][0][indikator_sasaran]" placeholder="Nama indikator kinerja..."
                            required>
                    </div>

                    <!-- FIELD WAJIB 2: Target Value -->
                    <div class="col-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Target</span>
                            <input type="number" class="form-control" name="sasaran[0][indikator][0][target_value]"
                                placeholder="100" step="0.01" required>
                        </div>
                    </div>

                    <!-- FIELD WAJIB 3: Satuan -->
                    <div class="col-6">
                        <input type="text" class="form-control form-control-sm"
                            name="sasaran[0][indikator][0][satuan]" placeholder="Satuan (%, Orang, Kegiatan...)" required>
                    </div>
                </div>

                <hr class="my-2">

                <!-- Program Section -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-semibold">
                        <i class="bi bi-folder"></i> Program & Kegiatan
                        <span class="badge bg-secondary ms-1">Kode Otomatis</span>
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-success btn-xs" onclick="addProgram(this)">
                        <i class="bi bi-plus"></i> Program
                    </button>
                </div>

                <div class="program-container"></div>
            </div>

        </div>
    </template>

    <!-- Template Program Item - TANPA KODE -->
    <template id="template-program">
        <div class="program-item card border-start border-success border-3 mb-2">
            <div class="card-body p-2 bg-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-success fw-semibold">
                        <i class="bi bi-folder-fill"></i> Program <span class="program-number">1</span>
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-xs" onclick="removeProgram(this)">
                        <i class="bi bi-x"></i>
                    </button>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-12">
                        <input type="text" class="form-control form-control-sm"
                            name="sasaran[0][indikator][0][program][0][nama_program]" placeholder="Nama Program" required>
                    </div>
                    <div class="col-12">
                        <input type="text" class="form-control form-control-sm"
                            name="sasaran[0][indikator][0][program][0][anggaran]" placeholder="Anggaran (Rp)" required>
                        <small class="text-muted" style="font-size: 0.7rem;">
                            <i class="bi bi-info-circle"></i> Contoh: ketik 200000000 → otomatis jadi Rp 200.000.000
                        </small>
                    </div>
                    <!-- Hidden input untuk urutan otomatis -->
                    <input type="hidden" name="sasaran[0][indikator][0][program][0][urutan]" value="1">
                </div>

                <!-- Kegiatan Section -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-list-task"></i> Kegiatan
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-info btn-xs"
                        style="padding: 0.1rem 0.3rem; font-size: 0.7rem;" onclick="addKegiatan(this)">
                        <i class="bi bi-plus"></i> Kegiatan
                    </button>
                </div>

                <div class="kegiatan-container ms-2"></div>
            </div>
        </div>
    </template>

    <!-- Template Kegiatan Item - TANPA KODE -->
    <template id="template-kegiatan">
        <div class="kegiatan-item card bg-light mb-1" style="border-left: 3px solid #0dcaf0;">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <small class="text-info" style="font-size: 0.75rem;">
                        <i class="bi bi-list-check"></i> Kegiatan <span class="kegiatan-number">1</span>
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                        style="padding: 0.1rem 0.3rem; font-size: 0.7rem;" onclick="removeKegiatan(this)">
                        <i class="bi bi-x"></i>
                    </button>
                </div>

                <div class="row g-1 mb-1">
                    <div class="col-12">
                        <input type="text" class="form-control form-control-sm" style="font-size: 0.8rem;"
                            name="sasaran[0][indikator][0][program][0][kegiatan][0][nama_kegiatan]"
                            placeholder="Nama Kegiatan" required>
                    </div>
                    <div class="col-12">
                        <input type="text" class="form-control form-control-sm" style="font-size: 0.8rem;"
                            name="sasaran[0][indikator][0][program][0][kegiatan][0][anggaran]" placeholder="Anggaran (Rp)"
                            required>
                    </div>
                    <!-- Hidden input untuk urutan otomatis -->
                    <input type="hidden" name="sasaran[0][indikator][0][program][0][kegiatan][0][urutan]"
                        value="1">
                </div>

                <!-- Sub Kegiatan Section -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted" style="font-size: 0.7rem;">
                        <i class="bi bi-arrow-return-right"></i> Sub Kegiatan
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-xs"
                        style="padding: 0.05rem 0.2rem; font-size: 0.65rem;" onclick="addSubKegiatan(this)">
                        <i class="bi bi-plus"></i> Sub
                    </button>
                </div>

                <div class="subkegiatan-container ms-2"></div>
            </div>
        </div>
    </template>

    <!-- Template Sub Kegiatan Item - TANPA KODE -->
    <template id="template-subkegiatan">
        <div class="subkegiatan-item p-2 mb-1 bg-white border rounded" style="border-left: 2px solid #6c757d !important;">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <small class="text-muted" style="font-size: 0.7rem;">
                    <i class="bi bi-arrow-return-right"></i> Sub <span class="subkegiatan-number">1</span>
                </small>
                <button type="button" class="btn btn-sm btn-outline-danger"
                    style="padding: 0.05rem 0.2rem; font-size: 0.65rem;" onclick="removeSubKegiatan(this)">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="row g-1">
                <div class="col-12">
                    <input type="text" class="form-control form-control-sm" style="font-size: 0.75rem;"
                        name="sasaran[0][indikator][0][program][0][kegiatan][0][subkegiatan][0][nama_sub_kegiatan]"
                        placeholder="Nama Sub Kegiatan" required>
                </div>
                <div class="col-6">
                    <input type="text" class="form-control form-control-sm" style="font-size: 0.75rem;"
                        name="sasaran[0][indikator][0][program][0][kegiatan][0][subkegiatan][0][anggaran]"
                        placeholder="Anggaran (Rp)" required>
                </div>
                <div class="col-6">
                    <input type="number" class="form-control form-control-sm" style="font-size: 0.75rem;"
                        name="sasaran[0][indikator][0][program][0][kegiatan][0][subkegiatan][0][target_value]"
                        placeholder="Target" step="0.01" required>
                </div>
                <div class="col-12">
                    <input type="text" class="form-control form-control-sm" style="font-size: 0.75rem;"
                        name="sasaran[0][indikator][0][program][0][kegiatan][0][subkegiatan][0][satuan]"
                        placeholder="Satuan" required>
                </div>
            </div>
        </div>
    </template>

    <style>
        .card {
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
            border-radius: 10px;
        }

        .card-title {
            color: #012970;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* Preview Styling - WARNA BIRU */
        .info-card {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            color: white;
        }

        .info-card .card-title {
            color: white;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 0.75rem;
        }

        .preview-section {
            padding-top: 0.5rem;
        }

        .preview-item {
            margin-bottom: 0.5rem;
        }

        .preview-label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .preview-value {
            font-size: 1rem;
            font-weight: 600;
            color: white;
        }

        .preview-detail {
            display: block;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.4;
        }

        .preview-count {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            text-align: center;
            margin-top: 0.5rem;
        }

        .info-card hr {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .sasaran-item {
            transition: all 0.3s ease;
            animation: slideIn 0.3s ease-out;
        }

        .sasaran-item:hover {
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .indikator-item {
            transition: all 0.3s ease;
            animation: fadeIn 0.3s ease-out;
        }

        .indikator-item:hover {
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
        }

        .program-item {
            animation: slideIn 0.2s ease-out;
        }

        .kegiatan-item {
            animation: slideIn 0.2s ease-out;
            font-size: 0.9rem;
        }

        .subkegiatan-item {
            animation: slideIn 0.2s ease-out;
            font-size: 0.85rem;
        }

        .btn-xs {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
        }

        .program-container,
        .kegiatan-container,
        .subkegiatan-container {
            margin-top: 0.5rem;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #1e88e5;
            box-shadow: 0 0 0 0.2rem rgba(30, 136, 229, 0.15);
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
        }

        /* Buttons - WARNA BIRU */
        .btn-primary {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(30, 136, 229, 0.4);
        }

        #empty-sasaran {
            padding: 3rem 1rem;
        }

        .badge {
            padding: 0.4em 0.7em;
            font-weight: 600;
        }

        .border-info {
            border-left: 4px solid #0dcaf0 !important;
        }

        @media (max-width: 991px) {
            .info-card {
                margin-bottom: 1.5rem;
            }
        }
    </style>
@endsection
@push('scripts')
    <script>
        let sasaranIndex = 0;

        // ============================================
        // FUNGSI FORMAT RUPIAH
        // ============================================
        function formatRupiah(angka, prefix = '') {
            if (!angka) return prefix;

            const numberString = angka.toString().replace(/[^,\d]/g, '');
            const split = numberString.split(',');
            const sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                const separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix + rupiah;
        }

        function unformatRupiah(rupiahString) {
            return rupiahString.replace(/[^0-9]/g, '');
        }

        // ============================================
        // APPLY FORMAT RUPIAH KE INPUT ANGGARAN
        // ============================================
        function applyRupiahFormat(inputElement) {
            $(inputElement).on('keyup', function() {
                let value = $(this).val();
                value = unformatRupiah(value);
                $(this).data('raw-value', value);
                $(this).val(formatRupiah(value, 'Rp '));
            });

            $(inputElement).on('focus', function() {
                const rawValue = $(this).data('raw-value') || unformatRupiah($(this).val());
                $(this).val(rawValue);
                $(this).select();
            });

            $(inputElement).on('blur', function() {
                const rawValue = $(this).val();
                $(this).data('raw-value', rawValue);
                if (rawValue) {
                    $(this).val(formatRupiah(rawValue, 'Rp '));
                }
            });
        }

        // ============================================
        // UPDATE URUTAN OTOMATIS
        // ============================================
        function updateAllUrutan() {
            // Update urutan Program
            $('.indikator-item').each(function() {
                $(this).find('.program-item').each(function(index) {
                    $(this).find('.program-number').first().text(index + 1);
                    $(this).find('input[name*="[urutan]"]').first().val(index + 1);
                });
            });

            // Update urutan Kegiatan
            $('.program-item').each(function() {
                $(this).find('.kegiatan-item').each(function(index) {
                    $(this).find('.kegiatan-number').first().text(index + 1);
                    $(this).find('input[name*="[urutan]"]').first().val(index + 1);
                });
            });

            // Update urutan Sub Kegiatan
            $('.kegiatan-item').each(function() {
                $(this).find('.subkegiatan-item').each(function(index) {
                    $(this).find('.subkegiatan-number').first().text(index + 1);
                });
            });
        }

        // ============================================
        // SEBELUM SUBMIT: KEMBALIKAN KE ANGKA MURNI
        // ============================================
        function prepareAnggaranForSubmit() {
            $('input[name*="anggaran"]').each(function() {
                const rawValue = $(this).data('raw-value') || unformatRupiah($(this).val());
                $(this).val(rawValue);
            });
        }

        $(document).ready(function() {
            // Initialize Select2
            if (typeof $.fn.select2 === 'undefined') {
                console.error('Select2 library not loaded!');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Library Select2 tidak berhasil dimuat. Silakan refresh halaman.',
                    confirmButtonColor: '#1e88e5'
                });
                return;
            }

            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- Pilih Pegawai --',
                allowClear: true
            });

            // Update preview on change
            $('#pegawai_id').change(function() {
                updatePegawaiPreview();
                loadAtasan($(this).val());
            });

            $('#periode_mulai, #periode_selesai').change(updatePeriodePreview);
            $('#tahun').change(function() {
                const tahun = $(this).val();
                $('#periode_mulai').val(tahun + '-01-01');
                $('#periode_selesai').val(tahun + '-12-31');
                updatePeriodePreview();
            });

            // Real-time update preview
            $('#pegawai_id, #tahun, #periode_mulai, #periode_selesai').on('input change', updateAllPreviews);

            // Observer untuk detect perubahan DOM
            let observerTimeout;
            const observer = new MutationObserver(function(mutations) {
                clearTimeout(observerTimeout);
                observerTimeout = setTimeout(function() {
                    updateSasaranCount();

                    // Apply format rupiah ke input anggaran yang baru
                    $('input[name*="anggaran"]:not(.rupiah-formatted)').each(function() {
                        $(this).addClass('rupiah-formatted');
                        applyRupiahFormat(this);
                    });

                    // Update urutan
                    updateAllUrutan();
                }, 100);
            });

            const sasaranContainer = document.getElementById('sasaran-container');
            if (sasaranContainer) {
                observer.observe(sasaranContainer, {
                    childList: true,
                    subtree: true
                });
            }

            // Form validation
            $('#formPerjanjianKinerja').submit(function(e) {
                if (!$('#atasan_id').val()) {
                    e.preventDefault();
                    e.stopPropagation();

                    $('#atasan_display').addClass('is-invalid');
                    $('#atasan-error').show();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: 'Silakan pilih pegawai terlebih dahulu untuk mendapatkan data atasan',
                        confirmButtonColor: '#1e88e5'
                    });
                    return false;
                }

                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Form Tidak Lengkap',
                        text: 'Mohon lengkapi semua field yang wajib diisi',
                        confirmButtonColor: '#1e88e5'
                    });
                    return false;
                }

                // Kembalikan format anggaran ke angka murni
                prepareAnggaranForSubmit();

                // Update urutan terakhir
                updateAllUrutan();

                $(this).addClass('was-validated');
            });

            // Initialize preview
            setTimeout(function() {
                updateAllPreviews();
            }, 100);
        });

        function updatePegawaiPreview() {
            const selected = $('#pegawai_id option:selected');
            if (selected.val()) {
                const nama = selected.text().split(' - ')[0];
                const nip = selected.data('nip');
                const jabatan = selected.data('jabatan');
                const bidang = selected.data('bidang');

                $('#preview-pegawai').text(nama);
                $('#preview-pegawai-detail').html(
                    `NIP: ${nip}<br>Jabatan: ${jabatan}<br>Unit: ${bidang}`
                );
            } else {
                $('#preview-pegawai').text('-');
                $('#preview-pegawai-detail').html('');
            }
        }

        function updatePeriodePreview() {
            const mulai = $('#periode_mulai').val();
            const selesai = $('#periode_selesai').val();
            if (mulai && selesai) {
                const startDate = new Date(mulai).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
                const endDate = new Date(selesai).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
                $('#preview-periode').text(`${startDate} - ${endDate}`);
            } else {
                $('#preview-periode').text('-');
            }
        }

        function loadAtasan(pegawaiId) {
            if (!pegawaiId) {
                $('#atasan_display').val('').removeClass('is-invalid');
                $('#atasan_id').val('');
                $('#atasan-error').hide();
                $('#preview-atasan').text('-');
                $('#preview-atasan-detail').html('');
                return;
            }

            $.ajax({
                url: "{{ route('perjanjian-kinerja.get-atasan', '') }}/" + pegawaiId,
                type: 'GET',
                beforeSend: function() {
                    $('#atasan_display').val('Loading...').removeClass('is-invalid');
                    $('#atasan-error').hide();
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const atasan = response.data;

                        $('#atasan_display').val(`${atasan.nama} - ${atasan.nip}`).removeClass('is-invalid');
                        $('#atasan_id').val(atasan.id);
                        $('#atasan-error').hide();

                        $('#preview-atasan').text(atasan.nama);
                        $('#preview-atasan-detail').html(
                            `NIP: ${atasan.nip}<br>Jabatan: ${atasan.jabatan}`
                        );
                    } else {
                        $('#atasan_display').val('Atasan tidak ditemukan').addClass('is-invalid');
                        $('#atasan_id').val('');
                        $('#atasan-error').text(response.message || 'Pegawai tidak memiliki atasan langsung')
                            .show();
                        $('#preview-atasan').text('-');
                        $('#preview-atasan-detail').html('');

                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: response.message || 'Pegawai tidak memiliki atasan langsung',
                            confirmButtonColor: '#1e88e5'
                        });
                    }
                },
                error: function(xhr) {
                    $('#atasan_display').val('Error loading atasan').addClass('is-invalid');
                    $('#atasan_id').val('');
                    $('#atasan-error').text('Gagal memuat data atasan').show();
                    $('#preview-atasan').text('-');
                    $('#preview-atasan-detail').html('');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data atasan',
                        confirmButtonColor: '#1e88e5'
                    });
                }
            });
        }

        function addSasaran() {
            $('#empty-sasaran').hide();
            const template = document.getElementById('template-sasaran');
            const clone = template.content.cloneNode(true);
            const sasaranItem = clone.querySelector('.sasaran-item');

            sasaranItem.dataset.index = sasaranIndex;
            sasaranItem.querySelector('.sasaran-number').textContent = sasaranIndex + 1;
            sasaranItem.querySelector('textarea[name*="sasaran_strategis"]').name =
                `sasaran[${sasaranIndex}][sasaran_strategis]`;
            sasaranItem.querySelector('input[name*="urutan"]').name = `sasaran[${sasaranIndex}][urutan]`;
            sasaranItem.querySelector('input[name*="urutan"]').value = sasaranIndex + 1;

            $('#sasaran-container').append(sasaranItem);
            sasaranIndex++;
            updateSasaranCount();
        }

        function removeSasaran(btn) {
            Swal.fire({
                title: 'Hapus Sasaran?',
                text: 'Semua indikator di sasaran ini akan ikut terhapus',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(btn).closest('.sasaran-item').fadeOut(300, function() {
                        $(this).remove();
                        updateSasaranNumbers();
                        updateSasaranCount();
                        if ($('.sasaran-item').length === 0) {
                            $('#empty-sasaran').fadeIn();
                        }
                    });
                }
            });
        }

        function addIndikator(btn) {
            const sasaranItem = $(btn).closest('.sasaran-item');
            const sasaranIdx = sasaranItem.data('index');
            const container = sasaranItem.find('.indikator-container');
            const indikatorCount = container.find('.indikator-item').length;

            const template = document.getElementById('template-indikator');
            const clone = template.content.cloneNode(true);

            clone.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/sasaran\[\d+\]/, `sasaran[${sasaranIdx}]`)
                        .replace(/indikator\[\d+\]/, `indikator[${indikatorCount}]`));
                }
            });

            clone.querySelector('.indikator-number').textContent = indikatorCount + 1;
            container.append(clone);
        }

        function removeIndikator(btn) {
            Swal.fire({
                title: 'Hapus Indikator?',
                text: 'Semua program di indikator ini akan ikut terhapus',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(btn).closest('.indikator-item').fadeOut(200, function() {
                        $(this).remove();
                        updateIndikatorNumbers();
                    });
                }
            });
        }

        function addProgram(btn) {
            const indikatorItem = $(btn).closest('.indikator-item');
            const sasaranIdx = $(btn).closest('.sasaran-item').data('index');
            const indikatorIdx = indikatorItem.parent().children('.indikator-item').index(indikatorItem);
            const container = indikatorItem.find('.program-container');
            const programCount = container.find('.program-item').length;

            const template = document.getElementById('template-program');
            const clone = template.content.cloneNode(true);

            clone.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name
                        .replace(/sasaran\[\d+\]/, `sasaran[${sasaranIdx}]`)
                        .replace(/indikator\[\d+\]/, `indikator[${indikatorIdx}]`)
                        .replace(/program\[\d+\]/, `program[${programCount}]`));
                }
            });

            clone.querySelector('.program-number').textContent = programCount + 1;
            clone.querySelector('input[name*="urutan"]').value = programCount + 1;
            container.append(clone);

            updateAllUrutan();
        }

        function removeProgram(btn) {
            Swal.fire({
                title: 'Hapus Program?',
                text: 'Semua kegiatan di program ini akan ikut terhapus',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(btn).closest('.program-item').fadeOut(200, function() {
                        $(this).remove();
                        updateAllUrutan();
                    });
                }
            });
        }

        function addKegiatan(btn) {
            const programItem = $(btn).closest('.program-item');
            const indikatorItem = $(btn).closest('.indikator-item');
            const sasaranIdx = $(btn).closest('.sasaran-item').data('index');
            const indikatorIdx = indikatorItem.parent().children('.indikator-item').index(indikatorItem);
            const programIdx = programItem.parent().children('.program-item').index(programItem);
            const container = programItem.find('.kegiatan-container');
            const kegiatanCount = container.find('.kegiatan-item').length;

            const template = document.getElementById('template-kegiatan');
            const clone = template.content.cloneNode(true);

            clone.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name
                        .replace(/sasaran\[\d+\]/, `sasaran[${sasaranIdx}]`)
                        .replace(/indikator\[\d+\]/, `indikator[${indikatorIdx}]`)
                        .replace(/program\[\d+\]/, `program[${programIdx}]`)
                        .replace(/kegiatan\[\d+\]/, `kegiatan[${kegiatanCount}]`));
                }
            });

            clone.querySelector('.kegiatan-number').textContent = kegiatanCount + 1;
            clone.querySelector('input[name*="urutan"]').value = kegiatanCount + 1;
            container.append(clone);

            updateAllUrutan();
        }

        function removeKegiatan(btn) {
            Swal.fire({
                title: 'Hapus Kegiatan?',
                text: 'Semua sub kegiatan di kegiatan ini akan ikut terhapus',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(btn).closest('.kegiatan-item').fadeOut(200, function() {
                        $(this).remove();
                        updateAllUrutan();
                    });
                }
            });
        }

        function addSubKegiatan(btn) {
            const kegiatanItem = $(btn).closest('.kegiatan-item');
            const programItem = $(btn).closest('.program-item');
            const indikatorItem = $(btn).closest('.indikator-item');
            const sasaranIdx = $(btn).closest('.sasaran-item').data('index');
            const indikatorIdx = indikatorItem.parent().children('.indikator-item').index(indikatorItem);
            const programIdx = programItem.parent().children('.program-item').index(programItem);
            const kegiatanIdx = kegiatanItem.parent().children('.kegiatan-item').index(kegiatanItem);
            const container = kegiatanItem.find('.subkegiatan-container');
            const subKegiatanCount = container.find('.subkegiatan-item').length;

            const template = document.getElementById('template-subkegiatan');
            const clone = template.content.cloneNode(true);

            clone.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name
                        .replace(/sasaran\[\d+\]/, `sasaran[${sasaranIdx}]`)
                        .replace(/indikator\[\d+\]/, `indikator[${indikatorIdx}]`)
                        .replace(/program\[\d+\]/, `program[${programIdx}]`)
                        .replace(/kegiatan\[\d+\]/, `kegiatan[${kegiatanIdx}]`)
                        .replace(/subkegiatan\[\d+\]/, `subkegiatan[${subKegiatanCount}]`));
                }
            });

            clone.querySelector('.subkegiatan-number').textContent = subKegiatanCount + 1;
            container.append(clone);

            updateAllUrutan();
        }

        function removeSubKegiatan(btn) {
            Swal.fire({
                title: 'Hapus Sub Kegiatan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(btn).closest('.subkegiatan-item').fadeOut(200, function() {
                        $(this).remove();
                        updateAllUrutan();
                    });
                }
            });
        }

        function updateSasaranNumbers() {
            $('.sasaran-item').each(function(index) {
                $(this).find('.sasaran-number').text(index + 1);
                $(this).find('input[name*="urutan"]').val(index + 1);
            });
        }

        function updateIndikatorNumbers() {
            $('.sasaran-item').each(function() {
                $(this).find('.indikator-item').each(function(index) {
                    $(this).find('.indikator-number').text(index + 1);
                });
            });
        }

        function updateSasaranCount() {
            const count = $('.sasaran-item').length;
            $('#preview-total-sasaran').text(count);
        }

        function updateAllPreviews() {
            updatePegawaiPreview();
            updatePeriodePreview();
            updateSasaranCount();
        }
    </script>
@endpush
