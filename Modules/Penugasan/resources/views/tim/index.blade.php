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

        <!-- Anggota Tim Cards -->
        <div class="row" id="anggotaTimContainer">
            @forelse($anggotaTim as $anggota)
                <div class="col-xl-4 col-lg-6 mb-4 anggota-card" data-bidang="{{ $anggota->bidang_id }}"
                    data-jabatan="{{ $anggota->jabatan_id }}" data-workload="{{ $anggota->workload_persen }}"
                    data-nama="{{ strtolower($anggota->nama) }}">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body">
                            <!-- Header -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-circle bg-primary text-white me-3"
                                    style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold;">
                                    {{ strtoupper(substr($anggota->nama, 0, 2)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $anggota->nama }}</h5>
                                    <small class="text-muted">{{ $anggota->jabatan->nama_jabatan ?? '-' }}</small>
                                    <br>
                                    @if ($anggota->bidang)
                                        <span class="badge bg-secondary">{{ $anggota->bidang->nama_bidang }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Statistik Tugas -->
                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-warning fs-5">{{ $anggota->tugas_pending }}</div>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-primary fs-5">{{ $anggota->tugas_aktif }}</div>
                                        <small class="text-muted">Aktif</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-success fs-5">{{ $anggota->tugas_selesai }}</div>
                                        <small class="text-muted">Selesai</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Workload Bar -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">Workload</small>
                                    <small class="fw-bold">{{ number_format($anggota->workload_persen, 0) }}%</small>
                                </div>
                                @php
                                    $workloadClass =
                                        $anggota->workload_persen >= 70
                                            ? 'danger'
                                            : ($anggota->workload_persen >= 40
                                                ? 'warning'
                                                : 'success');
                                @endphp
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-{{ $workloadClass }}" role="progressbar"
                                        style="width: {{ $anggota->workload_persen }}%"
                                        aria-valuenow="{{ $anggota->workload_persen }}" aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <a href="{{ route('penugasan.tim.detail-anggota', $anggota->id) }}"
                                    class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
                                <a href="{{ route('penugasan.tim.form-berikan-tugas') }}?pegawai={{ $anggota->id }}"
                                    class="btn btn-sm btn-primary flex-grow-1">
                                    <i class="bi bi-send me-1"></i> Beri Tugas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-people fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">Belum Ada Anggota Tim</h5>
                            <p class="text-muted">Anda belum memiliki bawahan yang terdaftar dalam sistem.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- No Results Message -->
        <div class="row d-none" id="noResultsMessage">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    Tidak ada anggota tim yang sesuai dengan filter yang dipilih.
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterBidang = document.getElementById('filterBidang');
            const filterJabatan = document.getElementById('filterJabatan');
            const filterWorkload = document.getElementById('filterWorkload');
            const searchInput = document.getElementById('searchInput');
            const anggotaCards = document.querySelectorAll('.anggota-card');
            const noResultsMessage = document.getElementById('noResultsMessage');

            function applyFilters() {
                const bidangValue = filterBidang.value;
                const jabatanValue = filterJabatan.value;
                const workloadValue = filterWorkload.value;
                const searchValue = searchInput.value.toLowerCase();

                let visibleCount = 0;

                anggotaCards.forEach(card => {
                    let show = true;

                    // Filter bidang
                    if (bidangValue && card.dataset.bidang !== bidangValue) {
                        show = false;
                    }

                    // Filter jabatan
                    if (jabatanValue && card.dataset.jabatan !== jabatanValue) {
                        show = false;
                    }

                    // Filter workload
                    if (workloadValue) {
                        const workload = parseFloat(card.dataset.workload);
                        if (workloadValue === 'ringan' && workload >= 30) show = false;
                        if (workloadValue === 'sedang' && (workload < 30 || workload > 60)) show = false;
                        if (workloadValue === 'berat' && workload <= 60) show = false;
                    }

                    // Filter search
                    if (searchValue && !card.dataset.nama.includes(searchValue)) {
                        show = false;
                    }

                    // Show/hide card
                    if (show) {
                        card.classList.remove('d-none');
                        visibleCount++;
                    } else {
                        card.classList.add('d-none');
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
