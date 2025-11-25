@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Dashboard Penugasan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Beranda</a></li>
                <li class="breadcrumb-item active">Dashboard Penugasan</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <!-- Tugas Pokok Card -->
            <div class="col-xxl-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Tugas Pokok</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10">
                                <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-4">{{ $stats['tugas_pokok']['total'] }}</h6>
                                <div class="small text-muted mt-1">
                                    <span class="text-success">{{ $stats['tugas_pokok']['selesai'] }} selesai</span>
                                    <span class="mx-1">•</span>
                                    <span class="text-info">{{ $stats['tugas_pokok']['aktif'] }} aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tugas Harian Card -->
            <div class="col-xxl-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Tugas Harian</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10">
                                <i class="bi bi-calendar-check text-info fs-4"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-4">{{ $stats['tugas_harian']['total'] }}</h6>
                                <div class="small text-muted mt-1">
                                    <span class="text-warning">{{ $stats['tugas_harian']['pending'] }} pending</span>
                                    <span class="mx-1">•</span>
                                    <span class="text-primary">{{ $stats['tugas_harian']['dikerjakan'] }} dikerjakan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tugas Tambahan Card -->
            <div class="col-xxl-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Tugas Tambahan</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10">
                                <i class="bi bi-plus-circle text-success fs-4"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-4">{{ $stats['tugas_tambahan']['total'] }}</h6>
                                <div class="small text-muted mt-1">
                                    <span class="text-success">{{ $stats['tugas_tambahan']['selesai'] }} selesai</span>
                                    <span class="mx-1">•</span>
                                    <span class="text-info">{{ $stats['tugas_tambahan']['aktif'] }} aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rata-rata Nilai Card -->
            <div class="col-xxl-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Rata-rata Nilai</h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10">
                                <i class="bi bi-star text-warning fs-4"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-4">{{ number_format($stats['nilai_rata_rata'], 1) }}</h6>
                                <span class="text-muted small">dari skala 100</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tugas Pokok dengan Progress -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Tugas Pokok - Progress</h5>

                        @if ($tugasPokok->isEmpty())
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Belum ada tugas pokok yang ditugaskan.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nama Tugas</th>
                                            <th class="text-center">Total Harian</th>
                                            <th class="text-center">Selesai</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tugasPokok as $tp)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('penugasan.tugas-pokok.show', $tp->id) }}"
                                                        class="text-decoration-none fw-semibold">
                                                        {{ $tp->nama_tugas }}
                                                    </a>
                                                    <br>
                                                    <small class="text-muted">
                                                        Target: {{ $tp->target_value }} {{ $tp->satuan }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">{{ $tp->jumlah_tugas_harian }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success">{{ $tp->selesai_count }}</span>
                                                </td>
                                                <td>
                                                    @php
                                                        $progress =
                                                            $tp->jumlah_tugas_harian > 0
                                                                ? ($tp->selesai_count / $tp->jumlah_tugas_harian) * 100
                                                                : 0;
                                                        $progressClass =
                                                            $progress >= 75
                                                                ? 'success'
                                                                : ($progress >= 50
                                                                    ? 'info'
                                                                    : ($progress >= 25
                                                                        ? 'warning'
                                                                        : 'danger'));
                                                    @endphp
                                                    <div class="progress" style="height: 25px;">
                                                        <div class="progress-bar bg-{{ $progressClass }}"
                                                            role="progressbar" style="width: {{ $progress }}%;"
                                                            aria-valuenow="{{ $progress }}" aria-valuemin="0"
                                                            aria-valuemax="100">
                                                            {{ number_format($progress, 0) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar: Quick Actions & Tugas Mendesak -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Aksi Cepat</h5>
                        <div class="d-grid gap-2">
                            <a href="{{ route('penugasan.tugas-harian.tugas-saya') }}" class="btn btn-primary">
                                <i class="bi bi-calendar-check me-2"></i>Lihat Tugas Harian
                            </a>
                            <a href="{{ route('penugasan.tugas-tambahan.tugas-saya') }}" class="btn btn-outline-primary">
                                <i class="bi bi-plus-circle me-2"></i>Lihat Tugas Tambahan
                            </a>

                            @php
                                $canManageTeam = in_array(auth()->user()->jabatan?->kode, [
                                    'ADMIN',
                                    'KABAN',
                                    'SEKBAN',
                                    'KABID',
                                    'KASUBAG',
                                ]);
                            @endphp

                            @if ($canManageTeam)
                                <a href="{{ route('penugasan.tim.form-berikan-tugas') }}" class="btn btn-outline-success">
                                    <i class="bi bi-send me-2"></i>Berikan Tugas
                                </a>
                                <a href="{{ route('penugasan.tim.daftar-validasi') }}" class="btn btn-outline-warning">
                                    <i class="bi bi-check2-square me-2"></i>Validasi Tugas
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tugas Mendesak -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Tugas Mendesak</h5>

                        @if ($tugasMendesak->isEmpty() && $tugasTambahanMendesak->isEmpty())
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle me-2"></i>
                                Tidak ada tugas mendesak.
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($tugasMendesak as $tugas)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div class="fw-semibold small">
                                                <a href="{{ route('penugasan.tugas-harian.show', $tugas->id) }}"
                                                    class="text-decoration-none">
                                                    {{ Str::limit($tugas->nama_tugas, 40) }}
                                                </a>
                                            </div>
                                            @php
                                                $daysLeft = now()->diffInDays($tugas->tanggal_selesai, false);
                                                $urgencyClass = $daysLeft <= 2 ? 'danger' : 'warning';
                                            @endphp
                                            <span class="badge bg-{{ $urgencyClass }}">
                                                {{ abs($daysLeft) }}h
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3"></i>
                                            {{ $tugas->tanggal_selesai->format('d M Y') }}
                                        </small>
                                    </div>
                                @endforeach

                                @foreach ($tugasTambahanMendesak as $tugas)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div class="fw-semibold small">
                                                <a href="{{ route('penugasan.tugas-tambahan.show', $tugas->id) }}"
                                                    class="text-decoration-none">
                                                    {{ Str::limit($tugas->nama_tugas, 40) }}
                                                </a>
                                                <span class="badge bg-info">Tambahan</span>
                                            </div>
                                            @php
                                                $daysLeft = now()->diffInDays($tugas->tanggal_selesai, false);
                                                $urgencyClass = $daysLeft <= 2 ? 'danger' : 'warning';
                                            @endphp
                                            <span class="badge bg-{{ $urgencyClass }}">
                                                {{ abs($daysLeft) }}h
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3"></i>
                                            {{ $tugas->tanggal_selesai->format('d M Y') }}
                                        </small>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Mingguan Chart -->
        @if (!empty($progressMingguan))
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title">Progress 4 Minggu Terakhir</h5>
                            <div id="progressChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    @if (!empty($progressMingguan))
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const progressData = @json($progressMingguan);

                const options = {
                    series: [{
                        name: 'Progress Rata-rata',
                        data: progressData.map(item => item.avg_progress)
                    }],
                    chart: {
                        type: 'area',
                        height: 350,
                        toolbar: {
                            show: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    xaxis: {
                        categories: progressData.map(item => {
                            const date = new Date(item.minggu);
                            return date.toLocaleDateString('id-ID', {
                                day: 'numeric',
                                month: 'short'
                            });
                        })
                    },
                    yaxis: {
                        title: {
                            text: 'Progress (%)'
                        },
                        min: 0,
                        max: 100
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.3,
                        }
                    },
                    colors: ['#4154f1'],
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val.toFixed(1) + '%'
                            }
                        }
                    }
                };

                const chart = new ApexCharts(document.querySelector("#progressChart"), options);
                chart.render();
            });
        </script>
    @endif
@endpush
