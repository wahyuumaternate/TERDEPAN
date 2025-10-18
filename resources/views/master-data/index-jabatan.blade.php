@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Data Jabatan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Master Data</li>
                <li class="breadcrumb-item active">Jabatan</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Total Jabatan</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10">
                                <i class="bi bi-diagram-3 text-primary"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $data->count() }}</h6>
                                <span class="text-muted small pt-1">jabatan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Jabatan Struktural</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10">
                                <i class="bi bi-person-badge text-success"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $data->where('is_struktural', true)->count() }}</h6>
                                <span class="text-muted small pt-1">struktural</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Jabatan Fungsional</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10">
                                <i class="bi bi-gear text-info"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $data->where('is_struktural', false)->count() }}</h6>
                                <span class="text-muted small pt-1">fungsional</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Total Pegawai</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10">
                                <i class="bi bi-people text-warning"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">
                                    {{ $data->sum(function ($jabatan) {return $jabatan->pegawai->count();}) }}</h6>
                                <span class="text-muted small pt-1">pegawai</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="card-title mb-1 d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 rounded-3 p-2 me-3">
                                        <i class="bi bi-diagram-3-fill text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Jabatan</span>
                                        <small class="d-block text-muted fw-normal mt-1">Kelola data master jabatan</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <a href="{{ route('master.jabatan.create') }}"
                                    class="btn btn-primary btn-lg shadow-sm px-4 py-2">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Jabatan
                                </a>
                            </div>
                        </div>

                        <!-- Search and Filter -->
                        <div class="row mb-4">
                            <div class="col-lg-6 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" id="searchInput"
                                        placeholder="Cari kode atau nama jabatan...">
                                </div>
                            </div>
                            <div class="col-lg-2 mb-3">
                                <select class="form-select" id="filterLevel">
                                    <option value="">Semua Level</option>
                                    <option value="1">Level 1</option>
                                    <option value="2">Level 2</option>
                                    <option value="3">Level 3</option>
                                    <option value="4">Level 4</option>
                                    <option value="5">Level 5</option>
                                    <option value="6">Level 6</option>
                                </select>
                            </div>
                            <div class="col-lg-2 mb-3">
                                <select class="form-select" id="filterTipe">
                                    <option value="">Semua Tipe</option>
                                    <option value="struktural">Struktural</option>
                                    <option value="fungsional">Fungsional</option>
                                </select>
                            </div>
                            <div class="col-lg-2 mb-3">
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tabel Jabatan -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="jabatanTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="10%">Kode</th>
                                        <th width="25%">Nama Jabatan</th>
                                        <th width="10%">Level</th>
                                        <th width="15%">Tipe</th>
                                        <th width="10%">Pegawai</th>
                                        <th width="10%">Status</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $jabatan)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $jabatan->kode }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $jabatan->nama }}</div>
                                                @if ($jabatan->bebas_nilai_kinerja)
                                                    <small class="text-info">
                                                        <i class="bi bi-star me-1"></i>Bebas Nilai Kinerja
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                                    Level {{ $jabatan->level }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($jabatan->is_struktural)
                                                    <span class="badge bg-success">Struktural</span>
                                                @else
                                                    <span class="badge bg-info">Fungsional</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                    {{ $jabatan->pegawai->count() }} pegawai
                                                </span>
                                            </td>
                                            <td>
                                                @if ($jabatan->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-danger">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('master.jabatan.show', $jabatan->id) }}"
                                                        class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if ($jabatan->pegawai->count() == 0)
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="confirmDelete({{ $jabatan->id }})" title="Hapus">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-secondary disabled"
                                                            title="Tidak dapat dihapus karena masih memiliki pegawai">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-diagram-3 display-1 d-block mb-3"></i>
                                                    <h5>Belum ada data jabatan</h5>
                                                    <p>Silakan tambahkan data jabatan baru</p>
                                                    <a href="{{ route('master.jabatan.create') }}"
                                                        class="btn btn-primary">
                                                        <i class="bi bi-plus-circle me-1"></i> Tambah Jabatan
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Delete Confirmation -->
    <div class="modal fade" id="modalDeleteConfirm" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <p>Apakah Anda yakin ingin menghapus data jabatan ini?</p>
                    <form id="formDeleteJabatan" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" id="delete_jabatan_id" name="jabatan_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-danger" id="btnDeleteJabatan">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Table Styles */
        #jabatanTable thead th {
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
        }

        #jabatanTable tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.03);
        }

        /* Card Icon */
        .card-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }

        /* Form controls */
        .form-select,
        .form-control {
            border-color: #dee2e6;
            padding: 0.375rem 0.75rem;
            min-height: 38px;
        }

        .form-select:focus,
        .form-control:focus {
            border-color: #5F71E4;
            box-shadow: 0 0 0 0.25rem rgba(95, 113, 228, 0.25);
        }

        /* Dashboard cards */
        .info-card {
            border-radius: 12px;
            overflow: hidden;
        }

        .info-card .card-body {
            padding: 1.25rem;
        }

        .info-card h6 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }

        /* Buttons in card footer */
        .btn-group .btn {
            padding: 0.375rem 0.75rem;
        }

        /* Icon Box */
        .icon-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
        }

        /* Search Input */
        .input-group-text {
            background-color: #f8f9fa;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Search functionality
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#jabatanTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Filter functionality
            $('#filterLevel, #filterTipe, #filterStatus').on('change', function() {
                filterTable();
            });

            // Delete Jabatan button handler
            $('#btnDeleteJabatan').click(function() {
                const jabatanId = $('#delete_jabatan_id').val();
                deleteJabatan(jabatanId);
            });
        });

        function filterTable() {
            var levelFilter = $('#filterLevel').val().toLowerCase();
            var tipeFilter = $('#filterTipe').val().toLowerCase();
            var statusFilter = $('#filterStatus').val().toLowerCase();

            $('#jabatanTable tbody tr').each(function() {
                var level = $(this).find('td:eq(3)').text().toLowerCase();
                var tipe = $(this).find('td:eq(4)').text().toLowerCase();
                var status = $(this).find('td:eq(6)').text().toLowerCase();

                var showRow = true;

                if (levelFilter && level.indexOf(levelFilter) === -1) {
                    showRow = false;
                }
                if (tipeFilter && tipe.indexOf(tipeFilter) === -1) {
                    showRow = false;
                }
                if (statusFilter && status.indexOf(statusFilter === '1' ? 'aktif' : 'nonaktif') === -1) {
                    showRow = false;
                }

                $(this).toggle(showRow);
            });
        }

        // Function to confirm delete
        function confirmDelete(id) {
            $('#delete_jabatan_id').val(id);
            $('#modalDeleteConfirm').modal('show');
        }

        // Function to delete jabatan
        function deleteJabatan(id) {
            $.ajax({
                url: "{{ url('master/jabatan') }}/" + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                beforeSend: function() {
                    $('#btnDeleteJabatan').html(
                        '<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...'
                    );
                    $('#btnDeleteJabatan').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalDeleteConfirm').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data jabatan berhasil dihapus',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat menghapus data jabatan';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errorMessage
                    });
                },
                complete: function() {
                    // Reset button
                    $('#btnDeleteJabatan').html('<i class="bi bi-trash me-1"></i> Hapus');
                    $('#btnDeleteJabatan').prop('disabled', false);
                }
            });
        }
    </script>
@endpush
