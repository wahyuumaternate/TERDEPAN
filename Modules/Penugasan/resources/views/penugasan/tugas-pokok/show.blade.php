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
                        <!-- Header with Tabs -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="card-title mb-1 d-flex align-items-center">
                                        <div class="icon-box bg-primary bg-opacity-10 rounded-3 p-2 me-3">
                                            <i class="bi bi-list-task text-primary" style="font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold">Daftar Tugas</span>
                                            <small class="d-block text-muted fw-normal mt-1">Detail tugas pegawai</small>
                                        </div>
                                    </h5>
                                </div>
                            </div>

                            <!-- Tab Navigation with Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center">
                                <ul class="nav nav-tabs nav-tabs-bordered flex-grow-1" id="tugasTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="tugas-pokok-tab" data-bs-toggle="tab"
                                            data-bs-target="#tugas-pokok-content" type="button" role="tab"
                                            aria-controls="tugas-pokok-content" aria-selected="true">
                                            <i class="bi bi-file-earmark-text me-2"></i>Tugas Pokok
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tugas-harian-tab" data-bs-toggle="tab"
                                            data-bs-target="#tugas-harian-content" type="button" role="tab"
                                            aria-controls="tugas-harian-content" aria-selected="false">
                                            <i class="bi bi-calendar-day me-2"></i>Tugas Harian
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tugas-tambahan-tab" data-bs-toggle="tab"
                                            data-bs-target="#tugas-tambahan-content" type="button" role="tab"
                                            aria-controls="tugas-tambahan-content" aria-selected="false">
                                            <i class="bi bi-plus-circle me-2"></i>Tugas Tambahan
                                        </button>
                                    </li>
                                </ul>
                                <div class="d-flex gap-2 ms-3 mb-2">
                                    <button type="button" class="btn btn-success" onclick="showBerikanTugasModal()">
                                        <i class="bi bi-send me-1"></i> Berikan Tugas
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="showBuatTugasModal()">
                                        <i class="bi bi-plus-lg me-1"></i> Buat Tugas
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content" id="tugasTabContent">
                            <!-- Tugas Pokok Tab -->
                            <div class="tab-pane fade show active" id="tugas-pokok-content" role="tabpanel"
                                aria-labelledby="tugas-pokok-tab">
                                <!-- Filter Form -->
                                <form id="filterForm" action="{{ route('penugasan.tugas-pokok.show', $pegawai->id) }}"
                                    method="GET">
                                    <div class="row mb-4">
                                        <div class="col-lg-2 col-md-3 mb-3">
                                            <label class="form-label">Tahun</label>
                                            <select class="form-select" id="filterTahun" name="tahun"
                                                onchange="this.form.submit()">
                                                @foreach ($tahuns as $t)
                                                    <option value="{{ $t }}"
                                                        {{ $t == $tahun ? 'selected' : '' }}>
                                                        {{ $t }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-2 col-md-3 mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" id="filterStatus" name="status">
                                                <option value="">Semua Status</option>
                                                <option value="Pending"
                                                    {{ request()->status == 'Pending' ? 'selected' : '' }}>
                                                    Pending</option>
                                                <option value="Diterima"
                                                    {{ request()->status == 'Diterima' ? 'selected' : '' }}>
                                                    Diterima</option>
                                                <option value="Dikerjakan"
                                                    {{ request()->status == 'Dikerjakan' ? 'selected' : '' }}>Dikerjakan
                                                </option>
                                                <option value="Selesai"
                                                    {{ request()->status == 'Selesai' ? 'selected' : '' }}>
                                                    Selesai</option>
                                            </select>
                                        </div>

                                        <div class="col-12 mt-2">
                                            <div class="d-flex gap-2 justify-content-between">
                                                <div class="input-group" style="max-width: 400px;">
                                                    <input type="text" class="form-control" id="searchTugas"
                                                        name="search" value="{{ request()->search }}"
                                                        placeholder="Cari nama tugas...">
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
                                                            <div class="fw-bold">
                                                                {{ number_format($tugas->bobot_persen, 1) }}%
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="text-muted">{{ number_format($tugas->target_value, 0) }}
                                                                {{ $tugas->satuan }}</span>
                                                        </td>
                                                        <td>
                                                            @if ($tugas->progress->count() > 0)
                                                                @php
                                                                    $latestProgress = $tugas->progress->last();
                                                                    $progressPersen =
                                                                        $latestProgress->persentase_progress ?? 0;
                                                                @endphp
                                                                <div class="progress mb-1" style="height: 8px;">
                                                                    <div class="progress-bar bg-primary"
                                                                        role="progressbar"
                                                                        style="width: {{ $progressPersen }}%"
                                                                        aria-valuenow="{{ $progressPersen }}"
                                                                        aria-valuemin="0" aria-valuemax="100">
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
                                                                <button type="button"
                                                                    onclick="viewDetail({{ $tugas->id }})"
                                                                    class="btn btn-outline-primary" title="Detail">
                                                                    <i class="bi bi-eye"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center py-5">
                                                            <i class="bi bi-inbox"
                                                                style="font-size: 3rem; color: #ccc;"></i>
                                                            <p class="text-muted mt-2">Belum ada tugas pokok untuk tahun
                                                                ini</p>
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
                                                                $progressPersen =
                                                                    $latestProgress->persentase_progress ?? 0;
                                                            @endphp
                                                            <div class="mb-2">
                                                                <small class="text-muted">Progress:</small>
                                                                <div class="progress mb-1" style="height: 8px;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        style="width: {{ $progressPersen }}%"
                                                                        aria-valuenow="{{ $progressPersen }}"
                                                                        aria-valuemin="0" aria-valuemax="100">
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
                            <!-- End Tugas Pokok Tab -->

                            <!-- Tugas Harian Tab -->
                            <div class="tab-pane fade" id="tugas-harian-content" role="tabpanel"
                                aria-labelledby="tugas-harian-tab">
                                <div class="text-center py-5">
                                    <i class="bi bi-calendar-day" style="font-size: 4rem; color: #ccc;"></i>
                                    <h5 class="text-muted mt-3">Tugas Harian</h5>
                                    <p class="text-muted">Fitur tugas harian akan segera hadir</p>
                                </div>
                            </div>
                            <!-- End Tugas Harian Tab -->

                            <!-- Tugas Tambahan Tab -->
                            <div class="tab-pane fade" id="tugas-tambahan-content" role="tabpanel"
                                aria-labelledby="tugas-tambahan-tab">
                                <div class="text-center py-5">
                                    <i class="bi bi-plus-circle" style="font-size: 4rem; color: #ccc;"></i>
                                    <h5 class="text-muted mt-3">Tugas Tambahan</h5>
                                    <p class="text-muted">Fitur tugas tambahan akan segera hadir</p>
                                </div>
                            </div>
                            <!-- End Tugas Tambahan Tab -->
                        </div>
                        <!-- End Tab Content -->
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

    <!-- Modal Berikan Tugas -->
    <div class="modal fade" id="modalBerikanTugas" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-send me-2"></i>Berikan Tugas ke {{ $pegawai->nama }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formBerikanTugas">
                        @csrf
                        <input type="hidden" name="pegawai_id" value="{{ $pegawai->id }}">

                        <div class="mb-3">
                            <label for="jenis_tugas" class="form-label">Jenis Tugas <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="jenis_tugas" name="jenis_tugas" required>
                                <option value="">Pilih Jenis Tugas</option>
                                <option value="tugas_pokok">Tugas Pokok</option>
                                <option value="tugas_harian">Tugas Harian</option>
                                <option value="tugas_tambahan">Tugas Tambahan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="pilih_tugas" class="form-label">Pilih Tugas <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="pilih_tugas" name="tugas_id" required>
                                <option value="">Pilih tugas yang akan diberikan</option>
                            </select>
                            <small class="text-muted">Pilih jenis tugas terlebih dahulu</small>
                        </div>

                        <div class="mb-3">
                            <label for="catatan_penugasan" class="form-label">Catatan</label>
                            <textarea class="form-control" id="catatan_penugasan" name="catatan" rows="3"
                                placeholder="Tambahkan catatan atau instruksi khusus..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai"
                                    required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-success" id="btnBerikanTugas">
                        <i class="bi bi-send me-1"></i> Berikan Tugas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Buat Tugas -->
    <div class="modal fade" id="modalBuatTugas" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-lg me-2"></i>Buat Tugas Baru untuk {{ $pegawai->nama }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formBuatTugas">
                        @csrf
                        <input type="hidden" name="pegawai_id" value="{{ $pegawai->id }}">

                        <div class="mb-3">
                            <label for="jenis_tugas_baru" class="form-label">Jenis Tugas <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="jenis_tugas_baru" name="jenis_tugas" required>
                                <option value="">Pilih Jenis Tugas</option>
                                <option value="tugas_pokok">Tugas Pokok</option>
                                <option value="tugas_harian">Tugas Harian</option>
                                <option value="tugas_tambahan">Tugas Tambahan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="nama_tugas" class="form-label">Nama Tugas <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_tugas" name="nama_tugas"
                                placeholder="Masukkan nama tugas" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi_tugas" class="form-label">Deskripsi Tugas</label>
                            <textarea class="form-control" id="deskripsi_tugas" name="deskripsi" rows="3"
                                placeholder="Jelaskan detail tugas..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="periode_mulai" class="form-label">Periode Mulai <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="periode_mulai" name="periode_mulai"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="periode_selesai" class="form-label">Periode Selesai <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="periode_selesai" name="periode_selesai"
                                    required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="bobot_persen" class="form-label">Bobot (%)</label>
                                <input type="number" class="form-control" id="bobot_persen" name="bobot_persen"
                                    placeholder="0" min="0" max="100" step="0.1">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="target_value" class="form-label">Target <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="target_value" name="target_value"
                                    placeholder="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="satuan" class="form-label">Satuan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="satuan" name="satuan"
                                    placeholder="Dokumen, Laporan, dll" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="btnBuatTugas">
                        <i class="bi bi-plus-lg me-1"></i> Buat Tugas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Tab Styles */
        .nav-tabs-bordered {
            border-bottom: 2px solid #dee2e6;
        }

        .nav-tabs-bordered .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .nav-tabs-bordered .nav-link:hover {
            color: #0d6efd;
            border-bottom-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
        }

        .nav-tabs-bordered .nav-link.active {
            color: #0d6efd;
            border-bottom-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
        }

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

        /* Responsive buttons */
        @media (max-width: 768px) {
            .d-flex.gap-2.ms-3.mb-2 {
                margin-left: 0 !important;
                margin-top: 0.5rem;
                width: 100%;
            }

            .d-flex.gap-2.ms-3.mb-2 button {
                flex: 1;
            }
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

        // Function to show Berikan Tugas modal
        function showBerikanTugasModal() {
            $('#modalBerikanTugas').modal('show');
        }

        // Function to show Buat Tugas modal
        function showBuatTugasModal() {
            $('#modalBuatTugas').modal('show');
        }

        // Load tugas list based on jenis tugas
        $('#jenis_tugas').change(function() {
            const jenisTugas = $(this).val();
            const selectTugas = $('#pilih_tugas');

            selectTugas.html('<option value="">Memuat...</option>');

            if (jenisTugas) {
                $.ajax({
                    url: "{{ url('penugasan/get-tugas-list') }}",
                    type: 'GET',
                    data: {
                        jenis: jenisTugas
                    },
                    success: function(response) {
                        let options = '<option value="">Pilih tugas yang akan diberikan</option>';
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function(tugas) {
                                options +=
                                    `<option value="${tugas.id}">${tugas.nama_tugas}</option>`;
                            });
                        } else {
                            options = '<option value="">Tidak ada tugas tersedia</option>';
                        }
                        selectTugas.html(options);
                    },
                    error: function() {
                        selectTugas.html('<option value="">Gagal memuat data</option>');
                    }
                });
            } else {
                selectTugas.html('<option value="">Pilih tugas yang akan diberikan</option>');
            }
        });

        // Handle Berikan Tugas submission
        $('#btnBerikanTugas').click(function() {
            const formData = $('#formBerikanTugas').serialize();

            $.ajax({
                url: "{{ url('penugasan/berikan-tugas') }}",
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#btnBerikanTugas').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...'
                    );
                    $('#btnBerikanTugas').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalBerikanTugas').modal('hide');
                    $('#formBerikanTugas')[0].reset();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Tugas berhasil diberikan',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat memberikan tugas';
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
                    $('#btnBerikanTugas').html('<i class="bi bi-send me-1"></i> Berikan Tugas');
                    $('#btnBerikanTugas').prop('disabled', false);
                }
            });
        });

        // Handle Buat Tugas submission
        $('#btnBuatTugas').click(function() {
            const formData = $('#formBuatTugas').serialize();

            $.ajax({
                url: "{{ url('penugasan/buat-tugas') }}",
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#btnBuatTugas').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...'
                    );
                    $('#btnBuatTugas').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalBuatTugas').modal('hide');
                    $('#formBuatTugas')[0].reset();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Tugas baru berhasil dibuat',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat membuat tugas';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join('<br>');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        html: errorMessage
                    });
                },
                complete: function() {
                    $('#btnBuatTugas').html('<i class="bi bi-plus-lg me-1"></i> Buat Tugas');
                    $('#btnBuatTugas').prop('disabled', false);
                }
            });
        });
    </script>
@endpush
