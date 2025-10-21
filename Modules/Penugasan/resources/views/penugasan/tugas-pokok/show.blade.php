@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Tugas Pokok - {{ $pegawai->nama }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Penugasan</li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tugas-pokok.index') }}">Tugas Pokok</a></li>
                <li class="breadcrumb-item active">{{ $pegawai->nama }}</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Pegawai Info -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle-xl me-3">
                                {{ strtoupper(substr($pegawai->nama, 0, 2)) }}
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="mb-1 fw-bold">{{ $pegawai->nama }}</h4>
                                <p class="text-muted mb-1">NIP: {{ $pegawai->nip ?? '-' }}</p>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-primary">{{ $pegawai->jabatan->nama ?? '-' }}</span>
                                    <span class="badge bg-info">{{ $pegawai->bidang->nama ?? '-' }}</span>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('penugasan.tugas-pokok.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Total Tugas Pokok <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $stats['total'] }}</h6>
                                <span class="text-muted small pt-1">tugas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Pending <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-secondary bg-opacity-10">
                                <i class="bi bi-clock text-secondary"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $stats['pending'] }}</h6>
                                <span class="text-muted small pt-1">tugas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Dikerjakan <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10">
                                <i class="bi bi-gear text-warning"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $stats['dikerjakan'] }}</h6>
                                <span class="text-muted small pt-1">tugas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Selesai <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10">
                                <i class="bi bi-check-circle text-success"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $stats['selesai'] }}</h6>
                                <span class="text-muted small pt-1">tugas</span>
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
                                        <i class="bi bi-list-task text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Tugas Pokok</span>
                                        <small class="d-block text-muted fw-normal mt-1">Detail tugas pokok pegawai</small>
                                    </div>
                                </h5>
                            </div>
                        </div>

                        <!-- Filter Form -->
                        <form id="filterForm" action="{{ route('penugasan.tugas-pokok.show', $pegawai->id) }}"
                            method="GET">
                            <div class="row mb-4">
                                <div class="col-lg-2 col-md-3 mb-3">
                                    <label class="form-label">Tahun</label>
                                    <select class="form-select" id="filterTahun" name="tahun"
                                        onchange="this.form.submit()">
                                        @foreach ($tahuns as $t)
                                            <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>
                                                {{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-3 mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="filterStatus" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="Pending" {{ request()->status == 'Pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="Diterima" {{ request()->status == 'Diterima' ? 'selected' : '' }}>
                                            Diterima</option>
                                        <option value="Dikerjakan"
                                            {{ request()->status == 'Dikerjakan' ? 'selected' : '' }}>Dikerjakan</option>
                                        <option value="Selesai" {{ request()->status == 'Selesai' ? 'selected' : '' }}>
                                            Selesai</option>
                                    </select>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="d-flex gap-2 justify-content-between">
                                        <div class="input-group" style="max-width: 400px;">
                                            <input type="text" class="form-control" id="searchTugas" name="search"
                                                value="{{ request()->search }}" placeholder="Cari nama tugas...">
                                            <button class="btn btn-outline-secondary" type="submit">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="bi bi-filter me-1"></i> Filter
                                            </button>
                                            <a href="{{ route('penugasan.tugas-pokok.show', ['id' => $pegawai->id, 'tahun' => $tahun]) }}"
                                                class="btn btn-outline-secondary px-3">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- View Toggle -->
                        <div class="mb-4">
                            <div class="btn-group shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="viewMode" id="viewTable" checked>
                                <label class="btn btn-outline-primary px-4" for="viewTable">
                                    <i class="bi bi-table me-2"></i>Tampilan Tabel
                                </label>
                                <input type="radio" class="btn-check" name="viewMode" id="viewGrid">
                                <label class="btn btn-outline-primary px-4" for="viewGrid">
                                    <i class="bi bi-grid-3x3-gap me-2"></i>Tampilan Kartu
                                </label>
                            </div>
                        </div>

                        <!-- Table View -->
                        <div id="tableView" class="mb-3">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped" id="tugasTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" width="50">#</th>
                                            <th>Nama Tugas</th>
                                            <th>Periode</th>
                                            <th>Status</th>
                                            <th>Bobot</th>
                                            <th>Target</th>
                                            <th>Progress</th>
                                            <th class="text-center" width="150">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tugasPokok as $index => $tugas)
                                            <tr>
                                                <td class="text-center">
                                                    {{ ($tugasPokok->currentPage() - 1) * $tugasPokok->perPage() + $index + 1 }}
                                                </td>
                                                <td>
                                                    <div>
                                                        <span class="fw-semibold">{{ $tugas->nama_tugas }}</span>
                                                        @if ($tugas->deskripsi)
                                                            <br>
                                                            <small
                                                                class="text-muted">{{ Str::limit($tugas->deskripsi, 60) }}</small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <small>
                                                        <i class="bi bi-calendar-range text-info me-1"></i>
                                                        {{ date('d/m/Y', strtotime($tugas->periode_mulai)) }}<br>
                                                        <i class="bi bi-calendar-check text-success me-1"></i>
                                                        {{ date('d/m/Y', strtotime($tugas->periode_selesai)) }}
                                                    </small>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClass = [
                                                            'Pending' => 'bg-secondary',
                                                            'Diterima' => 'bg-info',
                                                            'Dikerjakan' => 'bg-warning',
                                                            'Selesai' => 'bg-success',
                                                            'Tidak_Selesai' => 'bg-danger',
                                                            'Divalidasi' => 'bg-primary',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="badge {{ $statusClass[$tugas->status] ?? 'bg-secondary' }}">
                                                        {{ str_replace('_', ' ', $tugas->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold">{{ number_format($tugas->bobot_persen, 1) }}%
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-muted">{{ number_format($tugas->target_value, 0) }}
                                                        {{ $tugas->satuan }}</span>
                                                </td>
                                                <td>
                                                    @if ($tugas->progress->count() > 0)
                                                        @php
                                                            $latestProgress = $tugas->progress->last();
                                                            $progressPersen = $latestProgress->persentase_progress ?? 0;
                                                        @endphp
                                                        <div class="progress mb-1" style="height: 8px;">
                                                            <div class="progress-bar bg-primary" role="progressbar"
                                                                style="width: {{ $progressPersen }}%"
                                                                aria-valuenow="{{ $progressPersen }}" aria-valuemin="0"
                                                                aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <small
                                                            class="text-muted">{{ number_format($progressPersen, 1) }}%</small>
                                                    @else
                                                        <span class="text-muted">Belum ada progress</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button"
                                                            onclick="showStatusModal({{ $tugas->id }}, '{{ $tugas->status }}')"
                                                            class="btn btn-outline-info" title="Update Status">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                        <button type="button" onclick="viewDetail({{ $tugas->id }})"
                                                            class="btn btn-outline-primary" title="Detail">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                                    <p class="text-muted mt-2">Belum ada tugas pokok untuk tahun ini</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $tugasPokok->withQueryString()->links() }}
                            </div>
                        </div>

                        <!-- Grid View -->
                        <div id="gridView" style="display: none;">
                            <div class="row">
                                @forelse($tugasPokok as $tugas)
                                    @php
                                        $statusClass = [
                                            'Pending' => 'bg-secondary',
                                            'Diterima' => 'bg-info',
                                            'Dikerjakan' => 'bg-warning',
                                            'Selesai' => 'bg-success',
                                            'Tidak_Selesai' => 'bg-danger',
                                            'Divalidasi' => 'bg-primary',
                                        ];
                                    @endphp
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card tugas-card h-100 shadow-sm">
                                            <div class="card-header bg-transparent">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <h6 class="card-title mb-0 text-primary fw-bold">
                                                        {{ Str::limit($tugas->nama_tugas, 40) }}
                                                    </h6>
                                                    <span
                                                        class="badge {{ $statusClass[$tugas->status] ?? 'bg-secondary' }}">
                                                        {{ str_replace('_', ' ', $tugas->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text small d-flex align-items-center mb-2">
                                                    <i class="bi bi-calendar-range text-info me-2"></i>
                                                    <span>
                                                        {{ date('d M Y', strtotime($tugas->periode_mulai)) }} -
                                                        {{ date('d M Y', strtotime($tugas->periode_selesai)) }}
                                                    </span>
                                                </p>

                                                <p class="card-text small d-flex align-items-center mb-2">
                                                    <i class="bi bi-percent text-warning me-2"></i>
                                                    <span><strong>Bobot:</strong>
                                                        {{ number_format($tugas->bobot_persen, 1) }}%</span>
                                                </p>

                                                <p class="card-text small d-flex align-items-center mb-2">
                                                    <i class="bi bi-bullseye text-success me-2"></i>
                                                    <span><strong>Target:</strong>
                                                        {{ number_format($tugas->target_value, 0) }}
                                                        {{ $tugas->satuan }}</span>
                                                </p>

                                                @if ($tugas->progress->count() > 0)
                                                    @php
                                                        $latestProgress = $tugas->progress->last();
                                                        $progressPersen = $latestProgress->persentase_progress ?? 0;
                                                    @endphp
                                                    <div class="mb-2">
                                                        <small class="text-muted">Progress:</small>
                                                        <div class="progress mb-1" style="height: 8px;">
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $progressPersen }}%"
                                                                aria-valuenow="{{ $progressPersen }}" aria-valuemin="0"
                                                                aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <small
                                                            class="text-muted">{{ number_format($progressPersen, 1) }}%</small>
                                                    </div>
                                                @endif

                                                @if ($tugas->deskripsi)
                                                    <p class="card-text small text-muted mt-3">
                                                        {{ Str::limit($tugas->deskripsi, 100) }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <div class="btn-group btn-group-sm w-100">
                                                    <button
                                                        onclick="showStatusModal({{ $tugas->id }}, '{{ $tugas->status }}')"
                                                        class="btn btn-outline-info" title="Update Status">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </button>
                                                    <button onclick="viewDetail({{ $tugas->id }})"
                                                        class="btn btn-outline-primary" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">Belum ada tugas pokok untuk tahun ini</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Pagination for Grid View -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $tugasPokok->withQueryString()->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Update Status -->
    <div class="modal fade" id="modalUpdateStatus" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status Tugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formUpdateStatus">
                        @csrf
                        <input type="hidden" id="status_tugas_id" name="tugas_id">

                        <div class="mb-3">
                            <label for="status" class="form-label">Status Tugas</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Pending">Pending</option>
                                <option value="Diterima">Diterima</option>
                                <option value="Dikerjakan">Dikerjakan</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Tidak_Selesai">Tidak Selesai</option>
                                <option value="Divalidasi">Divalidasi</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="btnUpdateStatus">
                        <i class="bi bi-check-circle me-1"></i> Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Card Styles */
        .tugas-card {
            position: relative;
            overflow: hidden;
            border-radius: 12px !important;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
        }

        .tugas-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        /* Table Styles */
        #tugasTable thead th {
            font-weight: 600;
            color: #495057;
        }

        #tugasTable tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.03);
        }

        /* Avatar Styles */
        .avatar-circle-xl {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 28px;
        }

        /* Card Icon */
        .card-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }

        /* Progress Bar */
        .progress {
            background-color: #e9ecef;
        }

        .progress-bar {
            background-color: #0d6efd;
        }

        /* Icon Box */
        .icon-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
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
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // View mode toggle
            $('input[name="viewMode"]').change(function() {
                let viewMode = $(this).attr('id');
                if (viewMode === 'viewGrid') {
                    $('#gridView').show();
                    $('#tableView').hide();
                    localStorage.setItem('tugas_view_mode', 'grid');
                } else {
                    $('#tableView').show();
                    $('#gridView').hide();
                    localStorage.setItem('tugas_view_mode', 'table');
                }
            });

            // Restore view mode from localStorage
            const savedViewMode = localStorage.getItem('tugas_view_mode');
            if (savedViewMode === 'grid') {
                $('#viewGrid').prop('checked', true).trigger('change');
            }

            // Update Status button handler
            $('#btnUpdateStatus').click(function() {
                const tugasId = $('#status_tugas_id').val();
                const status = $('#status').val();
                updateStatus(tugasId, status);
            });
        });

        // Function to show status modal
        function showStatusModal(id, currentStatus) {
            $('#status_tugas_id').val(id);
            $('#status').val(currentStatus);
            $('#modalUpdateStatus').modal('show');
        }

        // Function to update status
        function updateStatus(id, status) {
            $.ajax({
                url: "{{ url('penugasan/tugas-pokok') }}/" + id + "/update-status",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status
                },
                beforeSend: function() {
                    $('#btnUpdateStatus').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                    );
                    $('#btnUpdateStatus').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalUpdateStatus').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Status tugas berhasil diperbarui',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat memperbarui status';
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
                    $('#btnUpdateStatus').html('<i class="bi bi-check-circle me-1"></i> Update Status');
                    $('#btnUpdateStatus').prop('disabled', false);
                }
            });
        }

        // Function to view detail
        function viewDetail(id) {
            // Implement detail view functionality
            alert('Detail view for task ID: ' + id);
        }
    </script>
@endpush
