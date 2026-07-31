@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Tim Saya</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Beranda</a></li>
                <li class="breadcrumb-item active">Tim Saya</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Total Anggota</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10">
                                <i class="bi bi-people text-primary fs-3"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-2">{{ $anggotaTim->count() }}</h6>
                                <span class="text-muted small">Anggota aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Tugas Aktif</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10">
                                <i class="bi bi-play-circle text-info fs-3"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-2">{{ $anggotaTim->sum('tugas_aktif') }}</h6>
                                <span class="text-muted small">Sedang dikerjakan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Perlu Validasi</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10">
                                <i class="bi bi-check-circle text-warning fs-3"></i>
                            </div>
                            <div class="ps-3">
                                @php
                                    $validasiCount = \Modules\Penugasan\Models\TugasHarian::whereIn(
                                        'pegawai_id',
                                        $anggotaTim->pluck('id'),
                                    )
                                        ->where('status', 'validasi')
                                        ->count();
                                @endphp
                                <h6 class="mb-0 fw-bold fs-2">{{ $validasiCount }}</h6>
                                <span class="text-muted small">Menunggu review</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Rata-rata Workload</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10">
                                <i class="bi bi-speedometer text-success fs-3"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-2">{{ number_format($anggotaTim->avg('workload_persen'), 0) }}%
                                </h6>
                                <span class="text-muted small">Beban kerja tim</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Aksi Cepat</h5>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('penugasan.tim.form-berikan-tugas') }}"
                                    class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-send me-2"></i>Berikan Tugas
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('penugasan.tim.daftar-validasi') }}"
                                    class="btn btn-warning w-100 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-check-square me-2"></i>Validasi Tugas
                                    @if ($validasiCount > 0)
                                        <span class="badge bg-danger ms-2">{{ $validasiCount }}</span>
                                    @endif
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('penugasan.tim.monitoring') }}"
                                    class="btn btn-info w-100 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-graph-up me-2"></i>Monitoring Tim
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-outline-secondary w-100" data-bs-toggle="collapse"
                                    data-bs-target="#filterSection">
                                    <i class="bi bi-funnel me-2"></i>Filter Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="collapse mb-4" id="filterSection">
            <div class="card shadow-sm">
                <div class="card-body bg-light">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small">Bidang</label>
                            <select class="form-select" id="filterBidang">
                                <option value="">Semua Bidang</option>
                                @foreach ($anggotaTim->pluck('bidang')->unique()->filter() as $bidang)
                                    <option value="{{ $bidang->id }}">{{ $bidang->nama_bidang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Jabatan</label>
                            <select class="form-select" id="filterJabatan">
                                <option value="">Semua Jabatan</option>
                                @foreach ($anggotaTim->pluck('jabatan')->unique()->filter() as $jabatan)
                                    <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Workload</label>
                            <select class="form-select" id="filterWorkload">
                                <option value="">Semua Level</option>
                                <option value="ringan">Ringan (< 30%)</option>
                                <option value="sedang">Sedang (30-60%)</option>
                                <option value="berat">Berat (> 60%)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Cari</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Nama pegawai...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Anggota Tim Table -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                @if ($anggotaTim->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-people fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Belum Ada Anggota Tim</h5>
                        <p class="text-muted">Anda belum memiliki bawahan yang terdaftar dalam sistem.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="anggotaTimTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 20%;">Nama</th>
                                    <th style="width: 15%;">Jabatan</th>
                                    <th style="width: 12%;">Bidang</th>
                                    <th style="width: 8%;" class="text-center">Pending</th>
                                    <th style="width: 8%;" class="text-center">Aktif</th>
                                    <th style="width: 8%;" class="text-center">Selesai</th>
                                    <th style="width: 12%;">Workload</th>
                                    <th style="width: 12%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($anggotaTim as $index => $anggota)
                                    <tr class="anggota-row" data-bidang="{{ $anggota->profile->bidang_id }}"
                                        data-jabatan="{{ $anggota->profile->jabatan_id }}"
                                        data-workload="{{ $anggota->workload_persen }}"
                                        data-nama="{{ strtolower($anggota->nama) }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-2"
                                                    style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold;">
                                                    {{ strtoupper(substr($anggota->nama, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $anggota->nama }}</div>
                                                    <small
                                                        class="text-muted">{{ $anggota->profile->nomor_identitas ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $anggota->profile->jabatan->nama ?? '-' }}</small>
                                        </td>
                                        <td>
                                            @if ($anggota->profile->bidang)
                                                <span class="badge bg-secondary">{{ $anggota->profile->bidang->nama }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark">{{ $anggota->tugas_pending }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $anggota->tugas_aktif }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $anggota->tugas_selesai }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $workloadClass =
                                                    $anggota->workload_persen >= 70
                                                        ? 'danger'
                                                        : ($anggota->workload_persen >= 40
                                                            ? 'warning'
                                                            : 'success');
                                            @endphp
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ $workloadClass }}" role="progressbar"
                                                        style="width: {{ $anggota->workload_persen }}%"
                                                        aria-valuenow="{{ $anggota->workload_persen }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small
                                                    class="fw-bold text-nowrap">{{ number_format($anggota->workload_persen, 0) }}%</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('penugasan.tim.detail-anggota', $anggota->id) }}"
                                                    class="btn btn-outline-info" title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('penugasan.tim.form-berikan-tugas') }}?pegawai={{ $anggota->id }}"
                                                    class="btn btn-outline-primary" title="Beri Tugas">
                                                    <i class="bi bi-send"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- No Results Message -->
                    <div class="d-none text-center py-4" id="noResultsMessage">
                        <i class="bi bi-info-circle me-2 text-info"></i>
                        <span class="text-muted">Tidak ada anggota tim yang sesuai dengan filter yang dipilih.</span>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .btn-group-sm>.btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .avatar-circle {
            flex-shrink: 0;
        }

        .progress {
            min-width: 60px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterBidang = document.getElementById('filterBidang');
            const filterJabatan = document.getElementById('filterJabatan');
            const filterWorkload = document.getElementById('filterWorkload');
            const searchInput = document.getElementById('searchInput');
            const anggotaRows = document.querySelectorAll('.anggota-row');
            const noResultsMessage = document.getElementById('noResultsMessage');

            function applyFilters() {
                const bidangValue = filterBidang.value;
                const jabatanValue = filterJabatan.value;
                const workloadValue = filterWorkload.value;
                const searchValue = searchInput.value.toLowerCase();

                let visibleCount = 0;

                anggotaRows.forEach(row => {
                    let show = true;

                    // Filter bidang
                    if (bidangValue && row.dataset.bidang !== bidangValue) {
                        show = false;
                    }

                    // Filter jabatan
                    if (jabatanValue && row.dataset.jabatan !== jabatanValue) {
                        show = false;
                    }

                    // Filter workload
                    if (workloadValue) {
                        const workload = parseFloat(row.dataset.workload);
                        if (workloadValue === 'ringan' && workload >= 30) show = false;
                        if (workloadValue === 'sedang' && (workload < 30 || workload > 60)) show = false;
                        if (workloadValue === 'berat' && workload <= 60) show = false;
                    }

                    // Filter search
                    if (searchValue && !row.dataset.nama.includes(searchValue)) {
                        show = false;
                    }

                    // Show/hide row
                    if (show) {
                        row.classList.remove('d-none');
                        visibleCount++;
                    } else {
                        row.classList.add('d-none');
                    }
                });

                // Show/hide no results message
                if (visibleCount === 0) {
                    noResultsMessage.classList.remove('d-none');
                } else {
                    noResultsMessage.classList.add('d-none');
                }
            }

            // Event listeners
            filterBidang.addEventListener('change', applyFilters);
            filterJabatan.addEventListener('change', applyFilters);
            filterWorkload.addEventListener('change', applyFilters);

            // Debounce search
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(applyFilters, 300);
            });
        });
    </script>
@endpush
