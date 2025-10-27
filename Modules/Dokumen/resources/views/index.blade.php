@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Manajemen Dokumen</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Dokumen</li>
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
                                        <span class="fw-bold">Daftar Dokumen</span>
                                        <small class="d-block text-muted fw-normal mt-1">Kelola dokumen dan arsip
                                            organisasi</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary btn-lg shadow-sm px-4 py-2"
                                    data-bs-toggle="modal" data-bs-target="#modalUploadDokumen">
                                    <i class="bi bi-cloud-upload me-1"></i> Upload Dokumen
                                </button>
                            </div>
                        </div>

                        <!-- Filter Section -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Pencarian</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchDokumen"
                                        placeholder="Cari dokumen...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Final">Final</option>
                                    <option value="Archived">Archived</option>
                                </select>
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
                            <div class="table-responsive">
                                <table class="table datatable" id="dokumenTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nomor</th>
                                            <th scope="col">Judul Dokumen</th>
                                            <th scope="col">Jenis</th>
                                            <th scope="col">Tanggal</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Versi</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dokumenTableBody">
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="text-muted mt-2">Memuat dokumen...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Grid View -->
                        <div id="gridView" style="display: none;">
                            <div class="row" id="dokumenGridBody">
                                <div class="col-12 text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2">Memuat dokumen...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Upload -->
    <div class="modal fade" id="modalUploadDokumen" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Dokumen Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formUploadDokumen" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="folder_id" class="form-label">Folder <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="folder_id" name="folder_id" required>
                                    <option value="">Pilih Folder</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="jenis_id" class="form-label">Jenis Dokumen <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="jenis_id" name="jenis_id" required>
                                    <option value="">Pilih Jenis</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Dokumen <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="judul" name="judul" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nomor_surat" class="form-label">Nomor Surat</label>
                                <input type="text" class="form-control" id="nomor_surat" name="nomor_surat">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_dokumen" class="form-label">Tanggal Dokumen <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_dokumen" name="tanggal_dokumen"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Draft">Draft</option>
                                <option value="Final" selected>Final</option>
                                <option value="Archived">Archived</option>
                            </select>
                        </div>

                        <!-- Metadata Section -->
                        <div class="mb-3">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span>Metadata</span>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddMetadata">
                                    <i class="bi bi-plus-circle me-1"></i>Tambah Metadata
                                </button>
                            </label>
                            <div id="metadataContainer">
                                <!-- Metadata fields will be added here dynamically -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Upload File <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="file" name="file" required>
                            <div class="form-text">
                                Format: PDF, DOCX, XLSX (Max: 10MB)
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle"></i>
                                Untuk file Shapefile (.shp), harap di-zip terlebih dahulu bersama file pendukung (.shx,
                                .dbf, .prj)
                            </small>
                        </div>

                        <div id="uploadProgress" style="display: none;">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: 0%"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnUploadDokumen">
                        <i class="bi bi-cloud-upload me-1"></i> Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Dokumen -->
    <div class="modal fade" id="modalEditDokumen" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditDokumen" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_dokumen_id" name="dokumen_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_folder_id" class="form-label">Folder <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_folder_id" name="folder_id" required>
                                    <option value="">Pilih Folder</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_jenis_id" class="form-label">Jenis Dokumen <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_jenis_id" name="jenis_id" required>
                                    <option value="">Pilih Jenis</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_judul" class="form-label">Judul Dokumen <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_judul" name="judul" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_nomor_surat" class="form-label">Nomor Surat</label>
                                <input type="text" class="form-control" id="edit_nomor_surat" name="nomor_surat">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_tanggal_dokumen" class="form-label">Tanggal Dokumen <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_tanggal_dokumen"
                                    name="tanggal_dokumen" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="Draft">Draft</option>
                                <option value="Final">Final</option>
                                <option value="Archived">Archived</option>
                            </select>
                        </div>

                        <!-- Metadata Section for Edit -->
                        <div class="mb-3">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span>Metadata</span>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddEditMetadata">
                                    <i class="bi bi-plus-circle me-1"></i>Tambah Metadata
                                </button>
                            </label>
                            <div id="editMetadataContainer">
                                <!-- Existing metadata will be loaded here -->
                            </div>
                            <input type="hidden" name="metadata_delete" id="metadata_delete" value="">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File Saat Ini</label>
                            <div id="current_file_info" class="alert alert-info">
                                <!-- Will be filled by JavaScript -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_file" class="form-label">Upload File Baru (Opsional)</label>
                            <input type="file" class="form-control" id="edit_file" name="file">
                            <div class="form-text">
                                Format: PDF, DOCX, XLSX (Max: 10MB). Kosongkan jika tidak ingin mengganti file.
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle"></i>
                                Untuk file Shapefile (.shp), harap di-zip terlebih dahulu bersama file pendukung (.shx,
                                .dbf, .prj)
                            </small>
                        </div>

                        <div id="editProgress" style="display: none;">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: 0%"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnUpdateDokumen">
                        <i class="bi bi-save me-1"></i> Update
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetailDokumen" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailDokumenContent">
                    <!-- Dynamic content -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnDownloadDokumen" data-id="">
                        <i class="bi bi-download me-1"></i> Download
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Card Styles */
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
        }

        /* Status Badges */
        .badge-status {
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        /* Table Styles */
        .datatable-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        /* Metadata Styles */
        .metadata-row {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 8px 4px;
            margin-bottom: 8px !important;
        }

        .metadata-table {
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }

        .metadata-table th {
            background-color: #f1f3f5;
        }
    </style>
@endsection

@push('scripts')
    <script>
        let metadataCounter = 0;
        let deletedMetadataIds = [];
        let dataTable = null;

        $(document).ready(function() {
            console.log('Document ready');

            // Initialize metadata handling
            initMetadataHandling();

            // Load initial data
            loadFolders();
            loadDokumen();

            // Trigger saat modal upload dibuka
            $('#modalUploadDokumen').on('shown.bs.modal', function() {
                // Jika ada jenis terpilih, update requirements
                if ($('#jenis_id').val()) {
                    updateFileRequirements($('#jenis_id'), '#file', '#file + .form-text');
                }
            });

            // Reset form saat modal ditutup
            $('#modalUploadDokumen').on('hidden.bs.modal', function() {
                $('#formUploadDokumen')[0].reset();
                $('#metadataContainer').empty();
                metadataCounter = 0;
                $('#file + .form-text').html('Format: PDF, DOCX, XLSX (Max: 10MB)');
            });

            // Reset form edit saat modal ditutup
            $('#modalEditDokumen').on('hidden.bs.modal', function() {
                $('#formEditDokumen')[0].reset();
                $('#editMetadataContainer').empty();
                deletedMetadataIds = [];
                $('#metadata_delete').val('');
                metadataCounter = 0;
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

            // Handler untuk perubahan jenis dokumen di form upload
            $('#jenis_id').change(function() {
                updateFileRequirements($(this), '#file', '#file + .form-text');
            });

            // Handler untuk perubahan jenis dokumen di form edit
            $('#edit_jenis_id').change(function() {
                updateFileRequirements($(this), '#edit_file', '#edit_file + .form-text');
            });

            // Upload Dokumen
            $('#btnUploadDokumen').click(function() {
                let formData = new FormData($('#formUploadDokumen')[0]);

                $.ajax({
                    url: '{{ route('dokumen.store') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#uploadProgress').show();
                        $('#btnUploadDokumen').prop('disabled', true);
                    },
                    xhr: function() {
                        let xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                let percentComplete = (evt.loaded / evt.total) * 100;
                                $('#uploadProgress .progress-bar').css('width',
                                    percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        $('#modalUploadDokumen').modal('hide');
                        $('#formUploadDokumen')[0].reset();
                        $('#metadataContainer').empty();
                        metadataCounter = 0;
                        $('#uploadProgress').hide();
                        $('#uploadProgress .progress-bar').css('width', '0%');
                        $('#btnUploadDokumen').prop('disabled', false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Dokumen berhasil diupload',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Reload data setelah upload berhasil
                        loadDokumen();
                    },
                    error: function(xhr) {
                        $('#uploadProgress').hide();
                        $('#uploadProgress .progress-bar').css('width', '0%');
                        $('#btnUploadDokumen').prop('disabled', false);

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

            // Update Dokumen Handler
            $('#btnUpdateDokumen').click(function() {
                let dokumenId = $('#edit_dokumen_id').val();
                let formData = new FormData($('#formEditDokumen')[0]);

                $.ajax({
                    url: `/dokumen/${dokumenId}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#editProgress').show();
                        $('#btnUpdateDokumen').prop('disabled', true);
                    },
                    xhr: function() {
                        let xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                let percentComplete = (evt.loaded / evt.total) * 100;
                                $('#editProgress .progress-bar').css('width',
                                    percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        $('#modalEditDokumen').modal('hide');
                        $('#formEditDokumen')[0].reset();
                        $('#editProgress').hide();
                        $('#editProgress .progress-bar').css('width', '0%');
                        $('#btnUpdateDokumen').prop('disabled', false);
                        $('#editMetadataContainer').empty();
                        deletedMetadataIds = [];
                        $('#metadata_delete').val('');
                        metadataCounter = 0;

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Dokumen berhasil diupdate',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Reload data setelah update berhasil
                        loadDokumen();
                    },
                    error: function(xhr) {
                        $('#editProgress').hide();
                        $('#editProgress .progress-bar').css('width', '0%');
                        $('#btnUpdateDokumen').prop('disabled', false);

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

            // Download button in detail modal
            $('#btnDownloadDokumen').click(function() {
                let id = $(this).attr('data-id');
                if (id) downloadDokumen(id);
            });

            // Filter Change
            $('#filterKategori, #filterJenis, #filterStatus').change(function() {
                loadDokumen();
            });

            // Search
            let searchTimeout;
            $('#searchDokumen').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    loadDokumen();
                }, 500);
            });
        });

        // ============================================
        // METADATA HANDLING FUNCTIONS
        // ============================================

        function initMetadataHandling() {
            $('#btnAddMetadata').click(function() {
                addMetadataField();
            });

            $('#btnAddEditMetadata').click(function() {
                addEditMetadataField();
            });
        }

        function addMetadataField() {
            metadataCounter++;
            const html = `
                <div class="row mb-2 metadata-row" id="metadata-row-${metadataCounter}">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="metadata[${metadataCounter}][key]" placeholder="Kunci" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="metadata[${metadataCounter}][value]" placeholder="Nilai" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMetadataField('${metadataCounter}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#metadataContainer').append(html);
        }

        function removeMetadataField(counter) {
            $(`#metadata-row-${counter}`).remove();
        }

        function addEditMetadataField(existingMeta = null) {
            metadataCounter++;

            let html = `
                <div class="row mb-2 metadata-row" id="edit-metadata-row-${metadataCounter}">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="metadata[${metadataCounter}][key]"
                            placeholder="Kunci" value="${existingMeta ? existingMeta.key : ''}" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="metadata[${metadataCounter}][value]"
                            placeholder="Nilai" value="${existingMeta ? existingMeta.value : ''}" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-outline-danger"
                            ${existingMeta ? `onclick="deleteMetadata(${existingMeta.id}, ${metadataCounter})"`
                                        : `onclick="removeEditMetadataField(${metadataCounter})"`}>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            if (existingMeta) {
                html = html.replace(`name="metadata[${metadataCounter}][key]"`,
                    `name="metadata[${metadataCounter}][key]" data-id="${existingMeta.id}"`);
                html = html + `<input type="hidden" name="metadata[${metadataCounter}][id]" value="${existingMeta.id}">`;
            }

            $('#editMetadataContainer').append(html);
        }

        function removeEditMetadataField(counter) {
            $(`#edit-metadata-row-${counter}`).remove();
        }

        function deleteMetadata(id, counter) {
            deletedMetadataIds.push(id);
            $('#metadata_delete').val(deletedMetadataIds.join(','));
            $(`#edit-metadata-row-${counter}`).remove();
        }

        // ============================================
        // DATA LOADING FUNCTIONS
        // ============================================

        function loadFolders() {
            console.log('Loading folders...');
            $.ajax({
                url: '{{ route('dokumen.folders') }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Folders loaded:', response);
                    let options = '<option value="">Pilih Folder</option>';
                    if (Array.isArray(response) && response.length > 0) {
                        response.forEach(folder => {
                            options += `<option value="${folder.id}">${folder.nama}</option>`;
                        });
                    }
                    $('#folder_id').html(options);
                    $('#edit_folder_id').html(options);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading folders:', error);
                    console.error('Response:', xhr.responseText);
                }
            });
        }

        function loadDokumen() {
            console.log('Loading dokumen...');
            $.ajax({
                url: '{{ route('dokumen.index') }}',
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: {
                    kategori: $('#filterKategori').val(),
                    jenis: $('#filterJenis').val(),
                    status: $('#filterStatus').val(),
                    search: $('#searchDokumen').val()
                },
                success: function(response) {
                    console.log('Dokumen loaded:', response);
                    renderDokumen(response);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading dokumen:', error);
                    console.error('Response:', xhr.responseText);

                    $('#dokumenTableBody').html(`
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">Gagal memuat data dokumen</p>
                                <button class="btn btn-sm btn-primary" onclick="loadDokumen()">
                                    <i class="bi bi-arrow-clockwise"></i> Coba Lagi
                                </button>
                            </td>
                        </tr>
                    `);

                    $('#dokumenGridBody').html(`
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Gagal memuat data dokumen</p>
                            <button class="btn btn-sm btn-primary" onclick="loadDokumen()">
                                <i class="bi bi-arrow-clockwise"></i> Coba Lagi
                            </button>
                        </div>
                    `);
                }
            });
        }

        // ============================================
        // RENDER FUNCTIONS
        // ============================================

        function renderDokumen(data) {
            // Destroy DataTable terlebih dahulu jika ada
            if (dataTable) {
                dataTable.destroy();
                dataTable = null;
            }

            let listHtml = '';
            let gridHtml = '';

            if (!Array.isArray(data) || data.length === 0) {
                listHtml = `
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">Belum ada dokumen</p>
                        </td>
                    </tr>
                `;
                gridHtml = `
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Belum ada dokumen</p>
                    </div>
                `;
            } else {
                data.forEach((item, index) => {
                    let statusBadge = getStatusBadge(item.status);
                    let fileIcon = getFileIcon(item.files && item.files.length > 0 ? item.files[0].extension :
                        'pdf');
                    let metadataCount = item.metadata ? item.metadata.length : 0;
                    let metadataBadge = metadataCount > 0 ?
                        `<span class="badge bg-secondary" title="Memiliki ${metadataCount} metadata">
                            <i class="bi bi-tag-fill me-1"></i>${metadataCount}
                        </span>` : '';

                    // Table View
                    listHtml += `
                        <tr>
                            <td>${index + 1}</td>
                            <td><small class="text-muted">${item.nomor || '-'}</small></td>
                            <td>
                                <i class="${fileIcon} me-2"></i>
                                <a href="#" onclick="showDetail(${item.id}); return false;">${item.judul}</a>
                                ${item.nomor_surat ? `<br><small class="text-muted">No. Surat: ${item.nomor_surat}</small>` : ''}
                                ${metadataCount > 0 ? `<br>${metadataBadge}` : ''}
                            </td>
                            <td><span class="badge bg-info">${item.jenis?.nama || '-'}</span></td>
                            <td>${formatDate(item.tanggal_dokumen)}</td>
                            <td>${statusBadge}</td>
                            <td><small>v${item.version}</small></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="showDetail(${item.id})" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="downloadDokumen(${item.id})" title="Download">
                                        <i class="bi bi-download"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="editDokumen(${item.id})" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteDokumen(${item.id})" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;

                    // Grid View
                    gridHtml += `
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="card dokumen-card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="text-center mb-3">
                                        <i class="${fileIcon}" style="font-size: 3rem; color: #4154f1;"></i>
                                    </div>
                                    <h6 class="card-title mb-2" style="min-height: 48px;">${truncate(item.judul, 50)}</h6>
                                    <p class="card-text small text-muted">
                                        <i class="bi bi-folder"></i> ${item.jenis?.nama || '-'}<br>
                                        <i class="bi bi-calendar"></i> ${formatDate(item.tanggal_dokumen)}<br>
                                        ${metadataCount > 0 ? `<i class="bi bi-tag"></i> ${metadataCount} metadata<br>` : ''}
                                        <i class="bi bi-eye"></i> ${item.views || 0} views
                                    </p>
                                    <div class="mb-2">${statusBadge}</div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="btn-group btn-group-sm w-100">
                                        <button class="btn btn-outline-primary" onclick="showDetail(${item.id})" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="downloadDokumen(${item.id})" title="Download">
                                            <i class="bi bi-download"></i>
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="editDokumen(${item.id})" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteDokumen(${item.id})" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            // Update DOM
            $('#dokumenTableBody').html(listHtml);
            $('#dokumenGridBody').html(gridHtml);

            // Reinitialize DataTable hanya jika table view visible
            if ($('#tableView').is(':visible')) {
                setTimeout(function() {
                    initializeDataTable();
                }, 100);
            }
        }

        function initializeDataTable() {
            if (dataTable) {
                dataTable.destroy();
                dataTable = null;
            }

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

        // ============================================
        // CRUD FUNCTIONS
        // ============================================

        function editDokumen(id) {
            console.log('Edit dokumen ID:', id);
            $.ajax({
                url: `/dokumen/${id}/edit`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Edit data loaded:', response);

                    // Reset
                    deletedMetadataIds = [];
                    $('#metadata_delete').val('');
                    $('#edit_dokumen_id').val(response.id);

                    // Load dropdowns
                    loadEditDropdowns(response);

                    // Fill form
                    $('#edit_judul').val(response.judul);
                    $('#edit_nomor_surat').val(response.nomor_surat || '');

                    let tanggal = response.tanggal_dokumen ? response.tanggal_dokumen.substring(0, 10) : '';
                    $('#edit_tanggal_dokumen').val(tanggal);

                    $('#edit_deskripsi').val(response.deskripsi || '');
                    $('#edit_status').val(response.status);

                    // Load metadata
                    $('#editMetadataContainer').empty();
                    metadataCounter = 0;

                    if (response.metadata && response.metadata.length > 0) {
                        response.metadata.forEach(meta => {
                            addEditMetadataField(meta);
                        });
                    }

                    // Show current file
                    if (response.files && response.files.length > 0) {
                        let currentFile = response.files.find(f => f.is_current) || response.files[0];
                        $('#current_file_info').html(`
                            <i class="bi bi-file-earmark-${getFileIconClass(currentFile.extension)}"></i>
                            <strong>${currentFile.nama_file}</strong><br>
                            <small>Ukuran: ${formatFileSize(currentFile.size_kb)} | Versi: ${currentFile.version}</small>
                        `);
                    } else {
                        $('#current_file_info').html('<small class="text-muted">Tidak ada file</small>');
                    }

                    $('#modalEditDokumen').modal('show');
                },
                error: function(xhr) {
                    console.error('Error loading edit data:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data dokumen untuk edit'
                    });
                }
            });
        }

        function loadEditDropdowns(dokumenData) {
            // Load folders
            $.ajax({
                url: '{{ route('dokumen.folders') }}',
                type: 'GET',
                dataType: 'json',
                success: function(folders) {
                    let options = '<option value="">Pilih Folder</option>';
                    folders.forEach(folder => {
                        let selected = folder.id == dokumenData.folder_id ? 'selected' : '';
                        options += `<option value="${folder.id}" ${selected}>${folder.nama}</option>`;
                    });
                    $('#edit_folder_id').html(options);
                }
            });
        }

        function showDetail(id) {
            $.ajax({
                url: `/dokumen/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    let html = `
                        <div class="mb-4 p-3 rounded-3 bg-light">
                            <div class="text-center mb-3">
                                <i class="${getFileIcon(response.files && response.files.length > 0 ? response.files[0].extension : 'pdf')}"
                                   style="font-size: 4rem;"></i>
                            </div>
                            <h4 class="text-center mb-3">${response.judul}</h4>
                            <div class="text-center mb-2">
                                ${getStatusBadge(response.status)}
                                <span class="badge bg-secondary">v${response.version}</span>
                            </div>
                        </div>
                        
                        <table class="table table-striped">
                            <tr><th width="30%">Nomor Dokumen</th><td>${response.nomor || '-'}</td></tr>
                            <tr><th>Jenis</th><td>${response.jenis?.nama || '-'}</td></tr>
                            <tr><th>Folder</th><td>${response.folder?.nama || '-'}</td></tr>
                            <tr><th>Nomor Surat</th><td>${response.nomor_surat || '-'}</td></tr>
                            <tr><th>Tanggal</th><td>${formatDate(response.tanggal_dokumen)}</td></tr>
                            <tr><th>Deskripsi</th><td>${response.deskripsi || '-'}</td></tr>
                            <tr><th>Diupload oleh</th><td>${response.uploader?.nama || '-'}</td></tr>
                            <tr><th>Views/Downloads</th><td>${response.views || 0} / ${response.downloads || 0}</td></tr>
                        </table>`;

                    // Metadata section
                    if (response.metadata && response.metadata.length > 0) {
                        html += `
                            <h5 class="mt-4 mb-3">Metadata</h5>
                            <table class="table table-bordered metadata-table">
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
                                    <td><strong>${meta.key}</strong></td>
                                    <td>${meta.value}</td>
                                </tr>`;
                        });

                        html += `</tbody></table>`;
                    }
                    // File information
                    if (response.files && response.files.length > 0) {
                        let currentFile = response.files.find(f => f.is_current) || response.files[0];

                        html += `
                            <h5 class="mt-4 mb-3">Informasi File</h5>
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="bi bi-file-earmark-${getFileIconClass(currentFile.extension)} me-3" style="font-size: 2rem;"></i>
                                <div>
                                    <strong>${currentFile.nama_file}</strong><br>
                                    <small>Ukuran: ${formatFileSize(currentFile.size_kb)} | Versi: ${currentFile.version} | Format: .${currentFile.extension.toUpperCase()}</small>
                                </div>
                            </div>`;

                        // Multiple versions
                        if (response.files.length > 1) {
                            html += `
                                <div class="accordion" id="fileVersions">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseVersions" aria-expanded="false" aria-controls="collapseVersions">
                                                Versi Sebelumnya (${response.files.length - 1})
                                            </button>
                                        </h2>
                                        <div id="collapseVersions" class="accordion-collapse collapse" data-bs-parent="#fileVersions">
                                            <div class="accordion-body p-0">
                                                <table class="table table-sm mb-0">
                                                    <thead>
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
                                        <td>v${file.version}</td>
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
                                    </div>
                                </div>`;
                        }
                    }

                    $('#detailDokumenContent').html(html);
                    $('#btnDownloadDokumen').attr('data-id', id);
                    $('#modalDetailDokumen').modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat detail dokumen'
                    });
                }
            });
        }

        function downloadDokumen(id) {
            window.location.href = `/dokumen/${id}/download`;
        }

        function deleteDokumen(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Dokumen akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#4154f1',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, hapus!',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Batal'
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
                            });
                            loadDokumen();
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

        function updateFileRequirements(selectElement, fileInputId, textElementSelector) {
            let selectedOption = selectElement.find('option:selected');
            let allowedExt = selectedOption.data('allowed-ext') || 'pdf,doc,docx,xls,xlsx';
            let maxSize = selectedOption.data('max-size') || 10;

            // Update file input accept attribute
            let acceptTypes = allowedExt.split(',').map(ext => '.' + ext.trim()).join(',');
            $(fileInputId).attr('accept', acceptTypes);

            // Update text info
            let extList = allowedExt.toUpperCase().replace(/,/g, ', ');
            $(textElementSelector).html(`Format: ${extList} (Max: ${maxSize}MB)`);

            console.log('File requirements updated:', {
                allowed: allowedExt,
                maxSize: maxSize,
                accept: acceptTypes
            });
        }

        function getStatusBadge(status) {
            const badges = {
                'Draft': 'bg-secondary',
                'Final': 'bg-success',
                'Archived': 'bg-warning'
            };
            return `<span class="badge badge-status ${badges[status] || 'bg-secondary'}">${status}</span>`;
        }

        function getFileIcon(fileType) {
            const icons = {
                'pdf': 'bi bi-file-earmark-pdf-fill text-danger',
                'doc': 'bi bi-file-earmark-word-fill text-primary',
                'docx': 'bi bi-file-earmark-word-fill text-primary',
                'xls': 'bi bi-file-earmark-excel-fill text-success',
                'xlsx': 'bi bi-file-earmark-excel-fill text-success',
                'shp': 'bi bi-map-fill text-success',
                'zip': 'bi bi-file-earmark-zip-fill text-info',
                'rar': 'bi bi-file-earmark-zip-fill text-info',
                '7z': 'bi bi-file-earmark-zip-fill text-info',
            };
            return icons[fileType] || 'bi bi-file-earmark-text';
        }

        function getFileIconClass(extension) {
            const icons = {
                'pdf': 'pdf-fill text-danger',
                'doc': 'word-fill text-primary',
                'docx': 'word-fill text-primary',
                'xls': 'excel-fill text-success',
                'xlsx': 'excel-fill text-success',
                'shp': 'map-fill text-success',
                'zip': 'file-earmark-zip-fill text-info',
                'rar': 'file-earmark-zip-fill text-info',
                '7z': 'file-earmark-zip-fill text-info',
            };
            return icons[extension] || 'text';
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            let date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function formatDateTime(dateString) {
            if (!dateString) return '-';
            let date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatFileSize(sizeKb) {
            if (sizeKb < 1024) {
                return sizeKb + ' KB';
            } else {
                return (sizeKb / 1024).toFixed(2) + ' MB';
            }
        }

        function truncate(str, length) {
            if (!str) return '';
            return str.length > length ? str.substring(0, length) + '...' : str;
        }
    </script>
@endpush
