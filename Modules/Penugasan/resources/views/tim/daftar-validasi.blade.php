@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Validasi Tugas Tim</h1>
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
            <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0">
                            <i class="bi bi-check-circle me-2"></i>
                            Daftar Tugas Menunggu Validasi
                        </h5>
                        <small class="text-muted">Tugas harian yang perlu divalidasi dari anggota tim</small>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-warning text-dark fs-6">
                            <i class="bi bi-hourglass-split me-1"></i>
                            {{ $tugasValidasi->total() }} tugas menunggu
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
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 25%;">Nama Tugas</th>
                                    <th style="width: 15%;">Tugas Pokok</th>
                                    <th style="width: 15%;">Pegawai</th>
                                    <th style="width: 10%;">Deadline</th>
                                    <th style="width: 8%;">Target</th>
                                    <th style="width: 8%;">Bukti</th>
                                    <th style="width: 14%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tugasValidasi as $index => $tugas)
                                    @php
                                        $daysLeft = now()->diffInDays($tugas->tanggal_selesai, false);
                                        $isOverdue = $daysLeft < 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $tugasValidasi->firstItem() + $index }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $tugas->nama_tugas }}</div>
                                            @if ($tugas->deskripsi)
                                                <small class="text-muted">{{ Str::limit($tugas->deskripsi, 50) }}</small>
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
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-circle text-primary me-2 fs-5"></i>
                                                <div>
                                                    <div class="small fw-semibold">{{ $tugas->pegawai->nama }}</div>
                                                    <small
                                                        class="text-muted">{{ $tugas->pegawai->jabatan?->nama ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $tugas->tanggal_selesai->format('d M Y') }}</small>
                                            @if ($isOverdue)
                                                <br><span class="badge bg-danger badge-sm">
                                                    <i class="bi bi-exclamation-triangle"></i> Terlambat
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="fw-semibold">{{ $tugas->target_value }}</small>
                                            <small class="text-muted d-block">{{ $tugas->satuan }}</small>
                                        </td>
                                        <td>
                                            @if ($tugas->attachedFiles->isNotEmpty())
                                                <span class="badge bg-success">
                                                    <i class="bi bi-paperclip me-1"></i>
                                                    {{ $tugas->attachedFiles->count() }} file
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-dash-circle"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('penugasan.tugas-harian.show', $tugas->id) }}"
                                                    class="btn btn-outline-info" title="Detail" target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button class="btn btn-outline-success"
                                                    onclick="validasiTugas('{{ $tugas->id }}', 'setuju')"
                                                    title="Setujui">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                                <button class="btn btn-outline-danger"
                                                    onclick="validasiTugas('{{ $tugas->id }}', 'revisi')"
                                                    title="Revisi">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Menampilkan {{ $tugasValidasi->firstItem() }} - {{ $tugasValidasi->lastItem() }}
                            dari {{ $tugasValidasi->total() }} data
                        </div>
                        <div>
                            {{ $tugasValidasi->links() }}
                        </div>
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
                <form id="validasiForm">
                    @csrf
                    <input type="hidden" name="tugas_id" id="tugasId">
                    <input type="hidden" name="jenis_tugas" value="tugas_harian">
                    <input type="hidden" name="status_validasi" id="statusValidasi">

                    <div class="modal-body">
                        <div class="mb-3" id="nilaiGroup">
                            <label class="form-label">Nilai Kualitas <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="penilaian_kualitas" id="nilaiInput"
                                min="0" max="100" step="0.01" required>
                            <small class="text-muted">Nilai kualitas pekerjaan antara 0 - 100</small>
                        </div>

                        <div class="mb-3" id="progressGroup">
                            <label class="form-label">Update Progress Tugas Pokok</label>
                            <select class="form-select" name="progress_update_type" id="progressType" required>
                                <option value="otomatis">Otomatis (berdasarkan target)</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>

                        <div class="mb-3" id="progressValueGroup" style="display: none;">
                            <label class="form-label">Nilai Progress Manual <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="progress_value" id="progressValue"
                                min="0" step="0.01">
                            <small class="text-muted">Masukkan nilai progress manual</small>
                        </div>

                        <div class="mb-3" id="catatanValidasiGroup">
                            <label class="form-label">Catatan Validasi</label>
                            <textarea class="form-control" name="catatan_validasi" id="catatanValidasiInput" rows="3"
                                placeholder="Berikan feedback positif (opsional)"></textarea>
                        </div>

                        <div class="mb-3" id="catatanRevisiGroup" style="display: none;">
                            <label class="form-label">Catatan Revisi <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="catatan_revisi" id="catatanRevisiInput" rows="4" required
                                placeholder="Jelaskan apa yang perlu diperbaiki..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-save me-2"></i>Simpan
                        </button>
                    </div>
                </form>
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
        let validasiModal = null;
        let currentTugasId = null;
        let currentAction = null;

        document.addEventListener('DOMContentLoaded', function() {
            validasiModal = new bootstrap.Modal(document.getElementById('validasiModal'));
        });

        function validasiTugas(tugasId, action) {
            currentTugasId = tugasId;
            currentAction = action;

            const modalTitle = document.getElementById('validasiModalTitle');
            const nilaiGroup = document.getElementById('nilaiGroup');
            const progressGroup = document.getElementById('progressGroup');
            const progressValueGroup = document.getElementById('progressValueGroup');
            const catatanValidasiGroup = document.getElementById('catatanValidasiGroup');
            const catatanRevisiGroup = document.getElementById('catatanRevisiGroup');
            const submitBtn = document.getElementById('submitBtn');
            const statusValidasi = document.getElementById('statusValidasi');
            const nilaiInput = document.getElementById('nilaiInput');
            const progressType = document.getElementById('progressType');
            const progressValue = document.getElementById('progressValue');
            const catatanRevisiInput = document.getElementById('catatanRevisiInput');

            // Reset form
            document.getElementById('validasiForm').reset();
            document.getElementById('tugasId').value = tugasId;

            if (action === 'setuju') {
                modalTitle.textContent = 'Setujui Tugas';
                nilaiGroup.style.display = 'block';
                progressGroup.style.display = 'block';
                catatanValidasiGroup.style.display = 'block';
                catatanRevisiGroup.style.display = 'none';

                nilaiInput.required = true;
                nilaiInput.disabled = false;
                progressType.required = true;
                progressType.disabled = false;
                catatanRevisiInput.required = false;
                catatanRevisiInput.disabled = true;

                statusValidasi.value = 'diterima';
                submitBtn.innerHTML = '<i class="bi bi-check2 me-2"></i>Setujui';
                submitBtn.className = 'btn btn-success';
            } else if (action === 'revisi') {
                modalTitle.textContent = 'Minta Revisi';
                nilaiGroup.style.display = 'none';
                progressGroup.style.display = 'none';
                progressValueGroup.style.display = 'none';
                catatanValidasiGroup.style.display = 'none';
                catatanRevisiGroup.style.display = 'block';

                nilaiInput.required = false;
                nilaiInput.disabled = true;
                progressType.required = false;
                progressType.disabled = true;
                progressValue.required = false;
                progressValue.disabled = true;
                catatanRevisiInput.required = true;
                catatanRevisiInput.disabled = false;

                statusValidasi.value = 'revisi';
                submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Minta Revisi';
                submitBtn.className = 'btn btn-danger';
            }

            validasiModal.show();
        }

        // Toggle progress value input based on type
        document.getElementById('progressType').addEventListener('change', function() {
            const progressValueGroup = document.getElementById('progressValueGroup');
            const progressValue = document.getElementById('progressValue');

            if (this.value === 'manual') {
                progressValueGroup.style.display = 'block';
                progressValue.required = true;
                progressValue.disabled = false;
            } else {
                progressValueGroup.style.display = 'none';
                progressValue.required = false;
                progressValue.disabled = true;
                progressValue.value = ''; // Clear value
            }
        });

        document.getElementById('validasiForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const tugasId = document.getElementById('tugasId').value;
            const statusValidasi = document.getElementById('statusValidasi').value;

            // Debug: Check if jenis_tugas is in the form
            console.log('jenis_tugas value:', formData.get('jenis_tugas'));
            console.log('All form data:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }

            // Remove disabled fields from FormData (they won't be sent)
            // But also ensure we only send required fields based on status
            if (statusValidasi === 'revisi') {
                // For revisi, remove fields that are not needed
                formData.delete('penilaian_kualitas');
                formData.delete('progress_update_type');
                formData.delete('progress_value');
                formData.delete('catatan_validasi');
            } else if (statusValidasi === 'diterima') {
                // For diterima, remove catatan_revisi
                formData.delete('catatan_revisi');

                // If progress type is otomatis, remove progress_value
                if (formData.get('progress_update_type') === 'otomatis') {
                    formData.delete('progress_value');
                }
            }

            console.log('Final form data being sent:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }

            // Show loading
            const submitBtn = document.getElementById('submitBtn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

            fetch(`/penugasan/tim/validasi/${tugasId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        validasiModal.hide();

                        // Show success message with SweetAlert if available, else alert
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message || 'Validasi berhasil disimpan!',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            alert(data.message || 'Validasi berhasil disimpan!');
                            location.reload();
                        }
                    } else {
                        // Show error details if available
                        let errorMessage = data.message || 'Terjadi kesalahan';
                        if (data.errors) {
                            errorMessage += ':\n';
                            Object.values(data.errors).forEach(errors => {
                                errorMessage += '- ' + errors.join(', ') + '\n';
                            });
                        }

                        console.error('Server response:', data);
                        alert(errorMessage);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses validasi');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
        });
    </script>
@endpush
