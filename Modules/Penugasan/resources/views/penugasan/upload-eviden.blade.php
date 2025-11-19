@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Upload Eviden Kinerja</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">E-Kinerja</li>
                <li class="breadcrumb-item">Penugasan</li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.show', $tugas->pegawai->id) }}">Tugas Saya</a></li>
                <li class="breadcrumb-item active">Upload Eviden</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <!-- Card Header -->
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-white bg-opacity-20 rounded-3 p-2 me-3">
                                <i class="bi bi-cloud-upload" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">{{ $tugas->nama_tugas }}</h5>
                                <small class="opacity-90">
                                    @if (isset($tugas->tugasPokok))
                                        {{ $tugas->tugasPokok->nama_tugas }}
                                    @elseif(isset($jenisTugas) && $jenisTugas === 'tugas_tambahan')
                                        Tugas Tambahan
                                    @else
                                        Tugas Mandiri
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-4">
                        <!-- Alert untuk status revisi -->
                        @if ($tugas->status === 'revisi')
                            <div class="alert alert-warning border-0 mb-4">
                                <div class="d-flex">
                                    <i class="bi bi-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="alert-heading mb-2">Perlu Revisi</h6>
                                        <p class="mb-1"><strong>Catatan Revisi:</strong></p>
                                        <p class="mb-0">{{ $tugas->catatan_validasi ?? 'Tidak ada catatan' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- File yang Sudah Diupload -->
                        @if ($tugas->attachedFiles && $tugas->attachedFiles->count() > 0)
                            <div class="card bg-light mb-4">
                                <div class="card-header bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="bi bi-paperclip text-primary me-2"></i>
                                            File yang Sudah Diupload ({{ $tugas->attachedFiles->count() }})
                                        </h6>
                                        @if ($tugas->status === 'revisi')
                                            <small class="text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Pilih file yang perlu direvisi
                                            </small>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th width="40">#</th>
                                                    <th>Nama File</th>
                                                    <th width="100">Ukuran</th>
                                                    <th width="80">Versi</th>
                                                    <th width="150">Tanggal Upload</th>
                                                    <th width="150" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($tugas->attachedFiles as $index => $file)
                                                    <tr id="file-row-{{ $file->id }}">
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            <i
                                                                class="bi bi-file-earmark-{{ $file->extension === 'pdf' ? 'pdf' : 'text' }} text-danger me-2"></i>
                                                            {{ $file->original_name }}
                                                            <span class="badge bg-secondary ms-2"
                                                                id="status-badge-{{ $file->id }}">
                                                                Current
                                                            </span>
                                                        </td>
                                                        <td>{{ number_format($file->size / 1024, 2) }} KB</td>
                                                        <td>
                                                            <span class="badge bg-info">v{{ $file->version ?? 1 }}</span>
                                                        </td>
                                                        <td>{{ $file->created_at->format('d M Y H:i') }}</td>
                                                        <td class="text-center">
                                                            <div class="btn-group" role="group">
                                                                <a href="{{ Storage::url($file->storage_path) }}"
                                                                    class="btn btn-sm btn-outline-primary" target="_blank"
                                                                    title="Lihat File">
                                                                    <i class="bi bi-eye"></i>
                                                                </a>
                                                                @if ($tugas->status === 'revisi')
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-warning"
                                                                        onclick="pilihFilePengganti('{{ $file->id }}', {{ json_encode($file->original_name) }}, '{{ $file->folder_id }}')"
                                                                        title="Pilih File Pengganti">
                                                                        <i class="bi bi-arrow-repeat"></i> Ganti
                                                                    </button>
                                                                    <input type="file"
                                                                        id="file-input-{{ $file->id }}"
                                                                        class="d-none file-replacement-input"
                                                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls"
                                                                        data-file-id="{{ $file->id }}"
                                                                        data-file-name="{{ $file->original_name }}"
                                                                        data-folder-id="{{ $file->folder_id }}">
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if ($tugas->status === 'revisi')
                                        <div class="alert alert-warning border-0 mt-3 mb-0 small">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                            <strong>Mode Revisi:</strong> Klik tombol <strong>"Ganti"</strong> <i
                                                class="bi bi-arrow-repeat"></i> pada file yang ingin diperbarui dengan versi
                                            baru, atau upload file tambahan di bagian bawah.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Informasi Tugas -->
                        <div class="alert alert-info border-0 mb-4">
                            <div class="d-flex">
                                <i class="bi bi-info-circle me-3" style="font-size: 1.5rem;"></i>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-2">Informasi Tugas</h6>
                                    <div class="row small">
                                        <div class="col-md-6 mb-2">
                                            <strong>Tanggal Selesai:</strong>
                                            {{ date('d F Y', strtotime($tugas->tanggal_selesai)) }}
                                        </div>
                                        @if (isset($tugas->target_value) && isset($tugas->satuan))
                                            <div class="col-md-6 mb-2">
                                                <strong>Target:</strong> {{ number_format($tugas->target_value, 0) }}
                                                {{ $tugas->satuan }}
                                            </div>
                                        @endif
                                        <div class="col-12">
                                            <strong>Deskripsi:</strong> {{ $tugas->deskripsi ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form id="formUploadEviden" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="tugas_id" value="{{ $tugas->id }}">
                            <input type="hidden" name="jenis_tugas" value="{{ $jenisTugas ?? 'tugas_harian' }}">

                            <!-- Drag and Drop Zone / Simple File Selection -->
                            <div class="mb-4" id="fileSelectionArea">
                                @if ($tugas->status === 'revisi' && $tugas->attachedFiles->count() > 0)
                                    <!-- Simple button for revision mode -->
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-file-earmark-plus text-primary me-2"></i>
                                        Tambah File Baru (Opsional)
                                    </label>
                                    <div class="text-center p-4 border-2 border-dashed rounded bg-light">
                                        <input type="file" class="d-none" id="fileInput" name="files[]" multiple
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls">
                                        <i class="bi bi-plus-circle text-primary mb-3" style="font-size: 3rem;"></i>
                                        <p class="mb-3">Anda dapat menambahkan file baru selain mengganti file yang sudah
                                            ada</p>
                                        <button type="button" class="btn btn-primary"
                                            onclick="document.getElementById('fileInput').click()">
                                            <i class="bi bi-folder2-open me-2"></i>Pilih File Baru
                                        </button>
                                        <p class="text-muted small mt-3 mb-0">
                                            Format: PDF, DOC, DOCX, JPG, JPEG, PNG, XLSX, XLS
                                            <br>
                                            Maksimal 10MB per file
                                        </p>
                                    </div>
                                @else
                                    <!-- Full drag & drop zone for first upload -->
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-file-earmark-plus text-primary me-2"></i>
                                        Pilih Eviden Kinerja <span class="text-danger">*</span>
                                    </label>

                                    <!-- Drop Zone (Hidden when files exist) -->
                                    <div id="dropZone" class="drop-zone">
                                        <input type="file" class="d-none" id="fileInput" name="files[]" multiple
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls">

                                        <div class="drop-zone-content">
                                            <i class="bi bi-cloud-arrow-up drop-zone-icon"></i>
                                            <h5 class="drop-zone-title">Drag & Drop file di sini</h5>
                                            <p class="drop-zone-text mb-3">atau</p>
                                            <button type="button" class="btn btn-primary"
                                                onclick="document.getElementById('fileInput').click()">
                                                <i class="bi bi-folder2-open me-2"></i>Pilih File
                                            </button>
                                            <p class="drop-zone-info mt-3 mb-0">
                                                Format: PDF, DOC, DOCX, JPG, JPEG, PNG, XLSX, XLS
                                                <br>
                                                <small class="text-muted">Maksimal 10MB per file</small>
                                            </p>
                                        </div>

                                        <!-- Drop Zone Active State -->
                                        <div class="drop-zone-overlay">
                                            <i class="bi bi-cloud-arrow-up-fill drop-zone-overlay-icon"></i>
                                            <p class="drop-zone-overlay-text">Lepaskan file di sini</p>
                                        </div>
                                    </div>

                                    <!-- Simple Add More Button (Shown when files exist) -->
                                    <div id="addMoreButton" style="display: none;">
                                        <button type="button" class="btn btn-outline-primary"
                                            onclick="document.getElementById('fileInput').click()">
                                            <i class="bi bi-plus-circle me-2"></i>Tambah File Lainnya
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <!-- Tabel File yang Dipilih -->
                            <div id="fileTableContainer" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-files text-primary me-2"></i>
                                        File yang Dipilih (<span id="fileCount">0</span>)
                                    </h6>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="clearAllFiles()">
                                            <i class="bi bi-trash me-1"></i> Hapus Semua
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="fileTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50" class="text-center">#</th>
                                                <th>Nama File</th>
                                                <th width="150">Ukuran</th>
                                                <th width="200">Lokasi Tujuan <span class="text-danger">*</span></th>
                                                <th width="80" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="fileTableBody">
                                            <!-- File rows will be inserted here -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Keterangan -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Keterangan</label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                        placeholder="Jelaskan detail pengerjaan tugas ini..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Card Footer -->
                    <div class="card-footer bg-light py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('penugasan.show', $tugas->pegawai_id) }}"
                                class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" id="btnUpload" onclick="uploadFiles()"
                                    disabled>
                                    <i class="bi bi-cloud-upload me-1"></i> Upload File
                                </button>
                                <button type="button" class="btn btn-success" id="btnValidasi"
                                    onclick="requestValidasi()" disabled>
                                    <i class="bi bi-check-circle me-1"></i> Minta Validasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Pilih Folder -->
    <div class="modal fade" id="modalPilihFolder" tabindex="-1" aria-labelledby="modalPilihFolderLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalPilihFolderLabel">
                        <i class="bi bi-folder2-open text-primary me-2"></i>
                        Pilih Folder Tujuan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Breadcrumb Navigasi -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <small class="text-muted me-2">Lokasi yang dipilih:</small>
                            <div class="breadcrumb-path bg-light px-3 py-2 rounded flex-grow-1">
                                <i class="bi bi-folder text-warning me-2"></i>
                                <span id="currentPath">ROOT</span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigateBack()"
                            id="btnBack" disabled>
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div id="folderLoading" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Memuat folder...</p>
                    </div>

                    <!-- Folder List -->
                    <div id="folderList" class="folder-list">
                        <!-- Folders will be loaded here -->
                    </div>

                    <!-- Empty State -->
                    <div id="folderEmpty" class="text-center py-5" style="display: none;">
                        <i class="bi bi-folder-x" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Folder kosong</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" onclick="confirmFolderSelection()">
                        <i class="bi bi-check-circle me-1"></i> Pilih Folder Ini
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .icon-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
        }

        /* Uploaded Files Table Styles */
        .bg-light .card-header {
            border-bottom: 1px solid #dee2e6;
        }

        .table-sm th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        /* Drag and Drop Zone Styles */
        .drop-zone {
            position: relative;
            border: 3px dashed #cbd5e0;
            border-radius: 12px;
            padding: 3rem 2rem;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .drop-zone:hover {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }

        .drop-zone.drag-over {
            border-color: #0d6efd;
            background-color: #e7f1ff;
            border-style: solid;
        }

        .drop-zone-content {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .drop-zone-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .drop-zone:hover .drop-zone-icon {
            color: #0d6efd;
            transform: scale(1.1);
        }

        .drop-zone-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .drop-zone-text {
            color: #6c757d;
            font-size: 1rem;
        }

        .drop-zone-info {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Add More Button */
        #addMoreButton {
            margin-bottom: 1rem;
        }

        #addMoreButton .btn {
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        #addMoreButton .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
        }

        .drop-zone-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(13, 110, 253, 0.95);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .drop-zone.drag-over .drop-zone-overlay {
            opacity: 1;
        }

        .drop-zone-overlay-icon {
            font-size: 5rem;
            color: white;
            margin-bottom: 1rem;
            animation: bounce 1s infinite;
        }

        .drop-zone-overlay-text {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .table th {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .file-row:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .location-select {
            min-width: 250px;
        }

        .dropdown-menu {
            min-width: 200px;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
        }

        .dropdown-item:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }

        /* Loading overlay */
        .upload-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .upload-progress-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            min-width: 400px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        /* Modal Folder Styles */
        .folder-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 0.5rem;
        }

        .folder-item {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            border: 1px solid transparent;
            margin-bottom: 0.25rem;
        }

        .folder-item:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .folder-item.active {
            background-color: #e7f1ff;
            border-color: #0d6efd;
        }

        .folder-item i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }

        .folder-item .folder-name {
            font-weight: 500;
            color: #495057;
            flex-grow: 1;
        }

        .folder-item .folder-arrow {
            color: #6c757d;
            transition: transform 0.2s ease;
        }

        .folder-item:hover .folder-arrow {
            transform: translateX(3px);
        }

        .breadcrumb-path {
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }

        /* Scrollbar styling */
        .folder-list::-webkit-scrollbar {
            width: 8px;
        }

        .folder-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .folder-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .folder-list::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* File Replacement Styles */
        .btn-group .btn-sm {
            font-size: 0.8125rem;
            padding: 0.25rem 0.5rem;
        }

        #status-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }

        .folder-select-item {
            cursor: pointer;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .folder-select-item:hover {
            background-color: #f8f9fa !important;
        }

        .folder-select-item.active {
            background-color: #e7f1ff !important;
            border-color: #0d6efd !important;
        }

        .list-group-item-action {
            border: 1px solid #dee2e6;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let selectedFiles = [];
        let uploadedFiles = [];

        $(document).ready(function() {
            // Initialize drag and drop
            initDragAndDrop();

            // Handle file input change
            $('#fileInput').on('change', function(e) {
                const files = Array.from(e.target.files);

                if (files.length > 0) {
                    addFilesToTable(files);
                    // Clear input for next selection
                    $('#fileInput').val('');
                }
            });
        });

        // Initialize Drag and Drop functionality
        function initDragAndDrop() {
            const dropZone = document.getElementById('dropZone');

            if (!dropZone) return;

            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            // Highlight drop zone when dragging over it
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });

            // Handle dropped files
            dropZone.addEventListener('drop', handleDrop, false);

            // Click to select files
            dropZone.addEventListener('click', function(e) {
                // Don't trigger if clicking the button
                if (e.target.tagName !== 'BUTTON' && !e.target.closest('button')) {
                    document.getElementById('fileInput').click();
                }
            });
        }

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(e) {
            document.getElementById('dropZone').classList.add('drag-over');
        }

        function unhighlight(e) {
            document.getElementById('dropZone').classList.remove('drag-over');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = Array.from(dt.files);

            if (files.length > 0) {
                addFilesToTable(files);
            }
        }

        // Function to add files to table
        function addFilesToTable(files) {
            files.forEach(file => {
                // Validate file size (max 10MB)
                if (file.size > 10 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File Terlalu Besar',
                        text: `File "${file.name}" melebihi ukuran maksimal 10MB`,
                        timer: 3000
                    });
                    return;
                }

                // Validate file type
                const allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xlsx', 'xls'];
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExtension)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Format File Tidak Didukung',
                        text: `File "${file.name}" memiliki format yang tidak didukung`,
                        timer: 3000
                    });
                    return;
                }

                // Add file to array
                selectedFiles.push({
                    file: file,
                    location: ''
                });
            });

            renderFileTable();
        }

        // Function to render file table
        function renderFileTable() {
            const tbody = $('#fileTableBody');
            tbody.empty();

            if (selectedFiles.length === 0) {
                // No files - show drop zone, hide table and add button
                $('#dropZone').show();
                $('#addMoreButton').hide();
                $('#fileTableContainer').hide();
                $('#emptyState').show();
                $('#btnUpload').prop('disabled', true);
                $('#btnValidasi').prop('disabled', true);
                return;
            }

            // Has files - hide drop zone, show add button and table
            $('#dropZone').hide();
            $('#addMoreButton').show();
            $('#fileTableContainer').show();
            $('#emptyState').hide();
            $('#fileCount').text(selectedFiles.length);

            selectedFiles.forEach((fileObj, index) => {
                // Display location
                let locationDisplay = '';
                if (fileObj.location && fileObj.folderPath) {
                    locationDisplay = `
                        <div class="d-flex align-items-center">
                            <i class="bi bi-folder text-warning me-2"></i>
                            <small class="text-muted">${fileObj.folderPath}</small>
                        </div>
                    `;
                } else if (!fileObj.location) {
                    locationDisplay = '<span class="text-muted">-- Belum dipilih --</span>';
                }

                const row = `
                    <tr class="file-row">
                        <td class="text-center align-middle">${index + 1}</td>
                        <td class="align-middle">
                            <i class="bi bi-file-earmark-text text-primary me-2"></i>
                            <span class="fw-semibold">${fileObj.file.name}</span>
                        </td>
                        <td class="align-middle">${formatFileSize(fileObj.file.size)}</td>
                        <td class="align-middle">
                            <button type="button" class="btn btn-sm btn-outline-primary w-100" 
                                    onclick="openFolderModal(${index})">
                                <i class="bi bi-folder2-open me-1"></i> Pilih Folder
                            </button>
                            ${locationDisplay}
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="removeFile(${index})" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            // Check if all files have location selected
            updateUploadButton();
        }

        // Function to remove file
        function removeFile(index) {
            Swal.fire({
                title: 'Hapus File?',
                text: `Apakah Anda yakin ingin menghapus "${selectedFiles[index].file.name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    selectedFiles.splice(index, 1);
                    renderFileTable();
                }
            });
        }

        // Function to clear all files
        function clearAllFiles() {
            if (selectedFiles.length === 0) return;

            Swal.fire({
                title: 'Hapus Semua File?',
                text: 'Semua file yang dipilih akan dihapus',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Semua',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    selectedFiles = [];
                    uploadedFiles = [];
                    renderFileTable();
                }
            });
        }

        // Function to update upload button state
        function updateUploadButton() {
            // Check if there are new files or file replacements
            const hasNewFiles = selectedFiles.length > 0;
            const hasReplacements = Object.keys(fileReplacements).length > 0;

            // Check if all new files have folderId (location selected)
            const allNewFilesHaveLocation = hasNewFiles ? selectedFiles.every(fileObj => fileObj.folderId) : true;

            // Check if all replacements have folderId
            const allReplacementsHaveLocation = hasReplacements ?
                Object.values(fileReplacements).every(rep => rep.folderId) : true;

            // Enable button if:
            // 1. Has new files and all have location, OR
            // 2. Has replacements and all have location
            const canUpload = (hasNewFiles || hasReplacements) &&
                allNewFilesHaveLocation &&
                allReplacementsHaveLocation;

            $('#btnUpload').prop('disabled', !canUpload);

            console.log('Upload button state:', {
                hasNewFiles,
                hasReplacements,
                allNewFilesHaveLocation,
                allReplacementsHaveLocation,
                canUpload
            });
        }

        // ============================================
        // FILE REPLACEMENT FUNCTIONS (FOR REVISION MODE)
        // ============================================
        let fileReplacements = {}; // Object to store file replacements: {oldFileId: {file: File, folderId: string}}

        // Function to trigger file selection for replacement
        function pilihFilePengganti(fileId, fileName, oldFolderId) {
            console.log('Selecting replacement for file:', fileId, fileName, 'Old folder:', oldFolderId);

            // Store old folder ID for later use
            const fileInput = document.getElementById(`file-input-${fileId}`);
            fileInput.setAttribute('data-old-folder-id', oldFolderId);

            // Langsung buka file input tanpa konfirmasi
            fileInput.click();
        }

        // Handle file input change for replacement files
        $(document).on('change', '.file-replacement-input', function() {
            const fileInput = this;
            const fileId = $(this).data('file-id');
            const oldFileName = $(this).data('file-name');
            const oldFolderId = $(this).data('old-folder-id') || $(this).attr('data-old-folder-id');
            const file = fileInput.files[0];

            if (!file) return;

            console.log('File selected for replacement:', file.name, 'for file ID:', fileId, 'Old folder:',
                oldFolderId);

            // Validate file
            const maxSize = 10 * 1024 * 1024; // 10MB
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/jpg',
                'image/png',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            if (file.size > maxSize) {
                Swal.fire('Peringatan', 'Ukuran file tidak boleh lebih dari 10MB', 'warning');
                fileInput.value = '';
                return;
            }

            if (!allowedTypes.includes(file.type)) {
                Swal.fire('Peringatan', 'Format file tidak didukung', 'warning');
                fileInput.value = '';
                return;
            }

            // Store the replacement temporarily
            fileReplacements[fileId] = {
                file: file,
                oldFileName: oldFileName,
                oldFolderId: oldFolderId,
                folderId: null // Will be set when user selects folder
            };

            // Update UI to show replacement is ready
            $(`#status-badge-${fileId}`).removeClass('bg-secondary').addClass('bg-warning').text('Akan Diganti');

            // Show folder location choice
            showFolderLocationChoice(fileId, file.name, oldFolderId);
        });

        // Show choice: use same folder or select new folder
        function showFolderLocationChoice(fileId, fileName, oldFolderId) {
            Swal.fire({
                title: 'Pilih Lokasi Penyimpanan',
                html: `
                    <p class="mb-3">File baru: <strong>${fileName}</strong></p>
                    <p class="text-muted small mb-3">Pilih lokasi penyimpanan untuk file pengganti:</p>
                `,
                icon: 'question',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-folder-check me-1"></i> Lokasi yang Sama',
                denyButtonText: '<i class="bi bi-folder2-open me-1"></i> Pilih Lokasi Baru',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#0d6efd',
                denyButtonColor: '#6c757d',
                cancelButtonColor: '#dc3545',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Use same folder as old file
                    fileReplacements[fileId].folderId = oldFolderId;
                    $(`#status-badge-${fileId}`).removeClass('bg-warning').addClass('bg-success').text(
                        'Siap Upload');
                    console.log('Using same folder:', oldFolderId);

                    // Update upload button state
                    updateUploadButton();

                    Swal.fire({
                        icon: 'success',
                        title: 'Lokasi Dipilih',
                        text: 'File akan disimpan di lokasi yang sama dengan file lama',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else if (result.isDenied) {
                    // Select new folder
                    selectFolderForReplacement(fileId, fileName);
                } else {
                    // Cancelled - remove replacement
                    delete fileReplacements[fileId];
                    $(`#status-badge-${fileId}`).removeClass('bg-warning bg-success').addClass('bg-secondary').text(
                        'Current');
                    $(`#file-input-${fileId}`).val('');

                    // Update upload button state
                    updateUploadButton();
                }
            });
        }

        // Select folder for replacement file
        function selectFolderForReplacement(fileId, fileName) {
            // Show folder selection modal for this replacement
            currentReplacementFileId = fileId;

            Swal.fire({
                title: 'Pilih Lokasi Penyimpanan',
                html: `<p class="mb-3">Pilih folder untuk menyimpan file pengganti: <strong>${fileName}</strong></p>
                       <div id="folder-selector-replacement" class="text-start">
                           <div class="spinner-border text-primary" role="status">
                               <span class="visually-hidden">Loading...</span>
                           </div>
                       </div>`,
                showCancelButton: true,
                confirmButtonText: 'Konfirmasi',
                cancelButtonText: 'Batal',
                didOpen: () => {
                    loadFoldersForReplacement();
                },
                preConfirm: () => {
                    if (!fileReplacements[fileId].folderId) {
                        Swal.showValidationMessage('Pilih folder terlebih dahulu');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('Folder selected for replacement file:', fileReplacements[fileId].folderId);
                    // Replacement is ready - show success badge
                    $(`#status-badge-${fileId}`).removeClass('bg-warning').addClass('bg-success').text(
                        'Siap Upload');

                    // Update upload button state
                    updateUploadButton();
                } else {
                    // Cancelled - remove replacement
                    delete fileReplacements[fileId];
                    $(`#status-badge-${fileId}`).removeClass('bg-warning bg-success').addClass('bg-secondary').text(
                        'Current');
                    $(`#file-input-${fileId}`).val('');

                    // Update upload button state
                    updateUploadButton();
                }
            });
        }

        let currentReplacementFileId = null;

        // Load folders for replacement file selection
        function loadFoldersForReplacement() {
            $.ajax({
                url: '{{ route('terminaldata.foldersData.index') }}',
                type: 'GET',
                success: function(response) {
                    let folders = [];
                    if (Array.isArray(response)) {
                        folders = response;
                    } else if (response.data) {
                        folders = response.data;
                    }

                    renderFoldersForReplacement(folders);
                },
                error: function() {
                    $('#folder-selector-replacement').html('<p class="text-danger">Gagal memuat folder</p>');
                }
            });
        }

        // Render folders for replacement selection
        function renderFoldersForReplacement(folders) {
            let html = '<div class="list-group" style="max-height: 300px; overflow-y: auto;">';

            if (folders.length === 0) {
                html += '<p class="text-muted text-center py-3">Tidak ada folder tersedia</p>';
            } else {
                folders.forEach(folder => {
                    const folderName = folder.nama || folder.name || 'Unnamed';
                    html += `
                        <a href="javascript:void(0)" 
                           class="list-group-item list-group-item-action folder-select-item"
                           onclick="selectReplacementFolder('${folder.id}', '${folderName.replace(/'/g, "\\'")}')">
                            <i class="bi bi-folder-fill text-warning me-2"></i>${folderName}
                        </a>
                    `;
                });
            }

            html += '</div>';
            $('#folder-selector-replacement').html(html);
        }

        // Select folder for replacement file
        function selectReplacementFolder(folderId, folderName) {
            if (currentReplacementFileId && fileReplacements[currentReplacementFileId]) {
                fileReplacements[currentReplacementFileId].folderId = folderId;

                // Update UI to show selected folder
                $('.folder-select-item').removeClass('active');
                event.target.closest('.folder-select-item').classList.add('active');

                console.log('Folder selected:', folderId, folderName);
            }
        }

        // Function to upload files
        function uploadFiles() {
            // Check if there are new files or file replacements
            const hasNewFiles = selectedFiles.length > 0;
            const hasReplacements = Object.keys(fileReplacements).length > 0;

            if (!hasNewFiles && !hasReplacements) {
                Swal.fire('Peringatan', 'Belum ada file yang dipilih atau diganti', 'warning');
                return;
            }

            // Validate new files
            if (hasNewFiles) {
                const allHaveLocation = selectedFiles.every(fileObj => fileObj.folderId);
                if (!allHaveLocation) {
                    Swal.fire('Peringatan', 'Semua file harus memiliki folder tujuan', 'warning');
                    return;
                }
            }

            // Validate replacement files
            if (hasReplacements) {
                const allReplacementsHaveFolder = Object.values(fileReplacements).every(rep => rep.folderId);
                if (!allReplacementsHaveFolder) {
                    Swal.fire('Peringatan', 'Semua file pengganti harus memiliki folder tujuan', 'warning');
                    return;
                }
            }

            // Prepare FormData
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('tugas_id', '{{ $tugas->id }}');
            formData.append('jenis_tugas', '{{ $jenisTugas ?? 'tugas_harian' }}');
            formData.append('keterangan', $('#keterangan').val());

            // Add new files and folder IDs
            selectedFiles.forEach((fileObj, index) => {
                formData.append(`files[${index}]`, fileObj.file);
                formData.append(`folder_ids[${index}]`, fileObj.folderId);
            });

            // Add file replacements
            let replacementIndex = 0;
            for (const [oldFileId, replacement] of Object.entries(fileReplacements)) {
                formData.append(`replacements[${replacementIndex}][old_file_id]`, oldFileId);
                formData.append(`replacements[${replacementIndex}][file]`, replacement.file);
                formData.append(`replacements[${replacementIndex}][folder_id]`, replacement.folderId);
                replacementIndex++;
            }

            // Show upload progress
            const totalFiles = selectedFiles.length + Object.keys(fileReplacements).length;
            Swal.fire({
                title: 'Mengupload File...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mb-0">Sedang mengupload ${totalFiles} file</p>
                        ${hasReplacements ? `<small class="text-warning">${Object.keys(fileReplacements).length} file akan diganti</small><br>` : ''}
                        <small class="text-muted">Mohon tunggu...</small>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false
            });

            // Upload via AJAX
            $.ajax({
                url: '{{ route('penugasan.upload-bukti') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    // Upload progress
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = (evt.loaded / evt.total) * 100;
                            console.log('Upload progress: ' + percentComplete.toFixed(2) + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    uploadedFiles = response.files || [];

                    let successMessage = `${totalFiles} file berhasil diupload`;
                    if (hasReplacements) {
                        successMessage +=
                            ` (${Object.keys(fileReplacements).length} file diganti dengan versi baru)`;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Upload Berhasil!',
                        html: `
                            <p class="mb-2">${successMessage}</p>
                            <small class="text-muted">Sekarang Anda dapat meminta validasi dari atasan</small>
                        `,
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        // Reload page to show updated file list
                        window.location.reload();
                    });

                    // Enable validasi button
                    $('#btnValidasi').prop('disabled', false);
                    $('#btnUpload').prop('disabled', true);

                    // Disable editing
                    $('.location-select').prop('disabled', true);
                    $('.btn-outline-danger').prop('disabled', true);
                    $('#fileInput').prop('disabled', true);
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat upload file';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join('<br>');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Gagal',
                        html: errorMessage
                    });
                }
            });
        }

        // Function to request validation
        function requestValidasi() {
            if (uploadedFiles.length === 0) {
                Swal.fire('Peringatan', 'Anda harus upload file terlebih dahulu', 'warning');
                return;
            }

            Swal.fire({
                title: 'Minta Validasi?',
                html: `
                    <p>Anda akan meminta validasi untuk tugas ini</p>
                    <div class="alert alert-warning text-start small mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Setelah validasi diminta, status tugas akan berubah menjadi "Validasi" 
                        dan akan ditinjau oleh atasan Anda.
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Minta Validasi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const jenisTugas = '{{ $jenisTugas ?? 'tugas_harian' }}';
                    const updateStatusUrl = jenisTugas === 'tugas_harian' ?
                        `/penugasan/tugas-harian/{{ $tugas->id }}/update-status` :
                        `/penugasan/tugas-tambahan/{{ $tugas->id }}/update-status`;

                    $.ajax({
                        url: updateStatusUrl,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: 'validasi'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Memproses...',
                                text: 'Sedang mengirim permintaan validasi',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Permintaan validasi berhasil dikirim',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href =
                                    '{{ route('penugasan.show', $tugas->pegawai_id) }}';
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'Terjadi kesalahan saat mengirim permintaan validasi';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: errorMessage
                            });
                        }
                    });
                }
            });
        }

        // Helper function to format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
        }

        // ============================================
        // FOLDER SELECTION MODAL FUNCTIONS
        // ============================================
        let currentFileIndex = null;
        let currentFolderId = null;
        let folderHistory = [];
        let allFolders = [];

        // Open folder modal
        function openFolderModal(fileIndex) {
            currentFileIndex = fileIndex;
            currentFolderId = null;
            folderHistory = [];

            // Reset breadcrumb
            $('#currentPath').text('ROOT');

            // Disable back button
            $('#btnBack').prop('disabled', true);

            console.log('Opening folder modal for file index:', fileIndex);
            console.log('Reset: currentFolderId =', currentFolderId, ', folderHistory =', folderHistory);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('modalPilihFolder'));
            modal.show();

            // Load root folders
            loadFolders(null);
        }

        // Load folders from API
        function loadFolders(parentId = null) {
            console.log('=== loadFolders called ===');
            console.log('Parent ID:', parentId);
            console.log('Current folder ID:', currentFolderId);
            console.log('Current history:', folderHistory);

            $('#folderLoading').show();
            $('#folderList').hide();
            $('#folderEmpty').hide();

            // Call API endpoint
            $.ajax({
                url: '{{ route('terminaldata.foldersData.index') }}',
                type: 'GET',
                data: {
                    parent_id: parentId
                },
                success: function(response) {
                    console.log('=== API Response received ===');
                    console.log('Response:', response);
                    console.log('Parent ID used in request:', parentId);

                    // Handle different response structures
                    let folders = [];
                    if (Array.isArray(response)) {
                        folders = response;
                    } else if (response.data && Array.isArray(response.data)) {
                        folders = response.data;
                    } else if (response.folders && Array.isArray(response.folders)) {
                        folders = response.folders;
                    }

                    console.log('Folders extracted:', folders);
                    console.log('Number of folders:', folders.length);
                    allFolders = folders;
                    renderFolders(folders);
                },
                error: function(xhr) {
                    console.error('Error loading folders:', xhr);
                    $('#folderLoading').hide();
                    $('#folderList').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memuat Folder',
                        text: xhr.responseJSON?.message || 'Tidak dapat memuat daftar folder',
                        timer: 3000
                    });
                }
            });
        }

        // Render folders in list
        function renderFolders(folders) {
            console.log('=== renderFolders called ===');
            console.log('Folders parameter:', folders);
            console.log('Is array?', Array.isArray(folders));
            console.log('Length:', folders?.length);

            $('#folderLoading').hide();
            const folderList = $('#folderList');
            folderList.empty();

            if (!folders || folders.length === 0) {
                console.log('No folders to display - showing empty message');
                $('#folderList').hide();
                $('#folderEmpty').show();
                return;
            }

            console.log('Showing folder list');
            $('#folderList').show();
            $('#folderEmpty').hide();

            folders.forEach((folder, index) => {
                const hasSubfolders = (folder.subfolders_count || 0) > 0;
                const folderName = folder.nama || folder.name || 'Unnamed Folder';
                const folderId = folder.id;

                console.log(`Folder ${index + 1}:`, {
                    name: folderName,
                    id: folderId,
                    hasSubfolders: hasSubfolders,
                    subfolders_count: folder.subfolders_count
                });

                const folderItem = `
                    <div class="folder-item" 
                         onclick="selectFolder('${folderId}', '${folderName.replace(/'/g, "\\'")}', ${hasSubfolders})" 
                         ondblclick="navigateIntoFolder('${folderId}', '${folderName.replace(/'/g, "\\'")}')"
                         data-folder-id="${folderId}">
                        <i class="bi bi-folder-fill text-warning"></i>
                        <div class="folder-name">${folderName}</div>
                        <div class="d-flex align-items-center">
                            ${hasSubfolders ? 
                                `<span class="badge bg-secondary me-2">${folder.subfolders_count} folder</span>` : ''}
                            ${hasSubfolders ? '<i class="bi bi-chevron-right folder-arrow"></i>' : ''}
                        </div>
                    </div>
                `;
                folderList.append(folderItem);
            });

            console.log(`Successfully rendered ${folders.length} folders`);
        }

        // Select folder
        function selectFolder(folderId, folderName, hasSubfolders) {
            // Remove active class from all items
            $('.folder-item').removeClass('active');

            // Add active class to selected item
            $(`.folder-item[data-folder-id="${folderId}"]`).addClass('active');

            // Set current folder
            currentFolderId = folderId;

            // If double click or has subfolders, navigate into it
            // For now, just mark as selected
        }

        // Navigate into folder
        function navigateIntoFolder(folderId, folderName) {
            console.log('Navigating into folder:', folderName, 'ID:', folderId);
            console.log('Current folder ID before navigation:', currentFolderId);
            console.log('Current history before navigation:', folderHistory);

            // Push current folder to history before navigating
            folderHistory.push({
                id: folderId,
                name: folderName
            });

            // Update current folder ID
            currentFolderId = folderId;

            console.log('New current folder ID:', currentFolderId);
            console.log('New history:', folderHistory);

            // Update breadcrumb
            updateBreadcrumb();

            // Load subfolders
            loadFolders(folderId);

            // Enable back button
            $('#btnBack').prop('disabled', false);
        }

        // Navigate back to parent folder
        function navigateBack() {
            console.log('Navigating back');
            console.log('Current history:', folderHistory);

            if (folderHistory.length === 0) return;

            // Remove current folder from history
            folderHistory.pop();

            console.log('History after pop:', folderHistory);

            // Get parent folder ID
            if (folderHistory.length === 0) {
                // Back to root
                currentFolderId = null;
                loadFolders(null);
                $('#btnBack').prop('disabled', true);
            } else {
                // Back to parent
                const parent = folderHistory[folderHistory.length - 1];
                currentFolderId = parent.id;
                loadFolders(currentFolderId);
            }

            console.log('New current folder ID:', currentFolderId);

            // Update breadcrumb
            updateBreadcrumb();
        }

        // Update breadcrumb path
        function updateBreadcrumb() {
            let pathText = 'ROOT';

            if (folderHistory.length > 0) {
                pathText = 'ROOT / ' + folderHistory.map(f => f.name).join(' / ');
            }

            $('#currentPath').text(pathText);
            console.log('Breadcrumb updated to:', pathText);
        }

        // Confirm folder selection
        function confirmFolderSelection() {
            if (!currentFolderId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Folder Belum Dipilih',
                    text: 'Silakan pilih folder terlebih dahulu',
                    timer: 2000
                });
                return;
            }

            // Get folder path from breadcrumb
            let folderPath = $('#currentPath').text();

            console.log('Selected folder path:', folderPath);
            console.log('Selected folder ID:', currentFolderId);

            // Update file location
            selectedFiles[currentFileIndex].location = 'terminal_data';
            selectedFiles[currentFileIndex].folderId = currentFolderId;
            selectedFiles[currentFileIndex].folderPath = folderPath;

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('modalPilihFolder')).hide();

            // Re-render table
            renderFileTable();

            // Show success notification
            Swal.fire({
                icon: 'success',
                title: 'Folder Dipilih',
                text: `File akan disimpan di: ${folderPath}`,
                timer: 2000,
                toast: true,
                position: 'top-end',
                showConfirmButton: false
            });
        }
    </script>
@endpush
