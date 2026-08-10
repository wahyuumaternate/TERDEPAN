@extends('layouts.main')

@section('main')
    <section class="section">

        <!-- Statistik Penyimpanan -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card stats-card bg-gradient-primary text-white border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-folder2 stats-card-icon me-3"></i>
                        <div>
                            <h3 class="mb-0 fw-bold" id="totalFolder">0</h3>
                            <small>Total Folder</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stats-card bg-gradient-info text-white border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-diagram-3 stats-card-icon me-3"></i>
                        <div>
                            <h3 class="mb-0 fw-bold" id="totalRootFolder">0</h3>
                            <small>Folder Utama</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stats-card bg-gradient-success text-white border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-magic stats-card-icon me-3"></i>
                        <div>
                            <h3 class="mb-0 fw-bold" id="totalAutoFolder">0</h3>
                            <small>Folder Otomatis</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stats-card bg-gradient-warning text-white border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-file-earmark stats-card-icon me-3"></i>
                        <div>
                            <h3 class="mb-0 fw-bold" id="totalFiles">0</h3>
                            <small>Total File</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0" style="position: relative; min-height: 600px;">
                    <div class="card-body p-4">
                        <!-- Grid View -->
                        <div id="gridView">
                            <div class="row" id="folderGrid"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        /* Page Transition */
        body {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        body.loaded {
            opacity: 1;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            backdrop-filter: blur(5px);
            border-radius: 8px;
        }

        .loading-overlay .spinner-container {
            text-align: center;
        }

        .loading-overlay .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3rem;
        }

        .loading-overlay .loading-text {
            margin-top: 1rem;
            color: #0d6efd;
            font-weight: 500;
        }

        /* Skeleton Loading */
        .skeleton-card {
            height: 100px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        /* Smooth Hover Effects */
        .gdrive-folder-card {
            transform: translateY(0);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .gdrive-folder-card:hover {
            transform: translateY(-2px);
        }

        .gdrive-folder-card:active {
            transform: translateY(0);
        }

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

        .stats-card-icon {
            font-size: 2rem;
            opacity: 0.85;
        }

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
            font-size: 28px;
            transition: transform 0.2s ease;
        }

        .gdrive-folder-card:hover .gdrive-folder-icon i {
            transform: scale(1.05);
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
@endpush

@push('scripts')
    <script>
        let allFolders = [];
        const folderDetailRoute = '{{ route('terminaldata.folder.detail', ':id') }}';

        $(document).ready(function() {
            // Fade in page on load
            setTimeout(() => {
                document.body.classList.add('loaded');
            }, 100);

            // Remove any existing loading overlay from navigation
            $('.loading-overlay').remove();

            loadFolders();

            // Handle browser back button
            window.addEventListener('pageshow', function(event) {
                // Remove loading overlay when coming back via browser back button
                $('.loading-overlay').remove();
            });
        });

        function loadFolders() {
            // Show skeleton loading
            $('#folderGrid').html(`
                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3"><div class="skeleton-card"></div></div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3"><div class="skeleton-card"></div></div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3"><div class="skeleton-card"></div></div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3"><div class="skeleton-card"></div></div>
                </div>
            `);

            $.ajax({
                url: '{{ route('terminaldata.foldersData.index') }}',
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    console.log('Folders loaded:', response); // Debug

                    // Handle both response formats
                    if (response.success && response.data) {
                        allFolders = Array.isArray(response.data) ? response.data : [];
                    } else {
                        allFolders = Array.isArray(response) ? response : [];
                    }

                    updateStats(allFolders);
                    updateParentOptions(allFolders);

                    renderGrid(allFolders);

                    // Remove any loading overlay after data loaded
                    $('.loading-overlay').remove();
                },
                error: function(xhr, status, error) {
                    console.error('Error loading folders:', error);
                    showError('Gagal memuat data folder: ' + error);
                    $('.loading-overlay').remove();
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
            // Function removed - no CRUD operations on this page
        }

        function navigateToFolderFiles(folderId) {
            // Remove any existing loading overlay first
            $('.loading-overlay').remove();

            // Show loading overlay in content area only
            const card = document.querySelector('.card.shadow-sm.border-0');

            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="spinner-container">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="loading-text">Memuat folder...</div>
                </div>
            `;
            card.appendChild(overlay);

            // Navigate
            setTimeout(() => {
                const baseUrl = '{{ route('terminaldata.folder.detail', ':id') }}';
                window.location.href = baseUrl.replace(':id', folderId);
            }, 100);
        }

        // Grid View - Update to make clickable
        function renderGrid(folders) {
            if (!Array.isArray(folders) || folders.length === 0) {
                $('#folderGrid').html(`
                    <div class="col-12 empty-state text-center">
                        <i class="bi bi-folder-x empty-state-icon"></i>
                        <h4 class="text-muted mb-2">Belum ada folder</h4>
                        <p class="text-muted mb-4">Folder level 1 akan tampil di sini</p>
                    </div>
                `);
                return;
            }

            // Filter hanya folder level 1 (root folders)
            const level1Folders = folders.filter(f => f.level === 0 || !f.parent_id);

            if (level1Folders.length === 0) {
                $('#folderGrid').html(`
                    <div class="col-12 empty-state text-center">
                        <i class="bi bi-folder-x empty-state-icon"></i>
                        <h4 class="text-muted mb-2">Belum ada folder level 1</h4>
                        <p class="text-muted mb-4">Folder utama akan tampil di sini</p>
                    </div>
                `);
                return;
            }

            let html = '<div class="row g-3">';
            level1Folders.forEach(item => {
                const bidangName = item.bidang ? item.bidang.nama : 'Tidak ada bidang';
                const bidangColor = item.bidang?.warna || '#6366f1'; // Default indigo color
                const subfolderCount = folders.filter(f => f.parent_id === item.id).length;

                html += `
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="gdrive-folder-card" 
                             onclick="navigateToFolderFiles('${item.id}')"
                             onmouseenter="prefetchFolder('${item.id}')"
                             data-folder-id="${item.id}">
                            <div class="gdrive-folder-content">
                                <div class="gdrive-folder-icon">
                                    <i class="bi bi-folder-fill" style="color: ${bidangColor};"></i>
                                </div>
                                <div class="gdrive-folder-info">
                                    <div class="gdrive-folder-title" title="${item.name}">
                                        ${item.name}
                                    </div>
                                    <div class="gdrive-folder-meta">
                                        ${item.bidang ? `<small class="badge" style="background-color: ${bidangColor}; color: white; font-size: 10px; padding: 2px 6px;">${item.bidang.nama}</small>` : ''}
                                        <small class="text-muted">${item.total_files || 0} file${(item.total_files || 0) !== 1 ? 's' : ''}</small>
                                        ${subfolderCount > 0 ? ` <span class="text-muted">• ${subfolderCount} folder${subfolderCount !== 1 ? 's' : ''}</span>` : ''}
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

        // Prefetch next page for faster navigation
        function prefetchFolder(folderId) {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = folderDetailRoute.replace(':id', folderId);

            // Only add if not already prefetched
            if (!document.querySelector(`link[href="${link.href}"]`)) {
                document.head.appendChild(link);
            }
        }

        function updateParentOptions(folders) {
            // Function removed - no CRUD operations on this page
        }
    </script>
@endpush
