@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Tugas Pokok</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tugas-pokok.index') }}">Penugasan</a></li>
                <li class="breadcrumb-item active">Tugas Pokok</li>
            </ol>
        </nav>
    </div>

    <section class="section">
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
                        <h5 class="card-title">Status Progress <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10">
                                <i class="bi bi-check-circle text-success"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $stats['dikerjakan'] }}/{{ $stats['pending'] }}</h6>
                                <span class="text-muted small pt-1">dikerjakan/pending</span>
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
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10">
                                <i class="bi bi-award text-info"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $stats['selesai'] }}</h6>
                                <span class="text-muted small pt-1">tugas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Total Bobot <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10">
                                <i class="bi bi-percent text-warning"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ number_format($stats['total_bobot'], 1) }}%</h6>
                                <span class="text-muted small pt-1">bobot</span>
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
                                        <small class="d-block text-muted fw-normal mt-1">Kelola tugas pokok pegawai</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <a href="{{ route('penugasan.tugas-pokok.create') }}"
                                    class="btn btn-primary btn-lg shadow-sm px-4 py-2">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Tugas Baru
                                </a>
                            </div>
                        </div>

                        <!-- Filter Form -->
                        <form id="filterForm" action="{{ route('penugasan.tugas-pokok.index') }}" method="GET">
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
                                    <label class="form-label">Pegawai</label>
                                    <select class="form-select select2" id="filterPegawai" name="pegawai_id">
                                        <option value="">Semua Pegawai</option>
                                        @foreach ($pegawais as $pegawai)
                                            <option value="{{ $pegawai->id }}"
                                                {{ request()->pegawai_id == $pegawai->id ? 'selected' : '' }}>
                                                {{ $pegawai->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-3 mb-3">
                                    <label class="form-label">Jabatan</label>
                                    <select class="form-select" id="filterJabatan" name="jabatan_id">
                                        <option value="">Semua Jabatan</option>
                                        @foreach ($jabatans as $jabatan)
                                            <option value="{{ $jabatan->id }}"
                                                {{ request()->jabatan_id == $jabatan->id ? 'selected' : '' }}>
                                                {{ $jabatan->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-3 mb-3">
                                    <label class="form-label">Bidang</label>
                                    <select class="form-select" id="filterBidang" name="bidang_id">
                                        <option value="">Semua Bidang</option>
                                        @foreach ($bidangs as $bidang)
                                            <option value="{{ $bidang->id }}"
                                                {{ request()->bidang_id == $bidang->id ? 'selected' : '' }}>
                                                {{ $bidang->nama }}
                                            </option>
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

                                <div class="col-lg-2 col-md-3 mb-3">
                                    <label class="form-label">Pemberi Tugas</label>
                                    <select class="form-select select2" id="filterPemberiTugas" name="pemberi_tugas_id">
                                        <option value="">Semua Pemberi Tugas</option>
                                        @foreach ($pemberiTugas as $pemberi)
                                            <option value="{{ $pemberi->id }}"
                                                {{ request()->pemberi_tugas_id == $pemberi->id ? 'selected' : '' }}>
                                                {{ $pemberi->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="d-flex gap-2 justify-content-between">
                                        <div class="input-group" style="max-width: 300px;">
                                            <input type="text" class="form-control" id="searchTugas" name="search"
                                                value="{{ request()->search }}" placeholder="Cari tugas...">
                                            <button class="btn btn-outline-secondary" type="submit">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="bi bi-filter me-1"></i> Filter
                                            </button>
                                            <a href="{{ route('penugasan.tugas-pokok.index', ['tahun' => $tahun]) }}"
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
                                            <th>Pegawai</th>
                                            <th>Pemberi Tugas</th>
                                            <th>Periode</th>
                                            <th>Status</th>
                                            <th>Bobot</th>
                                            <th>Progress</th>
                                            <th class="text-center" width="120">Aksi</th>
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
                                                        <span
                                                            class="d-block text-primary fw-bold">{{ $tugas->nama_tugas }}</span>
                                                        @if ($tugas->deskripsi)
                                                            <small
                                                                class="text-muted">{{ Str::limit($tugas->deskripsi, 60) }}</small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1">
                                                            <span
                                                                class="d-block fw-semibold">{{ $tugas->pegawai->nama }}</span>
                                                            @if (isset($tugas->pegawai->nip))
                                                                <small class="text-muted">NIP
                                                                    {{ $tugas->pegawai->nip }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if (isset($tugas->pegawai->jabatan))
                                                        <span class="badge bg-light text-dark border mt-1">
                                                            {{ $tugas->pegawai->jabatan->nama }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-semibold">{{ $tugas->pemberiTugas->nama }}</span>
                                                    <br>
                                                    @if (isset($tugas->pemberiTugas->jabatan))
                                                        <small
                                                            class="text-muted">{{ $tugas->pemberiTugas->jabatan->nama }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>
                                                        {{ date('d M Y', strtotime($tugas->periode_mulai)) }} -
                                                        {{ date('d M Y', strtotime($tugas->periode_selesai)) }}
                                                    </small>
                                                    <br>
                                                    @php
                                                        $now = now();
                                                        $mulai = \Carbon\Carbon::parse($tugas->periode_mulai);
                                                        $selesai = \Carbon\Carbon::parse($tugas->periode_selesai);

                                                        if ($now < $mulai) {
                                                            $badgeClass = 'bg-info';
                                                            $badgeText = 'Belum Mulai';
                                                        } elseif ($now > $selesai) {
                                                            $badgeClass = 'bg-danger';
                                                            $badgeText = 'Terlambat';
                                                        } else {
                                                            $badgeClass = 'bg-success';
                                                            $badgeText = 'Aktif';
                                                        }
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClass = [
                                                            'Pending' => 'bg-secondary',
                                                            'Diterima' => 'bg-info',
                                                            'Dikerjakan' => 'bg-warning',
                                                            'Selesai' => 'bg-success',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="badge {{ $statusClass[$tugas->status] ?? 'bg-secondary' }}">
                                                        {{ $tugas->status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold">{{ number_format($tugas->bobot_persen, 1) }}%
                                                    </div>
                                                    @if ($tugas->biaya_aktivitas)
                                                        <small class="text-muted">
                                                            Rp {{ number_format($tugas->biaya_aktivitas, 0, ',', '.') }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($tugas->progress->count() > 0)
                                                        @php
                                                            $latestProgress = $tugas->progress->last();
                                                            $progressPersen = $latestProgress->persentase_progress ?? 0;
                                                        @endphp
                                                        <div class="progress mb-1" style="height: 8px;">
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $progressPersen }}%"
                                                                aria-valuenow="{{ $progressPersen }}" aria-valuemin="0"
                                                                aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <small
                                                            class="text-muted">{{ number_format($progressPersen, 1) }}%</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown"
                                                            aria-expanded="false">
                                                            <i class="bi bi-gear"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <!-- Lihat Detail -->
                                                            <li>
                                                                <a href="{{ route('penugasan.tugas-pokok.show', $tugas->id) }}"
                                                                    class="dropdown-item">
                                                                    <i class="bi bi-eye text-primary me-2"></i> Lihat
                                                                    Detail
                                                                </a>
                                                            </li>

                                                            <!-- Edit -->
                                                            <li>
                                                                <a href="{{ route('penugasan.tugas-pokok.edit', $tugas->id) }}"
                                                                    class="dropdown-item">
                                                                    <i class="bi bi-pencil text-warning me-2"></i> Edit
                                                                    Data
                                                                </a>
                                                            </li>

                                                            <!-- Update Status -->
                                                            <li>
                                                                <a href="javascript:void(0)"
                                                                    onclick="showStatusModal({{ $tugas->id }}, '{{ $tugas->status }}')"
                                                                    class="dropdown-item">
                                                                    <i class="bi bi-arrow-repeat text-info me-2"></i>
                                                                    Update Status
                                                                </a>
                                                            </li>

                                                            <!-- Progress -->
                                                            @if ($tugas->status == 'Dikerjakan')
                                                                <li>
                                                                    <a href="{{ route('penugasan.tugas-pokok.progress', $tugas->id) }}"
                                                                        class="dropdown-item">
                                                                        <i class="bi bi-graph-up text-success me-2"></i>
                                                                        Update Progress
                                                                    </a>
                                                                </li>
                                                            @endif

                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>

                                                            <!-- Delete -->
                                                            <li>
                                                                <a href="javascript:void(0)"
                                                                    onclick="confirmDelete({{ $tugas->id }})"
                                                                    class="dropdown-item text-danger">
                                                                    <i class="bi bi-trash text-danger me-2"></i> Hapus
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-5">
                                                    <div class="py-3">
                                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                                        <p class="text-muted mt-2">Belum ada tugas pokok</p>
                                                        <a href="{{ route('penugasan.tugas-pokok.create') }}"
                                                            class="btn btn-sm btn-primary mt-1">
                                                            <i class="bi bi-plus-circle me-1"></i> Tambah Tugas Baru
                                                        </a>
                                                    </div>
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
                                        ];
                                    @endphp
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card tugas-card h-100 shadow-sm">
                                            <div class="card-header bg-transparent">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <h6 class="card-title mb-0 text-primary fw-bold">
                                                        {{ Str::limit($tugas->nama_tugas, 30) }}
                                                    </h6>
                                                    <span
                                                        class="badge {{ $statusClass[$tugas->status] ?? 'bg-secondary' }}">
                                                        {{ $tugas->status }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <span class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-person-badge text-primary me-2"></i>
                                                        <span>{{ $tugas->pegawai->nama }}</span>
                                                    </span>
                                                    <small class="text-muted d-flex align-items-center">
                                                        <i class="bi bi-briefcase me-2"></i>
                                                        <span>{{ $tugas->pegawai->jabatan->nama ?? '-' }}</span>
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <small class="text-muted d-flex align-items-center">
                                                        <i class="bi bi-arrow-up-circle me-2"></i>
                                                        <span>Pemberi: {{ $tugas->pemberiTugas->nama }}</span>
                                                    </small>
                                                </div>

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
                                                    <p class="card-text small text-muted">
                                                        {{ Str::limit($tugas->deskripsi, 100) }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <div class="btn-group btn-group-sm w-100">
                                                    <a href="{{ route('penugasan.tugas-pokok.show', $tugas->id) }}"
                                                        class="btn btn-outline-primary" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('penugasan.tugas-pokok.edit', $tugas->id) }}"
                                                        class="btn btn-outline-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button
                                                        onclick="showStatusModal({{ $tugas->id }}, '{{ $tugas->status }}')"
                                                        class="btn btn-outline-info" title="Update Status">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </button>
                                                    @if ($tugas->status == 'Dikerjakan')
                                                        <a href="{{ route('penugasan.tugas-pokok.progress', $tugas->id) }}"
                                                            class="btn btn-outline-success" title="Progress">
                                                            <i class="bi bi-graph-up"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">Belum ada tugas pokok</p>
                                        <a href="{{ route('penugasan.tugas-pokok.create') }}"
                                            class="btn btn-sm btn-primary mt-1">
                                            <i class="bi bi-plus-circle me-1"></i> Tambah Tugas Baru
                                        </a>
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
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span>Data yang dihapus tidak dapat dikembalikan!</span>
                    </div>
                    <p>Apakah Anda yakin ingin menghapus tugas pokok ini?</p>
                    <form id="formDeleteTugas" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" id="delete_tugas_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-danger" id="btnDeleteTugas">
                        <i class="bi bi-trash me-1"></i> Hapus
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
            // Initialize select2 if available
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }

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

            // Delete Tugas button handler
            $('#btnDeleteTugas').click(function() {
                const tugasId = $('#delete_tugas_id').val();
                deleteTugas(tugasId);
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
                url: "{{ url('tugas-pokok') }}/" + id + "/update-status",
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

        // Function to confirm delete
        function confirmDelete(id) {
            $('#delete_tugas_id').val(id);
            $('#modalDeleteConfirm').modal('show');
        }

        // Function to delete tugas
        function deleteTugas(id) {
            $.ajax({
                url: "{{ url('tugas-pokok') }}/" + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    $('#btnDeleteTugas').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                    );
                    $('#btnDeleteTugas').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalDeleteConfirm').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: 'Tugas pokok berhasil dihapus.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Gagal menghapus tugas pokok';
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
                    $('#btnDeleteTugas').html('<i class="bi bi-trash me-1"></i> Hapus');
                    $('#btnDeleteTugas').prop('disabled', false);
                }
            });
        }
    </script>
@endpush
