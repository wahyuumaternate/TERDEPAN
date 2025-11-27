@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Kelola Periode PK</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.pk-saya') }}">Perjanjian Kinerja</a>
                </li>
                <li class="breadcrumb-item active">Periode</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Info Periode Aktif -->
        @if ($periodeAktif)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Periode Aktif:</strong> {{ $periodeAktif->nama_periode }} ({{ $periodeAktif->tahun }})
                <br>
                <small>
                    {{ $periodeAktif->tanggal_mulai->format('d M Y') }} -
                    {{ $periodeAktif->tanggal_selesai->format('d M Y') }}
                    @if ($periodeAktif->isMelewatiDeadline())
                        <span class="badge bg-warning text-dark ms-2">Melewati Deadline</span>
                    @else
                        <span class="badge bg-success ms-2">{{ $periodeAktif->tanggal_selesai->diffForHumans() }}</span>
                    @endif
                </small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @else
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Tidak Ada Periode Aktif</strong>
                <br>
                <small>Silakan buat dan aktifkan periode untuk membuka pengisian PK</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="card-title mb-1">Daftar Periode</h5>
                                <p class="text-muted mb-0 small">Kelola periode pengisian perjanjian kinerja</p>
                            </div>
                            <a href="{{ route('perjanjian-kinerja.periode.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Buat Periode Baru
                            </a>
                        </div>

                        <!-- Filter -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <form method="GET" action="{{ route('perjanjian-kinerja.periode.index') }}">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Tahun</label>
                                            <select class="form-select" name="tahun" onchange="this.form.submit()">
                                                <option value="">Semua Tahun</option>
                                                @foreach ($tahuns as $t)
                                                    <option value="{{ $t }}"
                                                        {{ request('tahun') == $t ? 'selected' : '' }}>
                                                        {{ $t }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Status</label>
                                            <select class="form-select" name="status" onchange="this.form.submit()">
                                                <option value="">Semua Status</option>
                                                <option value="Aktif"
                                                    {{ request('status') == 'Aktif' ? 'selected' : '' }}>
                                                    Aktif</option>
                                                <option value="Selesai"
                                                    {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                                <option value="Ditutup"
                                                    {{ request('status') == 'Ditutup' ? 'selected' : '' }}>Ditutup</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Tampilkan</label>
                                            <select class="form-select" name="per_page" onchange="this.form.submit()">
                                                <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15
                                                </option>
                                                <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30
                                                </option>
                                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ route('perjanjian-kinerja.periode.index') }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="10%">Tahun</th>
                                        <th width="20%">Nama Periode</th>
                                        <th width="20%">Tanggal</th>
                                        <th width="10%" class="text-center">Status</th>
                                        <th width="10%" class="text-center">Jumlah PK</th>
                                        <th width="15%">Dibuka/Ditutup Oleh</th>
                                        <th width="10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($periodes as $index => $periode)
                                        <tr>
                                            <td>{{ $periodes->firstItem() + $index }}</td>
                                            <td>
                                                <span class="badge bg-primary fs-6">{{ $periode->tahun }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $periode->nama_periode }}</strong>
                                                @if ($periode->deskripsi)
                                                    <br><small
                                                        class="text-muted">{{ Str::limit($periode->deskripsi, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="bi bi-calendar3"></i>
                                                    {{ $periode->tanggal_mulai->format('d M Y') }}
                                                    <br>
                                                    <i class="bi bi-calendar-check"></i>
                                                    {{ $periode->tanggal_selesai->format('d M Y') }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $statusClass = match ($periode->status) {
                                                        'Aktif' => 'bg-success',
                                                        'Selesai' => 'bg-info',
                                                        'Ditutup' => 'bg-secondary',
                                                        default => 'bg-secondary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">
                                                    @if ($periode->is_active)
                                                        <i class="bi bi-circle-fill"
                                                            style="font-size: 6px; vertical-align: middle;"></i>
                                                    @endif
                                                    {{ $periode->status }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-primary rounded-pill">{{ $periode->perjanjian_kinerja_count }}</span>
                                            </td>
                                            <td>
                                                @if ($periode->pembuka)
                                                    <small class="text-success">
                                                        <i class="bi bi-unlock"></i>
                                                        {{ $periode->pembuka->nama }}
                                                        <br>{{ $periode->dibuka_pada?->format('d/m/Y H:i') }}
                                                    </small>
                                                @endif
                                                @if ($periode->penutup)
                                                    <br><small class="text-danger">
                                                        <i class="bi bi-lock"></i>
                                                        {{ $periode->penutup->nama }}
                                                        <br>{{ $periode->ditutup_pada?->format('d/m/Y H:i') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('perjanjian-kinerja.periode.show', $periode->id) }}"
                                                        class="btn btn-info" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    @if (!$periode->is_active)
                                                        <a href="{{ route('perjanjian-kinerja.periode.edit', $periode->id) }}"
                                                            class="btn btn-warning" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endif

                                                    @if ($periode->is_active)
                                                        <button type="button" class="btn btn-danger btn-tutup-periode"
                                                            data-id="{{ $periode->id }}"
                                                            data-nama="{{ $periode->nama_periode }}"
                                                            title="Tutup Periode">
                                                            <i class="bi bi-lock"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-success btn-buka-periode"
                                                            data-id="{{ $periode->id }}"
                                                            data-nama="{{ $periode->nama_periode }}"
                                                            title="Buka Periode">
                                                            <i class="bi bi-unlock"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                                <p class="text-muted mt-3">Belum ada periode</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if ($periodes->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted small">
                                    Menampilkan {{ $periodes->firstItem() }} - {{ $periodes->lastItem() }} dari
                                    {{ $periodes->total() }} data
                                </div>
                                <div>
                                    {{ $periodes->links() }}
                                </div>
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
