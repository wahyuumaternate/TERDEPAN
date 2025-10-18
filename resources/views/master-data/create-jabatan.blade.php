@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Tambah Jabatan Baru</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master.jabatan.index') }}">Master Data Jabatan</a></li>
                <li class="breadcrumb-item active">Tambah Jabatan</li>
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
                            <h5 class="mb-0 fw-bold">Tambah Jabatan Baru</h5>
                            <small class="text-muted">Lengkapi form di bawah untuk menambah jabatan baru</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="formJabatan" action="{{ route('master.jabatan.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Level Info -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-diagram-3 me-2"></i>Hierarki Level
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="level-info">
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="badge bg-primary me-2">1</div>
                                        <small>Kepala Badan (KB)</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="badge bg-primary me-2">2</div>
                                        <small>Sekretaris Badan (SB)</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="badge bg-primary me-2">3</div>
                                        <small>Kepala Bidang (KBD)</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="badge bg-info me-2">4</div>
                                        <small>Staff (STF)</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="badge bg-success me-2">5</div>
                                        <small>Jabatan Fungsional (JF)</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="badge bg-warning me-2">6</div>
                                        <small>Tenaga Teknis (TT)</small>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Level digunakan untuk struktur hierarki organisasi
                                </small>
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
                                    <i class="bi bi-check-lg me-1"></i> Simpan Jabatan
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
                                    <label class="form-label">Kode Jabatan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('kode') is-invalid @enderror"
                                        name="kode" value="{{ old('kode') }}" required
                                        placeholder="Contoh: KB, SB, KBD" style="text-transform: uppercase;">
                                    @error('kode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Kode unik jabatan (maksimal 10 karakter)</small>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                        name="nama" value="{{ old('nama') }}" required
                                        placeholder="Contoh: Kepala Bidang Perencanaan">
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Level Hierarki <span class="text-danger">*</span></label>
                                    <select class="form-select @error('level') is-invalid @enderror" name="level"
                                        required>
                                        <option value="">Pilih Level</option>
                                        <option value="1" {{ old('level') == '1' ? 'selected' : '' }}>Level 1 - Kepala
                                            Badan</option>
                                        <option value="2" {{ old('level') == '2' ? 'selected' : '' }}>Level 2 -
                                            Sekretaris Badan</option>
                                        <option value="3" {{ old('level') == '3' ? 'selected' : '' }}>Level 3 - Kepala
                                            Bidang</option>
                                        <option value="4" {{ old('level') == '4' ? 'selected' : '' }}>Level 4 - Staff
                                        </option>
                                        <option value="5" {{ old('level') == '5' ? 'selected' : '' }}>Level 5 -
                                            Jabatan Fungsional</option>
                                        <option value="6" {{ old('level') == '6' ? 'selected' : '' }}>Level 6 - Tenaga
                                            Teknis</option>
                                    </select>
                                    @error('level')
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

                    <!-- Konfigurasi Jabatan -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-gear me-2"></i>Konfigurasi Jabatan
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="is_struktural" name="is_struktural" value="1"
                                            {{ old('is_struktural') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_struktural">
                                            <strong>Jabatan Struktural</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">Aktifkan jika jabatan merupakan jabatan struktural</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="bebas_nilai_kinerja" name="bebas_nilai_kinerja" value="1"
                                            {{ old('bebas_nilai_kinerja') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="bebas_nilai_kinerja">
                                            <strong>Bebas Nilai Kinerja</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">Untuk Tenaga Teknis yang tidak dinilai kinerjanya</small>
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Informasi:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Struktural:</strong> Jabatan dengan hierarki organisasi (KB, SB, KBD)</li>
                                    <li><strong>Fungsional:</strong> Jabatan berdasarkan keahlian dan kompetensi</li>
                                    <li><strong>Bebas Nilai Kinerja:</strong> Khusus untuk Tenaga Teknis yang tidak
                                        dievaluasi</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Card -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-eye me-2"></i>Preview Jabatan
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="me-3">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 50px; height: 50px;">
                                        <i class="bi bi-diagram-3 text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold" id="previewNama">-</h6>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <small class="text-muted" id="previewKode">-</small>
                                        <span class="badge bg-primary bg-opacity-10 text-primary"
                                            id="previewLevel">-</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success d-none" id="previewStruktural">Struktural</span>
                                        <span class="badge bg-info d-none" id="previewFungsional">Fungsional</span>
                                        <span class="badge bg-warning d-none" id="previewBebasNilai">Bebas Nilai
                                            Kinerja</span>
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

        .form-check-input:checked {
            background-color: #5F71E4;
            border-color: #5F71E4;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Live preview updates
            $('input[name="nama"]').on('input', function() {
                const nama = $(this).val() || '-';
                $('#previewNama').text(nama);
            });

            $('input[name="kode"]').on('input', function() {
                const kode = $(this).val() || '-';
                $('#previewKode').text(kode);
            });

            $('select[name="level"]').on('change', function() {
                const level = $(this).val();
                if (level) {
                    $('#previewLevel').text('Level ' + level);
                } else {
                    $('#previewLevel').text('-');
                }
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

            $('input[name="is_struktural"]').on('change', function() {
                const isStruktural = $(this).is(':checked');
                if (isStruktural) {
                    $('#previewStruktural').removeClass('d-none');
                    $('#previewFungsional').addClass('d-none');
                } else {
                    $('#previewStruktural').addClass('d-none');
                    $('#previewFungsional').removeClass('d-none');
                }
            });

            $('input[name="bebas_nilai_kinerja"]').on('change', function() {
                const bebasNilai = $(this).is(':checked');
                if (bebasNilai) {
                    $('#previewBebasNilai').removeClass('d-none');
                } else {
                    $('#previewBebasNilai').addClass('d-none');
                }
            });

            // Auto uppercase for kode
            $('input[name="kode"]').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });

            // Form validation
            $('#formJabatan').submit(function(e) {
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

            // Initialize preview with default fungsional
            $('#previewFungsional').removeClass('d-none');
        });
    </script>
@endpush
