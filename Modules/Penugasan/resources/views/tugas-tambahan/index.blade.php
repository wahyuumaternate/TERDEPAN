@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Daftar Tugas Tambahan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('penugasan.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Tugas Tambahan</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-plus me-2"></i>
                            Manajemen Tugas Tambahan
                        </h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-outline-secondary me-2" data-bs-toggle="collapse"
                            data-bs-target="#filterSection">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="{{ route('penugasan.tugas-tambahan.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Tambah Tugas
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <div class="collapse mb-3" id="filterSection">
                    <div class="card card-body bg-light">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small">Status</label>
                                <select class="form-select form-select-sm" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="dikerjakan">Dikerjakan</option>
                                    <option value="revisi">Revisi</option>
                                    <option value="validasi">Validasi</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Pegawai</label>
                                <select class="form-select form-select-sm" id="filterPegawai">
                                    <option value="">Semua Pegawai</option>
                                    @foreach ($pegawaiList as $pegawai)
                                        <option value="{{ $pegawai->id }}">{{ $pegawai->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Tipe</label>
                                <select class="form-select form-select-sm" id="filterTipe">
                                    <option value="">Semua</option>
                                    <option value="lintas_bidang">Lintas Bidang</option>
                                    <option value="dalam_bidang">Dalam Bidang</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Periode</label>
                                <select class="form-select form-select-sm" id="filterPeriode">
                                    <option value="">Semua Periode</option>
                                    <option value="hari_ini">Hari Ini</option>
                                    <option value="minggu_ini">Minggu Ini</option>
                                    <option value="bulan_ini">Bulan Ini</option>
                                    <option value="terlambat">Terlambat</option>
                                    <option value="mendesak">Mendesak (≤3 hari)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card border-start border-primary border-4 mb-0">
                            <div class="card-body p-3">
                                <h4 class="mb-0">{{ $stats['total'] }}</h4>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-start border-warning border-4 mb-0">
                            <div class="card-body p-3">
                                <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-start border-primary border-4 mb-0">
                            <div class="card-body p-3">
                                <h4 class="mb-0">{{ $stats['dikerjakan'] }}</h4>
                                <small class="text-muted">Dikerjakan</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-start border-danger border-4 mb-0">
                            <div class="card-body p-3">
                                <h4 class="mb-0">{{ $stats['revisi'] }}</h4>
                                <small class="text-muted">Revisi</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-start border-info border-4 mb-0">
                            <div class="card-body p-3">
                                <h4 class="mb-0">{{ $stats['validasi'] }}</h4>
                                <small class="text-muted">Validasi</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-start border-success border-4 mb-0">
                            <div class="card-body p-3">
                                <h4 class="mb-0">{{ $stats['selesai'] }}</h4>
                                <small class="text-muted">Selesai</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Insights -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>{{ $stats['lintas_bidang'] }}</strong> tugas lintas bidang dari total
                                <strong>{{ $stats['total'] }}</strong> tugas tambahan
                            </div>
                            <div>
                                <small class="text-muted">{{ $stats['terlambat'] }} tugas terlambat |
                                    {{ $stats['mendesak'] }} mendesak</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="searchInput"
                            placeholder="Cari nama tugas atau pemberi tugas...">
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">{{ $tugasTambahan->total() }} tugas ditemukan</small>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 3%">#</th>
                                <th style="width: 22%">Nama Tugas</th>
                                <th style="width: 13%">Pegawai</th>
                                <th style="width: 10%">Bidang</th>
                                <th style="width: 13%">Pemberi Tugas</th>
                                <th class="text-center" style="width: 10%">Deadline</th>
                                <th class="text-center" style="width: 8%">Status</th>
                                <th class="text-center" style="width: 8%">Target</th>
                                <th class="text-center" style="width: 7%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tugasTambahan as $index => $tugas)
                                <tr>
                                    <td>{{ $tugasTambahan->firstItem() + $index }}</td>
                                    <td>
                                        <a href="{{ route('penugasan.tugas-tambahan.show', $tugas->id) }}"
                                            class="text-decoration-none fw-semibold text-dark">
                                            {{ $tugas->nama_tugas }}
                                        </a>
                                        @if ($tugas->is_lintas_bidang)
                                            <span class="badge bg-info">
                                                <i class="bi bi-arrow-left-right"></i> Lintas Bidang
                                            </span>
                                        @endif
                                        @if ($tugas->deskripsi)
                                            <br><small class="text-muted">{{ Str::limit($tugas->deskripsi, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $tugas->pegawai->nama ?? 'N/A' }}</div>
                                        @if ($tugas->pegawai && $tugas->pegawai->jabatan)
                                            <small class="text-muted">{{ $tugas->pegawai->jabatan->nama_jabatan }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($tugas->pegawai && $tugas->pegawai->bidang)
                                            <span
                                                class="badge bg-secondary">{{ $tugas->pegawai->bidang->nama_bidang }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $tugas->pemberiTugas->nama ?? 'N/A' }}</div>
                                        @if ($tugas->pemberiTugas && $tugas->pemberiTugas->jabatan)
                                            <br><small
                                                class="text-muted">{{ $tugas->pemberiTugas->jabatan->nama_jabatan }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $deadline = \Carbon\Carbon::parse($tugas->tanggal_selesai);
                                            $daysLeft = now()->diffInDays($deadline, false);
                                            $isOverdue = $daysLeft < 0;
                                            $isUrgent = $daysLeft >= 0 && $daysLeft <= 3;
                                        @endphp
                                        <div>{{ $deadline->format('d M Y') }}</div>
                                        @if ($tugas->status !== 'selesai')
                                            @if ($isOverdue)
                                                <span class="badge bg-danger">Terlambat</span>
                                            @elseif($isUrgent)
                                                <span class="badge bg-warning text-dark">{{ $daysLeft }}h</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusConfig = [
                                                'pending' => ['class' => 'warning', 'icon' => 'clock'],
                                                'dikerjakan' => ['class' => 'primary', 'icon' => 'play-circle'],
                                                'revisi' => ['class' => 'danger', 'icon' => 'arrow-clockwise'],
                                                'validasi' => ['class' => 'info', 'icon' => 'check-circle'],
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
                                        <a href="{{ route('penugasan.tugas-tambahan.show', $tugas->id) }}"
                                            class="btn btn-sm btn-outline-primary" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('update', $tugas)
                                            <a href="{{ route('penugasan.tugas-tambahan.edit', $tugas->id) }}"
                                                class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-3">Tidak ada data tugas tambahan</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Menampilkan {{ $tugasTambahan->firstItem() ?? 0 }} - {{ $tugasTambahan->lastItem() ?? 0 }}
                        dari {{ $tugasTambahan->total() }} data
                    </div>
                    <div>
                        {{ $tugasTambahan->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const filterStatus = document.getElementById('filterStatus');
        const filterPegawai = document.getElementById('filterPegawai');
        const filterTipe = document.getElementById('filterTipe');
        const filterPeriode = document.getElementById('filterPeriode');
        const searchInput = document.getElementById('searchInput');

        function applyFilters() {
            const params = new URLSearchParams();
            if (filterStatus.value) params.append('status', filterStatus.value);
            if (filterPegawai.value) params.append('pegawai', filterPegawai.value);
            if (filterTipe.value) params.append('tipe', filterTipe.value);
            if (filterPeriode.value) params.append('periode', filterPeriode.value);
            if (searchInput.value) params.append('search', searchInput.value);

            window.location.href = '{{ route('penugasan.tugas-tambahan.index') }}?' + params.toString();
        }

        filterStatus.addEventListener('change', applyFilters);
        filterPegawai.addEventListener('change', applyFilters);
        filterTipe.addEventListener('change', applyFilters);
        filterPeriode.addEventListener('change', applyFilters);

        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 500);
        });
    </script>
@endpush
