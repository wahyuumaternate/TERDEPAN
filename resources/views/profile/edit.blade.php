@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Profil Saya</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">E-Kinerja</a></li>
                <li class="breadcrumb-item active">Profil</li>
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
                            <h5 class="mb-0 fw-bold">{{ $pegawai->profile?->gelar_depan }} {{ $pegawai->nama }}
                                {{ $pegawai->profile?->gelar_belakang }}</h5>
                            <small class="text-muted">{{ $pegawai->profile?->nomor_identitas }} • {{ $pegawai->email }}</small>
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

        <form id="formProfile" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="row">
                <!-- Profil & Foto -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-person-circle me-2"></i>Profil Pegawai
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="profile-photo-container mb-3 mt-2">
                                @if ($pegawai->profile?->foto_profile_url)
                                    <img src="{{ $pegawai->profile->foto_profile_url }}"
                                        alt="{{ $pegawai->nama }}" class="rounded-circle shadow" id="profilePhoto"
                                        style="width: 120px; height: 120px; object-fit: cover;">
                                @else
                                    @if ($pegawai->profile?->jenis_kelamin == 'L')
                                        <img src="{{ asset('assets/img/avatar-laki-laki.webp') }}"
                                            alt="{{ $pegawai->nama }}" class="rounded-circle shadow" id="profilePhoto"
                                            style="width: 120px; height: 120px; object-fit: cover;">
                                    @else
                                        <img src="{{ asset('assets/img/avatar-perempuan.webp') }}"
                                            alt="{{ $pegawai->nama }}" class="rounded-circle shadow" id="profilePhoto"
                                            style="width: 120px; height: 120px; object-fit: cover;">
                                    @endif
                                @endif
                            </div>

                            <div class="mb-3 edit-mode d-none">
                                <input type="file" class="form-control" id="foto_profile" name="foto_profile"
                                    accept="image/*">
                                <small class="text-muted">Format: JPG, JPEG, PNG. Maksimal 2MB</small>
                            </div>

                            <div class="profile-info">
                                <h5 class="fw-bold mb-1">{{ $pegawai->profile?->gelar_depan }} {{ $pegawai->nama }}
                                    {{ $pegawai->profile?->gelar_belakang }}</h5>
                                <p class="text-muted mb-2">{{ $pegawai->profile?->jabatan->nama ?? '-' }}</p>
                                <p class="text-muted small mb-3">{{ $pegawai->profile?->bidang->nama ?? '-' }}</p>

                                <div class="row text-start">
                                    <div class="col-12 mb-2">
                                        <small class="text-muted">Status Kepegawaian</small>
                                        <div class="fw-bold">
                                            @switch($pegawai->profile?->status_kepegawaian)
                                                @case('PNS')
                                                    <span class="badge bg-success">PNS</span>
                                                @break

                                                @case('CPNS')
                                                    <span class="badge bg-success bg-opacity-50">CPNS</span>
                                                @break

                                                @case('PPPK')
                                                    <span class="badge bg-info">PPPK</span>
                                                @break

                                                @case('Kontrak')
                                                    <span class="badge bg-warning">Kontrak</span>
                                                @break
                                            @endswitch
                                        </div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <small class="text-muted">Status Aktif</small>
                                        <div class="fw-bold">
                                            @switch($pegawai->profile?->status_aktif)
                                                @case('Aktif')
                                                    <span class="badge bg-success">Aktif</span>
                                                @break

                                                @case('Nonaktif')
                                                    <span class="badge bg-danger">Nonaktif</span>
                                                @break

                                                @case('Cuti')
                                                    <span class="badge bg-warning">Cuti</span>
                                                @break

                                                @case('Pensiun')
                                                    <span class="badge bg-secondary">Pensiun</span>
                                                @break
                                            @endswitch
                                        </div>
                                    </div>
                                    @if ($pegawai->profile?->last_login_at)
                                        <div class="col-12">
                                            <small class="text-muted">Login Terakhir</small>
                                            <div class="fw-bold small">{{ $pegawai->profile?->last_login_at->format('d M Y H:i') }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Password -->
                    <div class="card shadow-sm border-0 mb-4 edit-mode d-none">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-key me-2"></i>Ubah Password
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Password Saat Ini</label>
                                <input type="password" class="form-control" name="current_password"
                                    id="current_password" autocomplete="current-password">
                                @error('current_password', 'updatePassword')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" class="form-control" name="password" id="password"
                                    placeholder="Kosongkan jika tidak ingin mengubah" autocomplete="new-password">
                                @error('password', 'updatePassword')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" name="password_confirmation"
                                    id="password_confirmation" placeholder="Kosongkan jika tidak ingin mengubah"
                                    autocomplete="new-password">
                                @error('password_confirmation', 'updatePassword')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Kosongkan field password jika tidak ingin mengubah password.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Detail Data -->
                <div class="col-lg-8">
                    <!-- Informasi Identitas -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-card-text me-2"></i>Informasi Identitas
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nomor Identitas</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->nomor_identitas }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="nomor_identitas"
                                            value="{{ $pegawai->profile?->nomor_identitas }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipe Identitas</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->tipe_identitas }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <select class="form-select" name="tipe_identitas" required>
                                            <option value="NIP"
                                                {{ $pegawai->profile?->tipe_identitas == 'NIP' ? 'selected' : '' }}>NIP</option>
                                            <option value="NIK"
                                                {{ $pegawai->profile?->tipe_identitas == 'NIK' ? 'selected' : '' }}>NIK</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Gelar Depan</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->gelar_depan ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="gelar_depan"
                                            value="{{ $pegawai->profile?->gelar_depan }}" placeholder="Dr., Ir., dll">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext fw-bold">{{ $pegawai->nama }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="nama"
                                            value="{{ $pegawai->nama }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Gelar Belakang</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->gelar_belakang ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="gelar_belakang"
                                            value="{{ $pegawai->profile?->gelar_belakang }}" placeholder="S.T., M.T., dll">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->email }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="email" class="form-control" name="email"
                                            value="{{ $pegawai->email }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No. Telepon</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->no_telepon ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="no_telepon"
                                            value="{{ $pegawai->profile?->no_telepon }}" placeholder="08xxxxxxxxxx">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">
                                            {{ $pegawai->profile?->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <select class="form-select" name="jenis_kelamin" required>
                                            <option value="L" {{ $pegawai->profile?->jenis_kelamin == 'L' ? 'selected' : '' }}>
                                                Laki-laki</option>
                                            <option value="P" {{ $pegawai->profile?->jenis_kelamin == 'P' ? 'selected' : '' }}>
                                                Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">
                                            {{ $pegawai->profile?->tanggal_lahir ? date('d M Y', strtotime($pegawai->profile?->tanggal_lahir)) : '-' }}
                                        </div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="date" class="form-control" name="tanggal_lahir"
                                            value="{{ $pegawai->profile?->tanggal_lahir }}">
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Alamat</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->alamat ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <textarea class="form-control" name="alamat" rows="3" placeholder="Alamat lengkap">{{ $pegawai->profile?->alamat }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Kepegawaian -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-briefcase me-2"></i>Informasi Kepegawaian
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jabatan</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->jabatan->nama ?? '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control"
                                            value="{{ $pegawai->profile?->jabatan->nama ?? '-' }}" disabled>
                                        <small class="text-muted">Hubungi administrator untuk mengubah jabatan</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bidang</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->bidang->nama ?? '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control"
                                            value="{{ $pegawai->profile?->bidang->nama ?? '-' }}" disabled>
                                        <small class="text-muted">Hubungi administrator untuk mengubah bidang</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status Kepegawaian</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->status_kepegawaian }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control"
                                            value="{{ $pegawai->profile?->status_kepegawaian }}" disabled>
                                        <small class="text-muted">Hubungi administrator untuk mengubah status</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status Aktif</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->status_aktif }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" value="{{ $pegawai->profile?->status_aktif }}"
                                            disabled>
                                        <small class="text-muted">Hubungi administrator untuk mengubah status</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pangkat</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->pangkat ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="pangkat"
                                            value="{{ $pegawai->profile?->pangkat }}" placeholder="Penata, Pembina, dll">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Golongan</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->golongan ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="golongan"
                                            value="{{ $pegawai->profile?->golongan }}" placeholder="III/a, III/b, IV/a, dll">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Masuk</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">
                                            {{ $pegawai->profile?->tanggal_masuk ? date('d M Y', strtotime($pegawai->profile?->tanggal_masuk)) : '-' }}
                                        </div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control"
                                            value="{{ $pegawai->profile?->tanggal_masuk ? date('d M Y', strtotime($pegawai->profile?->tanggal_masuk)) : '-' }}"
                                            disabled>
                                        <small class="text-muted">Hubungi administrator untuk mengubah</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Atasan Langsung</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile?->atasanLangsung->nama ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control"
                                            value="{{ $pegawai->profile?->atasanLangsung->nama ?? '-' }}" disabled>
                                        <small class="text-muted">Hubungi administrator untuk mengubah atasan</small>
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
        .profile-photo-container {
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

        /* Photo preview */
        #profilePhoto {
            border: 3px solid #fff;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
        }

        /* Custom form styling */
        .form-control:focus,
        .form-select:focus {
            border-color: #5F71E4;
            box-shadow: 0 0 0 0.25rem rgba(95, 113, 228, 0.25);
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
                    $('#formProfile').submit();
                }
            });

            // Photo preview
            $('#foto_profile').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#profilePhoto').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
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
                $('#formProfile')[0].reset();

                // Clear password fields
                $('#current_password, #password, #password_confirmation').val('');

                // Reset photo preview
                @if ($pegawai->profile?->foto_profile_url)
                    $('#profilePhoto').attr('src', '{{ $pegawai->profile->foto_profile_url }}');
                @else
                    @if ($pegawai->profile?->jenis_kelamin == 'L')
                        $('#profilePhoto').attr('src', '{{ asset('assets/img/avatar-laki-laki.webp') }}');
                    @else
                        $('#profilePhoto').attr('src', '{{ asset('assets/img/avatar-perempuan.webp') }}');
                    @endif
                @endif
            }

            function validateForm() {
                let isValid = true;

                // Validate required fields
                $('#formProfile input[required], #formProfile select[required]').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                // Validate email format
                const email = $('input[name="email"]').val();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (email && !emailRegex.test(email)) {
                    $('input[name="email"]').addClass('is-invalid');
                    isValid = false;
                }

                // Validate password confirmation
                const password = $('#password').val();
                const passwordConfirmation = $('#password_confirmation').val();
                if (password && password !== passwordConfirmation) {
                    $('#password_confirmation').addClass('is-invalid');
                    isValid = false;

                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Konfirmasi password tidak sama dengan password baru.'
                    });
                    return false;
                }

                // If changing password, current password is required
                if (password && !$('#current_password').val()) {
                    $('#current_password').addClass('is-invalid');
                    isValid = false;

                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Masukkan password saat ini untuk mengubah password.'
                    });
                    return false;
                }

                if (!isValid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Mohon lengkapi semua field yang wajib diisi dengan benar.'
                    });
                }

                return isValid;
            }

            // Form submission handler
            $('#formProfile').submit(function(e) {
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
                            text: 'Profil berhasil diperbarui',
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
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
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

            // Show success message
            @if (session('status') === 'profile-updated')
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Profil berhasil diperbarui',
                    timer: 2000,
                    showConfirmButton: false
                });
            @endif

            @if (session('status') === 'password-updated')
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Password berhasil diperbarui',
                    timer: 2000,
                    showConfirmButton: false
                });
            @endif
        });
    </script>
@endpush
