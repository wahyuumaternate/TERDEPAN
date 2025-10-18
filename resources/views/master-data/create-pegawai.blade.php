@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Tambah Pegawai Baru</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master.pegawai.index') }}">Master Data Pegawai</a></li>
                <li class="breadcrumb-item active">Tambah Pegawai</li>
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
                            <h5 class="mb-0 fw-bold">Tambah Pegawai Baru</h5>
                            <small class="text-muted">Lengkapi form di bawah untuk menambah pegawai baru</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="formPegawai" action="{{ route('master.pegawai.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <!-- Foto Profile -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-person-circle me-2"></i>Foto Profil
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="profile-photo-container mb-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center shadow mx-auto"
                                    id="profilePhoto" style="width: 120px; height: 120px;">
                                    <i class="bi bi-person text-primary" style="font-size: 3rem;"></i>
                                </div>
                            </div>

                            <div class="mb-3">
                                <input type="file" class="form-control" id="foto_profile" name="foto_profile"
                                    accept="image/*">
                                <small class="text-muted">Format: JPG, JPEG, PNG. Maksimal 2MB</small>
                            </div>
                        </div>
                    </div>
                    <!-- Password -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-key me-2"></i>Password Login
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        name="password" required placeholder="Minimal 6 karakter">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Konfirmasi Password <span
                                            class="text-danger">*</span></label>
                                    <input type="password"
                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                        name="password_confirmation" required placeholder="Ulangi password">
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
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
                                    <i class="bi bi-check-lg me-1"></i> Simpan Pegawai
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Form Data -->
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
                                    <label class="form-label">Nomor Identitas <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('nomor_identitas') is-invalid @enderror"
                                        name="nomor_identitas" value="{{ old('nomor_identitas') }}" required
                                        placeholder="Contoh: 197812312005011001">
                                    @error('nomor_identitas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipe Identitas <span class="text-danger">*</span></label>
                                    <select class="form-select @error('tipe_identitas') is-invalid @enderror"
                                        name="tipe_identitas" required>
                                        <option value="">Pilih Tipe Identitas</option>
                                        <option value="NIP" {{ old('tipe_identitas') == 'NIP' ? 'selected' : '' }}>NIP
                                        </option>
                                        <option value="NIK" {{ old('tipe_identitas') == 'NIK' ? 'selected' : '' }}>NIK
                                        </option>
                                    </select>
                                    @error('tipe_identitas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Gelar Depan</label>
                                    <input type="text" class="form-control @error('gelar_depan') is-invalid @enderror"
                                        name="gelar_depan" value="{{ old('gelar_depan') }}" placeholder="Dr., Ir., dll">
                                    @error('gelar_depan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                        name="nama" value="{{ old('nama') }}" required placeholder="Nama lengkap">
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Gelar Belakang</label>
                                    <input type="text" class="form-control @error('gelar_belakang') is-invalid @enderror"
                                        name="gelar_belakang" value="{{ old('gelar_belakang') }}"
                                        placeholder="S.T., M.T., dll">
                                    @error('gelar_belakang')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email') }}" required placeholder="email@domain.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control @error('no_telepon') is-invalid @enderror"
                                        name="no_telepon" value="{{ old('no_telepon') }}" placeholder="08xxxxxxxxxx">
                                    @error('no_telepon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select class="form-select @error('jenis_kelamin') is-invalid @enderror"
                                        name="jenis_kelamin" required>
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>
                                            Laki-laki</option>
                                        <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>
                                            Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date"
                                        class="form-control @error('tanggal_lahir') is-invalid @enderror"
                                        name="tanggal_lahir" value="{{ old('tanggal_lahir') }}">
                                    @error('tanggal_lahir')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea class="form-control @error('alamat') is-invalid @enderror" name="alamat" rows="3"
                                        placeholder="Alamat lengkap">{{ old('alamat') }}</textarea>
                                    @error('alamat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                    <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                                    <select class="form-select @error('jabatan_id') is-invalid @enderror"
                                        name="jabatan_id" required>
                                        <option value="">Pilih Jabatan</option>
                                        @foreach (App\Models\MasterJabatan::all() as $jabatan)
                                            <option value="{{ $jabatan->id }}"
                                                {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                                {{ $jabatan->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jabatan_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bidang <span class="text-danger">*</span></label>
                                    <select class="form-select @error('bidang_id') is-invalid @enderror" name="bidang_id"
                                        required>
                                        <option value="">Pilih Bidang</option>
                                        @foreach (App\Models\MasterBidang::all() as $bidang)
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
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status Kepegawaian <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('status_kepegawaian') is-invalid @enderror"
                                        name="status_kepegawaian" required>
                                        <option value="">Pilih Status Kepegawaian</option>
                                        <option value="PNS" {{ old('status_kepegawaian') == 'PNS' ? 'selected' : '' }}>
                                            PNS</option>
                                        <option value="PPPK"
                                            {{ old('status_kepegawaian') == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                                        <option value="Kontrak"
                                            {{ old('status_kepegawaian') == 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                                    </select>
                                    @error('status_kepegawaian')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status Aktif</label>
                                    <select class="form-select @error('status_aktif') is-invalid @enderror"
                                        name="status_aktif">
                                        <option value="Aktif"
                                            {{ old('status_aktif') == 'Aktif' ? 'selected' : 'selected' }}>Aktif</option>
                                        <option value="Nonaktif"
                                            {{ old('status_aktif') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                        <option value="Cuti" {{ old('status_aktif') == 'Cuti' ? 'selected' : '' }}>Cuti
                                        </option>
                                        <option value="Pensiun" {{ old('status_aktif') == 'Pensiun' ? 'selected' : '' }}>
                                            Pensiun</option>
                                    </select>
                                    @error('status_aktif')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pangkat</label>
                                    <input type="text" class="form-control @error('pangkat') is-invalid @enderror"
                                        name="pangkat" value="{{ old('pangkat') }}" placeholder="Penata, Pembina, dll">
                                    @error('pangkat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Golongan</label>
                                    <input type="text" class="form-control @error('golongan') is-invalid @enderror"
                                        name="golongan" value="{{ old('golongan') }}"
                                        placeholder="III/a, III/b, IV/a, dll">
                                    @error('golongan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Masuk</label>
                                    <input type="date"
                                        class="form-control @error('tanggal_masuk') is-invalid @enderror"
                                        name="tanggal_masuk" value="{{ old('tanggal_masuk') }}">
                                    @error('tanggal_masuk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Keluar</label>
                                    <input type="date"
                                        class="form-control @error('tanggal_keluar') is-invalid @enderror"
                                        name="tanggal_keluar" value="{{ old('tanggal_keluar') }}">
                                    @error('tanggal_keluar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Atasan Langsung</label>
                                    <select class="form-select @error('atasan_langsung_id') is-invalid @enderror"
                                        name="atasan_langsung_id">
                                        <option value="">Pilih Atasan Langsung</option>
                                        @foreach (App\Models\MasterPegawai::all() as $atasan)
                                            <option value="{{ $atasan->id }}"
                                                {{ old('atasan_langsung_id') == $atasan->id ? 'selected' : '' }}>
                                                {{ $atasan->nama }} - {{ $atasan->jabatan->nama ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('atasan_langsung_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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

        .text-danger {
            color: #dc3545 !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Photo preview
            $('#foto_profile').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#profilePhoto').html(
                            `<img src="${e.target.result}" alt="Preview" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">`
                            );
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Form validation
            $('#formPegawai').submit(function(e) {
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
                if (password !== passwordConfirmation) {
                    $('input[name="password_confirmation"]').addClass('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Mohon lengkapi semua field yang wajib diisi dengan benar.'
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
