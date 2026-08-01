@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Upload Bukti Pengerjaan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.show', $tugas->id) }}">{{ Str::limit($tugas->nama_tugas, 40) }}</a>
                </li>
                <li class="breadcrumb-item active">Upload Bukti</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>{{ $tugas->nama_tugas }}</h5>
                    </div>
                    <div class="card-body p-4">
                        @if ($tugas->status === 'revisi')
                            <div class="alert alert-warning">
                                <strong>Perlu Revisi</strong>
                                <p class="mb-0">{{ $tugas->catatan_validasi ?? 'Tidak ada catatan' }}</p>
                            </div>
                        @endif

                        @if ($tugas->attachedFiles->isNotEmpty())
                            <div class="card bg-light mb-4">
                                <div class="card-header bg-transparent">
                                    <h6 class="mb-0"><i class="bi bi-paperclip me-2"></i>File yang Sudah Diupload
                                        ({{ $tugas->attachedFiles->count() }})</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        @foreach ($tugas->attachedFiles as $file)
                                            <div
                                                class="list-group-item border-0 px-0 d-flex justify-content-between align-items-center">
                                                <span><i class="bi bi-file-earmark-text text-danger me-2"></i>{{ $file->original_name }}</span>
                                                <a href="{{ route('terminaldata.filesData.download', $file->id) }}"
                                                    class="btn btn-sm btn-outline-secondary"><i
                                                        class="bi bi-download"></i></a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form id="uploadForm">
                            <div class="mb-3">
                                <label class="form-label">Folder Tujuan <span class="text-danger">*</span></label>
                                <select class="form-select" id="folderSelect" required>
                                    <option value="">Memuat folder...</option>
                                </select>
                                <small class="text-muted">File akan disimpan di folder Terminal Data yang dipilih</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">File Bukti <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="fileInput" required
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.bmp,.svg,.webp">
                                <small class="text-muted">Maks. 100MB — PDF, Word, Excel, PowerPoint, atau gambar</small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('penugasan.show', $tugas->id) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary" id="btnUpload">
                                    <i class="bi bi-cloud-upload me-1"></i>Upload
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        document.addEventListener('DOMContentLoaded', function() {
            fetch("{{ route('terminaldata.foldersData.index') }}")
                .then(r => r.json())
                .then(response => {
                    const folders = Array.isArray(response) ? response : (response.data || []);
                    const select = document.getElementById('folderSelect');
                    select.innerHTML = '<option value="">Pilih folder</option>';
                    folders.forEach(folder => {
                        const option = document.createElement('option');
                        option.value = folder.id;
                        option.textContent = folder.nama || folder.name || 'Folder';
                        select.appendChild(option);
                    });
                })
                .catch(() => {
                    document.getElementById('folderSelect').innerHTML =
                        '<option value="">Gagal memuat folder</option>';
                });
        });

        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const folderId = document.getElementById('folderSelect').value;
            const file = document.getElementById('fileInput').files[0];

            if (!folderId || !file) {
                Swal.fire('Perhatian', 'Folder dan file wajib dipilih', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('folder_id', folderId);
            formData.append('file', file);

            const btn = document.getElementById('btnUpload');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengupload...';

            fetch("{{ route('penugasan.upload-bukti', $tugas->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-cloud-upload me-1"></i>Upload';
                    }
                })
                .catch(() => {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat mengupload', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-cloud-upload me-1"></i>Upload';
                });
        });
    </script>
@endpush
