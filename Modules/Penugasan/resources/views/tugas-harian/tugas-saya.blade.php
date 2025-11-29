@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Tugas Harian Saya</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('penugasan.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Tugas Harian Saya</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-3" id="statusTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $status === 'all' ? 'active' : '' }}" type="button"
                    onclick="window.location.href='{{ route('penugasan.tugas-harian.tugas-saya') }}'">
                    <i class="bi bi-list-ul me-1"></i>Semua
                    <span class="badge bg-secondary">{{ $tugasHarian->total() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $status === 'pending' ? 'active' : '' }}" type="button"
                    onclick="window.location.href='{{ route('penugasan.tugas-harian.tugas-saya', ['status' => 'pending']) }}'">
                    <i class="bi bi-clock me-1"></i>Pending
                    <span
                        class="badge bg-warning">{{ isset($grouped['pending']) ? $grouped['pending']->count() : 0 }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $status === 'dikerjakan' ? 'active' : '' }}" type="button"
                    onclick="window.location.href='{{ route('penugasan.tugas-harian.tugas-saya', ['status' => 'dikerjakan']) }}'">
                    <i class="bi bi-play-circle me-1"></i>Dikerjakan
                    <span
                        class="badge bg-primary">{{ isset($grouped['dikerjakan']) ? $grouped['dikerjakan']->count() : 0 }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $status === 'revisi' ? 'active' : '' }}" type="button"
                    onclick="window.location.href='{{ route('penugasan.tugas-harian.tugas-saya', ['status' => 'revisi']) }}'">
                    <i class="bi bi-arrow-clockwise me-1"></i>Revisi
                    <span class="badge bg-danger">{{ isset($grouped['revisi']) ? $grouped['revisi']->count() : 0 }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $status === 'validasi' ? 'active' : '' }}" type="button"
                    onclick="window.location.href='{{ route('penugasan.tugas-harian.tugas-saya', ['status' => 'validasi']) }}'">
                    <i class="bi bi-check-circle me-1"></i>Validasi
                    <span
                        class="badge bg-info">{{ isset($grouped['validasi']) ? $grouped['validasi']->count() : 0 }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $status === 'selesai' ? 'active' : '' }}" type="button"
                    onclick="window.location.href='{{ route('penugasan.tugas-harian.tugas-saya', ['status' => 'selesai']) }}'">
                    <i class="bi bi-check-all me-1"></i>Selesai
                    <span
                        class="badge bg-success">{{ isset($grouped['selesai']) ? $grouped['selesai']->count() : 0 }}</span>
                </button>
            </li>
        </ul>

        <!-- Quick Stats -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clock-history fs-4 text-warning me-2"></i>
                            <div>
                                <div class="small text-muted">Pending</div>
                                <h6 class="mb-0">{{ isset($grouped['pending']) ? $grouped['pending']->count() : 0 }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-hourglass-split fs-4 text-primary me-2"></i>
                            <div>
                                <div class="small text-muted">Dikerjakan</div>
                                <h6 class="mb-0">
                                    {{ isset($grouped['dikerjakan']) ? $grouped['dikerjakan']->count() : 0 }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-circle fs-4 text-danger me-2"></i>
                            <div>
                                <div class="small text-muted">Revisi</div>
                                <h6 class="mb-0">{{ isset($grouped['revisi']) ? $grouped['revisi']->count() : 0 }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle fs-4 text-success me-2"></i>
                            <div>
                                <div class="small text-muted">Selesai</div>
                                <h6 class="mb-0">{{ isset($grouped['selesai']) ? $grouped['selesai']->count() : 0 }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tugas List -->
        <div class="card">
            <div class="card-body">
                @if ($tugasHarian->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-3">Belum ada tugas harian</p>
                        <a href="{{ route('penugasan.dashboard') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 30%;">Nama Tugas</th>
                                    <th style="width: 15%;">Tugas Pokok</th>
                                    <th style="width: 12%;">Pemberi Tugas</th>
                                    <th style="width: 10%;">Deadline</th>
                                    <th style="width: 8%;">Target</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 10%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tugasHarian as $index => $tugas)
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
                                        $daysLeft = now()->diffInDays($tugas->tanggal_selesai, false);
                                        $isUrgent = $daysLeft >= 0 && $daysLeft <= 3;
                                        $isOverdue = $daysLeft < 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $tugasHarian->firstItem() + $index }}</td>
                                        <td>
                                            <a href="{{ route('penugasan.tugas-harian.show', $tugas->id) }}"
                                                class="text-decoration-none fw-semibold text-dark">
                                                {{ $tugas->nama_tugas }}
                                            </a>
                                            @if ($tugas->is_mandiri)
                                                <span class="badge bg-info badge-sm ms-1">
                                                    <i class="bi bi-person-check"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($tugas->tugasPokok)
                                                <small class="text-muted">
                                                    <i class="bi bi-folder me-1"></i>
                                                    {{ Str::limit($tugas->tugasPokok->nama_tugas, 30) }}
                                                </small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($tugas->pemberiTugas)
                                                <small>{{ Str::limit($tugas->pemberiTugas->nama, 20) }}</small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $tugas->tanggal_selesai->format('d M Y') }}</small>
                                            @if ($isOverdue && $tugas->status !== 'selesai')
                                                <br><span class="badge bg-danger badge-sm">
                                                    <i class="bi bi-exclamation-triangle"></i> Terlambat
                                                </span>
                                            @elseif($isUrgent && $tugas->status !== 'selesai')
                                                <br><span class="badge bg-warning badge-sm">
                                                    {{ $daysLeft }}h lagi
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="fw-semibold">{{ $tugas->target_value }}</small>
                                            <small class="text-muted">{{ $tugas->satuan }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $config['class'] }}">
                                                <i
                                                    class="bi bi-{{ $config['icon'] }} me-1"></i>{{ ucfirst($tugas->status) }}
                                            </span>
                                            @if ($tugas->status === 'selesai' && $tugas->nilai_akhir)
                                                <br><small class="text-warning">
                                                    <i class="bi bi-star-fill"></i> {{ $tugas->nilai_akhir }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('penugasan.tugas-harian.show', $tugas->id) }}"
                                                    class="btn btn-outline-primary" title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if ($tugas->status === 'pending')
                                                    <button class="btn btn-outline-success"
                                                        onclick="terimaTugas('{{ $tugas->id }}')" title="Terima">
                                                        <i class="bi bi-check2"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger"
                                                        onclick="tolakTugas('{{ $tugas->id }}')" title="Tolak">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                @elseif(in_array($tugas->status, ['dikerjakan', 'revisi']))
                                                    <a href="{{ route('penugasan.tugas-harian.show', $tugas->id) }}"
                                                        class="btn btn-outline-primary" title="Upload Bukti">
                                                        <i class="bi bi-upload"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $tugasHarian->links() }}
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
                    <h5 class="modal-title">Tolak Tugas</h5>
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

@push('styles')
    <style>
        .nav-tabs .nav-link {
            color: #666;
        }

        .nav-tabs .nav-link.active {
            font-weight: 600;
        }

        .badge-sm {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .btn-group-sm>.btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let currentTugasId = null;

        function terimaTugas(tugasId) {
            if (!confirm('Apakah Anda yakin menerima tugas ini?')) {
                return;
            }

            fetch(`/penugasan/tugas-harian/${tugasId}/terima`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
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
