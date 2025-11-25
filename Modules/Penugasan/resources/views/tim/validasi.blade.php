@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Validasi Tugas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('penugasan.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tim.index') }}">Tim</a></li>
                <li class="breadcrumb-item active">Validasi</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0">
                            <i class="bi bi-check-circle me-2"></i>
                            Daftar Tugas Menunggu Validasi
                        </h5>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-warning text-dark fs-6">
                            {{ $tugasValidasi->total() }} tugas menunggu
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Tabs -->
                <ul class="nav nav-tabs mb-3" id="validasiTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all"
                            type="button">
                            Semua ({{ $tugasValidasi->total() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="harian-tab" data-bs-toggle="tab" data-bs-target="#harian"
                            type="button">
                            Tugas Harian ({{ $tugasValidasi->where('tipe', 'harian')->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tambahan-tab" data-bs-toggle="tab" data-bs-target="#tambahan"
                            type="button">
                            Tugas Tambahan ({{ $tugasValidasi->where('tipe', 'tambahan')->count() }})
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="validasiTabContent">
                    <div class="tab-pane fade show active" id="all" role="tabpanel">
                        @include('Penugasan::tim._validasi-table', ['tugas' => $tugasValidasi])
                    </div>
                    <div class="tab-pane fade" id="harian" role="tabpanel">
                        @include('Penugasan::tim._validasi-table', [
                            'tugas' => $tugasValidasi->where('tipe', 'harian'),
                        ])
                    </div>
                    <div class="tab-pane fade" id="tambahan" role="tabpanel">
                        @include('Penugasan::tim._validasi-table', [
                            'tugas' => $tugasValidasi->where('tipe', 'tambahan'),
                        ])
                    </div>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Menampilkan {{ $tugasValidasi->firstItem() ?? 0 }} - {{ $tugasValidasi->lastItem() ?? 0 }}
                        dari {{ $tugasValidasi->total() }} data
                    </div>
                    <div>
                        {{ $tugasValidasi->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Detail & Validasi -->
    <div class="modal fade" id="validasiModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Tugas & Validasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <!-- Content will be loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Setujui -->
    <div class="modal fade" id="setujuiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Setujui Tugas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="setujuiForm">
                    @csrf
                    <input type="hidden" name="tugas_id" id="setujuiTugasId">
                    <input type="hidden" name="tipe" id="setujuiTipe">
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Apakah Anda yakin ingin menyetujui tugas ini?
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan Validasi (Opsional)</label>
                            <textarea class="form-control" name="catatan" rows="3" placeholder="Berikan catatan atau apresiasi..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nilai Akhir</label>
                            <input type="number" class="form-control" name="nilai" step="0.01"
                                placeholder="Masukkan nilai (opsional)">
                            <small class="text-muted">Biarkan kosong jika menggunakan nilai default</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Setujui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Revisi -->
    <div class="modal fade" id="revisiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Minta Revisi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="revisiForm">
                    @csrf
                    <input type="hidden" name="tugas_id" id="revisiTugasId">
                    <input type="hidden" name="tipe" id="revisiTipe">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Tugas akan dikembalikan ke pegawai untuk diperbaiki.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan Revisi <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="catatan" rows="4" required
                                placeholder="Jelaskan apa yang perlu diperbaiki..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-arrow-clockwise me-1"></i>Minta Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Quick Validation Buttons
        function showSetujuiModal(tugasId, tipe) {
            document.getElementById('setujuiTugasId').value = tugasId;
            document.getElementById('setujuiTipe').value = tipe;
            new bootstrap.Modal(document.getElementById('setujuiModal')).show();
        }

        function showRevisiModal(tugasId, tipe) {
            document.getElementById('revisiTugasId').value = tugasId;
            document.getElementById('revisiTipe').value = tipe;
            new bootstrap.Modal(document.getElementById('revisiModal')).show();
        }

        // Setujui Form Handler
        document.getElementById('setujuiForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const tipe = formData.get('tipe');
            const tugasId = formData.get('tugas_id');

            const route = tipe === 'harian' ?
                `/penugasan/tugas-harian/${tugasId}/validasi` :
                `/penugasan/tugas-tambahan/${tugasId}/validasi`;

            fetch(route, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Tugas berhasil divalidasi');
                        location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat validasi');
                });
        });

        // Revisi Form Handler
        document.getElementById('revisiForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const tipe = formData.get('tipe');
            const tugasId = formData.get('tugas_id');

            const route = tipe === 'harian' ?
                `/penugasan/tugas-harian/${tugasId}/revisi` :
                `/penugasan/tugas-tambahan/${tugasId}/revisi`;

            fetch(route, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Permintaan revisi berhasil dikirim');
                        location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat meminta revisi');
                });
        });

        // View Detail
        function viewDetail(tugasId, tipe) {
            const modal = new bootstrap.Modal(document.getElementById('validasiModal'));
            const modalContent = document.getElementById('modalContent');

            modalContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
            modal.show();

            const route = tipe === 'harian' ?
                `/penugasan/tugas-harian/${tugasId}` :
                `/penugasan/tugas-tambahan/${tugasId}`;

            fetch(route + '/detail-validasi')
                .then(response => response.text())
                .then(html => {
                    modalContent.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalContent.innerHTML = '<div class="alert alert-danger">Gagal memuat detail</div>';
                });
        }
    </script>
@endpush
