@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Berikan Tugas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tim.index') }}">Tim Saya</a></li>
                <li class="breadcrumb-item active">Berikan Tugas</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <!-- Form Section -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Form Berikan Tugas</h5>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Error!</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('penugasan.tim.berikan-tugas') }}" method="POST" id="formBerikanTugas">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Jenis Tugas <span class="text-danger">*</span></label>
                                    <select class="form-select" name="jenis" id="jenisSelect" required>
                                        <option value="pokok" {{ old('jenis') === 'pokok' ? 'selected' : '' }}>Tugas
                                            Pokok</option>
                                        <option value="tambahan"
                                            {{ old('jenis', 'tambahan') === 'tambahan' ? 'selected' : '' }}>Tugas
                                            Tambahan</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pegawai <span class="text-danger">*</span></label>
                                    @php $jenisDefault = old('jenis', 'tambahan'); @endphp
                                    <select class="form-select pegawai-select {{ $jenisDefault !== 'pokok' ? 'd-none' : '' }}"
                                        name="pegawai_id" id="pegawaiPokok"
                                        {{ $jenisDefault !== 'pokok' ? 'disabled' : 'required' }}>
                                        <option value="">Pilih Pegawai</option>
                                        @foreach ($bawahanLangsung as $pegawai)
                                            <option value="{{ $pegawai->id }}"
                                                {{ old('pegawai_id') == $pegawai->id ? 'selected' : '' }}>
                                                {{ $pegawai->nama }} - {{ $pegawai->profile->jabatan->nama ?? '' }}
                                                @if ($pegawai->profile->bidang)
                                                    ({{ $pegawai->profile->bidang->nama }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <select class="form-select pegawai-select {{ $jenisDefault === 'pokok' ? 'd-none' : '' }}"
                                        name="pegawai_id" id="pegawaiTambahan"
                                        {{ $jenisDefault === 'pokok' ? 'disabled' : 'required' }}>
                                        <option value="">Pilih Pegawai</option>
                                        @foreach ($pegawaiTugasTambahan as $pegawai)
                                            <option value="{{ $pegawai->id }}"
                                                {{ old('pegawai_id') == $pegawai->id ? 'selected' : '' }}>
                                                {{ $pegawai->nama }} - {{ $pegawai->profile->jabatan->nama ?? '' }}
                                                @if ($pegawai->profile->bidang)
                                                    ({{ $pegawai->profile->bidang->nama }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Tugas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_tugas"
                                    value="{{ old('nama_tugas') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="deskripsi" rows="3" required>{{ old('deskripsi') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alasan Penugasan</label>
                                <textarea class="form-control" name="alasan_penugasan" rows="2"
                                    placeholder="Opsional - jelaskan mengapa pegawai ini yang ditugaskan">{{ old('alasan_penugasan') }}</textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="tanggal_mulai"
                                        value="{{ old('tanggal_mulai', now()->toDateString()) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="tanggal_selesai"
                                        value="{{ old('tanggal_selesai') }}" required>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Target Value</label>
                                    <input type="number" class="form-control" name="target_value" step="0.01"
                                        value="{{ old('target_value') }}" placeholder="Opsional">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Satuan</label>
                                    <input type="text" class="form-control" name="satuan"
                                        value="{{ old('satuan') }}" placeholder="dokumen, kegiatan, dll">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Bobot (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="bobot_persen" min="0"
                                        max="100" step="0.01" value="{{ old('bobot_persen') }}" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('penugasan.tim.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i>Berikan Tugas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Tugas Baru Diberikan</h6>
                    </div>
                    <div class="card-body">
                        @if ($recentAssignments->count() > 0)
                            @foreach ($recentAssignments as $assignment)
                                <div class="d-flex mb-3">
                                    <i class="bi bi-circle-fill text-primary me-2" style="font-size: 8px; margin-top: 6px;"></i>
                                    <div>
                                        <h6 class="mb-1">{{ Str::limit($assignment->nama_tugas, 40) }}</h6>
                                        <p class="text-muted small mb-1">Untuk:
                                            <strong>{{ $assignment->pegawai->nama ?? 'N/A' }}</strong>
                                        </p>
                                        <small class="text-muted"><i
                                                class="bi bi-clock me-1"></i>{{ $assignment->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center mb-0">Belum ada tugas yang diberikan</p>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Statistik Tim</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Bawahan</span>
                            <strong>{{ $bawahanLangsung->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tugas Aktif</span>
                            <strong class="text-primary">{{ $stats['tugas_aktif'] ?? 0 }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Menunggu Validasi</span>
                            <strong class="text-warning">{{ $stats['menunggu_validasi'] ?? 0 }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}'
            });
        @endif

        // Toggle pegawai select based on jenis
        const jenisSelect = document.getElementById('jenisSelect');
        const pegawaiPokok = document.getElementById('pegawaiPokok');
        const pegawaiTambahan = document.getElementById('pegawaiTambahan');

        jenisSelect.addEventListener('change', function() {
            if (this.value === 'pokok') {
                pegawaiPokok.classList.remove('d-none');
                pegawaiPokok.disabled = false;
                pegawaiPokok.required = true;
                pegawaiTambahan.classList.add('d-none');
                pegawaiTambahan.disabled = true;
                pegawaiTambahan.required = false;
            } else {
                pegawaiTambahan.classList.remove('d-none');
                pegawaiTambahan.disabled = false;
                pegawaiTambahan.required = true;
                pegawaiPokok.classList.add('d-none');
                pegawaiPokok.disabled = true;
                pegawaiPokok.required = false;
            }
        });

        // Auto-select pegawai from query string (?pegawai=)
        const params = new URLSearchParams(window.location.search);
        const pegawaiParam = params.get('pegawai');
        if (pegawaiParam) {
            document.querySelectorAll('.pegawai-select').forEach(select => {
                if ([...select.options].some(opt => opt.value === pegawaiParam)) {
                    select.value = pegawaiParam;
                }
            });
        }

        document.querySelectorAll('input[name="tanggal_selesai"]').forEach(input => {
            input.addEventListener('change', function() {
                const mulai = this.closest('form').querySelector('input[name="tanggal_mulai"]').value;
                if (mulai && this.value < mulai) {
                    Swal.fire('Perhatian', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai', 'warning');
                    this.value = '';
                }
            });
        });
    </script>
@endpush
