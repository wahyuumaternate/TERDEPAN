@extends('layouts.main')

@php
    $jenisLabel = match ($jenis) {
        'pokok' => 'Tugas Pokok',
        'tambahan' => 'Tugas Tambahan',
        default => 'Semua Penugasan',
    };
    $statusBadgeMap = [
        'pending' => 'bg-secondary',
        'proses' => 'bg-primary',
        'revisi' => 'bg-warning text-dark',
        'terlambat' => 'bg-danger',
        'selesai' => 'bg-success',
        'ditolak' => 'bg-dark',
    ];
@endphp

@section('main')
    <div class="pagetitle">
        <h1>Daftar Penugasan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">{{ $jenisLabel }}</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Stats Cards -->
        <div class="row mb-4">
            @foreach (['total' => ['Total', 'primary'], 'pending' => ['Pending', 'secondary'], 'proses' => ['Proses', 'primary'], 'terlambat' => ['Terlambat', 'danger'], 'menunggu_nilai' => ['Menunggu Nilai', 'info'], 'selesai' => ['Selesai', 'success']] as $key => [$label, $color])
                <div class="col">
                    <div class="card info-card shadow-sm border-0">
                        <div class="card-body text-center py-3">
                            <h5 class="fw-bold mb-0 text-{{ $color }}">{{ $stats[$key] }}</h5>
                            <small class="text-muted">{{ $label }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <div class="icon-box bg-primary bg-opacity-10 rounded-3 p-2 me-3">
                            <i class="bi bi-list-task text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                        {{ $jenisLabel }}
                    </h5>
                </div>

                <!-- Filter Form -->
                <form action="{{ route('penugasan.index') }}" method="GET" class="row g-3 mb-4">
                    <input type="hidden" name="jenis" value="{{ $jenis }}">
                    <div class="col-lg-3">
                        <select class="form-select" name="status" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            @foreach (['pending', 'proses', 'revisi', 'terlambat', 'selesai', 'ditolak'] as $s)
                                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <select class="form-select" name="bidang_id" onchange="this.form.submit()">
                            <option value="">Semua Bidang</option>
                            @foreach ($bidangList as $bidang)
                                <option value="{{ $bidang->id }}"
                                    {{ request('bidang_id') == $bidang->id ? 'selected' : '' }}>{{ $bidang->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                placeholder="Cari nama tugas atau pegawai...">
                            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <a href="{{ route('penugasan.index', ['jenis' => $jenis]) }}"
                            class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                        </a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Tugas</th>
                                <th>Pegawai</th>
                                <th>Bidang</th>
                                <th class="text-center">Jenis</th>
                                <th class="text-center">Bobot</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Deadline</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($penugasan as $tugas)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $tugas->nama_tugas }}</div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $tugas->pegawai->nama }}</div>
                                        <small
                                            class="text-muted">{{ $tugas->pegawai->profile->jabatan->nama ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $tugas->pegawai->profile->bidang->nama ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($tugas->jenis) }}</span>
                                    </td>
                                    <td class="text-center">{{ $tugas->bobot_persen }}%</td>
                                    <td class="text-center">
                                        <span
                                            class="badge {{ $statusBadgeMap[$tugas->status] ?? 'bg-secondary' }}">{{ ucfirst($tugas->status) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <small>{{ $tugas->tanggal_selesai->format('d M Y') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('penugasan.show', $tugas->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2 mb-0">Tidak ada data penugasan</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $penugasan->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </section>

    <style>
        .icon-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
        }
    </style>
@endsection
