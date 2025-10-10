@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Manajemen Jenis Dokumen</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dokumen.index') }}">Dokumen</a></li>
                <li class="breadcrumb-item active">Jenis</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="card-title mb-1 d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 rounded-3 p-2 me-3">
                                        <i class="bi bi-file-earmark-text-fill text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Jenis Dokumen</span>
                                        <small class="d-block text-muted fw-normal mt-1">Kelola jenis dokumen untuk
                                            kategorisasi yang efisien</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary btn-lg shadow-sm px-4 py-2"
                                    onclick="showCreateModal()">
                                    <i class="bi bi-plus-circle me-2"></i>Tambah Jenis
                                </button>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="stats-card bg-gradient-primary text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Total Jenis Dokumen</h6>
                                            <h3 class="mb-0 fw-bold" id="totalJenis">{{ count($jenis) }}</h3>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="bi bi-file-earmark-text-fill"
                                                style="font-size: 2.5rem; opacity: 0.3;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="stats-card bg-gradient-success text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Dengan Penomoran</h6>
                                            <h3 class="mb-0 fw-bold" id="totalDenganNomor">
                                                {{ $jenis->where('perlu_nomor', true)->count() }}</h3>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="bi bi-hash" style="font-size: 2.5rem; opacity: 0.3;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="stats-card bg-gradient-info text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Kategori Terkait</h6>
                                            <h3 class="mb-0 fw-bold" id="totalKategoriTerkait">
                                                {{ $jenis->pluck('kategori_id')->unique()->count() }}</h3>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="bi bi-folder-fill" style="font-size: 2.5rem; opacity: 0.3;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- View Toggle -->
                        <div class="mb-3">
                            <div class="btn-group shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="viewMode" id="viewGrid" checked>
                                <label class="btn btn-outline-primary px-4" for="viewGrid">
                                    <i class="bi bi-grid-3x3-gap me-2"></i>Grid View
                                </label>
                                <input type="radio" class="btn-check" name="viewMode" id="viewTable">
                                <label class="btn btn-outline-primary px-4" for="viewTable">
                                    <i class="bi bi-table me-2"></i>Table View
                                </label>
                            </div>
                        </div>

                        <!-- Grid View -->
                        <div id="gridView" class="row">
                            @if (count($jenis) == 0)
                                <div class="col-12 empty-state text-center">
                                    <i class="bi bi-file-earmark-x empty-state-icon"></i>
                                    <h4 class="text-muted mb-2">Belum ada jenis dokumen</h4>
                                    <p class="text-muted mb-4">Mulai dengan menambahkan jenis dokumen baru untuk
                                        mengorganisir dokumen Anda</p>
                                    <button class="btn btn-primary btn-lg px-5" onclick="showCreateModal()">
                                        <i class="bi bi-plus-circle me-2"></i>Tambah Jenis Dokumen Pertama
                                    </button>
                                </div>
                            @else
                                @foreach ($jenis as $item)
                                    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                                        <div class="card jenis-card h-100 shadow-sm"
                                            style="--card-color: {{ $item->kategori->warna ?? '#4154f1' }}; --card-color-light: {{ $item->kategori->warna ?? '#4154f1' }}20;">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <span class="badge-kode"
                                                        style="border-color: {{ $item->kategori->warna ?? '#4154f1' }}; color: {{ $item->kategori->warna ?? '#4154f1' }};">
                                                        {{ $item->kode }}
                                                    </span>
                                                </div>
                                                <h5 class="mb-3 fw-bold">{{ $item->nama }}</h5>
                                                <span class="badge badge-kategori"
                                                    style="background: {{ $item->kategori->warna ?? '#4154f1' }}20; color: {{ $item->kategori->warna ?? '#4154f1' }};">
                                                    {{ $item->kategori->nama ?? 'Kategori tidak ditemukan' }}
                                                </span>
                                                {{-- @if ($item->perlu_nomor)
                                                    <div class="mt-3">
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-hash me-1"></i>Perlu Penomoran
                                                        </span>
                                                    </div>
                                                @endif --}}
                                            </div>
                                            <div class="card-footer bg-transparent border-0 pb-3 px-3">
                                                <div class="d-grid gap-2">
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-outline-primary"
                                                            onclick="showDetail({{ $item->id }})" title="Detail">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-warning"
                                                            onclick="showEditModal({{ $item->id }})" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteJenis({{ $item->id }})" title="Hapus">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Table View -->
                        <div id="tableView" style="display: none;">
                            <table class="table datatable" id="jenisTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Nama Jenis</th>
                                        <th>Kategori</th>
                                        <th>Format Nomor</th>
                                        <th>Ukuran Maks.</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($jenis as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <span class="badge"
                                                    style="background-color: {{ $item->kategori->warna ?? '#4154f1' }};">{{ $item->kode }}</span>
                                            </td>
                                            <td><strong>{{ $item->nama }}</strong></td>
                                            <td>{{ $item->kategori->nama ?? 'Kategori tidak ditemukan' }}</td>
                                            <td>{{ $item->nomor_format ?? '-' }}</td>
                                            <td>{{ $item->max_size_mb }} MB</td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-outline-primary"
                                                        onclick="showDetail({{ $item->id }})" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning"
                                                        onclick="showEditModal({{ $item->id }})" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger"
                                                        onclick="deleteJenis({{ $item->id }})" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="modalJenis" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-primary text-white pb-3 pt-3">
                    <h5 class="modal-title fw-bold" id="modalTitle">
                        <i class="bi bi-file-earmark-plus me-2"></i>Tambah Jenis Dokumen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formJenis">
                        @csrf
                        <input type="hidden" id="jenis_id" name="jenis_id">
                        <input type="hidden" id="_method" name="_method" value="POST">

                        <div class="mb-3">
                            <label for="kategori_id" class="form-label">
                                Kategori <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="kategori_id" name="kategori_id" required>
                                <option value="">Pilih Kategori</option>
                                <!-- Kategori options will be loaded dynamically -->
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="kode" class="form-label">
                                Kode Jenis <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="kode" name="kode" maxlength="20"
                                required placeholder="Contoh: MOM, SK, SOP">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Maksimal 20 karakter, huruf dan angka
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label">
                                Nama Jenis <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nama" name="nama" maxlength="100"
                                required placeholder="Contoh: Notulensi Rapat, Surat Keputusan">
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perlu_nomor" name="perlu_nomor">
                                <label class="form-check-label" for="perlu_nomor">
                                    Perlu Nomor Dokumen
                                </label>
                                <small class="d-block text-muted ms-4">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Aktifkan jika dokumen ini membutuhkan penomoran otomatis
                                </small>
                            </div>
                        </div>

                        <div class="mb-3" id="nomorFormatContainer" style="display: none;">
                            <label for="nomor_format" class="form-label">
                                Format Nomor
                            </label>
                            <input type="text" class="form-control" id="nomor_format" name="nomor_format"
                                placeholder="Contoh: {nomor}/{kode}/{bulan}/{tahun}">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Variabel: {nomor}, {kode}, {tahun}, {bulan}, {tanggal}
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="folder_pattern" class="form-label">
                                Pola Folder
                            </label>
                            <input type="text" class="form-control" id="folder_pattern" name="folder_pattern"
                                placeholder="/{bidang}/{jenis}/{year}/{month}/" value="/{bidang}/{jenis}/{year}/{month}/">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Variabel: {bidang}, {jenis}, {year}, {month}, {day}
                            </small>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-7">
                                <label for="allowed_ext" class="form-label">
                                    Ekstensi File yang Diizinkan
                                </label>
                                <input type="text" class="form-control" id="allowed_ext" name="allowed_ext"
                                    placeholder="pdf,docx,xlsx" value="pdf,docx,xlsx">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Pisahkan dengan koma (,)
                                </small>
                            </div>

                            <div class="col-md-5">
                                <label for="max_size_mb" class="form-label">
                                    Ukuran Maksimal (MB)
                                </label>
                                <input type="number" class="form-control" id="max_size_mb" name="max_size_mb"
                                    placeholder="10" value="10" min="1" max="100">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="btnSaveJenis">
                        <i class="bi bi-check me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetailJenis" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-primary text-white pb-4 pt-4">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="bi bi-info-circle me-2"></i>Detail Jenis Dokumen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="detailJenisContent">
                    <!-- Dynamic content -->
                </div>
                <div class="modal-footer bg-light border-0 p-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Modern Card Styles */
        .card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .jenis-card {
            position: relative;
            overflow: hidden;
            border-radius: 16px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid #f0f0f0 !important;
        }

        .jenis-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--card-color, #4154f1), var(--card-color-light, #6c7ef5));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .jenis-card:hover::before {
            transform: scaleX(1);
        }

        .jenis-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
            border-color: var(--card-color, #4154f1) !important;
        }

        .jenis-card .card-body {
            padding: 2rem 1.5rem;
        }

        .jenis-icon-wrapper {
            width: 90px;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .jenis-card:hover .jenis-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        .jenis-icon {
            font-size: 3rem;
            transition: all 0.3s ease;
        }

        /* Stats Cards - Theme Colors */
        .stats-card {
            border-radius: 16px !important;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        /* Badge */
        .badge-kategori {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 50px;
        }

        .badge-kode {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 10px;
            letter-spacing: 1px;
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
        }

        /* Modal Animations */
        .modal.fade .modal-dialog {
            transform: scale(0.8);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modal.show .modal-dialog {
            transform: scale(1);
            opacity: 1;
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
        }

        .empty-state-icon {
            font-size: 6rem;
            color: #e0e0e0;
            margin-bottom: 2rem;
        }

        /* Table Styles */
        .datatable-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .icon-cell {
            font-size: 1.5rem;
        }

        .form-switch .form-check-input {
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .jenis-card .card-body {
                padding: 1.5rem 1rem;
            }

            .jenis-icon-wrapper {
                width: 70px;
                height: 70px;
            }

            .jenis-icon {
                font-size: 2.5rem;
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        let allJenis = @json($jenis);
        let viewMode = 'grid';
        let dataTable = null;

        $(document).ready(function() {
            loadKategori();
            initializeView();

            // View mode toggle
            $('input[name="viewMode"]').change(function() {
                viewMode = $(this).attr('id') === 'viewGrid' ? 'grid' : 'table';
                toggleView();
            });

            // Toggle nomor format visibility
            $('#perlu_nomor').change(function() {
                $('#nomorFormatContainer').toggle(this.checked);
                if (!this.checked) {
                    $('#nomor_format').val('');
                }
            });

            $('#btnSaveJenis').click(function() {
                saveJenis();
            });
        });

        function initializeView() {
            // Initialize the table view if needed
            if (viewMode === 'table') {
                $('#gridView').hide();
                $('#tableView').show();

                // Initialize DataTable
                dataTable = new simpleDatatables.DataTable("#jenisTable", {
                    searchable: true,
                    fixedHeight: false,
                    perPage: 10,
                    labels: {
                        placeholder: "Cari jenis dokumen...",
                        perPage: "Data per halaman",
                        noRows: "Tidak ada data",
                        info: "Menampilkan {start} sampai {end} dari {rows} data",
                    }
                });
            }
        }

        function toggleView() {
            if (viewMode === 'grid') {
                $('#gridView').show();
                $('#tableView').hide();
                if (dataTable) {
                    dataTable.destroy();
                    dataTable = null;
                }
            } else {
                $('#gridView').hide();
                $('#tableView').show();

                if (!dataTable) {
                    // Initialize DataTable if not already initialized
                    dataTable = new simpleDatatables.DataTable("#jenisTable", {
                        searchable: true,
                        fixedHeight: false,
                        perPage: 10,
                        labels: {
                            placeholder: "Cari jenis dokumen...",
                            perPage: "Data per halaman",
                            noRows: "Tidak ada data",
                            info: "Menampilkan {start} sampai {end} dari {rows} data",
                        }
                    });
                }
            }
        }

        function loadKategori() {
            $.ajax({
                url: '{{ route('dokumen.kategori.index') }}',
                type: 'GET',
                success: function(response) {
                    allKategori = response;
                    let options = '<option value="">Pilih Kategori</option>';

                    response.forEach(function(item) {
                        options += `<option value="${item.id}">${item.nama}</option>`;
                    });

                    $('#kategori_id').html(options);
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data kategori',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        }

        function loadJenis() {
            $.ajax({
                url: '{{ route('dokumen.jenis.index') }}',
                type: 'GET',
                success: function(response) {
                    allJenis = response;
                    updateStats(response);
                    renderGrid(response);
                },
                error: function(xhr) {
                    $('#gridView').html(`
                        <div class="col-12 empty-state text-center">
                            <i class="bi bi-exclamation-triangle empty-state-icon"></i>
                            <h4 class="text-muted mb-3">Gagal memuat jenis dokumen</h4>
                            <p class="text-muted mb-4">Terjadi kesalahan saat mengambil data</p>
                            <button class="btn btn-primary btn-lg" onclick="loadJenis()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Coba Lagi
                            </button>
                        </div>
                    `);
                }
            });
        }

        function updateStats(data) {
            $('#totalJenis').text(data.length);

            // Count documents with numbering
            const denganNomor = data.filter(item => item.perlu_nomor).length;
            $('#totalDenganNomor').text(denganNomor);

            // Count unique categories
            const uniqueKategori = [...new Set(data.map(item => item.kategori_id))].length;
            $('#totalKategoriTerkait').text(uniqueKategori);
        }

        function renderGrid(data) {
            let html = '';

            if (!Array.isArray(data) || data.length === 0) {
                html = `
                    <div class="col-12 empty-state text-center">
                        <i class="bi bi-file-earmark-x empty-state-icon"></i>
                        <h4 class="text-muted mb-2">Belum ada jenis dokumen</h4>
                        <p class="text-muted mb-4">Mulai dengan menambahkan jenis dokumen baru untuk mengorganisir dokumen Anda</p>
                        <button class="btn btn-primary btn-lg px-5" onclick="showCreateModal()">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Jenis Dokumen Pertama
                        </button>
                    </div>
                `;
            } else {
                data.forEach((item) => {
                    // Find related category
                    const kategori = allKategori.find(k => k.id === item.kategori_id) || {};
                    const color = kategori.warna || '#4154f1';
                    const lightColor = color + '20';

                    html += `
                        <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                            <div class="card jenis-card h-100 shadow-sm" 
                                 style="--card-color: ${color}; --card-color-light: ${lightColor};">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <span class="badge-kode" style="border-color: ${color}; color: ${color};">
                                            ${item.kode}
                                        </span>
                                    </div>
                                    <h5 class="mb-3 fw-bold">${item.nama}</h5>
                                    <span class="badge badge-kategori" style="background: ${lightColor}; color: ${color};">
                                        ${kategori.nama || 'Kategori tidak ditemukan'}
                                    </span>
                                    ${item.perlu_nomor ? 
                                        `<div class="mt-3">
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-hash me-1"></i>Perlu Penomoran
                                                        </span>
                                                    </div>` : ''}
                                </div>
                                <div class="card-footer bg-transparent border-0 pb-3 px-3">
                                    <div class="d-grid gap-2">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="showDetail(${item.id})" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="showEditModal(${item.id})" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteJenis(${item.id})" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            $('#gridView').html(html);
        }

        function renderTable(data) {
            // Destroy existing datatable if exists
            if (dataTable) {
                dataTable.destroy();
            }

            let tbody = '';
            data.forEach((item, index) => {
                // Find related category
                const kategori = allKategori.find(k => k.id === item.kategori_id) || {};
                const color = kategori.warna || '#4154f1';

                tbody += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <span class="badge" style="background-color: ${color};">${item.kode}</span>
                        </td>
                        <td><strong>${item.nama}</strong></td>
                        <td>${kategori.nama || 'Kategori tidak ditemukan'}</td>
                        <td>${item.nomor_format || '<span class="text-muted">-</span>'}</td>
                        <td>${item.max_size_mb} MB</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" onclick="showDetail(${item.id})" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" onclick="showEditModal(${item.id})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteJenis(${item.id})" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            $('#jenisTable tbody').html(tbody);

            // Initialize DataTable
            dataTable = new simpleDatatables.DataTable("#jenisTable", {
                searchable: true,
                fixedHeight: false,
                perPage: 10,
                labels: {
                    placeholder: "Cari jenis dokumen...",
                    perPage: "Data per halaman",
                    noRows: "Tidak ada data",
                    info: "Menampilkan {start} sampai {end} dari {rows} data",
                }
            });
        }

        function showCreateModal() {
            $('#modalTitle').html('<i class="bi bi-file-earmark-plus me-2"></i>Tambah Jenis Dokumen');
            $('#formJenis')[0].reset();
            $('#jenis_id').val('');
            $('#_method').val('POST');
            $('#folder_pattern').val('/{bidang}/{jenis}/{year}/{month}/');
            $('#allowed_ext').val('pdf,docx,xlsx');
            $('#max_size_mb').val(10);
            $('#perlu_nomor').prop('checked', false);
            $('#nomorFormatContainer').hide();

            $('#modalJenis').modal('show');
        }

        function showEditModal(id) {
            $.ajax({
                url: `/dokumen/jenis/${id}`,
                type: 'GET',
                success: function(response) {
                    $('#modalTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Jenis Dokumen');
                    $('#jenis_id').val(response.id);
                    $('#_method').val('PUT');
                    $('#kategori_id').val(response.kategori_id);
                    $('#kode').val(response.kode);
                    $('#nama').val(response.nama);
                    $('#folder_pattern').val(response.folder_pattern);
                    $('#allowed_ext').val(response.allowed_ext);
                    $('#max_size_mb').val(response.max_size_mb);

                    const perluNomor = response.perlu_nomor ? true : false;
                    $('#perlu_nomor').prop('checked', perluNomor);
                    $('#nomorFormatContainer').toggle(perluNomor);
                    $('#nomor_format').val(response.nomor_format || '');

                    $('#modalJenis').modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data jenis dokumen',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        }

        function saveJenis() {
            let id = $('#jenis_id').val();
            let method = $('#_method').val();
            let url = id ? `/dokumen/jenis/${id}` : '/dokumen/jenis';

            let formData = {
                _token: '{{ csrf_token() }}',
                kategori_id: $('#kategori_id').val(),
                kode: $('#kode').val(),
                nama: $('#nama').val(),
                folder_pattern: $('#folder_pattern').val(),
                nomor_format: $('#nomor_format').val(),
                allowed_ext: $('#allowed_ext').val(),
                max_size_mb: $('#max_size_mb').val(),
                perlu_nomor: $('#perlu_nomor').prop('checked') ? 1 : 0
            };

            if (method === 'PUT') {
                formData._method = 'PUT';
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('#btnSaveJenis').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...'
                    );
                },
                success: function(response) {
                    $('#modalJenis').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: method === 'PUT' ? 'Jenis dokumen berhasil diupdate' :
                            'Jenis dokumen berhasil ditambahkan',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadJenis();
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan';

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        errorMessage = '<ul class="text-start">';
                        Object.keys(errors).forEach(key => {
                            errors[key].forEach(error => {
                                errorMessage += `<li>${error}</li>`;
                            });
                        });
                        errorMessage += '</ul>';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        html: errorMessage,
                        confirmButtonColor: '#0d6efd'
                    });
                },
                complete: function() {
                    $('#btnSaveJenis').prop('disabled', false).html(
                        '<i class="bi bi-check-circle me-1"></i>Simpan'
                    );
                }
            });
        }

        function showDetail(id) {
            $.ajax({
                url: `/dokumen/jenis/${id}`,
                type: 'GET',
                success: function(response) {
                    // Find related category
                    const kategori = allKategori.find(k => k.id === response.kategori_id) || {};
                    const color = kategori.warna || '#4154f1';
                    const lightColor = color + '20';

                    let html = `
                        <div class="text-center mb-4 p-4 rounded-3" style="background: linear-gradient(135deg, ${color}15 0%, ${color}05 100%);">
                            <div class="d-inline-block p-4 rounded-circle mb-3" style="background: ${lightColor};">
                                <i class="bi bi-file-earmark-text-fill" style="font-size: 5rem; color: ${color};"></i>
                            </div>
                            <h3 class="fw-bold mb-2">${response.nama}</h3>
                            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2">
                                <span class="badge px-4 py-2" style="background: ${color}; font-size: 1.2rem;">
                                    ${response.kode}
                                </span>
                                <span class="badge px-3 py-2 bg-secondary">
                                    ${kategori.nama || 'Kategori tidak ditemukan'}
                                </span>
                                ${response.perlu_nomor ? 
                                    `<span class="badge px-3 py-2 bg-success">
                                                    <i class="bi bi-hash me-1"></i>Perlu Penomoran
                                                </span>` : ''}
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-123 text-primary me-2"></i>
                                            <small class="text-muted">Format Nomor</small>
                                        </div>
                                        <h5 class="mb-0 fw-bold">${response.nomor_format || '<span class="text-muted">-</span>'}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-folder-fill text-primary me-2"></i>
                                            <small class="text-muted">Pola Folder</small>
                                        </div>
                                        <p class="mb-0 fw-bold font-monospace">${response.folder_pattern}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-file-earmark-fill text-primary me-2"></i>
                                            <small class="text-muted">Ekstensi yang Diizinkan</small>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            ${response.allowed_ext.split(',').map(ext => 
                                                `<span class="badge bg-light text-dark px-2 py-1">.${ext.trim()}</span>`
                                            ).join('')}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-hdd-fill text-primary me-2"></i>
                                            <small class="text-muted">Ukuran Maksimal</small>
                                        </div>
                                        <h5 class="mb-0 fw-bold">${response.max_size_mb} MB</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-calendar-plus text-primary me-2"></i>
                                            <small class="text-muted">Dibuat</small>
                                        </div>
                                        <p class="mb-0 fw-semibold">${formatDateTime(response.created_at)}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-calendar-check text-primary me-2"></i>
                                            <small class="text-muted">Terakhir Diupdate</small>
                                        </div>
                                        <p class="mb-0 fw-semibold">${formatDateTime(response.updated_at)}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    $('#detailJenisContent').html(html);
                    $('#modalDetailJenis').modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat detail jenis dokumen',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        }

        function deleteJenis(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Jenis dokumen akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#4154f1',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, hapus!',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/dokumen/jenis/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Jenis dokumen berhasil dihapus.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadJenis();
                        },
                        error: function(xhr) {
                            let errorMessage = 'Gagal menghapus jenis dokumen';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: errorMessage,
                                confirmButtonColor: '#0d6efd'
                            });
                        }
                    });
                }
            });
        }

        function formatDateTime(dateString) {
            if (!dateString) return '-';
            let date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            let date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }
    </script>
@endpush
