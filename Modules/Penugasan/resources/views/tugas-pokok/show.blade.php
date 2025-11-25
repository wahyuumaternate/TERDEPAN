@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Detail Tugas Pokok</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('penugasan.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tugas-pokok.tugas-saya') }}">Tugas Pokok</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Header Card -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">{{ $tugasPokok->nama_tugas }}</h4>
                        <p class="mb-0">
                            <i class="bi bi-calendar-range me-1"></i>
                            {{ \Carbon\Carbon::parse($tugasPokok->tanggal_mulai)->format('d M Y') }} -
                            {{ \Carbon\Carbon::parse($tugasPokok->tanggal_selesai)->format('d M Y') }}
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        @php
                            $progress = $tugasPokok->progress_persen ?? 0;
                            $statusConfig = [
                                'pending' => ['class' => 'warning', 'icon' => 'clock'],
                                'dikerjakan' => ['class' => 'info', 'icon' => 'play-circle'],
                                'selesai' => ['class' => 'success', 'icon' => 'check-all'],
                            ];
                            $config = $statusConfig[$tugasPokok->status] ?? [
                                'class' => 'secondary',
                                'icon' => 'circle',
                            ];
                        @endphp
                        <span class="badge bg-{{ $config['class'] }} fs-5 mb-2">
                            <i class="bi bi-{{ $config['icon'] }} me-1"></i>{{ ucfirst($tugasPokok->status) }}
                        </span>
                        <div class="fs-2 fw-bold">{{ number_format($progress, 0) }}%</div>
                        <small>Progress Keseluruhan</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Progress Bar -->
                <div class="mb-4">
                    @php
                        $progressClass =
                            $progress >= 75
                                ? 'success'
                                : ($progress >= 50
                                    ? 'info'
                                    : ($progress >= 25
                                        ? 'warning'
                                        : 'danger'));
                    @endphp
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-{{ $progressClass }}" role="progressbar"
                            style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0"
                            aria-valuemax="100">
                            <strong>{{ number_format($progress, 0) }}%</strong>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">
                            {{ $tugasPokok->tugasHarian->where('status', 'selesai')->count() }} /
                            {{ $tugasPokok->tugasHarian->count() }} tugas selesai
                        </small>
                        <small class="text-muted">
                            Target: <strong>{{ $tugasPokok->target_value }} {{ $tugasPokok->satuan }}</strong>
                        </small>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="row g-3">
                    @if ($tugasPokok->deskripsi)
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Deskripsi</label>
                            <p class="text-muted">{{ $tugasPokok->deskripsi }}</p>
                        </div>
                    @endif

                    @if ($tugasPokok->indikatorPK)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Indikator Kinerja</label>
                            <p>{{ $tugasPokok->indikatorPK->indikator_sasaran }}</p>
                        </div>
                    @endif

                    @if ($tugasPokok->perjanjianKinerja)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Perjanjian Kinerja</label>
                            <p>
                                Periode:
                                {{ \Carbon\Carbon::parse($tugasPokok->perjanjianKinerja->periode_mulai)->format('M Y') }} -
                                {{ \Carbon\Carbon::parse($tugasPokok->perjanjianKinerja->periode_selesai)->format('M Y') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tugas Harian Section -->
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-task me-2"></i>
                        Breakdown Tugas Harian ({{ $tugasPokok->tugasHarian->count() }})
                    </h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTugasModal">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Tugas Mandiri
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if ($tugasPokok->tugasHarian->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-3">Belum ada breakdown tugas harian</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTugasModal">
                            <i class="bi bi-plus me-2"></i>Tambah Tugas Harian
                        </button>
                    </div>
                @else
                    <!-- Filter Status -->
                    <div class="btn-group mb-3" role="group">
                        <button type="button" class="btn btn-outline-secondary active" data-filter="all">
                            Semua ({{ $tugasPokok->tugasHarian->count() }})
                        </button>
                        <button type="button" class="btn btn-outline-warning" data-filter="pending">
                            Pending ({{ $tugasPokok->tugasHarian->where('status', 'pending')->count() }})
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-filter="dikerjakan">
                            Dikerjakan ({{ $tugasPokok->tugasHarian->where('status', 'dikerjakan')->count() }})
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-filter="revisi">
                            Revisi ({{ $tugasPokok->tugasHarian->where('status', 'revisi')->count() }})
                        </button>
                        <button type="button" class="btn btn-outline-success" data-filter="selesai">
                            Selesai ({{ $tugasPokok->tugasHarian->where('status', 'selesai')->count() }})
                        </button>
                    </div>

                    <!-- Tugas Harian Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tugasHarianTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%">Nama Tugas</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Deadline</th>
                                    <th class="text-center">Target</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tugasPokok->tugasHarian->sortByDesc(function ($th) {
            $order = ['revisi' => 1, 'dikerjakan' => 2, 'pending' => 3, 'validasi' => 4, 'selesai' => 5];
            return $order[$th->status] ?? 99;
        }) as $th)
                                    <tr data-status="{{ $th->status }}">
                                        <td>
                                            <a href="{{ route('penugasan.tugas-harian.show', $th->id) }}"
                                                class="text-decoration-none text-dark fw-semibold">
                                                {{ $th->nama_tugas }}
                                            </a>
                                            @if ($th->is_mandiri)
                                                <span class="badge bg-info" title="Tugas Mandiri">
                                                    <i class="bi bi-person-check"></i> Mandiri
                                                </span>
                                            @endif
                                            @if ($th->deskripsi)
                                                <br><small class="text-muted">{{ Str::limit($th->deskripsi, 60) }}</small>
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
                                                $config = $statusConfig[$th->status] ?? [
                                                    'class' => 'secondary',
                                                    'icon' => 'circle',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $config['class'] }}">
                                                <i class="bi bi-{{ $config['icon'] }} me-1"></i>
                                                {{ ucfirst($th->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $daysLeft = now()->diffInDays($th->tanggal_selesai, false);
                                                $isOverdue = $daysLeft < 0;
                                                $isUrgent = $daysLeft >= 0 && $daysLeft <= 3;
                                            @endphp
                                            <div>{{ $th->tanggal_selesai->format('d M Y') }}</div>
                                            @if ($isOverdue && $th->status !== 'selesai')
                                                <span class="badge bg-danger">Terlambat</span>
                                            @elseif($isUrgent && $th->status !== 'selesai')
                                                <span class="badge bg-warning text-dark">{{ $daysLeft }}h</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $th->target_value }}</strong>
                                            <small class="d-block text-muted">{{ $th->satuan }}</small>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('penugasan.tugas-harian.show', $th->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Modal Add Tugas Mandiri -->
    <div class="modal fade" id="addTugasModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tugas Harian Mandiri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTugasForm">
                    @csrf
                    <input type="hidden" name="tugas_pokok_id" value="{{ $tugasPokok->id }}">
                    <input type="hidden" name="is_mandiri" value="1">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Tugas mandiri akan memerlukan persetujuan dari atasan sebelum dapat dikerjakan.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Tugas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_tugas" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_mulai" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_selesai" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Target Value <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="target_value" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="satuan"
                                    placeholder="dokumen, kegiatan, dll" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Filter Status
        document.querySelectorAll('[data-filter]').forEach(btn => {
            btn.addEventListener('click', function() {
                const filter = this.dataset.filter;

                // Update active button
                document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Filter rows
                document.querySelectorAll('#tugasHarianTable tbody tr').forEach(row => {
                    if (filter === 'all' || row.dataset.status === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Add Tugas Form Handler
        document.getElementById('addTugasForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('{{ route('penugasan.tugas-harian.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Tugas mandiri berhasil dibuat dan menunggu persetujuan atasan');
                        location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan tugas');
                });
        });
    </script>
@endpush
