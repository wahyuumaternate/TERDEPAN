@extends('layouts.main')

@section('main')
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
@endsection
