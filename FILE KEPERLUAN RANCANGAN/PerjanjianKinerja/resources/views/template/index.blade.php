@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Template Perjanjian Kinerja</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Perjanjian Kinerja</li>
                <li class="breadcrumb-item active">Template</li>
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
                                        <i class="bi bi-file-earmark-ruled-fill text-primary"
                                            style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Template PK</span>
                                        <small class="d-block text-muted fw-normal mt-1">Kelola template dokumen perjanjian
                                            kinerja</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <a href="{{ route('perjanjian-kinerja.template.create') }}"
                                    class="btn btn-primary btn-lg shadow-sm px-4 py-2">
                                    <i class="bi bi-plus-circle me-1"></i> Buat Template Baru
                                </a>
                            </div>
                        </div>

                        <!-- Filter Section -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Jabatan</label>
                                <select class="form-select" id="filterJabatan">
                                    <option value="">Semua Jabatan</option>
                                    @foreach ($jabatans as $jabatan)
                                        <option value="{{ $jabatan->id }}">{{ $jabatan->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tahun</label>
                                <select class="form-select" id="filterTahun">
                                    <option value="">Semua Tahun</option>
                                    @foreach ($tahuns as $tahun)
                                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Pencarian</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchTemplate"
                                        placeholder="Cari template...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- View Toggle -->
                        <div class="mb-3">
                            <div class="btn-group shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="viewMode" id="viewTable" checked>
                                <label class="btn btn-outline-primary px-4" for="viewTable">
                                    <i class="bi bi-table me-2"></i>Table View
                                </label>
                                <input type="radio" class="btn-check" name="viewMode" id="viewGrid">
                                <label class="btn btn-outline-primary px-4" for="viewGrid">
                                    <i class="bi bi-grid-3x3-gap me-2"></i>Grid View
                                </label>
                            </div>
                        </div>

                        <!-- Table View -->
                        <div id="tableView">
                            <div class="table-responsive">
                                <table class="table datatable" id="templateTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Kode Template</th>
                                            <th scope="col">Nama Template</th>
                                            <th scope="col">Jabatan</th>
                                            <th scope="col">Tahun</th>
                                            <th scope="col">Versi</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Sections</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="templateTableBody">
                                        @forelse($templates as $index => $template)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><code class="text-primary">{{ $template->kode_template }}</code></td>
                                                <td>
                                                    <strong>{{ $template->nama_template }}</strong>
                                                    <br><small class="text-muted">{{ $template->page_size }} -
                                                        {{ $template->orientation }}</small>
                                                </td>
                                                <td><span class="badge bg-info">{{ $template->jabatan->nama }}</span></td>
                                                <td>{{ $template->tahun }}</td>
                                                <td><small>v{{ $template->versi }}</small></td>
                                                <td>
                                                    @if ($template->is_active)
                                                        <span class="badge bg-success">Aktif</span>
                                                    @else
                                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $template->sections->count() }}
                                                        sections</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('perjanjian-kinerja.template.show', $template->id) }}"
                                                            class="btn btn-outline-primary" title="Detail">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('perjanjian-kinerja.template.edit', $template->id) }}"
                                                            class="btn btn-outline-warning" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        @if (!$template->is_active)
                                                            <button onclick="activateTemplate({{ $template->id }})"
                                                                class="btn btn-outline-success" title="Aktifkan">
                                                                <i class="bi bi-check-circle"></i>
                                                            </button>
                                                        @endif
                                                        <button onclick="duplicateTemplate({{ $template->id }})"
                                                            class="btn btn-outline-info" title="Duplikat">
                                                            <i class="bi bi-files"></i>
                                                        </button>
                                                        @if ($template->perjanjianKinerja->count() == 0)
                                                            <button onclick="deleteTemplate({{ $template->id }})"
                                                                class="btn btn-outline-danger" title="Hapus">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-5">
                                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                                    <p class="text-muted mt-2">Belum ada template</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Grid View -->
                        <div id="gridView" style="display: none;">
                            <div class="row" id="templateGridBody">
                                @forelse($templates as $template)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card template-card h-100 shadow-sm border-0">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <code
                                                            class="text-primary fw-bold">{{ $template->kode_template }}</code>
                                                        @if ($template->is_active)
                                                            <span class="badge bg-success ms-2">Aktif</span>
                                                        @else
                                                            <span class="badge bg-secondary ms-2">Tidak Aktif</span>
                                                        @endif
                                                    </div>
                                                    <span class="badge bg-info">{{ $template->tahun }}</span>
                                                </div>
                                                <h6 class="card-title mb-2" style="min-height: 48px;">
                                                    {{ $template->nama_template }}</h6>
                                                <p class="card-text small text-muted">
                                                    <i class="bi bi-briefcase"></i> {{ $template->jabatan->nama }}<br>
                                                    <i class="bi bi-file-earmark"></i> {{ $template->page_size }} -
                                                    {{ $template->orientation }}<br>
                                                    <i class="bi bi-list-ul"></i> {{ $template->sections->count() }}
                                                    sections<br>
                                                    <i class="bi bi-diagram-3"></i>
                                                    {{ $template->perjanjianKinerja->count() }} PK
                                                </p>
                                                <div class="badge bg-light text-dark border">
                                                    <i class="bi bi-layers"></i> Versi {{ $template->versi }}
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <div class="btn-group btn-group-sm w-100">
                                                    <a href="{{ route('perjanjian-kinerja.template.show', $template->id) }}"
                                                        class="btn btn-outline-primary" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('perjanjian-kinerja.template.edit', $template->id) }}"
                                                        class="btn btn-outline-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button onclick="duplicateTemplate({{ $template->id }})"
                                                        class="btn btn-outline-info" title="Duplikat">
                                                        <i class="bi bi-files"></i>
                                                    </button>
                                                    @if ($template->perjanjianKinerja->count() == 0)
                                                        <button onclick="deleteTemplate({{ $template->id }})"
                                                            class="btn btn-outline-danger" title="Hapus">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">Belum ada template</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Duplicate -->
    <div class="modal fade" id="modalDuplicateTemplate" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Duplikat Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formDuplicateTemplate">
                        @csrf
                        <input type="hidden" id="duplicate_template_id">
                        <div class="mb-3">
                            <label for="kode_template" class="form-label">Kode Template <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode_template" name="kode_template" required>
                            <div class="form-text">Format: TPK-KABAN-2025</div>
                        </div>
                        <div class="mb-3">
                            <label for="nama_template" class="form-label">Nama Template <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_template" name="nama_template" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnDuplicateTemplate">
                        <i class="bi bi-files me-1"></i> Duplikat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Card Styles */
        .template-card {
            position: relative;
            overflow: hidden;
            border-radius: 16px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid #f0f0f0 !important;
        }

        .template-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
        }

        /* Table Styles */
        .datatable-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        /* Icon Box */
        .icon-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
        }
    </style>
@endsection

@push('scripts')
    <script>
        let dataTable = null;

        $(document).ready(function() {
            // Initialize DataTable
            initializeDataTable();

            // View mode toggle
            $('input[name="viewMode"]').change(function() {
                let viewMode = $(this).attr('id');
                if (viewMode === 'viewGrid') {
                    $('#gridView').show();
                    $('#tableView').hide();
                    if (dataTable) {
                        dataTable.destroy();
                        dataTable = null;
                    }
                } else {
                    $('#tableView').show();
                    $('#gridView').hide();
                    setTimeout(function() {
                        if (!dataTable) {
                            initializeDataTable();
                        }
                    }, 100);
                }
            });

            // Duplicate button handler
            $('#btnDuplicateTemplate').click(function() {
                let templateId = $('#duplicate_template_id').val();
                let formData = {
                    _token: '{{ csrf_token() }}',
                    kode_template: $('#kode_template').val(),
                    nama_template: $('#nama_template').val()
                };

                $.ajax({
                    url: `/perjanjian-kinerja/template/${templateId}/duplicate`,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#modalDuplicateTemplate').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Template berhasil diduplikasi',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan saat duplikasi template';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMessage
                        });
                    }
                });
            });

            // Reset modal on close
            $('#modalDuplicateTemplate').on('hidden.bs.modal', function() {
                $('#formDuplicateTemplate')[0].reset();
            });
        });

        function initializeDataTable() {
            if (dataTable) {
                dataTable.destroy();
                dataTable = null;
            }

            dataTable = new simpleDatatables.DataTable("#templateTable", {
                searchable: true,
                fixedHeight: false,
                perPage: 10,
                labels: {
                    placeholder: "Cari template...",
                    perPage: "Data per halaman",
                    noRows: "Tidak ada data",
                    info: "Menampilkan {start} sampai {end} dari {rows} data",
                }
            });
        }

        function activateTemplate(id) {
            Swal.fire({
                title: 'Aktifkan Template?',
                text: "Template lain untuk jabatan dan tahun yang sama akan dinonaktifkan",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Ya, Aktifkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/perjanjian-kinerja/template/${id}/activate`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Template berhasil diaktifkan',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Gagal mengaktifkan template'
                            });
                        }
                    });
                }
            });
        }

        function duplicateTemplate(id) {
            $.ajax({
                url: `/perjanjian-kinerja/template/${id}`,
                type: 'GET',
                success: function(response) {
                    $('#duplicate_template_id').val(id);
                    $('#kode_template').val(response.kode_template + '-COPY');
                    $('#nama_template').val(response.nama_template + ' (Copy)');
                    $('#modalDuplicateTemplate').modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data template'
                    });
                }
            });
        }

        function deleteTemplate(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Template akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/perjanjian-kinerja/template/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Template berhasil dihapus.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Gagal menghapus template'
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush
