@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Manajemen Template Dokumen</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dokumen.index') }}">Dokumen</a></li>
                <li class="breadcrumb-item active">Template</li>
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
                                        <i class="bi bi-file-earmark-text-fill text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Template Dokumen</span>
                                        <small class="d-block text-muted fw-normal mt-1">Kelola template untuk generate
                                            dokumen otomatis</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary btn-lg shadow-sm px-4 py-2"
                                    onclick="showCreateModal()">
                                    <i class="bi bi-plus-circle me-2"></i>Buat Template
                                </button>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="stats-card bg-gradient-primary text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Total Template</h6>
                                            <h3 class="mb-0 fw-bold" id="totalTemplate">0</h3>
                                        </div>
                                        <i class="bi bi-file-earmark-text" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card bg-gradient-success text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Template Aktif</h6>
                                            <h3 class="mb-0 fw-bold" id="templateAktif">0</h3>
                                        </div>
                                        <i class="bi bi-check-circle" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card bg-gradient-warning text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Template PDF</h6>
                                            <h3 class="mb-0 fw-bold" id="templatePdf">0</h3>
                                        </div>
                                        <i class="bi bi-file-pdf" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card bg-gradient-info text-white rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-white">Dokumen Generated</h6>
                                            <h3 class="mb-0 fw-bold" id="totalGenerated">0</h3>
                                        </div>
                                        <i class="bi bi-file-earmark-check" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Jenis Dokumen</label>
                                <select class="form-select" id="filterJenis">
                                    <option value="">Semua Jenis</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Pencarian</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchTemplate"
                                        placeholder="Cari template...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi bi-search"></i>
                                    </button>
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
                        <div id="gridView" class="row">
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status"
                                    style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-3">Memuat template...</p>
                            </div>
                        </div>

                        <!-- Table View -->
                        <div id="tableView" style="display: none;">
                            <table class="table datatable" id="templateTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Template</th>
                                        <th>Kode</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Format</th>
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

    <!-- Modal Create/Edit Template -->
    <div class="modal fade" id="modalTemplate" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="bi bi-file-earmark-plus me-2"></i>Buat Template Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formTemplate">
                        @csrf
                        <input type="hidden" id="template_id" name="template_id">
                        <input type="hidden" id="_method" name="_method" value="POST">

                        <div class="row">
                            <!-- Left Column - Template Info -->
                            <div class="col-md-4">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3">
                                            <i class="bi bi-info-circle me-2"></i>Informasi Template
                                        </h6>

                                        <div class="mb-3">
                                            <label for="jenis_id" class="form-label">Jenis Dokumen <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" id="jenis_id" name="jenis_id" required>
                                                <option value="">Pilih Jenis</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="nama" class="form-label">Nama Template <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nama" name="nama"
                                                required placeholder="Contoh: Surat Perjanjian Kerja">
                                        </div>

                                        <div class="mb-3">
                                            <label for="kode" class="form-label">Kode <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="kode" name="kode"
                                                required placeholder="SPK-001">
                                        </div>

                                        <div class="mb-3">
                                            <label for="deskripsi" class="form-label">Deskripsi</label>
                                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi template..."></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="format_output" class="form-label">Format Output <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" id="format_output" name="format_output" required>
                                                <option value="html">HTML</option>
                                                <option value="pdf">PDF</option>
                                                <option value="docx">DOCX</option>
                                            </select>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="is_active"
                                                name="is_active" checked>
                                            <label class="form-check-label" for="is_active">Template Aktif</label>
                                        </div>

                                        <hr>

                                        <h6 class="fw-bold mb-3">
                                            <i class="bi bi-code-square me-2"></i>Variables
                                        </h6>
                                        <div id="variablesList" class="mb-3"
                                            style="max-height: 300px; overflow-y: auto;">
                                            <small class="text-muted">Loading variables...</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Editor -->
                            <div class="col-md-8">
                                <ul class="nav nav-tabs mb-3" id="editorTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="content-tab" data-bs-toggle="tab"
                                            data-bs-target="#content" type="button">
                                            <i class="bi bi-file-text me-2"></i>Content
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="header-tab" data-bs-toggle="tab"
                                            data-bs-target="#header" type="button">
                                            <i class="bi bi-layout-text-window me-2"></i>Header
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="footer-tab" data-bs-toggle="tab"
                                            data-bs-target="#footer" type="button">
                                            <i class="bi bi-layout-text-window-reverse me-2"></i>Footer
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="settings-tab" data-bs-toggle="tab"
                                            data-bs-target="#settings" type="button">
                                            <i class="bi bi-gear me-2"></i>Settings
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="editorTabContent">
                                    <!-- Content Tab -->
                                    <div class="tab-pane fade show active" id="content" role="tabpanel">
                                        <div class="mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="insertVariable('content')">
                                                <i class="bi bi-braces me-1"></i>Insert Variable
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="formatText('bold', 'content')">
                                                <i class="bi bi-type-bold"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="formatText('italic', 'content')">
                                                <i class="bi bi-type-italic"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="formatText('underline', 'content')">
                                                <i class="bi bi-type-underline"></i>
                                            </button>
                                        </div>
                                        <textarea class="form-control font-monospace" id="template_content" name="content" rows="20"></textarea>
                                        <small class="text-muted mt-2 d-block">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Klik "Insert Variable" untuk menambahkan variabel dinamis atau klik langsung
                                            pada variable di sidebar kiri
                                        </small>
                                    </div>

                                    <!-- Header Tab -->
                                    <div class="tab-pane fade" id="header" role="tabpanel">
                                        <div class="mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="insertVariable('header')">
                                                <i class="bi bi-braces me-1"></i>Insert Variable
                                            </button>
                                        </div>
                                        <textarea class="form-control font-monospace" id="template_header" name="header" rows="8"></textarea>
                                    </div>

                                    <!-- Footer Tab -->
                                    <div class="tab-pane fade" id="footer" role="tabpanel">
                                        <div class="mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="insertVariable('footer')">
                                                <i class="bi bi-braces me-1"></i>Insert Variable
                                            </button>
                                        </div>
                                        <textarea class="form-control font-monospace" id="template_footer" name="footer" rows="8"></textarea>
                                    </div>

                                    <!-- Settings Tab -->
                                    <div class="tab-pane fade" id="settings" role="tabpanel">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body">
                                                <h6 class="fw-bold mb-3">Page Settings</h6>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Orientation</label>
                                                        <select class="form-select" id="setting_orientation">
                                                            <option value="portrait">Portrait</option>
                                                            <option value="landscape">Landscape</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Paper Size</label>
                                                        <select class="form-select" id="setting_paper">
                                                            <option value="a4">A4</option>
                                                            <option value="letter">Letter</option>
                                                            <option value="legal">Legal</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <h6 class="fw-bold mb-3 mt-3">Margins (mm)</h6>
                                                <div class="row">
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Top</label>
                                                        <input type="number" class="form-control" id="margin_top"
                                                            value="20">
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Right</label>
                                                        <input type="number" class="form-control" id="margin_right"
                                                            value="20">
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Bottom</label>
                                                        <input type="number" class="form-control" id="margin_bottom"
                                                            value="20">
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Left</label>
                                                        <input type="number" class="form-control" id="margin_left"
                                                            value="20">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="previewTemplate()">
                        <i class="bi bi-eye me-1"></i>Preview
                    </button>
                    <button type="button" class="btn btn-primary" id="btnSaveTemplate">
                        <i class="bi bi-check-circle me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Preview -->
    <div class="modal fade" id="modalPreview" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-eye me-2"></i>Preview Template
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="previewContent" style="min-height: 500px;">
                    <!-- Preview will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Generate -->
    <div class="modal fade" id="modalGenerate" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-lightning-charge me-2"></i>Generate Dokumen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formGenerate">
                        <input type="hidden" id="generate_template_id">

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Data user dan system akan otomatis terisi. Isi custom data jika diperlukan.
                        </div>

                        <div id="customDataContainer">
                            <!-- Custom fields will be added here -->
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCustomField()">
                            <i class="bi bi-plus-circle me-1"></i>Tambah Custom Data
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="generateDocument()">
                        <i class="bi bi-lightning-charge me-1"></i>Generate
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

        /* Template Card */
        .template-card {
            position: relative;
            overflow: hidden;
            border-radius: 16px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid #f0f0f0 !important;
        }

        .template-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .template-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
            border-color: #667eea !important;
        }

        .template-icon-wrapper {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border-radius: 20px;
            background: linear-gradient(135deg, #667eea15, #764ba215);
            transition: all 0.3s ease;
        }

        .template-card:hover .template-icon-wrapper {
            transform: scale(1.1);
        }

        .template-icon {
            font-size: 2.5rem;
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

        /* Variables List */
        .variable-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .variable-item:hover {
            background: #e9ecef;
            border-color: #667eea;
            transform: translateX(5px);
        }

        .variable-code {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #667eea;
            font-weight: 600;
        }

        .variable-desc {
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* Editor Styles */
        .font-monospace {
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9rem;
        }

        #template_content,
        #template_header,
        #template_footer {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            transition: border-color 0.3s ease;
        }

        #template_content:focus,
        #template_header:focus,
        #template_footer:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1);
        }

        /* Tab Styles */
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 12px 24px;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            color: #667eea;
            background: #f8f9fa;
        }

        .nav-tabs .nav-link.active {
            color: #667eea;
            background: white;
            border-bottom: 3px solid #667eea;
        }

        /* Modal Styles */
        .modal-xl {
            max-width: 1400px;
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
            border-bottom: 2px solid #dee2e6;
        }

        .datatable-table td {
            vertical-align: middle;
        }

        /* Custom Data Field */
        .custom-data-row {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            border: 1px solid #e9ecef;
        }

        /* Preview Content */
        #previewContent {
            background: white;
            padding: 40px;
            border: 1px solid #dee2e6;
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

        /* Scrollbar */
        textarea::-webkit-scrollbar {
            width: 8px;
        }

        textarea::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        textarea::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }

        textarea::-webkit-scrollbar-thumb:hover {
            background: #764ba2;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .template-card .card-body {
                padding: 1.5rem 1rem;
            }

            .template-icon-wrapper {
                width: 60px;
                height: 60px;
            }

            .template-icon {
                font-size: 2rem;
            }

            .modal-xl {
                max-width: 100%;
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        // Initialize TinyMCE for template editing
        function initTinyMCE() {
            tinymce.init({
                selector: '#template_content, #template_header, #template_footer',
                height: 500,
                menubar: true,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount', 'pagebreak'
                ],
                toolbar: 'undo redo | formatselect | ' +
                    'bold italic forecolor backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | table image | insertVariable',
                // Image upload settings
                images_upload_url: '/dokumen/template/upload-image',
                images_upload_base_path: '/storage/template-images',
                images_upload_handler: function(blobInfo, success, failure) {
                    var xhr, formData;
                    xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', '/dokumen/template/upload-image');
                    xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));

                    xhr.onload = function() {
                        var json;
                        if (xhr.status != 200) {
                            failure('HTTP Error: ' + xhr.status);
                            return;
                        }
                        json = JSON.parse(xhr.responseText);
                        if (!json || typeof json.location != 'string') {
                            failure('Invalid JSON: ' + xhr.responseText);
                            return;
                        }
                        success(json.location);
                    };

                    formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());

                    xhr.send(formData);
                },
                setup: function(editor) {
                    // Add custom insert variable button
                    editor.ui.registry.addButton('insertVariable', {
                        text: 'Insert Variable',
                        icon: 'code-sample',
                        tooltip: 'Insert Variable',
                        onAction: function() {
                            insertVariableToTinyMCE(editor);
                        }
                    });

                    // Format variables in the editor
                    editor.on('BeforeSetContent', function(e) {
                        if (e.content) {
                            // Using a function to process variables without PHP parsing issues
                            e.content = e.content.replace(/\{\{([^}]+)\}\}/g, function(match) {
                                return '<span class="template-variable">' + match + '</span>';
                            });
                        }
                    });

                    // Make sure variables are saved properly
                    editor.on('GetContent', function(e) {
                        if (e.content) {
                            // Remove span tags but keep the variable content
                            e.content = e.content.replace(
                                /<span class="template-variable">\{\{(.*?)\}\}<\/span>/g,
                                function(match, p1) {
                                    return '{{ ' + p1.trim() + ' }}';
                                });
                        }
                    });
                },
                content_style: `
            body { font-family:Arial,sans-serif; font-size:14px }
            .template-variable {
                background-color: #f0f8ff;
                border: 1px solid #cce5ff;
                border-radius: 3px;
                padding: 2px 5px;
                font-family: monospace;
                color: #0066cc;
                cursor: default;
                display: inline-block;
            }
        `,
                relative_urls: false,
                remove_script_host: false,
                document_base_url: window.location.origin + '/',
                // Enable autosave to prevent lost content
                autosave_ask_before_unload: true,
                autosave_interval: "30s",
            });
        }
        // Function to insert variable into TinyMCE
        function insertVariableToTinyMCE(editor) {
            var html = generateVariableSelector();

            Swal.fire({
                title: 'Insert Variable',
                html: html,
                width: 600,
                showCancelButton: true,
                confirmButtonText: 'Insert',
                cancelButtonText: 'Batal',
                preConfirm: function() {
                    return document.getElementById('selected-variable').value;
                }
            }).then(function(result) {
                if (result.isConfirmed && result.value) {
                    // Insert the variable with appropriate styling
                    editor.insertContent('<span class="template-variable">' + result.value + '</span>');
                }
            });
        }

        function generateVariableSelector() {
            var html = '<select class="form-select" id="selected-variable">';
            html += '<option value="">Pilih variable...</option>';

            Object.keys(allVariables).forEach(function(category) {
                html += '<optgroup label="' + category.toUpperCase() + '">';
                Object.keys(allVariables[category]).forEach(function(key) {
                    var fullKey = '{{ ' + category + ' . ' + key + ' }}';
                    html += '<option value="' + fullKey + '">' + allVariables[category][key] + '</option>';
                });
                html += '</optgroup>';
            });

            html += '</select>';
            return html;
        }

        // Function to show template library
        function showTemplateLibrary() {
            Swal.fire({
                title: 'Template Library',
                html: `
            <div class="template-library-grid">
                <div class="template-library-item" data-template="letter">
                    <i class="bi bi-file-text"></i>
                    <h5>Surat Umum</h5>
                </div>
                <div class="template-library-item" data-template="contract">
                    <i class="bi bi-file-earmark-text"></i>
                    <h5>Kontrak Kerja</h5>
                </div>
                <div class="template-library-item" data-template="memo">
                    <i class="bi bi-sticky"></i>
                    <h5>Memo Internal</h5>
                </div>
                <div class="template-library-item" data-template="invoice">
                    <i class="bi bi-receipt"></i>
                    <h5>Faktur</h5>
                </div>
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: 'Buat Kosong',
                cancelButtonText: 'Batal',
                width: 600
            }).then(function(result) {
                if (result.isConfirmed) {
                    showCreateModal();
                }
            });

            $(document).on('click', '.template-library-item', function() {
                var templateType = $(this).data('template');
                loadTemplateBoilerplate(templateType);
                Swal.close();
            });
        }

        // Function to load template boilerplate
        function loadTemplateBoilerplate(type) {
            // Show the create modal first
            showCreateModal();

            // Load predefined template based on type
            var content = '';
            @verbatim
            switch (type) {
                case 'letter':
                    content = `
            <p style="text-align: right;">{{ system . date }}</p>
            <p>No: {{ system . nomor_surat }}/{{ system . kode_surat }}/{{ system . tahun }}</p>
            <br>
            <p>Kepada Yth,<br>
            {{ custom . nama_penerima }}<br>
            {{ custom . jabatan_penerima }}<br>
            {{ custom . alamat_penerima }}</p>
            <br>
            <p style="text-align: center;"><strong>{{ custom . judul_surat }}</strong></p>
            <br>
            <p>Dengan hormat,</p>
            <p>{{ custom . isi_surat }}</p>
            <br>
            <p>Demikian surat ini kami sampaikan. Atas perhatian Bapak/Ibu, kami ucapkan terima kasih.</p>
            <br>
            <p style="text-align: right;">Hormat kami,<br><br><br><br>
            <strong>{{ user . name }}</strong><br>
            {{ user . position }}</p>`;
                    break;

                case 'contract':
                    content = `
            <h2 style="text-align: center;">PERJANJIAN KERJA</h2>
            <p style="text-align: center;">Nomor: {{ system . nomor_surat }}/{{ system . kode_surat }}/{{ system . tahun }}</p>
            <br>
            <p>Yang bertanda tangan di bawah ini:</p>
            <ol>
                <li><strong>Nama:</strong> {{ user . name }}<br>
                <strong>Jabatan:</strong> {{ user . position }}<br>
                <strong>Alamat:</strong> {{ user . address }}<br>
                Selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong></li>
                <br>
                <li><strong>Nama:</strong> {{ custom . nama_pihak_kedua }}<br>
                <strong>Jabatan:</strong> {{ custom . jabatan_pihak_kedua }}<br>
                <strong>Alamat:</strong> {{ custom . alamat_pihak_kedua }}<br>
                Selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong></li>
            </ol>
            
            <p>PIHAK PERTAMA dan PIHAK KEDUA secara bersama-sama disebut sebagai <strong>PARA PIHAK</strong>, sepakat untuk mengadakan perjanjian kerja dengan ketentuan sebagai berikut:</p>
            
            <h4>Pasal 1<br>RUANG LINGKUP PEKERJAAN</h4>
            <p>{{ custom . ruang_lingkup }}</p>
            
            <h4>Pasal 2<br>JANGKA WAKTU</h4>
            <p>{{ custom . jangka_waktu }}</p>
            
            <p>Demikian perjanjian ini dibuat dengan sebenar-benarnya pada tanggal {{ system . date }}.</p>
            
            <table style="width: 100%">
                <tr>
                    <td style="width: 50%; text-align: center">PIHAK PERTAMA</td>
                    <td style="width: 50%; text-align: center">PIHAK KEDUA</td>
                </tr>
                <tr>
                    <td style="height: 80px"></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align: center"><strong>{{ user . name }}</strong></td>
                    <td style="text-align: center"><strong>{{ custom . nama_pihak_kedua }}</strong></td>
                </tr>
            </table>`;
                    break;

                case 'memo':
                    content = `
            <h2 style="text-align: center;">MEMO INTERNAL</h2>
            <hr>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 20%"><strong>Tanggal</strong></td>
                    <td>: {{ system . date }}</td>
                </tr>
                <tr>
                    <td><strong>Nomor</strong></td>
                    <td>: {{ system . nomor_surat }}/MEMO/{{ system . tahun }}</td>
                </tr>
                <tr>
                    <td><strong>Kepada</strong></td>
                    <td>: {{ custom . penerima_memo }}</td>
                </tr>
                <tr>
                    <td><strong>Dari</strong></td>
                    <td>: {{ user . name }} - {{ user . position }}</td>
                </tr>
                <tr>
                    <td><strong>Perihal</strong></td>
                    <td>: {{ custom . perihal_memo }}</td>
                </tr>
            </table>
            <hr>
            <div>
                {{ custom . isi_memo }}
            </div>
            <br><br>
            <p style="text-align: right;">
                {{ system . company }},<br>
                {{ system . date }}<br><br><br><br>
                <strong>{{ user . name }}</strong><br>
                {{ user . position }}
            </p>`;
                    break;

                case 'invoice':
                    content = `
            <div style="text-align: center;">
                <h2>INVOICE</h2>
                <p>{{ system . company }}</p>
            </div>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <strong>Tagihan Kepada:</strong><br>
                        {{ custom . nama_client }}<br>
                        {{ custom . alamat_client }}<br>
                        {{ custom . telepon_client }}
                    </td>
                    <td style="width: 50%; vertical-align: top; text-align: right;">
                        <strong>No. Invoice:</strong> {{ system . nomor_surat }}/INV/{{ system . tahun }}<br>
                        <strong>Tanggal:</strong> {{ system . date }}<br>
                        <strong>Jatuh Tempo:</strong> {{ custom . tanggal_jatuh_tempo }}
                    </td>
                </tr>
            </table>
            
            <br>
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">No</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Deskripsi</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Jumlah</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Harga</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">1</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ custom . item1_deskripsi }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ custom . item1_jumlah }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ custom . item1_harga }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ custom . item1_total }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">2</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ custom . item2_deskripsi }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ custom . item2_jumlah }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ custom . item2_harga }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ custom . item2_total }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong>Subtotal</strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ custom . subtotal }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong>PPN (11%)</strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ custom . ppn }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong>Total</strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong>{{ custom . total }}</strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <br>
            <div>
                <strong>Catatan:</strong><br>
                {{ custom . catatan }}
            </div>
            
            <div style="margin-top: 30px;">
                <p><strong>Metode Pembayaran:</strong></p>
                <p>
                    Bank: {{ system . bank_name }}<br>
                    Account: {{ system . bank_account }}<br>
                    Atas Nama: {{ system . bank_account_name }}
                </p>
            </div>`;
                    break;
            }
        @endverbatim
        // Set the content to TinyMCE
        if (content && tinymce.get('template_content')) {
            tinymce.get('template_content').setContent(content);
        }
        }

        // Function to show variable helper
        function showVariableHelper() {
            var html = '<div class="variable-helper-content">';

            Object.keys(allVariables).forEach(function(category) {
                html += '<div class="variable-category">';
                html += '<h5>' + category.toUpperCase() + '</h5>';

                Object.keys(allVariables[category]).forEach(function(key) {
                    var fullKey = '{{ ' + category + ' . ' + key + ' }}';
                    html += '<div class="variable-helper-item">';
                    html += '<code>' + fullKey + '</code>';
                    html += '<span>' + allVariables[category][key] + '</span>';
                    html += '</div>';
                });

                html += '</div>';
            });

            html += '</div>';

            Swal.fire({
                title: 'Available Variables',
                html: html,
                width: 800,
                showCloseButton: true,
                showConfirmButton: false
            });
        }

        // Global variables
        var allTemplates = [];
        var allVariables = {};
        var viewMode = 'grid';
        var dataTable = null;
        var customFieldCounter = 0;
        var editorInstances = {};

        $(document).ready(function() {
            console.log('Template management initialized');

            // Load initial data
            loadTemplates();
            loadJenis();
            loadVariables();

            // View mode toggle
            $('input[name="viewMode"]').change(function() {
                viewMode = $(this).attr('id') === 'viewGrid' ? 'grid' : 'table';
                toggleView();
            });

            // Filter handlers
            $('#filterJenis, #filterStatus').change(function() {
                filterTemplates();
            });

            // Search handler
            var searchTimeout;
            $('#searchTemplate').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    filterTemplates();
                }, 500);
            });

            // Save template button
            $('#btnSaveTemplate').click(function() {
                saveTemplate();
            });

            // Preview template button
            $(document).on('click', '#btnPreviewTemplate, .btn-preview', function() {
                previewTemplate();
            });

            // Print preview button (delegated for dynamic content)
            $(document).on('click', '#btnPrintPreview', function() {
                printPreview();
            });

            // Show template library button
            $(document).on('click', '#btnTemplateLibrary', function() {
                showTemplateLibrary();
            });

            // Show variable helper button
            $(document).on('click', '#btnVariableHelper', function() {
                showVariableHelper();
            });

            // Generate document button
            $('#btnGenerateDocument').click(function() {
                generateDocument();
            });

            // Add custom field button
            $('#btnAddCustomField').click(function() {
                addCustomField();
            });

            // Event delegation for dynamic buttons
            $(document).on('click', '.btn-detail', function() {
                var id = $(this).data('id');
                showDetail(id);
            });

            $(document).on('click', '.btn-edit', function() {
                var id = $(this).data('id');
                showEditModal(id);
            });

            $(document).on('click', '.btn-generate', function() {
                var id = $(this).data('id');
                showGenerateModal(id);
            });

            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                deleteTemplate(id);
            });

            // Create template buttons
            $('#btnCreateTemplate, #btnCreateTemplateEmpty').click(function() {
                showCreateModal();
            });

            // Remove custom field button (delegated)
            $(document).on('click', '.btn-remove-field', function() {
                var fieldId = $(this).data('field-id');
                $('#custom-field-' + fieldId).remove();
            });

            // Handle tab changes to focus the correct editor
            $('#editorTabs button').on('shown.bs.tab', function(e) {
                var targetTab = $(e.target).attr('data-bs-target').replace('#', '');
                if (tinymce.get('template_' + targetTab)) {
                    setTimeout(function() {
                        tinymce.get('template_' + targetTab).focus();
                    }, 100);
                }
            });

            // Modal open - initialize TinyMCE
            $('#modalTemplate').on('shown.bs.modal', function() {
                // Initialize TinyMCE
                initTinyMCE();
            });

            // Modal close - destroy TinyMCE and reset form
            $('#modalTemplate').on('hidden.bs.modal', function() {
                // Destroy TinyMCE instances to prevent memory leaks
                if (tinymce.get('template_content')) {
                    tinymce.get('template_content').remove();
                }
                if (tinymce.get('template_header')) {
                    tinymce.get('template_header').remove();
                }
                if (tinymce.get('template_footer')) {
                    tinymce.get('template_footer').remove();
                }

                $('#formTemplate')[0].reset();
                $('#template_id').val('');
                $('#_method').val('POST');
                customFieldCounter = 0;
            });

            // Add "Template Library" and "Variable Helper" buttons to the create modal header
            var extraButtons = `
        <div class="d-inline-block ms-3">
            <button type="button" class="btn btn-sm btn-outline-light" id="btnTemplateLibrary">
                <i class="bi bi-collection me-1"></i>Template Library
            </button>
            <button type="button" class="btn btn-sm btn-outline-light" id="btnVariableHelper">
                <i class="bi bi-braces me-1"></i>Variables
            </button>
        </div>
    `;
            $('.modal-header .modal-title').after(extraButtons);
        });

        // ==================== LOAD DATA ====================

        function loadTemplates() {
            $.ajax({
                url: '/dokumen/template',
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    console.log('Templates loaded:', response);
                    allTemplates = response;
                    updateStats(response);
                    renderGrid(response);
                },
                error: function(xhr) {
                    console.error('Error loading templates:', xhr);
                    showError('Gagal memuat template');
                }
            });
        }

        function loadJenis() {
            $.ajax({
                url: '/dokumen/jenis',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">Pilih Jenis</option>';
                    var filterOptions = '<option value="">Semua Jenis</option>';

                    response.forEach(function(jenis) {
                        options += '<option value="' + jenis.id + '">' + jenis.nama + '</option>';
                        filterOptions += '<option value="' + jenis.id + '">' + jenis.nama + '</option>';
                    });

                    $('#jenis_id').html(options);
                    $('#filterJenis').html(filterOptions);
                }
            });
        }

        function loadVariables() {
            $.ajax({
                url: '/dokumen/template/variables',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Variables loaded:', response);
                    allVariables = response;
                    renderVariablesList(response);
                },
                error: function(xhr) {
                    console.error('Error loading variables:', xhr);
                }
            });
        }

        // ==================== RENDER FUNCTIONS ====================

        function updateStats(data) {
            $('#totalTemplate').text(data.length);

            var aktif = data.filter(function(t) {
                return t.is_active;
            }).length;
            $('#templateAktif').text(aktif);

            var pdf = data.filter(function(t) {
                return t.format_output === 'pdf';
            }).length;
            $('#templatePdf').text(pdf);

            $('#totalGenerated').text('0');
        }

        function renderGrid(data) {
            var html = '';

            if (!Array.isArray(data) || data.length === 0) {
                html = `
            <div class="col-12 empty-state text-center">
                <i class="bi bi-inbox empty-state-icon"></i>
                <h4 class="text-muted mb-2">Belum ada template</h4>
                <p class="text-muted mb-4">Mulai dengan membuat template dokumen pertama Anda</p>
                <button class="btn btn-primary btn-lg px-5" id="btnCreateTemplateEmpty">
                    <i class="bi bi-plus-circle me-2"></i>Buat Template Pertama
                </button>
            </div>
        `;
            } else {
                data.forEach(function(item) {
                    var formatBadge = getFormatBadge(item.format_output);
                    var statusBadge = item.is_active ?
                        '<span class="badge bg-success">Aktif</span>' :
                        '<span class="badge bg-secondary">Tidak Aktif</span>';

                    html += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card template-card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="template-icon-wrapper">
                                <i class="bi bi-file-earmark-text template-icon"></i>
                            </div>
                            <h5 class="mb-2 fw-bold text-center">${item.nama}</h5>
                            <p class="text-muted text-center small mb-3">${item.kode}</p>
                            <div class="text-center mb-3">
                                ${formatBadge}
                                ${statusBadge}
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="bi bi-folder me-1"></i>${item.jenis?.nama || '-'}</span>
                                <span><i class="bi bi-calendar me-1"></i>${formatDate(item.created_at)}</span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3">
                            <div class="d-grid gap-2">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary btn-detail" data-id="${item.id}" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success btn-generate" data-id="${item.id}" title="Generate">
                                        <i class="bi bi-lightning-charge"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning btn-edit" data-id="${item.id}" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${item.id}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                });
            }

            $('#gridView').html(html);
        }

        function renderTable(data) {
            if (dataTable) {
                dataTable.destroy();
                dataTable = null;
            }

            var tbody = '';
            data.forEach(function(item, index) {
                var formatBadge = getFormatBadge(item.format_output);
                var statusBadge = item.is_active ?
                    '<span class="badge bg-success">Aktif</span>' :
                    '<span class="badge bg-secondary">Tidak Aktif</span>';

                tbody += `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${item.nama}</strong></td>
                <td><code>${item.kode}</code></td>
                <td>${item.jenis?.nama || '-'}</td>
                <td>${formatBadge}</td>
                <td>${statusBadge}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary btn-detail" data-id="${item.id}" title="Detail">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-success btn-generate" data-id="${item.id}" title="Generate">
                            <i class="bi bi-lightning-charge"></i>
                        </button>
                        <button class="btn btn-outline-warning btn-edit" data-id="${item.id}" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-delete" data-id="${item.id}" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
            });

            $('#templateTable tbody').html(tbody);

            setTimeout(function() {
                dataTable = new simpleDatatables.DataTable("#templateTable", {
                    searchable: true,
                    fixedHeight: false,
                    perPage: 10
                });
            }, 100);
        }

        function renderVariablesList(variables) {
            var html = '';

            Object.keys(variables).forEach(function(category) {
                html += '<div class="mb-3">';
                html += '<h6 class="text-uppercase small text-muted mb-2">' + category + '</h6>';

                Object.keys(variables[category]).forEach(function(key) {
                    var fullKey = category + '.' + key;
                    var displayKey = '{{ ' + fullKey + ' }}';
                    var varForClick = '{{ ' + fullKey + ' }}';

                    html += '<div class="variable-item" data-variable="' + varForClick + '">';
                    html += '    <div>';
                    html += '        <div class="variable-code">' + displayKey + '</div>';
                    html += '        <div class="variable-desc">' + variables[category][key] + '</div>';
                    html += '    </div>';
                    html += '    <i class="bi bi-plus-circle text-primary"></i>';
                    html += '</div>';
                });

                html += '</div>';
            });

            $('#variablesList').html(html);

            // Add click handler for variable items
            $(document).on('click', '.variable-item', function() {
                var variable = $(this).data('variable');
                var activeTab = $('.tab-pane.active').attr('id');

                // Find which editor is active based on the visible tab
                var editorId;
                if (activeTab === 'content') editorId = 'template_content';
                else if (activeTab === 'header') editorId = 'template_header';
                else if (activeTab === 'footer') editorId = 'template_footer';

                if (editorId && tinymce.get(editorId)) {
                    tinymce.get(editorId).focus();
                    tinymce.get(editorId).insertContent('<span class="template-variable">' + variable + '</span>');
                }
            });
        }

        // ==================== MODAL FUNCTIONS ====================

        function showCreateModal() {
            $('#modalTitle').html('<i class="bi bi-file-earmark-plus me-2"></i>Buat Template Baru');
            $('#formTemplate')[0].reset();
            $('#template_id').val('');
            $('#_method').val('POST');
            $('#is_active').prop('checked', true);
            $('#modalTemplate').modal('show');
        }

        function showEditModal(id) {
            $.ajax({
                url: '/dokumen/template/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#modalTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Template');
                    $('#template_id').val(response.id);
                    $('#_method').val('PUT');

                    $('#jenis_id').val(response.jenis_id);
                    $('#nama').val(response.nama);
                    $('#kode').val(response.kode);
                    $('#deskripsi').val(response.deskripsi);

                    $('#format_output').val(response.format_output);
                    $('#is_active').prop('checked', response.is_active);

                    // Load settings
                    if (response.settings) {
                        $('#setting_orientation').val(response.settings.orientation || 'portrait');
                        $('#setting_paper').val(response.settings.paper || 'a4');
                        $('#margin_top').val(response.settings.margins?.top || 20);
                        $('#margin_right').val(response.settings.margins?.right || 20);
                        $('#margin_bottom').val(response.settings.margins?.bottom || 20);
                        $('#margin_left').val(response.settings.margins?.left || 20);
                    }

                    $('#modalTemplate').modal('show');

                    // Set TinyMCE content after modal is shown and editors are initialized
                    $('#modalTemplate').on('shown.bs.modal', function() {
                        setTimeout(function() {
                            if (tinymce.get('template_content')) {
                                tinymce.get('template_content').setContent(response.content);
                            }
                            if (tinymce.get('template_header')) {
                                tinymce.get('template_header').setContent(response.header ||
                                    '');
                            }
                            if (tinymce.get('template_footer')) {
                                tinymce.get('template_footer').setContent(response.footer ||
                                    '');
                            }
                        }, 300);
                    });
                },
                error: function() {
                    showError('Gagal memuat data template');
                }
            });
        }

        function showDetail(id) {
            $.ajax({
                url: '/dokumen/template/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(template) {
                    var formatBadge = getFormatBadge(template.format_output);
                    var statusBadge = template.is_active ?
                        '<span class="badge bg-success">Aktif</span>' :
                        '<span class="badge bg-secondary">Tidak Aktif</span>';

                    var settingsInfo = '';
                    if (template.settings) {
                        settingsInfo = `
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-muted">Orientation:</small><br>
                            <strong>${template.settings.orientation || 'portrait'}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Paper Size:</small><br>
                            <strong>${(template.settings.paper || 'A4').toUpperCase()}</strong>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <small class="text-muted">Margins:</small><br>
                            <span class="badge bg-light text-dark">Top: ${template.settings.margins?.top || 20}mm</span>
                            <span class="badge bg-light text-dark">Right: ${template.settings.margins?.right || 20}mm</span>
                            <span class="badge bg-light text-dark">Bottom: ${template.settings.margins?.bottom || 20}mm</span>
                            <span class="badge bg-light text-dark">Left: ${template.settings.margins?.left || 20}mm</span>
                        </div>
                    </div>
                `;
                    }

                    Swal.fire({
                        title: '<i class="bi bi-file-earmark-text me-2"></i>' + template.nama,
                        html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <small class="text-muted">Kode:</small><br>
                            <code class="fs-6">${template.kode}</code>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Jenis Dokumen:</small><br>
                            <strong>${template.jenis?.nama || '-'}</strong>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Deskripsi:</small><br>
                            ${template.deskripsi || '<em class="text-muted">Tidak ada deskripsi</em>'}
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Format & Status:</small><br>
                            ${formatBadge} ${statusBadge}
                        </div>
                        
                        ${settingsInfo}
                        
                        <div class="row mt-3">
                            <div class="col-6">
                                <small class="text-muted">Dibuat:</small><br>
                                <small>${formatDate(template.created_at)}</small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Diupdate:</small><br>
                                <small>${formatDate(template.updated_at)}</small>
                            </div>
                        </div>
                        
                        <hr class="my-3">
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="Swal.close(); showPreviewFromDetail(${id})">
                                <i class="bi bi-eye me-2"></i>Lihat Preview
                            </button>
                            <button class="btn btn-outline-success" onclick="Swal.close(); showGenerateModal(${id})">
                                <i class="bi bi-lightning-charge me-2"></i>Generate Dokumen
                            </button>
                        </div>
                    </div>
                `,
                        width: 600,
                        showConfirmButton: false,
                        showCloseButton: true
                    });
                },
                error: function() {
                    showError('Gagal memuat detail template');
                }
            });
        }

        function showPreviewFromDetail(id) {
            $.ajax({
                url: '/dokumen/template/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(template) {
                    // Store template data for preview
                    currentPreviewTemplate = template;

                    // Show preview directly without opening the editor modal
                    previewTemplateFromData(template);
                },
                error: function() {
                    showError('Gagal memuat template untuk preview');
                }
            });
        }

        function previewTemplateFromData(template) {
            var content = template.content;
            var header = template.header || '';
            var footer = template.footer || '';
            var format = template.format_output;

            var orientation = 'portrait';
            var paper = 'a4';
            var margins = {
                top: 20,
                right: 20,
                bottom: 20,
                left: 20
            };

            if (template.settings) {
                orientation = template.settings.orientation || orientation;
                paper = template.settings.paper || paper;
                margins = template.settings.margins || margins;
            }

            if (!content) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Content Kosong',
                    text: 'Template tidak memiliki content'
                });
                return;
            }

            // Replace variables with sample data
            var sampleData = {
                'user.name': 'John Doe',
                'user.email': 'john@example.com',
                'user.phone': '081234567890',
                'user.address': 'Jl. Contoh No. 123, Jakarta',
                'user.position': 'Manager',
                'system.date': new Date().toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                }),
                'system.time': new Date().toLocaleTimeString('id-ID'),
                'system.app_name': 'Aplikasi Dokumen',
                'system.company': 'PT. Contoh Indonesia',
                'system.nomor_surat': 'DOC/2024/001',
                'system.kode_surat': 'DOC',
                'system.tahun': new Date().getFullYear(),
                'system.bank_name': 'Bank Mandiri',
                'system.bank_account': '1234567890',
                'system.bank_account_name': 'PT. Contoh Indonesia',
                'custom.field1': 'Sample Value 1',
                'custom.field2': 'Sample Value 2',
                'custom.nama_penerima': 'Budi Santoso',
                'custom.jabatan_penerima': 'Direktur',
                'custom.alamat_penerima': 'Jl. Sudirman No. 123, Jakarta',
                'custom.judul_surat': 'SURAT PEMBERITAHUAN',
                'custom.isi_surat': 'Dengan ini kami sampaikan bahwa...',
                'custom.nama_pihak_kedua': 'Ahmad Hidayat',
                'custom.jabatan_pihak_kedua': 'Pekerja',
                'custom.alamat_pihak_kedua': 'Jl. Melati No. 45, Jakarta',
                'custom.ruang_lingkup': 'Pekerjaan yang harus dilakukan meliputi...',
                'custom.jangka_waktu': 'Perjanjian ini berlaku selama 1 (satu) tahun...'
            };

            // Process content
            var processedContent = content;
            var processedHeader = header;
            var processedFooter = footer;

            // Replace all variables
            Object.keys(sampleData).forEach(function(key) {
                var regex = new RegExp('\\{\\{\\s*' + key.replace('.', '\\.') + '\\s*\\}\\}', 'g');
                processedContent = processedContent.replace(regex, sampleData[key]);
                processedHeader = processedHeader.replace(regex, sampleData[key]);
                processedFooter = processedFooter.replace(regex, sampleData[key]);
            });

            // Set page dimensions based on paper size
            var pageWidth = paper === 'letter' ? '215.9mm' : (paper === 'legal' ? '215.9mm' : '210mm');
            var pageHeight = paper === 'letter' ? '279.4mm' : (paper === 'legal' ? '355.6mm' : '297mm');

            if (orientation === 'landscape') {
                var temp = pageWidth;
                pageWidth = pageHeight;
                pageHeight = temp;
            }

            var previewHtml = `
        <div class="preview-wrapper">
            <div class="preview-toolbar mb-3 p-3 bg-light rounded">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <span class="badge bg-primary me-2">${format.toUpperCase()}</span>
                        <span class="badge bg-info me-2">${paper.toUpperCase()}</span>
                        <span class="badge bg-secondary">${orientation}</span>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnPrintPreview">
                            <i class="bi bi-printer me-1"></i>Print Preview
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="preview-page" style="
                width: ${pageWidth};
                min-height: ${pageHeight};
                margin: 0 auto;
                background: white;
                padding: ${margins.top}mm ${margins.right}mm ${margins.bottom}mm ${margins.left}mm;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
                border: 1px solid #ddd;
                box-sizing: border-box;
            ">
                ${processedHeader ? `
                                                                        <div class="preview-header" style="
                                                                            border-bottom: 2px solid #333;
                                                                            padding-bottom: 15px;
                                                                            margin-bottom: 25px;
                                                                        ">${processedHeader}</div>
                                                                    ` : ''}
                
                <div class="preview-content" style="
                    line-height: 1.8;
                    font-size: 12pt;
                    text-align: justify;
                ">${processedContent}</div>
                
                ${processedFooter ? `
                                                                        <div class="preview-footer" style="
                                                                            border-top: 1px solid #333;
                                                                            padding-top: 15px;
                                                                            margin-top: 25px;
                                                                        ">${processedFooter}</div>
                                                                    ` : ''}
            </div>
            
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Note:</strong> Ini adalah preview dengan sample data. 
                Variabel akan diganti dengan data sebenarnya saat generate dokumen.
            </div>
        </div>
    `;

            $('#previewContent').html(previewHtml);
            $('#modalPreview').modal('show');
        }

        function showGenerateModal(id) {
            $('#generate_template_id').val(id);
            $('#customDataContainer').empty();
            customFieldCounter = 0;
            $('#modalGenerate').modal('show');
        }

        // ==================== CRUD FUNCTIONS ====================

        function saveTemplate() {
            var id = $('#template_id').val();
            var method = $('#_method').val();
            var url = id ? '/dokumen/template/' + id : '/dokumen/template';

            // Collect settings
            var settings = {
                orientation: $('#setting_orientation').val(),
                paper: $('#setting_paper').val(),
                margins: {
                    top: parseInt($('#margin_top').val()),
                    right: parseInt($('#margin_right').val()),
                    bottom: parseInt($('#margin_bottom').val()),
                    left: parseInt($('#margin_left').val())
                }
            };

            // Get content from TinyMCE editors
            var content = tinymce.get('template_content') ? tinymce.get('template_content').getContent() : '';
            var header = tinymce.get('template_header') ? tinymce.get('template_header').getContent() : '';
            var footer = tinymce.get('template_footer') ? tinymce.get('template_footer').getContent() : '';

            var formData = {
                _token: $('input[name="_token"]').val(),
                jenis_id: $('#jenis_id').val(),
                nama: $('#nama').val(),
                kode: $('#kode').val(),
                deskripsi: $('#deskripsi').val(),
                content: content,
                header: header,
                footer: footer,
                format_output: $('#format_output').val(),
                is_active: $('#is_active').is(':checked') ? 1 : 0,
                settings: settings
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
                    showLoading();
                },
                success: function(response) {
                    hideLoading();
                    $('#modalTemplate').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Template berhasil disimpan',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    loadTemplates();
                },
                error: function(xhr) {
                    hideLoading();
                    handleError(xhr);
                }
            });
        }

        function deleteTemplate(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Template akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#667eea',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/dokumen/template/' + id,
                        type: 'DELETE',
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Template berhasil dihapus',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadTemplates();
                        },
                        error: function(xhr) {
                            handleError(xhr);
                        }
                    });
                }
            });
        }

        // ==================== TEMPLATE FUNCTIONS ====================

        function previewTemplate() {
            // Get content from TinyMCE editors
            var content = tinymce.get('template_content') ? tinymce.get('template_content').getContent() : '';
            var header = tinymce.get('template_header') ? tinymce.get('template_header').getContent() : '';
            var footer = tinymce.get('template_footer') ? tinymce.get('template_footer').getContent() : '';
            var format = $('#format_output').val();
            var orientation = $('#setting_orientation').val();
            var paper = $('#setting_paper').val();

            if (!content) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Content Kosong',
                    text: 'Isi content template terlebih dahulu'
                });
                return;
            }

            // Replace variables with sample data
            var sampleData = {
                'user.name': 'John Doe',
                'user.email': 'john@example.com',
                'user.phone': '081234567890',
                'user.address': 'Jl. Contoh No. 123, Jakarta',
                'user.position': 'Manager',
                'system.date': new Date().toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                }),
                'system.time': new Date().toLocaleTimeString('id-ID'),
                'system.app_name': 'Aplikasi Dokumen',
                'system.company': 'PT. Contoh Indonesia',
                'system.nomor_surat': 'DOC/2024/001',
                'system.kode_surat': 'DOC',
                'system.tahun': new Date().getFullYear(),
                'system.bank_name': 'Bank Mandiri',
                'system.bank_account': '1234567890',
                'system.bank_account_name': 'PT. Contoh Indonesia',
                'custom.field1': 'Sample Value 1',
                'custom.field2': 'Sample Value 2',
                'custom.nama_penerima': 'Budi Santoso',
                'custom.jabatan_penerima': 'Direktur',
                'custom.alamat_penerima': 'Jl. Sudirman No. 123, Jakarta',
                'custom.judul_surat': 'SURAT PEMBERITAHUAN',
                'custom.isi_surat': 'Dengan ini kami sampaikan bahwa...'
            };

            // Process content
            var processedContent = content;
            var processedHeader = header;
            var processedFooter = footer;

            // Replace all variables
            Object.keys(sampleData).forEach(function(key) {
                var regex = new RegExp('\\{\\{\\s*' + key.replace('.', '\\.') + '\\s*\\}\\}', 'g');
                processedContent = processedContent.replace(regex, sampleData[key]);
                processedHeader = processedHeader.replace(regex, sampleData[key]);
                processedFooter = processedFooter.replace(regex, sampleData[key]);
            });

            // Set page dimensions based on paper size
            var pageWidth = paper === 'letter' ? '215.9mm' : (paper === 'legal' ? '215.9mm' : '210mm');
            var pageHeight = paper === 'letter' ? '279.4mm' : (paper === 'legal' ? '355.6mm' : '297mm');

            if (orientation === 'landscape') {
                var temp = pageWidth;
                pageWidth = pageHeight;
                pageHeight = temp;
            }

            var previewHtml = `
        <div class="preview-wrapper">
            <div class="preview-toolbar mb-3 p-3 bg-light rounded">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <span class="badge bg-primary me-2">${format.toUpperCase()}</span>
                        <span class="badge bg-info me-2">${paper.toUpperCase()}</span>
                        <span class="badge bg-secondary">${orientation}</span>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnPrintPreview">
                            <i class="bi bi-printer me-1"></i>Print Preview
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="preview-page" style="
                width: ${pageWidth};
                min-height: ${pageHeight};
                margin: 0 auto;
                background: white;
                padding: ${$('#margin_top').val()}mm ${$('#margin_right').val()}mm ${$('#margin_bottom').val()}mm ${$('#margin_left').val()}mm;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
                border: 1px solid #ddd;
                box-sizing: border-box;
            ">
                ${processedHeader ? `
                                                                        <div class="preview-header" style="
                                                                            border-bottom: 2px solid #333;
                                                                            padding-bottom: 15px;
                                                                            margin-bottom: 25px;
                                                                        ">${processedHeader}</div>
                                                                    ` : ''}
                
                <div class="preview-content" style="
                    line-height: 1.8;
                    font-size: 12pt;
                    text-align: justify;
                ">${processedContent}</div>
                
                ${processedFooter ? `
                                                                        <div class="preview-footer" style="
                                                                            border-top: 1px solid #333;
                                                                            padding-top: 15px;
                                                                            margin-top: 25px;
                                                                        ">${processedFooter}</div>
                                                                    ` : ''}
            </div>
            
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Note:</strong> Ini adalah preview dengan sample data. 
                Variabel akan diganti dengan data sebenarnya saat generate dokumen.
            </div>
        </div>
    `;

            $('#previewContent').html(previewHtml);
            $('#modalPreview').modal('show');
        }

        function printPreview() {
            var previewContent = $('.preview-page').html();
            var printWindow = window.open('', '_blank');
            printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print Preview</title>
            <style>
                @page { 
                    size: ${$('#setting_paper').val()} ${$('#setting_orientation').val()};
                    margin: ${$('#margin_top').val()}mm ${$('#margin_right').val()}mm ${$('#margin_bottom').val()}mm ${$('#margin_left').val()}mm;
                }
                body { 
                    font-family: Arial, sans-serif;
                    line-height: 1.8;
                }
                @media print {
                    body { margin: 0; }
                }
            </style>
        </head>
        <body>
            ${previewContent}
        </body>
        </html>
    `);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function() {
                printWindow.print();
            }, 250);
        }

        // ==================== GENERATE FUNCTIONS ====================

        function addCustomField() {
            customFieldCounter++;
            var html = `
        <div class="custom-data-row" id="custom-field-${customFieldCounter}">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <input type="text" class="form-control form-control-sm" 
                           name="custom_key_${customFieldCounter}" placeholder="Key (e.g., custom.field1)">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control form-control-sm" 
                           name="custom_value_${customFieldCounter}" placeholder="Value">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-field" 
                            data-field-id="${customFieldCounter}">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
            $('#customDataContainer').append(html);
        }

        function generateDocument() {
            var templateId = $('#generate_template_id').val();

            var customData = {};
            for (var i = 1; i <= customFieldCounter; i++) {
                var key = $('input[name="custom_key_' + i + '"]').val();
                var value = $('input[name="custom_value_' + i + '"]').val();
                if (key && value) {
                    customData[key] = value;
                }
            }

            $.ajax({
                url: '/dokumen/template/' + templateId + '/generate',
                type: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(),
                    data: customData
                },
                dataType: 'json',
                beforeSend: function() {
                    showLoading('Generating document...');
                },
                success: function(response) {
                    hideLoading();
                    $('#modalGenerate').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Dokumen berhasil di-generate',
                        showCancelButton: true,
                        confirmButtonText: 'Lihat Hasil',
                        cancelButtonText: 'Tutup'
                    }).then(function(result) {
                        if (result.isConfirmed && response.data && response.data.file_path) {
                            window.open('/storage/' + response.data.file_path, '_blank');
                        }
                    });
                },
                error: function(xhr) {
                    hideLoading();
                    handleError(xhr);
                }
            });
        }

        // ==================== UTILITY FUNCTIONS ====================

        function toggleView() {
            if (viewMode === 'grid') {
                $('#gridView').show();
                $('#tableView').hide();
            } else {
                $('#gridView').hide();
                $('#tableView').show();
                renderTable(allTemplates);
            }
        }

        function filterTemplates() {
            var filtered = allTemplates;

            var jenisId = $('#filterJenis').val();
            if (jenisId) {
                filtered = filtered.filter(function(t) {
                    return t.jenis_id == jenisId;
                });
            }

            var status = $('#filterStatus').val();
            if (status !== '') {
                filtered = filtered.filter(function(t) {
                    return t.is_active == status;
                });
            }

            var search = $('#searchTemplate').val().toLowerCase();
            if (search) {
                filtered = filtered.filter(function(t) {
                    return t.nama.toLowerCase().includes(search) ||
                        t.kode.toLowerCase().includes(search) ||
                        (t.deskripsi && t.deskripsi.toLowerCase().includes(search));
                });
            }

            if (viewMode === 'grid') {
                renderGrid(filtered);
            } else {
                renderTable(filtered);
            }
        }

        function getFormatBadge(format) {
            var badges = {
                'pdf': '<span class="badge badge-format format-pdf">PDF</span>',
                'html': '<span class="badge badge-format format-html">HTML</span>',
                'docx': '<span class="badge badge-format format-docx">DOCX</span>'
            };
            return badges[format] || '<span class="badge bg-secondary">Unknown</span>';
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            var date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
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

        function handleError(xhr) {
            var errorMessage = 'Terjadi kesalahan';

            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                var errors = xhr.responseJSON.errors;
                errorMessage = '<ul class="text-start">';
                Object.keys(errors).forEach(function(key) {
                    errors[key].forEach(function(error) {
                        errorMessage += '<li>' + error + '</li>';
                    });
                });
                errorMessage += '</ul>';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: errorMessage
            });

        }
    </script>
@endpush
