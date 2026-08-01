@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Buat Tugas Mandiri</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Buat Tugas Mandiri</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-plus-circle me-2"></i>Form Tugas Mandiri
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Tugas mandiri (self-initiated) akan menunggu persetujuan atasan langsung Anda sebelum dapat
                            dikerjakan.
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('penugasan.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="pegawai_id" value="{{ auth()->id() }}">

                            <div class="mb-3">
                                <label class="form-label">Jenis Tugas <span class="text-danger">*</span></label>
                                <select class="form-select" name="jenis" required>
                                    <option value="pokok" {{ old('jenis') === 'pokok' ? 'selected' : '' }}>Tugas Pokok
                                    </option>
                                    <option value="tambahan" {{ old('jenis', 'tambahan') === 'tambahan' ? 'selected' : '' }}>
                                        Tugas Tambahan</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Tugas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_tugas" value="{{ old('nama_tugas') }}"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="deskripsi" rows="4" required>{{ old('deskripsi') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alasan Penugasan</label>
                                <textarea class="form-control" name="alasan_penugasan" rows="2" placeholder="Opsional - jelaskan mengapa tugas ini perlu dikerjakan">{{ old('alasan_penugasan') }}</textarea>
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

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Target Value</label>
                                    <input type="number" class="form-control" name="target_value" step="0.01"
                                        value="{{ old('target_value') }}" placeholder="Opsional">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Satuan</label>
                                    <input type="text" class="form-control" name="satuan"
                                        value="{{ old('satuan') }}" placeholder="dokumen, kegiatan, dll">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Bobot (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="bobot_persen" min="0" max="100"
                                    step="0.01" value="{{ old('bobot_persen') }}" required>
                                <small class="text-muted">Bobot digunakan untuk menghitung nilai akhir setelah tugas
                                    divalidasi: nilai_akhir = bobot × realisasi / 100</small>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('penugasan.tugas-saya') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i>Ajukan Tugas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
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
@endsection
