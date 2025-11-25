@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Daftar Tugas Pokok</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('penugasan.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Tugas Pokok</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check me-2"></i>
                            Manajemen Tugas Pokok
                        </h5>
                    </div>
                    <div class="col-md-6 text-end">
                        @can('create', App\Models\TugasPokok::class)
                            <a href="{{ route('penugasan.tugas-pokok.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Tugas Pokok
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label small">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="dikerjakan">Dikerjakan</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Pegawai</label>
                        <select class="form-select" id="filterPegawai">
                            <option value="">Semua Pegawai</option>
                            @foreach ($pegawaiList as $pegawai)
                                <option value="{{ $pegawai->id }}">{{ $pegawai->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Progress</label>
                        <select class="form-select" id="filterProgress">
                            <option value="">Semua</option>
                            <option value="0-25">0-25%</option>
                            <option value="26-50">26-50%</option>
                            <option value="51-75">51-75%</option>
                            <option value="76-100">76-100%</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Cari</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari nama tugas...">
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-start border-primary border-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                                        <small class="text-muted">Total Tugas</small>
                                    </div>
                                    <div class="text-primary">
                                        <i class="bi bi-clipboard-check fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-start border-warning border-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                    <div class="text-warning">
                                        <i class="bi bi-clock fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-start border-info border-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 class="mb-0">{{ $stats['dikerjakan'] }}</h3>
                                        <small class="text-muted">Dikerjakan</small>
                                    </div>
                                    <div class="text-info">
                                        <i class="bi bi-play-circle fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-start border-success border-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 class="mb-0">{{ $stats['selesai'] }}</h3>
                                        <small class="text-muted">Selesai</small>
                                    </div>
                                    <div class="text-success">
                                        <i class="bi bi-check-all fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tugasPokokTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 30%">Nama Tugas</th>
                                <th>Pegawai</th>
                                <th>Periode</th>
                                <th class="text-center">Progress</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Target</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tugasPokok as $index => $tugas)
                                <tr>
                                    <td>{{ $tugasPokok->firstItem() + $index }}</td>
                                    <td>
                                        <a href="{{ route('penugasan.tugas-pokok.show', $tugas->id) }}"
                                            class="text-decoration-none fw-semibold text-dark">
                                            {{ $tugas->nama_tugas }}
                                        </a>
                                        @if ($tugas->deskripsi)
                                            <br><small class="text-muted">{{ Str::limit($tugas->deskripsi, 50) }}</small>
                                        @endif
                                        @if ($tugas->tugasHarian->count() > 0)
                                            <br><span class="badge bg-secondary">
                                                <i class="bi bi-list-task"></i> {{ $tugas->tugasHarian->count() }}
                                                breakdown
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $tugas->pegawai->nama ?? 'N/A' }}
                                        @if ($tugas->pegawai && $tugas->pegawai->masterJabatan)
                                            <br><small
                                                class="text-muted">{{ $tugas->pegawai->masterJabatan->nama_jabatan }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            {{ \Carbon\Carbon::parse($tugas->tanggal_mulai)->format('d M Y') }}
                                            <br>s/d<br>
                                            {{ \Carbon\Carbon::parse($tugas->tanggal_selesai)->format('d M Y') }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $progress = $tugas->progress_persen ?? 0;
                                            $progressClass =
                                                $progress >= 75
                                                    ? 'success'
                                                    : ($progress >= 50
                                                        ? 'info'
                                                        : ($progress >= 25
                                                            ? 'warning'
                                                            : 'danger'));
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $progressClass }}" role="progressbar"
                                                style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <small>{{ number_format($progress, 0) }}%</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusConfig = [
                                                'pending' => ['class' => 'warning', 'icon' => 'clock'],
                                                'dikerjakan' => ['class' => 'info', 'icon' => 'play-circle'],
                                                'selesai' => ['class' => 'success', 'icon' => 'check-all'],
                                            ];
                                            $config = $statusConfig[$tugas->status] ?? [
                                                'class' => 'secondary',
                                                'icon' => 'circle',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $config['class'] }}">
                                            <i class="bi bi-{{ $config['icon'] }}"></i>
                                            {{ ucfirst($tugas->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ $tugas->target_value }}</strong>
                                        <br><small class="text-muted">{{ $tugas->satuan }}</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('penugasan.tugas-pokok.show', $tugas->id) }}"
                                            class="btn btn-sm btn-outline-primary" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('update', $tugas)
                                            <a href="{{ route('penugasan.tugas-pokok.edit', $tugas->id) }}"
                                                class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-3">Tidak ada data tugas pokok</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Menampilkan {{ $tugasPokok->firstItem() ?? 0 }} - {{ $tugasPokok->lastItem() ?? 0 }}
                        dari {{ $tugasPokok->total() }} data
                    </div>
                    <div>
                        {{ $tugasPokok->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // Filter functionality
        const filterStatus = document.getElementById('filterStatus');
        const filterPegawai = document.getElementById('filterPegawai');
        const filterProgress = document.getElementById('filterProgress');
        const searchInput = document.getElementById('searchInput');

        function applyFilters() {
            const status = filterStatus.value;
            const pegawai = filterPegawai.value;
            const progress = filterProgress.value;
            const search = searchInput.value;

            const params = new URLSearchParams();
            if (status) params.append('status', status);
            if (pegawai) params.append('pegawai', pegawai);
            if (progress) params.append('progress', progress);
            if (search) params.append('search', search);

            window.location.href = '{{ route('penugasan.tugas-pokok.index') }}?' + params.toString();
        }

        filterStatus.addEventListener('change', applyFilters);
        filterPegawai.addEventListener('change', applyFilters);
        filterProgress.addEventListener('change', applyFilters);

        // Debounce search
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 500);
        });
    </script>
@endpush
