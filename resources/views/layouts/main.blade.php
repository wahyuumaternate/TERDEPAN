<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>TERDEPAN - Sistem Manajemen Dokumen</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
    <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

</head>

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ url('/') }}" class="logo d-flex align-items-center">
                {{-- <i class="bi bi-folder2-open"></i> --}}
                <span class="d-none d-lg-block">TERDEPAN</span>
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->

        <div class="search-bar">
            <form class="search-form d-flex align-items-center" method="POST" action="#">
                @csrf
                <input type="text" name="query" placeholder="Search anything here" title="Enter search keyword">
                <button type="submit" title="Search"><i class="bi bi-search"></i></button>
            </form>
        </div><!-- End Search Bar -->

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">

                <li class="nav-item d-block d-lg-none">
                    <a class="nav-link nav-icon search-bar-toggle " href="#">
                        <i class="bi bi-search"></i>
                    </a>
                </li><!-- End Search Icon-->

                <!-- Download APK Button -->
                <li class="nav-item">
                    <a class="btn btn-warning text-white me-2" href="#">
                        Download APK
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="badge bg-primary badge-number">4</span>
                    </a><!-- End Notification Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                        <li class="dropdown-header">
                            You have 4 new notifications
                            <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="notification-item">
                            <i class="bi bi-exclamation-circle text-warning"></i>
                            <div>
                                <h4>Lorem Ipsum</h4>
                                <p>Quae dolorem earum veritatis oditseno</p>
                                <p>30 min. ago</p>
                            </div>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="dropdown-footer">
                            <a href="#">Show all notifications</a>
                        </li>

                    </ul><!-- End Notification Dropdown Items -->

                </li><!-- End Notification Nav -->

                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-chat-dots"></i>
                    </a>
                </li>

                <li class="nav-item dropdown pe-3">

                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                        data-bs-toggle="dropdown">
                        <img src="{{ asset('assets/img/profile-img.jpg') }}" alt="Profile" class="rounded-circle">
                        <span class="d-none d-md-block dropdown-toggle ps-2">K. Anderson</span>
                    </a><!-- End Profile Iamge Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6>Kevin Anderson</h6>
                            <span>Web Designer</span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ url('users-profile') }}">
                                <i class="bi bi-person"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ url('users-profile') }}">
                                <i class="bi bi-gear"></i>
                                <span>Account Settings</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>

                    </ul><!-- End Profile Dropdown Items -->
                </li><!-- End Profile Nav -->

            </ul>
        </nav><!-- End Icons Navigation -->

    </header><!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">


        <ul class="sidebar-nav" id="sidebar-nav">

            <!-- Dashboard Nav -->
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/') }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li><!-- End Dashboard Nav -->

            <!-- Management File / Arsip Dokumen -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#dokumen-nav" data-bs-toggle="collapse"
                    href="#">
                    <i class="bi bi-folder2-open"></i><span>Arsip Dokumen</span><i
                        class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="dokumen-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ url('dokumen') }}">
                            <i class="bi bi-circle"></i><span>Semua Dokumen</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('dokumen/kategori') }}">
                            <i class="bi bi-circle"></i><span>Kategori Dokumen</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('dokumen/upload') }}">
                            <i class="bi bi-circle"></i><span>Upload Dokumen</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Management File Nav -->

            <!-- E-Kinerja -->
            <li class="nav-heading">E-Kinerja</li>

            <!-- Perjanjian Kinerja Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#pk-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-file-earmark-text"></i><span>Perjanjian Kinerja</span><i
                        class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="pk-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ url('perjanjian-kinerja') }}">
                            <i class="bi bi-circle"></i><span>Daftar PK</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('perjanjian-kinerja/template') }}">
                            <i class="bi bi-circle"></i><span>Template PK</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('perjanjian-kinerja/create') }}">
                            <i class="bi bi-circle"></i><span>Buat PK Baru</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Perjanjian Kinerja Nav -->

            <!-- Penugasan Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#tugas-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-list-task"></i><span>Penugasan</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="tugas-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ url('penugasan/tugas-pokok') }}">
                            <i class="bi bi-circle"></i><span>Tugas Pokok</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('penugasan/tugas-harian') }}">
                            <i class="bi bi-circle"></i><span>Tugas Harian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('penugasan/tugas-tambahan') }}">
                            <i class="bi bi-circle"></i><span>Tugas Tambahan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('penugasan/mandiri') }}">
                            <i class="bi bi-circle"></i><span>Penugasan Mandiri</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Penugasan Nav -->

            <!-- Progress & Validasi Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#progress-nav" data-bs-toggle="collapse"
                    href="#">
                    <i class="bi bi-graph-up"></i><span>Progress & Validasi</span><i
                        class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="progress-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ url('progress') }}">
                            <i class="bi bi-circle"></i><span>Input Progress</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('validasi') }}">
                            <i class="bi bi-circle"></i><span>Validasi Tugas</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('revisi') }}">
                            <i class="bi bi-circle"></i><span>Revisi</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Progress & Validasi Nav -->

            <!-- Penilaian Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#penilaian-nav" data-bs-toggle="collapse"
                    href="#">
                    <i class="bi bi-award"></i><span>Penilaian</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="penilaian-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ url('penilaian/bulanan') }}">
                            <i class="bi bi-circle"></i><span>Nilai Bulanan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('penilaian/tahunan') }}">
                            <i class="bi bi-circle"></i><span>Nilai Tahunan</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Penilaian Nav -->

            <!-- Delegasi & Workload Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#delegasi-nav" data-bs-toggle="collapse"
                    href="#">
                    <i class="bi bi-arrow-left-right"></i><span>Delegasi & Workload</span><i
                        class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="delegasi-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ url('delegasi') }}">
                            <i class="bi bi-circle"></i><span>Delegasi Tugas</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('workload') }}">
                            <i class="bi bi-circle"></i><span>Monitor Workload</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Delegasi & Workload Nav -->

            <!-- Master Data -->
            <li class="nav-heading">Master Data</li>

            <!-- Master Data Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#master-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-database"></i><span>Master Data</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="master-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ url('master/pegawai') }}">
                            <i class="bi bi-circle"></i><span>Pegawai</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('master/jabatan') }}">
                            <i class="bi bi-circle"></i><span>Jabatan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('master/bidang') }}">
                            <i class="bi bi-circle"></i><span>Bidang</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('master/ttd-digital') }}">
                            <i class="bi bi-circle"></i><span>TTD Digital</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Master Data Nav -->

            <!-- Sistem -->
            <li class="nav-heading">Sistem</li>

            <!-- Sistem Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#sistem-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-sliders"></i><span>Sistem</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="sistem-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ url('sistem/notifikasi') }}">
                            <i class="bi bi-circle"></i><span>Notifikasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('sistem/audit-log') }}">
                            <i class="bi bi-circle"></i><span>Audit Log</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('sistem/config') }}">
                            <i class="bi bi-circle"></i><span>Konfigurasi</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Sistem Nav -->

        </ul>

        <!-- Storage Indicator -->
        <div class="sidebar-storage mt-auto p-3">
            <div class="text-center mb-3">
                <i class="bi bi-folder2-open text-warning" style="font-size: 3rem;"></i>
            </div>
            <h6 class="text-center mb-2">75% In-use</h6>
            <div class="progress mb-2" style="height: 8px;">
                <div class="progress-bar bg-warning" role="progressbar" style="width: 75%" aria-valuenow="75"
                    aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between">
                <small class="text-muted">600GB</small>
                <small class="text-muted">800GB</small>
            </div>
        </div>


    </aside><!-- End Sidebar-->

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Selamat Datang di TERDEPAN</h1>
            <p class="text-muted">Sistem Manajemen Dokumen dan Kolaborasi yang Aman</p>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <!-- Total Dokumen Card -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card info-card sales-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Dokumen</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>0</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- End Total Dokumen Card -->

                <!-- Total Folder Card -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card info-card revenue-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Folder</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-folder"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>5</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- End Total Folder Card -->

                <!-- Total Pengguna Card -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card info-card customers-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Pengguna</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>3</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- End Total Pengguna Card -->

                <!-- Storage Terpakai Card -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card info-card">
                        <div class="card-body">
                            <h5 class="card-title">Storage Terpakai</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-hdd"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>0</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- End Storage Terpakai Card -->

                <!-- Dokumen Per Kategori -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Dokumen Per Kategori</h5>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary rounded-circle me-2"
                                        style="width: 10px; height: 10px;"></span>
                                    <span>Umum</span>
                                </div>
                                <span class="text-muted">0 dokumen (0.0%)</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary rounded-circle me-2"
                                        style="width: 10px; height: 10px;"></span>
                                    <span>Bahan Tayang</span>
                                </div>
                                <span class="text-muted">0 dokumen (0.0%)</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary rounded-circle me-2"
                                        style="width: 10px; height: 10px;"></span>
                                    <span>Lintas Sektor</span>
                                </div>
                                <span class="text-muted">0 dokumen (0.0%)</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary rounded-circle me-2"
                                        style="width: 10px; height: 10px;"></span>
                                    <span>Data Spasial</span>
                                </div>
                                <span class="text-muted">0 dokumen (0.0%)</span>
                            </div>

                        </div>
                    </div>
                </div><!-- End Dokumen Per Kategori -->

                <!-- Aktivitas Terbaru -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Aktivitas Terbaru</h5>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3">Belum ada aktivitas</p>
                            </div>
                        </div>
                    </div>
                </div><!-- End Aktivitas Terbaru -->

                <!-- Dokumen Per Bulan -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Dokumen Per Bulan</h5>
                            <div id="chartDokumenBulan"></div>
                        </div>
                    </div>
                </div><!-- End Dokumen Per Bulan -->

                <!-- Distribusi Tipe File -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Distribusi Tipe File</h5>
                            <div id="chartTipeFile"></div>
                        </div>
                    </div>
                </div><!-- End Distribusi Tipe File -->

            </div>
        </section>

    </main><!-- End #main -->

    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span>BAPPEDA KUNINGAN</span></strong>. All Rights Reserved
        </div>
    </footer>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/quill/quill.js') }}"></script>
    <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>

    <!-- Template Main JS File -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

</body>

</html>
