@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Edit Periode</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.pk-saya') }}">Perjanjian Kinerja</a>
                </li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.periode.index') }}">Periode</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="card-title mb-1">Edit Periode {{ $periode->nama_periode }}</h5>
                                <p class="text-muted mb-0 small">Perbarui informasi periode</p>
                            </div>
                            <span class="badge bg-{{ $periode->status == 'Aktif' ? 'success' : 'secondary' }} fs-6">
                                {{ $periode->status }}
                            </span>
                        </div>

                        @if ($periode->is_active)
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <div>Periode ini sedang <strong>aktif</strong>. Tutup periode terlebih dahulu untuk
                                    mengedit.</div>
                            </div>
                        @endif

                        <form action="{{ route('perjanjian-kinerja.periode.update', $periode->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <label for="tahun" class="col-sm-3 col-form-label">
                                    Tahun <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('tahun') is-invalid @enderror" id="tahun"
                                        name="tahun" required {{ $periode->is_active ? 'disabled' : '' }}>
                                        <option value="">Pilih Tahun</option>
                                        @for ($year = date('Y') - 1; $year <= date('Y') + 2; $year++)
                                            <option value="{{ $year }}"
                                                {{ old('tahun', $periode->tahun) == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('tahun')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if ($periode->is_active)
                                        <input type="hidden" name="tahun" value="{{ $periode->tahun }}">
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="nama_periode" class="col-sm-3 col-form-label">
                                    Nama Periode <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control @error('nama_periode') is-invalid @enderror"
                                        id="nama_periode" name="nama_periode"
                                        value="{{ old('nama_periode', $periode->nama_periode) }}" required
                                        {{ $periode->is_active ? 'readonly' : '' }}>
                                    @error('nama_periode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tanggal_mulai" class="col-sm-3 col-form-label">
                                    Tanggal Mulai <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                        id="tanggal_mulai" name="tanggal_mulai"
                                        value="{{ old('tanggal_mulai', $periode->tanggal_mulai->format('Y-m-d')) }}"
                                        required {{ $periode->is_active ? 'readonly' : '' }}>
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tanggal_selesai" class="col-sm-3 col-form-label">
                                    Tanggal Selesai <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="date"
                                        class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                        id="tanggal_selesai" name="tanggal_selesai"
                                        value="{{ old('tanggal_selesai', $periode->tanggal_selesai->format('Y-m-d')) }}"
                                        required>
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Tanggal selesai dapat diperbarui meskipun periode
                                        aktif</small>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <label for="deskripsi" class="col-sm-3 col-form-label">
                                    Deskripsi
                                </label>
                                <div class="col-sm-9">
                                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3"
                                        {{ $periode->is_active ? 'readonly' : '' }}>{{ old('deskripsi', $periode->deskripsi) }}</textarea>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            @if ($periode->perjanjian_kinerja_count > 0)
                                <div class="alert alert-info d-flex align-items-start" role="alert">
                                    <i class="bi bi-info-circle me-2" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <strong>Informasi:</strong>
                                        <p class="mb-0 mt-1 small">
                                            Periode ini memiliki <strong>{{ $periode->perjanjian_kinerja_count }}
                                                PK</strong>
                                            yang terkait. Perubahan tertentu dibatasi untuk menjaga konsistensi data.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between mt-4">
                                <div>
                                    @if (!$periode->is_active && $periode->perjanjian_kinerja_count == 0)
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal">
                                            <i class="bi bi-trash me-1"></i>Hapus Periode
                                        </button>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('perjanjian-kinerja.periode.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Timeline Info -->
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Riwayat Periode</h6>
                        <ul class="timeline">
                            <li>
                                <i class="bi bi-plus-circle text-primary"></i>
                                <div>
                                    <strong>Dibuat</strong>
                                    <p class="text-muted small mb-0">{{ $periode->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </li>
                            @if ($periode->dibuka_pada)
                                <li>
                                    <i class="bi bi-unlock text-success"></i>
                                    <div>
                                        <strong>Dibuka oleh {{ $periode->pembuka->nama }}</strong>
                                        <p class="text-muted small mb-0">{{ $periode->dibuka_pada?->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </li>
                            @endif
                            @if ($periode->ditutup_pada)
                                <li>
                                    <i class="bi bi-lock text-danger"></i>
                                    <div>
                                        <strong>Ditutup oleh {{ $periode->penutup->nama }}</strong>
                                        <p class="text-muted small mb-0">
                                            {{ $periode->ditutup_pada?->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Hapus Periode</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus periode <strong>{{ $periode->nama_periode }}</strong>?</p>
                    <p class="text-muted small mb-0">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('perjanjian-kinerja.periode.destroy', $periode->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
                $('#tanggal_selesai').val('{{ $periode->tanggal_selesai->format('Y-m-d') }}');
            }
        });
    </script>
@endpush
