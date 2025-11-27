@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Detail Tugas Tambahan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('penugasan.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tugas-tambahan.tugas-saya') }}">Tugas Tambahan</a>
                </li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Task Info Card -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">{{ $tugasTambahan->nama_tugas }}</h5>
                    </div>
                    <div class="card-body">
                        <!-- Status Badge -->
                        <div class="mb-3">
                            @php
                                $statusConfig = [
                                    'pending' => [
                                        'class' => 'warning',
                                        'icon' => 'clock',
                                        'text' => 'Menunggu Konfirmasi',
                                    ],
                                    'dikerjakan' => [
                                        'class' => 'primary',
                                        'icon' => 'play-circle',
                                        'text' => 'Sedang Dikerjakan',
                                    ],
                                    'revisi' => [
                                        'class' => 'danger',
                                        'icon' => 'arrow-clockwise',
                                        'text' => 'Perlu Revisi',
                                    ],
                                    'validasi' => [
                                        'class' => 'info',
                                        'icon' => 'check-circle',
                                        'text' => 'Dalam Validasi',
                                    ],
                                    'selesai' => ['class' => 'success', 'icon' => 'check-all', 'text' => 'Selesai'],
                                ];
                                $config = $statusConfig[$tugasTambahan->status] ?? [
                                    'class' => 'secondary',
                                    'icon' => 'circle',
                                    'text' => ucfirst($tugasTambahan->status),
                                ];
                            @endphp
                            <span class="badge bg-{{ $config['class'] }} fs-5">
                                <i class="bi bi-{{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                            </span>
                            @if ($tugasTambahan->is_lintas_bidang)
                                <span class="badge bg-info fs-5 ms-2">
                                    <i class="bi bi-arrow-left-right me-1"></i>Lintas Bidang
                                </span>
                            @endif
                        </div>

                        <!-- Revisi Alert -->
                        @if ($tugasTambahan->status === 'revisi' && $tugasTambahan->latestRevisi)
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">
                                    <i class="bi bi-exclamation-triangle me-2"></i>Catatan Revisi
                                </h6>
                                <p class="mb-0">{{ $tugasTambahan->latestRevisi->catatan }}</p>
                                <hr>
                                <small class="text-muted">
                                    Oleh: {{ $tugasTambahan->latestRevisi->direvisiOleh->nama ?? 'N/A' }} -
                                    {{ $tugasTambahan->latestRevisi->tanggal->format('d M Y H:i') }}
                                </small>
                            </div>
                        @endif

                        <!-- Info Grid -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Pemberi Tugas</label>
                                <p>
                                    {{ $tugasTambahan->pemberiTugas->nama ?? 'N/A' }}
                                    @if ($tugasTambahan->pemberiTugas && $tugasTambahan->pemberiTugas->jabatan)
                                        <br><small
                                            class="text-muted">{{ $tugasTambahan->pemberiTugas->jabatan->nama_jabatan }}</small>
                                    @endif
                                </p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Tanggal Mulai</label>
                                <p>
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ \Carbon\Carbon::parse($tugasTambahan->tanggal_mulai)->format('d F Y') }}
                                </p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Deadline</label>
                                <p>
                                    @php
                                        $deadline = \Carbon\Carbon::parse($tugasTambahan->tanggal_selesai);
                                        $daysLeft = now()->diffInDays($deadline, false);
                                        $isOverdue = $daysLeft < 0;
                                        $isUrgent = $daysLeft >= 0 && $daysLeft <= 3;
                                    @endphp
                                    <i class="bi bi-calendar-check me-1"></i>
                                    {{ $deadline->format('d F Y') }}
                                    @if ($tugasTambahan->status !== 'selesai')
                                        @if ($isOverdue)
                                            <span class="badge bg-danger ms-2">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Terlambat
                                                {{ abs($daysLeft) }} hari
                                            </span>
                                        @elseif($isUrgent)
                                            <span class="badge bg-warning text-dark ms-2">
                                                <i class="bi bi-clock-history me-1"></i>{{ $daysLeft }} hari lagi
                                            </span>
                                        @endif
                                    @endif
                                </p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Target</label>
                                <p class="fs-5">
                                    <strong>{{ $tugasTambahan->target_value }}</strong>
                                    <small class="text-muted">{{ $tugasTambahan->satuan }}</small>
                                </p>
                            </div>

                            @if ($tugasTambahan->deskripsi)
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-muted">Deskripsi</label>
                                    <p class="text-muted">{{ $tugasTambahan->deskripsi }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 border-top pt-3">
                            @if ($tugasTambahan->status === 'pending')
                                <button type="button" class="btn btn-success me-2" onclick="terimaTugas()">
                                    <i class="bi bi-check-circle me-1"></i>Terima Tugas
                                </button>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#tolakModal">
                                    <i class="bi bi-x-circle me-1"></i>Tolak Tugas
                                </button>
                            @elseif(in_array($tugasTambahan->status, ['dikerjakan', 'revisi']))
                                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal"
                                    data-bs-target="#uploadBuktiModal">
                                    <i class="bi bi-cloud-upload me-1"></i>Upload Bukti
                                </button>
                                <button type="button" class="btn btn-info me-2" data-bs-toggle="modal"
                                    data-bs-target="#progressModal">
                                    <i class="bi bi-graph-up me-1"></i>Update Progress
                                </button>
                                <button type="button" class="btn btn-success" onclick="submitTugas()">
                                    <i class="bi bi-send me-1"></i>Submit untuk Validasi
                                </button>
                            @elseif($tugasTambahan->status === 'validasi')
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Tugas sedang dalam proses validasi oleh atasan
                                </div>
                            @elseif($tugasTambahan->status === 'selesai')
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Tugas telah selesai dan divalidasi
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- File Attachments Card -->
                @if ($tugasTambahan->attachedFiles && $tugasTambahan->attachedFiles->count() > 0)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-paperclip me-2"></i>
                                File Lampiran ({{ $tugasTambahan->attachedFiles->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach ($tugasTambahan->attachedFiles as $file)
                                    @php
                                        $extension = strtolower(pathinfo($file->nama_file, PATHINFO_EXTENSION));
                                        $iconConfig = [
                                            'pdf' => ['icon' => 'file-earmark-pdf', 'color' => 'danger'],
                                            'doc' => ['icon' => 'file-earmark-word', 'color' => 'primary'],
                                            'docx' => ['icon' => 'file-earmark-word', 'color' => 'primary'],
                                            'xls' => ['icon' => 'file-earmark-excel', 'color' => 'success'],
                                            'xlsx' => ['icon' => 'file-earmark-excel', 'color' => 'success'],
                                            'jpg' => ['icon' => 'file-earmark-image', 'color' => 'info'],
                                            'jpeg' => ['icon' => 'file-earmark-image', 'color' => 'info'],
                                            'png' => ['icon' => 'file-earmark-image', 'color' => 'info'],
                                        ];
                                        $iconData = $iconConfig[$extension] ?? [
                                            'icon' => 'file-earmark',
                                            'color' => 'secondary',
                                        ];
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center border rounded p-2">
                                            <i
                                                class="bi bi-{{ $iconData['icon'] }} text-{{ $iconData['color'] }} fs-2 me-3"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ $file->nama_file }}</div>
                                                <small
                                                    class="text-muted">{{ $file->keterangan ?? 'Tidak ada keterangan' }}</small>
                                            </div>
                                            <a href="{{ Storage::url($file->path) }}"
                                                class="btn btn-sm btn-outline-primary" download>
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Revision History Card -->
                @if ($tugasTambahan->historyRevisi && $tugasTambahan->historyRevisi->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-clock-history me-2"></i>
                                Riwayat Revisi ({{ $tugasTambahan->historyRevisi->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @foreach ($tugasTambahan->historyRevisi as $revisi)
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="mb-1">{{ $revisi->direvisiOleh->nama ?? 'N/A' }}</h6>
                                                    <small class="text-muted">
                                                        {{ $revisi->tanggal->format('d F Y H:i') }}
                                                    </small>
                                                </div>
                                                <span class="badge bg-warning">Revisi</span>
                                            </div>
                                            <p class="mb-0">{{ $revisi->catatan }}</p>
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
                <!-- Progress Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-graph-up me-2"></i>
                            Tracking Progress
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($tugasTambahan->progress && $tugasTambahan->progress->count() > 0)
                            @foreach ($tugasTambahan->progress->take(5) as $progress)
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>{{ $progress->progress_persen }}%</strong>
                                        <small class="text-muted">{{ $progress->tanggal->format('d M Y') }}</small>
                                    </div>
                                    <div class="progress mb-2" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: {{ $progress->progress_persen }}%"></div>
                                    </div>
                                    @if ($progress->deskripsi_kegiatan)
                                        <p class="mb-1 small">{{ $progress->deskripsi_kegiatan }}</p>
                                    @endif
                                    @if ($progress->kendala)
                                        <p class="mb-0 small text-danger">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $progress->kendala }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                            @if ($tugasTambahan->progress->count() > 5)
                                <div class="text-center">
                                    <small class="text-muted">Menampilkan 5 dari {{ $tugasTambahan->progress->count() }}
                                        update terakhir</small>
                                </div>
                            @endif
                        @else
                            <p class="text-muted text-center mb-0">Belum ada progress tercatat</p>
                        @endif
                    </div>
                </div>

                <!-- Quick Info Card -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Informasi Cepat
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="small text-muted">Dibuat</label>
                            <p class="mb-0">{{ $tugasTambahan->created_at->format('d F Y H:i') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted">Terakhir Update</label>
                            <p class="mb-0">{{ $tugasTambahan->updated_at->format('d F Y H:i') }}</p>
                        </div>
                        @if ($tugasTambahan->validator)
                            <div class="mb-3">
                                <label class="small text-muted">Validator</label>
                                <p class="mb-0">
                                    {{ $tugasTambahan->validator->nama }}
                                    @if ($tugasTambahan->validator->jabatan)
                                        <br><small
                                            class="text-muted">{{ $tugasTambahan->validator->jabatan->nama_jabatan }}</small>
                                    @endif
                                </p>
                            </div>
                        @endif
                        <div class="mb-0">
                            <label class="small text-muted">Durasi Tugas</label>
                            <p class="mb-0">
                                {{ \Carbon\Carbon::parse($tugasTambahan->tanggal_mulai)->diffInDays($tugasTambahan->tanggal_selesai) }}
                                hari
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Upload Bukti -->
    <div class="modal fade" id="uploadBuktiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Bukti Pengerjaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="uploadBuktiForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">File Bukti <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="file[]" multiple
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                            <small class="text-muted">Format: PDF, Word, Excel, atau gambar (JPG, PNG). Max 5MB per
                                file.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-upload me-2"></i>Upload
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
                            <label class="form-label">Progress (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="progress_persen" min="0"
                                max="100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="deskripsi_kegiatan" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kendala</label>
                            <textarea class="form-control" name="kendala" rows="2" placeholder="Isi jika ada kendala yang dihadapi"></textarea>
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
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Apakah Anda yakin ingin menolak tugas ini?
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="alasan_penolakan" rows="4" required
                                placeholder="Jelaskan alasan penolakan tugas"></textarea>
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
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ffc107;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #ffc107;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 12px;
            width: 2px;
            height: calc(100% + 8px);
            background: #dee2e6;
        }

        .timeline-item:last-child::before {
            display: none;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Terima Tugas
        function terimaTugas() {
            if (confirm('Apakah Anda yakin akan menerima tugas ini?')) {
                fetch('{{ route('penugasan.tugas-tambahan.terima', $tugasTambahan->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Tugas berhasil diterima');
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
        }

        // Submit untuk Validasi
        function submitTugas() {
            if (confirm('Apakah Anda yakin ingin submit tugas untuk validasi?')) {
                fetch('{{ route('penugasan.tugas-tambahan.submit', $tugasTambahan->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Tugas berhasil disubmit untuk validasi');
                            location.reload();
                        } else {
                            alert(data.message || 'Terjadi kesalahan');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat submit tugas');
                    });
            }
        }

        // Upload Bukti Form Handler
        document.getElementById('uploadBuktiForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('{{ route('penugasan.tugas-tambahan.upload-bukti', $tugasTambahan->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Bukti berhasil diupload');
                        location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat upload bukti');
                });
        });

        // Progress Form Handler
        document.getElementById('progressForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('{{ route('penugasan.tugas-tambahan.update-progress', $tugasTambahan->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Progress berhasil diupdate');
                        location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat update progress');
                });
        });

        // Tolak Form Handler
        document.getElementById('tolakForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('{{ route('penugasan.tugas-tambahan.tolak', $tugasTambahan->id) }}', {
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
