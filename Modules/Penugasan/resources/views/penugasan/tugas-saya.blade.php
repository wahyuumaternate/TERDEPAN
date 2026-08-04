@extends('layouts.main')

@php
    $prioritasBadgeMap = [
        'rendah' => 'bg-secondary',
        'sedang' => 'bg-info text-dark',
        'tinggi' => 'bg-danger',
    ];

    /**
     * Warna badge status semantik — dok. 08 §5.3. "Selesai" punya dua varian
     * tergantung apakah realisasi_persen sudah diisi atasan (sudah dinilai) atau belum.
     */
    $warnaStatus = function (string $status, ?string $realisasi) {
        return match ($status) {
            'pending' => 'bg-secondary',
            'proses' => 'bg-primary',
            'revisi' => 'bg-warning text-dark',
            'terlambat' => 'bg-danger',
            'selesai' => $realisasi === null ? 'bg-info text-dark' : 'bg-success',
            'ditolak' => 'bg-dark',
            default => 'bg-secondary',
        };
    };

    $labelStatus = fn (string $status, ?string $realisasi) => $status === 'selesai' && $realisasi === null
        ? 'Menunggu Nilai'
        : ucfirst($status);
@endphp

@section('main')
    <div class="pagetitle d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h1>{{ $tab === 'diberikan' ? 'Tugas yang Saya Berikan' : 'Tugas Saya' }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $tab === 'diberikan' ? 'Tugas yang Saya Berikan' : 'Tugas Saya' }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('penugasan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Buat Tugas
        </a>
    </div>

    <section class="section">
        <!-- Tab -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'saya' ? 'active' : '' }}"
                    href="{{ route('penugasan.tugas-saya', ['tab' => 'saya']) }}">
                    <i class="bi bi-person-check me-1"></i>Tugas Saya
                </a>
            </li>
            @if ($bisaMemberi)
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'diberikan' ? 'active' : '' }}"
                        href="{{ route('penugasan.tugas-saya', ['tab' => 'diberikan']) }}">
                        <i class="bi bi-send me-1"></i>Tugas yang Saya Berikan
                        @if ($tab === 'diberikan' && $perpanjanganMenunggu->isNotEmpty())
                            <span class="badge bg-danger ms-1">{{ $perpanjanganMenunggu->count() }}</span>
                        @endif
                    </a>
                </li>
            @endif
        </ul>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($tab === 'diberikan' && $perpanjanganMenunggu->isNotEmpty())
            <div class="alert alert-warning" role="alert">
                <h6 class="alert-heading"><i class="bi bi-hourglass-split me-2"></i>Menunggu Keputusan Anda</h6>
                <p class="mb-2 small">{{ $perpanjanganMenunggu->count() }} pengajuan perpanjangan waktu menunggu persetujuan:</p>
                <ul class="mb-0 small">
                    @foreach ($perpanjanganMenunggu as $pengajuan)
                        <li>
                            <a href="{{ route('penugasan.show', $pengajuan->penugasan_id) }}">
                                {{ $pengajuan->penugasan->nama_tugas ?? '-' }}
                            </a>
                            — {{ $pengajuan->penugasan->pegawai->nama ?? '-' }}
                            (minta sampai {{ optional($pengajuan->deadline_diminta)->format('d M Y') }})
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Filter -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="col-md-3">
                        <label class="form-label small">Status</label>
                        <select class="form-select" name="status" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            @foreach ($statusOptions as $s)
                                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Prioritas</label>
                        <select class="form-select" name="prioritas" onchange="this.form.submit()">
                            <option value="">Semua Prioritas</option>
                            @foreach ($prioritasOptions as $p)
                                <option value="{{ $p }}" {{ request('prioritas') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Jenis</label>
                        <select class="form-select" name="jenis" onchange="this.form.submit()">
                            <option value="">Semua Jenis</option>
                            <option value="pokok" {{ request('jenis') === 'pokok' ? 'selected' : '' }}>Tugas Pokok</option>
                            <option value="tambahan" {{ request('jenis') === 'tambahan' ? 'selected' : '' }}>Tugas Tambahan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('penugasan.tugas-saya', ['tab' => $tab]) }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Grid Kartu Tugas -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
            @forelse ($penugasan as $tugas)
                @php
                    $antrianPersetujuan = $tab === 'diberikan' && $tugas->is_mandiri && $tugas->status === 'pending';
                @endphp
                <div class="col">
                    <a href="{{ route('penugasan.show', $tugas->id) }}"
                        class="card shadow-sm h-100 text-decoration-none text-reset {{ $antrianPersetujuan ? 'border-start border-4 border-warning' : 'border-0' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <span class="badge bg-info bg-opacity-10 text-info">{{ $tugas->jenis === 'pokok' ? 'Tugas Pokok' : 'Tugas Tambahan' }}</span>
                                <span class="badge {{ $warnaStatus($tugas->status, $tugas->realisasi_persen) }}">
                                    {{ $labelStatus($tugas->status, $tugas->realisasi_persen) }}
                                </span>
                            </div>

                            @if ($antrianPersetujuan)
                                <div class="small text-warning-emphasis fw-semibold mb-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>Menunggu Keputusan Anda
                                </div>
                            @endif

                            <h6 class="fw-bold mb-1">{{ Str::limit($tugas->nama_tugas, 60) }}</h6>

                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <span class="badge {{ $prioritasBadgeMap[$tugas->prioritas] ?? 'bg-secondary' }}">
                                    <i class="bi bi-flag me-1"></i>{{ ucfirst($tugas->prioritas) }}
                                </span>
                                @if ($tugas->is_mandiri)
                                    <span class="badge bg-secondary bg-opacity-25 text-body"><i class="bi bi-person-check me-1"></i>Mandiri</span>
                                @endif
                                @if ($tugas->mode_grup === 'kolektif')
                                    <span class="badge bg-secondary bg-opacity-25 text-body">
                                        <i class="bi bi-people me-1"></i>Kolektif{{ $tugas->is_koordinator ? ' (Koordinator)' : '' }}
                                    </span>
                                @elseif ($tugas->mode_grup === 'per_orang')
                                    <span class="badge bg-secondary bg-opacity-25 text-body"><i class="bi bi-people me-1"></i>Per Orang</span>
                                @endif
                            </div>

                            <div class="small text-muted mb-2">
                                @if ($tab === 'diberikan')
                                    <i class="bi bi-person me-1"></i>{{ $tugas->pegawai->nama ?? '-' }}
                                @else
                                    <i class="bi bi-person-badge me-1"></i>{{ $tugas->pemberiTugas->nama ?? 'Mandiri' }}
                                @endif
                            </div>

                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $tugas->progress_persen }}%"
                                    aria-valuenow="{{ $tugas->progress_persen }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <div class="small text-muted">
                                <i class="bi bi-calendar-event me-1"></i>
                                {{ optional($tugas->deadline_terbaru)->format('d M Y') }}
                                @if ($tugas->deadline_terbaru && $tugas->tanggal_selesai && ! $tugas->deadline_terbaru->isSameDay($tugas->tanggal_selesai))
                                    <span class="badge bg-warning bg-opacity-25 text-warning-emphasis">Diperbarui dari {{ $tugas->tanggal_selesai->format('d M Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">Tidak ada penugasan</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $penugasan->links() }}
        </div>
    </section>
@endsection
