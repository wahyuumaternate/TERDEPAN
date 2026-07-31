@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Daftar Tugas Harian</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('penugasan.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Tugas Harian</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="bi bi-list-task me-2"></i>
                            Manajemen Tugas Harian
                        </h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-outline-secondary me-2" data-bs-toggle="collapse"
                            data-bs-target="#filterSection">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="{{ route('penugasan.tim.form-berikan-tugas') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Tambah Tugas
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Section (Collapsible) -->
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
                                <label class="form-label small">Bidang</label>
                                <select class="form-select form-select-sm" id="filterBidang">
                                    <option value="">Semua Bidang</option>
                                    @foreach ($bidangList as $bidang)
                                        <option value="{{ $bidang->id }}">{{ $bidang->nama }}</option>
                                    @endforeach
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



                <!-- Search & Stats -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="searchInput"
                            placeholder="Cari nama tugas atau deskripsi...">
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">
                            Total: <strong>{{ $tugasHarian->total() }}</strong> |
                            Pending: <span class="text-warning">{{ $stats['pending'] }}</span> |
                            Dikerjakan: <span class="text-primary">{{ $stats['dikerjakan'] }}</span> |
                            Validasi: <span class="text-info">{{ $stats['validasi'] }}</span> |
                            Selesai: <span class="text-success">{{ $stats['selesai'] }}</span>
                        </small>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tugasHarianTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 3%">#</th>
                                <th style="width: 22%">Nama Tugas</th>
                                <th style="width: 13%">Pegawai</th>
                                <th style="width: 10%">Bidang</th>
                                <th style="width: 15%">Tugas Pokok</th>
                                <th class="text-center" style="width: 10%">Deadline</th>
                                <th class="text-center" style="width: 8%">Status</th>
                                <th class="text-center" style="width: 8%">Target</th>
                                <th class="text-center" style="width: 7%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tugasHarian as $index => $tugas)
                                <tr>
                                    <td>{{ $tugasHarian->firstItem() + $index }}</td>
                                    <td>
                                        <a href="{{ route('penugasan.tugas-harian.show', $tugas->id) }}"
                                            class="text-decoration-none fw-semibold text-dark">
                                            {{ $tugas->nama_tugas }}
                                        </a>
                                        @if ($tugas->is_mandiri)
                                            <span class="badge bg-info">
                                                <i class="bi bi-person-check"></i> Mandiri
                                            </span>
                                        @endif
                                        @if ($tugas->deskripsi)
                                            <br><small class="text-muted">{{ Str::limit($tugas->deskripsi, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $tugas->pegawai->nama ?? 'N/A' }}</div>
                                        @if ($tugas->pegawai && $tugas->pegawai->profile->jabatan)
                                            <small class="text-muted">{{ $tugas->pegawai->profile->jabatan->nama_jabatan }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($tugas->pegawai && $tugas->pegawai->profile->bidang)
                                            <span class="badge bg-secondary">{{ $tugas->pegawai->profile->bidang->nama }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($tugas->tugasPokok)
                                            <a href="{{ route('penugasan.tugas-pokok.show', $tugas->tugasPokok->id) }}"
                                                class="text-decoration-none">
                                                {{ Str::limit($tugas->tugasPokok->nama_tugas, 35) }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
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
                                        <a href="{{ route('penugasan.tugas-harian.show', $tugas->id) }}"
                                            class="btn btn-sm btn-outline-primary" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('update', $tugas)
                                            <a href="{{ route('penugasan.tugas-harian.edit', $tugas->id) }}"
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
                                        <p class="text-muted mt-3">Tidak ada data tugas harian</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Menampilkan {{ $tugasHarian->firstItem() ?? 0 }} - {{ $tugasHarian->lastItem() ?? 0 }}
                        dari {{ $tugasHarian->total() }} data
                    </div>
                    <div>
                        {{ $tugasHarian->links() }}
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
        const filterBidang = document.getElementById('filterBidang');
        const filterPeriode = document.getElementById('filterPeriode');
        const searchInput = document.getElementById('searchInput');

        function applyFilters() {
            const params = new URLSearchParams();
            if (filterStatus.value) params.append('status', filterStatus.value);
            if (filterPegawai.value) params.append('pegawai', filterPegawai.value);
            if (filterBidang.value) params.append('bidang', filterBidang.value);
            if (filterPeriode.value) params.append('periode', filterPeriode.value);
            if (searchInput.value) params.append('search', searchInput.value);

            window.location.href = '{{ route('penugasan.tugas-harian.index') }}?' + params.toString();
        }

        filterStatus.addEventListener('change', applyFilters);
        filterPegawai.addEventListener('change', applyFilters);
        filterBidang.addEventListener('change', applyFilters);
        filterPeriode.addEventListener('change', applyFilters);

        // Debounce search
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 500);
        });
    </script>
@endpush
