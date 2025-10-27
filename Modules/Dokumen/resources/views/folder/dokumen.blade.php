@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Dokumen Folder: {{ $folder->nama }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dokumen.index') }}">Dokumen</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dokumen.folder.index') }}">Folder</a></li>
                <li class="breadcrumb-item active">{{ $folder->nama }}</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <!-- Folder Info Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                                    <i class="bi bi-folder-fill text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0">{{ $folder->nama }}</h3>
                                    <p class="text-muted mb-0">
                                        <code class="fs-6">{{ $folder->path }}</code>
                                        @if ($folder->is_auto)
                                            <span class="badge bg-success ms-2">
                                                <i class="bi bi-gear-fill me-1"></i>Auto Generated
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('dokumen.folder.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-arrow-left me-1"></i>Kembali
                                </a>
                                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalUploadDokumen">
                                    <i class="bi bi-cloud-upload me-2"></i>Upload Dokumen
                                </button>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100 bg-info bg-opacity-10">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="icon-box bg-info text-white rounded-circle p-3 me-3">
                                            <i class="bi bi-diagram-3"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Level</h6>
                                            <h5 class="fw-bold mb-0">{{ $folder->level }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100 bg-success bg-opacity-10">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="icon-box bg-success text-white rounded-circle p-3 me-3">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Bidang</h6>
                                            <h5 class="fw-bold mb-0">{{ $folder->bidang->nama ?? 'Tidak ada' }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100 bg-warning bg-opacity-10">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="icon-box bg-warning text-white rounded-circle p-3 me-3">
                                            <i class="bi bi-folder2-open"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Tipe Folder</h6>
                                            <h5 class="fw-bold mb-0">{{ $folder->is_auto ? 'Otomatis' : 'Manual' }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100 bg-primary bg-opacity-10">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="icon-box bg-primary text-white rounded-circle p-3 me-3">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Total Dokumen</h6>
                                            <h5 class="fw-bold mb-0">{{ count($dokumen) }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                <!-- Filter Button -->
                                <button type="button" class="btn btn-outline-secondary" id="toggleFilter">
                                    <i class="bi bi-funnel-fill me-1"></i> Filter
                                </button>
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
                                <input type="radio" class="btn-check" name="viewMode" id="viewTable" checked>
                                <label class="btn btn-outline-primary px-4" for="viewTable">
                                    <i class="bi bi-table me-2"></i>Table View
                                </label>
                                <input type="radio" class="btn-check" name="viewMode" id="viewGrid">
                                <label class="btn btn-outline-primary px-4" for="viewGrid">
                                    <i class="bi bi-grid-3x3-gap me-2"></i>Grid View
                                </label>
                            </div>
                        </div>

                        <!-- Table View -->
                        <div id="tableView">
                            @if (count($dokumen) > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="dokumenTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" width="50">#</th>
                                                <th scope="col">Nomor</th>
                                                <th scope="col">Dokumen</th>
                                                <th scope="col">Tanggal</th>
                                                <th scope="col">Status</th>
                                                <th scope="col" width="140">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($dokumen as $index => $item)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td><small class="text-muted">{{ $item->nomor ?? '-' }}</small></td>
                                                    <td>
                                                        @php
                                                            $fileIcon = '';
                                                            if ($item->files && count($item->files) > 0) {
                                                                $extension = $item->files[0]->extension ?? 'pdf';
                                                                if ($extension == 'pdf') {
                                                                    $fileIcon =
                                                                        'bi bi-file-earmark-pdf-fill text-danger';
                                                                } elseif (in_array($extension, ['doc', 'docx'])) {
                                                                    $fileIcon =
                                                                        'bi bi-file-earmark-word-fill text-primary';
                                                                } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                                                    $fileIcon =
                                                                        'bi bi-file-earmark-excel-fill text-success';
                                                                } else {
                                                                    $fileIcon = 'bi bi-file-earmark-text';
                                                                }
                                                            } else {
                                                                $fileIcon = 'bi bi-file-earmark-text';
                                                            }
                                                        @endphp
                                                        <div class="d-flex align-items-center">
                                                            <div class="icon-box rounded-3 p-2 me-2 bg-light">
                                                                <i class="{{ $fileIcon }}"
                                                                    style="font-size: 1.5rem;"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">
                                                                    <a href="#"
                                                                        onclick="showDetail({{ $item->id }}); return false;"
                                                                        class="text-decoration-none">
                                                                        {{ $item->judul }}
                                                                    </a>
                                                                </h6>
                                                                @if ($item->nomor_surat)
                                                                    <small class="text-muted d-block">No. Surat:
                                                                        {{ $item->nomor_surat }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($item->tanggal_dokumen)->format('d M Y') }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusClass = 'bg-secondary';
                                                            $statusIcon = 'bi-clock';
                                                            if ($item->status === 'Final') {
                                                                $statusClass = 'bg-success';
                                                                $statusIcon = 'bi-check-circle';
                                                            } elseif ($item->status === 'Draft') {
                                                                $statusClass = 'bg-warning text-dark';
                                                                $statusIcon = 'bi-pencil';
                                                            } elseif ($item->status === 'Archived') {
                                                                $statusClass = 'bg-secondary';
                                                                $statusIcon = 'bi-archive';
                                                            }
                                                        @endphp
                                                        <span class="badge {{ $statusClass }} rounded-pill px-3 py-2">
                                                            <i
                                                                class="bi {{ $statusIcon }} me-1"></i>{{ $item->status }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-primary btn-sm"
                                                                onclick="showDetail({{ $item->id }})" title="Detail">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                            <a href="{{ route('dokumen.download', $item->id) }}"
                                                                class="btn btn-success btn-sm" title="Download">
                                                                <i class="bi bi-download"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-warning btn-sm"
                                                                onclick="editDokumen({{ $item->id }})"
                                                                title="Edit">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                onclick="deleteDokumen({{ $item->id }})"
                                                                title="Hapus">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-folder-x" style="font-size: 5rem; color: #e0e0e0;"></i>
                                        <h4 class="mt-3">Belum Ada Dokumen</h4>
                                        <p class="text-muted mb-4">Folder ini belum memiliki dokumen.</p>
                                        <button type="button" class="btn btn-primary px-4 py-2" data-bs-toggle="modal"
                                            data-bs-target="#modalUploadDokumen">
                                            <i class="bi bi-cloud-upload me-2"></i>Upload Dokumen Sekarang
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Grid View -->
                        <div id="gridView" style="display: none;">
                            @if (count($dokumen) > 0)
                                <div class="row g-3">
                                    @foreach ($dokumen as $item)
                                        @php
                                            $fileIcon = 'bi-file-earmark-text';
                                            $fileIconColor = '#5f6368';
                                            if ($item->files && count($item->files) > 0) {
                                                $extension = $item->files[0]->extension ?? 'pdf';
                                                if ($extension == 'pdf') {
                                                    $fileIcon = 'bi-file-earmark-pdf-fill';
                                                    $fileIconColor = '#dc3545';
                                                } elseif (in_array($extension, ['doc', 'docx'])) {
                                                    $fileIcon = 'bi-file-earmark-word-fill';
                                                    $fileIconColor = '#2b579a';
                                                } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                                    $fileIcon = 'bi-file-earmark-excel-fill';
                                                    $fileIconColor = '#1d6f42';
                                                } elseif (in_array($extension, ['ppt', 'pptx'])) {
                                                    $fileIcon = 'bi-file-earmark-ppt-fill';
                                                    $fileIconColor = '#d24726';
                                                }
                                            }

                                            $statusClass = 'bg-secondary';
                                            if ($item->status === 'Final') {
                                                $statusClass = 'bg-success';
                                            } elseif ($item->status === 'Draft') {
                                                $statusClass = 'bg-warning text-dark';
                                            }
                                        @endphp

                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                            <div class="gdrive-card" onclick="showDetail({{ $item->id }})">
                                                <div class="gdrive-card-preview">
                                                    <i class="bi {{ $fileIcon }}"
                                                        style="color: {{ $fileIconColor }};"></i>
                                                </div>
                                                <div class="gdrive-card-body">
                                                    <div class="gdrive-card-title" title="{{ $item->judul }}">
                                                        {{ $item->judul }}
                                                    </div>
                                                    <div class="gdrive-card-meta">
                                                        <small class="text-muted">
                                                            {{ \Carbon\Carbon::parse($item->tanggal_dokumen)->format('d M Y') }}
                                                        </small>
                                                        <span
                                                            class="badge {{ $statusClass }} badge-sm">{{ $item->status }}</span>
                                                    </div>
                                                </div>
                                                <div class="gdrive-card-actions">
                                                    <button type="button" class="btn btn-sm btn-link text-secondary p-1"
                                                        onclick="event.stopPropagation(); showDetail({{ $item->id }})"
                                                        title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <a href="{{ route('dokumen.download', $item->id) }}"
                                                        class="btn btn-sm btn-link text-secondary p-1"
                                                        onclick="event.stopPropagation()" title="Download">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-link text-secondary p-1"
                                                        onclick="event.stopPropagation(); editDokumen({{ $item->id }})"
                                                        title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-link text-danger p-1"
                                                        onclick="event.stopPropagation(); deleteDokumen({{ $item->id }})"
                                                        title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-folder-x" style="font-size: 5rem; color: #e0e0e0;"></i>
                                        <h4 class="mt-3">Belum Ada Dokumen</h4>
                                        <p class="text-muted mb-4">Folder ini belum memiliki dokumen.</p>
                                        <button type="button" class="btn btn-primary px-4 py-2" data-bs-toggle="modal"
                                            data-bs-target="#modalUploadDokumen">
                                            <i class="bi bi-cloud-upload me-2"></i>Upload Dokumen Sekarang
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Upload -->
    <div class="modal fade" id="modalUploadDokumen" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Dokumen Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formUploadDokumen" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="folder_id" value="{{ $folder->id }}">

                        <div class="mb-4">
                            <div class="alert alert-info d-flex">
                                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                                <div>
                                    <strong>Info:</strong> Dokumen akan disimpan di folder
                                    <strong>{{ $folder->nama }}</strong>
                                    <span class="d-block small">{{ $folder->path }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- File Upload - Main Focus -->
                        <div class="mb-4">
                            <label for="file" class="form-label fw-semibold fs-5">
                                <i class="bi bi-cloud-upload me-2"></i>Pilih File <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control form-control-lg" id="file" name="file"
                                required>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Semua format file didukung (Max: 50MB)
                            </div>
                        </div>

                        <!-- Judul - Auto filled from filename -->
                        <div class="mb-3">
                            <label for="judul" class="form-label fw-semibold">
                                Nama Dokumen <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="judul" name="judul"
                                required placeholder="Otomatis dari nama file">
                            <div class="form-text">Nama akan otomatis diisi dari nama file, Anda bisa mengubahnya</div>
                        </div>

                        <!-- Deskripsi - Optional & Collapsible -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="showDesc">
                                <label class="form-check-label" for="showDesc">
                                    Tambahkan deskripsi (opsional)
                                </label>
                            </div>
                            <div id="descContainer" style="display: none;" class="mt-2">
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="2"
                                    placeholder="Tambahkan deskripsi singkat"></textarea>
                            </div>
                        </div>

                        <!-- Status - Simple -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusFinal"
                                        value="Final" checked>
                                    <label class="form-check-label" for="statusFinal">
                                        <i class="bi bi-check-circle text-success me-1"></i>Final
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusDraft"
                                        value="Draft">
                                    <label class="form-check-label" for="statusDraft">
                                        <i class="bi bi-pencil text-warning me-1"></i>Draft
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Progress -->
                        <div id="uploadProgress" class="mt-3" style="display: none;">
                            <label class="form-label fw-semibold">Proses Upload</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: 0%"></div>
                            </div>
                            <p class="text-center mt-2 mb-0" id="uploadProgressText">Mempersiapkan upload...</p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary px-4" id="btnUploadDokumen">
                        <i class="bi bi-cloud-upload me-1"></i>Upload Dokumen
                    </button>
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

    <style>
        /* Google Drive Style Cards */
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

        .gdrive-card-meta .badge-sm {
            font-size: 10px;
            padding: 2px 6px;
        }

        .gdrive-card-actions {
            padding: 8px 12px;
            border-top: 1px solid #e8eaed;
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .gdrive-card:hover .gdrive-card-actions {
            opacity: 1;
        }

        .gdrive-card-actions .btn {
            padding: 4px 8px;
            font-size: 14px;
        }

        .gdrive-card-actions .btn-link {
            text-decoration: none;
            color: #5f6368;
        }

        .gdrive-card-actions .btn-link:hover {
            background-color: #f1f3f4;
            color: #202124;
        }

        /* Card Styles (old - for backward compatibility) */
        .dokumen-card {
            position: relative;
            overflow: hidden;
            border-radius: 16px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid #f0f0f0 !important;
        }

        .dokumen-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
            border-color: #4154f1 !important;
        }

        /* Document icon wrapper */
        .document-icon-wrapper {
            width: 90px;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            transition: all 0.3s ease;
        }

        .dokumen-card:hover .document-icon-wrapper {
            transform: scale(1.1);
        }

        /* Status Badges */
        .badge {
            font-weight: 600;
            font-size: 0.75rem;
        }

        .badge.rounded-pill {
            padding: 0.5rem 0.8rem;
        }

        /* Table Styles */
        #dokumenTable {
            vertical-align: middle;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
        }

        #dokumenTable thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            padding: 15px;
        }

        #dokumenTable tbody td {
            padding: 15px;
            vertical-align: middle;
        }

        /* Icon Box */
        .icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-box.rounded-circle {
            width: 48px;
            height: 48px;
        }

        /* Metadata Styles */
        .metadata-row {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px !important;
            border: 1px solid #eee;
            transition: all 0.2s ease;
        }

        .metadata-row:hover {
            background-color: #f0f0f0;
        }

        .metadata-table {
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }

        .metadata-table th {
            background-color: #f1f3f5;
        }

        /* Empty State */
        .empty-state {
            padding: 3rem 2rem;
        }

        /* Detail view styles */
        .detail-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .detail-file-preview {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            border: 1px solid #e9ecef;
        }

        /* Form styles */
        .form-label {
            margin-bottom: 0.5rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .form-control-lg,
        .form-select-lg {
            padding: 0.75rem 1rem;
            font-size: 1.1rem;
        }
    </style>
@endsection

@push('scripts')
    <script>
        let dataTable = null;

        $(document).ready(function() {
            console.log('Document ready');

            // Initialize metadata handling
            initMetadataHandling();

            // Auto-fill judul from filename
            $('#file').on('change', function() {
                let file = this.files[0];
                if (file) {
                    // Get filename without extension
                    let filename = file.name;
                    let nameWithoutExt = filename.substring(0, filename.lastIndexOf('.')) || filename;

                    // Set to judul field if empty
                    if (!$('#judul').val()) {
                        $('#judul').val(nameWithoutExt);
                    }
                }
            });

            // Toggle description
            $('#showDesc').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#descContainer').slideDown();
                } else {
                    $('#descContainer').slideUp();
                    $('#deskripsi').val('');
                }
            });

            // Initialize DataTable
            initializeDataTable();

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

                    setTimeout(function() {
                        if (!dataTable) {
                            initializeDataTable();
                        }
                    }, 100);
                }
            });

            // Modal setup
            $('#modalUploadDokumen').on('shown.bs.modal', function() {
                // Focus on file input
                $('#file').focus();
            });

            // Reset form when modal is closed
            $('#modalUploadDokumen').on('hidden.bs.modal', function() {
                $('#formUploadDokumen')[0].reset();
                $('#showDesc').prop('checked', false);
                $('#descContainer').hide();
                $('#uploadProgress').hide();
                $('#uploadProgress .progress-bar').css('width', '0%');
                $('#uploadProgressText').text('Mempersiapkan upload...');
            });

            // Reset edit form when modal is closed
            $('#modalEditDokumen').on('hidden.bs.modal', function() {
                $('#formEditDokumen')[0].reset();
                $('#editProgress').hide();
                $('#editProgress .progress-bar').css('width', '0%');
                $('#editProgressText').text('Mempersiapkan update...');
            });

            // Upload Document
            $('#btnUploadDokumen').click(function() {
                let form = $('#formUploadDokumen')[0];

                // Basic form validation
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                let formData = new FormData(form);

                $.ajax({
                    url: '{{ route('dokumen.store') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#uploadProgress').show();
                        $('#uploadProgressText').text('Memulai upload...');
                        $('#btnUploadDokumen').prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...'
                        );
                    },
                    xhr: function() {
                        let xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                let percentComplete = (evt.loaded / evt.total) * 100;
                                $('#uploadProgress .progress-bar').css('width',
                                    percentComplete + '%');
                                $('#uploadProgressText').text(
                                    `Upload ${Math.round(percentComplete)}% selesai`
                                );

                                if (percentComplete >= 100) {
                                    $('#uploadProgressText').text(
                                        'Memproses dokumen...');
                                }
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        $('#modalUploadDokumen').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Dokumen berhasil diupload',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload page to show new document
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        $('#uploadProgress').hide();
                        $('#btnUploadDokumen').prop('disabled', false).html(
                            '<i class="bi bi-cloud-upload me-1"></i>Upload Dokumen'
                        );

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
                    }
                });
            });

            // Update Document
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
        });

        // ============================================
        // DATATABLE INITIALIZATION
        // ============================================

        function initializeDataTable() {
            if (dataTable) {
                dataTable.destroy();
                dataTable = null;
            }

            if ($('#dokumenTable').length) {
                dataTable = new simpleDatatables.DataTable("#dokumenTable", {
                    searchable: true,
                    fixedHeight: false,
                    perPage: 10,
                    labels: {
                        placeholder: "Cari dokumen...",
                        perPage: "Data per halaman",
                        noRows: "Tidak ada data",
                        info: "Menampilkan {start} sampai {end} dari {rows} data",
                    }
                });
            }
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
                                            <td>${response.folder?.nama || '-'} <small class="text-muted">${response.folder?.path || ''}</small></td>
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
                                            <td>${response.deskripsi || '<em class="text-muted">Tidak ada deskripsi</em>'}</td>
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

        // Initialize detail elements for already rendered items
        window.showDetail = showDetail;
        window.editDokumen = editDokumen;
        window.deleteDokumen = deleteDokumen;
    </script>
@endpush
