@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Tugas Pokok Saya</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('penugasan.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Tugas Pokok Saya</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Alert & Sync Button -->
        <div class="card mb-3">
            <div class="card-body pt-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-1">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            Sinkronisasi Tugas Pokok
                        </h6>
                        <p class="text-muted small mb-0">
                            Tugas pokok akan otomatis dibuat dari Indikator Perjanjian Kinerja Anda yang sudah disetujui.
                            Klik tombol "Sinkronisasi dari PK" untuk memuat data terbaru.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-primary" onclick="sinkronisasiTugasPokok()">
                            <i class="bi bi-arrow-repeat me-2"></i>Sinkronisasi dari PK
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check me-2"></i>
                            Daftar Tugas Pokok
                        </h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <select class="form-select form-select-sm d-inline-block w-auto" id="filterTahun"
                            onchange="filterByYear()">
                            <option value="">Semua Tahun</option>
                            @foreach ($tahuns as $year)
                                <option value="{{ $year }}" {{ $tahun == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if ($tugasPokok->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-3 mb-2">Belum ada tugas pokok yang ditugaskan</p>
                        <small class="text-muted">Klik tombol "Sinkronisasi dari PK" di atas untuk memuat tugas pokok dari
                            Perjanjian Kinerja Anda</small>
                    </div>
                @else
                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 30%">Nama Tugas</th>
                                    <th class="text-center">Periode</th>
                                    <th class="text-center">Progress</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Target</th>
                                    <th class="text-center">Breakdown</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tugasPokok as $index => $tp)
                                    <tr>
                                        <td>{{ $tugasPokok->firstItem() + $index }}</td>
                                        <td>
                                            <a href="{{ route('penugasan.tugas-pokok.show', $tp->id) }}"
                                                class="text-decoration-none fw-semibold text-dark">
                                                {{ $tp->nama_tugas }}
                                            </a>
                                            @if ($tp->deskripsi)
                                                <br><small class="text-muted">{{ Str::limit($tp->deskripsi, 50) }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <small>
                                                {{ \Carbon\Carbon::parse($tp->tanggal_mulai)->format('d M Y') }}
                                                <br>s/d<br>
                                                {{ \Carbon\Carbon::parse($tp->tanggal_selesai)->format('d M Y') }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $progress = $tp->progress_persen ?? 0;
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
                                                    style="width: {{ $progress }}%;"
                                                    aria-valuenow="{{ $progress }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <small>{{ number_format($progress, 0) }}%</small>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                {{ $tp->tugasHarian->where('status', 'selesai')->count() }} /
                                                {{ $tp->tugasHarian->count() }} selesai
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusConfig = [
                                                    'pending' => ['class' => 'warning', 'icon' => 'clock'],
                                                    'dikerjakan' => ['class' => 'info', 'icon' => 'play-circle'],
                                                    'selesai' => ['class' => 'success', 'icon' => 'check-all'],
                                                ];
                                                $config = $statusConfig[$tp->status] ?? [
                                                    'class' => 'secondary',
                                                    'icon' => 'circle',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $config['class'] }}">
                                                <i class="bi bi-{{ $config['icon'] }}"></i>
                                                {{ ucfirst($tp->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $tp->target_value }}</strong>
                                            <br><small class="text-muted">{{ $tp->satuan }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if ($tp->tugasHarian->count() > 0)
                                                <button class="btn btn-sm btn-outline-secondary" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#breakdown{{ $tp->id }}">
                                                    <i class="bi bi-list-task"></i> {{ $tp->tugasHarian->count() }} tugas
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('penugasan.tugas-pokok.show', $tp->id) }}"
                                                class="btn btn-sm btn-primary" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @if ($tp->tugasHarian->count() > 0)
                                        <tr class="collapse" id="breakdown{{ $tp->id }}">
                                            <td colspan="8" class="bg-light">
                                                <div class="p-3">
                                                    <h6 class="mb-3">
                                                        <i class="bi bi-list-task me-2"></i>
                                                        Breakdown Tugas Harian ({{ $tp->tugasHarian->count() }})
                                                    </h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead class="table-secondary">
                                                                <tr>
                                                                    <th style="width: 40%">Nama Tugas</th>
                                                                    <th class="text-center">Status</th>
                                                                    <th class="text-center">Deadline</th>
                                                                    <th class="text-center">Target</th>
                                                                    <th class="text-center">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($tp->tugasHarian as $th)
                                                                    <tr>
                                                                        <td>
                                                                            <a href="{{ route('penugasan.tugas-harian.show', $th->id) }}"
                                                                                class="text-decoration-none text-dark">
                                                                                {{ $th->nama_tugas }}
                                                                            </a>
                                                                            @if ($th->is_mandiri)
                                                                                <span class="badge bg-info ms-1"
                                                                                    title="Tugas Mandiri">
                                                                                    <i class="bi bi-person-check"></i>
                                                                                    Mandiri
                                                                                </span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            @php
                                                                                $statusConfig = [
                                                                                    'pending' => [
                                                                                        'class' => 'warning',
                                                                                        'icon' => 'clock',
                                                                                    ],
                                                                                    'dikerjakan' => [
                                                                                        'class' => 'primary',
                                                                                        'icon' => 'play-circle',
                                                                                    ],
                                                                                    'revisi' => [
                                                                                        'class' => 'danger',
                                                                                        'icon' => 'arrow-clockwise',
                                                                                    ],
                                                                                    'validasi' => [
                                                                                        'class' => 'info',
                                                                                        'icon' => 'check-circle',
                                                                                    ],
                                                                                    'selesai' => [
                                                                                        'class' => 'success',
                                                                                        'icon' => 'check-all',
                                                                                    ],
                                                                                ];
                                                                                $config = $statusConfig[
                                                                                    $th->status
                                                                                ] ?? [
                                                                                    'class' => 'secondary',
                                                                                    'icon' => 'circle',
                                                                                ];
                                                                            @endphp
                                                                            <span class="badge bg-{{ $config['class'] }}">
                                                                                <i class="bi bi-{{ $config['icon'] }}"></i>
                                                                                {{ ucfirst($th->status) }}
                                                                            </span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            @php
                                                                                $daysLeft = now()->diffInDays(
                                                                                    $th->tanggal_selesai,
                                                                                    false,
                                                                                );
                                                                                $isUrgent =
                                                                                    $daysLeft >= 0 && $daysLeft <= 3;
                                                                                $isOverdue = $daysLeft < 0;
                                                                            @endphp
                                                                            <small>{{ $th->tanggal_selesai->format('d M Y') }}</small>
                                                                            @if ($isOverdue && $th->status !== 'selesai')
                                                                                <br><span
                                                                                    class="badge bg-danger">Terlambat</span>
                                                                            @elseif($isUrgent && $th->status !== 'selesai')
                                                                                <br><span
                                                                                    class="badge bg-warning text-dark">{{ $daysLeft }}h</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <strong>{{ $th->target_value }}</strong>
                                                                            <small
                                                                                class="d-block text-muted">{{ $th->satuan }}</small>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            @if ($th->status === 'pending')
                                                                                <button class="btn btn-sm btn-success me-1"
                                                                                    onclick="terimaTugas('{{ $th->id }}')"
                                                                                    title="Terima">
                                                                                    <i class="bi bi-check2"></i>
                                                                                </button>
                                                                                <button
                                                                                    class="btn btn-sm btn-outline-danger"
                                                                                    onclick="tolakTugas('{{ $th->id }}')"
                                                                                    title="Tolak">
                                                                                    <i class="bi bi-x"></i>
                                                                                </button>
                                                                            @elseif(in_array($th->status, ['dikerjakan', 'revisi']))
                                                                                <a href="{{ route('penugasan.tugas-harian.show', $th->id) }}"
                                                                                    class="btn btn-sm btn-primary"
                                                                                    title="Upload Bukti">
                                                                                    <i class="bi bi-upload"></i>
                                                                                </a>
                                                                            @elseif($th->status === 'validasi')
                                                                                <button class="btn btn-sm btn-info"
                                                                                    disabled title="Menunggu Validasi">
                                                                                    <i class="bi bi-hourglass-split"></i>
                                                                                </button>
                                                                            @else
                                                                                <a href="{{ route('penugasan.tugas-harian.show', $th->id) }}"
                                                                                    class="btn btn-sm btn-outline-secondary"
                                                                                    title="Lihat">
                                                                                    <i class="bi bi-eye"></i>
                                                                                </a>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
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
                @endif
            </div>
        </div>
    </section>

    <!-- Modal Tolak Tugas -->
    <div class="modal fade" id="tolakModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Tugas Harian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="tolakForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="alasan_penolakan" rows="4" required
                                placeholder="Jelaskan alasan penolakan tugas ini..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle me-2"></i>Tolak Tugas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentTugasId = null;

        // Get CSRF token
        function getCsrfToken() {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                return metaTag.content;
            }
            // Fallback: get from form if exists
            const csrfInput = document.querySelector('input[name="_token"]');
            if (csrfInput) {
                return csrfInput.value;
            }
            // Last resort: get from Laravel
            return '{{ csrf_token() }}';
        }

        // Filter by Year
        function filterByYear() {
            const tahun = document.getElementById('filterTahun').value;
            const url = new URL(window.location.href);

            if (tahun) {
                url.searchParams.set('tahun', tahun);
            } else {
                url.searchParams.delete('tahun');
            }

            window.location.href = url.toString();
        }

        // Sinkronisasi Tugas Pokok dari PK
        function sinkronisasiTugasPokok() {
            if (!confirm('Sinkronisasi akan membuat tugas pokok baru dari Perjanjian Kinerja Anda. Lanjutkan?')) {
                return;
            }

            // Show loading
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sinkronisasi...';
            btn.disabled = true;

            fetch('{{ route('penugasan.tugas-pokok.sinkron') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const message =
                            `Sinkronisasi berhasil!\n\nTugas baru: ${data.created}\nDiskip (sudah ada): ${data.skipped}`;
                        alert(message);
                        if (data.created > 0) {
                            location.reload();
                        } else {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }
                    } else {
                        alert(data.message || 'Terjadi kesalahan saat sinkronisasi');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat sinkronisasi tugas pokok');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }

        function terimaTugas(tugasId) {
            if (!confirm('Apakah Anda yakin menerima tugas ini?')) {
                return;
            }

            fetch(`/penugasan/tugas-harian/${tugasId}/terima`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Tugas berhasil diterima!');
                        location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menerima tugas');
                });
        }

        function tolakTugas(tugasId) {
            currentTugasId = tugasId;
            const modal = new bootstrap.Modal(document.getElementById('tolakModal'));
            modal.show();
        }

        document.getElementById('tolakForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(`/penugasan/tugas-harian/${currentTugasId}/tolak`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Tugas berhasil ditolak');
                        location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menolak tugas');
                });
        });
    </script>
@endpush
