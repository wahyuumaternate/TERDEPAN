@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Buat Template Baru</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.template.index') }}">Template PK</a></li>
                <li class="breadcrumb-item active">Buat Baru</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <form action="{{ route('perjanjian-kinerja.template.store') }}" method="POST" id="formTemplate">
            @csrf
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Informasi Dasar -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-info-circle text-primary"></i>
                                Informasi Dasar
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kode_template" class="form-label">
                                        Kode Template <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('kode_template') is-invalid @enderror"
                                        id="kode_template" name="kode_template" value="{{ old('kode_template') }}"
                                        placeholder="TPK-KABAN-2025" required>
                                    @error('kode_template')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="bi bi-info-circle"></i> Format: TPK-JABATAN-TAHUN
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="nama_template" class="form-label">
                                        Nama Template <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('nama_template') is-invalid @enderror"
                                        id="nama_template" name="nama_template" value="{{ old('nama_template') }}"
                                        placeholder="Template PK Kepala Bappeda 2025" required>
                                    @error('nama_template')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="jabatan_id" class="form-label">
                                        Jabatan <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select select2 @error('jabatan_id') is-invalid @enderror"
                                        id="jabatan_id" name="jabatan_id" required>
                                        <option value="">-- Pilih Jabatan --</option>
                                        @foreach ($jabatans as $jabatan)
                                            <option value="{{ $jabatan->id }}"
                                                {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                                {{ $jabatan->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jabatan_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="tahun" class="form-label">
                                        Tahun <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control @error('tahun') is-invalid @enderror"
                                        id="tahun" name="tahun" value="{{ old('tahun', $currentYear) }}"
                                        min="2020" max="2100" required>
                                    @error('tahun')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="page_size" class="form-label">
                                        Ukuran Halaman <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('page_size') is-invalid @enderror" id="page_size"
                                        name="page_size" required>
                                        @foreach ($pageSizes as $size)
                                            <option value="{{ $size }}"
                                                {{ old('page_size', 'A4') == $size ? 'selected' : '' }}>
                                                {{ $size }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('page_size')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="orientation" class="form-label">
                                        Orientasi <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('orientation') is-invalid @enderror" id="orientation"
                                        name="orientation" required>
                                        @foreach ($orientations as $orient)
                                            <option value="{{ $orient }}"
                                                {{ old('orientation', 'Portrait') == $orient ? 'selected' : '' }}>
                                                {{ $orient }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('orientation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                            value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <i class="bi bi-check-circle text-success"></i>
                                            Aktifkan template
                                        </label>
                                        <div class="form-text">
                                            Template aktif akan otomatis digunakan untuk PK baru
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kop Surat -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                                Kop Surat
                            </h5>
                            <textarea class="form-control editor @error('kop_surat_html') is-invalid @enderror" id="kop_surat_html"
                                name="kop_surat_html" rows="8">{{ old('kop_surat_html') }}</textarea>
                            @error('kop_surat_html')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-lightbulb"></i>
                                Gunakan HTML untuk format kop surat (logo, nama instansi, alamat, dll)
                            </div>
                        </div>
                    </div>

                    <!-- Header Template -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-layout-text-window text-primary"></i>
                                Header Template
                            </h5>
                            <textarea class="form-control editor @error('header_template') is-invalid @enderror" id="header_template"
                                name="header_template" rows="6">{{ old('header_template') }}</textarea>
                            @error('header_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-lightbulb"></i>
                                Judul dokumen, nomor surat, dll
                            </div>
                        </div>
                    </div>

                    <!-- Pernyataan Pembuka -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-chat-left-quote text-primary"></i>
                                Pernyataan Pembuka
                            </h5>
                            <textarea class="form-control editor @error('pernyataan_pembuka') is-invalid @enderror" id="pernyataan_pembuka"
                                name="pernyataan_pembuka" rows="8">{{ old('pernyataan_pembuka') }}</textarea>
                            @error('pernyataan_pembuka')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-lightbulb"></i>
                                Paragraf pembuka perjanjian kinerja
                            </div>
                        </div>
                    </div>

                    <!-- Pernyataan Penutup -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-chat-right-quote text-primary"></i>
                                Pernyataan Penutup
                            </h5>
                            <textarea class="form-control editor @error('pernyataan_penutup') is-invalid @enderror" id="pernyataan_penutup"
                                name="pernyataan_penutup" rows="8">{{ old('pernyataan_penutup') }}</textarea>
                            @error('pernyataan_penutup')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-lightbulb"></i>
                                Paragraf penutup perjanjian kinerja
                            </div>
                        </div>
                    </div>

                    <!-- Footer Template -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-file-earmark-arrow-down text-primary"></i>
                                Footer Template (Tanda Tangan)
                            </h5>
                            <textarea class="form-control editor @error('footer_template') is-invalid @enderror" id="footer_template"
                                name="footer_template" rows="10">{{ old('footer_template') }}</textarea>
                            @error('footer_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-lightbulb"></i>
                                Template untuk tanda tangan pihak pertama dan kedua
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Preview Card -->
                    <div class="card info-card">
                        <div class="card-body">
                            <h5 class="card-title text-white">
                                <i class="bi bi-eye"></i>
                                Preview Template
                            </h5>

                            <div class="preview-section">
                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-file-earmark"></i> Kode Template
                                    </label>
                                    <div id="preview-kode" class="preview-value">-</div>
                                </div>

                                <hr class="my-3">

                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-tag"></i> Nama Template
                                    </label>
                                    <div id="preview-nama" class="preview-value">-</div>
                                </div>

                                <hr class="my-3">

                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-briefcase"></i> Jabatan
                                    </label>
                                    <div id="preview-jabatan" class="preview-value">-</div>
                                </div>

                                <hr class="my-3">

                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-calendar"></i> Tahun
                                    </label>
                                    <div id="preview-tahun" class="preview-value">-</div>
                                </div>

                                <hr class="my-3">

                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-file-earmark"></i> Ukuran & Orientasi
                                    </label>
                                    <div id="preview-paper" class="preview-value">-</div>
                                </div>

                                <hr class="my-3">

                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-toggle-on"></i> Status
                                    </label>
                                    <div id="preview-status" class="preview-value">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-save"></i> Simpan Template
                            </button>
                            <a href="{{ route('perjanjian-kinerja.template.index') }}"
                                class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <!-- Variable Guide -->
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="card-title text-info">
                                <i class="bi bi-code-square"></i> Variabel Template
                            </h6>
                            <div class="small">
                                <p class="mb-2"><strong>Data Pegawai:</strong></p>
                                <ul class="ps-3 mb-3">
                                    <li><code>{nama_pegawai}</code></li>
                                    <li><code>{nip_pegawai}</code></li>
                                    <li><code>{jabatan_pegawai}</code></li>
                                    <li><code>{bidang_pegawai}</code></li>
                                </ul>

                                <p class="mb-2"><strong>Data Atasan:</strong></p>
                                <ul class="ps-3 mb-3">
                                    <li><code>{nama_atasan}</code></li>
                                    <li><code>{nip_atasan}</code></li>
                                    <li><code>{jabatan_atasan}</code></li>
                                </ul>

                                <p class="mb-2"><strong>Data Dokumen:</strong></p>
                                <ul class="ps-3 mb-3">
                                    <li><code>{nomor_perjanjian}</code></li>
                                    <li><code>{tanggal_ttd}</code></li>
                                    <li><code>{tempat_ttd}</code></li>
                                    <li><code>{total_anggaran}</code></li>
                                </ul>

                                <p class="mb-2"><strong>Data Dinamis:</strong></p>
                                <ul class="ps-3">
                                    <li><code>{sasaran_rows}</code></li>
                                    <li><code>{program_rows}</code></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Tips Card -->
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6 class="card-title text-warning">
                                <i class="bi bi-lightbulb"></i> Tips
                            </h6>
                            <ul class="small mb-0 ps-3">
                                <li class="mb-2">Gunakan HTML untuk formatting</li>
                                <li class="mb-2">Variabel akan diganti otomatis saat PDF dibuat</li>
                                <li class="mb-2">Sections default akan dibuat otomatis</li>
                                <li>Template dapat dipreview sebelum digunakan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>

    <style>
        /* Card Styling */
        .card {
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
            border-radius: 10px;
        }

        .card-title {
            color: #012970;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* Preview Styling */
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .info-card .card-title {
            color: white;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 0.75rem;
        }

        .preview-section {
            padding-top: 0.5rem;
        }

        .preview-item {
            margin-bottom: 0.5rem;
        }

        .preview-label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .preview-value {
            font-size: 1rem;
            font-weight: 600;
            color: white;
            word-wrap: break-word;
        }

        .info-card hr {
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Editor Styling */
        .editor {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        /* Form Controls */
        .form-control:focus,
        .form-select:focus {
            border-color: #4154f1;
            box-shadow: 0 0 0 0.2rem rgba(65, 84, 241, 0.15);
        }

        .select2-container {
            width: 100% !important;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5568d3 0%, #65408b 100%);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Border Cards */
        .border-info {
            border-left: 4px solid #0dcaf0 !important;
        }

        .border-warning {
            border-left: 4px solid #ffc107 !important;
        }

        /* Code styling */
        code {
            background-color: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.875em;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Real-time preview updates
            $('#kode_template').on('input', function() {
                $('#preview-kode').text($(this).val() || '-');
            });

            $('#nama_template').on('input', function() {
                $('#preview-nama').text($(this).val() || '-');
            });

            $('#jabatan_id').change(function() {
                const text = $(this).find('option:selected').text();
                $('#preview-jabatan').text(text !== '-- Pilih Jabatan --' ? text : '-');
            });

            $('#tahun').on('input change', function() {
                $('#preview-tahun').text($(this).val() || '-');
            });

            $('#page_size, #orientation').change(updatePaperPreview);

            $('#is_active').change(function() {
                if ($(this).is(':checked')) {
                    $('#preview-status').html('<span class="badge bg-success">Aktif</span>');
                } else {
                    $('#preview-status').html('<span class="badge bg-secondary">Tidak Aktif</span>');
                }
            });

            // Initialize preview
            updateAllPreviews();

            // Form validation
            $('#formTemplate').submit(function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Form Tidak Lengkap',
                        text: 'Mohon lengkapi semua field yang wajib diisi',
                        confirmButtonColor: '#667eea'
                    });
                }
                $(this).addClass('was-validated');
            });
        });

        function updatePaperPreview() {
            const size = $('#page_size').val();
            const orientation = $('#orientation').val();
            if (size && orientation) {
                $('#preview-paper').text(`${size} - ${orientation}`);
            } else {
                $('#preview-paper').text('-');
            }
        }

        function updateAllPreviews() {
            $('#preview-kode').text($('#kode_template').val() || '-');
            $('#preview-nama').text($('#nama_template').val() || '-');

            const jabatanText = $('#jabatan_id option:selected').text();
            $('#preview-jabatan').text(jabatanText !== '-- Pilih Jabatan --' ? jabatanText : '-');

            $('#preview-tahun').text($('#tahun').val() || '-');
            updatePaperPreview();

            if ($('#is_active').is(':checked')) {
                $('#preview-status').html('<span class="badge bg-success">Aktif</span>');
            } else {
                $('#preview-status').html('<span class="badge bg-secondary">Tidak Aktif</span>');
            }
        }
    </script>
@endpush
