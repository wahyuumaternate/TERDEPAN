@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Detail Anggota Tim</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tim.index') }}">Tim Saya</a></li>
                <li class="breadcrumb-item active">{{ $pegawai->nama }}</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-3"
                                    style="width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                    {{ strtoupper(substr($pegawai->nama, 0, 2)) }}
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">{{ $pegawai->nama }}</h5>
                                    <small class="text-muted">{{ $pegawai->profile->jabatan->nama ?? '-' }} •
                                        {{ $pegawai->profile->bidang->nama ?? '-' }}</small>
                                </div>
                            </div>
                            <a href="{{ route('penugasan.tim.form-berikan-tugas') }}?pegawai={{ $pegawai->id }}"
                                class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>Berikan Tugas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title small">Total</h6>
                        <h4 class="fw-bold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title small">Pending</h6>
                        <h4 class="fw-bold mb-0 text-secondary">{{ $stats['pending'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title small">Dikerjakan</h6>
                        <h4 class="fw-bold mb-0 text-primary">{{ $stats['dikerjakan'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title small">Selesai</h6>
                        <h4 class="fw-bold mb-0 text-success">{{ $stats['selesai'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title small">Tugas Pokok</h6>
                        <h4 class="fw-bold mb-0">{{ $stats['pokok'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title small">Tugas Tambahan</h6>
                        <h4 class="fw-bold mb-0">{{ $stats['tambahan'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tahun -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <select class="form-select" name="tahun" onchange="this.form.submit()">
                    @foreach ($tahuns as $t)
                        <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <!-- Daftar Penugasan -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Tugas</th>
                                <th>Jenis</th>
                                <th class="text-center">Bobot</th>
                                <th class="text-center">Progress</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Deadline</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($penugasanList as $tugas)
                                <tr>
                                    <td>{{ $tugas->nama_tugas }}</td>
                                    <td>
                                        <span
                                            class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($tugas->jenis) }}</span>
                                    </td>
                                    <td class="text-center">{{ $tugas->bobot_persen }}%</td>
                                    <td class="text-center">{{ $tugas->progress_persen }}%</td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-{{ $tugas->status === 'selesai' ? 'success' : ($tugas->status === 'revisi' ? 'danger' : 'primary') }}">
                                            {{ ucfirst($tugas->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $tugas->tanggal_selesai->format('d M Y') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('penugasan.show', $tugas->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada penugasan pada tahun
                                        ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $penugasanList->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection
