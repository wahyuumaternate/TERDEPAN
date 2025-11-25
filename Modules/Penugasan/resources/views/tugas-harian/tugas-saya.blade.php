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
        @if ($tugasHarian->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-3">Belum ada tugas harian</p>
                    <a href="{{ route('penugasan.dashboard') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
                    </a>
                </div>
            </div>
        @else
            <div class="row">
                @foreach ($tugasHarian as $tugas)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 shadow-sm border-0 hover-shadow">
                            <div class="card-body">
                                <!-- Status Badge -->
                                <div class="d-flex justify-content-between align-items-start mb-2">
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
                                        <i class="bi bi-{{ $config['icon'] }} me-1"></i>{{ ucfirst($tugas->status) }}
                                    </span>

                                    @if ($tugas->is_mandiri)
                                        <span class="badge bg-info">
                                            <i class="bi bi-person-check me-1"></i>Mandiri
                                        </span>
                                    @endif
                                </div>

                                <!-- Nama Tugas -->
                                <h6 class="card-title mb-2">
                                    <a href="{{ route('penugasan.tugas-harian.show', $tugas->id) }}"
                                        class="text-decoration-none text-dark fw-bold stretched-link">
                                        {{ Str::limit($tugas->nama_tugas, 60) }}
                                    </a>
                                </h6>

                                <!-- Tugas Pokok Reference -->
                                @if ($tugas->tugasPokok)
                                    <p class="small text-muted mb-2">
                                        <i class="bi bi-folder me-1"></i>
                                        {{ Str::limit($tugas->tugasPokok->nama_tugas, 40) }}
                                    </p>
                                @endif

                                <!-- Deadline -->
                                @php
                                    $daysLeft = now()->diffInDays($tugas->tanggal_selesai, false);
                                    $isUrgent = $daysLeft >= 0 && $daysLeft <= 3;
                                    $isOverdue = $daysLeft < 0;
                                @endphp
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        {{ $tugas->tanggal_selesai->format('d M Y') }}
                                    </small>
                                    @if ($isOverdue && $tugas->status !== 'selesai')
                                        <span class="badge bg-danger ms-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Terlambat
                                        </span>
                                    @elseif($isUrgent && $tugas->status !== 'selesai')
                                        <span class="badge bg-warning ms-2">
                                            <i class="bi bi-hourglass me-1"></i>{{ $daysLeft }}h lagi
                                        </span>
                                    @endif
                                </div>

                                <!-- Pemberi Tugas -->
                                @if ($tugas->pemberiTugas)
                                    <p class="small text-muted mb-3">
                                        <i class="bi bi-person me-1"></i>
                                        {{ $tugas->pemberiTugas->nama }}
                                    </p>
                                @endif

                                <!-- Quick Actions -->
                                <div class="d-flex gap-1 position-relative" style="z-index: 1;">
                                    @if ($tugas->status === 'pending')
                                        <button class="btn btn-sm btn-success flex-fill"
                                            onclick="event.preventDefault(); terimaTugas('{{ $tugas->id }}')">
                                            <i class="bi bi-check2 me-1"></i>Terima
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="event.preventDefault(); tolakTugas('{{ $tugas->id }}')">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    @elseif(in_array($tugas->status, ['dikerjakan', 'revisi']))
                                        <button class="btn btn-sm btn-primary flex-fill"
                                            onclick="event.preventDefault(); window.location.href='{{ route('penugasan.tugas-harian.show', $tugas->id) }}'">
                                            <i class="bi bi-upload me-1"></i>Upload Bukti
                                        </button>
                                    @elseif($tugas->status === 'validasi')
                                        <button class="btn btn-sm btn-info flex-fill" disabled>
                                            <i class="bi bi-hourglass-split me-1"></i>Menunggu Validasi
                                        </button>
                                    @elseif($tugas->status === 'selesai')
                                        @if ($tugas->nilai_akhir)
                                            <button class="btn btn-sm btn-success flex-fill" disabled>
                                                <i class="bi bi-star-fill me-1"></i>Nilai: {{ $tugas->nilai_akhir }}
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-success flex-fill" disabled>
                                                <i class="bi bi-check-all me-1"></i>Selesai
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $tugasHarian->links() }}
            </div>
        @endif
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
        .hover-shadow {
            transition: all 0.3s ease;
        }

        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .nav-tabs .nav-link {
            color: #666;
        }

        .nav-tabs .nav-link.active {
            font-weight: 600;
        }

        .stretched-link::after {
            z-index: 0;
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
