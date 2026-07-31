@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Detail & Edit Pegawai</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">E-Kinerja</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master.pegawai.index') }}">Master Data Pegawai</a></li>
                <li class="breadcrumb-item active">{{ $pegawai->nama }}</li>
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
                            <h5 class="mb-0 fw-bold">{{ $pegawai->profile->gelar_depan }} {{ $pegawai->nama }}
                                {{ $pegawai->profile->gelar_belakang }}</h5>
                            <small class="text-muted">{{ $pegawai->profile->nomor_identitas }} • {{ $pegawai->email }}</small>
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

        <form id="formPegawai" action="{{ route('master.pegawai.update', $pegawai->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

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
                                @if ($pegawai->profile->foto_profile_path)
                                    <img src="{{ asset($pegawai->profile->foto_profile_path) }}" alt="{{ $pegawai->nama }}"
                                        class="rounded-circle shadow" id="profilePhoto"
                                        style="width: 120px; height: 120px; object-fit: cover;">
                                @else
                                    @if ($pegawai->profile->jenis_kelamin == 'L')
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
                                <h5 class="fw-bold mb-1">{{ $pegawai->profile->gelar_depan }} {{ $pegawai->nama }}
                                    {{ $pegawai->profile->gelar_belakang }}</h5>
                                <p class="text-muted mb-2">{{ $pegawai->profile->jabatan->nama ?? '-' }}</p>
                                <p class="text-muted small mb-3">{{ $pegawai->profile->bidang->nama ?? '-' }}</p>

                                <div class="row text-start">
                                    <div class="col-12 mb-2">
                                        <small class="text-muted">Status Kepegawaian</small>
                                        <div class="fw-bold">
                                            @switch($pegawai->profile->status_kepegawaian)
                                                @case('PNS')
                                                    <span class="badge bg-success">PNS</span>
                                                @break

                                                @case('PPPK')
                                                    <span class="badge bg-info">PPPK</span>
                                                @break

                                                @case('Kontrak')
                                                    <span class="badge bg-warning">Kontrak</span>
                                                @break

                                                @default
                                                    <span class="badge bg-secondary">{{ $pegawai->profile->status_kepegawaian }}</span>
                                            @endswitch
                                        </div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <small class="text-muted">Status Aktif</small>
                                        <div class="fw-bold">
                                            @switch($pegawai->profile->status_aktif)
                                                @case('Aktif')
                                                    <span class="badge bg-success">{{ $pegawai->profile->status_aktif }}</span>
                                                @break

                                                @case('Nonaktif')
                                                    <span class="badge bg-danger">{{ $pegawai->profile->status_aktif }}</span>
                                                @break

                                                @case('Cuti')
                                                    <span class="badge bg-warning">{{ $pegawai->profile->status_aktif }}</span>
                                                @break

                                                @case('Pensiun')
                                                    <span class="badge bg-secondary">{{ $pegawai->profile->status_aktif }}</span>
                                                @break

                                                @default
                                                    <span class="badge bg-light text-dark">{{ $pegawai->profile->status_aktif }}</span>
                                            @endswitch
                                        </div>
                                    </div>
                                    @if ($pegawai->profile->last_login_at)
                                        <div class="col-12">
                                            <small class="text-muted">Login Terakhir</small>
                                            <div class="fw-bold small">{{ $pegawai->profile->last_login_at->format('d M Y H:i') }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
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
                                        <div class="form-control-plaintext">{{ $pegawai->profile->nomor_identitas }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="nomor_identitas"
                                            value="{{ $pegawai->profile->nomor_identitas }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipe Identitas</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile->tipe_identitas }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <select class="form-select" name="tipe_identitas" required>
                                            <option value="NIP"
                                                {{ $pegawai->profile->tipe_identitas == 'NIP' ? 'selected' : '' }}>NIP</option>
                                            <option value="NIK"
                                                {{ $pegawai->profile->tipe_identitas == 'NIK' ? 'selected' : '' }}>NIK</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Gelar Depan</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile->gelar_depan ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="gelar_depan"
                                            value="{{ $pegawai->profile->gelar_depan }}" placeholder="Dr., Ir., dll">
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
                                        <div class="form-control-plaintext">{{ $pegawai->profile->gelar_belakang ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="gelar_belakang"
                                            value="{{ $pegawai->profile->gelar_belakang }}" placeholder="S.T., M.T., dll">
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
                                        <div class="form-control-plaintext">{{ $pegawai->profile->no_telepon ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="no_telepon"
                                            value="{{ $pegawai->profile->no_telepon }}" placeholder="08xxxxxxxxxx">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">
                                            {{ $pegawai->profile->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <select class="form-select" name="jenis_kelamin" required>
                                            <option value="L" {{ $pegawai->profile->jenis_kelamin == 'L' ? 'selected' : '' }}>
                                                Laki-laki</option>
                                            <option value="P" {{ $pegawai->profile->jenis_kelamin == 'P' ? 'selected' : '' }}>
                                                Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">
                                            {{ $pegawai->profile->tanggal_lahir ? date('d M Y', strtotime($pegawai->profile->tanggal_lahir)) : '-' }}
                                        </div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="date" class="form-control" name="tanggal_lahir"
                                            value="{{ $pegawai->profile->tanggal_lahir }}">
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Alamat</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile->alamat ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <textarea class="form-control" name="alamat" rows="3" placeholder="Alamat lengkap">{{ $pegawai->profile->alamat }}</textarea>
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
                                        <div class="form-control-plaintext">{{ $pegawai->profile->jabatan->nama ?? '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <select class="form-select" name="jabatan_id" required>
                                            <option value="">Pilih Jabatan</option>
                                            @foreach (\App\Models\MasterJabatan::all() as $jabatan)
                                                <option value="{{ $jabatan->id }}"
                                                    {{ $pegawai->profile->jabatan_id == $jabatan->id ? 'selected' : '' }}>
                                                    {{ $jabatan->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bidang</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile->bidang->nama ?? '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <select class="form-select" name="bidang_id" required>
                                            <option value="">Pilih Bidang</option>
                                            @foreach (\App\Models\MasterBidang::all() as $bidang)
                                                <option value="{{ $bidang->id }}"
                                                    {{ $pegawai->profile->bidang_id == $bidang->id ? 'selected' : '' }}>
                                                    {{ $bidang->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status Kepegawaian</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile->status_kepegawaian }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <select class="form-select" name="status_kepegawaian" required>
                                            <option value="PNS"
                                                {{ $pegawai->profile->status_kepegawaian == 'PNS' ? 'selected' : '' }}>PNS</option>
                                            <option value="PPPK"
                                                {{ $pegawai->profile->status_kepegawaian == 'PPPK' ? 'selected' : '' }}>PPPK
                                            </option>
                                            <option value="Kontrak"
                                                {{ $pegawai->profile->status_kepegawaian == 'Kontrak' ? 'selected' : '' }}>Kontrak
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status Aktif</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile->status_aktif }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <select class="form-select" name="status_aktif">
                                            <option value="Aktif"
                                                {{ $pegawai->profile->status_aktif == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                            <option value="Nonaktif"
                                                {{ $pegawai->profile->status_aktif == 'Nonaktif' ? 'selected' : '' }}>Nonaktif
                                            </option>
                                            <option value="Cuti"
                                                {{ $pegawai->profile->status_aktif == 'Cuti' ? 'selected' : '' }}>Cuti</option>
                                            <option value="Pensiun"
                                                {{ $pegawai->profile->status_aktif == 'Pensiun' ? 'selected' : '' }}>Pensiun
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pangkat</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile->pangkat ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="pangkat"
                                            value="{{ $pegawai->profile->pangkat }}" placeholder="Penata, Pembina, dll">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Golongan</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile->golongan ?: '-' }}</div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="text" class="form-control" name="golongan"
                                            value="{{ $pegawai->profile->golongan }}" placeholder="III/a, III/b, IV/a, dll">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Masuk</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">
                                            {{ $pegawai->profile->tanggal_masuk ? date('d M Y', strtotime($pegawai->profile->tanggal_masuk)) : '-' }}
                                        </div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="date" class="form-control" name="tanggal_masuk"
                                            value="{{ $pegawai->profile->tanggal_masuk }}">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Keluar</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">
                                            {{ $pegawai->profile->tanggal_keluar ? date('d M Y', strtotime($pegawai->profile->tanggal_keluar)) : '-' }}
                                        </div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="date" class="form-control" name="tanggal_keluar"
                                            value="{{ $pegawai->profile->tanggal_keluar }}">
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Atasan Langsung</label>
                                    <div class="view-mode">
                                        <div class="form-control-plaintext">{{ $pegawai->profile->atasanLangsung->nama ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <select class="form-select" name="atasan_langsung_id">
                                            <option value="">Pilih Atasan Langsung</option>
                                            @foreach (\App\Models\User::where('id', '!=', $pegawai->id)->get() as $atasan)
<option value="{{ $atasan->id }}" {{ $pegawai->atasan_langsung_id == $atasan->id ? 'selected' : '' }}>
                                                    {{ $atasan->nama }} - {{ $atasan->jabatan->nama ?? '' }}
                                                </option>
@endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reset Password (hanya muncul di edit mode) -->
                    <div class="card shadow-sm border-0 mb-4 edit-mode d-none">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-key me-2"></i>Reset Password
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Konfirmasi Password</label>
                                    <input type="password" class="form-control" name="password_confirmation" placeholder="Kosongkan jika tidak ingin mengubah">
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Kosongkan field password jika tidak ingin mengubah password pegawai.
                            </small>
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
        .view-mode, .edit-mode {
            transition: all 0.3s ease;
        }

        /* Photo preview */
        #profilePhoto {
            border: 3px solid #fff;
            box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
        }

        /* Custom form styling */
        .form-control:focus, .form-select:focus {
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
                    $('#formPegawai').submit();
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
                $('#formPegawai')[0].reset();

                // Reset photo preview
                @if ($pegawai->profile->foto_profile_path)
                    $('#profilePhoto').attr('src', '{{ asset($pegawai->profile->foto_profile_path) }}');
                @else
                    $('#profilePhoto').html('<i class="bi bi-person text-primary" style="font-size: 3rem;"></i>');
                @endif
            }

            function validateForm() {
                let isValid = true;

                // Validate required fields
                $('#formPegawai input[required], #formPegawai select[required]').each(function() {
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
                const password = $('input[name="password"]').val();
                const passwordConfirmation = $('input[name="password_confirmation"]').val();
                if (password && password !== passwordConfirmation) {
                    $('input[name="password_confirmation"]').addClass('is-invalid');
                    isValid = false;
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
            $('#formPegawai').submit(function(e) {
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
                            text: 'Data pegawai berhasil diperbarui',
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
        });
    </script>
@endpush)
