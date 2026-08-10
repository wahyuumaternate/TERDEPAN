@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Detail & Edit Sub Bidang</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master.sub-bidang.index') }}">Master Data Sub
                        Bidang</a></li>
                <li class="breadcrumb-item active">{{ $subBidang->nama }}</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-outline-secondary me-3" onclick="history.back()">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </button>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $subBidang->nama }}</h5>
                            <small class="text-muted">{{ $subBidang->bidang->nama ?? '-' }}</small>
                        </div>
                    </div>
                    <form action="{{ route('master.sub-bidang.destroy', $subBidang->id) }}" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus sub bidang ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                    </form>
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
                        <form action="{{ route('master.sub-bidang.update', $subBidang->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Bidang Induk <span class="text-danger">*</span></label>
                                <select class="form-select @error('bidang_id') is-invalid @enderror" name="bidang_id"
                                    required>
                                    @foreach ($bidangList as $bidang)
                                        <option value="{{ $bidang->id }}"
                                            {{ old('bidang_id', $subBidang->bidang_id) == $bidang->id ? 'selected' : '' }}>
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
                                    name="nama" value="{{ old('nama', $subBidang->nama) }}" required>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end pt-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
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
