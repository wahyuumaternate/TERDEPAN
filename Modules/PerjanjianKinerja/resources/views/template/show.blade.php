@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Detail Template</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.template.index') }}">Template PK</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <!-- Left Column: Template Info -->
            <div class="col-lg-4">
                <!-- Template Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="icon-box bg-primary bg-opacity-10 rounded-circle p-4 mx-auto mb-3"
                                style="width: 100px; height: 100px;">
                                <i class="bi bi-file-earmark-ruled-fill text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h4 class="mb-2">{{ $template->nama_template }}</h4>
                            <code class="text-primary fs-6">{{ $template->kode_template }}</code>
                        </div>

                        <div class="text-center mb-3">
                            @if ($template->is_active)
                                <span class="badge bg-success fs-6 px-3 py-2">
                                    <i class="bi bi-check-circle me-1"></i>Template Aktif
                                </span>
                            @else
                                <span class="badge bg-secondary fs-6 px-3 py-2">
                                    <i class="bi bi-dash-circle me-1"></i>Tidak Aktif
                                </span>
                            @endif
                            <span class="badge bg-info fs-6 px-3 py-2 ms-2">
                                <i class="bi bi-layers me-1"></i>Versi {{ $template->versi }}
                            </span>
                        </div>

                        <hr>

                        <!-- Template Details -->
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted" width="40%"><i class="bi bi-briefcase me-2"></i>Jabatan</td>
                                <td class="fw-bold">{{ $template->jabatan->nama }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="bi bi-calendar me-2"></i>Tahun</td>
                                <td class="fw-bold">{{ $template->tahun }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="bi bi-file-earmark me-2"></i>Ukuran</td>
                                <td class="fw-bold">{{ $template->page_size }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="bi bi-phone-landscape me-2"></i>Orientasi</td>
                                <td class="fw-bold">{{ $template->orientation }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="bi bi-list-ul me-2"></i>Sections</td>
                                <td class="fw-bold">{{ $template->sections->count() }} sections</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="bi bi-diagram-3 me-2"></i>Digunakan</td>
                                <td class="fw-bold">{{ $usageCount }} PK</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="bi bi-activity me-2"></i>PK Aktif</td>
                                <td>
                                    @if ($activeUsage > 0)
                                        <span class="badge bg-warning">{{ $activeUsage }} aktif</span>
                                    @else
                                        <span class="text-muted">Tidak ada</span>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <hr>

                        <!-- Timestamps -->
                        <div class="small text-muted">
                            <p class="mb-1">
                                <i class="bi bi-clock-history me-1"></i>
                                Dibuat: {{ $template->created_at->format('d M Y H:i') }}
                            </p>
                            <p class="mb-0">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                Update: {{ $template->updated_at->format('d M Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">
                            <i class="bi bi-gear me-2"></i>Aksi
                        </h6>

                        <div class="d-grid gap-2">
                            <a href="{{ route('perjanjian-kinerja.template.preview-pdf', $template->id) }}"
                                class="btn btn-primary" target="_blank">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Preview PDF
                            </a>

                            <a href="{{ route('perjanjian-kinerja.template.download-pdf', $template->id) }}"
                                class="btn btn-outline-primary">
                                <i class="bi bi-download me-2"></i>Download PDF
                            </a>

                            @if (!$template->is_active)
                                <button type="button" class="btn btn-success"
                                    onclick="activateTemplate({{ $template->id }})">
                                    <i class="bi bi-check-circle me-2"></i>Aktifkan Template
                                </button>
                            @endif

                            @if ($activeUsage == 0)
                                <a href="{{ route('perjanjian-kinerja.template.edit', $template->id) }}"
                                    class="btn btn-warning">
                                    <i class="bi bi-pencil me-2"></i>Edit Template
                                </a>
                            @else
                                <button type="button" class="btn btn-warning" disabled title="Template sedang digunakan">
                                    <i class="bi bi-lock me-2"></i>Edit (Terkunci)
                                </button>
                            @endif

                            <button type="button" class="btn btn-info" onclick="duplicateTemplate({{ $template->id }})">
                                <i class="bi bi-files me-2"></i>Duplikat Template
                            </button>

                            @if ($usageCount == 0)
                                <button type="button" class="btn btn-danger"
                                    onclick="deleteTemplate({{ $template->id }})">
                                    <i class="bi bi-trash me-2"></i>Hapus Template
                                </button>
                            @else
                                <button type="button" class="btn btn-danger" disabled title="Template masih digunakan">
                                    <i class="bi bi-lock me-2"></i>Hapus (Terkunci)
                                </button>
                            @endif

                            <a href="{{ route('perjanjian-kinerja.template.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sections & Content -->
            <div class="col-lg-8">
                <!-- Sections List -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-4">
                            <i class="bi bi-list-ol me-2"></i>Sections Template
                            <span class="badge bg-primary ms-2">{{ $template->sections->count() }}</span>
                        </h5>

                        <div class="accordion accordion-flush" id="accordionSections">
                            @foreach ($template->sections as $section)
                                <div class="accordion-item border rounded-3 mb-2">
                                    <h2 class="accordion-header" id="heading{{ $section->id }}">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapse{{ $section->id }}"
                                            aria-expanded="false">
                                            <div class="d-flex align-items-center w-100">
                                                <span
                                                    class="badge bg-light text-dark border me-3">{{ $section->urutan }}</span>
                                                <div class="flex-grow-1">
                                                    <strong>{{ $section->section_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <code>{{ $section->section_code }}</code>
                                                        <span class="mx-2">•</span>
                                                        <span
                                                            class="badge badge-sm 
                                                            @if ($section->section_type == 'static') bg-info
                                                            @elseif($section->section_type == 'dynamic') bg-warning
                                                            @elseif($section->section_type == 'table') bg-success
                                                            @else bg-primary @endif">
                                                            {{ ucfirst($section->section_type) }}
                                                        </span>
                                                        @if ($section->is_required)
                                                            <span class="badge bg-danger badge-sm ms-1">Required</span>
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $section->id }}" class="accordion-collapse collapse"
                                        data-bs-parent="#accordionSections">
                                        <div class="accordion-body bg-light">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block mb-1">Section Code</small>
                                                    <code class="text-primary">{{ $section->section_code }}</code>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block mb-1">Section Type</small>
                                                    <span
                                                        class="badge 
                                                        @if ($section->section_type == 'static') bg-info
                                                        @elseif($section->section_type == 'dynamic') bg-warning
                                                        @elseif($section->section_type == 'table') bg-success
                                                        @else bg-primary @endif">
                                                        {{ ucfirst($section->section_type) }}
                                                    </span>
                                                </div>
                                            </div>

                                            @if ($section->content_template)
                                                <div class="mt-3">
                                                    <small class="text-muted d-block mb-2">Content Template:</small>
                                                    <div class="bg-white border rounded p-3">
                                                        <pre class="mb-0" style="max-height: 300px; overflow-y: auto;"><code>{{ $section->content_template }}</code></pre>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if ($template->sections->count() == 0)
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">Belum ada sections</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Template Content Preview -->
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-4">
                            <i class="bi bi-eye me-2"></i>Preview Konten Template
                        </h5>

                        <!-- Kop Surat -->
                        @if ($template->kop_surat_html)
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-file-earmark-text me-1"></i>Kop Surat
                                </h6>
                                <div class="border rounded p-3 bg-light">
                                    {!! $template->kop_surat_html !!}
                                </div>
                            </div>
                        @endif

                        <!-- Header -->
                        @if ($template->header_template)
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-file-text me-1"></i>Header
                                </h6>
                                <div class="border rounded p-3 bg-light">
                                    {!! $template->header_template !!}
                                </div>
                            </div>
                        @endif

                        <!-- Pernyataan Pembuka -->
                        @if ($template->pernyataan_pembuka)
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-chat-left-quote me-1"></i>Pernyataan Pembuka
                                </h6>
                                <div class="border rounded p-3 bg-light">
                                    {!! $template->pernyataan_pembuka !!}
                                </div>
                            </div>
                        @endif

                        <!-- Pernyataan Penutup -->
                        @if ($template->pernyataan_penutup)
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-chat-right-quote me-1"></i>Pernyataan Penutup
                                </h6>
                                <div class="border rounded p-3 bg-light">
                                    {!! $template->pernyataan_penutup !!}
                                </div>
                            </div>
                        @endif

                        <!-- Footer -->
                        @if ($template->footer_template)
                            <div class="mb-0">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i>Footer (TTD)
                                </h6>
                                <div class="border rounded p-3 bg-light">
                                    {!! $template->footer_template !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Usage Statistics -->
                @if ($usageCount > 0)
                    <div class="card shadow-sm border-0 mt-3">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-4">
                                <i class="bi bi-diagram-3 me-2"></i>Penggunaan Template
                                <span class="badge bg-primary ms-2">{{ $usageCount }}</span>
                            </h5>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Nomor PK</th>
                                            <th>Pegawai</th>
                                            <th>Tahun</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($template->perjanjianKinerja->take(10) as $index => $pk)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><code>{{ $pk->nomor_perjanjian }}</code></td>
                                                <td>{{ $pk->pegawai->nama }}</td>
                                                <td>{{ $pk->tahun }}</td>
                                                <td>
                                                    <span
                                                        class="badge 
                                                        @if ($pk->status_dokumen == 'Draft') bg-secondary
                                                        @elseif($pk->status_dokumen == 'Aktif') bg-success
                                                        @elseif($pk->status_dokumen == 'Menunggu_TTD') bg-warning
                                                        @else bg-info @endif">
                                                        {{ $pk->status_dokumen }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('perjanjian-kinerja.show', $pk->id) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Lihat PK">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Belum ada PK yang
                                                    menggunakan template ini</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($template->perjanjianKinerja->count() > 10)
                                <div class="text-center mt-3">
                                    <a href="{{ route('perjanjian-kinerja.index', ['template_id' => $template->id]) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-plus-circle me-1"></i>Lihat Semua ({{ $usageCount }})
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Modal Duplicate -->
    <div class="modal fade" id="modalDuplicateTemplate" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Duplikat Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formDuplicateTemplate">
                        @csrf
                        <input type="hidden" id="duplicate_template_id" value="{{ $template->id }}">
                        <div class="mb-3">
                            <label for="kode_template" class="form-label">Kode Template <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode_template" name="kode_template"
                                value="{{ $template->kode_template }}-COPY" required>
                            <div class="form-text">Format: TPK-KABAN-2025</div>
                        </div>
                        <div class="mb-3">
                            <label for="nama_template" class="form-label">Nama Template <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_template" name="nama_template"
                                value="{{ $template->nama_template }} (Copy)" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnDuplicateTemplate">
                        <i class="bi bi-files me-1"></i> Duplikat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            color: #012970;
        }

        pre code {
            font-size: 0.85rem;
            line-height: 1.5;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Duplicate button handler
            $('#btnDuplicateTemplate').click(function() {
                let templateId = $('#duplicate_template_id').val();
                let formData = {
                    _token: '{{ csrf_token() }}',
                    kode_template: $('#kode_template').val(),
                    nama_template: $('#nama_template').val()
                };

                $.ajax({
                    url: `/perjanjian-kinerja/template/${templateId}/duplicate`,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#modalDuplicateTemplate').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Template berhasil diduplikasi',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href =
                                `/perjanjian-kinerja/template/${response.id || response.data?.id}`;
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan saat duplikasi template';
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
            });
        });

        function activateTemplate(id) {
            Swal.fire({
                title: 'Aktifkan Template?',
                text: "Template lain untuk jabatan dan tahun yang sama akan dinonaktifkan",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Ya, Aktifkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/perjanjian-kinerja/template/${id}/activate`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Template berhasil diaktifkan',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Gagal mengaktifkan template'
                            });
                        }
                    });
                }
            });
        }

        function duplicateTemplate(id) {
            $('#modalDuplicateTemplate').modal('show');
        }

        function deleteTemplate(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Template akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/perjanjian-kinerja/template/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Template berhasil dihapus.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href =
                                    '{{ route('perjanjian-kinerja.template.index') }}';
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Gagal menghapus template'
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush
