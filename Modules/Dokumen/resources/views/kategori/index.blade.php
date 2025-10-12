@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Manajemen Kategori Dokumen</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dokumen.index') }}">Dokumen</a></li>
                <li class="breadcrumb-item active">Kategori</li>
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
                                        <i class="bi bi-folder-fill text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Kategori</span>
                                        <small class="d-block text-muted fw-normal mt-1">Kelola kategori dokumen untuk
                                            organisasi yang lebih baik</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary btn-lg shadow-sm px-4 py-2"
                                    onclick="showCreateModal()">
                                    <i class="bi bi-plus-circle me-2"></i>Tambah Kategori
                                </button>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="stats-card bg-gradient-primary text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Total Kategori</h6>
                                            <h3 class="mb-0 fw-bold" id="totalKategori">0</h3>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="bi bi-folder-fill" style="font-size: 2.5rem; "></i>
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
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-3 fw-light">Memuat kategori...</p>
                            </div>
                        </div>

                        <!-- Table View -->
                        <div id="tableView" style="display: none;">
                            <table class="table datatable" id="kategoriTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Icon</th>
                                        <th>Nama Kategori</th>
                                        <th>Warna</th>
                                        <th>Jumlah Jenis</th>
                                        <th>Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic content -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="modalKategori" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-primary text-white pb-4 pt-4">
                    <h5 class="modal-title fw-bold" id="modalTitle">
                        <i class="bi bi-folder-plus me-2"></i>Tambah Kategori
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formKategori">
                        @csrf
                        <input type="hidden" id="kategori_id" name="kategori_id">
                        <input type="hidden" id="_method" name="_method" value="POST">

                        <div class="mb-4">
                            <label for="nama" class="form-label fw-semibold">
                                Nama Kategori <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="nama" name="nama"
                                required placeholder="Contoh: Umum, Bahan Tayang, Surat">
                        </div>

                        <div class="mb-4">
                            <label for="icon" class="form-label fw-semibold">Icon (Bootstrap Icons)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-0">
                                    <i class="bi bi-folder" id="iconPreview"></i>
                                </span>
                                <input type="text" class="form-control border-0 bg-light" id="icon"
                                    name="icon" placeholder="folder, file-earmark, archive">
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Lihat icon di: <a href="https://icons.getbootstrap.com/" target="_blank"
                                    class="text-decoration-none">Bootstrap Icons</a>
                            </small>
                        </div>

                        <div class="mb-4">
                            <label for="warna" class="form-label fw-semibold">Warna</label>
                            <div class="d-flex gap-2 flex-wrap mb-2">
                                <button type="button" class="color-preset" data-color="#4154f1"
                                    style="background: #4154f1;"></button>
                                <button type="button" class="color-preset" data-color="#0d6efd"
                                    style="background: #0d6efd;"></button>
                                <button type="button" class="color-preset" data-color="#2ecc71"
                                    style="background: #2ecc71;"></button>
                                <button type="button" class="color-preset" data-color="#e74c3c"
                                    style="background: #e74c3c;"></button>
                                <button type="button" class="color-preset" data-color="#f39c12"
                                    style="background: #f39c12;"></button>
                                <button type="button" class="color-preset" data-color="#1abc9c"
                                    style="background: #1abc9c;"></button>
                                <button type="button" class="color-preset" data-color="#34495e"
                                    style="background: #34495e;"></button>
                                <button type="button" class="color-preset" data-color="#e91e63"
                                    style="background: #e91e63;"></button>
                            </div>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="warna"
                                    name="warna" value="#4154f1" title="Pilih warna">
                                <input type="text" class="form-control bg-light" id="warnaText" value="#4154f1"
                                    readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-eye me-1"></i>Preview
                            </label>
                            <div class="preview-container">
                                <div class="card border-3" id="previewCard" style="border-color: #4154f1;">
                                    <div class="card-body text-center py-4">
                                        <div class="preview-icon-wrapper mb-3">
                                            <i class="bi bi-folder" id="previewIcon"
                                                style="font-size: 4rem; color: #4154f1;"></i>
                                        </div>
                                        <h5 class="mb-0 fw-bold" id="previewNama">Nama Kategori</h5>
                                        <small class="text-muted mt-2 d-block">Urutan akan otomatis</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light border-0 p-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary px-4" id="btnSaveKategori">
                        <i class="bi bi-check-circle me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetailKategori" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-primary text-white pb-4 pt-4">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="bi bi-info-circle me-2"></i>Detail Kategori
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="detailKategoriContent">
                    <!-- Dynamic content -->
                </div>
                <div class="modal-footer bg-light border-0 p-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
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

        .kategori-card {
            position: relative;
            overflow: hidden;
            border-radius: 16px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid #f0f0f0 !important;
        }

        .kategori-card::before {
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

        .kategori-card:hover::before {
            transform: scaleX(1);
        }

        .kategori-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
            border-color: var(--card-color, #4154f1) !important;
        }

        .kategori-card .card-body {
            padding: 2rem 1.5rem;
        }

        .kategori-icon-wrapper {
            width: 90px;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .kategori-card:hover .kategori-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        .kategori-icon {
            font-size: 3rem;
            transition: all 0.3s ease;
        }

        /* Stats Cards - Blue Theme */
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

        /* Color Presets */
        .color-preset {
            width: 40px;
            height: 40px;
            border: 3px solid #fff;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .color-preset:hover {
            transform: scale(1.15);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .color-preset.active {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
        }

        /* Preview Container */
        .preview-container {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 20px;
            border-radius: 12px;
        }

        .preview-icon-wrapper {
            display: inline-block;
            padding: 20px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
        }

        /* Badge */
        .badge-urutan {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 50px;
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

        .color-indicator {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .kategori-card .card-body {
                padding: 1.5rem 1rem;
            }

            .kategori-icon-wrapper {
                width: 70px;
                height: 70px;
            }

            .kategori-icon {
                font-size: 2.5rem;
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        let allKategori = [];
        let viewMode = 'grid';
        let dataTable = null;

        $(document).ready(function() {
            loadKategori();

            // View mode toggle
            $('input[name="viewMode"]').change(function() {
                viewMode = $(this).attr('id') === 'viewGrid' ? 'grid' : 'table';
                toggleView();
            });

            // Color presets
            $('.color-preset').click(function() {
                const color = $(this).data('color');
                $('#warna').val(color);
                $('#warnaText').val(color);
                $('#previewCard').css('border-color', color);
                $('#previewIcon').css('color', color);

                $('.color-preset').removeClass('active');
                $(this).addClass('active');
            });

            // Live preview
            $('#nama').on('input', function() {
                $('#previewNama').text($(this).val() || 'Nama Kategori');
            });

            $('#icon').on('input', function() {
                let iconClass = $(this).val() ? 'bi-' + $(this).val() : 'bi-folder';
                $('#iconPreview').attr('class', 'bi ' + iconClass);
                $('#previewIcon').attr('class', 'bi ' + iconClass);
            });

            $('#warna').on('input', function() {
                let color = $(this).val();
                $('#warnaText').val(color);
                $('#previewCard').css('border-color', color);
                $('#previewIcon').css('color', color);
            });

            $('#btnSaveKategori').click(function() {
                saveKategori();
            });
        });

        function toggleView() {
            if (viewMode === 'grid') {
                $('#gridView').show();
                $('#tableView').hide();
            } else {
                $('#gridView').hide();
                $('#tableView').show();
                renderTable(allKategori);
            }
        }

        function loadKategori() {
            $.ajax({
                url: '{{ route('dokumen.kategori.index') }}',
                type: 'GET',
                success: function(response) {
                    allKategori = response;
                    updateStats(response);
                    renderGrid(response);
                },
                error: function(xhr) {
                    $('#gridView').html(`
                        <div class="col-12 empty-state text-center">
                            <i class="bi bi-exclamation-triangle empty-state-icon"></i>
                            <h4 class="text-muted mb-3">Gagal memuat kategori</h4>
                            <p class="text-muted mb-4">Terjadi kesalahan saat mengambil data</p>
                            <button class="btn btn-primary btn-lg" onclick="loadKategori()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Coba Lagi
                            </button>
                        </div>
                    `);
                }
            });
        }

        function updateStats(data) {
            $('#totalKategori').text(data.length);
        }

        function renderGrid(data) {
            let html = '';

            if (!Array.isArray(data) || data.length === 0) {
                html = `
                    <div class="col-12 empty-state text-center">
                        <i class="bi bi-inbox empty-state-icon"></i>
                        <h4 class="text-muted mb-2">Belum ada kategori</h4>
                        <p class="text-muted mb-4">Mulai dengan menambahkan kategori baru untuk mengorganisir dokumen Anda</p>
                        <button class="btn btn-primary btn-lg px-5" onclick="showCreateModal()">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Kategori Pertama
                        </button>
                    </div>
                `;
            } else {
                data.forEach((item, index) => {
                    let iconClass = item.icon ? 'bi-' + item.icon : 'bi-folder';
                    let color = item.warna || '#4154f1';
                    let lightColor = color + '20';

                    html += `
                        <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                            <div class="card kategori-card h-100 shadow-sm" 
                                 style="--card-color: ${color}; --card-color-light: ${lightColor};">
                                <div class="card-body text-center">
                                    <div class="kategori-icon-wrapper" style="background: ${lightColor};">
                                        <i class="bi ${iconClass} kategori-icon" style="color: ${color};"></i>
                                    </div>
                                    <h5 class="mb-2 fw-bold">${item.nama}</h5>
                                    <span class="badge badge-urutan" style="background: ${lightColor}; color: ${color};">
                                        #${index + 1}
                                    </span>
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
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteKategori(${item.id})" title="Hapus">
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
                let iconClass = item.icon ? 'bi-' + item.icon : 'bi-folder';
                let color = item.warna || '#4154f1';
                let jenisCount = item.jenis ? item.jenis.length : 0;

                tbody += `
                    <tr>
                        <td>${index + 1}</td>
                        <td class="text-center">
                            <i class="bi ${iconClass} icon-cell" style="color: ${color};"></i>
                        </td>
                        <td><strong>${item.nama}</strong></td>
                        <td>
                            <span class="color-indicator" style="background-color: ${color};" title="${color}"></span>
                            <small class="ms-2">${color}</small>
                        </td>
                        <td>${jenisCount} jenis</td>
                        <td>${formatDate(item.created_at)}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" onclick="showDetail(${item.id})" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" onclick="showEditModal(${item.id})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteKategori(${item.id})" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            $('#kategoriTable tbody').html(tbody);

            // Initialize DataTable
            dataTable = new simpleDatatables.DataTable("#kategoriTable", {
                searchable: true,
                fixedHeight: false,
                perPage: 10,
                labels: {
                    placeholder: "Cari kategori...",
                    perPage: "Data per halaman",
                    noRows: "Tidak ada data",
                    info: "Menampilkan {start} sampai {end} dari {rows} data",
                }
            });
        }

        function showCreateModal() {
            $('#modalTitle').html('<i class="bi bi-folder-plus me-2"></i>Tambah Kategori');
            $('#formKategori')[0].reset();
            $('#kategori_id').val('');
            $('#_method').val('POST');

            $('#previewNama').text('Nama Kategori');
            $('#previewIcon').attr('class', 'bi bi-folder').css('color', '#4154f1');
            $('#previewCard').css('border-color', '#4154f1');
            $('#iconPreview').attr('class', 'bi bi-folder');
            $('#warna').val('#4154f1');
            $('#warnaText').val('#4154f1');
            $('.color-preset').removeClass('active');
            $('.color-preset[data-color="#4154f1"]').addClass('active');

            $('#modalKategori').modal('show');
        }

        function showEditModal(id) {
            $.ajax({
                url: `/dokumen/kategori/${id}`,
                type: 'GET',
                success: function(response) {
                    $('#modalTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Kategori');
                    $('#kategori_id').val(response.id);
                    $('#_method').val('PUT');
                    $('#nama').val(response.nama);
                    $('#icon').val(response.icon || '');

                    let color = response.warna || '#4154f1';
                    $('#warna').val(color);
                    $('#warnaText').val(color);

                    $('.color-preset').removeClass('active');
                    $(`.color-preset[data-color="${color}"]`).addClass('active');

                    let iconClass = response.icon ? 'bi-' + response.icon : 'bi-folder';

                    $('#previewNama').text(response.nama);
                    $('#previewIcon').attr('class', 'bi ' + iconClass).css('color', color);
                    $('#previewCard').css('border-color', color);
                    $('#iconPreview').attr('class', 'bi ' + iconClass);

                    $('#modalKategori').modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data kategori',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        }

        function saveKategori() {
            let id = $('#kategori_id').val();
            let method = $('#_method').val();
            let url = id ? `/dokumen/kategori/${id}` : '/dokumen/kategori';

            let formData = {
                _token: '{{ csrf_token() }}',
                nama: $('#nama').val(),
                icon: $('#icon').val(),
                warna: $('#warna').val()
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
                    $('#btnSaveKategori').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...'
                    );
                },
                success: function(response) {
                    $('#modalKategori').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: method === 'PUT' ? 'Kategori berhasil diupdate' :
                            'Kategori berhasil ditambahkan',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadKategori();
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
                    $('#btnSaveKategori').prop('disabled', false).html(
                        '<i class="bi bi-check-circle me-1"></i>Simpan'
                    );
                }
            });
        }

        function showDetail(id) {
            $.ajax({
                url: `/dokumen/kategori/${id}`,
                type: 'GET',
                success: function(response) {
                    let iconClass = response.icon ? 'bi-' + response.icon : 'bi-folder';
                    let color = response.warna || '#4154f1';
                    let lightColor = color + '20';

                    // Cari index/urutan kategori
                    let urutanIndex = allKategori.findIndex(item => item.id === response.id) + 1;

                    let html = `
                        <div class="text-center mb-4 p-4 rounded-3" style="background: linear-gradient(135deg, ${color}15 0%, ${color}05 100%);">
                            <div class="d-inline-block p-4 rounded-circle mb-3" style="background: ${lightColor};">
                                <i class="bi ${iconClass}" style="font-size: 5rem; color: ${color};"></i>
                            </div>
                            <h3 class="fw-bold mb-2">${response.nama}</h3>
                            <span class="badge px-4 py-2" style="background: ${color}; font-size: 1rem;">
                                Urutan: #${urutanIndex}
                            </span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-tag-fill text-primary me-2"></i>
                                            <small class="text-muted">Icon</small>
                                        </div>
                                        <h5 class="mb-0 fw-bold">${response.icon || '-'}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-palette-fill text-primary me-2"></i>
                                            <small class="text-muted">Warna</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 30px; height: 30px; background: ${color}; border-radius: 8px; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                                            <h5 class="mb-0 fw-bold">${color}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-files text-primary me-2"></i>
                                            <small class="text-muted">Jumlah Jenis Dokumen</small>
                                        </div>
                                        <h5 class="mb-0 fw-bold">${response.jenis ? response.jenis.length : 0} jenis</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-sort-numeric-down text-primary me-2"></i>
                                            <small class="text-muted">Posisi Urutan</small>
                                        </div>
                                        <h5 class="mb-0 fw-bold">#${urutanIndex} dari ${allKategori.length}</h5>
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

                    $('#detailKategoriContent').html(html);
                    $('#modalDetailKategori').modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat detail kategori',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        }

        function deleteKategori(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Kategori akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#4154f1',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, hapus!',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Batal',
                reverseButtons: true,
                // customClass: {
                //     confirmButton: 'btn btn-danger px-4',
                //     cancelButton: 'btn btn-primary px-4'
                // }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/dokumen/kategori/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Kategori berhasil dihapus.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadKategori();
                        },
                        error: function(xhr) {
                            let errorMessage = 'Gagal menghapus kategori';
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
