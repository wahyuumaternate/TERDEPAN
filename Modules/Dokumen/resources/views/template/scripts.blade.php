<script>
    let allTemplates = [];
    let allVariables = {};
    let viewMode = 'grid';
    let dataTable = null;
    let customFieldCounter = 0;

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
        let searchTimeout;
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

        // Modal close - reset form
        $('#modalTemplate').on('hidden.bs.modal', function() {
            $('#formTemplate')[0].reset();
            $('#template_id').val('');
            $('#_method').val('POST');
            customFieldCounter = 0;
        });
    });

    // ==================== LOAD DATA ====================

    function loadTemplates() {
        $.ajax({
            url: '{{ route('dokumen.template.index') }}',
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
            url: '{{ route('dokumen.jenis') }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                let options = '<option value="">Pilih Jenis</option>';
                let filterOptions = '<option value="">Semua Jenis</option>';
                
                response.forEach(jenis => {
                    options += `<option value="${jenis.id}">${jenis.nama}</option>`;
                    filterOptions += `<option value="${jenis.id}">${jenis.nama}</option>`;
                });
                
                $('#jenis_id').html(options);
                $('#filterJenis').html(filterOptions);
            }
        });
    }

    function loadVariables() {
        $.ajax({
            url: '{{ route('dokumen.template.variables') }}',
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
        
        let aktif = data.filter(t => t.is_active).length;
        $('#templateAktif').text(aktif);
        
        let pdf = data.filter(t => t.format_output === 'pdf').length;
        $('#templatePdf').text(pdf);
        
        // Total generated would come from backend
        $('#totalGenerated').text('0');
    }

    function renderGrid(data) {
        let html = '';

        if (!Array.isArray(data) || data.length === 0) {
            html = `
                <div class="col-12 empty-state text-center">
                    <i class="bi bi-inbox empty-state-icon"></i>
                    <h4 class="text-muted mb-2">Belum ada template</h4>
                    <p class="text-muted mb-4">Mulai dengan membuat template dokumen pertama Anda</p>
                    <button class="btn btn-primary btn-lg px-5" onclick="showCreateModal()">
                        <i class="bi bi-plus-circle me-2"></i>Buat Template Pertama
                    </button>
                </div>
            `;
        } else {
            data.forEach(item => {
                let formatBadge = getFormatBadge(item.format_output);
                let statusBadge = item.is_active 
                    ? '<span class="badge bg-success">Aktif</span>' 
                    : '<span class="badge bg-secondary">Tidak Aktif</span>';

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
                                        <button class="btn btn-sm btn-outline-primary" onclick="showDetail(${item.id})" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="showGenerateModal(${item.id})" title="Generate">
                                            <i class="bi bi-lightning-charge"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="showEditModal(${item.id})" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate(${item.id})" title="Hapus">
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

        let tbody = '';
        data.forEach((item, index) => {
            let formatBadge = getFormatBadge(item.format_output);
            let statusBadge = item.is_active 
                ? '<span class="badge bg-success">Aktif</span>' 
                : '<span class="badge bg-secondary">Tidak Aktif</span>';

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
                            <button class="btn btn-outline-primary" onclick="showDetail(${item.id})" title="Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-success" onclick="showGenerateModal(${item.id})" title="Generate">
                                <i class="bi bi-lightning-charge"></i>
                            </button>
                            <button class="btn btn-outline-warning" onclick="showEditModal(${item.id})" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteTemplate(${item.id})" title="Hapus">
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
        let html = '';
        
        Object.keys(variables).forEach(category => {
            html += `<div class="mb-3">`;
            html += `<h6 class="text-uppercase small text-muted mb-2">${category}</h6>`;
            
            Object.keys(variables[category]).forEach(key => {
                let fullKey = `${category}.${key}`;
                html += `
                    <div class="variable-item" onclick="insertVariableToEditor('{{${fullKey}}}')">
                        <div>
                            <div class="variable-code">{{${fullKey}}}</div>
                            <div class="variable-desc">${variables[category][key]}</div>
                        </div>
                        <i class="bi bi-plus-circle text-primary"></i>
                    </div>
                `;
            });
            
            html += `</div>`;
        });
        
        $('#variablesList').html(html);
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
            url: `/dokumen/template/${id}`,
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
                $('#template_content').val(response.content);
                $('#template_header').val(response.header || '');
                $('#template_footer').val(response.footer || '');
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
            },
            error: function() {
                showError('Gagal memuat data template');
            }
        });
    }

    function showDetail(id) {
        // Implementation similar to kategori detail
        Swal.fire({
            title: 'Detail Template',
            text: 'Feature detail akan ditampilkan di sini',
            icon: 'info'
        });
    }

    function showGenerateModal(id) {
        $('#generate_template_id').val(id);
        $('#customDataContainer').empty();
        customFieldCounter = 0;
        $('#modalGenerate').modal('show');
    }

    // ==================== CRUD FUNCTIONS ====================

    function saveTemplate() {
        let id = $('#template_id').val();
        let method = $('#_method').val();
        let url = id ? `/dokumen/template/${id}` : '/dokumen/template';

        // Collect settings
        let settings = {
            orientation: $('#setting_orientation').val(),
            paper: $('#setting_paper').val(),
            margins: {
                top: parseInt($('#margin_top').val()),
                right: parseInt($('#margin_right').val()),
                bottom: parseInt($('#margin_bottom').val()),
                left: parseInt($('#margin_left').val())
            }
        };

        let formData = {
            _token: '{{ csrf_token() }}',
            jenis_id: $('#jenis_id').val(),
            nama: $('#nama').val(),
            kode: $('#kode').val(),
            deskripsi: $('#deskripsi').val(),
            content: $('#template_content').val(),
            header: $('#template_header').val(),
            footer: $('#template_footer').val(),
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
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/dokumen/template/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
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

    function insertVariable(target) {
        // Show variable selector
        Swal.fire({
            title: 'Pilih Variable',
            html: generateVariableSelector(),
            width: 600,
            showCancelButton: true,
            confirmButtonText: 'Insert',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                return document.getElementById('selected-variable').value;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                insertVariableToEditor(result.value, target);
            }
        });
    }

    function generateVariableSelector() {
        let html = '<select class="form-select" id="selected-variable">';
        html += '<option value="">Pilih variable...</option>';
        
        Object.keys(allVariables).forEach(category => {
            html += `<optgroup label="${category.toUpperCase()}">`;
            Object.keys(allVariables[category]).forEach(key => {
                let fullKey = `{{${category}.${key}}}`;
                html += `<option value="${fullKey}">${allVariables[category][key]}</option>`;
            });
            html += '</optgroup>';
        });
        
        html += '</select>';
        return html;
    }

    function insertVariableToEditor(variable, target = 'content') {
        let textarea = $(`#template_${target}`)[0];
        if (!textarea) return;
        
        let startPos = textarea.selectionStart;
        let endPos = textarea.selectionEnd;
        let text = textarea.value;
        
        textarea.value = text.substring(0, startPos) + variable + text.substring(endPos);
        textarea.selectionStart = textarea.selectionEnd = startPos + variable.length;
        textarea.focus();
    }

    function formatText(command, target) {
        // Basic text formatting
        let textarea = $(`#template_${target}`)[0];
        if (!textarea) return;
        
        let startPos = textarea.selectionStart;
        let endPos = textarea.selectionEnd;
        let selectedText = textarea.value.substring(startPos, endPos);
        
        if (!selectedText) {
            alert('Pilih text terlebih dahulu');
            return;
        }
        
        let formattedText = '';
        switch(command) {
            case 'bold':
                formattedText = `<strong>${selectedText}</strong>`;
                break;
            case 'italic':
                formattedText = `<em>${selectedText}</em>`;
                break;
            case 'underline':
                formattedText = `<u>${selectedText}</u>`;
                break;
        }
        
        let text = textarea.value;
        textarea.value = text.substring(0, startPos) + formattedText + text.substring(endPos);
    }

    function previewTemplate() {
        let content = $('#template_content').val();
        let header = $('#template_header').val();
        let footer = $('#template_footer').val();
        
        if (!content) {
            Swal.fire({
                icon: 'warning',
                title: 'Content Kosong',
                text: 'Isi content template terlebih dahulu'
            });
            return;
        }
        
        // Build preview HTML
        let previewHtml = `
            <div style="padding: 40px; background: white; border: 1px solid #ddd;">
                ${header ? `<div style="border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px;">${header}</div>` : ''}
                <div style="line-height: 1.8;">${content}</div>
                ${footer ? `<div style="border-top: 1px solid #000; padding-top: 20px; margin-top: 30px;">${footer}</div>` : ''}
            </div>
        `;
        
        $('#previewContent').html(previewHtml);
        $('#modalPreview').modal('show');
    }

    // ==================== GENERATE FUNCTIONS ====================

    function addCustomField() {
        customFieldCounter++;
        let html = `
            <div class="custom-data-row" id="custom-field-${customFieldCounter}">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <input type="text" class="form-control form-control-sm" 
                               name="custom_key_${customFieldCounter}" placeholder="Key (e.g., custom_field_1)">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm" 
                               name="custom_value_${customFieldCounter}" placeholder="Value">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="removeCustomField(${customFieldCounter})">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#customDataContainer').append(html);
    }

    function removeCustomField(id) {
        $(`#custom-field-${id}`).remove();
    }

    function generateDocument() {
        let templateId = $('#generate_template_id').val();
        
        // Collect custom data
        let customData = {};
        for (let i = 1; i <= customFieldCounter; i++) {
            let key = $(`input[name="custom_key_${i}"]`).val();
            let value = $(`input[name="custom_value_${i}"]`).val();
            if (key && value) {
                customData[key] = value;
            }
        }
        
        $.ajax({
            url: `/dokumen/template/${templateId}/generate`,
            type: 'POST',
            data: {
                __token: '{{ csrf_token() }}',
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
                }).then((result) => {
                    if (result.isConfirmed && response.data.file_path) {
                        // Download atau preview generated document
                        window.open(`/storage/${response.data.file_path}`, '_blank');
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
        let filtered = allTemplates;
        
        // Filter by jenis
        let jenisId = $('#filterJenis').val();
        if (jenisId) {
            filtered = filtered.filter(t => t.jenis_id == jenisId);
        }
        
        // Filter by status
        let status = $('#filterStatus').val();
        if (status !== '') {
            filtered = filtered.filter(t => t.is_active == status);
        }
        
        // Filter by search
        let search = $('#searchTemplate').val().toLowerCase();
        if (search) {
            filtered = filtered.filter(t => 
                t.nama.toLowerCase().includes(search) ||
                t.kode.toLowerCase().includes(search) ||
                (t.deskripsi && t.deskripsi.toLowerCase().includes(search))
            );
        }
        
        if (viewMode === 'grid') {
            renderGrid(filtered);
        } else {
            renderTable(filtered);
        }
    }

    function getFormatBadge(format) {
        const badges = {
            'pdf': '<span class="badge badge-format format-pdf">PDF</span>',
            'html': '<span class="badge badge-format format-html">HTML</span>',
            'docx': '<span class="badge badge-format format-docx">DOCX</span>'
        };
        return badges[format] || '<span class="badge bg-secondary">Unknown</span>';
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
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function showLoading(message = 'Loading...') {
        let html = `
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
            html: errorMessage
        });
    }
</script>