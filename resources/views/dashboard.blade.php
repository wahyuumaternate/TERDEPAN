@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Beranda E-Kinerja</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Beranda</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

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
                                <h6 class="mb-0 fw-bold fs-4">{{ $stats['tugas_pokok']['total'] ?? 0 }}</h6>
                                <div class="small text-muted mt-1">
                                    <span class="text-success">{{ $stats['tugas_pokok']['selesai'] ?? 0 }} selesai</span>
                                    <span class="mx-1">•</span>
                                    <span class="text-info">{{ $stats['tugas_pokok']['aktif'] ?? 0 }} aktif</span>
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
                                <h6 class="mb-0 fw-bold fs-4">{{ $stats['tugas_harian']['total'] ?? 0 }}</h6>
                                <div class="small text-muted mt-1">
                                    <span class="text-warning">{{ $stats['tugas_harian']['pending'] ?? 0 }} pending</span>
                                    <span class="mx-1">•</span>
                                    <span class="text-primary">{{ $stats['tugas_harian']['dikerjakan'] ?? 0 }}
                                        dikerjakan</span>
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
                                <h6 class="mb-0 fw-bold fs-4">{{ $stats['tugas_tambahan']['total'] ?? 0 }}</h6>
                                <div class="small text-muted mt-1">
                                    <span class="text-success">{{ $stats['tugas_tambahan']['selesai'] ?? 0 }} selesai</span>
                                    <span class="mx-1">•</span>
                                    <span class="text-info">{{ $stats['tugas_tambahan']['aktif'] ?? 0 }} aktif</span>
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
                                <h6 class="mb-0 fw-bold fs-4">{{ number_format($stats['nilai_rata_rata'] ?? 0, 1) }}</h6>
                                <span class="text-muted small">dari skala 100</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tugas Baru/Pending -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-bell-fill text-warning me-2"></i>
                            Tugas Baru dari Atasan
                        </h5>

                        @if (empty($tugasBaruPending) || $tugasBaruPending->isEmpty())
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle me-2"></i>
                                Tidak ada tugas baru yang perlu ditangani.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 40%">Nama Tugas</th>
                                            <th>Dari</th>
                                            <th class="text-center">Jenis</th>
                                            <th class="text-center">Deadline</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tugasBaruPending as $tugas)
                                            <tr>
                                                <td>
                                                    <span
                                                        class="fw-semibold">{{ Str::limit($tugas['nama_tugas'], 50) }}</span>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock me-1"></i>
                                                        {{ \Carbon\Carbon::parse($tugas['created_at'])->diffForHumans() }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <small>{{ $tugas['pemberi_tugas'] }}</small>
                                                </td>
                                                <td class="text-center">
                                                    @if ($tugas['jenis'] === 'harian')
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-calendar-check me-1"></i>Harian
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-plus-circle me-1"></i>Tambahan
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($tugas['tanggal_selesai'])
                                                        <small>
                                                            <i class="bi bi-calendar3 text-info me-1"></i>
                                                            {{ \Carbon\Carbon::parse($tugas['tanggal_selesai'])->format('d M Y') }}
                                                        </small>
                                                    @else
                                                        <small class="text-muted">-</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-clock-history me-1"></i>
                                                        {{ ucfirst($tugas['status']) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if ($tugas['jenis'] === 'harian')
                                                        <a href="{{ route('penugasan.tugas-harian.show', $tugas['id']) }}"
                                                            class="btn btn-sm btn-primary">
                                                            <i class="bi bi-eye me-1"></i>Lihat
                                                        </a>
                                                    @else
                                                        <a href="{{ route('penugasan.tugas-tambahan.show', $tugas['id']) }}"
                                                            class="btn btn-sm btn-primary">
                                                            <i class="bi bi-eye me-1"></i>Lihat
                                                        </a>
                                                    @endif
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

            <!-- Sidebar: Quick Actions -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Aksi Cepat</h5>
                        <div class="d-grid gap-2">
                            <a href="{{ route('penugasan.tugas-harian.tugas-saya') }}" class="btn btn-primary">
                                <i class="bi bi-calendar-check me-2"></i>Lihat Tugas Harian
                            </a>
                            <a href="{{ route('penugasan.tugas-tambahan.tugas-saya') }}" class="btn btn-outline-primary">
                                <i class="bi bi-plus-circle me-2"></i>Lihat Tugas Tambahan
                            </a>
                            <a href="{{ route('penugasan.tugas-pokok.tugas-saya') }}" class="btn btn-outline-info">
                                <i class="bi bi-file-earmark-text me-2"></i>Tugas Pokok Saya
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
                                <a href="{{ route('penugasan.tim.form-berikan-tugas') }}"
                                    class="btn btn-outline-success">
                                    <i class="bi bi-send me-2"></i>Berikan Tugas
                                </a>
                                <a href="{{ route('penugasan.tim.daftar-validasi') }}" class="btn btn-outline-warning">
                                    <i class="bi bi-check2-square me-2"></i>Validasi Tugas
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Mingguan Chart -->
        @if (!empty($progressMingguan) && !$progressMingguan->isEmpty())
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
    @if (!empty($progressMingguan) && !$progressMingguan->isEmpty())
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
