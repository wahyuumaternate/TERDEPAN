@extends('terminaldata::components.layouts.master')

@section('main')
    <div class="pagetitle">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('terminaldata.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('terminaldata.folders.index') }}">Folders</a></li>
                @if (isset($folder))
                    @foreach ($folder->getBreadcrumb() as $crumb)
                        @if ($loop->last)
                            <li class="breadcrumb-item active">{{ $crumb->name }}</li>
                        @else
                            <li class="breadcrumb-item">
                                <a href="{{ route('terminaldata.folder.detail', $crumb->id) }}">{{ $crumb->name }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            </ol>
        </nav>
        <h1><i class="bi bi-folder-fill text-primary me-2"></i>{{ $folder->name ?? 'Folder' }}</h1>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="mb-3">
                    <a href="{{ route('terminaldata.folders.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                </div>

                <!-- Dokumen Section -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="card-title mb-1 d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 rounded-3 p-2 me-3">
                                        <i class="bi bi-file-earmark-text-fill text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Dokumen</span>
                                        <small class="d-block text-muted fw-normal mt-1">Dokumen yang tersimpan di folder
                                            ini</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <button type="button" class="btn btn-success shadow-sm me-2" data-bs-toggle="modal"
                                    data-bs-target="#modalTambahFolder">
                                    <i class="bi bi-folder-plus me-2"></i>Folder Baru
                                </button>
                                <form id="formUploadDokumen" style="display: inline-block;">
                                    @csrf
                                    <input type="hidden" name="folder_id" value="{{ $folder->id }}">
                                    <input type="file" id="fileUpload" name="file" style="display: none;"
                                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" multiple>
                                    <button type="button" class="btn btn-primary shadow-sm" id="btnTriggerUpload">
                                        <i class="bi bi-cloud-upload me-2"></i>Upload Dokumen
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Filter Section (Collapsible) -->
                        <div class="row mb-4" id="filterSection" style="display: none;">
                            <div class="col-12">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" id="filterStatus">
                                                    <option value="">Semua Status</option>
                                                    <option value="Draft">Draft</option>
                                                    <option value="Final">Final</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Pencarian</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="searchDokumen"
                                                        placeholder="Cari dokumen...">
                                                    <button class="btn btn-outline-primary" type="button" id="btnSearch">
                                                        <i class="bi bi-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-12 text-end">
                                                <button class="btn btn-sm btn-outline-secondary" id="resetFilter">
                                                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- View Toggle -->
                        <div class="mb-3">
                            <div class="btn-group shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="viewMode" id="viewGrid" checked>
                                <label class="btn btn-outline-primary px-4" for="viewGrid">
                                    <i class="bi bi-grid-3x3-gap me-2"></i>Grid View
                                </label>
                                <input type="radio" class="btn-check" name="viewMode" id="viewTable">
                                <label class="btn btn-outline-primary px-4" for="viewTable">
                                    <i class="bi bi-table me-2"></i>Table View
                                </label>
                            </div>
                        </div>

                        <!-- Grid View -->
                        <div id="gridView">
                            <!-- Subfolders Section -->
                            <div id="subfoldersSection" style="display: none;">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-folder me-2"></i>Subfolder (<span id="subfolderCount">0</span>)
                                </h6>
                                <!-- Skeleton Loading for Subfolders -->
                                <div class="row g-3 mb-3" id="subfoldersSkeleton">
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                        <div class="skeleton-folder-card">
                                            <div class="skeleton-folder-content">
                                                <div class="skeleton skeleton-folder-icon"></div>
                                                <div class="skeleton-folder-info">
                                                    <div class="skeleton skeleton-title"></div>
                                                    <div class="skeleton skeleton-text"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                        <div class="skeleton-folder-card">
                                            <div class="skeleton-folder-content">
                                                <div class="skeleton skeleton-folder-icon"></div>
                                                <div class="skeleton-folder-info">
                                                    <div class="skeleton skeleton-title"></div>
                                                    <div class="skeleton skeleton-text"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                        <div class="skeleton-folder-card">
                                            <div class="skeleton-folder-content">
                                                <div class="skeleton skeleton-folder-icon"></div>
                                                <div class="skeleton-folder-info">
                                                    <div class="skeleton skeleton-title"></div>
                                                    <div class="skeleton skeleton-text"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                        <div class="skeleton-folder-card">
                                            <div class="skeleton-folder-content">
                                                <div class="skeleton skeleton-folder-icon"></div>
                                                <div class="skeleton-folder-info">
                                                    <div class="skeleton skeleton-title"></div>
                                                    <div class="skeleton skeleton-text"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3" id="subfoldersGrid"></div>
                                <hr class="my-4">
                            </div>

                            <!-- Documents Section -->
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-file-earmark-text me-2"></i>Dokumen (<span
                                    id="documentCount">{{ $files ? count($files) : '0' }}</span>)
                            </h6>
                            @if (count($files) > 0)
                                <div class="row g-3" id="dokumenGrid">
                                    @foreach ($files as $item)
                                        @php
                                            // Support both TdFile and Dokumen structure
                                            // TdFile has storage_path, while Dokumen has files relationship
                                            if (isset($item->storage_path) || isset($item->extension)) {
                                                // TdFile structure (direct file)
                                                $extension = $item->extension ?? 'pdf';
                                                $fileName = $item->name ?? $item->original_name;
                                                $fileSize = round($item->size / 1024, 2); // Convert bytes to KB
                                            } else {
                                                // Dokumen structure (multiple versions)
                                                $currentFile = null;
                                                if (isset($item->files) && $item->files) {
                                                    $currentFile =
                                                        $item->files->where('is_current', true)->first() ??
                                                        $item->files->first();
                                                }

                                                if ($currentFile) {
                                                    $extension = $currentFile->extension ?? 'pdf';
                                                    $fileName = $item->judul ?? ($item->name ?? 'Unknown');
                                                    $fileSize = $currentFile->ukuran_kb ?? 0;
                                                } else {
                                                    // Fallback if no files found
                                                    continue;
                                                }
                                            }

                                            $fileIcon = 'bi-file-earmark-text-fill';
                                            $iconColor = '#6c757d';

                                            if ($extension === 'pdf') {
                                                $fileIcon = 'bi-file-earmark-pdf-fill';
                                                $iconColor = '#dc3545';
                                            } elseif (in_array($extension, ['doc', 'docx'])) {
                                                $fileIcon = 'bi-file-earmark-word-fill';
                                                $iconColor = '#0d6efd';
                                            } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                                $fileIcon = 'bi-file-earmark-excel-fill';
                                                $iconColor = '#198754';
                                            } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                $fileIcon = 'bi-file-earmark-image-fill';
                                                $iconColor = '#fd7e14';
                                            }
                                        @endphp
                                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                            <div class="gdrive-card" onclick="showFileDetail({{ $item->id }})">
                                                <div class="gdrive-card-preview">
                                                    <i class="bi {{ $fileIcon }}"
                                                        style="color: {{ $iconColor }}; font-size: 4rem;"></i>
                                                </div>
                                                <div class="gdrive-card-body">
                                                    <div class="gdrive-card-title" title="{{ $fileName }}">
                                                        {{ $fileName }}
                                                    </div>
                                                    <div class="gdrive-card-meta">
                                                        <small class="text-muted">
                                                            {{ number_format($fileSize, 0) }} KB
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="gdrive-card-actions">
                                                    <div class="gdrive-folder-menu" onclick="event.stopPropagation();">
                                                        <button class="btn btn-link" type="button"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item"
                                                                    href="{{ route('terminaldata.filesData.download', $item->id) }}"
                                                                    onclick="event.stopPropagation()">
                                                                    <i class="bi bi-download me-2"></i>Unduh
                                                                </a></li>
                                                            <li><a class="dropdown-item" href="#"
                                                                    onclick="editFile({{ $item->id }}); return false;">
                                                                    <i class="bi bi-pencil-square me-2"></i>Ganti Nama
                                                                </a></li>
                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>
                                                            <li><a class="dropdown-item" href="#"
                                                                    onclick="alert('Fitur salin link dalam pengembangan'); return false;">
                                                                    <i class="bi bi-link-45deg me-2"></i>Salin Link
                                                                </a></li>
                                                            <li><a class="dropdown-item" href="#"
                                                                    onclick="showFileDetail({{ $item->id }}); return false;">
                                                                    <i class="bi bi-info-circle me-2"></i>Informasi File
                                                                </a></li>
                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>
                                                            <li><a class="dropdown-item text-danger" href="#"
                                                                    onclick="deleteFile({{ $item->id }}); return false;">
                                                                    <i class="bi bi-trash me-2"></i>Pindahkan ke Sampah
                                                                </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-state text-center">
                                    <i class="bi bi-file-earmark-x empty-state-icon"></i>
                                    <h4 class="text-muted mb-2">Belum ada dokumen</h4>
                                    <p class="text-muted mb-4">Mulai dengan upload dokumen pertama Anda</p>
                                    <button class="btn btn-primary btn-lg px-5" id="btnTriggerUpload">
                                        <i class="bi bi-cloud-upload me-2"></i>Upload Dokumen
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Table View -->
                        <div id="tableView" style="display: none;">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-folder-fill me-2"></i>Folder & Dokumen
                            </h6>
                            <table class="table" id="combinedTable">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Pemilik</th>
                                        <th>Tanggal Diubah</th>
                                        <th>Ukuran/Total</th>
                                        <th width="80">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="combinedTableBody">
                                    <!-- Will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upload Progress Modal -->
    <div class="modal fade" id="modalUploadProgress" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-cloud-upload text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="mb-3">Mengupload Dokumen</h5>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar"
                            style="width: 0%" id="uploadProgressBar"></div>
                    </div>
                    <p class="text-muted mb-0" id="uploadProgressText">Mempersiapkan upload...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit (similar structure to Upload modal with improvements) -->
    <div class="modal fade" id="modalEditDokumen" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Dokumen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formEditDokumen" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_dokumen_id" name="dokumen_id">
                        <input type="hidden" name="folder_id" value="{{ $folder->id }}">

                        <div class="mb-4">
                            <div class="alert alert-warning d-flex">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                <div>
                                    <strong>Perhatian:</strong> Dokumen akan tetap disimpan di folder
                                    <strong>{{ $folder->nama }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_judul" class="form-label fw-semibold">
                                Judul Dokumen <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="edit_judul" name="judul"
                                required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_nomor_surat" class="form-label fw-semibold">Nomor Surat</label>
                                <input type="text" class="form-control" id="edit_nomor_surat" name="nomor_surat">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_tanggal_dokumen" class="form-label fw-semibold">
                                    Tanggal Dokumen <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="edit_tanggal_dokumen"
                                    name="tanggal_dokumen" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_deskripsi" class="form-label fw-semibold">Deskripsi</label>
                            <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="edit_status" class="form-label fw-semibold">Status Dokumen</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="edit_statusDraft"
                                        value="Draft">
                                    <label class="form-check-label" for="edit_statusDraft">
                                        Draft
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="edit_statusFinal"
                                        value="Final">
                                    <label class="form-check-label" for="edit_statusFinal">
                                        Final
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status"
                                        id="edit_statusArchived" value="Archived">
                                    <label class="form-check-label" for="edit_statusArchived">
                                        Archived
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">File Saat Ini</label>
                            <div id="current_file_info" class="alert alert-info d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div>Memuat informasi file...</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_file" class="form-label fw-semibold">Upload File Baru (Opsional)</label>
                            <input type="file" class="form-control" id="edit_file" name="file">
                            <div class="form-text" id="editFileHelp">
                                Format: PDF, DOCX, XLSX (Max: 10MB). Kosongkan jika tidak ingin mengganti file.
                            </div>
                            <div class="alert alert-light mt-2 p-2 small">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                Mengganti file akan membuat versi baru dokumen
                            </div>
                        </div>

                        <div id="editProgress" class="mt-3" style="display: none;">
                            <label class="form-label fw-semibold">Proses Update</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: 0%"></div>
                            </div>
                            <p class="text-center mt-2 mb-0" id="editProgressText">Mempersiapkan update...</p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-warning px-4" id="btnUpdateDokumen">
                        <i class="bi bi-save me-1"></i>Update Dokumen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetailDokumen" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-text me-2"></i>Detail Dokumen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="detailDokumenContent">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat detail dokumen...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Tutup
                        </button>
                        <button type="button" class="btn btn-warning" id="btnEditFromDetail" data-id="">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                        <button type="button" class="btn btn-primary" id="btnDownloadDokumen" data-id="">
                            <i class="bi bi-download me-1"></i>Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Subfolder -->
    <div class="modal fade" id="modalTambahFolder" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-success text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-folder-plus me-2"></i>Folder Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formTambahFolder">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $folder->id }}">

                        <div class="mb-3">
                            <label for="nama_folder" class="form-label fw-semibold">
                                <i class="bi bi-folder me-2"></i>Nama<span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="nama_folder" name="nama"
                                placeholder="Contoh: Laporan 2025" required maxlength="100">
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-success px-4" id="btnSaveFolder">
                        <i class="bi bi-check-circle me-1"></i>Buat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upload Progress (Bottom Right) -->
    <div id="uploadProgressModal" style="display: none;">
        <div class="upload-modal-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong id="uploadModalTitle">Mengupload <span id="uploadCount">0</span> file</strong>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-link text-white p-0" id="minimizeUpload">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-white p-0 ms-2" id="closeUpload">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="upload-modal-body" id="uploadModalBody">
            <!-- Upload items will be added here dynamically -->
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Google Drive Style Folder Cards */
        .gdrive-folder-card {
            background: white;
            border: 1px solid #dadce0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            overflow: visible;
            position: relative;
        }

        .gdrive-folder-card:hover {
            box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 2px 6px 2px rgba(60, 64, 67, .15);
            border-color: transparent;
        }

        .gdrive-folder-content {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            gap: 12px;
        }

        .gdrive-folder-icon {
            flex-shrink: 0;
        }

        .gdrive-folder-icon i {
            font-size: 24px;
            color: #5f6368;
        }

        .gdrive-folder-card:hover .gdrive-folder-icon i {
            color: #1a73e8;
        }

        .gdrive-folder-info {
            flex: 1;
            min-width: 0;
        }

        .gdrive-folder-title {
            font-size: 14px;
            color: #202124;
            font-weight: 400;
            line-height: 20px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 2px;
        }

        .gdrive-folder-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
        }

        .gdrive-folder-meta small,
        .gdrive-folder-meta span {
            font-size: 12px;
            color: #5f6368;
        }

        .gdrive-folder-meta .text-muted {
            color: #5f6368 !important;
        }

        .gdrive-folder-menu {
            opacity: 0;
            transition: opacity 0.2s ease;
            flex-shrink: 0;
        }

        .gdrive-folder-card:hover .gdrive-folder-menu {
            opacity: 1;
        }

        .gdrive-folder-menu .btn-link {
            color: #5f6368;
            text-decoration: none;
            padding: 4px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gdrive-folder-menu .btn-link:hover {
            background-color: #f1f3f4;
            color: #202124;
        }

        /* Dropdown menu styling */
        .gdrive-folder-menu .dropdown-menu {
            border: 1px solid #dadce0;
            box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 2px 6px 2px rgba(60, 64, 67, .15);
            border-radius: 8px;
            padding: 8px 0;
            min-width: 160px;
        }

        .gdrive-folder-menu .dropdown-item {
            padding: 10px 16px;
            font-size: 14px;
            color: #202124;
        }

        .gdrive-folder-menu .dropdown-item:hover {
            background-color: #f1f3f4;
        }

        .gdrive-folder-menu .dropdown-item i {
            width: 20px;
            text-align: center;
        }

        .gdrive-folder-menu .dropdown-divider {
            margin: 8px 0;
            border-color: #e8eaed;
        }

        /* Google Drive Style Document Cards */
        .gdrive-card {
            background: white;
            border: 1px solid #e8eaed;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .gdrive-card:hover {
            box-shadow: 0 1px 3px 0 rgba(60, 64, 67, .3), 0 4px 8px 3px rgba(60, 64, 67, .15);
            border-color: transparent;
        }

        .gdrive-card-preview {
            background: #f1f3f4;
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #e8eaed;
            min-height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gdrive-card-preview i {
            font-size: 64px;
        }

        .gdrive-card-body {
            padding: 12px 16px;
            flex: 1;
        }

        .gdrive-card-title {
            font-size: 14px;
            color: #202124;
            font-weight: 400;
            line-height: 20px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 8px;
        }

        .gdrive-card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .gdrive-card-meta small {
            font-size: 12px;
            color: #5f6368;
        }

        .gdrive-card-actions {
            padding: 8px 12px;
            border-top: 1px solid #e8eaed;
            display: flex;
            gap: 4px;
            opacity: 1;
            transition: opacity 0.2s ease;
            position: relative;
            z-index: 10;
        }

        .gdrive-card:hover .gdrive-card-actions {
            opacity: 1;
        }

        .gdrive-card-actions .gdrive-folder-menu {
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }

        .gdrive-card-actions .gdrive-folder-menu .btn-link {
            color: #5f6368;
            text-decoration: none;
            padding: 4px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gdrive-card-actions .gdrive-folder-menu .btn-link:hover {
            background-color: #f1f3f4;
            color: #202124;
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
        }

        .empty-state-icon {
            font-size: 6rem;
            color: #e0e0e0;
            margin-bottom: 2rem;
        }

        /* Table Styles */
        .datatable-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        /* Skeleton Loading Styles */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s ease-in-out infinite;
            border-radius: 4px;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .skeleton-card {
            background: white;
            border: 1px solid #e8eaed;
            border-radius: 8px;
            height: 100%;
            overflow: hidden;
        }

        .skeleton-preview {
            height: 140px;
            background: #f1f3f4;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #e8eaed;
        }

        .skeleton-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(90deg, #e0e0e0 25%, #d0d0d0 50%, #e0e0e0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s ease-in-out infinite;
            border-radius: 8px;
        }

        .skeleton-body {
            padding: 12px;
        }

        .skeleton-title {
            height: 16px;
            width: 70%;
            margin-bottom: 8px;
        }

        .skeleton-text {
            height: 12px;
            width: 40%;
        }

        .skeleton-folder-card {
            background: white;
            border: 1px solid #e8eaed;
            border-radius: 8px;
            padding: 16px;
            height: 100%;
        }

        .skeleton-folder-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .skeleton-folder-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }

        .skeleton-folder-info {
            flex: 1;
        }

        .skeleton-table-row {
            height: 53px;
            border-bottom: 1px solid #e8eaed;
        }

        .skeleton-table-cell {
            padding: 12px;
            vertical-align: middle;
        }

        .skeleton-table-icon {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            display: inline-block;
        }

        .skeleton-table-text {
            height: 16px;
            display: inline-block;
            vertical-align: middle;
        }

        /* Upload Progress Modal */
        #uploadProgressModal {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 400px;
            max-height: 500px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        #uploadProgressModal.minimized .upload-modal-body {
            display: none;
        }

        #uploadProgressModal.minimized {
            max-height: 60px;
        }

        .upload-modal-header {
            background: #0d6efd;
            color: white;
            padding: 12px 16px;
            font-weight: 500;
            cursor: pointer;
        }

        .upload-modal-body {
            max-height: 400px;
            overflow-y: auto;
            padding: 12px;
        }

        .upload-item {
            padding: 12px;
            border-bottom: 1px solid #e8eaed;
            transition: background-color 0.2s;
        }

        .upload-item:last-child {
            border-bottom: none;
        }

        .upload-item:hover {
            background-color: #f8f9fa;
        }

        .upload-item-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            background: #f1f3f4;
        }

        .upload-item-progress {
            height: 4px;
            background: #e8eaed;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 8px;
        }

        .upload-item-progress-bar {
            height: 100%;
            background: #0d6efd;
            transition: width 0.3s ease;
        }

        .upload-item-progress-bar.success {
            background: #198754;
        }

        .upload-item-progress-bar.error {
            background: #dc3545;
        }

        .upload-item-status {
            font-size: 12px;
            margin-top: 4px;
        }

        .upload-item-status.success {
            color: #198754;
        }

        .upload-item-status.error {
            color: #dc3545;
        }

        .upload-item-status.processing {
            color: #0d6efd;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let dataTable = null;
        let subfolders = [];
        let documents = @json($files);
        let uploadQueue = [];
        let activeUploads = 0;
        const MAX_CONCURRENT_UPLOADS = 3;

        $(document).ready(function() {
            console.log('Document ready');
            console.log('Files/Documents data:', documents);

            // Load subfolders
            loadSubfolders();

            // Trigger file upload when button clicked
            $('#btnTriggerUpload').click(function() {
                $('#fileUpload').click();
            });

            // Handle file selection - Multiple files
            $('#fileUpload').on('change', function() {
                let files = this.files;
                if (files.length > 0) {
                    // Validate all files
                    const maxSize = 50 * 1024 * 1024; // 50MB in bytes
                    let hasError = false;

                    for (let i = 0; i < files.length; i++) {
                        if (files[i].size > maxSize) {
                            Swal.fire({
                                icon: 'error',
                                title: 'File Terlalu Besar',
                                text: `File "${files[i].name}" melebihi batas 50MB`,
                                confirmButtonColor: '#0d6efd'
                            });
                            hasError = true;
                            break;
                        }
                    }

                    if (!hasError) {
                        // Show upload modal
                        $('#uploadProgressModal').fadeIn();

                        // Add files to upload queue
                        for (let i = 0; i < files.length; i++) {
                            uploadQueue.push(files[i]);
                            addUploadItem(files[i]);
                        }

                        // Update count
                        updateUploadCount();

                        // Start processing queue
                        processUploadQueue();
                    }

                    // Reset file input
                    $(this).val('');
                }
            });

            // Upload modal controls
            $('#minimizeUpload').click(function() {
                $('#uploadProgressModal').toggleClass('minimized');
            });

            $('#closeUpload').click(function() {
                if (activeUploads > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Upload sedang berjalan',
                        text: 'Masih ada file yang sedang diupload. Tutup tetap?',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Tutup',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#uploadProgressModal').fadeOut();
                            uploadQueue = [];
                        }
                    });
                } else {
                    $('#uploadProgressModal').fadeOut();
                }
            });

            // Toggle filter section
            $('#toggleFilter').click(function() {
                $('#filterSection').slideToggle();
            });

            // Reset filter
            $('#resetFilter').click(function() {
                $('#filterStatus').val('');
                $('#searchDokumen').val('');
            });

            // View mode toggle
            $('input[name="viewMode"]').change(function() {
                let viewMode = $(this).attr('id');
                if (viewMode === 'viewGrid') {
                    $('#gridView').show();
                    $('#tableView').hide();

                    if (dataTable) {
                        dataTable.destroy();
                        dataTable = null;
                    }
                } else {
                    $('#tableView').show();
                    $('#gridView').hide();

                    populateCombinedTable();
                }
            });

            // Reset edit form when modal is closed
            $('#modalEditDokumen').on('hidden.bs.modal', function() {
                $('#formEditDokumen')[0].reset();
                $('#editProgress').hide();
                $('#editProgress .progress-bar').css('width', '0%');
                $('#editProgressText').text('Mempersiapkan update...');
            });

            // Reset folder form when modal is closed
            $('#modalTambahFolder').on('hidden.bs.modal', function() {
                $('#formTambahFolder')[0].reset();
            });

            // Save Folder
            $('#btnSaveFolder').click(function() {
                console.log('Button clicked');
                let form = $('#formTambahFolder')[0];

                // Basic form validation
                if (!form.checkValidity()) {
                    console.log('Form validation failed');
                    form.reportValidity();
                    return;
                }

                let formData = {
                    _token: '{{ csrf_token() }}',
                    parent_id: '{{ $folder->id }}',
                    name: $('#nama_folder').val()
                };

                console.log('Form data:', formData);

                $.ajax({
                    url: '{{ route('terminaldata.foldersData.store') }}',
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        console.log('AJAX starting...');
                        $('#btnSaveFolder').prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...'
                        );
                    },
                    success: function(response) {
                        console.log('AJAX success:', response);
                        $('#modalTambahFolder').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Subfolder berhasil dibuat',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload to show new subfolder
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.log('AJAX error:', xhr.status, xhr.responseJSON);
                        $('#btnSaveFolder').prop('disabled', false).html(
                            '<i class="bi bi-check-circle me-1"></i>Buat'
                        );

                        let errorMessage = 'Terjadi kesalahan';

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            errorMessage = '<ul class="text-start">';
                            Object.keys(errors).forEach(key => {
                                errors[key].forEach(error => {
                                    errorMessage += `<li>${error}</li>`;
                                });
                            });
                            errorMessage += '</ul>';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: errorMessage,
                            confirmButtonColor: '#0d6efd'
                        });
                    },
                    complete: function() {
                        $('#btnSaveFolder').prop('disabled', false).html(
                            '<i class="bi bi-check-circle me-1"></i>Buat'
                        );
                    }
                });
            });
        });

        // ============================================
        // UPLOAD QUEUE FUNCTIONS
        // ============================================

        function addUploadItem(file) {
            const uploadId = generateUploadId();
            const fileSize = formatFileSize(file.size);
            const extension = file.name.split('.').pop().toLowerCase();

            let iconClass = 'bi-file-earmark-text-fill text-secondary';
            if (extension === 'pdf') iconClass = 'bi-file-earmark-pdf-fill text-danger';
            else if (['doc', 'docx'].includes(extension)) iconClass = 'bi-file-earmark-word-fill text-primary';
            else if (['xls', 'xlsx'].includes(extension)) iconClass = 'bi-file-earmark-excel-fill text-success';

            const html = `
                <div class="upload-item" data-upload-id="${uploadId}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="upload-item-icon">
                            <i class="bi ${iconClass}" style="font-size: 20px;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium text-truncate" style="max-width: 280px;">${file.name}</div>
                            <div class="text-muted small">${fileSize}</div>
                            <div class="upload-item-progress">
                                <div class="upload-item-progress-bar" style="width: 0%"></div>
                            </div>
                            <div class="upload-item-status processing">
                                <i class="bi bi-hourglass-split"></i> Menunggu...
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#uploadModalBody').append(html);

            // Store upload ID with file
            file.uploadId = uploadId;
        }

        function updateUploadCount() {
            const total = uploadQueue.length + activeUploads;
            $('#uploadCount').text(total);

            if (total === 0) {
                setTimeout(() => {
                    $('#uploadProgressModal').fadeOut();
                    $('#uploadModalBody').empty();
                }, 2000);
            }
        }

        function processUploadQueue() {
            while (activeUploads < MAX_CONCURRENT_UPLOADS && uploadQueue.length > 0) {
                const file = uploadQueue.shift();
                uploadFile(file);
            }
        }

        function uploadFile(file) {
            activeUploads++;
            const uploadId = file.uploadId;

            updateUploadStatus(uploadId, 'processing', 'Mengupload...');

            const formData = new FormData();
            formData.append('file', file);
            formData.append('folder_id', '{{ $folder->id }}');
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '{{ route('terminaldata.filesData.upload') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            updateUploadProgress(uploadId, percentComplete);
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    updateUploadStatus(uploadId, 'success', 'Berhasil diupload');
                    updateUploadProgress(uploadId, 100, 'success');

                    // Reload after all uploads complete
                    activeUploads--;
                    updateUploadCount();

                    if (activeUploads === 0 && uploadQueue.length === 0) {
                        // File already processed, reload immediately
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        processUploadQueue();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Gagal mengupload';
                    updateUploadStatus(uploadId, 'error', message);
                    updateUploadProgress(uploadId, 100, 'error');

                    activeUploads--;
                    updateUploadCount();
                    processUploadQueue();
                }
            });
        }

        function updateUploadProgress(uploadId, percent, status = '') {
            const item = $(`.upload-item[data-upload-id="${uploadId}"]`);
            item.find('.upload-item-progress-bar').css('width', percent + '%');
            if (status) {
                item.find('.upload-item-progress-bar').addClass(status);
            }
        }

        function updateUploadStatus(uploadId, type, message) {
            const item = $(`.upload-item[data-upload-id="${uploadId}"]`);
            const statusEl = item.find('.upload-item-status');

            statusEl.removeClass('processing success error').addClass(type);

            let icon = 'bi-hourglass-split';
            if (type === 'success') icon = 'bi-check-circle-fill';
            else if (type === 'error') icon = 'bi-x-circle-fill';
            else if (type === 'processing') icon = 'bi-arrow-repeat';

            statusEl.html(`<i class="bi ${icon}"></i> ${message}`);
        }

        function generateUploadId() {
            return 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // ============================================
        // OLD UPLOAD FUNCTION (Keep for reference, can be removed)
        // ============================================

        function uploadDocument() {
            let form = $('#formUploadDokumen')[0];
            let formData = new FormData(form);

            // Show progress modal
            $('#modalUploadProgress').modal('show');

            $.ajax({
                url: '{{ route('dokumen.store') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            let percentComplete = (evt.loaded / evt.total) * 100;
                            $('#uploadProgressBar').css('width', percentComplete + '%');
                            $('#uploadProgressText').text(
                                `Mengupload ${Math.round(percentComplete)}% selesai`
                            );

                            if (percentComplete >= 100) {
                                $('#uploadProgressText').text('Memproses dokumen...');
                            }
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $('#modalUploadProgress').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Dokumen berhasil diupload',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Reset form and reload
                        $('#formUploadDokumen')[0].reset();
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    $('#modalUploadProgress').modal('hide');

                    let errorMessage = 'Terjadi kesalahan saat upload dokumen';

                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            errorMessage += '<br><ul style="text-align: left;">';
                            Object.keys(errors).forEach(key => {
                                errors[key].forEach(error => {
                                    errorMessage += `<li>${error}</li>`;
                                });
                            });
                            errorMessage += '</ul>';
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        html: errorMessage
                    });

                    // Reset file input
                    $('#fileUpload').val('');
                }
            });
        }

        // ============================================
        // DATATABLE INITIALIZATION
        $('#btnUpdateDokumen').click(function() {
            let form = $('#formEditDokumen')[0];

            // Basic form validation
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            let dokumenId = $('#edit_dokumen_id').val();
            let formData = new FormData(form);

            $.ajax({
                url: `/dokumen/${dokumenId}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#editProgress').show();
                    $('#editProgressText').text('Memulai update...');
                    $('#btnUpdateDokumen').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Updating...'
                    );
                },
                xhr: function() {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            let percentComplete = (evt.loaded / evt.total) * 100;
                            $('#editProgress .progress-bar').css('width',
                                percentComplete + '%');
                            $('#editProgressText').text(
                                `Upload ${Math.round(percentComplete)}% selesai`
                            );

                            if (percentComplete >= 100) {
                                $('#editProgressText').text(
                                    'Memproses update dokumen...');
                            }
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $('#modalEditDokumen').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Dokumen berhasil diupdate',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Reload page to show updated document
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    $('#editProgress').hide();
                    $('#btnUpdateDokumen').prop('disabled', false).html(
                        '<i class="bi bi-save me-1"></i>Update Dokumen'
                    );

                    let errorMessage = 'Terjadi kesalahan saat update dokumen';

                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            errorMessage += '<br><ul style="text-align: left;">';
                            Object.keys(errors).forEach(key => {
                                errors[key].forEach(error => {
                                    errorMessage += `<li>${error}</li>`;
                                });
                            });
                            errorMessage += '</ul>';
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        html: errorMessage
                    });
                }
            });
        });

        // Set up download button in detail modal
        $('#btnDownloadDokumen').click(function() {
            let id = $(this).attr('data-id');
            if (id) {
                window.location.href = `/dokumen/${id}/download`;
            }
        });

        // Set up edit from detail button
        $('#btnEditFromDetail').click(function() {
            let id = $(this).attr('data-id');
            if (id) {
                $('#modalDetailDokumen').modal('hide');
                editDokumen(id);
            }
        });

        // ============================================
        // COMBINED TABLE POPULATION
        // ============================================

        function prepareTableData() {
            console.log('Preparing table data...', 'Subfolders:', subfolders.length, 'Documents:', documents.length);
            let tbody = '';

            try {
                // Add folders first
                subfolders.forEach(folder => {
                    const updatedDate = folder.updated_at ? formatDateOnly(folder.updated_at) : '-';
                    const creatorName = folder.creator ? folder.creator.nama : (folder.created_by ? 'User #' +
                        folder.created_by : '-');

                    tbody += `
                    <tr style="cursor:pointer;" onclick="navigateToFolder('${folder.id}')">
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-folder-fill text-secondary me-2" style="font-size: 20px;"></i>
                                ${folder.name}
                            </div>
                        </td>
                        <td>${creatorName}</td>
                        <td>${updatedDate}</td>
                        <td>
                            <small class="text-muted">${folder.total_files || 0} file${(folder.total_files || 0) !== 1 ? 's' : ''}</small>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <button class="btn btn-sm btn-link text-secondary p-1" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="navigateToFolder('${folder.id}'); return false;">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Buka
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="alert('Fitur ganti nama dalam pengembangan'); return false;">
                                    <i class="bi bi-pencil-square me-2"></i>Ganti Nama
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="copyFolderLink('${folder.id}'); return false;">
                                    <i class="bi bi-link-45deg me-2"></i>Salin Link
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="alert('Fitur dalam pengembangan'); return false;">
                                    <i class="bi bi-info-circle me-2"></i>Informasi Folder
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteFolder('${folder.id}', '${folder.name}'); return false;">
                                    <i class="bi bi-trash me-2"></i>Pindahkan ke Sampah
                                </a></li>
                            </ul>
                        </td>
                    </tr>
                    `;
                });

                // Add documents
                documents.forEach(doc => {
                    let extension, fileName, fileSize, updatedDate, creatorName;

                    // Support both TdFile and Dokumen structure
                    if (doc.storage_path || doc.extension) {
                        // TdFile structure (direct file)
                        extension = doc.extension || 'pdf';
                        fileName = doc.name || doc.original_name;
                        fileSize = doc.size ? `${(doc.size / 1024).toFixed(2)} KB` : '-';
                        updatedDate = doc.updated_at ? new Date(doc.updated_at).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }) : '-';
                        creatorName = doc.creator ? doc.creator.nama : '-';
                    } else {
                        // Dokumen structure (multiple versions)
                        const currentFile = doc.files && doc.files.length > 0 ? (doc.files.find(f => f
                                .is_current) ||
                            doc.files[0]) : null;
                        extension = currentFile ? currentFile.extension : 'pdf';
                        fileName = doc.judul;
                        fileSize = currentFile ? `${Number(currentFile.size_kb).toLocaleString('id-ID')} KB` : '-';
                        updatedDate = doc.updated_at ? new Date(doc.updated_at).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }) : '-';
                        creatorName = doc.uploader ? doc.uploader.nama : '-';
                    }

                    let fileIcon = 'bi-file-earmark-text-fill';
                    if (extension === 'pdf') {
                        fileIcon = 'bi-file-earmark-pdf-fill text-danger';
                    } else if (['doc', 'docx'].includes(extension)) {
                        fileIcon = 'bi-file-earmark-word-fill text-primary';
                    } else if (['xls', 'xlsx'].includes(extension)) {
                        fileIcon = 'bi-file-earmark-excel-fill text-success';
                    } else if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                        fileIcon = 'bi-file-earmark-image-fill text-warning';
                    }

                    tbody += `
                    <tr style="cursor:pointer;" onclick="showFileDetail('${doc.id}')">
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi ${fileIcon} me-2" style="font-size: 20px;"></i>
                                ${fileName}
                            </div>
                        </td>
                        <td>${creatorName}</td>
                        <td>${updatedDate}</td>
                        <td>${fileSize}</td>
                        <td onclick="event.stopPropagation();">
                            <button class="btn btn-sm btn-link text-secondary p-1" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="${doc.storage_path ? '/api/files/' + doc.id + '/download' : '/dokumen/' + doc.id + '/download'}" onclick="event.stopPropagation()">
                                    <i class="bi bi-download me-2"></i>Unduh
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="editFile('${doc.id}'); return false;">
                                    <i class="bi bi-pencil-square me-2"></i>Edit
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteFile('${doc.id}', '${fileName}'); return false;">
                                    <i class="bi bi-trash me-2"></i>Hapus
                                </a></li>
                            </ul>
                        </td>
                    </tr>
                    `;
                });

                $('#combinedTableBody').html(tbody);
                console.log('Table data prepared successfully. Total rows:', subfolders.length + documents.length);
            } catch (error) {
                console.error('Error preparing table data:', error);
            }
        }

        function populateCombinedTable() {
            console.log('Populating combined table...');

            // Destroy existing DataTable if any
            if (dataTable) {
                console.log('Destroying existing DataTable');
                dataTable.destroy();
                dataTable = null;
            }

            // Generate tbody HTML (call prepareTableData to ensure fresh data)
            prepareTableData();

            // Initialize DataTable after a small delay to ensure DOM is ready
            setTimeout(function() {
                const rowCount = $('#combinedTableBody tr').length;
                console.log('Table body has', rowCount, 'rows');

                if ($('#combinedTable').length && rowCount > 0) {
                    dataTable = new simpleDatatables.DataTable("#combinedTable", {
                        searchable: true,
                        fixedHeight: false,
                        perPage: 10,
                        labels: {
                            placeholder: "Cari folder atau dokumen...",
                            perPage: "Data per halaman",
                            noRows: "Tidak ada data",
                            info: "Menampilkan {start} sampai {end} dari {rows} data",
                        }
                    });
                    console.log('DataTable initialized successfully with', rowCount, 'rows');
                } else {
                    console.warn('Cannot initialize DataTable: no rows found');
                }
            }, 150);
        }

        // ============================================
        // CRUD FUNCTIONS
        // ============================================

        function showDetail(id) {
            $('#modalDetailDokumen').modal('show');
            $('#detailDokumenContent').html(`
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat detail dokumen...</p></div>
            `);

            $.ajax({
                url: `/dokumen/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Prepare file icon based on extension
                    let fileIcon = 'bi-file-earmark-text';
                    let fileIconColor = '#4154f1';
                    if (response.files && response.files.length > 0) {
                        const extension = response.files[0].extension || 'pdf';
                        if (extension === 'pdf') {
                            fileIcon = 'bi-file-earmark-pdf-fill';
                            fileIconColor = '#dc3545';
                        } else if (['doc', 'docx'].includes(extension)) {
                            fileIcon = 'bi-file-earmark-word-fill';
                            fileIconColor = '#0d6efd';
                        } else if (['xls', 'xlsx'].includes(extension)) {
                            fileIcon = 'bi-file-earmark-excel-fill';
                            fileIconColor = '#198754';
                        }
                    }

                    // Determine status badge
                    let statusClass = 'bg-secondary';
                    let statusIcon = 'bi-clock';
                    if (response.status === 'Final') {
                        statusClass = 'bg-success';
                        statusIcon = 'bi-check-circle';
                    } else if (response.status === 'Draft') {
                        statusClass = 'bg-warning text-dark';
                        statusIcon = 'bi-pencil';
                    } else if (response.status === 'Archived') {
                        statusClass = 'bg-secondary';
                        statusIcon = 'bi-archive';
                    }

                    let html = `
                        <div class="p-4">
                            <!-- Document Header Section -->
                            <div class="detail-header mb-4 text-center">
                                <div class="mb-3">
                                    <i class="bi ${fileIcon}" style="font-size: 5rem; color: ${fileIconColor};"></i>
                                </div>
                                <h3 class="fw-bold mb-3">${response.judul}</h3>
                                <div class="d-flex justify-content-center gap-2 mb-2">
                                    <span class="badge ${statusClass} rounded-pill px-3 py-2">
                                        <i class="bi ${statusIcon} me-1"></i>${response.status}
                                    </span>
                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                        <i class="bi bi-layers me-1"></i>v${response.version}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Document Info Table -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body p-0">
                                    <table class="table table-striped mb-0">
                                        <tr>
                                            <th class="bg-light" width="30%">Nomor Dokumen</th>
                                            <td>${response.nomor || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Jenis</th>
                                            <td>${response.jenis?.nama || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Folder</th>
                                            <td>${response.folder?.nama || '-'} <small class=\"text-muted\">${response.folder?.path || ''}</small></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Nomor Surat</th>
                                            <td>${response.nomor_surat || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Tanggal</th>
                                            <td>${formatDate(response.tanggal_dokumen)}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Deskripsi</th>
                                            <td>${response.deskripsi || '<em class=\"text-muted\">Tidak ada deskripsi</em>'}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Diupload oleh</th>
                                            <td>${response.uploader?.nama || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Statistik</th>
                                            <td>
                                                <div class="d-flex gap-3">
                                                    <div>
                                                        <i class="bi bi-eye me-1"></i>${response.views || 0} Views
                                                    </div>
                                                    <div>
                                                        <i class="bi bi-download me-1"></i>${response.downloads || 0} Downloads
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>`;

                    // Metadata section
                    if (response.metadata && response.metadata.length > 0) {
                        html += `
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bi bi-tags me-2"></i>Metadata
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th width="40%">Kunci</th>
                                                <th width="60%">Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;

                        response.metadata.forEach(meta => {
                            html += `
                                <tr>
                                    <td class="fw-bold">${meta.key}</td>
                                    <td>${meta.value}</td>
                                </tr>`;
                        });

                        html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>`;
                    }

                    // File information
                    if (response.files && response.files.length > 0) {
                        let currentFile = response.files.find(f => f.is_current) || response.files[0];
                        const extension = currentFile.extension || 'pdf';

                        let fileIconClass = 'text';
                        if (extension === 'pdf') {
                            fileIconClass = 'pdf-fill text-danger';
                        } else if (['doc', 'docx'].includes(extension)) {
                            fileIconClass = 'word-fill text-primary';
                        } else if (['xls', 'xlsx'].includes(extension)) {
                            fileIconClass = 'excel-fill text-success';
                        }

                        html += `
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bi bi-file-earmark me-2"></i>File Dokumen
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="detail-file-preview">
                                        <div class="me-3">
                                            <i class="bi bi-file-earmark-${fileIconClass}" style="font-size: 3rem;"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">${currentFile.nama_file}</h6>
                                            <p class="mb-0 text-muted">
                                                <span class="badge bg-info text-dark me-2">${extension.toUpperCase()}</span>
                                                Ukuran: ${formatFileSize(currentFile.size_kb)} | 
                                                Versi: ${currentFile.version} | 
                                                Diupload: ${formatDateTime(currentFile.created_at)}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                        // Multiple versions
                        if (response.files.length > 1) {
                            html += `
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0" data-bs-toggle="collapse" href="#collapseVersions" role="button" aria-expanded="false" style="cursor: pointer;">
                                            <i class="bi bi-clock-history me-2"></i>Riwayat Versi <span class="badge bg-primary">${response.files.length - 1}</span>
                                            <i class="bi bi-chevron-down float-end"></i>
                                        </h5>
                                    </div>
                                    <div class="collapse" id="collapseVersions">
                                        <div class="card-body p-0">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Versi</th>
                                                        <th>Nama File</th>
                                                        <th>Ukuran</th>
                                                        <th>Tanggal Upload</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;

                            const oldVersions = response.files
                                .filter(f => !f.is_current)
                                .sort((a, b) => b.version - a.version);

                            oldVersions.forEach(file => {
                                html += `
                                    <tr>
                                        <td><span class="badge bg-secondary">v${file.version}</span></td>
                                        <td>${file.nama_file}</td>
                                        <td>${formatFileSize(file.size_kb)}</td>
                                        <td>${formatDateTime(file.created_at)}</td>
                                    </tr>`;
                            });

                            html += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>`;
                        }
                    }

                    // Footer
                    html += `</div>`;

                    $('#detailDokumenContent').html(html);
                    $('#btnDownloadDokumen').attr('data-id', id);
                    $('#btnEditFromDetail').attr('data-id', id);
                },
                error: function() {
                    $('#detailDokumenContent').html(`
                        <div class="text-center p-5">
                            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                            <h4 class="mt-3">Gagal Memuat Detail</h4>
                            <p class="text-muted mb-4">Terjadi kesalahan saat memuat detail dokumen.</p>
                            <button class="btn btn-primary" onclick="showDetail(${id})">
                                <i class="bi bi-arrow-clockwise me-2"></i>Coba Lagi
                            </button>
                        </div>
                    `);
                }
            });
        }

        function editDokumen(id) {
            console.log('Edit dokumen ID:', id);

            // Show loading state in the modal
            $('#current_file_info').html(`
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div>Memuat informasi dokumen...</div>
                </div>
            `);

            $('#modalEditDokumen').modal('show');

            $.ajax({
                url: `/dokumen/${id}/edit`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Edit data loaded:', response);

                    $('#edit_dokumen_id').val(response.id);

                    // Fill form fields
                    $('#edit_judul').val(response.judul);
                    $('#edit_nomor_surat').val(response.nomor_surat || '');

                    let tanggal = response.tanggal_dokumen ? response.tanggal_dokumen.substring(0, 10) : '';
                    $('#edit_tanggal_dokumen').val(tanggal);

                    $('#edit_deskripsi').val(response.deskripsi || '');

                    // Set status radio buttons
                    $(`input[name="status"][value="${response.status}"]`).prop('checked', true);

                    // Show current file info
                    if (response.files && response.files.length > 0) {
                        let currentFile = response.files.find(f => f.is_current) || response.files[0];
                        const extension = currentFile.extension || '';

                        let iconClass = 'text';
                        let iconColor = '#6c757d';
                        if (extension === 'pdf') {
                            iconClass = 'pdf-fill';
                            iconColor = '#dc3545';
                        } else if (['doc', 'docx'].includes(extension)) {
                            iconClass = 'word-fill';
                            iconColor = '#0d6efd';
                        } else if (['xls', 'xlsx'].includes(extension)) {
                            iconClass = 'excel-fill';
                            iconColor = '#198754';
                        }

                        $('#current_file_info').html(`
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-${iconClass} me-3" style="font-size: 2rem; color: ${iconColor};"></i>
                                <div>
                                    <strong>${currentFile.nama_file}</strong><br>
                                    <small>
                                        <span class="badge bg-info text-dark me-1">${extension.toUpperCase()}</span>
                                        Ukuran: ${formatFileSize(currentFile.size_kb)} | 
                                        Versi: ${currentFile.version} | 
                                        Diupload: ${formatDateTime(currentFile.created_at)}
                                    </small>
                                </div>
                            </div>
                        `);
                    } else {
                        $('#current_file_info').html('<small class="text-muted">Tidak ada file</small>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading edit data:', xhr);

                    $('#modalEditDokumen').modal('hide');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data dokumen untuk edit'
                    });
                }
            });
        }

        function deleteDokumen(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Dokumen akan dihapus permanen beserta semua file dan metadata-nya. Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, Hapus!',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Batal',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/dokumen/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Dokumen berhasil dihapus.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload page to refresh document list
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Gagal menghapus dokumen'
                            });
                        }
                    });
                }
            });
        }

        // ============================================
        // SUBFOLDER FUNCTIONS
        // ============================================

        function loadSubfolders() {
            const folderId = '{{ $folder->id }}';

            // Show skeleton and section
            $('#subfoldersSection').show();
            $('#subfoldersSkeleton').show();
            $('#subfoldersGrid').hide();

            $.ajax({
                url: `{{ route('terminaldata.foldersData.children', ':folderId') }}`.replace(':folderId',
                    folderId),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Subfolders loaded:', response);

                    // Hide skeleton
                    $('#subfoldersSkeleton').hide();

                    subfolders = response && Array.isArray(response) ? response : [];

                    if (subfolders.length > 0) {
                        renderSubfoldersGrid(subfolders);
                        $('#subfolderCount').text(subfolders.length);
                        $('#subfoldersGrid').show();
                    } else {
                        console.log('No subfolders found, hiding section');
                        $('#subfoldersSection').hide();
                    }

                    // Data is ready, prepareTableData is called by populateCombinedTable when needed
                    console.log('Subfolders ready:', subfolders.length, 'folders');
                },
                error: function(xhr, status, error) {
                    console.error('Error loading subfolders:', error, xhr);

                    // Hide skeleton on error
                    $('#subfoldersSkeleton').hide();
                    subfolders = [];
                    $('#subfoldersSection').hide();
                }
            });
        }

        function renderSubfoldersGrid(folders) {
            if (!folders || folders.length === 0) {
                $('#subfoldersSection').hide();
                return;
            }

            $('#subfoldersSection').show();
            let html = '';

            folders.forEach(folder => {
                const subfolderCount = 0; // Could be calculated if needed

                html += `
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="gdrive-folder-card" onclick="navigateToFolder('${folder.id}')">
                        <div class="gdrive-folder-content">
                            <div class="gdrive-folder-icon">
                                <i class="bi bi-folder-fill"></i>
                            </div>
                            <div class="gdrive-folder-info">
                                <div class="gdrive-folder-title" title="${folder.name}">
                                    ${folder.name}
                                </div>
                                <div class="gdrive-folder-meta">
                                    <small class="text-muted">${folder.total_files || 0} file${(folder.total_files || 0) !== 1 ? 's' : ''}</small>
                                </div>
                            </div>
                            <div class="gdrive-folder-menu" onclick="event.stopPropagation();">
                                <button class="btn btn-sm btn-link p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" onclick="navigateToFolder('${folder.id}'); return false;">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Buka
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="alert('Fitur ganti nama dalam pengembangan'); return false;">
                                        <i class="bi bi-pencil-square me-2"></i>Ganti Nama
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="copyFolderLink('${folder.id}'); return false;">
                                        <i class="bi bi-link-45deg me-2"></i>Salin Link
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="alert('Fitur dalam pengembangan'); return false;">
                                        <i class="bi bi-info-circle me-2"></i>Informasi Folder
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); deleteFolder('${folder.id}', '${folder.name}'); return false;">
                                        <i class="bi bi-trash me-2"></i>Pindahkan ke Sampah
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            });

            $('#subfoldersGrid').html(html);
        }

        function navigateToFolder(folderId) {
            window.location.href = `{{ route('terminaldata.folder.detail', ':folderId') }}`.replace(':folderId', folderId);
        }

        function copyFolderLink(id) {
            const link = `${window.location.origin}{{ route('terminaldata.folder.detail', ':id') }}`.replace(':id', id);

            if (navigator.clipboard) {
                navigator.clipboard.writeText(link).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Link folder berhasil disalin',
                        timer: 1500,
                        showConfirmButton: false
                    });
                });
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = link;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Link folder berhasil disalin',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }

        function formatDateOnly(dateString) {
            if (!dateString) return '-';
            let date = new Date(dateString);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const day = date.getDate();
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            return `${day} ${month} ${year}`;
        }

        // ============================================
        // UTILITY FUNCTIONS
        // ============================================

        function formatDate(dateString) {
            if (!dateString) return '-';
            let date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        }

        function formatDateTime(dateString) {
            if (!dateString) return '-';
            let date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatFileSize(sizeKb) {
            if (!sizeKb) return '0 KB';
            if (sizeKb < 1024) {
                return sizeKb + ' KB';
            } else {
                return (sizeKb / 1024).toFixed(2) + ' MB';
            }
        }

        // ============================================
        // FOLDER DELETE FUNCTION
        // ============================================

        function deleteFolder(folderId, folderName) {
            Swal.fire({
                title: 'Hapus Folder?',
                html: `Apakah Anda yakin ingin memindahkan folder <strong>"${folderName}"</strong> ke sampah?<br><small class="text-muted">Folder harus kosong (tidak ada subfolder dan dokumen)</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Menghapus...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Make AJAX request to delete folder
                    $.ajax({
                        url: `{{ route('terminaldata.foldersData.destroy', ':folderId') }}`.replace(
                            ':folderId', folderId),
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Folder berhasil dihapus',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload subfolders or redirect to parent
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let message = 'Gagal menghapus folder';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: message
                            });
                        }
                    });
                }
            });
        }

        // Initialize detail elements for already rendered items
        window.showDetail = showDetail;
        window.editDokumen = editDokumen;
        window.deleteDokumen = deleteDokumen;
        window.deleteFolder = deleteFolder;
        window.showFileDetail = showFileDetail;
        window.editFile = editFile;
        window.deleteFile = deleteFile;

        // ============================================
        // FILE OPERATIONS (for TdFile)
        // ============================================

        function showFileDetail(id) {
            Swal.fire({
                icon: 'info',
                title: 'File Detail',
                text: 'Fitur detail file dalam pengembangan',
                confirmButtonColor: '#0d6efd'
            });
        }

        function editFile(id) {
            Swal.fire({
                icon: 'info',
                title: 'Edit File',
                text: 'Fitur edit nama file dalam pengembangan',
                confirmButtonColor: '#0d6efd'
            });
        }

        function deleteFile(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "File akan dihapus. Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, Hapus!',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Batal',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('terminal-data/api/files') }}/${id}`,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'File berhasil dihapus.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Gagal menghapus file'
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush
