@extends('terminaldata::components.layouts.master')

@section('main')
    <div class="pagetitle">
        <h1>Terminal Data</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/terminal-data') }}">Beranda</a></li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row">
            <!-- Total Dokumen Card -->
            <div class="col-xxl-4 col-md-4">
                <div class="card info-card sales-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Dokumen
                            @if ($stats['user_bidang'])
                                <span>| {{ $stats['user_bidang'] }}</span>
                            @else
                                <span>| Semua Bidang</span>
                            @endif
                        </h5>

                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ number_format($stats['total_files']) }}</h6>
                                <span class="text-muted small pt-2">dokumen tersedia</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Total Dokumen Card -->

            <!-- Total Folder Card -->
            <div class="col-xxl-4 col-md-4">
                <div class="card info-card revenue-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Folder
                            @if ($stats['user_bidang'])
                                <span>| {{ $stats['user_bidang'] }}</span>
                            @else
                                <span>| Semua Bidang</span>
                            @endif
                        </h5>

                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-folder"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ number_format($stats['total_folders']) }}</h6>
                                <span class="text-muted small pt-2">folder aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Total Folder Card -->

            <!-- Storage Terpakai Card -->
            <div class="col-xxl-4 col-md-4">
                <div class="card info-card customers-card">
                    <div class="card-body">
                        <h5 class="card-title">Storage Terpakai
                            @if ($stats['user_bidang'])
                                <span>| {{ $stats['user_bidang'] }}</span>
                            @else
                                <span>| Total</span>
                            @endif
                        </h5>

                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-hdd"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ \Illuminate\Support\Number::fileSize($stats['total_size']) }}</h6>
                                <span class="text-muted small pt-2">ruang digunakan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Storage Terpakai Card -->
        </div>

        <div class="row">
            <!-- Upload Trend per Bulan -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Upload Dokumen
                            @if ($stats['user_bidang'])
                                <span>| {{ $stats['user_bidang'] }}</span>
                            @else
                                <span>| Semua Bidang</span>
                            @endif
                        </h5>

                        <!-- Line Chart -->
                        <div id="uploadTrendChart" style="min-height: 400px;"></div>

                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                new ApexCharts(document.querySelector("#uploadTrendChart"), {
                                    series: @json($stats['monthly_uploads']['series']),
                                    chart: {
                                        type: 'line',
                                        height: 350,
                                        zoom: {
                                            enabled: false
                                        },
                                        toolbar: {
                                            show: true
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
                                        categories: @json($stats['monthly_uploads']['labels']),
                                    },
                                    yaxis: {
                                        title: {
                                            text: 'Jumlah Dokumen'
                                        }
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: function(val) {
                                                return val + " dokumen"
                                            }
                                        }
                                    },
                                    legend: {
                                        position: 'top',
                                        horizontalAlign: 'left'
                                    }
                                }).render();
                            });
                        </script>
                        <!-- End Line Chart -->
                    </div>
                </div>
            </div><!-- End Upload Trend per Bulan -->

            <!-- Storage Terpakai Card -->
            <div class="col-lg-6">
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
                </div>
            </div><!-- End Storage Terpakai Card -->

            <!-- Distribusi Tipe File -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Distribusi Tipe File
                            @if ($stats['user_bidang'])
                                <span>| {{ $stats['user_bidang'] }}</span>
                            @else
                                <span>| Semua Bidang</span>
                            @endif
                        </h5>

                        <!-- Column Chart -->
                        <div id="columnChart" style="min-height: 350px;"></div>

                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                const fileTypes = @json($stats['file_types']);
                                const categories = Object.keys(fileTypes);
                                const data = Object.values(fileTypes);

                                new ApexCharts(document.querySelector("#columnChart"), {
                                    series: [{
                                        name: 'Jumlah Dokumen',
                                        data: data
                                    }],
                                    chart: {
                                        type: 'bar',
                                        height: 350,
                                        toolbar: {
                                            show: false
                                        }
                                    },
                                    plotOptions: {
                                        bar: {
                                            borderRadius: 4,
                                            horizontal: false,
                                            columnWidth: '55%',
                                            endingShape: 'rounded',
                                            distributed: true
                                        }
                                    },
                                    dataLabels: {
                                        enabled: true
                                    },
                                    stroke: {
                                        show: true,
                                        width: 2,
                                        colors: ['transparent']
                                    },
                                    xaxis: {
                                        categories: categories,
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
                                    },
                                    legend: {
                                        show: false
                                    }
                                }).render();
                            });
                        </script>
                        <!-- End Column Chart -->
                    </div>
                </div>
            </div><!-- End Distribusi Tipe File Card -->
        </div>
    </section>
@endsection
