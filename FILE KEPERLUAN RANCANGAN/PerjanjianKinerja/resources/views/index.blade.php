@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Perjanjian Kinerja</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Perjanjian Kinerja</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Total Perjanjian <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $stats['total'] }}</h6>
                                <span class="text-muted small pt-1">dokumen</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Status Dokumen <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10">
                                <i class="bi bi-check-circle text-success"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $stats['aktif'] }}/{{ $stats['draft'] }}</h6>
                                <span class="text-muted small pt-1">aktif/draft</span>
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
                                <span class="text-muted small pt-1">dokumen</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Total Anggaran <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10">
                                <i class="bi bi-cash-stack text-warning"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">Rp {{ number_format($stats['total_anggaran'], 0, ',', '.') }}</h6>
                                <span class="text-muted small pt-1">anggaran</span>
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
                                        <i class="bi bi-file-earmark-text-fill text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Perjanjian Kinerja</span>
                                        <small class="d-block text-muted fw-normal mt-1">Kelola dokumen perjanjian
                                            kinerja pegawai</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <a href="{{ route('perjanjian-kinerja.create') }}"
                                    class="btn btn-primary btn-lg shadow-sm px-4 py-2">
                                    <i class="bi bi-plus-circle me-1"></i> Buat Perjanjian Baru
                                </a>
                            </div>
                        </div>

                        <!-- Filter Form -->
                        <form id="filterForm" action="{{ route('perjanjian-kinerja.index') }}" method="GET">
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
                                        <option value="Draft" {{ request()->status == 'Draft' ? 'selected' : '' }}>Draft
                                        </option>
                                        <option value="Generated"
                                            {{ request()->status == 'Generated' ? 'selected' : '' }}>Generated</option>
                                        <option value="Menunggu_TTD"
                                            {{ request()->status == 'Menunggu_TTD' ? 'selected' : '' }}>Menunggu TTD
                                        </option>
                                        <option value="Aktif" {{ request()->status == 'Aktif' ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="Selesai" {{ request()->status == 'Selesai' ? 'selected' : '' }}>
                                            Selesai</option>
                                        <option value="Dibatalkan"
                                            {{ request()->status == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-3 mb-3">
                                    <label class="form-label">Pencarian</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="searchPK" name="search"
                                            value="{{ request()->search }}" placeholder="Cari perjanjian...">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bi bi-filter me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('perjanjian-kinerja.index', ['tahun' => $tahun]) }}"
                                            class="btn btn-outline-secondary px-3">
                                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                        </a>
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
                                <table class="table table-hover table-striped" id="pkTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" width="50">#</th>
                                            <th>Nomor</th>
                                            <th>Pegawai</th>
                                            <th>Atasan</th>
                                            <th>Periode</th>
                                            <th>Status</th>
                                            <th>Anggaran</th>
                                            <th class="text-center" width="120">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pkTableBody">
                                        @forelse($perjanjians as $index => $pk)
                                            <tr>
                                                <td class="text-center">
                                                    {{ ($perjanjians->currentPage() - 1) * $perjanjians->perPage() + $index + 1 }}
                                                </td>
                                                <td>
                                                    <span class="d-block text-primary fw-bold">
                                                        {{ $pk->nomor_perjanjian }}
                                                    </span>
                                                    <small
                                                        class="text-muted">{{ $pk->template->nama_template ?? 'Template tidak ada' }}</small>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1">
                                                            <span
                                                                class="d-block fw-semibold">{{ $pk->pegawai->nama }}</span>
                                                            @if (isset($pk->pegawai->nip))
                                                                <small class="text-muted">NIP
                                                                    {{ $pk->pegawai->nip }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if (isset($pk->pegawai->jabatan))
                                                        <span class="badge bg-light text-dark border mt-1">
                                                            {{ $pk->pegawai->jabatan->nama }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-semibold">{{ $pk->atasan->nama }}</span>
                                                    <br>
                                                    @if (isset($pk->atasan->jabatan))
                                                        <small class="text-muted">{{ $pk->atasan->jabatan->nama }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $pk->tahun }}</span>
                                                    <br>
                                                    <small>
                                                        {{ date('d M Y', strtotime($pk->periode_mulai)) }} -
                                                        {{ date('d M Y', strtotime($pk->periode_selesai)) }}
                                                    </small>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClass = [
                                                            'Draft' => 'bg-secondary',
                                                            'Generated' => 'bg-info',
                                                            'Menunggu_TTD' => 'bg-warning',
                                                            'Aktif' => 'bg-success',
                                                            'Selesai' => 'bg-primary',
                                                            'Dibatalkan' => 'bg-danger',
                                                        ];
                                                        $statusText = str_replace('_', ' ', $pk->status_dokumen);
                                                    @endphp
                                                    <span
                                                        class="badge {{ $statusClass[$pk->status_dokumen] }}">{{ $statusText }}</span>
                                                    @if ($pk->is_locked)
                                                        <i class="bi bi-lock-fill text-muted ms-1"
                                                            title="Dokumen Terkunci"></i>
                                                    @endif
                                                    <br>
                                                    @if ($pk->tanggal_ttd)
                                                        <small class="text-muted">
                                                            <i class="bi bi-pen-fill"></i>
                                                            {{ date('d/m/Y', strtotime($pk->tanggal_ttd)) }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="fw-bold">Rp
                                                        {{ number_format($pk->total_anggaran, 0, ',', '.') }}</div>
                                                    @if ($pk->sasaran->count() > 0)
                                                        <small class="text-muted">{{ $pk->sasaran->count() }}
                                                            sasaran</small>
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
                                                                <a href="{{ route('perjanjian-kinerja.show', $pk->id) }}"
                                                                    class="dropdown-item">
                                                                    <i class="bi bi-eye text-primary me-2"></i> Lihat
                                                                    Detail
                                                                </a>
                                                            </li>

                                                            <!-- Edit -->
                                                            @if (!$pk->is_locked)
                                                                <li>
                                                                    <a href="{{ route('perjanjian-kinerja.edit', $pk->id) }}"
                                                                        class="dropdown-item">
                                                                        <i class="bi bi-pencil text-warning me-2"></i> Edit
                                                                        Data
                                                                    </a>
                                                                </li>
                                                            @endif

                                                            <!-- Generate PDF -->
                                                            <li>
                                                                <a href="javascript:void(0)"
                                                                    onclick="confirmGeneratePDF({{ $pk->id }})"
                                                                    class="dropdown-item">
                                                                    <i class="bi bi-file-pdf text-danger me-2"></i>
                                                                    Generate PDF
                                                                </a>
                                                            </li>

                                                            <!-- PDF Operations -->
                                                            @if ($pk->dokumen->where('is_latest', true)->first())
                                                                <li>
                                                                    <a href="{{ route('perjanjian-kinerja.preview', $pk->id) }}"
                                                                        class="dropdown-item" target="_blank">
                                                                        <i class="bi bi-file-text text-info me-2"></i>
                                                                        Preview PDF
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="{{ route('perjanjian-kinerja.download', $pk->id) }}"
                                                                        class="dropdown-item">
                                                                        <i class="bi bi-download text-success me-2"></i>
                                                                        Download PDF
                                                                    </a>
                                                                </li>
                                                            @endif

                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>

                                                            <!-- Hitung Total Anggaran -->
                                                            <li>
                                                                <a href="javascript:void(0)"
                                                                    onclick="calculateAnggaran({{ $pk->id }})"
                                                                    class="dropdown-item">
                                                                    <i class="bi bi-calculator text-primary me-2"></i>
                                                                    Hitung Anggaran
                                                                </a>
                                                            </li>

                                                            <!-- TTD Perjanjian -->
                                                            @if (($pk->status_dokumen == 'Menunggu_TTD' || $pk->status_dokumen == 'Generated') && !$pk->tanggal_ttd)
                                                                <li>
                                                                    <a href="javascript:void(0)"
                                                                        onclick="showSignModal({{ $pk->id }})"
                                                                        class="dropdown-item">
                                                                        <i class="bi bi-pen text-success me-2"></i> Tanda
                                                                        Tangani
                                                                    </a>
                                                                </li>
                                                            @endif

                                                            <!-- Lock/Unlock -->
                                                            @if (!$pk->is_locked)
                                                                <li>
                                                                    <a href="javascript:void(0)"
                                                                        onclick="confirmLock({{ $pk->id }})"
                                                                        class="dropdown-item">
                                                                        <i class="bi bi-lock text-warning me-2"></i> Kunci
                                                                        Dokumen
                                                                    </a>
                                                                </li>
                                                            @elseif(!$pk->tanggal_ttd)
                                                                <li>
                                                                    <a href="javascript:void(0)"
                                                                        onclick="confirmUnlock({{ $pk->id }})"
                                                                        class="dropdown-item">
                                                                        <i class="bi bi-unlock text-warning me-2"></i> Buka
                                                                        Kunci
                                                                    </a>
                                                                </li>
                                                            @endif

                                                            <!-- Delete -->
                                                            @if ($pk->status_dokumen == 'Draft' && !$pk->is_locked)
                                                                <li>
                                                                    <hr class="dropdown-divider">
                                                                </li>
                                                                <li>
                                                                    <a href="javascript:void(0)"
                                                                        onclick="confirmDelete({{ $pk->id }})"
                                                                        class="dropdown-item text-danger">
                                                                        <i class="bi bi-trash text-danger me-2"></i> Hapus
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <div class="py-3">
                                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                                        <p class="text-muted mt-2">Belum ada perjanjian kinerja</p>
                                                        <a href="{{ route('perjanjian-kinerja.create') }}"
                                                            class="btn btn-sm btn-primary mt-1">
                                                            <i class="bi bi-plus-circle me-1"></i> Buat Perjanjian Baru
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
                                {{ $perjanjians->withQueryString()->links() }}
                            </div>
                        </div>

                        <!-- Grid View -->
                        <div id="gridView" style="display: none;">
                            <div class="row" id="pkGridBody">
                                @forelse($perjanjians as $pk)
                                    @php
                                        $statusClass = [
                                            'Draft' => 'bg-secondary',
                                            'Generated' => 'bg-info',
                                            'Menunggu_TTD' => 'bg-warning',
                                            'Aktif' => 'bg-success',
                                            'Selesai' => 'bg-primary',
                                            'Dibatalkan' => 'bg-danger',
                                        ];
                                        $statusText = str_replace('_', ' ', $pk->status_dokumen);
                                        $borderClass = $pk->is_locked ? 'border-success' : '';
                                    @endphp
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card pk-card h-100 shadow-sm {{ $borderClass }}">
                                            <div class="card-header bg-transparent">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <span
                                                            class="text-primary fw-bold">{{ $pk->nomor_perjanjian }}</span>
                                                        @if ($pk->is_locked)
                                                            <i class="bi bi-lock-fill text-success ms-1"
                                                                title="Dokumen Terkunci"></i>
                                                        @endif
                                                    </div>
                                                    <span
                                                        class="badge {{ $statusClass[$pk->status_dokumen] }}">{{ $statusText }}</span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title mb-3">
                                                    <span class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-person-badge text-primary me-2"></i>
                                                        <span>{{ $pk->pegawai->nama }}</span>
                                                    </span>
                                                    <small class="text-muted d-flex align-items-center">
                                                        <i class="bi bi-briefcase me-2"></i>
                                                        <span>{{ $pk->pegawai->jabatan->nama ?? '-' }}</span>
                                                    </small>
                                                </h6>
                                                <div class="mb-3">
                                                    <small class="text-muted d-flex align-items-center">
                                                        <i class="bi bi-arrow-up-circle me-2"></i>
                                                        <span>Atasan: {{ $pk->atasan->nama }}</span>
                                                    </small>
                                                </div>
                                                <p class="card-text small d-flex align-items-center mb-2">
                                                    <i class="bi bi-calendar-range text-info me-2"></i>
                                                    <span>
                                                        <strong>Periode:</strong> {{ $pk->tahun }}
                                                        <br>
                                                        <span class="text-muted">
                                                            {{ date('d M Y', strtotime($pk->periode_mulai)) }} -
                                                            {{ date('d M Y', strtotime($pk->periode_selesai)) }}
                                                        </span>
                                                    </span>
                                                </p>
                                                <p class="card-text small d-flex align-items-center mb-2">
                                                    <i class="bi bi-cash-stack text-success me-2"></i>
                                                    <span>
                                                        <strong>Anggaran:</strong> Rp
                                                        {{ number_format($pk->total_anggaran, 0, ',', '.') }}
                                                    </span>
                                                </p>
                                                <div class="card-text small d-flex align-items-center mb-2">
                                                    <i class="bi bi-list-check text-primary me-2"></i>
                                                    <span>
                                                        <strong>Sasaran:</strong> {{ $pk->sasaran->count() }} sasaran
                                                    </span>
                                                </div>
                                                @if ($pk->tanggal_ttd)
                                                    <p class="card-text small d-flex align-items-center mt-3">
                                                        <i class="bi bi-pen text-warning me-2"></i>
                                                        <span>
                                                            <strong>TTD:</strong>
                                                            {{ date('d M Y', strtotime($pk->tanggal_ttd)) }}
                                                            <br>di {{ $pk->tempat_ttd }}
                                                        </span>
                                                    </p>
                                                @endif

                                                <div class="d-flex flex-wrap gap-1 mt-3">
                                                    @if ($pk->dokumen->where('is_latest', true)->first())
                                                        <div class="badge bg-info bg-opacity-10 text-info">
                                                            <i class="bi bi-file-pdf me-1"></i>
                                                            Dokumen
                                                            v{{ $pk->dokumen->where('is_latest', true)->first()->versi }}
                                                        </div>
                                                    @endif
                                                    @if ($pk->template)
                                                        <div class="badge bg-secondary bg-opacity-10 text-secondary">
                                                            <i class="bi bi-layout-text-window me-1"></i>
                                                            {{ $pk->template->kode_template ?? 'Template' }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <div class="btn-group btn-group-sm w-100">
                                                    <a href="{{ route('perjanjian-kinerja.show', $pk->id) }}"
                                                        class="btn btn-outline-primary" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if (!$pk->is_locked)
                                                        <a href="{{ route('perjanjian-kinerja.edit', $pk->id) }}"
                                                            class="btn btn-outline-warning" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endif
                                                    <button onclick="confirmGeneratePDF({{ $pk->id }})"
                                                        class="btn btn-outline-danger" title="Generate PDF">
                                                        <i class="bi bi-file-pdf"></i>
                                                    </button>
                                                    @if ($pk->dokumen->where('is_latest', true)->first())
                                                        <a href="{{ route('perjanjian-kinerja.download', $pk->id) }}"
                                                            class="btn btn-outline-success" title="Download PDF">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    @endif
                                                    @if (($pk->status_dokumen == 'Menunggu_TTD' || $pk->status_dokumen == 'Generated') && !$pk->tanggal_ttd)
                                                        <button onclick="showSignModal({{ $pk->id }})"
                                                            class="btn btn-outline-info" title="Tanda Tangan">
                                                            <i class="bi bi-pen"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">Belum ada perjanjian kinerja</p>
                                        <a href="{{ route('perjanjian-kinerja.create') }}"
                                            class="btn btn-sm btn-primary mt-1">
                                            <i class="bi bi-plus-circle me-1"></i> Buat Perjanjian Baru
                                        </a>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Pagination for Grid View -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $perjanjians->withQueryString()->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Generate PDF -->
    <div class="modal fade" id="modalGeneratePDF" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Dokumen PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <span>Dokumen PDF akan dibuat berdasarkan template yang dipilih. Pastikan semua data perjanjian
                            kinerja sudah benar dan lengkap.</span>
                    </div>
                    <form id="formGeneratePDF">
                        @csrf
                        <input type="hidden" id="pdf_pk_id" name="pk_id">
                    </form>
                    <p>Apakah Anda yakin ingin men-generate dokumen PDF untuk perjanjian kinerja ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="btnGeneratePDF">
                        <i class="bi bi-file-pdf me-1"></i> Generate PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sign Document -->
    <div class="modal fade" id="modalSignDocument" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tanda Tangan Perjanjian Kinerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span>Dokumen yang sudah ditandatangani akan otomatis dikunci dan tidak dapat diubah lagi.</span>
                    </div>
                    <form id="formSignDocument" method="POST">
                        @csrf
                        <input type="hidden" id="sign_pk_id" name="pk_id">

                        <div class="mb-3">
                            <label for="tanggal_ttd" class="form-label">Tanggal Tanda Tangan</label>
                            <input type="date" class="form-control" id="tanggal_ttd" name="tanggal_ttd" required
                                value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="mb-3">
                            <label for="tempat_ttd" class="form-label">Tempat Tanda Tangan</label>
                            <input type="text" class="form-control" id="tempat_ttd" name="tempat_ttd" required
                                placeholder="Contoh: Sofifi" value="Sofifi">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-success" id="btnSignDocument">
                        <i class="bi bi-pen me-1"></i> Tanda Tangani
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Lock Confirmation -->
    <div class="modal fade" id="modalLockConfirm" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Kunci Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span>Dokumen yang dikunci tidak akan dapat diubah sampai dibuka kembali.</span>
                    </div>
                    <p>Apakah Anda yakin ingin mengunci dokumen perjanjian kinerja ini?</p>
                    <form id="formLockDocument">
                        @csrf
                        <input type="hidden" id="lock_pk_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-warning" id="btnLockDocument">
                        <i class="bi bi-lock me-1"></i> Kunci Dokumen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Unlock Confirmation -->
    <div class="modal fade" id="modalUnlockConfirm" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Buka Kunci Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <span>Membuka kunci dokumen akan memungkinkan untuk melakukan perubahan pada dokumen ini.</span>
                    </div>
                    <p>Apakah Anda yakin ingin membuka kunci dokumen perjanjian kinerja ini?</p>
                    <form id="formUnlockDocument">
                        @csrf
                        <input type="hidden" id="unlock_pk_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-info" id="btnUnlockDocument">
                        <i class="bi bi-unlock me-1"></i> Buka Kunci
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
                    <p>Apakah Anda yakin ingin menghapus perjanjian kinerja ini?</p>
                    <form id="formDeletePK" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" id="delete_pk_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-danger" id="btnDeletePK">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Card Styles */
        .pk-card {
            position: relative;
            overflow: hidden;
            border-radius: 12px !important;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
        }

        .pk-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .pk-card.border-success {
            border-color: #28a745 !important;
            border-width: 2px !important;
        }

        /* Table Styles */
        #pkTable thead th {
            font-weight: 600;
            color: #495057;
        }

        #pkTable tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.03);
        }

        /* Card Icon */
        .card-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }

        /* Pagination Styles */
        .pagination {
            --bs-pagination-color: #5F71E4;
            --bs-pagination-hover-color: #4558CA;
            --bs-pagination-focus-color: #4558CA;
            --bs-pagination-active-bg: #5F71E4;
            --bs-pagination-active-border-color: #5F71E4;
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
        .card-footer .btn-group .btn {
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
                    localStorage.setItem('pk_view_mode', 'grid');
                } else {
                    $('#tableView').show();
                    $('#gridView').hide();
                    localStorage.setItem('pk_view_mode', 'table');
                }
            });

            // Restore view mode from localStorage
            const savedViewMode = localStorage.getItem('pk_view_mode');
            if (savedViewMode === 'grid') {
                $('#viewGrid').prop('checked', true).trigger('change');
            }

            // Generate PDF button handler
            $('#btnGeneratePDF').click(function() {
                const pkId = $('#pdf_pk_id').val();
                generatePDF(pkId);
            });

            // Sign Document button handler
            $('#btnSignDocument').click(function() {
                const pkId = $('#sign_pk_id').val();
                const tanggalTtd = $('#tanggal_ttd').val();
                const tempatTtd = $('#tempat_ttd').val();

                if (!tanggalTtd) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Tanggal tanda tangan harus diisi'
                    });
                    return;
                }

                signDocument(pkId, tanggalTtd, tempatTtd);
            });

            // Lock Document button handler
            $('#btnLockDocument').click(function() {
                const pkId = $('#lock_pk_id').val();
                lockDocument(pkId);
            });

            // Unlock Document button handler
            $('#btnUnlockDocument').click(function() {
                const pkId = $('#unlock_pk_id').val();
                unlockDocument(pkId);
            });

            // Delete PK button handler
            $('#btnDeletePK').click(function() {
                const pkId = $('#delete_pk_id').val();
                deletePK(pkId);
            });

            // Reset form button handler
            $('.btn-reset-form').click(function(e) {
                e.preventDefault();
                $('#filterForm').find('select, input').val('');
                window.location.href = "{{ route('perjanjian-kinerja.index', ['tahun' => $tahun]) }}";
            });
        });

        // Function to confirm generate PDF
        function confirmGeneratePDF(id) {
            $('#pdf_pk_id').val(id);
            $('#modalGeneratePDF').modal('show');
        }

        // Function to generate PDF
        function generatePDF(id) {
            $.ajax({
                url: "{{ url('perjanjian-kinerja') }}/" + id + "/generate",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    // Show loading indicator
                    $('#btnGeneratePDF').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                        );
                    $('#btnGeneratePDF').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalGeneratePDF').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Dokumen PDF berhasil di-generate',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat generate dokumen';
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
                    $('#btnGeneratePDF').html('<i class="bi bi-file-pdf me-1"></i> Generate PDF');
                    $('#btnGeneratePDF').prop('disabled', false);
                }
            });
        }

        // Function to show sign modal
        function showSignModal(id) {
            $('#sign_pk_id').val(id);
            $('#modalSignDocument').modal('show');
        }

        // Function to sign document
        function signDocument(id, tanggalTtd, tempatTtd) {
            $.ajax({
                url: "{{ url('perjanjian-kinerja') }}/" + id + "/sign",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    tanggal_ttd: tanggalTtd,
                    tempat_ttd: tempatTtd
                },
                beforeSend: function() {
                    // Show loading indicator
                    $('#btnSignDocument').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                        );
                    $('#btnSignDocument').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalSignDocument').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Dokumen berhasil ditandatangani',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat menandatangani dokumen';
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
                    $('#btnSignDocument').html('<i class="bi bi-pen me-1"></i> Tanda Tangani');
                    $('#btnSignDocument').prop('disabled', false);
                }
            });
        }

        // Function to calculate anggaran
        function calculateAnggaran(id) {
            Swal.fire({
                title: 'Menghitung anggaran...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ url('perjanjian-kinerja') }}/" + id + "/calculate-anggaran",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: `Total anggaran: ${response.formatted}`,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat menghitung anggaran';
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

        // Function to confirm lock
        function confirmLock(id) {
            $('#lock_pk_id').val(id);
            $('#modalLockConfirm').modal('show');
        }

        // Function to lock document
        function lockDocument(id) {
            $.ajax({
                url: "{{ url('perjanjian-kinerja') }}/" + id + "/lock",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    // Show loading indicator
                    $('#btnLockDocument').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                        );
                    $('#btnLockDocument').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalLockConfirm').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Dokumen berhasil dikunci',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat mengunci dokumen';
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
                    $('#btnLockDocument').html('<i class="bi bi-lock me-1"></i> Kunci Dokumen');
                    $('#btnLockDocument').prop('disabled', false);
                }
            });
        }

        // Function to confirm unlock
        function confirmUnlock(id) {
            $('#unlock_pk_id').val(id);
            $('#modalUnlockConfirm').modal('show');
        }

        // Function to unlock document
        function unlockDocument(id) {
            $.ajax({
                url: "{{ url('perjanjian-kinerja') }}/" + id + "/unlock",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    // Show loading indicator
                    $('#btnUnlockDocument').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                        );
                    $('#btnUnlockDocument').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalUnlockConfirm').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Dokumen berhasil dibuka kuncinya',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat membuka kunci dokumen';
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
                    $('#btnUnlockDocument').html('<i class="bi bi-unlock me-1"></i> Buka Kunci');
                    $('#btnUnlockDocument').prop('disabled', false);
                }
            });
        }

        // Function to confirm delete
        function confirmDelete(id) {
            $('#delete_pk_id').val(id);
            $('#modalDeleteConfirm').modal('show');
        }

        // Function to delete PK
        function deletePK(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ url('perjanjian-kinerja') }}/" + id;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';

            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';

            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);

            $.ajax({
                url: "{{ url('perjanjian-kinerja') }}/" + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    // Show loading indicator
                    $('#btnDeletePK').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                        );
                    $('#btnDeletePK').prop('disabled', true);
                },
                success: function(response) {
                    $('#modalDeleteConfirm').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: 'Perjanjian kinerja berhasil dihapus.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Gagal menghapus perjanjian kinerja';
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
                    $('#btnDeletePK').html('<i class="bi bi-trash me-1"></i> Hapus');
                    $('#btnDeletePK').prop('disabled', false);
                }
            });
        }
    </script>
@endpush
