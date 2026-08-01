@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Validasi Tugas Tim</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tim.index') }}">Tim</a></li>
                <li class="breadcrumb-item active">Validasi</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Daftar Tugas Menunggu Validasi</h5>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-warning text-dark fs-6">
                            <i class="bi bi-hourglass-split me-1"></i>{{ $tugasValidasi->total() }} tugas menunggu
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if ($tugasValidasi->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle-fill fs-1 text-success"></i>
                        <p class="text-muted mt-3">Tidak ada tugas yang menunggu validasi</p>
                        <a href="{{ route('penugasan.tim.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Tim
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Tugas</th>
                                    <th>Jenis</th>
                                    <th>Pegawai</th>
                                    <th class="text-center">Bobot</th>
                                    <th class="text-center">Deadline</th>
                                    <th class="text-center">Bukti</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tugasValidasi as $tugas)
                                    @php
                                        $isOverdue = now()->gt($tugas->tanggal_selesai);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $tugas->nama_tugas }}</div>
                                            @if ($tugas->deskripsi)
                                                <small class="text-muted">{{ Str::limit($tugas->deskripsi, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($tugas->jenis) }}</span>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold">{{ $tugas->pegawai->nama }}</div>
                                            <small
                                                class="text-muted">{{ $tugas->pegawai->profile->jabatan?->nama ?? '-' }}</small>
                                        </td>
                                        <td class="text-center">{{ $tugas->bobot_persen }}%</td>
                                        <td class="text-center">
                                            <small>{{ $tugas->tanggal_selesai->format('d M Y') }}</small>
                                            @if ($isOverdue)
                                                <br><span class="badge bg-danger badge-sm">Terlambat</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($tugas->attachedFiles->isNotEmpty())
                                                <span class="badge bg-success"><i class="bi bi-paperclip me-1"></i>
                                                    {{ $tugas->attachedFiles->count() }} file</span>
                                            @else
                                                <span class="badge bg-secondary"><i class="bi bi-dash-circle"></i></span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('penugasan.show', $tugas->id) }}"
                                                    class="btn btn-outline-info" title="Detail" target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button class="btn btn-outline-success"
                                                    onclick="bukaValidasi('{{ $tugas->id }}', '{{ $tugas->bobot_persen }}', 'diterima')"
                                                    title="Setujui">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                                <button class="btn btn-outline-danger"
                                                    onclick="bukaValidasi('{{ $tugas->id }}', '{{ $tugas->bobot_persen }}', 'revisi')"
                                                    title="Minta Revisi">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Menampilkan {{ $tugasValidasi->firstItem() }} - {{ $tugasValidasi->lastItem() }} dari
                            {{ $tugasValidasi->total() }} data
                        </div>
                        {{ $tugasValidasi->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Modal Validasi -->
    <div class="modal fade" id="validasiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="validasiModalTitle">Validasi Tugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="tugasId">
                    <input type="hidden" id="bobotPersen">
                    <input type="hidden" id="statusValidasi">

                    <div class="mb-3" id="realisasiGroup">
                        <label class="form-label">Realisasi (%) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="realisasiInput" min="0" max="100"
                            step="0.01">
                        <small class="text-muted">Bobot tugas: <strong id="bobotDisplay"></strong>%</small>
                    </div>

                    <div class="alert alert-info d-none" id="previewAlert">
                        Nilai Akhir: <strong id="previewNilai">-</strong> (bobot × realisasi / 100)
                    </div>

                    <div class="mb-3">
                        <label class="form-label" id="catatanLabel">Catatan Validasi</label>
                        <textarea class="form-control" id="catatanInput" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="submitValidasiBtn" onclick="submitValidasi()">
                        <i class="bi bi-save me-2"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .badge-sm {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let validasiModal = null;

        document.addEventListener('DOMContentLoaded', function() {
            validasiModal = new bootstrap.Modal(document.getElementById('validasiModal'));
        });

        function bukaValidasi(tugasId, bobotPersen, status) {
            document.getElementById('tugasId').value = tugasId;
            document.getElementById('bobotPersen').value = bobotPersen;
            document.getElementById('statusValidasi').value = status;
            document.getElementById('bobotDisplay').textContent = bobotPersen;
            document.getElementById('realisasiInput').value = '';
            document.getElementById('catatanInput').value = '';
            document.getElementById('previewAlert').classList.add('d-none');

            const title = document.getElementById('validasiModalTitle');
            const realisasiGroup = document.getElementById('realisasiGroup');
            const catatanLabel = document.getElementById('catatanLabel');
            const submitBtn = document.getElementById('submitValidasiBtn');

            if (status === 'diterima') {
                title.textContent = 'Setujui Tugas';
                realisasiGroup.classList.remove('d-none');
                document.getElementById('realisasiInput').required = true;
                catatanLabel.innerHTML = 'Catatan Validasi';
                document.getElementById('catatanInput').required = false;
                submitBtn.className = 'btn btn-success';
                submitBtn.innerHTML = '<i class="bi bi-check2 me-1"></i>Setujui';
            } else {
                title.textContent = 'Minta Revisi';
                realisasiGroup.classList.add('d-none');
                document.getElementById('realisasiInput').required = false;
                catatanLabel.innerHTML = 'Catatan Revisi <span class="text-danger">*</span>';
                document.getElementById('catatanInput').required = true;
                submitBtn.className = 'btn btn-danger';
                submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Minta Revisi';
            }

            validasiModal.show();
        }

        let previewTimeout;
        document.getElementById('realisasiInput').addEventListener('input', function() {
            const bobot = document.getElementById('bobotPersen').value;
            const realisasi = this.value;

            clearTimeout(previewTimeout);
            if (realisasi === '') {
                document.getElementById('previewAlert').classList.add('d-none');
                return;
            }

            previewTimeout = setTimeout(() => {
                fetch("{{ route('penugasan.tim.preview-penilaian') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            bobot_persen: bobot,
                            realisasi_persen: realisasi
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('previewNilai').textContent = data.data.nilai_akhir;
                            document.getElementById('previewAlert').classList.remove('d-none');
                        }
                    });
            }, 300);
        });

        function submitValidasi() {
            const tugasId = document.getElementById('tugasId').value;
            const status = document.getElementById('statusValidasi').value;
            const payload = {
                status_validasi: status,
                catatan_validasi: document.getElementById('catatanInput').value,
            };

            if (status === 'diterima') {
                payload.realisasi_persen = document.getElementById('realisasiInput').value;
            }

            fetch(`{{ url('/penugasan/tim/validasi') }}/${tugasId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        validasiModal.hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => window.location.reload());
                    } else {
                        let msg = data.message || 'Terjadi kesalahan';
                        if (data.errors) {
                            msg += ': ' + Object.values(data.errors).flat().join(', ');
                        }
                        Swal.fire('Gagal', msg, 'error');
                    }
                });
        }
    </script>
@endpush
