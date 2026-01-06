@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Perjanjian Kinerja Saya</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">PK Saya</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Informasi Periode -->
        @if(isset($periodeAktif))
            <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-calendar-check fs-3 text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="alert-heading mb-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            Periode Pengisian PK Sedang Aktif
                        </h5>
                        <div class="d-flex flex-wrap gap-3 small">
                            <div>
                                <i class="bi bi-calendar-event me-1"></i>
                                <strong>Mulai:</strong> {{ \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->format('d M Y') }}
                            </div>
                            <div>
                                <i class="bi bi-calendar-x me-1"></i>
                                <strong>Selesai:</strong> {{ \Carbon\Carbon::parse($periodeAktif->tanggal_selesai)->format('d M Y') }}
                            </div>
                        </div>
                        @if($periodeAktif->keterangan)
                            <div class="mt-2 small text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                {{ $periodeAktif->keterangan }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-exclamation-triangle fs-3 text-warning"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="alert-heading mb-1">
                            <i class="bi bi-x-circle-fill text-warning"></i>
                            Periode Pengisian PK Belum Aktif
                        </h5>
                        <p class="mb-0">Akan diinformasikan jika periode pengisian PK telah di buka</p>
                    </div> 
                </div>
            </div>
        @endif

        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Total PK <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10">
                                <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-2">{{ $stats['total'] }}</h6>
                                <span class="text-muted small">dokumen</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Draft <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-secondary bg-opacity-10">
                                <i class="bi bi-pencil-square text-secondary fs-3"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-2">{{ $stats['draft'] }}</h6>
                                <span class="text-muted small">dokumen</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Aktif <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10">
                                <i class="bi bi-check-circle text-success fs-3"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-2">{{ $stats['aktif'] }}</h6>
                                <span class="text-muted small">dokumen</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Selesai <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10">
                                <i class="bi bi-award text-info fs-3"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold fs-2">{{ $stats['selesai'] }}</h6>
                                <span class="text-muted small">dokumen</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="card-title mb-1 d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 rounded-3 p-2 me-3">
                                        <i class="bi bi-file-earmark-text-fill text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Perjanjian Kinerja Saya</span>
                                        <p class="text-muted mb-0 small">Kelola dan monitor perjanjian kinerja Anda</p>
                                    </div>
                                </h5>
                            </div>
                            @php
                                $periodeAktif = \Modules\PerjanjianKinerja\Models\PkPeriode::getPeriodeAktif($tahun);
                                $kodeJabatan = auth()->user()->jabatan?->kode;
                                $canCreateForOthers = in_array($kodeJabatan, ['KABAN', 'SEKBAN', 'KABID']);

                                // Cek apakah user sudah punya PK di periode aktif
                                $hasPkInActivePeriod = false;
                                if ($periodeAktif) {
                                    $hasPkInActivePeriod = \Modules\PerjanjianKinerja\Models\PkPerjanjianKinerja::where(
                                        'pegawai_id',
                                        auth()->id(),
                                    )
                                        ->where('periode_id', $periodeAktif->id)
                                        ->exists();
                                }
                            @endphp
                            @if ($periodeAktif)
                                <div class="d-flex gap-2">
                                    @if (!$hasPkInActivePeriod)
                                        <a href="{{ route('perjanjian-kinerja.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i>Buat PK Saya
                                        </a>
                                    @endif
                                    @if ($canCreateForOthers)
                                        <a href="{{ route('perjanjian-kinerja.create') }}?for_others=1"
                                            class="btn btn-success">
                                            <i class="bi bi-people me-1"></i>Buat PK Bawahan
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-warning mb-0 py-2 px-3">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    <small>Tidak ada periode aktif</small>
                                </div>
                            @endif
                        </div>

                        <!-- Filter Section -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <form method="GET" action="{{ route('perjanjian-kinerja.pk-saya') }}" id="filterForm">
                                    <div class="row g-3">
                                        <!-- Tahun Filter -->
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Tahun</label>
                                            <select class="form-select" name="tahun" onchange="this.form.submit()">
                                                @foreach ($tahuns as $t)
                                                    <option value="{{ $t }}"
                                                        {{ $tahun == $t ? 'selected' : '' }}>
                                                        {{ $t }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Status Filter -->
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Status</label>
                                            <select class="form-select" name="status" onchange="this.form.submit()">
                                                <option value="">Semua Status</option>
                                                <option value="Draft"
                                                    {{ request('status') == 'Draft' ? 'selected' : '' }}>
                                                    Draft
                                                </option>
                                                <option value="Aktif"
                                                    {{ request('status') == 'Aktif' ? 'selected' : '' }}>
                                                    Aktif
                                                </option>
                                                <option value="Selesai"
                                                    {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Items per page -->
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Tampilkan</label>
                                            <select class="form-select" name="per_page" onchange="this.form.submit()">
                                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10
                                                </option>
                                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25
                                                </option>
                                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Reset Button -->
                                    <div class="mt-3">
                                        <a href="{{ route('perjanjian-kinerja.pk-saya') }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Filter
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
                                        <th width="15%">Nomor Perjanjian</th>
                                        <th width="10%">Tahun</th>
                                        <th width="15%">Template</th>
                                        <th width="15%">Atasan Langsung</th>
                                        <th width="10%" class="text-center">Status</th>
                                        <th width="15%" class="text-center">Dokumen</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($perjanjians as $index => $pk)
                                        <tr>
                                            <td>{{ $perjanjians->firstItem() + $index }}</td>
                                            <td>
                                                <strong>{{ $pk->nomor_perjanjian ?? 'Belum ada' }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $pk->created_at->format('d M Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $pk->tahun }}</span>
                                            </td>
                                            <td>
                                                @if ($pk->template)
                                                    {{ $pk->template->nama_template }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($pk->atasan)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle bg-secondary text-white me-2"
                                                            style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">
                                                            {{ strtoupper(substr($pk->atasan->nama, 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">{{ $pk->atasan->nama }}</div>
                                                            <small
                                                                class="text-muted">{{ $pk->atasan->jabatan->nama_jabatan ?? '' }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $statusClass = match ($pk->status_dokumen) {
                                                        'Draft' => 'bg-secondary',
                                                        'Aktif' => 'bg-success',
                                                        'Selesai' => 'bg-info',
                                                        default => 'bg-secondary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">
                                                    {{ $pk->status_dokumen }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if ($pk->dokumen->isNotEmpty())
                                                    @php
                                                        $dokumen = $pk->dokumen->first();
                                                    @endphp
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <a href="{{ route('perjanjian-kinerja.preview', $pk->id) }}"
                                                            class="btn btn-sm btn-outline-info" title="Preview PDF"
                                                            target="_blank">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('perjanjian-kinerja.download', $pk->id) }}"
                                                            class="btn btn-sm btn-outline-primary" title="Download PDF">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    </div>
                                                    <small class="text-muted d-block mt-1">
                                                        {{ $dokumen->created_at ? \Carbon\Carbon::parse($dokumen->created_at)->format('d M Y') : '-' }}
                                                    </small>
                                                @else
                                                    <span class="badge bg-warning text-dark">Belum ada dokumen</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="{{ route('perjanjian-kinerja.show', $pk->id) }}"
                                                        class="btn btn-sm btn-info" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                                    <p class="mt-3">Belum ada perjanjian kinerja untuk tahun
                                                        {{ $tahun }}
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if ($perjanjians->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted small">
                                    Menampilkan {{ $perjanjians->firstItem() }} - {{ $perjanjians->lastItem() }} dari
                                    {{ $perjanjians->total() }} data
                                </div>
                                <div>
                                    {{ $perjanjians->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($perjanjians->isEmpty() && $stats['total'] == 0)
            <!-- Empty State with Action -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-info-circle text-primary" style="font-size: 4rem;"></i>
                            <h5 class="mt-3">Belum Ada Perjanjian Kinerja</h5>
                            <p class="text-muted mb-4">
                                Anda belum memiliki perjanjian kinerja. Silakan hubungi atasan Anda untuk membuat
                                perjanjian kinerja.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>
@endsection

@push('styles')
    <style>
        .info-card {
            transition: transform 0.2s;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .card-icon {
            width: 64px;
            height: 64px;
            font-size: 32px;
        }

        .avatar-circle {
            flex-shrink: 0;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
    </style>
@endpush
