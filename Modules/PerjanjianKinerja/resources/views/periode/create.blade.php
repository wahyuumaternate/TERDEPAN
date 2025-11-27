@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Buat Periode Baru</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.pk-saya') }}">Perjanjian Kinerja</a>
                </li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.periode.index') }}">Periode</a></li>
                <li class="breadcrumb-item active">Buat Baru</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-1">Form Periode Perjanjian Kinerja</h5>
                        <p class="text-muted mb-4 small">Isi informasi periode dengan lengkap</p>

                        <form action="{{ route('perjanjian-kinerja.periode.store') }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <label for="tahun" class="col-sm-3 col-form-label">
                                    Tahun <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('tahun') is-invalid @enderror" id="tahun"
                                        name="tahun" required>
                                        <option value="">Pilih Tahun</option>
                                        @for ($year = date('Y') - 1; $year <= date('Y') + 2; $year++)
                                            <option value="{{ $year }}"
                                                {{ old('tahun') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('tahun')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Tahun periode perjanjian kinerja</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="nama_periode" class="col-sm-3 col-form-label">
                                    Nama Periode <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control @error('nama_periode') is-invalid @enderror"
                                        id="nama_periode" name="nama_periode" value="{{ old('nama_periode') }}" required
                                        placeholder="Contoh: Periode I Tahun 2025">
                                    @error('nama_periode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Nama unik untuk periode ini</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tanggal_mulai" class="col-sm-3 col-form-label">
                                    Tanggal Mulai <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                        id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                                        required>
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Tanggal periode mulai dibuka</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tanggal_selesai" class="col-sm-3 col-form-label">
                                    Tanggal Selesai <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="date"
                                        class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                        id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                                        required>
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Tanggal deadline pengisian PK</small>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <label for="deskripsi" class="col-sm-3 col-form-label">
                                    Deskripsi
                                </label>
                                <div class="col-sm-9">
                                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3"
                                        placeholder="Keterangan tambahan (opsional)">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="alert alert-info d-flex align-items-start" role="alert">
                                <i class="bi bi-info-circle me-2" style="font-size: 1.5rem;"></i>
                                <div>
                                    <strong>Catatan Penting:</strong>
                                    <ul class="mb-0 mt-1 small">
                                        <li>Periode tidak akan langsung aktif setelah dibuat</li>
                                        <li>Gunakan tombol <strong>"Buka Periode"</strong> untuk mengaktifkan</li>
                                        <li>Hanya 1 periode yang dapat aktif per tahun</li>
                                        <li>Periode yang sudah aktif tidak dapat diedit</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('perjanjian-kinerja.periode.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i>Simpan Periode
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // Validasi tanggal
        $('#tanggal_mulai, #tanggal_selesai').on('change', function() {
            const mulai = $('#tanggal_mulai').val();
            const selesai = $('#tanggal_selesai').val();

            if (mulai && selesai && mulai > selesai) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Tanggal selesai harus lebih besar dari tanggal mulai'
                });
                $('#tanggal_selesai').val('');
            }
        });

        // Auto-generate nama periode
        $('#tahun').on('change', function() {
            const tahun = $(this).val();
            if (tahun && !$('#nama_periode').val()) {
                $('#nama_periode').val(`Periode ${tahun}`);
            }
        });
    </script>
@endpush
