@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Detail & Edit Bidang</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">E-Kinerja</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master.bidang.index') }}">Master Data Bidang</a></li>
                <li class="breadcrumb-item active">{{ $bidang->nama }}</li>
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
                        <div class="d-flex align-items-center">
                            @if ($bidang->warna)
                                <div class="rounded-circle me-3"
                                    style="width: 40px; height: 40px; background-color: {{ $bidang->warna }}; border: 2px solid #dee2e6;">
                                </div>
                            @endif
                            <div>
                                <h5 class="mb-0 fw-bold">{{ $bidang->nama }}</h5>
                                <small class="text-muted">{{ $bidang->kode }} • {{ $bidang->pegawai->count() }}
                                    pegawai</small>
                            </div>
                        </div>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" id="btnEdit">
                            <i class="bi bi-pencil-square me-1"></i> Edit
                        </button>
                        <button type="button" class="btn btn-success d-none" id="btnSave">
                            <i class="bi bi-check-lg me-1"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-outline-secondary d-none" id="btnCancel">
                            <i class="bi bi-x-lg me-1"></i> Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <form id="formBidang" action="{{ route('master.bidang.update', $bidang->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Color Preview & Stats -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-palette me-2"></i>Warna Bidang
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="color-preview-container mb-3">
                                <div class="rounded-circle mx-auto shadow" id="colorPreview"
                                    style="width: 120px; height: 120px; background-color: {{ $bidang->warna ?: '#6c757d' }}; border: 3px solid #fff;">
                                </div>
                            </div>

                            <div class="mb-3 edit-mode d-none">
                                <input type="color" class="form-control form-control-color" id="warna" name="warna"
                                    value="{{ $bidang->warna ?: '#6c757d' }}" style="width: 100%; height: 50px;">
                                <small class="text-muted">Pilih warna untuk identifikasi visual</small>
                            </div>

                            <div class="bidang-info">
                                <h5 class="fw-bold mb-1">{{ $bidang->nama }}</h5>
                                <p class="text-muted mb-2">{{ $bidang->kode }}</p>

                                <div class="row text-start">
                                    <div class="col-12 mb-2">
                                        <small class="text-muted">Status</small>
                                        <div class="fw-bold">
                                            @if ($bidang->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-danger">Nonaktif</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <small class="text-muted">Total Pegawai</small>
                                        <div class="fw-bold">{{ $bidang->pegawai->count() }} pegawai</div>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted">Dibuat</small>
                                        <div class="fw-bold small">{{ $formatDate($bidang->created_at) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pegawai List -->
                    @if ($bidang->pegawai->count() > 0)
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-people me-2"></i>Daftar Pegawai
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    @foreach ($bidang->pegawai->take(5) as $pegawai)
                                        <div class="list-group-item border-0 px-0">
                                            <div class="d-flex align-items-center">
                                                @if ($pegawai->foto_profile_path)
                                                    <img src="{{ asset($pegawai->foto_profile_path) }}"
                                                        alt="{{ $pegawai->nama }}" class="rounded-circle me-2"
                                                        style="width: 32px; height: 32px; object-fit: cover;">
                                                @else
                                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2"
                                                        style="width: 32px; height: 32px;">
                                                        <i class="bi bi-person text-primary" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                @endif
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold small">{{ $pegawai->nama }}</div>
                                                    <small class="text-muted">{{ $pegawai->jabatan->nama ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if ($bidang->pegawai->count() > 5)
                                        <div class="list-group-item border-0 px-0 text-center">
                                            <small class="text-muted">dan {{ $bidang->pegawai->count() - 5 }} pegawai
                                                lainnya</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Detail Data -->
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
                                    <label class="form-label">Kode Bidang</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext fw-bold">{{ $bidang->kode }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="kode"
                                            value="{{ $bidang->kode }}" required style="text-transform: uppercase;">
                                    </div>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Nama Bidang</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext fw-bold">{{ $bidang->nama }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="nama"
                                            value="{{ $bidang->nama }}" required>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $bidang->deskripsi ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <textarea class="form-control" name="deskripsi" rows="4" placeholder="Deskripsi tugas dan fungsi bidang">{{ $bidang->deskripsi }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">
                                            @if ($bidang->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-danger">Nonaktif</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <select class="form-select" name="is_active">
                                            <option value="1" {{ $bidang->is_active ? 'selected' : '' }}>Aktif
                                            </option>
                                            <option value="0" {{ !$bidang->is_active ? 'selected' : '' }}>Nonaktif
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Warna</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext d-flex align-items-center">
                                            @if ($bidang->warna)
                                                <div class="rounded me-2"
                                                    style="width: 20px; height: 20px; background-color: {{ $bidang->warna }}; border: 1px solid #dee2e6;">
                                                </div>
                                                {{ $bidang->warna }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistik -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-graph-up me-2"></i>Statistik
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="border rounded p-3">
                                        <h4 class="fw-bold mb-1 text-primary">{{ $bidang->pegawai->count() }}</h4>
                                        <small class="text-muted">Total Pegawai</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="border rounded p-3">
                                        <h4 class="fw-bold mb-1 text-success">
                                            {{ $bidang->pegawai->where('status_aktif', 'Aktif')->count() }}</h4>
                                        <small class="text-muted">Pegawai Aktif</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="border rounded p-3">
                                        <h4 class="fw-bold mb-1 text-info">
                                            {{ $bidang->pegawai->where('status_kepegawaian', 'PNS')->count() }}</h4>
                                        <small class="text-muted">PNS</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="border rounded p-3">
                                        <h4 class="fw-bold mb-1 text-warning">
                                            {{ $bidang->pegawai->whereIn('status_kepegawaian', ['PPPK', 'Kontrak'])->count() }}
                                        </h4>
                                        <small class="text-muted">Non PNS</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>

    <style>
        .color-preview-container {
            position: relative;
        }

        .card {
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            border-bottom: 1px solid #dee2e6;
            background-color: #f8f9fa !important;
        }

        .form-control-plaintext {
            padding: 0.375rem 0;
            margin-bottom: 0;
            background-color: transparent;
            border: solid transparent;
            border-width: 1px 0;
            font-weight: 500;
        }

        .btn-group .btn {
            padding: 0.5rem 1rem;
        }

        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }

        /* Smooth transitions */
        .view-mode,
        .edit-mode {
            transition: all 0.3s ease;
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

        /* Auto uppercase for kode */
        input[name="kode"] {
            text-transform: uppercase;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let editMode = false;

            // Toggle edit mode
            $('#btnEdit').click(function() {
                toggleEditMode(true);
            });

            $('#btnCancel').click(function() {
                toggleEditMode(false);
                resetForm();
            });

            // Save form
            $('#btnSave').click(function() {
                if (validateForm()) {
                    $('#formBidang').submit();
                }
            });

            // Color preview
            $('#warna').change(function() {
                const color = $(this).val();
                $('#colorPreview').css('background-color', color);
            });

            // Auto uppercase for kode
            $('input[name="kode"]').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });

            function toggleEditMode(enable) {
                editMode = enable;

                if (enable) {
                    $('.view-mode').addClass('d-none');
                    $('.edit-mode').removeClass('d-none');
                    $('#btnEdit').addClass('d-none');
                    $('#btnSave, #btnCancel').removeClass('d-none');
                } else {
                    $('.edit-mode').addClass('d-none');
                    $('.view-mode').removeClass('d-none');
                    $('#btnSave, #btnCancel').addClass('d-none');
                    $('#btnEdit').removeClass('d-none');
                }
            }

            function resetForm() {
                // Reset form to original values
                $('#formBidang')[0].reset();

                // Reset color preview
                $('#colorPreview').css('background-color', '{{ $bidang->warna ?: '#6c757d' }}');
            }

            function validateForm() {
                let isValid = true;

                // Validate required fields
                $('#formBidang input[required], #formBidang select[required]').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Mohon lengkapi semua field yang wajib diisi.'
                    });
                }

                return isValid;
            }

            // Form submission handler
            $('#formBidang').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#btnSave').html(
                            '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...'
                        );
                        $('#btnSave').prop('disabled', true);
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Data bidang berhasil diperbarui',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan saat menyimpan data';

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors)[0][0];
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMessage
                        });
                    },
                    complete: function() {
                        $('#btnSave').html('<i class="bi bi-check-lg me-1"></i> Simpan');
                        $('#btnSave').prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush
