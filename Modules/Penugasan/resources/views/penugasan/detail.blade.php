@extends('layouts.main')

@php
    $jenisLabel = $penugasan->jenis === 'pokok' ? 'Tugas Pokok' : 'Tugas Tambahan';
    $statusBadge = [
        'pending' => 'bg-secondary',
        'dikerjakan' => 'bg-primary',
        'revisi' => 'bg-danger',
        'validasi' => 'bg-warning text-dark',
        'selesai' => 'bg-success',
    ][$penugasan->status] ?? 'bg-secondary';
    $isPegawai = auth()->id() === $penugasan->pegawai_id;
@endphp

@section('main')
    <div class="pagetitle">
        <h1>Detail Penugasan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ route('penugasan.tugas-saya', ['jenis' => $penugasan->jenis]) }}">{{ $jenisLabel }} Saya</a>
                </li>
                <li class="breadcrumb-item active">{{ Str::limit($penugasan->nama_tugas, 40) }}</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div>
                                <span class="badge bg-info bg-opacity-10 text-info mb-2">{{ $jenisLabel }}</span>
                                <h4 class="fw-bold mb-0">{{ $penugasan->nama_tugas }}</h4>
                            </div>
                            <span class="badge {{ $statusBadge }} fs-6">{{ ucfirst($penugasan->status) }}</span>
                        </div>

                        @if ($penugasan->deskripsi)
                            <p class="mb-3">{{ $penugasan->deskripsi }}</p>
                        @endif

                        @if ($penugasan->alasan_penugasan)
                            <div class="alert alert-light border mb-3">
                                <small class="text-muted d-block mb-1">Alasan Penugasan</small>
                                {{ $penugasan->alasan_penugasan }}
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-3">
                                <small class="text-muted d-block">Pegawai</small>
                                <div class="fw-semibold">{{ $penugasan->pegawai->nama }}</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Pemberi Tugas</small>
                                <div class="fw-semibold">{{ $penugasan->pemberiTugas->nama ?? 'Mandiri' }}</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Tanggal Mulai</small>
                                <div class="fw-semibold">{{ $penugasan->tanggal_mulai->format('d M Y') }}</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Tanggal Selesai</small>
                                <div class="fw-semibold">{{ $penugasan->tanggal_selesai->format('d M Y') }}</div>
                            </div>
                            @if ($penugasan->target_value)
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Target</small>
                                    <div class="fw-semibold">{{ $penugasan->target_value }} {{ $penugasan->satuan }}
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-3">
                                <small class="text-muted d-block">Bobot</small>
                                <div class="fw-semibold">{{ $penugasan->bobot_persen }}%</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Progress</small>
                                <div class="fw-semibold">{{ $penugasan->progress_persen }}%</div>
                            </div>
                            @if ($penugasan->status === 'selesai')
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Realisasi</small>
                                    <div class="fw-semibold">{{ $penugasan->realisasi_persen }}%</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Nilai Akhir</small>
                                    <div class="fw-bold text-success fs-5">{{ $penugasan->nilai_akhir }}</div>
                                </div>
                            @endif
                        </div>

                        @if ($penugasan->status === 'selesai' && $penugasan->catatan_validasi)
                            <div class="alert alert-success mt-3 mb-0">
                                <small class="text-muted d-block mb-1">Catatan Validasi
                                    ({{ $penugasan->validator->nama ?? '-' }})</small>
                                {{ $penugasan->catatan_validasi }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Aksi Kontekstual -->
                @if ($isPegawai)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Aksi</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @if ($penugasan->status === 'pending')
                                    <button class="btn btn-success" onclick="terimaTugas()">
                                        <i class="bi bi-check-circle me-1"></i>Terima Tugas
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="tolakTugas()">
                                        <i class="bi bi-x-circle me-1"></i>Tolak Tugas
                                    </button>
                                @elseif(in_array($penugasan->status, ['dikerjakan', 'revisi']))
                                    <a href="{{ route('penugasan.form-upload-bukti', $penugasan->id) }}"
                                        class="btn btn-primary">
                                        <i class="bi bi-cloud-upload me-1"></i>Upload Bukti Pengerjaan
                                    </a>
                                    <button class="btn btn-outline-secondary" data-bs-toggle="modal"
                                        data-bs-target="#progressModal">
                                        <i class="bi bi-graph-up me-1"></i>Catat Progress
                                    </button>
                                    <button class="btn btn-success" onclick="submitTugas()">
                                        <i class="bi bi-send-check me-1"></i>Submit untuk Validasi
                                    </button>
                                @elseif($penugasan->status === 'validasi')
                                    <span class="text-muted">
                                        <i class="bi bi-hourglass-split me-1"></i>Menunggu validasi dari atasan
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if ($penugasan->historyRevisi->isNotEmpty())
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0"><i class="bi bi-arrow-repeat me-2"></i>Riwayat Revisi</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @foreach ($penugasan->historyRevisi as $revisi)
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex justify-content-between">
                                            <strong>Revisi ke-{{ $revisi->revisi_ke }}</strong>
                                            <small class="text-muted">{{ $revisi->tanggal_revisi->format('d M Y') }}</small>
                                        </div>
                                        <p class="mb-1 small">{{ $revisi->catatan_revisi }}</p>
                                        <small class="text-muted">Oleh: {{ $revisi->direvisiOleh->nama ?? '-' }} • Deadline:
                                            {{ optional($revisi->deadline_revisi)->format('d M Y') }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0"><i class="bi bi-graph-up me-2"></i>Log Progress</h6>
                    </div>
                    <div class="card-body">
                        @forelse ($penugasan->progress as $log)
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <div>
                                    <div class="fw-semibold">{{ $log->deskripsi_kegiatan }}</div>
                                    @if ($log->kendala)
                                        <small class="text-danger">Kendala: {{ $log->kendala }}</small>
                                    @endif
                                    <small class="text-muted d-block">{{ $log->tanggal->format('d M Y') }}</small>
                                </div>
                                <span class="badge bg-primary align-self-start">{{ $log->progress_persen }}%</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Belum ada catatan progress.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0"><i class="bi bi-paperclip me-2"></i>Bukti Pengerjaan
                            ({{ $penugasan->attachedFiles->count() }})</h6>
                    </div>
                    <div class="card-body">
                        @forelse ($penugasan->attachedFiles as $file)
                            <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                    <span class="small">{{ $file->original_name }}</span>
                                </div>
                                <a href="{{ route('terminaldata.filesData.download', $file->id) }}"
                                    class="btn btn-sm btn-outline-secondary" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Belum ada file bukti diupload.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Catat Progress -->
    <div class="modal fade" id="progressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Catat Progress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Progress (%) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="progressPersen" min="0" max="100"
                            step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="deskripsiKegiatan" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kendala (opsional)</label>
                        <textarea class="form-control" id="kendala" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="simpanProgress()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function reloadWithMessage(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => window.location.reload());
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: message
            });
        }

        function terimaTugas() {
            fetch("{{ route('penugasan.terima', $penugasan->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
        }

        function tolakTugas() {
            Swal.fire({
                title: 'Alasan Penolakan',
                input: 'textarea',
                inputPlaceholder: 'Jelaskan alasan menolak tugas ini...',
                showCancelButton: true,
                confirmButtonText: 'Tolak Tugas',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    fetch("{{ route('penugasan.tolak', $penugasan->id) }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                alasan_penolakan: result.value
                            })
                        })
                        .then(r => r.json())
                        .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
                }
            });
        }

        function submitTugas() {
            fetch("{{ route('penugasan.submit', $penugasan->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
        }

        function simpanProgress() {
            const payload = {
                progress_persen: document.getElementById('progressPersen').value,
                deskripsi_kegiatan: document.getElementById('deskripsiKegiatan').value,
                kendala: document.getElementById('kendala').value,
            };

            fetch("{{ route('penugasan.update-progress', $penugasan->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
        }
    </script>
@endpush
