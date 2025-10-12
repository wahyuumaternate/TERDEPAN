@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Riwayat Dokumen Saya</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dokumen.template.index') }}">Dokumen</a></li>
                <li class="breadcrumb-item active">Riwayat</li>
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
                                        <i class="bi bi-clock-history text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Dokumen Saya</span>
                                        <small class="d-block text-muted fw-normal mt-1">Riwayat dokumen yang telah
                                            di-generate</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <a href="{{ route('dokumen.template.index') }}"
                                    class="btn btn-primary btn-lg shadow-sm px-4 py-2">
                                    <i class="bi bi-plus-circle me-2"></i>Generate Dokumen Baru
                                </a>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="stats-card bg-gradient-primary text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Total Dokumen</h6>
                                            <h3 class="mb-0 fw-bold" id="totalDokumen">0</h3>
                                        </div>
                                        <i class="bi bi-file-earmark-text" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card bg-gradient-success text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Bulan Ini</h6>
                                            <h3 class="mb-0 fw-bold" id="dokumenBulanIni">0</h3>
                                        </div>
                                        <i class="bi bi-calendar-check" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card bg-gradient-warning text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Format PDF</h6>
                                            <h3 class="mb-0 fw-bold" id="dokumenPdf">0</h3>
                                        </div>
                                        <i class="bi bi-file-pdf" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card bg-gradient-info text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Minggu Ini</h6>
                                            <h3 class="mb-0 fw-bold" id="dokumenMingguIni">0</h3>
                                        </div>
                                        <i class="bi bi-calendar-week" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Jenis Dokumen</label>
                                <select class="form-select" id="filterJenis">
                                    <option value="">Semua Jenis</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Format</label>
                                <select class="form-select" id="filterFormat">
                                    <option value="">Semua Format</option>
                                    <option value="pdf">PDF</option>
                                    <option value="html">HTML</option>
                                    <option value="docx">DOCX</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Periode</label>
                                <select class="form-select" id="filterPeriode">
                                    <option value="">Semua Periode</option>
                                    <option value="today">Hari Ini</option>
                                    <option value="week">Minggu Ini</option>
                                    <option value="month">Bulan Ini</option>
                                    <option value="year">Tahun Ini</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pencarian</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchDokumen"
                                        placeholder="Cari dokumen...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Document List -->
                        <div id="documentList" class="row">
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-3">Memuat dokumen...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal View Document -->
    <div class="modal fade" id="modalViewDocument" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-eye me-2"></i><span id="docTitle">Preview Dokumen</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="min-height: 500px;">
                    <div id="documentPreview" style="background: #f5f5f5; padding: 20px; border-radius: 8px;">
                        <!-- Document preview will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnDownloadDoc">
                        <i class="bi bi-download me-1"></i>Download
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Stats Cards Gradients */
        .stats-card {
            border-radius: 16px !important;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        /* Document Card */
        .document-card {
            position: relative;
            overflow: hidden;
            border-radius: 16px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid #f0f0f0 !important;
        }

        .document-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .document-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
            border-color: #667eea !important;
        }

        .document-icon-wrapper {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border-radius: 16px;
            background: linear-gradient(135deg, #667eea15, #764ba215);
        }

        .document-icon {
            font-size: 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Badge Styles */
        .badge-format {
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .badge-format.format-pdf {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
        }

        .badge-format.format-html {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .badge-format.format-docx {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
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

        /* Document Preview */
        .document-preview-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-height: 70vh;
            overflow-y: auto;
        }

        .document-preview-content iframe {
            width: 100%;
            min-height: 600px;
            border: none;
            border-radius: 8px;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-content {
            background: white;
            padding: 30px 40px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
    </style>
@endsection

@push('scripts')
    <script>
        var allDocuments = [];
        var currentDocumentUrl = '';

        $(document).ready(function() {
            console.log('Document history initialized');

            loadDocuments();
            loadJenis();

            // Filter handlers
            $('#filterJenis, #filterFormat, #filterPeriode').change(function() {
                filterDocuments();
            });

            // Search handler
            var searchTimeout;
            $('#searchDokumen').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    filterDocuments();
                }, 500);
            });

            // Download button
            $('#btnDownloadDoc').click(function() {
                if (currentDocumentUrl) {
                    window.open(currentDocumentUrl, '_blank');
                }
            });

            // Event delegation for document actions
            $(document).on('click', '.btn-view-doc', function() {
                var id = $(this).data('id');
                viewDocument(id);
            });

            $(document).on('click', '.btn-download-doc', function() {
                var url = $(this).data('url');
                window.open(url, '_blank');
            });

            $(document).on('click', '.btn-delete-doc', function() {
                var id = $(this).data('id');
                deleteDocument(id);
            });

            $(document).on('click', '.btn-regenerate-doc', function() {
                var templateId = $(this).data('template-id');
                regenerateDocument(templateId);
            });
        });

        // ==================== LOAD DATA ====================

        function loadDocuments() {
            $.ajax({
                url: '{{ route('dokumen.history.data') }}',
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    console.log('Documents loaded:', response);
                    allDocuments = response;
                    updateStats(response);
                    renderDocuments(response);
                },
                error: function(xhr) {
                    console.error('Error loading documents:', xhr);
                    showError('Gagal memuat dokumen');
                }
            });
        }

        function loadJenis() {
            $.ajax({
                url: '{{ route('dokumen.jenis') }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">Semua Jenis</option>';
                    response.forEach(function(jenis) {
                        options += '<option value="' + jenis.id + '">' + jenis.nama + '</option>';
                    });
                    $('#filterJenis').html(options);
                }
            });
        }

        // ==================== RENDER FUNCTIONS ====================

        function updateStats(data) {
            $('#totalDokumen').text(data.length);

            var now = new Date();
            var startOfWeek = new Date(now.setDate(now.getDate() - now.getDay()));
            var startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);

            var thisWeek = data.filter(function(d) {
                return new Date(d.created_at) >= startOfWeek;
            }).length;

            var thisMonth = data.filter(function(d) {
                return new Date(d.created_at) >= startOfMonth;
            }).length;

            var pdfCount = data.filter(function(d) {
                return d.format === 'pdf';
            }).length;

            $('#dokumenMingguIni').text(thisWeek);
            $('#dokumenBulanIni').text(thisMonth);
            $('#dokumenPdf').text(pdfCount);
        }

        function renderDocuments(data) {
            var html = '';

            if (!Array.isArray(data) || data.length === 0) {
                html = `
                    <div class="col-12 empty-state text-center">
                        <i class="bi bi-inbox empty-state-icon"></i>
                        <h4 class="text-muted mb-2">Belum ada dokumen</h4>
                        <p class="text-muted mb-4">Dokumen yang Anda generate akan muncul di sini</p>
                        <a href="{{ route('dokumen.template.index') }}" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-plus-circle me-2"></i>Generate Dokumen
                        </a>
                    </div>
                `;
            } else {
                data.forEach(function(doc) {
                    var formatBadge = getFormatBadge(doc.format);
                    var fileIcon = getFileIcon(doc.format);

                    html += `
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card document-card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="document-icon-wrapper">
                                        <i class="bi ${fileIcon} document-icon"></i>
                                    </div>
                                    <h6 class="mb-2 fw-bold text-center">${doc.template?.nama || 'Dokumen'}</h6>
                                    <p class="text-muted text-center small mb-3">${doc.template?.kode || '-'}</p>
                                    <div class="text-center mb-3">
                                        ${formatBadge}
                                        ${doc.jenis ? '<span class="badge bg-info">' + doc.jenis.nama + '</span>' : ''}
                                    </div>
                                    <hr>
                                    <div class="mb-2">
                                        <small class="text-muted"><i class="bi bi-calendar me-1"></i>Dibuat:</small><br>
                                        <strong class="small">${formatDateTime(doc.generated_at || doc.created_at)}</strong>
                                    </div>
                                    ${doc.file_name ? `
                                        <div class="mb-2">
                                            <small class="text-muted"><i class="bi bi-file-earmark me-1"></i>File:</small><br>
                                            <small class="text-truncate d-block" title="${doc.file_name}">${doc.file_name}</small>
                                        </div>
                                        ` : ''}
                                </div>
                                <div class="card-footer bg-transparent border-0 pb-3">
                                    <div class="d-grid gap-2">
                                        ${doc.file_path ? `
                                            <button class="btn btn-primary btn-sm btn-view-doc" data-id="${doc.id}">
                                                <i class="bi bi-eye me-1"></i>Lihat Dokumen
                                            </button>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-success btn-download-doc" data-url="/storage/${doc.file_path}" title="Download">
                                                    <i class="bi bi-download"></i>
                                                </button>
                                                <button class="btn btn-outline-primary btn-regenerate-doc" data-template-id="${doc.template_id}" title="Generate Ulang">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-delete-doc" data-id="${doc.id}" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                            ` : `
                                            <div class="alert alert-warning mb-0 py-2">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <small>File tidak tersedia</small>
                                            </div>
                                            `}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            $('#documentList').html(html);
        }

        // ==================== DOCUMENT ACTIONS ====================

        function viewDocument(id) {
            $.ajax({
                url: '/dokumen/generated/' + id,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    showLoading('Memuat dokumen...');
                },
                success: function(doc) {
                    hideLoading();

                    $('#docTitle').text(doc.template?.nama || 'Dokumen');
                    currentDocumentUrl = '/storage/' + doc.file_path;

                    var fileExt = doc.format || 'unknown';
                    var previewHtml = '';

                    if (fileExt === 'pdf') {
                        previewHtml = `
                            <div class="document-preview-content">
                                <iframe src="/storage/${doc.file_path}#toolbar=0" style="width: 100%; min-height: 600px; border: none;"></iframe>
                            </div>
                        `;
                    } else if (fileExt === 'html') {
                        previewHtml = `
                            <div class="document-preview-content">
                                <iframe src="/storage/${doc.file_path}" style="width: 100%; min-height: 600px; border: none;"></iframe>
                            </div>
                        `;
                    } else {
                        previewHtml = `
                            <div class="text-center py-5">
                                <i class="bi bi-file-earmark-text" style="font-size: 5rem; color: #667eea;"></i>
                                <h5 class="mt-3">Preview tidak tersedia untuk format ini</h5>
                                <p class="text-muted">Silakan download dokumen untuk melihat isinya</p>
                                <button class="btn btn-primary" onclick="window.open('/storage/${doc.file_path}', '_blank')">
                                    <i class="bi bi-download me-2"></i>Download Dokumen
                                </button>
                            </div>
                        `;
                    }

                    $('#documentPreview').html(previewHtml);
                    $('#modalViewDocument').modal('show');
                },
                error: function(xhr) {
                    hideLoading();
                    showError('Gagal memuat dokumen');
                }
            });
        }

        function deleteDocument(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Dokumen akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#667eea',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/dokumen/generated/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Dokumen berhasil dihapus',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadDocuments();
                        },
                        error: function(xhr) {
                            showError('Gagal menghapus dokumen');
                        }
                    });
                }
            });
        }

        function regenerateDocument(templateId) {
            Swal.fire({
                title: 'Generate Ulang Dokumen?',
                text: "Dokumen baru akan dibuat dari template ini",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, generate!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('dokumen.template.index') }}?template=' + templateId;
                }
            });
        }

        // ==================== FILTER FUNCTIONS ====================

        function filterDocuments() {
            var filtered = allDocuments;

            var jenisId = $('#filterJenis').val();
            if (jenisId) {
                filtered = filtered.filter(function(d) {
                    return d.jenis && d.jenis.id == jenisId;
                });
            }

            var format = $('#filterFormat').val();
            if (format) {
                filtered = filtered.filter(function(d) {
                    return d.format === format;
                });
            }

            var periode = $('#filterPeriode').val();
            if (periode) {
                var now = new Date();
                var filterDate;

                switch (periode) {
                    case 'today':
                        filterDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                        break;
                    case 'week':
                        filterDate = new Date(now.setDate(now.getDate() - now.getDay()));
                        break;
                    case 'month':
                        filterDate = new Date(now.getFullYear(), now.getMonth(), 1);
                        break;
                    case 'year':
                        filterDate = new Date(now.getFullYear(), 0, 1);
                        break;
                }

                if (filterDate) {
                    filtered = filtered.filter(function(d) {
                        return new Date(d.created_at) >= filterDate;
                    });
                }
            }

            var search = $('#searchDokumen').val().toLowerCase();
            if (search) {
                filtered = filtered.filter(function(d) {
                    return (d.template?.nama && d.template.nama.toLowerCase().includes(search)) ||
                        (d.template?.kode && d.template.kode.toLowerCase().includes(search)) ||
                        (d.file_name && d.file_name.toLowerCase().includes(search));
                });
            }

            renderDocuments(filtered);
        }

        // ==================== UTILITY FUNCTIONS ====================

        function getFormatBadge(format) {
            var ext = format || 'unknown';
            var badges = {
                'pdf': '<span class="badge badge-format format-pdf">PDF</span>',
                'html': '<span class="badge badge-format format-html">HTML</span>',
                'docx': '<span class="badge badge-format format-docx">DOCX</span>'
            };
            return badges[ext] || '<span class="badge bg-secondary">Unknown</span>';
        }

        function getFileIcon(format) {
            var icons = {
                'pdf': 'bi-file-pdf-fill',
                'html': 'bi-file-code-fill',
                'docx': 'bi-file-word-fill',
                'doc': 'bi-file-word-fill'
            };
            return icons[format] || 'bi-file-earmark-text-fill';
        }

        function formatDateTime(dateString) {
            if (!dateString) return '-';
            var date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function showLoading(message) {
            message = message || 'Loading...';
            var html = `
                <div class="loading-overlay">
                    <div class="loading-content">
                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                        <p class="mb-0 fw-semibold">${message}</p>
                    </div>
                </div>
            `;
            $('body').append(html);
        }

        function hideLoading() {
            $('.loading-overlay').remove();
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        }
    </script>
@endpush
