@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Manajemen Folder Dokumen</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dokumen.index') }}">Dokumen</a></li>
                <li class="breadcrumb-item active">Folder</li>
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
                                    <div class="icon-box bg-primary bg-opacity-10 rounded-3 p-2 me-3">
                                        <i class="bi bi-folder-fill text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Folder Dokumen</span>
                                        <small class="d-block text-muted fw-normal mt-1">Kelola struktur folder untuk
                                            organisasi dokumen yang lebih baik</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary btn-lg shadow-sm px-4 py-2"
                                    onclick="showCreateModal()">
                                    <i class="bi bi-folder-plus me-2"></i>Tambah Folder
                                </button>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="stats-card bg-gradient-primary text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Total Folder</h6>
                                            <h3 class="mb-0 fw-bold" id="totalFolder">0</h3>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="bi bi-folder-fill" style="font-size: 2.5rem; "></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card bg-gradient-success text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Root Folder</h6>
                                            <h3 class="mb-0 fw-bold" id="totalRootFolder">0</h3>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="bi bi-folder2-open" style="font-size: 2.5rem; "></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card bg-gradient-info text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Auto Folder</h6>
                                            <h3 class="mb-0 fw-bold" id="totalAutoFolder">0</h3>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="bi bi-gear-fill" style="font-size: 2.5rem; "></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card bg-gradient-warning text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Total Files</h6>
                                            <h3 class="mb-0 fw-bold" id="totalFiles">0</h3>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="bi bi-file-earmark-text" style="font-size: 2.5rem; "></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- View Toggle -->
                        <div class="mb-3">
                            <div class="btn-group shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="viewMode" id="viewTree" checked>
                                <label class="btn btn-outline-primary px-4" for="viewTree">
                                    <i class="bi bi-diagram-3 me-2"></i>Tree View
                                </label>
                                <input type="radio" class="btn-check" name="viewMode" id="viewGrid">
                                <label class="btn btn-outline-primary px-4" for="viewGrid">
                                    <i class="bi bi-grid-3x3-gap me-2"></i>Grid View
                                </label>
                                <input type="radio" class="btn-check" name="viewMode" id="viewTable">
                                <label class="btn btn-outline-primary px-4" for="viewTable">
                                    <i class="bi bi-table me-2"></i>Table View
                                </label>
                            </div>
                        </div>

                        <!-- Tree View -->
                        <div id="treeView">
                            <div class="folder-tree-container p-3 bg-light rounded-3">
                                <div id="folderTree"></div>
                            </div>
                        </div>

                        <!-- Grid View -->
                        <div id="gridView" style="display: none;">
                            <div class="row" id="folderGrid"></div>
                        </div>

                        <!-- Table View -->
                        <div id="tableView" style="display: none;">
                            <table class="table datatable" id="folderTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Folder</th>
                                        <th>Path</th>
                                        <th>Bidang</th>
                                        <th>Level</th>
                                        <th>Total Files</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="modalFolder" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-primary text-white pb-3 pt-3">
                    <h5 class="modal-title fw-bold" id="modalTitle">
                        <i class="bi bi-folder-plus me-2"></i>Tambah Folder
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formFolder">
                        @csrf
                        <input type="hidden" id="folder_id" name="folder_id">
                        <input type="hidden" id="_method" name="_method" value="POST">

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">
                                Parent Folder
                            </label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">Root Folder (Tidak ada parent)</option>
                            </select>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Pilih folder parent untuk membuat subfolder
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="bidang_id" class="form-label">
                                Bidang
                            </label>
                            <select class="form-select" id="bidang_id" name="bidang_id">
                                <option value="">Pilih Bidang (Opsional)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label">
                                Nama Folder <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nama" name="nama" maxlength="100"
                                required placeholder="Contoh: Arsip 2025">
                        </div>

                        <div class="mb-3">
                            <label for="path" class="form-label">
                                Path <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="path" name="path" required
                                placeholder="/root/folder">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Path lengkap folder (unik)
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="level" class="form-label">
                                Level
                            </label>
                            <input type="number" class="form-control" id="level" name="level" placeholder="0"
                                value="0" min="0">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Level kedalaman folder (0 = root)
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_auto" name="is_auto">
                                <label class="form-check-label" for="is_auto">
                                    Auto Generated Folder
                                </label>
                                <small class="d-block text-muted ms-4">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Folder dibuat otomatis oleh sistem
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="btnSaveFolder">
                        <i class="bi bi-check me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetailFolder" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-primary text-white pb-4 pt-4">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="bi bi-folder-fill me-2"></i>Detail Folder
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="detailFolderContent"></div>
                <div class="modal-footer bg-light border-0 p-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Stats Cards */
        .stats-card {
            border-radius: 16px !important;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        }

        /* Folder Tree */
        .folder-tree-container {
            min-height: 400px;
            max-height: 600px;
            overflow-y: auto;
        }

        .folder-item {
            padding: 12px 16px;
            margin: 6px 0;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .folder-item:hover {
            background: #f8f9fa;
            border-color: #0d6efd;
            transform: translateX(5px);
        }

        .folder-item.has-children::before {
            content: '▼';
            display: inline-block;
            margin-right: 8px;
            transition: transform 0.3s ease;
        }

        .folder-item.collapsed::before {
            transform: rotate(-90deg);
        }

        .folder-children {
            margin-left: 30px;
            border-left: 2px dashed #dee2e6;
            padding-left: 10px;
        }

        /* Folder Card */
        .folder-card {
            position: relative;
            overflow: hidden;
            border-radius: 16px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid #f0f0f0 !important;
        }

        .folder-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
            border-color: #0d6efd !important;
        }

        .folder-icon-wrapper {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border-radius: 20px;
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            transition: all 0.3s ease;
        }

        .folder-card:hover .folder-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        .folder-icon {
            font-size: 2.5rem;
            color: white;
        }

        /* Badge */
        .badge-auto {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
        }

        .badge-level {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
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
    </style>
@endsection

@push('scripts')
    <script>
        let allFolders = [];
        let allBidang = [];
        let viewMode = 'tree';
        let dataTable = null;

        $(document).ready(function() {
            loadBidang();
            loadFolders();

            // View mode toggle
            $('input[name="viewMode"]').change(function() {
                const selectedView = $(this).attr('id');
                viewMode = selectedView === 'viewTree' ? 'tree' : (selectedView === 'viewGrid' ? 'grid' :
                    'table');
                toggleView();
            });

            $('#btnSaveFolder').click(function() {
                saveFolder();
            });

            // Auto generate path from nama
            $('#nama').on('input', function() {
                if (!$('#folder_id').val()) {
                    const parentPath = $('#parent_id option:selected').data('path') || '';
                    const folderName = $(this).val().toLowerCase().replace(/\s+/g, '-').replace(
                        /[^a-z0-9-]/g, '');
                    const newPath = parentPath ? `${parentPath}/${folderName}` : `/${folderName}`;
                    $('#path').val(newPath);
                }
            });

            // Update path when parent changes
            $('#parent_id').change(function() {
                const parentPath = $(this).find('option:selected').data('path') || '';
                const parentLevel = parseInt($(this).find('option:selected').data('level') || 0);
                const folderName = $('#nama').val().toLowerCase().replace(/\s+/g, '-').replace(
                    /[^a-z0-9-]/g, '');

                if (folderName) {
                    const newPath = parentPath ? `${parentPath}/${folderName}` : `/${folderName}`;
                    $('#path').val(newPath);
                }

                $('#level').val(parentPath ? parentLevel + 1 : 0);
            });
        });

        function loadBidang() {
            $.ajax({
                url: '/master/bidang',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Bidang loaded:', response); // Debug
                    allBidang = Array.isArray(response) ? response : [];
                    let options = '<option value="">Pilih Bidang (Opsional)</option>';
                    allBidang.forEach(function(item) {
                        options += `<option value="${item.id}">${item.nama}</option>`;
                    });
                    $('#bidang_id').html(options);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading bidang:', error);
                    // Fallback: coba endpoint alternatif
                    $.ajax({
                        url: '/dokumen/folder/bidang',
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            console.log('Bidang loaded (alternative):', response);
                            allBidang = Array.isArray(response) ? response : [];
                            let options = '<option value="">Pilih Bidang (Opsional)</option>';
                            allBidang.forEach(function(item) {
                                options +=
                                    `<option value="${item.id}">${item.nama}</option>`;
                            });
                            $('#bidang_id').html(options);
                        },
                        error: function() {
                            console.error('Failed to load bidang from both endpoints');
                        }
                    });
                }
            });
        }

        function loadFolders() {
            $.ajax({
                url: '{{ route('dokumen.folder.index') }}',
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    console.log('Folders loaded:', response); // Debug
                    allFolders = Array.isArray(response) ? response : [];
                    updateStats(allFolders);
                    updateParentOptions(allFolders);

                    if (viewMode === 'tree') {
                        renderTree(allFolders);
                    } else if (viewMode === 'grid') {
                        renderGrid(allFolders);
                    } else {
                        renderTable(allFolders);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading folders:', error);
                    showError('Gagal memuat data folder: ' + error);
                }
            });
        }

        function updateStats(data) {
            if (!Array.isArray(data)) return;

            $('#totalFolder').text(data.length);

            const rootFolders = data.filter(f => !f.parent_id);
            $('#totalRootFolder').text(rootFolders.length);

            const autoFolders = data.filter(f => f.is_auto);
            $('#totalAutoFolder').text(autoFolders.length);

            const totalFiles = data.reduce((sum, f) => sum + (f.total_files || 0), 0);
            $('#totalFiles').text(totalFiles);
        }

        function updateParentOptions(folders) {
            let options = '<option value="">Root Folder (Tidak ada parent)</option>';
            folders.forEach(function(folder) {
                const indent = '—'.repeat(folder.level);
                options +=
                    `<option value="${folder.id}" data-path="${folder.path}" data-level="${folder.level}">${indent} ${folder.nama}</option>`;
            });
            $('#parent_id').html(options);
        }

        function toggleView() {
            $('#treeView, #gridView, #tableView').hide();

            if (viewMode === 'tree') {
                $('#treeView').show();
                renderTree(allFolders);
            } else if (viewMode === 'grid') {
                $('#gridView').show();
                renderGrid(allFolders);
            } else {
                $('#tableView').show();
                renderTable(allFolders);
            }
        }

        function renderTree(folders) {
            if (!Array.isArray(folders) || folders.length === 0) {
                $('#folderTree').html(`
                    <div class="empty-state text-center">
                        <i class="bi bi-folder-x empty-state-icon"></i>
                        <h4 class="text-muted mb-2">Belum ada folder</h4>
                        <p class="text-muted mb-4">Mulai dengan menambahkan folder baru</p>
                        <button class="btn btn-primary btn-lg px-5" onclick="showCreateModal()">
                            <i class="bi bi-folder-plus me-2"></i>Tambah Folder Pertama
                        </button>
                    </div>
                `);
                return;
            }

            const rootFolders = folders.filter(f => !f.parent_id);
            let html = buildTreeHTML(rootFolders, folders);
            $('#folderTree').html(html);
        }

        function buildTreeHTML(items, allItems) {
            let html = '';
            items.forEach(item => {
                const children = allItems.filter(f => f.parent_id === item.id);
                const hasChildren = children.length > 0;
                const bidangName = item.bidang ? item.bidang.nama : '-';

                html += `
            <div class="folder-item ${hasChildren ? 'has-children' : ''}" data-id="${item.id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div onclick="navigateToFolderFiles(${item.id})" style="cursor:pointer; flex-grow: 1;">
                        <i class="bi bi-folder-fill text-warning me-2"></i>
                        <strong>${item.nama}</strong>
                        <small class="text-muted ms-2">${item.path}</small>
                        ${item.is_auto ? '<span class="badge badge-auto ms-2"><i class="bi bi-gear-fill me-1"></i>Auto</span>' : ''}
                    </div>
                    <div>
                        <span class="badge bg-secondary me-2">${item.total_files} files</span>
                        <button class="btn btn-sm btn-outline-primary" onclick="showDetail(${item.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="showEditModal(${item.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteFolder(${item.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

                if (hasChildren) {
                    html += `<div class="folder-children">${buildTreeHTML(children, allItems)}</div>`;
                }
            });
            return html;
        }

        function navigateToFolderFiles(folderId) {
            window.location.href = `/dokumen/folder/${folderId}/dokumen`;
        }

        // First, add the navigation function
        function navigateToFolderFiles(folderId) {
            window.location.href = `/dokumen/folder/${folderId}/dokumen`;
        }

        // Tree View - Already works, but let's make it more explicit
        function buildTreeHTML(items, allItems) {
            let html = '';
            items.forEach(item => {
                const children = allItems.filter(f => f.parent_id === item.id);
                const hasChildren = children.length > 0;
                const bidangName = item.bidang ? item.bidang.nama : '-';

                html += `
            <div class="folder-item ${hasChildren ? 'has-children' : ''}" data-id="${item.id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div onclick="navigateToFolderFiles(${item.id})" style="cursor:pointer; flex-grow: 1;">
                        <i class="bi bi-folder-fill text-warning me-2"></i>
                        <strong>${item.nama}</strong>
                        <small class="text-muted ms-2">${item.path}</small>
                        ${item.is_auto ? '<span class="badge badge-auto ms-2"><i class="bi bi-gear-fill me-1"></i>Auto</span>' : ''}
                    </div>
                    <div>
                        <span class="badge bg-secondary me-2">${item.total_files} files</span>
                        <button class="btn btn-sm btn-outline-primary" onclick="navigateToFolderFiles(${item.id}); event.stopPropagation();">
                            <i class="bi bi-folder2-open me-1"></i>Dokumen
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="showDetail(${item.id}); event.stopPropagation();">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="showEditModal(${item.id}); event.stopPropagation();">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteFolder(${item.id}); event.stopPropagation();">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

                if (hasChildren) {
                    html += `<div class="folder-children">${buildTreeHTML(children, allItems)}</div>`;
                }
            });
            return html;
        }

        // Grid View - Update to make clickable
        function renderGrid(folders) {
            if (!Array.isArray(folders) || folders.length === 0) {
                $('#folderGrid').html(`
            <div class="col-12 empty-state text-center">
                <i class="bi bi-folder-x empty-state-icon"></i>
                <h4 class="text-muted mb-2">Belum ada folder</h4>
                <p class="text-muted mb-4">Mulai dengan menambahkan folder baru</p>
                <button class="btn btn-primary btn-lg px-5" onclick="showCreateModal()">
                    <i class="bi bi-folder-plus me-2"></i>Tambah Folder Pertama
                </button>
            </div>
        `);
                return;
            }

            let html = '<div class="row">';
            folders.forEach(item => {
                const bidangName = item.bidang ? item.bidang.nama : 'Tidak ada bidang';
                html += `
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="card folder-card h-100 shadow-sm" onclick="navigateToFolderFiles(${item.id})" style="cursor:pointer;">
                    <div class="card-body text-center">
                        <div class="folder-icon-wrapper mt-3">
                            <i class="bi bi-folder-fill folder-icon"></i>
                        </div>
                        <h5 class="mb-2 fw-bold">${item.nama}</h5>
                        <p class="text-muted small mb-2">${item.path}</p>
                        <span class="badge bg-secondary mb-2">${bidangName}</span>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <span class="badge badge-level">Level ${item.level}</span>
                            ${item.is_auto ? '<span class="badge badge-auto"><i class="bi bi-gear-fill me-1"></i>Auto</span>' : ''}
                        </div>
                        <div class="mt-3">
                            <h6 class="mb-0">${item.total_files} <small class="text-muted">files</small></h6>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-3 px-3" onclick="event.stopPropagation();">
                        <div class="d-grid gap-2">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="navigateToFolderFiles(${item.id})">
                                    <i class="bi bi-folder2-open me-1"></i>Dokumen
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="showDetail(${item.id})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="showEditModal(${item.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteFolder(${item.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
            });
            html += '</div>';
            $('#folderGrid').html(html);
        }

        // Table View - Update to make clickable
        function renderTable(folders) {
            if (dataTable) {
                dataTable.destroy();
            }

            let tbody = '';
            folders.forEach((item, index) => {
                const bidangName = item.bidang ? item.bidang.nama : '-';
                tbody += `
            <tr style="cursor:pointer;" onclick="navigateToFolderFiles(${item.id})">
                <td>${index + 1}</td>
                <td><strong>${item.nama}</strong></td>
                <td><code>${item.path}</code></td>
                <td>${bidangName}</td>
                <td><span class="badge badge-level">Level ${item.level}</span></td>
                <td><span class="badge bg-secondary">${item.total_files}</span></td>
                <td>
                    ${item.is_auto ? '<span class="badge badge-auto"><i class="bi bi-gear-fill me-1"></i>Auto</span>' : '<span class="badge bg-light text-dark">Manual</span>'}
                </td>
                <td onclick="event.stopPropagation();">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="navigateToFolderFiles(${item.id})">
                            <i class="bi bi-folder2-open me-1"></i>Dokumen
                        </button>
                        <button class="btn btn-outline-secondary" onclick="showDetail(${item.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="showEditModal(${item.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteFolder(${item.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
            });

            $('#folderTable tbody').html(tbody);

            dataTable = new simpleDatatables.DataTable("#folderTable", {
                searchable: true,
                fixedHeight: false,
                perPage: 10,
                labels: {
                    placeholder: "Cari folder...",
                    perPage: "Data per halaman",
                    noRows: "Tidak ada data",
                    info: "Menampilkan {start} sampai {end} dari {rows} data",
                }
            });

            // Add clickable styles after DataTable initialization
            $('#folderTable tbody tr').css('cursor', 'pointer');
        }

        function showCreateModal() {
            $('#modalTitle').html('<i class="bi bi-folder-plus me-2"></i>Tambah Folder');
            $('#formFolder')[0].reset();
            $('#folder_id').val('');
            $('#_method').val('POST');
            $('#level').val(0);
            $('#is_auto').prop('checked', false);

            // Reload bidang jika dropdown kosong
            if ($('#bidang_id option').length <= 1) {
                console.log('Bidang dropdown empty, reloading...');
                loadBidang();
            }

            $('#modalFolder').modal('show');
        }

        function showEditModal(id) {
            $.ajax({
                url: `/dokumen/folder/${id}`,
                type: 'GET',
                success: function(response) {
                    $('#modalTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Folder');
                    $('#folder_id').val(response.id);
                    $('#_method').val('PUT');
                    $('#parent_id').val(response.parent_id || '');
                    $('#bidang_id').val(response.bidang_id || '');
                    $('#nama').val(response.nama);
                    $('#path').val(response.path);
                    $('#level').val(response.level);
                    $('#is_auto').prop('checked', response.is_auto ? true : false);

                    // Reload bidang jika dropdown kosong
                    if ($('#bidang_id option').length <= 1) {
                        console.log('Bidang dropdown empty in edit, reloading...');
                        loadBidang();

                        // Set value setelah reload
                        setTimeout(function() {
                            $('#bidang_id').val(response.bidang_id || '');
                        }, 500);
                    }

                    $('#modalFolder').modal('show');
                },
                error: function() {
                    showError('Gagal memuat data folder');
                }
            });
        }

        function saveFolder() {
            let id = $('#folder_id').val();
            let method = $('#_method').val();
            let url = id ? `/dokumen/folder/${id}` : '/dokumen/folder';

            let formData = {
                _token: '{{ csrf_token() }}',
                parent_id: $('#parent_id').val() || null,
                bidang_id: $('#bidang_id').val() || null,
                nama: $('#nama').val(),
                path: $('#path').val(),
                level: $('#level').val(),
                is_auto: $('#is_auto').prop('checked') ? 1 : 0
            };

            if (method === 'PUT') {
                formData._method = 'PUT';
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('#btnSaveFolder').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...'
                    );
                },
                success: function(response) {
                    console.log('Save response:', response); // Debug
                    $('#modalFolder').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || (method === 'PUT' ? 'Folder berhasil diupdate' :
                            'Folder berhasil ditambahkan'),
                        timer: 2000,
                        showConfirmButton: false
                    });

                    loadFolders();
                },
                error: function(xhr) {
                    console.error('Save error:', xhr); // Debug
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
                        '<i class="bi bi-check me-1"></i>Simpan'
                    );
                }
            });
        }

        function showDetail(id) {
            $.ajax({
                url: `/dokumen/folder/${id}`,
                type: 'GET',
                success: function(response) {
                    const bidangName = response.bidang ? response.bidang.nama : 'Tidak ada';
                    const parentName = response.parent ? response.parent.nama : 'Root Folder';
                    const creatorName = response.creator ? response.creator.nama : 'Unknown';

                    let html = `
                        <div class="text-center mb-4 p-4 rounded-3" style="background: linear-gradient(135deg, #ffc10715 0%, #ff980005 100%);">
                            <div class="d-inline-block p-4 rounded-circle mb-3" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                                <i class="bi bi-folder-fill" style="font-size: 5rem; color: white;"></i>
                            </div>
                            <h3 class="fw-bold mb-2">${response.nama}</h3>
                            <p class="text-muted mb-2"><code>${response.path}</code></p>
                            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2">
                                <span class="badge badge-level px-3 py-2">Level ${response.level}</span>
                                ${response.is_auto ? 
                                    '<span class="badge badge-auto px-3 py-2"><i class="bi bi-gear-fill me-1"></i>Auto Generated</span>' : 
                                    '<span class="badge bg-secondary px-3 py-2"><i class="bi bi-hand-thumbs-up me-1"></i>Manual</span>'}
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-folder-fill text-primary me-2"></i>
                                            <small class="text-muted">Parent Folder</small>
                                        </div>
                                        <h6 class="mb-0 fw-bold">${parentName}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-building text-primary me-2"></i>
                                            <small class="text-muted">Bidang</small>
                                        </div>
                                        <h6 class="mb-0 fw-bold">${bidangName}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                            <small class="text-muted">Total Files</small>
                                        </div>
                                        <h5 class="mb-0 fw-bold">${response.total_files}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-person-fill text-primary me-2"></i>
                                            <small class="text-muted">Dibuat Oleh</small>
                                        </div>
                                        <h6 class="mb-0 fw-bold">${creatorName}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-calendar-plus text-primary me-2"></i>
                                            <small class="text-muted">Dibuat</small>
                                        </div>
                                        <p class="mb-0 fw-semibold">${formatDateTime(response.created_at)}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-calendar-check text-primary me-2"></i>
                                            <small class="text-muted">Terakhir Diupdate</small>
                                        </div>
                                        <p class="mb-0 fw-semibold">${formatDateTime(response.updated_at)}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    $('#detailFolderContent').html(html);
                    $('#modalDetailFolder').modal('show');
                },
                error: function() {
                    showError('Gagal memuat detail folder');
                }
            });
        }

        function deleteFolder(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Folder dan semua isinya akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#4154f1',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, hapus!',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/dokumen/folder/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Folder berhasil dihapus.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadFolders();
                        },
                        error: function(xhr) {
                            let errorMessage = 'Gagal menghapus folder';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: errorMessage,
                                confirmButtonColor: '#0d6efd'
                            });
                        }
                    });
                }
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: '#0d6efd'
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
    </script>
@endpush
