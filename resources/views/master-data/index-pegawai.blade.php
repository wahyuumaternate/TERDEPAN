@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Data Pegawai</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Master Data</li>
                <li class="breadcrumb-item active">Pegawai</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Total Pegawai</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10">
                                <i class="bi bi-people text-primary"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $data->count() }}</h6>
                                <span class="text-muted small pt-1">pegawai</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Status Aktif</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10">
                                <i class="bi bi-check-circle text-success"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $data->where('status_aktif', 'Aktif')->count() }}</h6>
                                <span class="text-muted small pt-1">aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">PNS</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10">
                                <i class="bi bi-award text-info"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $data->where('status_kepegawaian', 'PNS')->count() }}</h6>
                                <span class="text-muted small pt-1">pegawai</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Non PNS</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10">
                                <i class="bi bi-person-badge text-warning"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">
                                    {{ $data->whereIn('status_kepegawaian', ['PPPK', 'Kontrak'])->count() }}</h6>
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
                                        <i class="bi bi-people-fill text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Pegawai</span>
                                        <small class="d-block text-muted fw-normal mt-1">Kelola data master pegawai</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <a href="{{ route('master.pegawai.create') }}"
                                    class="btn btn-primary btn-lg shadow-sm px-4 py-2">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Pegawai
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
                                        placeholder="Cari nama, email, atau nomor identitas...">
                                </div>
                            </div>
                            <div class="col-lg-2 mb-3">
                                <select class="form-select" id="filterBidang">
                                    <option value="">Semua Bidang</option>
                                    @foreach ($data->pluck('bidang')->unique() as $bidang)
                                        @if ($bidang)
                                            <option value="{{ $bidang->nama }}">{{ $bidang->nama }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 mb-3">
                                <select class="form-select" id="filterJabatan">
                                    <option value="">Semua Jabatan</option>
                                    @foreach ($data->pluck('jabatan')->unique() as $jabatan)
                                        @if ($jabatan)
                                            <option value="{{ $jabatan->nama }}">{{ $jabatan->nama }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 mb-3">
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                    <option value="Cuti">Cuti</option>
                                    <option value="Pensiun">Pensiun</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tabel Pegawai -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="pegawaiTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="10%">Foto</th>
                                        <th width="15%">Identitas</th>
                                        <th width="20%">Nama Lengkap</th>
                                        <th width="15%">Jabatan</th>
                                        <th width="15%">Bidang</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $pegawai)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="text-center">
                                                @if ($pegawai->foto_profile_path)
                                                    <img src="{{ asset("storage/" . $pegawai->foto_profile_path) }}"
                                                        alt="{{ $pegawai->nama }}" class="rounded-circle d-flex align-items-center justify-content-center"
                                                        style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                        style="width: 40px; height: 40px;">
                                                        <i class="bi bi-person text-primary"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $pegawai->nomor_identitas }}</div>
                                                <small class="text-muted">{{ $pegawai->tipe_identitas }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-bold">
                                                    {{ $pegawai->gelar_depan }} {{ $pegawai->nama }}
                                                    {{ $pegawai->gelar_belakang }}
                                                </div>
                                                <small class="text-muted">{{ $pegawai->email }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                    {{ $pegawai->jabatan->nama ?? '-' }}
                                                </span>
                                                @if ($pegawai->pangkat)
                                                    <br><small class="text-muted">{{ $pegawai->pangkat }}
                                                        {{ $pegawai->golongan }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                    {{ $pegawai->bidang->nama ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                @switch($pegawai->status_aktif)
                                                    @case('Aktif')
                                                        <span class="badge bg-success">{{ $pegawai->status_aktif }}</span>
                                                    @break

                                                    @case('Nonaktif')
                                                        <span class="badge bg-danger">{{ $pegawai->status_aktif }}</span>
                                                    @break

                                                    @case('Cuti')
                                                        <span class="badge bg-warning">{{ $pegawai->status_aktif }}</span>
                                                    @break

                                                    @case('Pensiun')
                                                        <span class="badge bg-secondary">{{ $pegawai->status_aktif }}</span>
                                                    @break

                                                    @default
                                                        <span class="badge bg-light text-dark">{{ $pegawai->status_aktif }}</span>
                                                @endswitch
                                                <br><small class="text-muted">{{ $pegawai->status_kepegawaian }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('master.pegawai.show', $pegawai->id) }}"
                                                        class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmDelete({{ $pegawai->id }})" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="bi bi-people display-1 d-block mb-3"></i>
                                                        <h5>Belum ada data pegawai</h5>
                                                        <p>Silakan tambahkan data pegawai baru</p>
                                                        <a href="{{ route('master.pegawai.create') }}"
                                                            class="btn btn-primary">
                                                            <i class="bi bi-plus-circle me-1"></i> Tambah Pegawai
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
                        <p>Apakah Anda yakin ingin menghapus data pegawai ini?</p>
                        <form id="formDeletePegawai" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" id="delete_pegawai_id" name="pegawai_id">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Batal
                        </button>
                        <button type="button" class="btn btn-danger" id="btnDeletePegawai">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Table Styles */
            #pegawaiTable thead th {
                font-weight: 600;
                color: #495057;
                background-color: #f8f9fa;
            }

            #pegawaiTable tbody tr:hover {
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
                    $('#pegawaiTable tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                });

                // Filter functionality
                $('#filterBidang, #filterJabatan, #filterStatus').on('change', function() {
                    filterTable();
                });

                // Delete Pegawai button handler
                $('#btnDeletePegawai').click(function() {
                    const pegawaiId = $('#delete_pegawai_id').val();
                    deletePegawai(pegawaiId);
                });
            });

            function filterTable() {
                var bidangFilter = $('#filterBidang').val().toLowerCase();
                var jabatanFilter = $('#filterJabatan').val().toLowerCase();
                var statusFilter = $('#filterStatus').val().toLowerCase();

                $('#pegawaiTable tbody tr').each(function() {
                    var bidang = $(this).find('td:eq(5)').text().toLowerCase();
                    var jabatan = $(this).find('td:eq(4)').text().toLowerCase();
                    var status = $(this).find('td:eq(6)').text().toLowerCase();

                    var showRow = true;

                    if (bidangFilter && bidang.indexOf(bidangFilter) === -1) {
                        showRow = false;
                    }
                    if (jabatanFilter && jabatan.indexOf(jabatanFilter) === -1) {
                        showRow = false;
                    }
                    if (statusFilter && status.indexOf(statusFilter) === -1) {
                        showRow = false;
                    }

                    $(this).toggle(showRow);
                });
            }

            // Function to confirm delete
            function confirmDelete(id) {
                $('#delete_pegawai_id').val(id);
                $('#modalDeleteConfirm').modal('show');
            }

            // Function to delete pegawai
            function deletePegawai(id) {
                $.ajax({
                    url: "{{ url('master/pegawai') }}/" + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    beforeSend: function() {
                        $('#btnDeletePegawai').html(
                            '<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...'
                        );
                        $('#btnDeletePegawai').prop('disabled', true);
                    },
                    success: function(response) {
                        $('#modalDeleteConfirm').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Data pegawai berhasil dihapus',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan saat menghapus data pegawai';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMessage
                        });
                    },
                    complete: function() {
                        // Reset button
                        $('#btnDeletePegawai').html('<i class="bi bi-trash me-1"></i> Hapus');
                        $('#btnDeletePegawai').prop('disabled', false);
                    }
                });
            }
        </script>
    @endpush
