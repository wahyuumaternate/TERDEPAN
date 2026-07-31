@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Monitoring Tim</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tim.index') }}">Tim Saya</a></li>
                <li class="breadcrumb-item active">Monitoring</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        @php
            $user = auth()->user();
            $anggotaTim = \App\Models\User::whereRelation('profile', 'atasan_langsung_id', $user->id)
                ->where('status_aktif', 'Aktif')
                ->with(['jabatan', 'bidang'])
                ->withCount([
                    'tugasHarian as tugas_aktif' => function ($q) {
                        $q->whereIn('status', ['dikerjakan', 'validasi']);
                    },
                    'tugasHarian as tugas_selesai' => function ($q) {
                        $q->where('status', 'selesai');
                    },
                    'tugasHarian as tugas_pending' => function ($q) {
                        $q->where('status', 'pending');
                    },
                    'tugasHarian as tugas_terlambat' => function ($q) {
                        $q->whereIn('status', ['dikerjakan', 'validasi'])->where('tanggal_selesai', '<', now());
                    },
                ])
                ->get();

            $totalTugas = $anggotaTim->sum(function ($anggota) {
                return $anggota->tugas_aktif + $anggota->tugas_selesai + $anggota->tugas_pending;
            });

            $tugasPerluValidasi = \Modules\Penugasan\Models\TugasHarian::whereIn('pegawai_id', $anggotaTim->pluck('id'))
                ->where('status', 'validasi')
                ->count();

            $tugasTerlambat = $anggotaTim->sum('tugas_terlambat');

            $rataProgress = $totalTugas > 0 ? ($anggotaTim->sum('tugas_selesai') / $totalTugas) * 100 : 0;
        @endphp

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Total Tugas Tim</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10">
                                <i class="bi bi-list-task text-primary fs-3"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-2">{{ $totalTugas }}</h6>
                                <span class="text-muted small">Total tugas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Progress Tim</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10">
                                <i class="bi bi-graph-up text-success fs-3"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-2">{{ number_format($rataProgress, 0) }}%</h6>
                                <span class="text-muted small">Rata-rata</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Perlu Validasi</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10">
                                <i class="bi bi-check-circle text-warning fs-3"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-2">{{ $tugasPerluValidasi }}</h6>
                                <span class="text-muted small">Menunggu</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Tugas Terlambat</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10">
                                <i class="bi bi-exclamation-triangle text-danger fs-3"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-2">{{ $tugasTerlambat }}</h6>
                                <span class="text-muted small">Terlambat</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Performance Chart -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Workload Distribution</h5>
                        <div id="workloadChart" style="min-height: 400px;"></div>
                    </div>
                </div>
            </div>

            <!-- Alerts & Notifications -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Alerts & Perhatian</h5>

                        @php
                            $anggotaOverload = $anggotaTim->filter(function ($anggota) {
                                $workload = ($anggota->tugas_aktif + $anggota->tugas_pending) * 10;
                                return $workload > 70;
                            });
                        @endphp

                        <!-- Overload Warning -->
                        @if ($anggotaOverload->count() > 0)
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="bi bi-exclamation-triangle me-2"></i>Workload Tinggi
                                </h6>
                                <p class="mb-2 small">{{ $anggotaOverload->count() }} anggota tim memiliki beban kerja
                                    tinggi:</p>
                                <ul class="small mb-0">
                                    @foreach ($anggotaOverload->take(3) as $anggota)
                                        <li>{{ $anggota->nama }} -
                                            {{ min(($anggota->tugas_aktif + $anggota->tugas_pending) * 10, 100) }}%</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Pending Validation -->
                        @if ($tugasPerluValidasi > 0)
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="bi bi-info-circle me-2"></i>Tugas Perlu Validasi
                                </h6>
                                <p class="mb-2 small">Ada {{ $tugasPerluValidasi }} tugas menunggu validasi Anda.</p>
                                <a href="{{ route('penugasan.tim.daftar-validasi') }}" class="btn btn-sm btn-info">
                                    Validasi Sekarang
                                </a>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Overdue Tasks -->
                        @if ($tugasTerlambat > 0)
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="bi bi-exclamation-circle me-2"></i>Tugas Terlambat
                                </h6>
                                <p class="mb-0 small">{{ $tugasTerlambat }} tugas melewati deadline. Segera lakukan tindak
                                    lanjut.</p>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($anggotaOverload->count() == 0 && $tugasPerluValidasi == 0 && $tugasTerlambat == 0)
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                Semua tugas tim berjalan dengan baik!
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <div class="d-grid gap-2">
                            <a href="{{ route('penugasan.tim.form-berikan-tugas') }}" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>Berikan Tugas Baru
                            </a>
                            <a href="{{ route('penugasan.tim.daftar-validasi') }}" class="btn btn-warning">
                                <i class="bi bi-check-square me-2"></i>Validasi Tugas
                                @if ($tugasPerluValidasi > 0)
                                    <span class="badge bg-danger">{{ $tugasPerluValidasi }}</span>
                                @endif
                            </a>
                            <a href="{{ route('penugasan.tim.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-people me-2"></i>Lihat Anggota Tim
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Table -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Detail Performance Anggota Tim</h5>
                            <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i>Print
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 3%">#</th>
                                        <th style="width: 20%">Nama</th>
                                        <th style="width: 15%">Bidang/Jabatan</th>
                                        <th class="text-center" style="width: 8%">Pending</th>
                                        <th class="text-center" style="width: 8%">Aktif</th>
                                        <th class="text-center" style="width: 8%">Selesai</th>
                                        <th class="text-center" style="width: 8%">Terlambat</th>
                                        <th class="text-center" style="width: 12%">Workload</th>
                                        <th class="text-center" style="width: 10%">Status</th>
                                        <th class="text-center" style="width: 8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($anggotaTim->sortByDesc('tugas_aktif') as $index => $anggota)
                                        @php
                                            $workload = min(
                                                ($anggota->tugas_aktif + $anggota->tugas_pending) * 10,
                                                100,
                                            );
                                            $workloadClass =
                                                $workload >= 70 ? 'danger' : ($workload >= 40 ? 'warning' : 'success');
                                            $totalTugasAnggota =
                                                $anggota->tugas_aktif +
                                                $anggota->tugas_selesai +
                                                $anggota->tugas_pending;
                                            $progressAnggota =
                                                $totalTugasAnggota > 0
                                                    ? ($anggota->tugas_selesai / $totalTugasAnggota) * 100
                                                    : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $anggota->nama }}</div>
                                                <small class="text-muted">NIP: {{ $anggota->nip ?? '-' }}</small>
                                            </td>
                                            <td>
                                                @if ($anggota->profile->bidang)
                                                    <span
                                                        class="badge bg-secondary">{{ $anggota->profile->bidang->nama_bidang }}</span>
                                                @endif
                                                <br><small
                                                    class="text-muted">{{ $anggota->profile->jabatan->nama_jabatan ?? '-' }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning">{{ $anggota->tugas_pending }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $anggota->tugas_aktif }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success">{{ $anggota->tugas_selesai }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if ($anggota->tugas_terlambat > 0)
                                                    <span class="badge bg-danger">{{ $anggota->tugas_terlambat }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                                        <div class="progress-bar bg-{{ $workloadClass }}"
                                                            role="progressbar" style="width: {{ $workload }}%"
                                                            aria-valuenow="{{ $workload }}" aria-valuemin="0"
                                                            aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <small class="fw-bold">{{ number_format($workload, 0) }}%</small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if ($progressAnggota >= 75)
                                                    <span class="badge bg-success">Baik</span>
                                                @elseif($progressAnggota >= 50)
                                                    <span class="badge bg-info">Cukup</span>
                                                @elseif($progressAnggota >= 25)
                                                    <span class="badge bg-warning">Perlu Perhatian</span>
                                                @else
                                                    <span class="badge bg-danger">Kurang</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('penugasan.tim.detail-anggota', $anggota->id) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4">
                                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">Belum ada data anggota tim</p>
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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @php
                $chartData = $anggotaTim->map(function ($anggota) {
                    return [
                        'name' => $anggota->nama,
                        'pending' => $anggota->tugas_pending,
                        'aktif' => $anggota->tugas_aktif,
                        'selesai' => $anggota->tugas_selesai,
                    ];
                });
            @endphp

            const chartData = @json($chartData);

            const options = {
                series: [{
                    name: 'Pending',
                    data: chartData.map(d => d.pending)
                }, {
                    name: 'Aktif',
                    data: chartData.map(d => d.aktif)
                }, {
                    name: 'Selesai',
                    data: chartData.map(d => d.selesai)
                }],
                chart: {
                    type: 'bar',
                    height: 400,
                    stacked: true,
                    toolbar: {
                        show: true
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '60%',
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: chartData.map(d => d.name),
                    labels: {
                        rotate: -45,
                        trim: true
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah Tugas'
                    }
                },
                fill: {
                    opacity: 1
                },
                colors: ['#ffc107', '#0d6efd', '#198754'],
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " tugas"
                        }
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector("#workloadChart"), options);
            chart.render();
        });
    </script>
@endpush
