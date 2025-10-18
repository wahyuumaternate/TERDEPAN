@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Detail Jabatan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master.jabatan.index') }}">Master Data Jabatan</a></li>
                <li class="breadcrumb-item active">{{ $jabatan->nama }}</li>
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
                            <h5 class="mb-0 fw-bold">{{ $jabatan->nama }}</h5>
                            <small class="text-muted">
                                Level {{ $jabatan->level }} •
                                {{ $jabatan->is_struktural ? 'Struktural' : 'Fungsional' }} •
                                {{ $jabatan->pegawai()->count() }} Pegawai
                            </small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btnEdit">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                        <button type="button" class="btn btn-outline-danger" id="btnDelete">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Informasi Jabatan -->
            <div class="col-lg-8">
                <!-- Detail View -->
                <div id="detailView">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-info-circle me-2"></i>Detail Jabatan
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="text-muted small">Kode Jabatan</label>
                                    <p class="fw-bold">{{ $jabatan->kode }}</p>
                                </div>
                                <div class="col-md-9">
                                    <label class="text-muted small">Nama Jabatan</label>
                                    <p class="fw-bold">{{ $jabatan->nama }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small">Level Hierarki</label>
                                    <p>
                                        <span class="badge bg-primary">Level {{ $jabatan->level }}</span>
                                        <small class="text-muted d-block">
                                            @if ($jabatan->level == 1)
                                                Kepala Badan
                                            @elseif($jabatan->level == 2)
                                                Sekretaris Badan
                                            @elseif($jabatan->level == 3)
                                                Kepala Bidang
                                            @elseif($jabatan->level == 4)
                                                Jabatan Fungsional
                                            @elseif($jabatan->level == 5)
                                                Staff
                                            @else
                                                Tenaga Teknis
                                            @endif
                                        </small>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small">Tipe Jabatan</label>
                                    <p>
                                        @if ($jabatan->is_struktural)
                                            <span class="badge bg-success">Struktural</span>
                                        @else
                                            <span class="badge bg-info">Fungsional</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small">Status</label>
                                    <p>
                                        @if ($jabatan->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Nonaktif</span>
                                        @endif
                                    </p>
                                </div>
                                @if ($jabatan->bebas_nilai_kinerja)
                                    <div class="col-md-12">
                                        <label class="text-muted small">Penilaian Kinerja</label>
                                        <p>
                                            <span class="badge bg-warning">Bebas Nilai Kinerja</span>
                                            <small class="text-muted d-block">Jabatan ini tidak dinilai kinerjanya</small>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <div id="editForm" style="display: none;">
                    <form id="formEditJabatan" action="{{ route('master.jabatan.update', $jabatan->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-pencil me-2"></i>Edit Jabatan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Kode Jabatan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="kode"
                                            value="{{ $jabatan->kode }}" required style="text-transform: uppercase;">
                                    </div>
                                    <div class="col-md-9 mb-3">
                                        <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nama"
                                            value="{{ $jabatan->nama }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Level Hierarki <span class="text-danger">*</span></label>
                                        <select class="form-select" name="level" required>
                                            <option value="1" {{ $jabatan->level == 1 ? 'selected' : '' }}>Level 1 -
                                                Kepala Badan</option>
                                            <option value="2" {{ $jabatan->level == 2 ? 'selected' : '' }}>Level 2 -
                                                Sekretaris Badan</option>
                                            <option value="3" {{ $jabatan->level == 3 ? 'selected' : '' }}>Level 3 -
                                                Kepala Bidang</option>
                                            <option value="4" {{ $jabatan->level == 4 ? 'selected' : '' }}>Level 4 -
                                                Staff</option>
                                            <option value="5" {{ $jabatan->level == 5 ? 'selected' : '' }}>Level 5 -
                                                Jabatan Fungsional</option>
                                            <option value="6" {{ $jabatan->level == 6 ? 'selected' : '' }}>Level 6 -
                                                Tenaga Teknis</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="is_active">
                                            <option value="1" {{ $jabatan->is_active ? 'selected' : '' }}>Aktif
                                            </option>
                                            <option value="0" {{ !$jabatan->is_active ? 'selected' : '' }}>Nonaktif
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Tipe Jabatan</label>
                                        <div class="mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_struktural"
                                                    value="1" {{ $jabatan->is_struktural ? 'checked' : '' }}>
                                                <label class="form-check-label">
                                                    Jabatan Struktural
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bebas_nilai_kinerja"
                                                value="1" {{ $jabatan->bebas_nilai_kinerja ? 'checked' : '' }}>
                                            <label class="form-check-label">
                                                Bebas Nilai Kinerja (khusus Tenaga Teknis)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-outline-secondary" id="btnCancelEdit">
                                        <i class="bi bi-x-circle me-1"></i> Batal
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Data Pegawai -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-people me-2"></i>Pegawai dengan Jabatan Ini
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($jabatan->pegawai()->count() > 0)
                            <div class="row">
                                @foreach ($jabatan->pegawai as $pegawai)
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center p-3 border rounded">
                                            <div class="me-3">
                                                @if ($pegawai->foto_profile_path && file_exists(public_path('uploads/pegawai/' . $pegawai->foto_profile_path)))
                                                    <img src="{{ asset('uploads/pegawai/' . $pegawai->foto_profile_path) }}"
                                                        alt="Foto" class="rounded-circle"
                                                        style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                        style="width: 50px; height: 50px;">
                                                        <i class="bi bi-person text-primary"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-bold">{{ $pegawai->nama }}</h6>
                                                <small class="text-muted">NIP: {{ $pegawai->nomor_identitas }}</small>
                                                <div>
                                                    <small class="text-muted">{{ $pegawai->bidang->nama ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">Belum ada pegawai dengan jabatan ini</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-lg-4">
                <!-- Statistik -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-bar-chart me-2"></i>Statistik
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="text-primary fw-bold">{{ $jabatan->pegawai()->count() }}</h4>
                                    <small class="text-muted">Total Pegawai</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success fw-bold">{{ $jabatan->level }}</h4>
                                <small class="text-muted">Level Hierarki</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Level Info -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-diagram-3 me-2"></i>Hierarki Level
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="level-info">
                            <div class="mb-3">
                                @php
                                    $levels = [
                                        1 => 'Kepala Badan (KABAN)',
                                        2 => 'Sekretaris Badan (SEKBAN)',
                                        3 => 'Kepala Bidang (KABID)',
                                        4 => 'Jabatan Fungsional (JF)',
                                        5 => 'Staff (STAFF)',
                                        6 => 'Tenaga Teknis (TT)',
                                    ];
                                @endphp
                                @foreach ($levels as $level => $name)
                                    <div class="d-flex align-items-center mb-2">
                                        <div
                                            class="badge {{ $jabatan->level == $level ? 'bg-primary' : 'bg-secondary' }} me-2">
                                            {{ $level }}</div>
                                        <small
                                            class="{{ $jabatan->level == $level ? 'fw-bold' : '' }}">{{ $name }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Timestamps -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>Riwayat
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="text-muted small">Dibuat</label>
                                <p class="mb-0">{{ $formatDate($jabatan->created_at) }}</p>
                            </div>
                            <div class="col-12">
                                <label class="text-muted small">Terakhir Diubah</label>
                                <p class="mb-0">{{ $formatDate($jabatan->updated_at) }}</p>
                            </div>
                        </div>
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

        /* Make kode input uppercase */
        input[name="kode"] {
            text-transform: uppercase;
        }

        .level-info .badge {
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Edit mode toggle
            $('#btnEdit').click(function() {
                $('#detailView').hide();
                $('#editForm').show();
                $(this).hide();
                $('#btnDelete').hide();
            });

            $('#btnCancelEdit').click(function() {
                $('#editForm').hide();
                $('#detailView').show();
                $('#btnEdit').show();
                $('#btnDelete').show();
            });

            // Auto uppercase for kode
            $('input[name="kode"]').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });

            // Form submission
            $('#formEditJabatan').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('#formEditJabatan button[type="submit"]').html(
                            '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...'
                        ).prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan sistem.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMessage
                        });

                        $('#formEditJabatan button[type="submit"]').html(
                            '<i class="bi bi-check-lg me-1"></i> Simpan Perubahan'
                        ).prop('disabled', false);
                    }
                });
            });

            // Delete function
            $('#btnDelete').click(function() {
                const pegawaiCount = {{ $jabatan->pegawai()->count() }};

                if (pegawaiCount > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Dapat Menghapus!',
                        text: `Jabatan ini masih digunakan oleh ${pegawaiCount} pegawai. Silakan pindahkan pegawai ke jabatan lain terlebih dahulu.`,
                        confirmButtonText: 'Mengerti'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data jabatan '{{ $jabatan->nama }}' akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('master.jabatan.destroy', $jabatan->id) }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.href =
                                            '{{ route('master.jabatan.index') }}';
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errorMessage = 'Terjadi kesalahan sistem.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: errorMessage
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
