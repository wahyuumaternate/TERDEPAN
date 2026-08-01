@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Tambah Sub Bidang Baru</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master.sub-bidang.index') }}">Master Data Sub
                        Bidang</a></li>
                <li class="breadcrumb-item active">Tambah Sub Bidang</li>
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
                        <h5 class="mb-0 fw-bold">Tambah Sub Bidang Baru</h5>
                        <small class="text-muted">Lengkapi form di bawah untuk menambah sub bidang baru</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>Informasi Sub Bidang
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('master.sub-bidang.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Bidang Induk <span class="text-danger">*</span></label>
                                <select class="form-select @error('bidang_id') is-invalid @enderror" name="bidang_id"
                                    required>
                                    <option value="">Pilih Bidang</option>
                                    @foreach ($bidangList as $bidang)
                                        <option value="{{ $bidang->id }}"
                                            {{ old('bidang_id') == $bidang->id ? 'selected' : '' }}>
                                            {{ $bidang->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bidang_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Sub Bidang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                    name="nama" value="{{ old('nama') }}" required
                                    placeholder="Contoh: Sub Bidang Data dan Informasi">
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between gap-2 pt-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                    <i class="bi bi-x-circle me-1"></i> Batal
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> Simpan Sub Bidang
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .card {
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            border-bottom: 1px solid #dee2e6;
            background-color: #f8f9fa !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #5F71E4;
            box-shadow: 0 0 0 0.25rem rgba(95, 113, 228, 0.25);
        }
    </style>
@endsection
