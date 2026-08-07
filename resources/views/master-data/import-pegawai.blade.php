@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Import Pegawai</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">E-Kinerja</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master.pegawai.index') }}">Master Data Pegawai</a></li>
                <li class="breadcrumb-item active">Import Pegawai</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-outline-secondary me-3" onclick="history.back()">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </button>
                    <div>
                        <h5 class="mb-0 fw-bold">Import Pegawai dari CSV</h5>
                        <small class="text-muted">Tambah banyak pegawai sekaligus dari satu file CSV</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('master.pegawai.import.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="file_csv" class="form-label fw-semibold">File CSV</label>
                                <input type="file" class="form-control" id="file_csv" name="file_csv" accept=".csv,text/csv" required>
                                <div class="form-text">Maksimal 5MB. Pemisah kolom titik-koma (;), baris pertama header.</div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload me-1"></i> Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Format Kolom</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">Kolom yang dibaca (nama header persis, dipisah <code>;</code>):</p>
                        <code class="d-block small text-wrap">
                            Nama; Email; Jabatan; Bidang; NIP / ID; Gelar Depan; Gelar Belakang;
                            No Telpon; Jenis Kelamin; Tanggal Lahir; Alamat; Status Kepeg;
                            Status Aktif; Pangkat; Golongan; Tanggal Masuk; Atasan Langsung
                        </code>
                        <p class="small text-muted mt-3 mb-0">
                            Password pegawai hasil import otomatis <code>password</code> dan wajib diganti
                            saat login pertama kali. Baris dengan Jabatan/Bidang yang tidak dikenali,
                            email/nomor identitas yang sudah terdaftar, akan dilewati.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if (session('import_dilewati') && count(session('import_dilewati')) > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-warning bg-opacity-25">
                            <h6 class="card-title mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Baris yang Dilewati</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Baris</th>
                                        <th>Nama</th>
                                        <th>Alasan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (session('import_dilewati') as $item)
                                        <tr>
                                            <td>{{ $item['baris'] }}</td>
                                            <td>{{ $item['nama'] }}</td>
                                            <td>{{ $item['alasan'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>
@endsection
