@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>E-Kinerja Perencana</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/e-kinerja') }}">Beranda</a></li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row">
            <!-- Left side columns -->
            <div class="col-lg-8">
                <div class="row">
                    <!-- Total Dokumen Card -->
                    <div class="col-xxl-4 col-md-6">
                        <div class="card info-card sales-card">
                            <div class="filter">
                                <a class="icon" href="#" data-bs-toggle="dropdown"><i
                                        class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <li class="dropdown-header text-start">
                                        <h6>Filter</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="#">Hari Ini</a></li>
                                    <li><a class="dropdown-item" href="#">Bulan Ini</a></li>
                                    <li><a class="dropdown-item" href="#">Tahun Ini</a></li>
                                </ul>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title">Total Dokumen <span>| Tahun 2025</span></h5>

                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>248</h6>
                                        <span class="text-success small pt-1 fw-bold">12%</span>
                                        <span class="text-muted small pt-2 ps-1">peningkatan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- End Total Dokumen Card -->

                    <!-- Total Folder Card -->
                    <div class="col-xxl-4 col-md-6">
                        <div class="card info-card revenue-card">
                            <div class="filter">
                                <a class="icon" href="#" data-bs-toggle="dropdown"><i
                                        class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <li class="dropdown-header text-start">
                                        <h6>Filter</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="#">Per Bidang</a></li>
                                    <li><a class="dropdown-item" href="#">Per Jenis</a></li>
                                    <li><a class="dropdown-item" href="#">Semua</a></li>
                                </ul>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title">Total Folder <span>| Aktif</span></h5>

                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-folder"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>32</h6>
                                        <span class="text-success small pt-1 fw-bold">8%</span>
                                        <span class="text-muted small pt-2 ps-1">peningkatan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- End Total Folder Card -->

                    <!-- Total Pengguna Card -->
                    <div class="col-xxl-4 col-xl-12">
                        <div class="card info-card customers-card">
                            <div class="filter">
                                <a class="icon" href="#" data-bs-toggle="dropdown"><i
                                        class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <li class="dropdown-header text-start">
                                        <h6>Filter</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="#">PNS</a></li>
                                    <li><a class="dropdown-item" href="#">PPPK</a></li>
                                    <li><a class="dropdown-item" href="#">Kontrak</a></li>
                                </ul>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title">Total Pengguna <span>| Status Aktif</span></h5>

                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>27</h6>
                                        <span class="text-danger small pt-1 fw-bold">2%</span>
                                        <span class="text-muted small pt-2 ps-1">penurunan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- End Total Pengguna Card -->

                    <!-- Distribusi Dokumen per Bidang -->
                    <div class="col-12">
                        <div class="card">
                            <div class="filter">
                                <a class="icon" href="#" data-bs-toggle="dropdown"><i
                                        class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <li class="dropdown-header text-start">
                                        <h6>Filter</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="#">Bulanan</a></li>
                                    <li><a class="dropdown-item" href="#">Triwulan</a></li>
                                    <li><a class="dropdown-item" href="#">Tahunan</a></li>
                                </ul>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title">Dokumen Per Bidang <span>| 2025</span></h5>

                                <!-- Line Chart -->
                                <div id="bidangLineChart" style="min-height: 400px;" class="echart"></div>

                                <script>
                                    document.addEventListener("DOMContentLoaded", () => {
                                        echarts.init(document.querySelector("#bidangLineChart")).setOption({
                                            tooltip: {
                                                trigger: 'axis'
                                            },
                                            legend: {
                                                data: ['PLAN', 'EVAL', 'DATA', 'SEKRET']
                                            },
                                            grid: {
                                                left: '3%',
                                                right: '4%',
                                                bottom: '3%',
                                                containLabel: true
                                            },
                                            toolbox: {
                                                feature: {
                                                    saveAsImage: {}
                                                }
                                            },
                                            xAxis: {
                                                type: 'category',
                                                boundaryGap: false,
                                                data: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt']
                                            },
                                            yAxis: {
                                                type: 'value'
                                            },
                                            series: [{
                                                name: 'PLAN',
                                                type: 'line',
                                                stack: 'Total',
                                                data: [12, 15, 10, 18, 20, 16, 22, 24, 18, 21],
                                                areaStyle: {
                                                    opacity: 0.3
                                                },
                                                emphasis: {
                                                    focus: 'series'
                                                },
                                                smooth: true
                                            }, {
                                                name: 'EVAL',
                                                type: 'line',
                                                stack: 'Total',
                                                data: [8, 10, 12, 9, 11, 13, 15, 14, 16, 17],
                                                areaStyle: {
                                                    opacity: 0.3
                                                },
                                                emphasis: {
                                                    focus: 'series'
                                                },
                                                smooth: true
                                            }, {
                                                name: 'DATA',
                                                type: 'line',
                                                stack: 'Total',
                                                data: [5, 7, 6, 9, 8, 10, 12, 11, 13, 14],
                                                areaStyle: {
                                                    opacity: 0.3
                                                },
                                                emphasis: {
                                                    focus: 'series'
                                                },
                                                smooth: true
                                            }, {
                                                name: 'SEKRET',
                                                type: 'line',
                                                stack: 'Total',
                                                data: [7, 8, 9, 7, 10, 11, 9, 12, 14, 13],
                                                areaStyle: {
                                                    opacity: 0.3
                                                },
                                                emphasis: {
                                                    focus: 'series'
                                                },
                                                smooth: true
                                            }]
                                        });
                                    });
                                </script>
                                <!-- End Line Chart -->
                            </div>
                        </div>
                    </div><!-- End Distribusi Dokumen per Bidang -->

                    <!-- Status Kinerja Pegawai -->
                    <div class="col-12">
                        <div class="card recent-sales overflow-auto">
                            <div class="filter">
                                <a class="icon" href="#" data-bs-toggle="dropdown"><i
                                        class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <li class="dropdown-header text-start">
                                        <h6>Filter</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="#">Hari Ini</a></li>
                                    <li><a class="dropdown-item" href="#">Bulan Ini</a></li>
                                    <li><a class="dropdown-item" href="#">Tahun Ini</a></li>
                                </ul>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title">Status Kinerja Pegawai <span>| Bulan Oktober</span></h5>

                                <table class="table table-borderless datatable">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nama</th>
                                            <th scope="col">Bidang</th>
                                            <th scope="col">Tugas Aktif</th>
                                            <th scope="col">Progress</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row"><a href="#">#PG001</a></th>
                                            <td>Budi Santoso</td>
                                            <td>PLAN</td>
                                            <td>7</td>
                                            <td><span>85%</span></td>
                                            <td><span class="badge bg-success">Baik</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><a href="#">#PG002</a></th>
                                            <td>Siti Rahayu</td>
                                            <td>EVAL</td>
                                            <td>5</td>
                                            <td><span>92%</span></td>
                                            <td><span class="badge bg-success">Sangat Baik</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><a href="#">#PG003</a></th>
                                            <td>Ahmad Putra</td>
                                            <td>DATA</td>
                                            <td>8</td>
                                            <td><span>62%</span></td>
                                            <td><span class="badge bg-warning">Cukup</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><a href="#">#PG004</a></th>
                                            <td>Dewi Lestari</td>
                                            <td>SEKRET</td>
                                            <td>4</td>
                                            <td><span>75%</span></td>
                                            <td><span class="badge bg-success">Baik</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><a href="#">#PG005</a></th>
                                            <td>Agus Wijaya</td>
                                            <td>PLAN</td>
                                            <td>6</td>
                                            <td><span>45%</span></td>
                                            <td><span class="badge bg-danger">Kurang</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div><!-- End Status Kinerja Pegawai -->
                </div>
            </div><!-- End Left side columns -->

            <!-- Right side columns -->
            <div class="col-lg-4">
                <!-- Storage Terpakai Card -->
                <div class="card">
                    <div class="filter">
                        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li class="dropdown-header text-start">
                                <h6>Filter</h6>
                            </li>
                            <li><a class="dropdown-item" href="#">Per Bidang</a></li>
                            <li><a class="dropdown-item" href="#">Per Kategori</a></li>
                            <li><a class="dropdown-item" href="#">Semua</a></li>
                        </ul>
                    </div>

                    <div class="card-body pb-0">
                        <h5 class="card-title">Storage Terpakai <span>| Total</span></h5>

                        <div id="storageDonut" style="min-height: 400px;" class="echart"></div>

                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                echarts.init(document.querySelector("#storageDonut")).setOption({
                                    tooltip: {
                                        trigger: 'item'
                                    },
                                    legend: {
                                        top: '5%',
                                        left: 'center'
                                    },
                                    series: [{
                                        name: 'Storage',
                                        type: 'pie',
                                        radius: ['40%', '70%'],
                                        avoidLabelOverlap: false,
                                        label: {
                                            show: false,
                                            position: 'center'
                                        },
                                        emphasis: {
                                            label: {
                                                show: true,
                                                fontSize: '18',
                                                fontWeight: 'bold'
                                            }
                                        },
                                        labelLine: {
                                            show: false
                                        },
                                        data: [{
                                            value: 1200,
                                            name: 'PDF (1.2 GB)'
                                        }, {
                                            value: 850,
                                            name: 'DOCX (850 MB)'
                                        }, {
                                            value: 2300,
                                            name: 'Shapefile (2.3 GB)'
                                        }, {
                                            value: 490,
                                            name: 'XLSX (490 MB)'
                                        }, {
                                            value: 350,
                                            name: 'Lainnya (350 MB)'
                                        }]
                                    }]
                                });
                            });
                        </script>
                    </div>
                </div><!-- End Storage Terpakai Card -->

                <!-- Distribusi Tipe File -->
                <div class="card">
                    <div class="filter">
                        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li class="dropdown-header text-start">
                                <h6>Filter</h6>
                            </li>
                            <li><a class="dropdown-item" href="#">Hari Ini</a></li>
                            <li><a class="dropdown-item" href="#">Bulan Ini</a></li>
                            <li><a class="dropdown-item" href="#">Tahun Ini</a></li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">Distribusi Tipe File <span>| 2025</span></h5>

                        <!-- Column Chart -->
                        <div id="columnChart" style="min-height: 265px;"></div>

                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                new ApexCharts(document.querySelector("#columnChart"), {
                                    series: [{
                                        name: 'Jumlah Dokumen',
                                        data: [124, 85, 23, 12, 4]
                                    }],
                                    chart: {
                                        type: 'bar',
                                        height: 265,
                                        toolbar: {
                                            show: false
                                        }
                                    },
                                    plotOptions: {
                                        bar: {
                                            borderRadius: 4,
                                            horizontal: false,
                                            columnWidth: '55%',
                                            endingShape: 'rounded'
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
                                        categories: ['PDF', 'DOCX', 'XLSX', 'SHP', 'Lainnya'],
                                    },
                                    fill: {
                                        opacity: 1
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: function(val) {
                                                return val + " dokumen"
                                            }
                                        }
                                    }
                                }).render();
                            });
                        </script>
                        <!-- End Column Chart -->
                    </div>
                </div><!-- End Distribusi Tipe File Card -->

                <!-- Dokumen Per Kategori -->
                <div class="card">
                    <div class="filter">
                        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li class="dropdown-header text-start">
                                <h6>Filter</h6>
                            </li>
                            <li><a class="dropdown-item" href="#">Per Bidang</a></li>
                            <li><a class="dropdown-item" href="#">Semua</a></li>
                        </ul>
                    </div>

                    <div class="card-body pb-0">
                        <h5 class="card-title">Dokumen Per Kategori <span>| 2025</span></h5>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary rounded-circle me-2"
                                    style="width: 10px; height: 10px;"></span>
                                <span>Umum</span>
                            </div>
                            <div>
                                <span class="text-muted me-2">35 dokumen</span>
                                <span class="badge bg-success">14%</span>
                            </div>
                        </div>

                        <div class="progress mb-4">
                            <div class="progress-bar" role="progressbar" style="width: 14%" aria-valuenow="14"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info rounded-circle me-2" style="width: 10px; height: 10px;"></span>
                                <span>Bahan Tayang</span>
                            </div>
                            <div>
                                <span class="text-muted me-2">95 dokumen</span>
                                <span class="badge bg-success">38%</span>
                            </div>
                        </div>

                        <div class="progress mb-4">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 38%" aria-valuenow="38"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning rounded-circle me-2"
                                    style="width: 10px; height: 10px;"></span>
                                <span>Lintas Sektor</span>
                            </div>
                            <div>
                                <span class="text-muted me-2">75 dokumen</span>
                                <span class="badge bg-success">30%</span>
                            </div>
                        </div>

                        <div class="progress mb-4">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 30%"
                                aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger rounded-circle me-2"
                                    style="width: 10px; height: 10px;"></span>
                                <span>Data Spasial</span>
                            </div>
                            <div>
                                <span class="text-muted me-2">43 dokumen</span>
                                <span class="badge bg-success">18%</span>
                            </div>
                        </div>

                        <div class="progress mb-4">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 18%" aria-valuenow="18"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div><!-- End Dokumen Per Kategori Card -->


            </div><!-- End Right side columns -->
        </div>

        <!-- Workload Analysis & Tugas -->
        <div class="row">
            <!-- Workload Analysis -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Analisis Beban Kerja Pegawai <span>| Oktober 2025</span></h5>

                        <!-- Radar Chart -->
                        <div id="radarChart" style="min-height: 365px;"></div>

                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                new ApexCharts(document.querySelector("#radarChart"), {
                                    series: [{
                                        name: 'PLAN',
                                        data: [80, 50, 30, 40, 60, 20],
                                    }, {
                                        name: 'EVAL',
                                        data: [20, 30, 40, 80, 20, 80],
                                    }, {
                                        name: 'DATA',
                                        data: [44, 76, 78, 13, 43, 10],
                                    }, {
                                        name: 'SEKRET',
                                        data: [30, 50, 20, 60, 90, 30],
                                    }],
                                    chart: {
                                        height: 350,
                                        type: 'radar',
                                        dropShadow: {
                                            enabled: true,
                                            blur: 1,
                                            left: 1,
                                            top: 1
                                        }
                                    },
                                    title: {
                                        text: 'Beban Kerja per Bidang'
                                    },
                                    stroke: {
                                        width: 2
                                    },
                                    fill: {
                                        opacity: 0.1
                                    },
                                    markers: {
                                        size: 0
                                    },
                                    xaxis: {
                                        categories: ['Tugas Pokok', 'Tugas Harian', 'Tugas Tambahan', 'Deadline',
                                            'Kompleksitas', 'Penugasan Mandiri'
                                        ]
                                    }
                                }).render();
                            });
                        </script>
                        <!-- End Radar Chart -->
                    </div>
                </div>
            </div><!-- End Workload Analysis -->

            <!-- Progress Tugas Pokok -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Progress Tugas Pokok <span>| Tahun 2025</span></h5>

                        <!-- Progress Bars -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <div>Penyusunan RPJMD 2025-2030</div>
                                <div>75%</div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped bg-primary progress-bar-animated"
                                    role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <div>Evaluasi Kinerja Triwulan</div>
                                <div>90%</div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped bg-success progress-bar-animated"
                                    role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <div>Pemetaan Kawasan Strategis</div>
                                <div>45%</div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped bg-warning progress-bar-animated"
                                    role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <div>Koordinasi Lintas Sektor</div>
                                <div>60%</div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped bg-info progress-bar-animated"
                                    role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between mb-2">
                                <div>Implementasi Sistem TERDEPAN</div>
                                <div>30%</div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped bg-danger progress-bar-animated"
                                    role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                        </div>
                        <!-- End Progress Bars -->
                    </div>
                </div>
            </div><!-- End Progress Tugas Pokok -->
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Dokumen Per Bulan Chart
            new ApexCharts(document.querySelector("#chartDokumenBulan"), {
                series: [{
                    name: 'Dokumen',
                    data: [25, 35, 42, 30, 45, 52, 38, 60, 58, 65]
                }],
                chart: {
                    type: 'area',
                    height: 350,
                    zoom: {
                        enabled: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                title: {
                    text: 'Tren Dokumen Per Bulan',
                    align: 'left'
                },
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt'],
                yaxis: {
                    opposite: false
                },
                legend: {
                    horizontalAlign: 'left'
                }
            }).render();
        });
    </script>
@endsection
