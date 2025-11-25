@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Detail Tugas Harian</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('penugasan.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tugas-harian.tugas-saya') }}">Tugas Harian</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Informasi Tugas -->
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">{{ $tugasHarian->nama_tugas }}</h5>
                                @php
                                    $statusConfig = [
                                        'pending' => ['class' => 'warning', 'icon' => 'clock'],
                                        'dikerjakan' => ['class' => 'primary', 'icon' => 'play-circle'],
                                        'revisi' => ['class' => 'danger', 'icon' => 'arrow-clockwise'],
                                        'validasi' => ['class' => 'info', 'icon' => 'check-circle'],
                                        'selesai' => ['class' => 'success', 'icon' => 'check-all'],
                                    ];
                                    $config = $statusConfig[$tugasHarian->status] ?? [
                                        'class' => 'secondary',
                                        'icon' => 'circle',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $config['class'] }} fs-6">
                                    <i class="bi bi-{{ $config['icon'] }} me-1"></i>{{ ucfirst($tugasHarian->status) }}
                                </span>
                                @if ($tugasHarian->is_mandiri)
                                    <span class="badge bg-info fs-6">
                                        <i class="bi bi-person-check me-1"></i>Mandiri
                                    </span>
                                @endif
                            </div>

                            @if (in_array($tugasHarian->status, ['pending']))
                                <div class="btn-group">
                                    <button class="btn btn-success" onclick="terimaTugas()">
                                        <i class="bi bi-check2 me-2"></i>Terima
                                    </button>
                                    <button class="btn btn-danger" onclick="tolakTugas()">
                                        <i class="bi bi-x me-2"></i>Tolak
                                    </button>
                                </div>
                            @elseif(in_array($tugasHarian->status, ['dikerjakan', 'revisi']))
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadBuktiModal">
                                    <i class="bi bi-upload me-2"></i>Upload Bukti
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Deskripsi -->
                        @if ($tugasHarian->deskripsi)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Deskripsi Tugas</label>
                                <p class="text-muted">{{ $tugasHarian->deskripsi }}</p>
                            </div>
                        @endif

                        <!-- Info Grid -->
                        <div class="row g-3">
                            <!-- Tugas Pokok -->
                            @if ($tugasHarian->tugasPokok)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-muted">Tugas Pokok</label>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-folder2 text-primary me-2 fs-5"></i>
                                        <div>
                                            <a href="{{ route('penugasan.tugas-pokok.show', $tugasHarian->tugasPokok->id) }}"
                                                class="text-decoration-none fw-semibold">
                                                {{ $tugasHarian->tugasPokok->nama_tugas }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Pemberi Tugas -->
                            @if ($tugasHarian->pemberiTugas)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-muted">Pemberi Tugas</label>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle text-info me-2 fs-5"></i>
                                        <div>
                                            <div class="fw-semibold">{{ $tugasHarian->pemberiTugas->nama }}</div>
                                            <small
                                                class="text-muted">{{ $tugasHarian->pemberiTugas->jabatan?->nama }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Tanggal Mulai -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Tanggal Mulai</label>
                                <div>
                                    <i class="bi bi-calendar-event text-success me-2"></i>
                                    {{ $tugasHarian->tanggal_mulai->format('d F Y') }}
                                </div>
                            </div>

                            <!-- Deadline -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Deadline</label>
                                <div>
                                    @php
                                        $daysLeft = now()->diffInDays($tugasHarian->tanggal_selesai, false);
                                        $isOverdue = $daysLeft < 0;
                                    @endphp
                                    <i class="bi bi-calendar-x text-danger me-2"></i>
                                    {{ $tugasHarian->tanggal_selesai->format('d F Y') }}
                                    @if ($isOverdue && $tugasHarian->status !== 'selesai')
                                        <span class="badge bg-danger ms-2">Terlambat {{ abs($daysLeft) }} hari</span>
                                    @elseif($daysLeft >= 0 && $daysLeft <= 3)
                                        <span class="badge bg-warning ms-2">{{ $daysLeft }} hari lagi</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Target -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Target</label>
                                <div>
                                    <i class="bi bi-bullseye text-warning me-2"></i>
                                    <strong>{{ $tugasHarian->target_value }} {{ $tugasHarian->satuan }}</strong>
                                </div>
                            </div>

                            <!-- Nilai -->
                            @if ($tugasHarian->nilai_akhir)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-muted">Nilai</label>
                                    <div>
                                        <i class="bi bi-star-fill text-warning me-2"></i>
                                        <strong class="fs-5">{{ $tugasHarian->nilai_akhir }}</strong>
                                        <small class="text-muted">/100</small>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Catatan Revisi -->
                        @if ($tugasHarian->status === 'revisi' && $tugasHarian->catatan_revisi)
                            <div class="alert alert-danger mt-3">
                                <h6 class="alert-heading">
                                    <i class="bi bi-exclamation-triangle me-2"></i>Catatan Revisi
                                </h6>
                                <p class="mb-0">{{ $tugasHarian->catatan_revisi }}</p>
                                @if ($tugasHarian->validator)
                                    <hr>
                                    <small class="text-muted">
                                        Oleh: {{ $tugasHarian->validator->nama }} |
                                        {{ $tugasHarian->tanggal_validasi?->format('d M Y H:i') }}
                                    </small>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- File Bukti -->
                @if ($tugasHarian->attachedFiles->isNotEmpty())
                    <div class="card mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">
                                <i class="bi bi-paperclip me-2"></i>Bukti Pengerjaan
                                ({{ $tugasHarian->attachedFiles->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach ($tugasHarian->attachedFiles as $file)
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center p-2 border rounded">
                                            @php
                                                $extension = pathinfo($file->file_path, PATHINFO_EXTENSION);
                                                $iconClass = match ($extension) {
                                                    'pdf' => 'bi-file-pdf text-danger',
                                                    'doc', 'docx' => 'bi-file-word text-primary',
                                                    'xls', 'xlsx' => 'bi-file-excel text-success',
                                                    'jpg', 'jpeg', 'png', 'gif' => 'bi-file-image text-info',
                                                    default => 'bi-file-earmark text-secondary',
                                                };
                                            @endphp
                                            <i class="bi {{ $iconClass }} fs-3 me-3"></i>
                                            <div class="flex-grow-1">
                                                <div class="small fw-semibold">{{ $file->nama_file }}</div>
                                                <small
                                                    class="text-muted">{{ $file->uploaded_at?->format('d M Y H:i') }}</small>
                                            </div>
                                            <a href="{{ Storage::url($file->file_path) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- History Revisi -->
                @if ($tugasHarian->historyRevisi->isNotEmpty())
                    <div class="card">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">
                                <i class="bi bi-clock-history me-2"></i>Riwayat Revisi
                                ({{ $tugasHarian->historyRevisi->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @foreach ($tugasHarian->historyRevisi as $history)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-danger"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Revisi ke-{{ $history->revisi_ke }}</h6>
                                            <p class="text-muted mb-1">{{ $history->catatan }}</p>
                                            <small class="text-muted">
                                                <i class="bi bi-person me-1"></i>{{ $history->direvisiOleh?->nama }} |
                                                <i
                                                    class="bi bi-calendar me-1"></i>{{ $history->tanggal_revisi->format('d M Y H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Progress Tracking -->
                @if ($tugasHarian->progress->isNotEmpty())
                    <div class="card mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up me-2"></i>Progress Tracking
                            </h6>
                        </div>
                        <div class="card-body">
                            @foreach ($tugasHarian->progress->take(5) as $prog)
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-semibold">{{ $prog->progress_persen }}%</span>
                                        <small class="text-muted">{{ $prog->tanggal->format('d M Y') }}</small>
                                    </div>
                                    <div class="progress mb-2" style="height: 8px;">
                                        <div class="progress-bar bg-primary"
                                            style="width: {{ $prog->progress_persen }}%"></div>
                                    </div>
                                    <p class="small text-muted mb-0">{{ $prog->deskripsi_kegiatan }}</p>
                                    @if ($prog->kendala)
                                        <small class="text-danger">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $prog->kendala }}
                                        </small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Aksi</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if ($tugasHarian->status === 'pending')
                                <button class="btn btn-success" onclick="terimaTugas()">
                                    <i class="bi bi-check2 me-2"></i>Terima Tugas
                                </button>
                                <button class="btn btn-danger" onclick="tolakTugas()">
                                    <i class="bi bi-x me-2"></i>Tolak Tugas
                                </button>
                            @elseif(in_array($tugasHarian->status, ['dikerjakan', 'revisi']))
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#uploadBuktiModal">
                                    <i class="bi bi-upload me-2"></i>Upload Bukti
                                </button>
                                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#progressModal">
                                    <i class="bi bi-graph-up me-2"></i>Update Progress
                                </button>
                                @if ($tugasHarian->attachedFiles->isNotEmpty())
                                    <button class="btn btn-success" onclick="submitTugas()">
                                        <i class="bi bi-send me-2"></i>Submit untuk Validasi
                                    </button>
                                @endif
                            @elseif($tugasHarian->status === 'validasi')
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-hourglass-split me-2"></i>
                                    Menunggu validasi dari atasan
                                </div>
                            @elseif($tugasHarian->status === 'selesai')
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Tugas telah selesai
                                </div>
                            @endif

                            <hr>

                            <a href="{{ route('penugasan.tugas-harian.tugas-saya') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Upload Bukti -->
    <div class="modal fade" id="uploadBuktiModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Bukti Pengerjaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">File Bukti <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="file" required multiple
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            <div class="form-text">Format: PDF, Word, Excel, Image. Maks 5MB per file.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="3"
                                placeholder="Tambahkan keterangan jika diperlukan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-2"></i>Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Update Progress -->
    <div class="modal fade" id="progressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Progress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="progressForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Progress (%)</label>
                            <input type="number" class="form-control" name="progress_persen" min="0"
                                max="100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="deskripsi_kegiatan" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kendala (Opsional)</label>
                            <textarea class="form-control" name="kendala" rows="2"></textarea>
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

    <!-- Modal Tolak -->
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
                            <textarea class="form-control" name="alasan_penolakan" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle me-2"></i>Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-marker {
            position: absolute;
            left: -30px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 12px;
            bottom: -8px;
            width: 2px;
            background: #dee2e6;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function terimaTugas() {
            if (!confirm('Apakah Anda yakin menerima tugas ini?')) return;

            fetch(`/penugasan/tugas-harian/{{ $tugasHarian->id }}/terima`, {
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
                });
        }

        function tolakTugas() {
            const modal = new bootstrap.Modal(document.getElementById('tolakModal'));
            modal.show();
        }

        function submitTugas() {
            if (!confirm('Submit tugas untuk validasi? Pastikan semua bukti sudah diupload.')) return;

            fetch(`/penugasan/tugas-harian/{{ $tugasHarian->id }}/submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Tugas berhasil disubmit untuk validasi!');
                        location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                });
        }

        // Upload Form Handler
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('tugas_id', '{{ $tugasHarian->id }}');
            formData.append('tipe_tugas', 'tugas_harian');

            fetch('/penugasan/tugas-harian/{{ $tugasHarian->id }}/upload-bukti', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Bukti berhasil diupload!');
                        location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                });
        });

        // Progress Form Handler
        document.getElementById('progressForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(`/penugasan/tugas-harian/{{ $tugasHarian->id }}/update-progress`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Progress berhasil diupdate!');
                        location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                });
        });

        // Tolak Form Handler
        document.getElementById('tolakForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(`/penugasan/tugas-harian/{{ $tugasHarian->id }}/tolak`, {
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
                        window.location.href = '{{ route('penugasan.tugas-harian.tugas-saya') }}';
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                });
        });
    </script>
@endpush
