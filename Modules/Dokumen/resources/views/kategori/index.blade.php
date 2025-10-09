@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Manajemen Kategori Dokumen</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dokumen.index') }}">Dokumen</a></li>
                <li class="breadcrumb-item active">Kategori</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Daftar Kategori</h5>
                            <div>
                                <button type="button" class="btn btn-primary" onclick="showCreateModal()">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Kategori
                                </button>
                            </div>
                        </div>

                        <!-- Kategori Cards -->
                        <div class="row" id="kategoriContainer">
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2">Memuat kategori...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="modalKategori" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formKategori">
                        @csrf
                        <input type="hidden" id="kategori_id" name="kategori_id">
                        <input type="hidden" id="_method" name="_method" value="POST">

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Kategori <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required
                                placeholder="Contoh: Umum, Bahan Tayang">
                        </div>

                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon (Bootstrap Icons)</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi" id="iconPreview"></i>
                                </span>
                                <input type="text" class="form-control" id="icon" name="icon"
                                    placeholder="Contoh: folder, file-earmark, archive">
                            </div>
                            <small class="text-muted">
                                Lihat icon di: <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="warna" class="form-label">Warna</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="warna" name="warna"
                                    value="#4154f1" title="Pilih warna">
                                <input type="text" class="form-control" id="warnaText" value="#4154f1" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="urutan" class="form-label">Urutan</label>
                            <input type="number" class="form-control" id="urutan" name="urutan" min="1"
                                placeholder="Urutan tampilan">
                            <small class="text-muted">Semakin kecil angka, semakin atas posisinya</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preview</label>
                            <div class="card" id="previewCard" style="border-color: #4154f1;">
                                <div class="card-body text-center">
                                    <i class="bi bi-folder" id="previewIcon" style="font-size: 3rem; color: #4154f1;"></i>
                                    <h6 class="mt-2" id="previewNama">Nama Kategori</h6>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveKategori">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetailKategori" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailKategoriContent">
                    <!-- Dynamic content -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            loadKategori();

            // Live preview saat input berubah
            $('#nama').on('input', function() {
                $('#previewNama').text($(this).val() || 'Nama Kategori');
            });

            $('#icon').on('input', function() {
                let iconClass = $(this).val() ? 'bi-' + $(this).val() : 'bi-folder';
                $('#iconPreview').attr('class', 'bi ' + iconClass);
                $('#previewIcon').attr('class', 'bi ' + iconClass);
            });

            $('#warna').on('input', function() {
                let color = $(this).val();
                $('#warnaText').val(color);
                $('#previewCard').css('border-color', color);
                $('#previewIcon').css('color', color);
            });

            // Save kategori
            $('#btnSaveKategori').click(function() {
                saveKategori();
            });
        });

        // Load Kategori
        function loadKategori() {
            $.ajax({
                url: '{{ route('dokumen.kategori.index') }}',
                type: 'GET',
                success: function(response) {
                    renderKategori(response);
                },
                error: function(xhr) {
                    $('#kategoriContainer').html(`
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Gagal memuat kategori</p>
                            <button class="btn btn-sm btn-primary" onclick="loadKategori()">
                                <i class="bi bi-arrow-clockwise"></i> Coba Lagi
                            </button>
                        </div>
                    `);
                }
            });
        }

        // Render Kategori
        function renderKategori(data) {
            let html = '';

            if (!Array.isArray(data) || data.length === 0) {
                html = `
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Belum ada kategori</p>
                    </div>
                `;
            } else {
                data.forEach((item) => {
                    let iconClass = item.icon ? 'bi-' + item.icon : 'bi-folder';
                    let color = item.warna || '#4154f1';

                    html += `
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100" style="border-color: ${color}; border-width: 2px;">
                                <div class="card-body text-center">
                                    <i class="bi ${iconClass}" style="font-size: 3rem; color: ${color};"></i>
                                    <h6 class="mt-3 mb-2">${item.nama}</h6>
                                    <span class="badge bg-secondary">Urutan: ${item.urutan || '-'}</span>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="btn-group btn-group-sm w-100">
                                        <button class="btn btn-outline-primary" onclick="showDetail(${item.id})" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="showEditModal(${item.id})" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteKategori(${item.id})" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            $('#kategoriContainer').html(html);
        }

        // Show Create Modal
        function showCreateModal() {
            $('#modalTitle').text('Tambah Kategori');
            $('#formKategori')[0].reset();
            $('#kategori_id').val('');
            $('#_method').val('POST');

            // Reset preview
            $('#previewNama').text('Nama Kategori');
            $('#previewIcon').attr('class', 'bi bi-folder').css('color', '#4154f1');
            $('#previewCard').css('border-color', '#4154f1');
            $('#iconPreview').attr('class', 'bi');
            $('#warna').val('#4154f1');
            $('#warnaText').val('#4154f1');

            $('#modalKategori').modal('show');
        }

        // Show Edit Modal
        function showEditModal(id) {
            $.ajax({
                url: `dokumen/kategori/${id}`,
                type: 'GET',
                success: function(response) {
                    $('#modalTitle').text('Edit Kategori');
                    $('#kategori_id').val(response.id);
                    $('#_method').val('PUT');
                    $('#nama').val(response.nama);
                    $('#icon').val(response.icon || '');
                    $('#warna').val(response.warna || '#4154f1');
                    $('#warnaText').val(response.warna || '#4154f1');
                    $('#urutan').val(response.urutan || '');

                    // Update preview
                    let iconClass = response.icon ? 'bi-' + response.icon : 'bi-folder';
                    let color = response.warna || '#4154f1';

                    $('#previewNama').text(response.nama);
                    $('#previewIcon').attr('class', 'bi ' + iconClass).css('color', color);
                    $('#previewCard').css('border-color', color);
                    $('#iconPreview').attr('class', 'bi ' + iconClass);

                    $('#modalKategori').modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data kategori'
                    });
                }
            });
        }

        // Save Kategori
        function saveKategori() {
            let id = $('#kategori_id').val();
            let method = $('#_method').val();
            let url = id ? `/kategori/${id}` : '{{ route('dokumen.kategori.store') }}';

            let formData = {
                _token: '{{ csrf_token() }}',
                nama: $('#nama').val(),
                icon: $('#icon').val(),
                warna: $('#warna').val(),
                urutan: $('#urutan').val()
            };

            $.ajax({
                url: url,
                type: method === 'PUT' ? 'PUT' : 'POST',
                data: formData,
                success: function(response) {
                    $('#modalKategori').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: method === 'PUT' ? 'Kategori berhasil diupdate' :
                            'Kategori berhasil ditambahkan',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadKategori();
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        errorMessage = '<ul style="text-align: left;">';
                        Object.keys(errors).forEach(key => {
                            errors[key].forEach(error => {
                                errorMessage += `<li>${error}</li>`;
                            });
                        });
                        errorMessage += '</ul>';
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        html: errorMessage
                    });
                }
            });
        }

        // Show Detail
        function showDetail(id) {
            $.ajax({
                url: `/kategori/${id}`,
                type: 'GET',
                success: function(response) {
                    let iconClass = response.icon ? 'bi-' + response.icon : 'bi-folder';
                    let color = response.warna || '#4154f1';

                    let html = `
                        <div class="text-center mb-4">
                            <i class="bi ${iconClass}" style="font-size: 5rem; color: ${color};"></i>
                            <h4 class="mt-3">${response.nama}</h4>
                        </div>
                        <table class="table">
                            <tr><th width="30%">Icon</th><td>${response.icon || '-'}</td></tr>
                            <tr><th>Warna</th><td>
                                <span class="badge" style="background-color: ${color};">${color}</span>
                            </td></tr>
                            <tr><th>Urutan</th><td>${response.urutan || '-'}</td></tr>
                            <tr><th>Jumlah Jenis</th><td>${response.jenis ? response.jenis.length : 0} jenis dokumen</td></tr>
                            <tr><th>Dibuat</th><td>${formatDateTime(response.created_at)}</td></tr>
                            <tr><th>Diupdate</th><td>${formatDateTime(response.updated_at)}</td></tr>
                        </table>
                    `;

                    $('#detailKategoriContent').html(html);
                    $('#modalDetailKategori').modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat detail kategori'
                    });
                }
            });
        }

        // Delete Kategori
        function deleteKategori(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Kategori akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/kategori/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Kategori berhasil dihapus.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadKategori();
                        },
                        error: function(xhr) {
                            let errorMessage = 'Gagal menghapus kategori';
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

        // Helper function
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
    </script>
@endpush
