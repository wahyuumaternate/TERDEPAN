@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Files in Folder</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dokumen.index') }}">Dokumen</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dokumen.folder.index') }}">Folder</a></li>
                <li class="breadcrumb-item active" id="current-folder-name">Files</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="card-title mb-1 d-flex align-items-center">
                                    <div class="icon-box bg-warning bg-opacity-10 rounded-3 p-2 me-3">
                                        <i class="bi bi-folder-fill text-warning" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold" id="folder-title">Files in Folder</span>
                                        <small class="d-block text-muted fw-normal mt-1"
                                            id="folder-path">/path/to/folder</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-primary shadow-sm me-2"
                                    onclick="backToFolders()">
                                    <i class="bi bi-arrow-left-circle me-2"></i>Back to Folders
                                </button>
                                <button type="button" class="btn btn-primary shadow-sm px-4 py-2" id="upload-file-btn">
                                    <i class="bi bi-upload me-2"></i>Upload File
                                </button>
                            </div>
                        </div>

                        <!-- Folder Info Card -->
                        <div class="card shadow-sm border-0 bg-light mb-4">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <div class="folder-icon-wrapper me-3"
                                                style="width: 50px; height: 50px; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-folder-fill text-white" style="font-size: 1.8rem;"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-0 fw-bold" id="folder-name">Folder Name</h5>
                                                <p class="mb-0 text-muted small" id="folder-details">Detailed path</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                        <span class="badge badge-level me-2" id="folder-level">Level 0</span>
                                        <span class="badge bg-auto" id="folder-auto-status">
                                            <i class="bi bi-gear-fill me-1"></i>Auto
                                        </span>
                                        <span class="badge bg-primary ms-2" id="folder-file-count">
                                            <i class="bi bi-file-earmark me-1"></i>0 Files
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- View Toggle -->
                        <div class="mb-3">
                            <div class="btn-group shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="fileViewMode" id="fileViewGrid" checked>
                                <label class="btn btn-outline-primary px-4" for="fileViewGrid">
                                    <i class="bi bi-grid-3x3-gap me-2"></i>Grid View
                                </label>
                                <input type="radio" class="btn-check" name="fileViewMode" id="fileViewTable">
                                <label class="btn btn-outline-primary px-4" for="fileViewTable">
                                    <i class="bi bi-table me-2"></i>Table View
                                </label>
                            </div>
                        </div>

                        <!-- Search & Filter -->
                        <div class="mb-3 py-2 px-3 bg-light rounded-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">
                                            <i class="bi bi-search text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control" id="fileSearchInput"
                                            placeholder="Search files...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="fileSortOptions">
                                        <option value="name">Sort by: Name</option>
                                        <option value="date">Sort by: Date</option>
                                        <option value="size">Sort by: Size</option>
                                        <option value="type">Sort by: Type</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="fileTypeFilter">
                                        <option value="">All File Types</option>
                                        <option value="pdf">PDF Files</option>
                                        <option value="docx">Word Documents</option>
                                        <option value="xlsx">Excel Sheets</option>
                                        <option value="image">Images</option>
                                        <option value="other">Other Files</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Files Grid View -->
                        <div id="filesGridView">
                            <div class="row" id="filesGrid">
                                <!-- Files will be loaded here -->
                                <div class="col-12 text-center py-5" id="loadingFiles">
                                    <div class="spinner-border text-primary" role="status"
                                        style="width: 3rem; height: 3rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-3">Loading files...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Files Table View -->
                        <div id="filesTableView" style="display: none;">
                            <div class="table-responsive">
                                <table class="table align-middle" id="filesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>File</th>
                                            <th>Jenis</th>
                                            <th>Ukuran</th>
                                            <th>Diupload Oleh</th>
                                            <th>Tanggal</th>
                                            <th style="width: 120px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Files will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Empty State for No Files -->
                        <div id="emptyFilesState" style="display: none;">
                            <div class="text-center py-5">
                                <i class="bi bi-file-earmark-x text-muted" style="font-size: 5rem;"></i>
                                <h4 class="text-muted mt-4 mb-2">Tidak ada file</h4>
                                <p class="text-muted mb-4">Folder ini belum memiliki file</p>
                                <button class="btn btn-primary px-4" id="empty-upload-btn">
                                    <i class="bi bi-upload me-2"></i>Upload File Pertama
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Upload File -->
    <div class="modal fade" id="modalUploadFile" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-primary text-white pb-3 pt-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-upload me-2"></i>Upload File
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formUploadFile" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="upload_folder_id" name="folder_id">

                        <div class="mb-3">
                            <label for="jenis_id" class="form-label">
                                Jenis Dokumen <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="jenis_id" name="jenis_id" required>
                                <option value="">Pilih Jenis Dokumen</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label">
                                Nama File <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">
                                Deskripsi
                            </label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="fileUpload" class="form-label">
                                File <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control" id="fileUpload" name="file" required>
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle me-1"></i>
                                <span id="allowedExtensions">Format yang diizinkan: PDF, DOCX, XLSX (Max: 10MB)</span>
                            </small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="btnUploadFile">
                        <i class="bi bi-upload me-1"></i>Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal File Preview -->
    <div class="modal fade" id="modalFilePreview" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-primary text-white pb-3 pt-3">
                    <h5 class="modal-title fw-bold" id="previewFileName">
                        <i class="bi bi-file-earmark me-2"></i>File Preview
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" id="filePreviewContent">
                    <!-- File preview will be loaded here -->
                    <div class="d-flex justify-content-center align-items-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <a href="#" class="btn btn-success" id="downloadFileBtn" target="_blank">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal File Details -->
    <div class="modal fade" id="modalFileDetails" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-primary text-white pb-4 pt-4">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="bi bi-info-circle me-2"></i>Detail File
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="fileDetailsContent">
                    <!-- File details will be loaded here -->
                </div>
                <div class="modal-footer bg-light border-0 p-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* File Card Styles */
        .file-card {
            position: relative;
            border-radius: 12px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            border: 2px solid #f0f0f0 !important;
            height: 100%;
        }

        .file-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1) !important;
            border-color: #0d6efd !important;
        }

        .file-icon-wrapper {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .file-card:hover .file-icon-wrapper {
            transform: translateY(-5px);
        }

        .file-icon {
            font-size: 2.8rem;
        }

        /* File Icon Colors */
        .file-icon-pdf {
            background-color: #ffefef;
            color: #dc3545;
        }

        .file-icon-doc {
            background-color: #e7f1ff;
            color: #0d6efd;
        }

        .file-icon-xls {
            background-color: #e7ffe7;
            color: #198754;
        }

        .file-icon-ppt {
            background-color: #fff4e5;
            color: #fd7e14;
        }

        .file-icon-img {
            background-color: #e7f5ff;
            color: #0dcaf0;
        }

        .file-icon-zip {
            background-color: #fff4e5;
            color: #fd7e14;
        }

        .file-icon-txt {
            background-color: #f8f9fa;
            color: #6c757d;
        }

        .file-icon-other {
            background-color: #f8f9fa;
            color: #6c757d;
        }

        /* File Name */
        .file-name {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 48px;
            line-height: 1.2;
            text-overflow: ellipsis;
        }

        /* File Meta */
        .file-meta {
            color: #6c757d;
            font-size: 0.8rem;
        }

        .file-size {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            font-size: 0.75rem;
            background-color: #f8f9fa;
            border-radius: 4px;
            color: #495057;
            font-weight: 600;
        }

        .file-date {
            margin-top: 2px;
        }

        /* Badge Styles */
        .badge-level {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
        }

        .bg-auto {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
        }

        /* Table Styles */
        .table td {
            vertical-align: middle;
        }

        /* Empty State */
        .empty-state-icon {
            font-size: 6rem;
            color: #e0e0e0;
        }

        /* Preview Styles */
        .file-preview-container {
            width: 100%;
            min-height: 500px;
            max-height: 70vh;
            overflow: auto;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .file-preview-container img {
            max-width: 100%;
            height: auto;
        }

        .file-preview-container iframe {
            width: 100%;
            height: 100%;
            min-height: 500px;
            border: none;
        }

        .file-preview-placeholder {
            padding: 5rem;
            text-align: center;
        }

        /* Hover Actions */
        .file-actions {
            position: absolute;
            top: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 0 0 0 8px;
            padding: 4px;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .file-card:hover .file-actions {
            opacity: 1;
        }

        /* File Type Badge */
        .file-type-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 3px 8px;
            font-size: 0.7rem;
            font-weight: 600;
            border-radius: 4px;
            background-color: rgba(255, 255, 255, 0.9);
        }
    </style>
@endsection

@push('scripts')
    <script>
        // Global variables
        let currentFolderId = null;
        let folderData = null;
        let allFiles = [];
        let fileViewMode = 'grid';
        let filesDataTable = null;

        // Document ready
        $(document).ready(function() {
            // Get folder ID from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const folderId = urlParams.get('folder_id');

            if (folderId) {
                currentFolderId = folderId;
                loadFolderFiles(folderId);
            } else {
                // Redirect back to folders if no ID
                window.location.href = "{{ route('dokumen.folder.index') }}";
            }

            // File view mode toggle
            $('input[name="fileViewMode"]').change(function() {
                fileViewMode = $(this).attr('id') === 'fileViewGrid' ? 'grid' : 'table';
                toggleFileView();
            });

            // Search input handler
            $('#fileSearchInput').on('input', function() {
                const query = $(this).val().toLowerCase();
                filterFiles(query);
            });

            // Sort handler
            $('#fileSortOptions').on('change', function() {
                sortFiles($(this).val());
            });

            // Type filter handler
            $('#fileTypeFilter').on('change', function() {
                filterByType($(this).val());
            });

            // Upload button handlers
            $('#upload-file-btn, #empty-upload-btn').click(function() {
                showUploadModal(currentFolderId);
            });

            // Upload form submit
            $('#btnUploadFile').click(function() {
                uploadFile();
            });
        });

        // Function to load folder files
        function loadFolderFiles(folderId) {
            $('#loadingFiles').show();
            $('#emptyFilesState').hide();
            $('#filesGrid').html(''); // Clear grid view
            $('#filesTable tbody').html(''); // Clear table view

            $.ajax({
                url: `/dokumen/folder/${folderId}/files`,
                type: 'GET',
                success: function(response) {
                    folderData = response.folder;
                    allFiles = response.files;

                    // Update folder info
                    updateFolderInfo(response.folder);

                    // Render files
                    if (fileViewMode === 'grid') {
                        renderFilesGrid(response.files);
                    } else {
                        renderFilesTable(response.files);
                    }

                    $('#loadingFiles').hide();

                    // Show empty state if no files
                    if (!Array.isArray(response.files) || response.files.length === 0) {
                        $('#emptyFilesState').show();
                    }

                    // Load file jenis for upload form
                    loadJenisDokumen();
                },
                error: function(xhr) {
                    $('#loadingFiles').hide();
                    let errorMessage = 'Gagal memuat file dalam folder';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        }

        // Function to update folder info
        function updateFolderInfo(folder) {
            // Update title and breadcrumb
            $('#folder-title').text(`Files in ${folder.nama}`);
            $('#current-folder-name').text(folder.nama);

            // Update folder info card
            $('#folder-name').text(folder.nama);
            $('#folder-details').text(folder.path);
            $('#folder-path').text(folder.path);
            $('#folder-level').text(`Level ${folder.level}`);

            // Update folder status badge
            if (folder.is_auto) {
                $('#folder-auto-status').html('<i class="bi bi-gear-fill me-1"></i>Auto');
                $('#folder-auto-status').removeClass('bg-secondary').addClass('bg-auto');
            } else {
                $('#folder-auto-status').html('<i class="bi bi-hand-thumbs-up me-1"></i>Manual');
                $('#folder-auto-status').removeClass('bg-auto').addClass('bg-secondary');
            }

            // Update file count
            const fileCount = Array.isArray(allFiles) ? allFiles.length : 0;
            $('#folder-file-count').html(`<i class="bi bi-file-earmark me-1"></i>${fileCount} Files`);

            // Set folder ID for upload form
            $('#upload_folder_id').val(folder.id);
        }

        // Function to toggle file view
        function toggleFileView() {
            if (fileViewMode === 'grid') {
                $('#filesGridView').show();
                $('#filesTableView').hide();
                if (filesDataTable) {
                    filesDataTable.destroy();
                    filesDataTable = null;
                }
            } else {
                $('#filesGridView').hide();
                $('#filesTableView').show();
                renderFilesTable(allFiles);
            }
        }

        // Function to render files grid
        function renderFilesGrid(files) {
            let html = '';

            if (!Array.isArray(files) || files.length === 0) {
                $('#filesGrid').html('');
                return;
            }

            files.forEach(function(file) {
                const fileType = getFileType(file.extension);
                const iconClass = getFileIconClass(fileType);
                const date = formatDateTime(file.created_at);
                const fileSize = formatFileSize(file.size);

                html += `
                    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                        <div class="card file-card shadow-sm">
                            <div class="file-type-badge" style="color: ${getFileTypeColor(fileType)}">
                                ${file.extension.toUpperCase()}
                            </div>
                            <div class="file-actions">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-sm btn-light" onclick="previewFile('${file.id}', '${file.file_path}', '${file.original_name}')" title="Preview">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="/dokumen/file/${file.id}/download" class="btn btn-sm btn-light" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <button class="btn btn-sm btn-light" onclick="showFileDetails('${file.id}')" title="Details">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body text-center pt-4">
                                <div class="file-icon-wrapper ${iconClass}">
                                    <i class="bi ${getFileIconByType(fileType)} file-icon"></i>
                                </div>
                                <h5 class="mb-2 fw-bold file-name">${file.nama}</h5>
                                <p class="text-muted small mb-2 text-truncate">${file.original_name}</p>
                                <div class="file-meta mt-2">
                                    <div>
                                        <span class="file-size">${fileSize}</span>
                                    </div>
                                    <div class="file-date">
                                        <i class="bi bi-calendar me-1"></i>${date}
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 pb-3 px-3">
                                <div class="d-grid">
                                    <button class="btn btn-sm btn-outline-primary" onclick="previewFile('${file.id}', '${file.file_path}', '${file.original_name}')">
                                        <i class="bi bi-eye me-1"></i>Preview
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            $('#filesGrid').html(html);
        }

        // Function to render files table
        function renderFilesTable(files) {
            // Destroy existing datatable if it exists
            if (filesDataTable) {
                filesDataTable.destroy();
                filesDataTable = null;
            }

            let tbody = '';

            if (!Array.isArray(files) || files.length === 0) {
                tbody = `
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-file-earmark-x" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">No files found</p>
                        </td>
                    </tr>
                `;
            } else {
                files.forEach((file, index) => {
                    const fileType = getFileType(file.extension);
                    const iconClass = getFileIconClass(fileType);
                    const date = formatDateTime(file.created_at);
                    const fileSize = formatFileSize(file.size);

                    tbody += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="file-icon-wrapper me-3" style="width: 40px; height: 40px; ${getFileIconStyle(fileType)}">
                                        <i class="bi ${getFileIconByType(fileType)}" style="font-size: 1.2rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">${file.nama}</h6>
                                        <small class="text-muted">${file.original_name}</small>
                                    </div>
                                </div>
                            </td>
                            <td>${file.jenis ? file.jenis.nama : '-'}</td>
                            <td>${fileSize}</td>
                            <td>${file.uploader ? file.uploader.name : '-'}</td>
                            <td>${date}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="previewFile('${file.id}', '${file.file_path}', '${file.original_name}')" title="Preview">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="/dokumen/file/${file.id}/download" class="btn btn-outline-success" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <button class="btn btn-outline-info" onclick="showFileDetails('${file.id}')" title="Details">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }

            $('#filesTable tbody').html(tbody);

            // Initialize DataTable
            if (files.length > 0) {
                filesDataTable = new simpleDatatables.DataTable("#filesTable", {
                    searchable: true,
                    fixedHeight: false,
                    perPage: 10
                });
            }
        }

        // Function to filter files
        function filterFiles(query) {
            if (!query || query === '') {
                // If query is empty, show all files
                if (fileViewMode === 'grid') {
                    renderFilesGrid(allFiles);
                } else {
                    renderFilesTable(allFiles);
                }
                return;
            }

            // Filter files based on query
            const filteredFiles = allFiles.filter(file => {
                return file.nama.toLowerCase().includes(query) ||
                    file.original_name.toLowerCase().includes(query) ||
                    (file.jenis && file.jenis.nama.toLowerCase().includes(query)) ||
                    file.extension.toLowerCase().includes(query);
            });

            // Update view with filtered files
            if (fileViewMode === 'grid') {
                renderFilesGrid(filteredFiles);
            } else {
                renderFilesTable(filteredFiles);
            }
        }

        // Function to sort files
        function sortFiles(sortBy) {
            let sortedFiles = [...allFiles];

            switch (sortBy) {
                case 'name':
                    sortedFiles.sort((a, b) => a.nama.localeCompare(b.nama));
                    break;
                case 'date':
                    sortedFiles.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    break;
                case 'size':
                    sortedFiles.sort((a, b) => b.size - a.size);
                    break;
                case 'type':
                    sortedFiles.sort((a, b) => a.extension.localeCompare(b.extension));
                    break;
                default:
                    break;
            }

            // Update view with sorted files
            if (fileViewMode === 'grid') {
                renderFilesGrid(sortedFiles);
            } else {
                renderFilesTable(sortedFiles);
            }
        }

        // Function to filter by file type
        function filterByType(type) {
            if (!type || type === '') {
                // If type is empty, show all files
                if (fileViewMode === 'grid') {
                    renderFilesGrid(allFiles);
                } else {
                    renderFilesTable(allFiles);
                }
                return;
            }

            // Filter files based on type
            const filteredFiles = allFiles.filter(file => {
                const extension = file.extension.toLowerCase();

                switch (type) {
                    case 'pdf':
                        return extension === 'pdf';
                    case 'docx':
                        return ['doc', 'docx'].includes(extension);
                    case 'xlsx':
                        return ['xls', 'xlsx', 'csv'].includes(extension);
                    case 'image':
                        return ['jpg', 'jpeg', 'png', 'gif', 'bmp'].includes(extension);
                    case 'other':
                        return !['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'bmp']
                            .includes(extension);
                    default:
                        return true;
                }
            });

            // Update view with filtered files
            if (fileViewMode === 'grid') {
                renderFilesGrid(filteredFiles);
            } else {
                renderFilesTable(filteredFiles);
            }
        }

        // Function to show upload modal
        function showUploadModal(folderId) {
            $('#upload_folder_id').val(folderId);
            $('#formUploadFile')[0].reset();

            // Get allowed extensions from folder's jenis (if any)
            let allowedExtText = "Format yang diizinkan: PDF, DOCX, XLSX (Max: 10MB)";
            $('#allowedExtensions').text(allowedExtText);

            $('#modalUploadFile').modal('show');
        }

        // Function to load jenis dokumen
        function loadJenisDokumen() {
            $.ajax({
                url: '/dokumen/jenis',
                type: 'GET',
                success: function(response) {
                    let options = '<option value="">Pilih Jenis Dokumen</option>';

                    response.forEach(function(jenis) {
                        options += `<option value="${jenis.id}">${jenis.nama}</option>`;
                    });

                    $('#jenis_id').html(options);
                },
                error: function(xhr) {
                    console.error('Failed to load jenis dokumen:', xhr);
                }
            });
        }

        // Function to upload file
        function uploadFile() {
            const formData = new FormData(document.getElementById('formUploadFile'));

            $.ajax({
                url: '/dokumen/file/upload',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#btnUploadFile').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...'
                    );
                },
                success: function(response) {
                    $('#modalUploadFile').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'File berhasil diupload',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Reload folder files
                    loadFolderFiles(currentFolderId);
                },
                error: function(xhr) {
                    let errorMessage = 'Upload failed';

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
                        title: 'Upload Failed',
                        html: errorMessage,
                        confirmButtonColor: '#0d6efd'
                    });
                },
                complete: function() {
                    $('#btnUploadFile').prop('disabled', false).html(
                        '<i class="bi bi-upload me-1"></i>Upload'
                    );
                }
            });
        }

        // Function to preview file
        function previewFile(id, filePath, originalName) {
            $('#previewFileName').html(`<i class="bi bi-file-earmark me-2"></i>${originalName}`);
            $('#downloadFileBtn').attr('href', `/dokumen/file/${id}/download`);

            const extension = getFileExtension(originalName).toLowerCase();
            let previewHtml = '';

            // Check if file is previewable
            if (['jpg', 'jpeg', 'png', 'gif', 'bmp'].includes(extension)) {
                // Image preview
                previewHtml = `<img src="/storage/${filePath}" class="img-fluid">`;
            } else if (extension === 'pdf') {
                // PDF preview
                previewHtml = `<iframe src="/storage/${filePath}" width="100%" height="600"></iframe>`;
            } else {
                // Non-previewable file
                previewHtml = `
                    <div class="file-preview-placeholder">
                        <div class="file-icon-wrapper mb-4" style="width: 100px; height: 100px; ${getFileIconStyle(getFileType(extension))}">
                            <i class="bi ${getFileIconByType(getFileType(extension))}" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="mb-3">${originalName}</h4>
                        <p class="text-muted mb-4">This file cannot be previewed directly.</p>
                        <a href="/dokumen/file/${id}/download" class="btn btn-primary px-4">
                            <i class="bi bi-download me-2"></i>Download File
                        </a>
                    </div>
                `;
            }

            $('#filePreviewContent').html(previewHtml);
            $('#modalFilePreview').modal('show');
        }

        // Function to show file details
        function showFileDetails(id) {
            $.ajax({
                url: `/dokumen/file/${id}`,
                type: 'GET',
                success: function(response) {
                    const file = response;
                    const fileType = getFileType(file.extension);
                    const iconClass = getFileIconClass(fileType);

                    let html = `
                        <div class="text-center mb-4">
                            <div class="file-icon-wrapper mb-3" style="width: 80px; height: 80px; ${getFileIconStyle(fileType)}">
                                <i class="bi ${getFileIconByType(fileType)}" style="font-size: 2.5rem;"></i>
                            </div>
                            <h4 class="mb-1 fw-bold">${file.nama}</h4>
                            <p class="text-muted">${file.original_name}</p>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-layers text-primary me-2"></i>
                                            <small class="text-muted">Jenis Dokumen</small>
                                        </div>
                                        <h6 class="mb-0 fw-bold">${file.jenis ? file.jenis.nama : '-'}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-file-earmark text-primary me-2"></i>
                                            <small class="text-muted">File Type</small>
                                        </div>
                                        <h6 class="mb-0 fw-bold">${file.extension.toUpperCase()}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-hdd text-primary me-2"></i>
                                            <small class="text-muted">File Size</small>
                                        </div>
                                        <h6 class="mb-0 fw-bold">${formatFileSize(file.size)}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-person text-primary me-2"></i>
                                            <small class="text-muted">Uploaded By</small>
                                        </div>
                                        <h6 class="mb-0 fw-bold">${file.uploader ? file.uploader.name : '-'}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-calendar-plus text-primary me-2"></i>
                                            <small class="text-muted">Upload Date</small>
                                        </div>
                                        <h6 class="mb-0 fw-bold">${formatDateTime(file.created_at)}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-folder text-primary me-2"></i>
                                            <small class="text-muted">Folder</small>
                                        </div>
                                        <h6 class="mb-0 fw-bold">${folderData.nama}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${file.deskripsi ? `
                                    <div class="mt-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-card-text text-primary me-2"></i>
                                                    <small class="text-muted">Description</small>
                                                </div>
                                                <p class="mb-0">${file.deskripsi}</p>
                                            </div>
                                        </div>
                                    </div>
                                ` : ''}
                        
                        <div class="d-grid gap-2 mt-4">
                            <a href="/dokumen/file/${file.id}/download" class="btn btn-primary">
                                <i class="bi bi-download me-2"></i>Download File
                            </a>
                        </div>
                    `;

                    $('#fileDetailsContent').html(html);
                    $('#modalFileDetails').modal('show');
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to load file details';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        }

        // Function to go back to folders view
        function backToFolders() {
            window.location.href = "{{ route('dokumen.folder.index') }}";
        }

        // Helper function to get file extension
        function getFileExtension(filename) {
            return filename.split('.').pop();
        }

        // Helper function to get file type
        function getFileType(extension) {
            if (!extension) return 'other';

            extension = extension.toLowerCase();

            if (['pdf'].includes(extension)) {
                return 'pdf';
            } else if (['doc', 'docx', 'rtf'].includes(extension)) {
                return 'doc';
            } else if (['xls', 'xlsx', 'csv'].includes(extension)) {
                return 'xls';
            } else if (['ppt', 'pptx'].includes(extension)) {
                return 'ppt';
            } else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'].includes(extension)) {
                return 'img';
            } else if (['zip', 'rar', '7z', 'tar', 'gz'].includes(extension)) {
                return 'zip';
            } else if (['txt', 'md', 'json', 'xml', 'html', 'css', 'js'].includes(extension)) {
                return 'txt';
            } else {
                return 'other';
            }
        }

        // Helper function to get file icon class
        function getFileIconClass(type) {
            return `file-icon-${type}`;
        }

        // Helper function to get file icon
        function getFileIconByType(type) {
            switch (type) {
                case 'pdf':
                    return 'bi-file-earmark-pdf-fill';
                case 'doc':
                    return 'bi-file-earmark-word-fill';
                case 'xls':
                    return 'bi-file-earmark-excel-fill';
                case 'ppt':
                    return 'bi-file-earmark-ppt-fill';
                case 'img':
                    return 'bi-file-earmark-image-fill';
                case 'zip':
                    return 'bi-file-earmark-zip-fill';
                case 'txt':
                    return 'bi-file-earmark-text-fill';
                default:
                    return 'bi-file-earmark-fill';
            }
        }

        // Helper function to get file icon style
        function getFileIconStyle(type) {
            switch (type) {
                case 'pdf':
                    return 'background-color: #ffefef; color: #dc3545; display: flex; align-items: center; justify-content: center;';
                case 'doc':
                    return 'background-color: #e7f1ff; color: #0d6efd; display: flex; align-items: center; justify-content: center;';
                case 'xls':
                    return 'background-color: #e7ffe7; color: #198754; display: flex; align-items: center; justify-content: center;';
                case 'ppt':
                    return 'background-color: #fff4e5; color: #fd7e14; display: flex; align-items: center; justify-content: center;';
                case 'img':
                    return 'background-color: #e7f5ff; color: #0dcaf0; display: flex; align-items: center; justify-content: center;';
                case 'zip':
                    return 'background-color: #fff4e5; color: #fd7e14; display: flex; align-items: center; justify-content: center;';
                case 'txt':
                    return 'background-color: #f8f9fa; color: #6c757d; display: flex; align-items: center; justify-content: center;';
                default:
                    return 'background-color: #f8f9fa; color: #6c757d; display: flex; align-items: center; justify-content: center;';
            }
        }

        // Helper function to get file type color
        function getFileTypeColor(type) {
            switch (type) {
                case 'pdf':
                    return '#dc3545';
                case 'doc':
                    return '#0d6efd';
                case 'xls':
                    return '#198754';
                case 'ppt':
                    return '#fd7e14';
                case 'img':
                    return '#0dcaf0';
                case 'zip':
                    return '#fd7e14';
                case 'txt':
                    return '#6c757d';
                default:
                    return '#6c757d';
            }
        }

        // Helper function to format file size
        function formatFileSize(bytes) {
            if (!bytes || bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Helper function to format date time
        function formatDateTime(dateString) {
            if (!dateString) return '-';

            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
@endpush
