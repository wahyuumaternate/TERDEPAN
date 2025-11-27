@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Detail Periode</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.pk-saya') }}">Perjanjian Kinerja</a>
                </li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.periode.index') }}">Periode</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <!-- Info Periode -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0">Informasi Periode</h5>
                            <span
                                class="badge bg-{{ $periode->status == 'Aktif' ? 'success' : 'secondary' }} fs-6">{{ $periode->status }}</span>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small">Tahun</label>
                            <h4 class="mb-0">{{ $periode->tahun }}</h4>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small">Nama Periode</label>
                            <h6 class="mb-0">{{ $periode->nama_periode }}</h6>
                        </div>

                        @if ($periode->deskripsi)
                            <div class="mb-3">
                                <label class="text-muted small">Deskripsi</label>
                                <p class="mb-0">{{ $periode->deskripsi }}</p>
                            </div>
                        @endif

                        <hr>

                        <div class="mb-3">
                            <label class="text-muted small d-flex align-items-center">
                                <i class="bi bi-calendar3 me-2"></i>Tanggal Mulai
                            </label>
                            <p class="mb-0 fw-semibold">{{ $periode->tanggal_mulai->format('d F Y') }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small d-flex align-items-center">
                                <i class="bi bi-calendar-check me-2"></i>Tanggal Selesai
                            </label>
                            <p class="mb-0 fw-semibold">{{ $periode->tanggal_selesai->format('d F Y') }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small">Durasi</label>
                            <p class="mb-0">{{ $periode->tanggal_mulai->diffInDays($periode->tanggal_selesai) }} hari</p>
                        </div>

                        @if ($periode->is_active)
                            @if ($periode->isMelewatiDeadline())
                                <div class="alert alert-warning small mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Periode melewati deadline
                                </div>
                            @else
                                <div class="alert alert-success small mb-0">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $periode->tanggal_selesai->diffForHumans() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Riwayat -->
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Riwayat</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3 d-flex align-items-start">
                                <i class="bi bi-plus-circle text-primary me-2" style="font-size: 1.2rem;"></i>
                                <div>
                                    <small class="text-muted">Dibuat</small>
                                    <p class="mb-0 small fw-semibold">{{ $periode->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </li>
                            @if ($periode->pembuka)
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="bi bi-unlock text-success me-2" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <small class="text-muted">Dibuka oleh</small>
                                        <p class="mb-0 small fw-semibold">{{ $periode->pembuka->nama }}</p>
                                        <p class="mb-0 small text-muted">{{ $periode->dibuka_pada?->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </li>
                            @endif
                            @if ($periode->penutup)
                                <li class="mb-0 d-flex align-items-start">
                                    <i class="bi bi-lock text-danger me-2" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <small class="text-muted">Ditutup oleh</small>
                                        <p class="mb-0 small fw-semibold">{{ $periode->penutup->nama }}</p>
                                        <p class="mb-0 small text-muted">{{ $periode->ditutup_pada?->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if ($periode->is_active)
                                <button type="button" class="btn btn-danger btn-tutup-periode"
                                    data-id="{{ $periode->id }}" data-nama="{{ $periode->nama_periode }}">
                                    <i class="bi bi-lock me-1"></i>Tutup Periode
                                </button>
                            @else
                                <button type="button" class="btn btn-success btn-buka-periode"
                                    data-id="{{ $periode->id }}" data-nama="{{ $periode->nama_periode }}">
                                    <i class="bi bi-unlock me-1"></i>Buka Periode
                                </button>
                            @endif

                            <a href="{{ route('perjanjian-kinerja.periode.edit', $periode->id) }}" class="btn btn-warning">
                                <i class="bi bi-pencil me-1"></i>Edit Periode
                            </a>

                            <a href="{{ route('perjanjian-kinerja.periode.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar PK -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-1">Perjanjian Kinerja</h5>
                        <p class="text-muted mb-4 small">Daftar PK yang dibuat pada periode ini</p>

                        <!-- Statistik -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white border-0">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0">{{ $periode->perjanjian_kinerja_count }}</h3>
                                        <small>Total PK</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white border-0">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0">
                                            {{ $periode->perjanjianKinerja->where('status_validasi', 'Menunggu')->count() }}
                                        </h3>
                                        <small>Menunggu</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white border-0">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0">
                                            {{ $periode->perjanjianKinerja->where('status_validasi', 'Disetujui')->count() }}
                                        </h3>
                                        <small>Disetujui</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white border-0">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0">
                                            {{ $periode->perjanjianKinerja->where('status_validasi', 'Ditolak')->count() }}
                                        </h3>
                                        <small>Ditolak</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        @if ($periode->perjanjianKinerja->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="25%">Pegawai</th>
                                            <th width="20%">Jabatan</th>
                                            <th width="15%">Tanggal Buat</th>
                                            <th width="15%" class="text-center">Status</th>
                                            <th width="10%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($periode->perjanjianKinerja as $index => $pk)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $pk->pegawai->nama }}</strong>
                                                    <br><small class="text-muted">NIP:
                                                        {{ $pk->pegawai->nip }}</small>
                                                </td>
                                                <td>
                                                    <small>{{ $pk->pegawai->jabatan->nama_jabatan ?? '-' }}</small>
                                                </td>
                                                <td>
                                                    <small>{{ $pk->created_at->format('d M Y') }}</small>
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $statusClass = match ($pk->status_validasi) {
                                                            'Menunggu' => 'bg-warning',
                                                            'Disetujui' => 'bg-success',
                                                            'Ditolak' => 'bg-danger',
                                                            'Revisi' => 'bg-info',
                                                            default => 'bg-secondary',
                                                        };
                                                    @endphp
                                                    <span
                                                        class="badge {{ $statusClass }}">{{ $pk->status_validasi }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('perjanjian-kinerja.show', $pk->id) }}"
                                                        class="btn btn-sm btn-info" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-3">Belum ada PK pada periode ini</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // Buka Periode
        $(document).on('click', '.btn-buka-periode', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');

            Swal.fire({
                title: 'Buka Periode?',
                html: `Apakah Anda yakin ingin membuka periode <strong>${nama}</strong>?<br><small class="text-muted">Pegawai akan dapat membuat PK baru</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Buka',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/perjanjian-kinerja/periode/${id}/buka`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    });
                }
            });
        });

        // Tutup Periode
        $(document).on('click', '.btn-tutup-periode', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');

            Swal.fire({
                title: 'Tutup Periode?',
                html: `Apakah Anda yakin ingin menutup periode <strong>${nama}</strong>?<br><small class="text-muted">Pegawai tidak akan dapat membuat PK baru</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tutup',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/perjanjian-kinerja/periode/${id}/tutup`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush
