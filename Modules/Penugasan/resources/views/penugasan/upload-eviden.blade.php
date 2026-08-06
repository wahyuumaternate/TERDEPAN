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
                        @if ($tugas->eviden->isNotEmpty())
                            <div class="card bg-light mb-4">
                                <div class="card-header bg-transparent">
                                    <h6 class="mb-0"><i class="bi bi-paperclip me-2"></i>File yang Sudah Diupload
                                        ({{ $tugas->eviden->count() }})</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        @foreach ($tugas->eviden as $file)
                                            <div
                                                class="list-group-item border-0 px-0 d-flex justify-content-between align-items-center">
                                                <span><i class="bi bi-file-earmark-text text-danger me-2"></i>{{ $file->original_name }}</span>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Preview"
                                                        onclick="bukaPreviewEviden({{ Js::from($file->original_name) }}, {{ Js::from(route('terminaldata.filesData.serve', $file->id)) }}, {{ Js::from(route('terminaldata.filesData.download', $file->id)) }}, {{ $file->isImage() ? 'true' : 'false' }}, {{ $file->isPdf() ? 'true' : 'false' }})">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <a href="{{ route('terminaldata.filesData.download', $file->id) }}"
                                                        class="btn btn-sm btn-outline-secondary" title="Download"><i
                                                            class="bi bi-download"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus"
                                                        onclick="hapusBukti({{ Js::from($file->id) }}, {{ Js::from($file->original_name) }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form id="uploadForm">
                            <div class="alert alert-light border small mb-3">
                                <i class="bi bi-info-circle me-1"></i>File akan otomatis tersimpan rapi di folder
                                Eviden Kinerja Anda sendiri, dikelompokkan per tahun &amp; bulan.
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

    <!-- Modal Preview Dokumen Eviden -->
    <div class="modal fade" id="previewEvidenModal" tabindex="-1" aria-labelledby="previewEvidenModalLabel">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-truncate" id="previewEvidenModalLabel">Preview Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center" id="previewEvidenBody" style="min-height: 300px;"></div>
                <div class="modal-footer">
                    <a href="#" id="previewEvidenDownload" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function bukaPreviewEviden(nama, urlServe, urlDownload, isImage, isPdf) {
            document.getElementById('previewEvidenModalLabel').textContent = nama;
            document.getElementById('previewEvidenDownload').href = urlDownload;
            const body = document.getElementById('previewEvidenBody');

            if (isImage) {
                body.innerHTML = `<img src="${urlServe}" class="img-fluid rounded" alt="${nama}">`;
            } else if (isPdf) {
                body.innerHTML = `<iframe src="${urlServe}" style="width:100%;height:75vh;border:0"></iframe>`;
            } else {
                body.innerHTML = `
                    <div class="py-5">
                        <i class="bi bi-file-earmark-text fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted mb-0">Preview tidak tersedia untuk tipe file ini.<br>Silakan download untuk membukanya.</p>
                    </div>`;
            }

            new bootstrap.Modal(document.getElementById('previewEvidenModal')).show();
        }

        function hapusBukti(fileId, nama) {
            Swal.fire({
                title: 'Hapus Bukti Pengerjaan?',
                text: `"${nama}" akan dihapus permanen.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('/penugasan') }}/{{ $tugas->id }}/upload-bukti/${fileId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
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
                            }
                        })
                        .catch(() => {
                            Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus', 'error');
                        });
                }
            });
        }

        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const file = document.getElementById('fileInput').files[0];

            if (!file) {
                Swal.fire('Perhatian', 'File wajib dipilih', 'warning');
                return;
            }

            const formData = new FormData();
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
