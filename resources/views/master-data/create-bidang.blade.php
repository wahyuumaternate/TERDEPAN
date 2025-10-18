@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Tambah Bidang Baru</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master.bidang.index') }}">Master Data Bidang</a></li>
                <li class="breadcrumb-item active">Tambah Bidang</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Header Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-outline-secondary me-3" onclick="history.back()">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </button>
                        <div>
                            <h5 class="mb-0 fw-bold">Tambah Bidang Baru</h5>
                            <small class="text-muted">Lengkapi form di bawah untuk menambah bidang baru</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="formBidang" action="{{ route('master.bidang.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Color Preview -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-palette me-2"></i>Preview Warna
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="color-preview-container mb-3">
                                <div class="rounded-circle mx-auto shadow" id="colorPreview"
                                    style="width: 120px; height: 120px; background-color: #6c757d; border: 3px solid #fff;">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pilih Warna <small class="text-muted">(opsional)</small></label>
                                <input type="color"
                                    class="form-control form-control-color @error('warna') is-invalid @enderror"
                                    id="warna" name="warna" value="{{ old('warna', '#6c757d') }}"
                                    style="width: 100%; height: 50px;">
                                @error('warna')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Warna akan digunakan untuk identifikasi visual bidang</small>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between gap-2 py-2 align-items-center">
                                <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                    <i class="bi bi-x-circle me-1"></i> Batal
                                </button>
                                <button type="submit" class="btn btn-primary" id="btnSubmit">
                                    <i class="bi bi-check-lg me-1"></i> Simpan Bidang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Data -->
                <div class="col-lg-8">
                    <!-- Informasi Dasar -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-info-circle me-2"></i>Informasi Dasar
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Kode Bidang <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('kode') is-invalid @enderror"
                                        name="kode" value="{{ old('kode') }}" required
                                        placeholder="Contoh: PLAN, EVAL, DATA" style="text-transform: uppercase;">
                                    @error('kode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Kode unik bidang (maksimal 20 karakter)</small>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Nama Bidang <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                        name="nama" value="{{ old('nama') }}" required
                                        placeholder="Contoh: Bidang Perencanaan Ekonomi">
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Deskripsi <small class="text-muted">(opsional)</small></label>
                                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" name="deskripsi" rows="4"
                                        placeholder="Deskripsi tugas dan fungsi bidang">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select @error('is_active') is-invalid @enderror" name="is_active">
                                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Nonaktif
                                        </option>
                                    </select>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Card -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-eye me-2"></i>Preview Bidang
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="me-3">
                                    <div class="rounded-circle" id="previewColorSmall"
                                        style="width: 40px; height: 40px; background-color: #6c757d; border: 2px solid #dee2e6;">
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold" id="previewNama">-</h6>
                                    <small class="text-muted" id="previewKode">-</small>
                                    <div class="mt-1">
                                        <small class="text-muted" id="previewDeskripsi">-</small>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge bg-success" id="previewStatus">Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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

        /* Color preview */
        #colorPreview {
            border: 3px solid #fff;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
        }

        /* Custom form styling */
        .form-control:focus,
        .form-select:focus {
            border-color: #5F71E4;
            box-shadow: 0 0 0 0.25rem rgba(95, 113, 228, 0.25);
        }

        .text-danger {
            color: #dc3545 !important;
        }

        /* Make kode input uppercase */
        input[name="kode"] {
            text-transform: uppercase;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Color preview update
            $('#warna').on('input', function() {
                const color = $(this).val();
                $('#colorPreview').css('background-color', color);
                $('#previewColorSmall').css('background-color', color);
            });

            // Live preview updates
            $('input[name="nama"]').on('input', function() {
                const nama = $(this).val() || '-';
                $('#previewNama').text(nama);
            });

            $('input[name="kode"]').on('input', function() {
                const kode = $(this).val() || '-';
                $('#previewKode').text(kode);
            });

            $('textarea[name="deskripsi"]').on('input', function() {
                const deskripsi = $(this).val() || '-';
                $('#previewDeskripsi').text(deskripsi.length > 50 ? deskripsi.substring(0, 50) + '...' :
                    deskripsi);
            });

            $('select[name="is_active"]').on('change', function() {
                const isActive = $(this).val() === '1';
                const badge = $('#previewStatus');
                if (isActive) {
                    badge.removeClass('bg-danger').addClass('bg-success').text('Aktif');
                } else {
                    badge.removeClass('bg-success').addClass('bg-danger').text('Nonaktif');
                }
            });

            // Auto uppercase for kode
            $('input[name="kode"]').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });

            // Form validation
            $('#formBidang').submit(function(e) {
                let isValid = true;

                // Check required fields
                $(this).find('input[required], select[required]').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Mohon lengkapi semua field yang wajib diisi.'
                    });
                } else {
                    $('#btnSubmit').html(
                        '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                    $('#btnSubmit').prop('disabled', true);
                }
            });

            // Remove validation class on input
            $('.form-control, .form-select').on('input change', function() {
                $(this).removeClass('is-invalid');
            });
        });
    </script>
@endpush
