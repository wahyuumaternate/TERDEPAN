@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Daftar Tugas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">E-Kinerja</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.index') }}">Penugasan</a></li>
                <li class="breadcrumb-item active">{{ $pegawai->nama }}</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Tombol Kembali -->
        <div class="mb-3">
            <a href="{{ route('penugasan.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <!-- Pegawai Info with Stats -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body pt-3">
                        <div class="row align-items-center">
                            <!-- Info Pegawai (Kiri) -->
                            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle-xl me-3">
                                        {{ strtoupper(substr($pegawai->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <h4 class="mb-1 fw-bold">{{ $pegawai->nama }}</h4>
                                        <p class="text-muted mb-1 small">NIP: {{ $pegawai->nomor_identitas ?? '-' }}</p>
                                        <div class="d-flex gap-2">
                                            <span class="badge bg-primary">{{ $pegawai->jabatan->nama ?? '-' }}</span>
                                            <span class="badge bg-info">{{ $pegawai->bidang->nama ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats (Kanan) -->
                            <div class="col-lg-6 col-md-12">
                                <div class="row g-3">
                                    <!-- Tugas Pokok -->
                                    <div class="col-4">
                                        <div class="border-start border-primary border-3 ps-3 text-start">
                                            <div class="text-primary fw-bold h3 mb-0">{{ $totalTugasPokok }}</div>
                                            <div class="text-muted small">Tugas Pokok</div>
                                        </div>
                                    </div>

                                    <!-- Tugas Harian -->
                                    <div class="col-4">
                                        <div class="border-start border-success border-3 ps-3 text-start">
                                            <div class="text-success fw-bold h3 mb-0">{{ $totalTugasHarian }}</div>
                                            <div class="text-muted small">Tugas Harian</div>
                                            <div class="small mt-1">
                                                <span class="text-success fw-semibold">✓{{ $tugasSelesai }}</span>
                                                <span class="text-muted mx-1">|</span>
                                                <span class="text-warning fw-semibold">⟳{{ $tugasBerjalan }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tugas Tambahan -->
                                    <div class="col-4">
                                        <div class="border-start border-info border-3 ps-3 text-start">
                                            <div class="text-info fw-bold h3 mb-0">{{ $totalTugasTambahan }}</div>
                                            <div class="text-muted small">Tugas Tambahan</div>
                                            <div class="small mt-1">
                                                <span class="text-success fw-semibold">✓{{ $tugasTambahanSelesai }}</span>
                                                <span class="text-muted mx-1">|</span>
                                                <span class="text-warning fw-semibold">⟳{{ $tugasTambahanProgress }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                            <!-- Header dengan Action Buttons -->
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
                                <div class="d-flex gap-2">
                                    @php
                                        // Cek apakah user melihat tugasnya sendiri
                                        $isOwnTask = $pegawai->id == auth()->id();
                                        // Cek apakah user adalah atasan langsung dari pegawai ini
                                        $isAtasanLangsung = $pegawai->atasan_langsung_id == auth()->id();
                                        // User bisa memberikan tugas jika bukan tugas sendiri dan adalah atasan langsung
                                        $canBerikanTugas = !$isOwnTask && $isAtasanLangsung;
                                    @endphp

                                    @if ($isOwnTask)
                                        <button type="button" class="btn btn-primary" onclick="showBuatTugasModal()"
                                            id="btnBuatTugas">
                                            <i class="bi bi-plus-lg me-1"></i> Buat Tugas
                                        </button>
                                    @endif

                                    @if ($canBerikanTugas)
                                        <button type="button" class="btn btn-success" onclick="showBerikanTugasModal()">
                                            <i class="bi bi-send me-1"></i> Berikan Tugas
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Tab Navigation Full Width -->
                            <ul class="nav nav-tabs nav-tabs-bordered nav-justified" id="tugasTab" role="tablist">
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
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content" id="tugasTabContent">
                            <!-- Tugas Pokok Tab -->
                            <div class="tab-pane fade show active" id="tugas-pokok-content" role="tabpanel"
                                aria-labelledby="tugas-pokok-tab">
                                <!-- Filter Form -->
                                <form id="filterForm" action="{{ route('penugasan.show', $pegawai->id) }}"
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
                                                <option value="pending"
                                                    {{ request()->status == 'pending' ? 'selected' : '' }}>
                                                    Pending</option>
                                                <option value="dikerjakan"
                                                    {{ request()->status == 'dikerjakan' ? 'selected' : '' }}>Dikerjakan
                                                </option>
                                                <option value="selesai"
                                                    {{ request()->status == 'selesai' ? 'selected' : '' }}>
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
                                                    <a href="{{ route('penugasan.show', ['id' => $pegawai->id, 'tahun' => $tahun]) }}"
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
                                                    <th>Target</th>
                                                    <th>Progress</th>
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
                                                                    'pending' => 'bg-secondary',
                                                                    'dikerjakan' => 'bg-warning',
                                                                    'selesai' => 'bg-success',
                                                                ];
                                                            @endphp
                                                            <span
                                                                class="badge {{ $statusClass[$tugas->status] ?? 'bg-secondary' }}">
                                                                {{ ucfirst($tugas->status) }}
                                                            </span>
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
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-5">
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
                                                    'pending' => 'bg-secondary',
                                                    'dikerjakan' => 'bg-warning',
                                                    'selesai' => 'bg-success',
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
                                                                {{ ucfirst($tugas->status) }}
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

                                <!-- View Toggle for Tugas Harian -->
                                <div class="mb-4">
                                    <div class="btn-group shadow-sm" role="group">
                                        <input type="radio" class="btn-check" name="viewModeHarian"
                                            id="viewTableHarian" checked>
                                        <label class="btn btn-outline-primary px-4" for="viewTableHarian">
                                            <i class="bi bi-table me-2"></i>Tampilan Tabel
                                        </label>
                                        <input type="radio" class="btn-check" name="viewModeHarian"
                                            id="viewGridHarian">
                                        <label class="btn btn-outline-primary px-4" for="viewGridHarian">
                                            <i class="bi bi-grid-3x3-gap me-2"></i>Tampilan Kartu
                                        </label>
                                    </div>
                                </div>

                                <!-- Table View -->
                                <div id="tableViewHarian" class="mb-3">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped" id="tugasHarianTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" width="30"></th>
                                                    <th class="text-center" width="50">#</th>
                                                    <th width="250">Nama Tugas</th>
                                                    <th width="200">Tugas Pokok</th>
                                                    <th width="180">Periode</th>
                                                    <th class="text-center" width="120">Status</th>
                                                    <th class="text-center" width="100">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($tugasHarian as $index => $tugas)
                                                    <tr class="tugas-row" data-tugas-id="{{ $tugas->id }}">
                                                        <td class="text-center align-middle">
                                                            @if ($tugas->status === 'revisi' || ($tugas->historyRevisi && $tugas->historyRevisi->count() > 0))
                                                                <button class="btn btn-sm btn-link p-0"
                                                                    onclick="toggleRevisionHistory('{{ $tugas->id }}')"
                                                                    data-bs-toggle="tooltip" title="Lihat History Revisi">
                                                                    <i class="bi bi-chevron-right"
                                                                        id="chevron-{{ $tugas->id }}"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            {{ $index + 1 }}
                                                        </td>
                                                        <td class="align-middle">
                                                            <div>
                                                                <span class="fw-semibold">{{ $tugas->nama_tugas }}</span>
                                                                @if ($tugas->deskripsi)
                                                                    <br>
                                                                    <small
                                                                        class="text-muted">{{ Str::limit($tugas->deskripsi, 60) }}</small>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="align-middle">
                                                            @if ($tugas->tugasPokok)
                                                                <small class="text-primary">
                                                                    <i class="bi bi-link-45deg"></i>
                                                                    {{ Str::limit($tugas->tugasPokok->nama_tugas, 30) }}
                                                                </small>
                                                            @else
                                                                <small class="text-muted">-</small>
                                                            @endif
                                                        </td>
                                                        <td class="align-middle">
                                                            <small>
                                                                <i class="bi bi-calendar-range text-info me-1"></i>
                                                                {{ date('d/m/Y', strtotime($tugas->tanggal_mulai)) }}<br>
                                                                <i class="bi bi-calendar-check text-success me-1"></i>
                                                                {{ date('d/m/Y', strtotime($tugas->tanggal_selesai)) }}
                                                            </small>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            @php
                                                                $statusClass = [
                                                                    'pending' => 'bg-secondary',
                                                                    'dikerjakan' => 'bg-warning',
                                                                    'validasi' => 'bg-info',
                                                                    'revisi' => 'bg-danger',
                                                                    'selesai' => 'bg-success',
                                                                ];
                                                            @endphp
                                                            <span
                                                                class="badge {{ $statusClass[$tugas->status] ?? 'bg-secondary' }}">
                                                                {{ ucfirst($tugas->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <div class="dropdown">
                                                                <button
                                                                    class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                    type="button" data-bs-toggle="dropdown"
                                                                    aria-expanded="false">
                                                                    <i class="bi bi-gear"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <!-- Validasi -->
                                                                    <li>
                                                                        <a href="javascript:void(0)"
                                                                            onclick="validasiTugas('{{ $tugas->id }}', 'tugas_harian')"
                                                                            class="dropdown-item {{ $tugas->status === 'selesai' ? 'disabled' : '' }}">
                                                                            <i
                                                                                class="bi bi-check-circle text-success me-2"></i>
                                                                            Validasi
                                                                        </a>
                                                                    </li>

                                                                    <!-- Kerjakan (jika status pending) atau Upload Bukti (jika status dikerjakan/revisi) -->
                                                                    @if ($tugas->status === 'pending')
                                                                        <li>
                                                                            <a href="javascript:void(0)"
                                                                                onclick="kerjakanTugas('{{ $tugas->id }}', 'tugas_harian')"
                                                                                class="dropdown-item">
                                                                                <i
                                                                                    class="bi bi-play-circle text-info me-2"></i>
                                                                                Kerjakan
                                                                            </a>
                                                                        </li>
                                                                    @elseif($tugas->status === 'dikerjakan')
                                                                        <li>
                                                                            <a href="{{ route('penugasan.tugas-harian.upload-eviden', $tugas->id) }}"
                                                                                class="dropdown-item">
                                                                                <i
                                                                                    class="bi bi-cloud-upload text-primary me-2"></i>
                                                                                Upload Bukti
                                                                            </a>
                                                                        </li>
                                                                    @elseif($tugas->status === 'revisi')
                                                                        <li>
                                                                            <a href="{{ route('penugasan.tugas-harian.upload-eviden', $tugas->id) }}"
                                                                                class="dropdown-item">
                                                                                <i
                                                                                    class="bi bi-arrow-repeat text-warning me-2"></i>
                                                                                Upload Ulang Bukti
                                                                            </a>
                                                                        </li>
                                                                    @endif

                                                                    <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li>

                                                                    <!-- Edit -->
                                                                    <li>
                                                                        <a href="javascript:void(0)"
                                                                            onclick="editTugasHarian('{{ $tugas->id }}')"
                                                                            class="dropdown-item">
                                                                            <i
                                                                                class="bi bi-pencil-square text-warning me-2"></i>
                                                                            Edit
                                                                        </a>
                                                                    </li>

                                                                    <!-- Hapus -->
                                                                    <li>
                                                                        <a href="javascript:void(0)"
                                                                            onclick="deleteTugasHarian('{{ $tugas->id }}', '{{ $tugas->nama_tugas }}')"
                                                                            class="dropdown-item">
                                                                            <i class="bi bi-trash text-danger me-2"></i>
                                                                            Hapus
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <!-- History Revisi Row (hidden by default) -->
                                                    <tr class="revision-history-row" id="history-{{ $tugas->id }}"
                                                        style="display: none;">
                                                        <td colspan="7" class="p-2 bg-light border-top">
                                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                                <i class="bi bi-clock-history text-warning"></i>
                                                                <strong class="small">History Revisi</strong>
                                                            </div>
                                                            <div id="revision-content-{{ $tugas->id }}">
                                                                <div class="text-center py-2">
                                                                    <div class="spinner-border spinner-border-sm"
                                                                        role="status">
                                                                        <span class="visually-hidden">Loading...</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center py-5">
                                                            <i class="bi bi-inbox"
                                                                style="font-size: 3rem; color: #ccc;"></i>
                                                            <p class="text-muted mt-2">Belum ada tugas harian</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <div class="d-flex justify-content-center mt-4">
                                        {{-- Pagination removed for simple collection --}}
                                    </div>
                                </div>

                                <!-- Grid View -->
                                <div id="gridViewHarian" style="display: none;">
                                    <div class="row">
                                        @forelse($tugasHarian as $tugas)
                                            @php
                                                $statusClassHarian = [
                                                    'Assigned' => 'bg-secondary',
                                                    'In_Progress' => 'bg-warning',
                                                    'Completed' => 'bg-success',
                                                    'Overdue' => 'bg-danger',
                                                    'Cancelled' => 'bg-dark',
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
                                                                class="badge {{ $statusClassHarian[$tugas->status] ?? 'bg-secondary' }}">
                                                                {{ str_replace('_', ' ', $tugas->status) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <!-- Catatan Revisi Alert -->
                                                        @if ($tugas->status === 'revisi')
                                                            <div class="alert alert-warning alert-dismissible fade show mb-3"
                                                                role="alert">
                                                                <h6 class="alert-heading mb-2">
                                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                                    Perlu Revisi
                                                                </h6>
                                                                <p class="mb-1 small"><strong>Catatan:</strong>
                                                                    {{ $tugas->catatan_validasi ?? 'Tidak ada catatan' }}
                                                                </p>
                                                                @if ($tugas->validasi_oleh)
                                                                    <small class="text-muted">
                                                                        <i class="bi bi-person me-1"></i>
                                                                        {{ $tugas->validasiOleh->nama ?? 'Unknown' }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        @endif

                                                        @if ($tugas->tugasPokok)
                                                            <p class="card-text small d-flex align-items-center mb-2">
                                                                <i class="bi bi-link-45deg text-primary me-2"></i>
                                                                <span>{{ Str::limit($tugas->tugasPokok->nama_tugas, 40) }}</span>
                                                            </p>
                                                        @endif

                                                        <p class="card-text small d-flex align-items-center mb-2">
                                                            <i class="bi bi-calendar-x text-danger me-2"></i>
                                                            <span>
                                                                <strong>Tanggal Selesai:</strong>
                                                                {{ date('d M Y', strtotime($tugas->tanggal_selesai)) }}
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
                                                                    <div class="progress-bar bg-success"
                                                                        role="progressbar"
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
                                                            @if ($tugas->status === 'revisi')
                                                                <a href="{{ route('penugasan.tugas-harian.upload-eviden', $tugas->id) }}"
                                                                    class="btn btn-warning" title="Upload Ulang">
                                                                    <i class="bi bi-arrow-repeat me-1"></i> Upload Ulang
                                                                </a>
                                                            @endif
                                                            <button onclick="editTugasHarian('{{ $tugas->id }}')"
                                                                class="btn btn-outline-warning" title="Edit">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>
                                                            <button
                                                                onclick="deleteTugasHarian('{{ $tugas->id }}', '{{ $tugas->nama_tugas }}')"
                                                                class="btn btn-outline-danger" title="Hapus">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12 text-center py-5">
                                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                                <p class="text-muted mt-2">Belum ada tugas harian</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    <!-- Pagination for Grid View -->
                                    <div class="d-flex justify-content-center mt-4">
                                        {{-- Pagination removed for simple collection --}}
                                    </div>
                                </div>
                            </div>
                            <!-- End Tugas Harian Tab -->

                            <!-- Tugas Tambahan Tab -->
                            <div class="tab-pane fade" id="tugas-tambahan-content" role="tabpanel"
                                aria-labelledby="tugas-tambahan-tab">

                                <!-- View Toggle for Tugas Tambahan -->
                                <div class="mb-4">
                                    <div class="btn-group shadow-sm" role="group">
                                        <input type="radio" class="btn-check" name="viewModeTambahan"
                                            id="viewTableTambahan" checked>
                                        <label class="btn btn-outline-primary px-4" for="viewTableTambahan">
                                            <i class="bi bi-table me-2"></i>Tampilan Tabel
                                        </label>
                                        <input type="radio" class="btn-check" name="viewModeTambahan"
                                            id="viewGridTambahan">
                                        <label class="btn btn-outline-primary px-4" for="viewGridTambahan">
                                            <i class="bi bi-grid-3x3-gap me-2"></i>Tampilan Kartu
                                        </label>
                                    </div>
                                </div>

                                <!-- Table View -->
                                <div id="tableViewTambahan" class="mb-3">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped" id="tugasTambahanTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" width="50">#</th>
                                                    <th width="250">Nama Tugas</th>
                                                    <th width="180">Periode</th>
                                                    <th class="text-center" width="120">Target</th>
                                                    <th class="text-center" width="120">Status</th>
                                                    <th class="text-center" width="100">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($tugasTambahanList as $index => $tugas)
                                                    <tr class="tugas-row" data-tugas-id="{{ $tugas->id }}">
                                                        <td class="text-center align-middle">
                                                            {{ $index + 1 }}
                                                        </td>
                                                        <td class="align-middle">
                                                            <div>
                                                                <span class="fw-semibold">{{ $tugas->nama_tugas }}</span>
                                                                @if ($tugas->deskripsi)
                                                                    <br>
                                                                    <small
                                                                        class="text-muted">{{ Str::limit($tugas->deskripsi, 60) }}</small>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="align-middle">
                                                            <small>
                                                                <i class="bi bi-calendar-range text-info me-1"></i>
                                                                {{ date('d/m/Y', strtotime($tugas->tanggal_mulai)) }}<br>
                                                                <i class="bi bi-calendar-check text-success me-1"></i>
                                                                {{ date('d/m/Y', strtotime($tugas->deadline)) }}
                                                            </small>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <small>
                                                                <strong>{{ number_format($tugas->target_value, 0) }}</strong><br>
                                                                {{ $tugas->satuan }}
                                                            </small>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            @php
                                                                $statusClass = [
                                                                    'pending' => 'bg-secondary',
                                                                    'dikerjakan' => 'bg-warning',
                                                                    'validasi' => 'bg-info',
                                                                    'revisi' => 'bg-danger',
                                                                    'selesai' => 'bg-success',
                                                                ];
                                                            @endphp
                                                            <span
                                                                class="badge {{ $statusClass[$tugas->status] ?? 'bg-secondary' }}">
                                                                {{ ucfirst($tugas->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <div class="dropdown">
                                                                <button
                                                                    class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                    type="button" data-bs-toggle="dropdown"
                                                                    aria-expanded="false">
                                                                    <i class="bi bi-gear"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <!-- Validasi -->
                                                                    <li>
                                                                        <a href="javascript:void(0)"
                                                                            onclick="validasiTugas('{{ $tugas->id }}', 'tugas_tambahan')"
                                                                            class="dropdown-item {{ $tugas->status === 'selesai' ? 'disabled' : '' }}">
                                                                            <i
                                                                                class="bi bi-check-circle text-success me-2"></i>
                                                                            Validasi
                                                                        </a>
                                                                    </li>

                                                                    <!-- Kerjakan (jika status pending) atau Upload Bukti (jika status dikerjakan/revisi) -->
                                                                    @if ($tugas->status === 'pending')
                                                                        <li>
                                                                            <a href="javascript:void(0)"
                                                                                onclick="kerjakanTugas('{{ $tugas->id }}', 'tugas_tambahan')"
                                                                                class="dropdown-item">
                                                                                <i
                                                                                    class="bi bi-play-circle text-info me-2"></i>
                                                                                Kerjakan
                                                                            </a>
                                                                        </li>
                                                                    @elseif($tugas->status === 'dikerjakan')
                                                                        <li>
                                                                            <a href="{{ route('penugasan.tugas-tambahan.upload-eviden', $tugas->id) }}"
                                                                                class="dropdown-item">
                                                                                <i
                                                                                    class="bi bi-cloud-upload text-primary me-2"></i>
                                                                                Upload Bukti
                                                                            </a>
                                                                        </li>
                                                                    @elseif($tugas->status === 'revisi')
                                                                        <li>
                                                                            <a href="{{ route('penugasan.tugas-tambahan.upload-eviden', $tugas->id) }}"
                                                                                class="dropdown-item">
                                                                                <i
                                                                                    class="bi bi-arrow-repeat text-warning me-2"></i>
                                                                                Upload Ulang Bukti
                                                                            </a>
                                                                        </li>
                                                                    @endif

                                                                    <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li>

                                                                    <!-- Edit -->
                                                                    <li>
                                                                        <a href="javascript:void(0)"
                                                                            onclick="editTugasTambahan('{{ $tugas->id }}')"
                                                                            class="dropdown-item">
                                                                            <i
                                                                                class="bi bi-pencil-square text-warning me-2"></i>
                                                                            Edit
                                                                        </a>
                                                                    </li>

                                                                    <!-- Hapus -->
                                                                    <li>
                                                                        <a href="javascript:void(0)"
                                                                            onclick="deleteTugasTambahan('{{ $tugas->id }}', '{{ $tugas->nama_tugas }}')"
                                                                            class="dropdown-item">
                                                                            <i class="bi bi-trash text-danger me-2"></i>
                                                                            Hapus
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-5">
                                                            <i class="bi bi-inbox"
                                                                style="font-size: 3rem; color: #ccc;"></i>
                                                            <p class="text-muted mt-2">Belum ada tugas tambahan</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Grid View -->
                                <div id="gridViewTambahan" style="display: none;">
                                    <div class="row">
                                        @forelse($tugasTambahanList as $tugas)
                                            @php
                                                $statusClassTambahan = [
                                                    'pending' => 'bg-secondary',
                                                    'dikerjakan' => 'bg-warning',
                                                    'validasi' => 'bg-info',
                                                    'revisi' => 'bg-danger',
                                                    'selesai' => 'bg-success',
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
                                                                class="badge {{ $statusClassTambahan[$tugas->status] ?? 'bg-secondary' }}">
                                                                {{ ucfirst($tugas->status) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <!-- Catatan Revisi Alert -->
                                                        @if ($tugas->status === 'revisi')
                                                            <div class="alert alert-warning alert-dismissible fade show mb-3"
                                                                role="alert">
                                                                <h6 class="alert-heading mb-2">
                                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                                    Perlu Revisi
                                                                </h6>
                                                                <p class="mb-1 small"><strong>Catatan:</strong>
                                                                    {{ $tugas->catatan_validasi ?? 'Tidak ada catatan' }}
                                                                </p>
                                                                @if ($tugas->validator)
                                                                    <small class="text-muted">
                                                                        <i class="bi bi-person me-1"></i>
                                                                        {{ $tugas->validator->nama ?? 'Unknown' }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        @endif

                                                        <p class="card-text small d-flex align-items-center mb-2">
                                                            <i class="bi bi-calendar-range text-info me-2"></i>
                                                            <span>
                                                                <strong>Mulai:</strong>
                                                                {{ date('d M Y', strtotime($tugas->tanggal_mulai)) }}
                                                            </span>
                                                        </p>

                                                        <p class="card-text small d-flex align-items-center mb-2">
                                                            <i class="bi bi-calendar-x text-danger me-2"></i>
                                                            <span>
                                                                <strong>Deadline:</strong>
                                                                {{ date('d M Y', strtotime($tugas->deadline)) }}
                                                            </span>
                                                        </p>

                                                        <p class="card-text small d-flex align-items-center mb-2">
                                                            <i class="bi bi-bullseye text-success me-2"></i>
                                                            <span><strong>Target:</strong>
                                                                {{ number_format($tugas->target_value, 0) }}
                                                                {{ $tugas->satuan }}</span>
                                                        </p>

                                                        @if ($tugas->deskripsi)
                                                            <p class="card-text small text-muted mt-3">
                                                                {{ Str::limit($tugas->deskripsi, 100) }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <div class="card-footer bg-transparent">
                                                        <div class="btn-group btn-group-sm w-100">
                                                            @if ($tugas->status === 'pending')
                                                                <button
                                                                    onclick="kerjakanTugas('{{ $tugas->id }}', 'tugas_tambahan')"
                                                                    class="btn btn-info" title="Kerjakan">
                                                                    <i class="bi bi-play-circle me-1"></i> Kerjakan
                                                                </button>
                                                            @elseif($tugas->status === 'dikerjakan')
                                                                <a href="{{ route('penugasan.tugas-tambahan.upload-eviden', $tugas->id) }}"
                                                                    class="btn btn-primary" title="Upload Bukti">
                                                                    <i class="bi bi-cloud-upload me-1"></i> Upload Bukti
                                                                </a>
                                                            @elseif($tugas->status === 'revisi')
                                                                <a href="{{ route('penugasan.tugas-tambahan.upload-eviden', $tugas->id) }}"
                                                                    class="btn btn-warning" title="Upload Ulang">
                                                                    <i class="bi bi-arrow-repeat me-1"></i> Upload Ulang
                                                                </a>
                                                            @endif
                                                            <button onclick="editTugasTambahan('{{ $tugas->id }}')"
                                                                class="btn btn-outline-warning" title="Edit">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>
                                                            <button
                                                                onclick="deleteTugasTambahan('{{ $tugas->id }}', '{{ $tugas->nama_tugas }}')"
                                                                class="btn btn-outline-danger" title="Hapus">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12 text-center py-5">
                                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                                <p class="text-muted mt-2">Belum ada tugas tambahan</p>
                                            </div>
                                        @endforelse
                                    </div>
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
                                <option value="pending">Pending</option>
                                <option value="dikerjakan">Dikerjakan</option>
                                <option value="selesai">Selesai</option>
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
                            <label for="jenis_tugas_berikan" class="form-label">Jenis Tugas <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="jenis_tugas_berikan" name="jenis_tugas" required>
                                <option value="">Pilih Jenis Tugas</option>
                                <option value="tugas_harian">Tugas Harian</option>
                                <option value="tugas_tambahan">Tugas Tambahan</option>
                            </select>
                            <small class="text-muted">Tugas Harian berdasarkan Tugas Pokok, Tugas Tambahan adalah tugas
                                mandiri</small>
                        </div>

                        <!-- Field untuk Tugas Harian -->
                        <div id="tugasHarianFields" style="display: none;">
                            <div class="mb-3">
                                <label for="tugas_pokok_id" class="form-label">Tugas Pokok <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="tugas_pokok_id" name="tugas_pokok_id">
                                    <option value="">Pilih Tugas Pokok</option>
                                    @foreach ($tugasPokok as $tp)
                                        <option value="{{ $tp->id }}" data-satuan="{{ $tp->satuan }}">
                                            {{ $tp->nama_tugas }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Tugas harian harus berdasarkan tugas pokok pegawai</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nama_tugas_berikan" class="form-label">Nama Tugas <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_tugas_berikan" name="nama_tugas"
                                placeholder="Masukkan nama tugas" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi_tugas_berikan" class="form-label">Deskripsi Tugas</label>
                            <textarea class="form-control" id="deskripsi_tugas_berikan" name="deskripsi" rows="3"
                                placeholder="Jelaskan detail tugas..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai_berikan" class="form-label">Tanggal Mulai <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_mulai_berikan"
                                    name="tanggal_mulai" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_selesai_berikan" class="form-label">Tanggal Selesai <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_selesai_berikan"
                                    name="tanggal_selesai" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_value_berikan" class="form-label">Target <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="target_value_berikan" name="target_value"
                                    placeholder="1" min="1" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="satuan_berikan" class="form-label">Satuan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="satuan_berikan" name="satuan"
                                    placeholder="Dokumen, Laporan, dll" required readonly>
                                <small class="text-muted">Satuan akan mengikuti tugas pokok yang dipilih</small>
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
                        <i class="bi bi-plus-lg me-2"></i>Buat Tugas Mandiri
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formBuatTugas">
                        @csrf
                        <input type="hidden" name="jenis_tugas" value="tugas_harian">

                        <!-- Field untuk Tugas Pokok (required) -->
                        <div class="mb-3">
                            <label for="tugas_pokok_id_buat" class="form-label">Tugas Pokok <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="tugas_pokok_id_buat" name="tugas_pokok_id" required>
                                <option value="">Pilih Tugas Pokok</option>
                                @if (isset($tugasPokokList) && $tugasPokokList->count() > 0)
                                    @foreach ($tugasPokokList as $tp)
                                        <option value="{{ $tp->id }}" data-satuan="{{ $tp->satuan }}">
                                            {{ $tp->nama_tugas }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Tugas harian harus terkait dengan tugas pokok</small>
                        </div>

                        <div class="mb-3">
                            <label for="nama_tugas_buat" class="form-label">Nama Tugas <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_tugas_buat" name="nama_tugas"
                                placeholder="Masukkan nama tugas" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi_tugas_buat" class="form-label">Deskripsi Tugas</label>
                            <textarea class="form-control" id="deskripsi_tugas_buat" name="deskripsi" rows="3"
                                placeholder="Jelaskan detail tugas..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai_buat" class="form-label">Tanggal Mulai <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_mulai_buat" name="tanggal_mulai"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_selesai_buat" class="form-label">Tanggal Selesai <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_selesai_buat"
                                    name="tanggal_selesai" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_value_buat" class="form-label">Target <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="target_value_buat" name="target_value"
                                    placeholder="0" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="satuan_buat" class="form-label">Satuan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="satuan_buat" name="satuan"
                                    placeholder="Otomatis dari Tugas Pokok" readonly required>
                                <small class="text-muted">Satuan akan mengikuti tugas pokok yang dipilih</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="btnSubmitBuatTugas">
                        <i class="bi bi-plus-lg me-1"></i> Buat Tugas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Update Status Tugas Harian -->
    <div class="modal fade" id="modalUpdateStatusHarian" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status Tugas Harian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formUpdateStatusHarian">
                        @csrf
                        <input type="hidden" id="status_tugas_harian_id" name="tugas_id">

                        <div class="mb-3">
                            <label for="status_harian" class="form-label">Status Tugas</label>
                            <select class="form-select" id="status_harian" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="dikerjakan">Dikerjakan</option>
                                <option value="validasi">Validasi</option>
                                <option value="revisi">Revisi</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="btnUpdateStatusHarian">
                        <i class="bi bi-check-circle me-1"></i> Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Tugas Harian -->
    <div class="modal fade" id="modalEditTugasHarian" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Tugas Harian
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditTugasHarian">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_tugas_harian_id" name="tugas_id">

                        <div class="mb-3">
                            <label for="edit_tugas_pokok" class="form-label">Tugas Pokok</label>
                            <input type="text" class="form-control" id="edit_tugas_pokok" readonly disabled>
                            <small class="text-muted">Tugas pokok tidak dapat diubah</small>
                        </div>

                        <div class="mb-3">
                            <label for="edit_nama_tugas" class="form-label">Nama Tugas <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_nama_tugas" name="nama_tugas"
                                placeholder="Masukkan nama tugas" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_deskripsi" class="form-label">Deskripsi Tugas</label>
                            <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"
                                placeholder="Jelaskan detail tugas..."></textarea>
                        </div>


                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_tanggal_mulai" class="form-label">Tanggal Mulai <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_tanggal_mulai" name="tanggal_mulai"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_deadline" class="form-label">Tanggal Selesai <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_deadline" name="tanggal_selesai"
                                    required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_target_value" class="form-label">Target <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_target_value" name="target_value"
                                    placeholder="1" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_satuan" class="form-label">Satuan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_satuan" name="satuan"
                                    placeholder="Dokumen, Laporan, dll" required readonly>
                                <small class="text-muted">Satuan mengikuti tugas pokok yang dipilih</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-warning" id="btnUpdateTugasHarian">
                        <i class="bi bi-check-circle me-1"></i> Update Tugas
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/detail-tugas.css') }}">
@endpush

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
            // Reset form
            $('#formBerikanTugas')[0].reset();
            $('#tugasHarianFields').hide();
            $('#tugas_pokok_id').prop('required', false);
            $('#satuan_berikan').prop('readonly', true); // Default readonly
            $('#satuan_berikan').val('');

            $('#modalBerikanTugas').modal('show');
        }

        // Function to show Buat Tugas modal
        function showBuatTugasModal() {
            $('#modalBuatTugas').modal('show');
        }

        // Handle tugas pokok change to set satuan (Buat Tugas Mandiri)
        $('#tugas_pokok_id_buat').change(function() {
            const selectedOption = $(this).find('option:selected');
            const satuan = selectedOption.data('satuan');

            if (satuan) {
                $('#satuan_buat').val(satuan);
            } else {
                $('#satuan_buat').val('');
            }
        });

        // Handle jenis tugas change in Berikan Tugas modal
        $('#jenis_tugas_berikan').change(function() {
            const jenisTugas = $(this).val();

            if (jenisTugas === 'tugas_harian') {
                $('#tugasHarianFields').show();
                $('#tugas_pokok_id').prop('required', true);
                $('#satuan_berikan').prop('readonly', true);
                $('#satuan_berikan').val('');
            } else if (jenisTugas === 'tugas_tambahan') {
                $('#tugasHarianFields').hide();
                $('#tugas_pokok_id').prop('required', false);
                $('#satuan_berikan').prop('readonly', false); // Manual input untuk tugas tambahan
                $('#satuan_berikan').val('');
            } else {
                $('#tugasHarianFields').hide();
                $('#tugas_pokok_id').prop('required', false);
                $('#satuan_berikan').prop('readonly', true);
                $('#satuan_berikan').val('');
            }
        });

        // Handle tugas pokok change to set satuan
        $('#tugas_pokok_id').change(function() {
            const selectedOption = $(this).find('option:selected');
            const satuan = selectedOption.data('satuan');

            if (satuan) {
                $('#satuan_berikan').val(satuan);
            } else {
                $('#satuan_berikan').val('');
            }
        });

        // Load tugas list based on jenis tugas (for Buat Tugas modal)
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
            // Validation
            const jenisTugas = $('#jenis_tugas_berikan').val();
            const namaTugas = $('#nama_tugas_berikan').val().trim();
            const tanggalMulai = $('#tanggal_mulai_berikan').val();
            const tanggalSelesai = $('#tanggal_selesai_berikan').val();
            const targetValue = $('#target_value_berikan').val();
            const satuan = $('#satuan_berikan').val().trim();

            if (!jenisTugas) {
                Swal.fire('Peringatan', 'Pilih jenis tugas terlebih dahulu', 'warning');
                return;
            }

            if (jenisTugas === 'tugas_harian') {
                const tugasPokokId = $('#tugas_pokok_id').val();
                if (!tugasPokokId) {
                    Swal.fire('Peringatan', 'Pilih tugas pokok untuk tugas harian', 'warning');
                    return;
                }
            }

            if (!namaTugas) {
                Swal.fire('Peringatan', 'Nama tugas harus diisi', 'warning');
                return;
            }

            if (!tanggalMulai) {
                Swal.fire('Peringatan', 'Tanggal mulai harus diisi', 'warning');
                return;
            }

            if (!tanggalSelesai) {
                Swal.fire('Peringatan', 'Tanggal selesai harus diisi', 'warning');
                return;
            }

            if (!targetValue || targetValue <= 0) {
                Swal.fire('Peringatan', 'Target harus diisi dengan nilai lebih dari 0', 'warning');
                return;
            }

            if (!satuan) {
                Swal.fire('Peringatan', 'Satuan harus diisi', 'warning');
                return;
            }
            const formData = $('#formBerikanTugas').serialize();

            $.ajax({
                url: "{{ route('penugasan.berikan-tugas') }}",
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
                    $('#tugasHarianFields').hide();
                    $('#satuan_berikan').prop('readonly', true); // Reset to readonly
                    $('#satuan_berikan').val('');

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
                    $('#btnBerikanTugas').html('<i class="bi bi-send me-1"></i> Berikan Tugas');
                    $('#btnBerikanTugas').prop('disabled', false);
                }
            });
        });

        // Handle Buat Tugas submission
        $('#btnSubmitBuatTugas').click(function() {
            // Validation
            const tugasPokokId = $('#tugas_pokok_id_buat').val();
            const namaTugas = $('#nama_tugas_buat').val().trim();
            const tanggalMulai = $('#tanggal_mulai_buat').val();
            const tanggalSelesai = $('#tanggal_selesai_buat').val();
            const targetValue = $('#target_value_buat').val();

            if (!tugasPokokId) {
                Swal.fire('Peringatan', 'Tugas pokok harus dipilih', 'warning');
                return;
            }

            if (!namaTugas) {
                Swal.fire('Peringatan', 'Nama tugas harus diisi', 'warning');
                return;
            }

            if (!tanggalMulai) {
                Swal.fire('Peringatan', 'Tanggal mulai harus diisi', 'warning');
                return;
            }

            if (!tanggalSelesai) {
                Swal.fire('Peringatan', 'Tanggal selesai harus diisi', 'warning');
                return;
            }

            if (!targetValue || targetValue < 1) {
                Swal.fire('Peringatan', 'Target minimal 1', 'warning');
                return;
            }

            const formData = $('#formBuatTugas').serialize();

            $.ajax({
                url: "{{ route('penugasan.buat-tugas') }}",
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#btnSubmitBuatTugas').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...'
                    );
                    $('#btnSubmitBuatTugas').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalBuatTugas').modal('hide');
                    $('#formBuatTugas')[0].reset();

                    // Tampilkan pesan yang lebih informatif
                    let successMessage = response.message || 'Tugas harian mandiri berhasil dibuat';
                    if (response.tahun && response.tahun != '{{ $tahun }}') {
                        successMessage +=
                            '<br><small class="text-muted">Halaman akan dimuat dengan filter tahun ' +
                            response.tahun + '</small>';
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: successMessage,
                        timer: 2500,
                        showConfirmButton: false
                    }).then(() => {
                        // Reload dengan tahun yang sesuai dengan tugas yang baru dibuat
                        if (response.tahun) {
                            window.location.href =
                                "{{ route('penugasan.show', $pegawai->id) }}?tahun=" +
                                response.tahun;
                        } else {
                            window.location.reload();
                        }
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
                    $('#btnSubmitBuatTugas').html('<i class="bi bi-plus-lg me-1"></i> Buat Tugas');
                    $('#btnSubmitBuatTugas').prop('disabled', false);
                }
            });
        });

        // ===== TUGAS HARIAN FUNCTIONS =====

        // View mode toggle for Tugas Harian
        $('input[name="viewModeHarian"]').change(function() {
            let viewMode = $(this).attr('id');
            if (viewMode === 'viewGridHarian') {
                $('#gridViewHarian').show();
                $('#tableViewHarian').hide();
                localStorage.setItem('tugas_harian_view_mode', 'grid');
            } else {
                $('#tableViewHarian').show();
                $('#gridViewHarian').hide();
                localStorage.setItem('tugas_harian_view_mode', 'table');
            }
        });

        // Restore view mode from localStorage for Tugas Harian
        const savedViewModeHarian = localStorage.getItem('tugas_harian_view_mode');
        if (savedViewModeHarian === 'grid') {
            $('#viewGridHarian').prop('checked', true).trigger('change');
        }

        // Function to show status modal for Tugas Harian
        function showStatusModalHarian(id, currentStatus) {
            $('#status_tugas_harian_id').val(id);
            $('#status_harian').val(currentStatus);
            $('#modalUpdateStatusHarian').modal('show');
        }

        // Update Status button handler for Tugas Harian
        $('#btnUpdateStatusHarian').click(function() {
            const tugasId = $('#status_tugas_harian_id').val();
            const status = $('#status_harian').val();
            updateStatusHarian(tugasId, status);
        });

        // Function to update status for Tugas Harian
        function updateStatusHarian(id, status) {
            $.ajax({
                url: "{{ url('penugasan/tugas-harian') }}/" + id + "/update-status",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status
                },
                beforeSend: function() {
                    $('#btnUpdateStatusHarian').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                    );
                    $('#btnUpdateStatusHarian').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalUpdateStatusHarian').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Status tugas harian berhasil diperbarui',
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
                    $('#btnUpdateStatusHarian').html('<i class="bi bi-check-circle me-1"></i> Update Status');
                    $('#btnUpdateStatusHarian').prop('disabled', false);
                }
            });
        }

        // Function to edit Tugas Harian
        function editTugasHarian(id) {
            // Fetch tugas harian data via AJAX
            $.ajax({
                url: "{{ url('penugasan/tugas-harian') }}/" + id + "/edit",
                type: 'GET',
                success: function(response) {
                    // Fill form with data
                    $('#edit_tugas_harian_id').val(response.id);
                    $('#edit_tugas_pokok').val(response.tugas_pokok ? response.tugas_pokok.nama_tugas : '-');
                    $('#edit_nama_tugas').val(response.nama_tugas);
                    $('#edit_deskripsi').val(response.deskripsi);
                    $('#edit_tanggal_mulai').val(response.tanggal_mulai);
                    $('#edit_deadline').val(response.deadline);
                    $('#edit_target_value').val(response.target_value);
                    $('#edit_satuan').val(response.satuan);

                    // Show modal
                    $('#modalEditTugasHarian').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Tidak dapat memuat data tugas harian'
                    });
                }
            });
        }

        // Handle Update Tugas Harian button click
        $('#btnUpdateTugasHarian').click(function() {
            const tugasId = $('#edit_tugas_harian_id').val();
            const formData = {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                nama_tugas: $('#edit_nama_tugas').val(),
                deskripsi: $('#edit_deskripsi').val(),
                tanggal_mulai: $('#edit_tanggal_mulai').val(),
                deadline: $('#edit_deadline').val(),
                target_value: $('#edit_target_value').val(),
                satuan: $('#edit_satuan').val()
            };

            // Validation
            if (!formData.nama_tugas) {
                Swal.fire('Peringatan', 'Nama tugas harus diisi', 'warning');
                return;
            }
            if (!formData.tanggal_mulai) {
                Swal.fire('Peringatan', 'Tanggal mulai harus diisi', 'warning');
                return;
            }
            if (!formData.deadline) {
                Swal.fire('Peringatan', 'Deadline harus diisi', 'warning');
                return;
            }
            if (!formData.target_value || formData.target_value < 1) {
                Swal.fire('Peringatan', 'Target minimal 1', 'warning');
                return;
            }
            if (!formData.satuan) {
                Swal.fire('Peringatan', 'Satuan harus diisi', 'warning');
                return;
            }

            // Submit via AJAX
            $.ajax({
                url: "{{ url('penugasan/tugas-harian') }}/" + tugasId,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#btnUpdateTugasHarian').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                    );
                    $('#btnUpdateTugasHarian').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalEditTugasHarian').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Tugas harian berhasil diperbarui',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat memperbarui tugas';
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
                    $('#btnUpdateTugasHarian').html(
                        '<i class="bi bi-check-circle me-1"></i> Update Tugas');
                    $('#btnUpdateTugasHarian').prop('disabled', false);
                }
            });
        });

        // Function to delete Tugas Harian
        function deleteTugasHarian(id, namaTugas) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                html: `Apakah Anda yakin ingin menghapus tugas harian:<br><strong>${namaTugas}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('penugasan/tugas-harian') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Tugas harian berhasil dihapus',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'Terjadi kesalahan saat menghapus tugas';
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
                }
            });
        }

        // Function to validasi tugas
        function validasiTugas(tugasId, jenisTugas) {
            Swal.fire({
                title: 'Validasi Tugas',
                html: `
                    <div class="mb-3 text-start">
                        <label class="form-label fw-semibold">Status Validasi</label>
                        <select class="form-select" id="status_validasi_input" onchange="toggleValidationFields()">
                            <option value="">Pilih Status</option>
                            <option value="diterima">✅ Diterima (Selesai)</option>
                            <option value="revisi">🔄 Perlu Revisi</option>
                        </select>
                    </div>
                    
                    <!-- Fields untuk status diterima -->
                    <div id="fields_diterima" style="display: none;">
                        <!-- INFO PENILAIAN WAKTU (Auto-calculated) -->
                        <div class="alert alert-info text-start mb-3" id="info_penilaian_waktu" style="display: none;">
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-clock-history"></i> Penilaian Waktu (Bobot 80%)
                            </h6>
                            <div class="row small">
                                <div class="col-6">
                                    <strong>Deadline:</strong>
                                    <div id="info_deadline">-</div>
                                </div>
                                <div class="col-6">
                                    <strong>Selesai:</strong>
                                    <div id="info_tanggal_selesai">-</div>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Status:</strong>
                                    <span id="info_status_keterlambatan">-</span>
                                </div>
                                <div>
                                    <strong>Nilai Waktu:</strong>
                                    <span class="badge bg-primary fs-6" id="info_nilai_waktu">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- INPUT PENILAIAN KUALITAS -->
                        <div class="mb-3 text-start">
                            <label class="form-label fw-semibold">
                                Penilaian Kualitas (Bobot 20%) 
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="penilaian_kualitas_input" 
                                   min="0" max="100" step="1" placeholder="0-100"
                                   oninput="updatePreviewPenilaian('${tugasId}', '${jenisTugas}')">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Nilai kualitas pekerjaan dari atasan (0-100)
                            </small>
                        </div>

                        <!-- PREVIEW NILAI AKHIR -->
                        <div class="alert alert-success text-start mb-3" id="preview_nilai_akhir" style="display: none;">
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-calculator"></i> Preview Nilai Akhir
                            </h6>
                            <div class="row small mb-2">
                                <div class="col-6">
                                    <div class="text-muted">Waktu (80%):</div>
                                    <div class="fw-bold" id="preview_kontribusi_waktu">0</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted">Kualitas (20%):</div>
                                    <div class="fw-bold" id="preview_kontribusi_kualitas">0</div>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="text-center">
                                <small class="text-muted">Nilai Akhir:</small>
                                <h3 class="mb-1" id="preview_total">0.00</h3>
                                <span class="badge fs-6" id="preview_grade_badge">-</span>
                            </div>
                        </div>

                        <!-- Update Progress Tugas Pokok -->
                        <div class="mb-3 text-start">
                            <label class="form-label fw-semibold">Update Progress Tugas Pokok</label>
                            <select class="form-select" id="progress_update_type_input" onchange="toggleProgressValue()">
                                <option value="otomatis">Otomatis (berdasarkan target)</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                        
                        <div id="manual_progress" style="display: none;" class="mb-3 text-start">
                            <label class="form-label">Nilai Progress Manual</label>
                            <input type="number" class="form-control" id="progress_value_input" 
                                   min="0" placeholder="Masukkan nilai progress">
                        </div>
                        
                        <div class="mb-3 text-start">
                            <label class="form-label">Catatan (opsional)</label>
                            <textarea class="form-control" id="catatan_validasi_input" rows="2" 
                                      placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Fields untuk status revisi -->
                    <div id="fields_revisi" style="display: none;">
                        <div class="mb-3 text-start">
                            <label class="form-label fw-semibold">Alasan Revisi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="catatan_revisi_input" rows="3" 
                                      placeholder="Jelaskan alasan perlu revisi..."></textarea>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Simpan Validasi',
                cancelButtonText: 'Batal',
                width: '600px',
                didOpen: () => {
                    // Add event listeners after modal opens
                    window.toggleValidationFields = function() {
                        const status = document.getElementById('status_validasi_input').value;
                        const showDiterima = status === 'diterima';

                        document.getElementById('fields_diterima').style.display = showDiterima ? 'block' :
                            'none';
                        document.getElementById('fields_revisi').style.display = status === 'revisi' ?
                            'block' : 'none';

                        // Load info penilaian waktu saat diterima dipilih
                        if (showDiterima) {
                            loadInfoPenilaianWaktu(tugasId, jenisTugas);
                        }
                    };

                    window.toggleProgressValue = function() {
                        const type = document.getElementById('progress_update_type_input').value;
                        document.getElementById('manual_progress').style.display = type === 'manual' ?
                            'block' : 'none';
                    };
                },
                preConfirm: () => {
                    const statusValidasi = document.getElementById('status_validasi_input').value;

                    if (!statusValidasi) {
                        Swal.showValidationMessage('Status validasi harus dipilih');
                        return false;
                    }

                    if (statusValidasi === 'diterima') {
                        const penilaianKualitas = document.getElementById('penilaian_kualitas_input').value;
                        const progressUpdateType = document.getElementById('progress_update_type_input').value;
                        const progressValue = document.getElementById('progress_value_input').value;
                        const catatan = document.getElementById('catatan_validasi_input').value;

                        if (!penilaianKualitas || penilaianKualitas < 0 || penilaianKualitas > 100) {
                            Swal.showValidationMessage('Penilaian kualitas harus diisi dengan nilai 0-100');
                            return false;
                        }

                        if (progressUpdateType === 'manual' && !progressValue) {
                            Swal.showValidationMessage('Nilai progress manual harus diisi');
                            return false;
                        }

                        return {
                            status_validasi: statusValidasi,
                            penilaian_kualitas: penilaianKualitas,
                            progress_update_type: progressUpdateType,
                            progress_value: progressValue,
                            catatan_validasi: catatan
                        };
                    } else if (statusValidasi === 'revisi') {
                        const catatanRevisi = document.getElementById('catatan_revisi_input').value;

                        if (!catatanRevisi.trim()) {
                            Swal.showValidationMessage('Alasan revisi harus diisi');
                            return false;
                        }

                        return {
                            status_validasi: statusValidasi,
                            catatan_revisi: catatanRevisi
                        };
                    }

                    return {
                        status_validasi: statusValidasi
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Prepare base data
                    let requestData = {
                        _token: '{{ csrf_token() }}',
                        jenis_tugas: jenisTugas,
                        status_validasi: result.value.status_validasi
                    };

                    // Add fields based on status
                    if (result.value.status_validasi === 'diterima') {
                        requestData.penilaian_kualitas = result.value.penilaian_kualitas;
                        requestData.progress_update_type = result.value.progress_update_type;
                        if (result.value.progress_value) {
                            requestData.progress_value = result.value.progress_value;
                        }
                        if (result.value.catatan_validasi) {
                            requestData.catatan_validasi = result.value.catatan_validasi;
                        }
                    } else if (result.value.status_validasi === 'revisi') {
                        requestData.catatan_revisi = result.value.catatan_revisi;
                    }

                    // Show loading
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang menyimpan validasi',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/penugasan/validasi-tugas/${tugasId}`,
                        type: 'POST',
                        data: requestData,
                        success: function(response) {
                            let message = response.message || 'Validasi berhasil disimpan';

                            // Jika diterima, tampilkan info penilaian
                            if (response.penilaian) {
                                message += `\n\nNilai Waktu: ${response.penilaian.nilai_waktu}`;
                                message += `\nNilai Kualitas: ${response.penilaian.nilai_kualitas}`;
                                message += `\nNilai Akhir: ${response.penilaian.nilai_akhir}`;
                                message +=
                                    `\nGrade: ${response.penilaian.grade.grade} - ${response.penilaian.grade.kategori}`;
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: message.replace(/\n/g, '<br>'),
                                timer: 3000,
                                showConfirmButton: true
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'Terjadi kesalahan saat validasi';
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
                        }
                    });
                }
            });
        }

        // ========================================
        // PENILAIAN HELPER FUNCTIONS
        // ========================================

        /**
         * Load info penilaian waktu saat modal dibuka
         */
        function loadInfoPenilaianWaktu(tugasId, jenisTugas) {
            const infoBox = document.getElementById('info_penilaian_waktu');
            infoBox.style.display = 'block';

            // Dummy preview dengan nilai kualitas 0
            updatePreviewPenilaian(tugasId, jenisTugas);
        }

        /**
         * Update preview penilaian real-time saat input nilai kualitas
         */
        function updatePreviewPenilaian(tugasId, jenisTugas) {
            const nilaiKualitasInput = document.getElementById('penilaian_kualitas_input');
            const nilaiKualitas = parseFloat(nilaiKualitasInput?.value) || 0;

            // Validasi range
            if (nilaiKualitas < 0 || nilaiKualitas > 100) {
                return;
            }

            $.ajax({
                url: '{{ route('penugasan.preview-penilaian') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    tugas_id: tugasId,
                    jenis_tugas: jenisTugas,
                    nilai_kualitas: nilaiKualitas
                },
                success: function(response) {
                    const preview = response.preview;

                    // Update info waktu
                    document.getElementById('info_deadline').textContent = response.tanggal_deadline;
                    document.getElementById('info_tanggal_selesai').textContent = response.tanggal_selesai;
                    document.getElementById('info_status_keterlambatan').innerHTML =
                        `<span class="badge ${preview.keterlambatan.badge_class}">${preview.keterlambatan.status}</span>`;
                    document.getElementById('info_nilai_waktu').textContent = preview.keterlambatan.nilai;

                    // Update preview nilai akhir
                    if (nilaiKualitas > 0) {
                        document.getElementById('preview_nilai_akhir').style.display = 'block';
                        document.getElementById('preview_kontribusi_waktu').textContent =
                            preview.breakdown.kontribusi_waktu.toFixed(2);
                        document.getElementById('preview_kontribusi_kualitas').textContent =
                            preview.breakdown.kontribusi_kualitas.toFixed(2);
                        document.getElementById('preview_total').textContent =
                            preview.nilai_akhir.toFixed(2);

                        // Update grade badge
                        const gradeBadge = document.getElementById('preview_grade_badge');
                        gradeBadge.className = `badge fs-6 ${preview.grade.badge_class}`;
                        gradeBadge.textContent = `${preview.grade.grade} - ${preview.grade.kategori}`;
                    } else {
                        document.getElementById('preview_nilai_akhir').style.display = 'none';
                    }
                },
                error: function(xhr) {
                    console.error('Error preview penilaian:', xhr);
                }
            });
        }

        // Function to toggle revision history
        function toggleRevisionHistory(tugasId) {
            const historyRow = document.getElementById(`history-${tugasId}`);
            const chevron = document.getElementById(`chevron-${tugasId}`);

            if (historyRow.style.display === 'none') {
                // Show history
                historyRow.style.display = 'table-row';
                chevron.classList.remove('bi-chevron-right');
                chevron.classList.add('bi-chevron-down');

                // Load history via AJAX if not loaded yet
                if (!historyRow.getAttribute('data-loaded')) {
                    loadRevisionHistory(tugasId);
                    historyRow.setAttribute('data-loaded', 'true');
                }
            } else {
                // Hide history
                historyRow.style.display = 'none';
                chevron.classList.remove('bi-chevron-down');
                chevron.classList.add('bi-chevron-right');
            }
        }

        // Function to load revision history via AJAX
        function loadRevisionHistory(tugasId) {
            $.ajax({
                url: `/penugasan/tugas-harian/${tugasId}/history`,
                type: 'GET',
                success: function(response) {
                    let historyHtml = '';

                    if (response.history && response.history.length > 0) {
                        historyHtml = '<div class="small">';
                        response.history.forEach(function(item, index) {
                            const tanggal = new Date(item.tanggal_revisi).toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });

                            historyHtml += `
                                <div class="border-start border-danger border-2 ps-2 mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-danger">Revisi ke-${item.revisi_ke}</strong>
                                        <small class="text-muted">${tanggal}</small>
                                    </div>
                                    <div class="text-muted mb-1">${item.catatan_revisi}</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-person"></i> ${item.direvisi_oleh ? item.direvisi_oleh.nama : 'Unknown'}
                                        </small>
                                        ${item.dokumen_lama ? `
                                                                                                                                    <a href="/dokumen/${item.dokumen_lama.id}/download" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-2">
                                                                                                                                        <i class="bi bi-download"></i> File Lama
                                                                                                                                    </a>
                                                                                                                                ` : ''}
                                    </div>
                                </div>
                            `;
                        });
                        historyHtml += '</div>';
                    } else {
                        historyHtml =
                            '<div class="text-center text-muted py-2 small">Belum ada history revisi</div>';
                    }

                    document.getElementById(`revision-content-${tugasId}`).innerHTML = historyHtml;
                },
                error: function() {
                    document.getElementById(`revision-content-${tugasId}`).innerHTML =
                        '<div class="text-center text-muted py-2 small">Gagal memuat history</div>';
                }
            });
        }

        // Function to view detail
        function viewDetail(id) {
            // Implement detail view functionality
            alert('Detail view for task ID: ' + id);
        }

        // Function to start working on a task (change status from pending to dikerjakan)
        function kerjakanTugas(tugasId, jenisTugas = 'tugas_harian') {
            Swal.fire({
                title: 'Mulai Mengerjakan Tugas?',
                text: 'Status tugas akan berubah menjadi "Dikerjakan"',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Mulai',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tentukan route berdasarkan jenis tugas
                    let url;
                    if (jenisTugas === 'tugas_harian') {
                        url = `/penugasan/tugas-harian/${tugasId}/update-status`;
                    } else if (jenisTugas === 'tugas_tambahan') {
                        url = `/penugasan/tugas-tambahan/${tugasId}/update-status`;
                    } else {
                        Swal.fire('Error', 'Jenis tugas tidak valid', 'error');
                        return;
                    }

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: 'dikerjakan'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Memproses...',
                                text: 'Sedang mengubah status tugas',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Status tugas berhasil diubah menjadi "Dikerjakan"',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'Terjadi kesalahan saat mengubah status tugas';
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
                }
            });
        }

        // Function to upload bukti
        function uploadBukti(tugasId, jenisTugas) {
            Swal.fire({
                title: 'Upload Bukti Pengerjaan',
                html: `
                    <div class="mb-3">
                        <label class="form-label">File Bukti <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file_bukti_input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls" multiple>
                        <small class="form-text text-muted">Format: PDF, DOC, DOCX, JPG, JPEG, PNG, XLSX, XLS (Max 10MB per file)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan_bukti_input" rows="3" placeholder="Jelaskan detail pengerjaan..."></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Upload Bukti',
                cancelButtonText: 'Batal',
                width: '500px',
                preConfirm: () => {
                    const fileInput = document.getElementById('file_bukti_input');
                    const keterangan = document.getElementById('keterangan_bukti_input').value;

                    if (!fileInput.files.length) {
                        Swal.showValidationMessage('File bukti harus dipilih');
                        return false;
                    }

                    // Validate file size (max 10MB per file)
                    for (let file of fileInput.files) {
                        if (file.size > 10 * 1024 * 1024) {
                            Swal.showValidationMessage(
                                `File ${file.name} terlalu besar. Maksimal 10MB per file.`);
                            return false;
                        }
                    }

                    return {
                        files: fileInput.files,
                        keterangan: keterangan
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('tugas_id', tugasId);
                    formData.append('jenis_tugas', jenisTugas);
                    formData.append('keterangan', result.value.keterangan);

                    // Add all selected files
                    for (let file of result.value.files) {
                        formData.append('files[]', file);
                    }

                    $.ajax({
                        url: '{{ route('penugasan.upload-bukti') }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Uploading...',
                                text: 'Sedang mengupload file bukti',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Bukti berhasil diupload',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'Terjadi kesalahan saat upload bukti';
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
                        }
                    });
                }
            });
        }

        // ===== TUGAS TAMBAHAN FUNCTIONS =====

        // View mode toggle for Tugas Tambahan
        $('input[name="viewModeTambahan"]').change(function() {
            let viewMode = $(this).attr('id');
            if (viewMode === 'viewGridTambahan') {
                $('#gridViewTambahan').show();
                $('#tableViewTambahan').hide();
                localStorage.setItem('tugas_tambahan_view_mode', 'grid');
            } else {
                $('#tableViewTambahan').show();
                $('#gridViewTambahan').hide();
                localStorage.setItem('tugas_tambahan_view_mode', 'table');
            }
        });

        // Restore view mode from localStorage for Tugas Tambahan
        const savedViewModeTambahan = localStorage.getItem('tugas_tambahan_view_mode');
        if (savedViewModeTambahan === 'grid') {
            $('#viewGridTambahan').prop('checked', true).trigger('change');
        }

        // Function to edit Tugas Tambahan
        function editTugasTambahan(id) {
            // Fetch tugas tambahan data via AJAX
            $.ajax({
                url: "{{ url('penugasan/tugas-tambahan') }}/" + id + "/edit",
                type: 'GET',
                success: function(response) {
                    // Show edit modal with SweetAlert
                    Swal.fire({
                        title: 'Edit Tugas Tambahan',
                        html: `
                            <form id="formEditTugasTambahan">
                                <div class="mb-3 text-start">
                                    <label class="form-label">Nama Tugas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_nama_tugas_tambahan" value="${response.nama_tugas}" required>
                                </div>
                                <div class="mb-3 text-start">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="edit_deskripsi_tambahan" rows="3">${response.deskripsi || ''}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3 text-start">
                                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="edit_tanggal_mulai_tambahan" value="${response.tanggal_mulai}" required>
                                    </div>
                                    <div class="col-md-6 mb-3 text-start">
                                        <label class="form-label">Deadline <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="edit_deadline_tambahan" value="${response.deadline}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3 text-start">
                                        <label class="form-label">Target <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="edit_target_value_tambahan" value="${response.target_value}" min="1" required>
                                    </div>
                                    <div class="col-md-6 mb-3 text-start">
                                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_satuan_tambahan" value="${response.satuan}" required>
                                    </div>
                                </div>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update Tugas',
                        cancelButtonText: 'Batal',
                        width: '600px',
                        preConfirm: () => {
                            const namaTugas = document.getElementById('edit_nama_tugas_tambahan')
                                .value;
                            const deskripsi = document.getElementById('edit_deskripsi_tambahan')
                                .value;
                            const tanggalMulai = document.getElementById(
                                'edit_tanggal_mulai_tambahan').value;
                            const deadline = document.getElementById('edit_deadline_tambahan')
                                .value;
                            const targetValue = document.getElementById(
                                'edit_target_value_tambahan').value;
                            const satuan = document.getElementById('edit_satuan_tambahan').value;

                            if (!namaTugas) {
                                Swal.showValidationMessage('Nama tugas harus diisi');
                                return false;
                            }
                            if (!tanggalMulai) {
                                Swal.showValidationMessage('Tanggal mulai harus diisi');
                                return false;
                            }
                            if (!deadline) {
                                Swal.showValidationMessage('Deadline harus diisi');
                                return false;
                            }
                            if (!targetValue || targetValue < 1) {
                                Swal.showValidationMessage('Target minimal 1');
                                return false;
                            }
                            if (!satuan) {
                                Swal.showValidationMessage('Satuan harus diisi');
                                return false;
                            }

                            return {
                                nama_tugas: namaTugas,
                                deskripsi: deskripsi,
                                tanggal_mulai: tanggalMulai,
                                deadline: deadline,
                                target_value: targetValue,
                                satuan: satuan
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const formData = {
                                _token: '{{ csrf_token() }}',
                                _method: 'PUT',
                                ...result.value
                            };

                            $.ajax({
                                url: "{{ url('penugasan/tugas-tambahan') }}/" + id,
                                type: 'POST',
                                data: formData,
                                beforeSend: function() {
                                    Swal.fire({
                                        title: 'Memproses...',
                                        text: 'Sedang memperbarui tugas tambahan',
                                        allowOutsideClick: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        }
                                    });
                                },
                                success: function(response) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message ||
                                            'Tugas tambahan berhasil diperbarui',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                },
                                error: function(xhr) {
                                    let errorMessage =
                                        'Terjadi kesalahan saat memperbarui tugas';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMessage = xhr.responseJSON.message;
                                    } else if (xhr.responseJSON && xhr.responseJSON
                                        .errors) {
                                        const errors = xhr.responseJSON.errors;
                                        errorMessage = Object.values(errors).flat().join(
                                            '<br>');
                                    }

                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        html: errorMessage
                                    });
                                }
                            });
                        }
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Tidak dapat memuat data tugas tambahan'
                    });
                }
            });
        }

        // Function to delete Tugas Tambahan
        function deleteTugasTambahan(id, namaTugas) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                html: `Apakah Anda yakin ingin menghapus tugas tambahan:<br><strong>${namaTugas}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('penugasan/tugas-tambahan') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: response.message || 'Tugas tambahan berhasil dihapus',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'Terjadi kesalahan saat menghapus tugas';
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
                }
            });
        }
    </script>
@endpush
