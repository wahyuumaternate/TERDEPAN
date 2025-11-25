@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Tugas Tambahan Saya</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('penugasan.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Tugas Tambahan Saya</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Filter & Actions -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <ul class="nav nav-pills" id="statusFilter">
                            <li class="nav-item">
                                <a class="nav-link {{ $status === 'all' ? 'active' : '' }}"
                                    href="{{ route('penugasan.tugas-tambahan.tugas-saya') }}">
                                    Semua <span class="badge bg-light text-dark ms-1">{{ $tugasTambahan->total() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}"
                                    href="{{ route('penugasan.tugas-tambahan.tugas-saya', ['status' => 'pending']) }}">
                                    Pending <span
                                        class="badge bg-warning text-dark ms-1">{{ isset($grouped['pending']) ? $grouped['pending']->count() : 0 }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status === 'dikerjakan' ? 'active' : '' }}"
                                    href="{{ route('penugasan.tugas-tambahan.tugas-saya', ['status' => 'dikerjakan']) }}">
                                    Dikerjakan <span
                                        class="badge bg-primary ms-1">{{ isset($grouped['dikerjakan']) ? $grouped['dikerjakan']->count() : 0 }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status === 'validasi' ? 'active' : '' }}"
                                    href="{{ route('penugasan.tugas-tambahan.tugas-saya', ['status' => 'validasi']) }}">
                                    Validasi <span
                                        class="badge bg-info ms-1">{{ isset($grouped['validasi']) ? $grouped['validasi']->count() : 0 }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status === 'selesai' ? 'active' : '' }}"
                                    href="{{ route('penugasan.tugas-tambahan.tugas-saya', ['status' => 'selesai']) }}">
                                    Selesai <span
                                        class="badge bg-success ms-1">{{ isset($grouped['selesai']) ? $grouped['selesai']->count() : 0 }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-info fs-6">
                            <i class="bi bi-info-circle me-1"></i>
                            Tugas Tambahan (Cross-hierarchy)
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @if ($tugasTambahan->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-3 mb-0">Belum ada tugas tambahan</p>
                    <small class="text-muted">Tugas tambahan akan muncul ketika atasan memberikan tugas khusus</small>
                </div>
            </div>
        @else
            <!-- Table View -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%">Nama Tugas</th>
                                    <th class="text-center">Status</th>
                                    <th>Pemberi Tugas</th>
                                    <th class="text-center">Deadline</th>
                                    <th class="text-center">Target</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tugasTambahan as $tugas)
                                    <tr>
                                        <td>
                                            <a href="{{ route('penugasan.tugas-tambahan.show', $tugas->id) }}"
                                                class="text-decoration-none fw-semibold text-dark">
                                                {{ $tugas->nama_tugas }}
                                            </a>
                                            @if ($tugas->deskripsi)
                                                <br>
                                                <small class="text-muted">
                                                    {{ Str::limit($tugas->deskripsi, 80) }}
                                                </small>
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
                                                <i class="bi bi-{{ $config['icon'] }} me-1"></i>
                                                {{ ucfirst($tugas->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($tugas->pemberiTugas)
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-person-circle me-2 text-muted"></i>
                                                    <div>
                                                        <div class="small fw-semibold">{{ $tugas->pemberiTugas->nama }}
                                                        </div>
                                                        <small
                                                            class="text-muted">{{ $tugas->pemberiTugas->jabatan?->nama }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $daysLeft = now()->diffInDays($tugas->tanggal_selesai, false);
                                                $isUrgent = $daysLeft >= 0 && $daysLeft <= 3;
                                                $isOverdue = $daysLeft < 0;
                                            @endphp
                                            <div>
                                                {{ $tugas->tanggal_selesai->format('d M Y') }}
                                            </div>
                                            @if ($isOverdue && $tugas->status !== 'selesai')
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>Terlambat
                                                </span>
                                            @elseif($isUrgent && $tugas->status !== 'selesai')
                                                <span class="badge bg-warning text-dark">
                                                    {{ $daysLeft }} hari lagi
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $tugas->target_value }}</strong>
                                            <small class="text-muted d-block">{{ $tugas->satuan }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if ($tugas->status === 'pending')
                                                <button class="btn btn-sm btn-success me-1"
                                                    onclick="terimaTugas('{{ $tugas->id }}')" title="Terima Tugas">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="tolakTugas('{{ $tugas->id }}')" title="Tolak Tugas">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            @elseif(in_array($tugas->status, ['dikerjakan', 'revisi']))
                                                <a href="{{ route('penugasan.tugas-tambahan.show', $tugas->id) }}"
                                                    class="btn btn-sm btn-primary" title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @elseif($tugas->status === 'validasi')
                                                <button class="btn btn-sm btn-info" disabled>
                                                    <i class="bi bi-hourglass-split"></i>
                                                </button>
                                            @elseif($tugas->status === 'selesai')
                                                <a href="{{ route('penugasan.tugas-tambahan.show', $tugas->id) }}"
                                                    class="btn btn-sm btn-success" title="Lihat Detail">
                                                    <i class="bi bi-file-earmark-check"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $tugasTambahan->links() }}
                    </div>
                </div>
            </div>
        @endif
    </section>

    <!-- Modal Tolak Tugas -->
    <div class="modal fade" id="tolakModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Tugas Tambahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="tolakForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Tugas tambahan yang ditolak akan dihapus dari daftar tugas Anda.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="alasan_penolakan" rows="4" required
                                placeholder="Jelaskan alasan penolakan tugas ini..."></textarea>
                            <div class="form-text">Alasan akan dikirimkan ke pemberi tugas.</div>
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

        function terimaTugas(tugasId) {
            if (!confirm('Apakah Anda yakin menerima tugas tambahan ini?')) {
                return;
            }

            fetch(`/penugasan/tugas-tambahan/${tugasId}/terima`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Tugas tambahan berhasil diterima!');
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

            fetch(`/penugasan/tugas-tambahan/${currentTugasId}/tolak`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Tugas tambahan berhasil ditolak');
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
