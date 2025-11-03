@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Detail Perjanjian Kinerja</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.index') }}">Perjanjian Kinerja</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Header Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title text-primary mb-2">{{ $pk->nomor_perjanjian }}</h5>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-calendar3 me-2"></i>
                                    Periode: {{ date('d M Y', strtotime($pk->periode_mulai)) }} -
                                    {{ date('d M Y', strtotime($pk->periode_selesai)) }}
                                </p>
                            </div>
                            <div class="text-end">
                                @php
                                    $statusClass = [
                                        'Draft' => 'bg-secondary',
                                        'Generated' => 'bg-info',
                                        'Menunggu_TTD' => 'bg-warning',
                                        'Aktif' => 'bg-success',
                                        'Selesai' => 'bg-primary',
                                        'Dibatalkan' => 'bg-danger',
                                    ];
                                    $statusText = str_replace('_', ' ', $pk->status_dokumen);
                                @endphp
                                <span class="badge {{ $statusClass[$pk->status_dokumen] }} px-3 py-2">
                                    {{ $statusText }}
                                </span>
                                @if ($pk->is_locked)
                                    <span class="badge bg-success px-3 py-2 ms-2">
                                        <i class="bi bi-lock-fill me-1"></i> Terkunci
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="btn-toolbar gap-2" role="toolbar">
                            @if (!$pk->is_locked)
                                <a href="{{ route('perjanjian-kinerja.edit', $pk->id) }}" class="btn btn-warning">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            @endif

                            <button onclick="confirmGeneratePDF({{ $pk->id }})" class="btn btn-danger">
                                <i class="bi bi-file-pdf me-1"></i> Generate PDF
                            </button>

                            @if ($pk->dokumen->where('is_latest', true)->first())
                                <a href="{{ route('perjanjian-kinerja.preview', $pk->id) }}" class="btn btn-info"
                                    target="_blank">
                                    <i class="bi bi-eye me-1"></i> Preview PDF
                                </a>
                                <a href="{{ route('perjanjian-kinerja.download', $pk->id) }}" class="btn btn-success">
                                    <i class="bi bi-download me-1"></i> Download PDF
                                </a>
                            @endif

                            @if (($pk->status_dokumen == 'Menunggu_TTD' || $pk->status_dokumen == 'Generated') && !$pk->tanggal_ttd)
                                <button onclick="showSignModal({{ $pk->id }})" class="btn btn-primary">
                                    <i class="bi bi-pen me-1"></i> Tanda Tangani
                                </button>
                            @endif

                            <a href="{{ route('perjanjian-kinerja.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Pegawai Info -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-badge text-primary me-2"></i>
                            Informasi Pegawai
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td width="120" class="text-muted">Nama</td>
                                        <td class="fw-semibold">{{ $pk->pegawai->nama }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">NIP</td>
                                        <td>{{ $pk->pegawai->nomor_identitas ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Jabatan</td>
                                        <td>{{ $pk->pegawai->jabatan->nama ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Unit Kerja</td>
                                        <td>{{ $pk->pegawai->bidang->nama ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="border-start ps-3">
                                    <h6 class="text-muted mb-3">Atasan Langsung</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="120" class="text-muted">Nama</td>
                                            <td class="fw-semibold">{{ $pk->atasan->nama }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">NIP</td>
                                            <td>{{ $pk->atasan->nomor_identitas ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Jabatan</td>
                                            <td>{{ $pk->atasan->jabatan->nama ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sasaran & Indikator -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bullseye text-primary me-2"></i>
                            Sasaran dan Indikator Kinerja
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse($pk->sasaran as $index => $sasaran)
                            <div class="sasaran-item mb-4 pb-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="d-flex align-items-start mb-3">
                                    <span class="badge bg-primary rounded-circle me-3"
                                        style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                        {{ $sasaran->urutan }}
                                    </span>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-2">{{ $sasaran->sasaran_strategis }}</h6>

                                        @if ($sasaran->indikator->count() > 0)
                                            <div class="mt-3">
                                                <p class="text-muted small mb-2"><strong>Indikator Kinerja:</strong></p>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th width="50">No</th>
                                                                <th>Indikator</th>
                                                                <th width="100">Target</th>
                                                                <th width="80">Satuan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($sasaran->indikator as $ind)
                                                                <tr>
                                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                                    <td>{{ $ind->indikator_sasaran }}</td>
                                                                    <td class="text-end">
                                                                        {{ number_format($ind->target_value, 0) }}</td>
                                                                    <td>{{ $ind->satuan }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">Belum ada sasaran yang ditambahkan</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Program & Kegiatan -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>
                            Program, Kegiatan dan Sub Kegiatan
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $totalAnggaran = 0;
                        @endphp

                        @forelse($pk->sasaran as $sasaran)
                            @foreach ($sasaran->indikator as $indikator)
                                @foreach ($indikator->program as $program)
                                    <div class="program-item mb-4">
                                        <!-- Program -->
                                        <div class="d-flex align-items-start mb-2 p-3 bg-light rounded">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 text-primary">
                                                    <i class="bi bi-folder-fill me-2"></i>
                                                    {{ $program->nama_program }}
                                                </h6>
                                                <p class="text-muted small mb-0">
                                                    Anggaran: <strong>Rp
                                                        {{ number_format($program->anggaran, 0, ',', '.') }}</strong>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Kegiatan -->
                                        @foreach ($program->kegiatan as $kegiatan)
                                            <div class="kegiatan-item ms-4 mb-3">
                                                <div
                                                    class="d-flex align-items-start mb-2 p-2 bg-white border-start border-3 border-primary">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1" style="font-size: 0.95rem;">
                                                            <i class="bi bi-file-earmark-text me-2"></i>

                                                            {{ $kegiatan->nama_kegiatan }}
                                                        </h6>
                                                        <p class="text-muted small mb-0">
                                                            Anggaran: Rp
                                                            {{ number_format($kegiatan->anggaran, 0, ',', '.') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Sub Kegiatan -->
                                                @if ($kegiatan->subKegiatan->count() > 0)
                                                    <div class="ms-4">
                                                        @foreach ($kegiatan->subKegiatan as $subKegiatan)
                                                            <div
                                                                class="d-flex align-items-start mb-2 p-2 bg-light rounded">
                                                                <div class="flex-grow-1">
                                                                    <p class="mb-1 small">
                                                                        <i class="bi bi-arrow-return-right me-2"></i>

                                                                        {{ $subKegiatan->nama_sub_kegiatan }}
                                                                    </p>
                                                                    <div class="d-flex justify-content-between">
                                                                        <span class="text-muted small">
                                                                            Anggaran: Rp
                                                                            {{ number_format($subKegiatan->anggaran, 0, ',', '.') }}
                                                                        </span>
                                                                        <span class="text-muted small">
                                                                            Target: {{ $subKegiatan->target_value }}
                                                                            {{ $subKegiatan->satuan }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    @php
                                        $totalAnggaran += $program->anggaran;
                                    @endphp
                                @endforeach
                            @endforeach
                        @empty
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">Belum ada program yang ditambahkan</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Summary Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Ringkasan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="summary-item mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Total Sasaran</span>
                                <span class="badge bg-primary rounded-pill">{{ $pk->sasaran->count() }}</span>
                            </div>
                        </div>
                        <div class="summary-item mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Total Indikator</span>
                                <span class="badge bg-info rounded-pill">
                                    {{ $pk->sasaran->sum(function ($s) {return $s->indikator->count();}) }}
                                </span>
                            </div>
                        </div>
                        <div class="summary-item mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Total Program</span>
                                <span class="badge bg-success rounded-pill">
                                    @php
                                        $totalProgram = 0;
                                        foreach ($pk->sasaran as $s) {
                                            foreach ($s->indikator as $i) {
                                                $totalProgram += $i->program->count();
                                            }
                                        }
                                    @endphp
                                    {{ $totalProgram }}
                                </span>
                            </div>
                        </div>
                        <div class="summary-item">
                            <div class="text-muted mb-1 small">Total Anggaran</div>
                            <h4 class="text-primary mb-0">
                                Rp {{ number_format($pk->total_anggaran, 0, ',', '.') }}
                            </h4>
                        </div>
                    </div>
                </div>

                <!-- Template Info -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0">
                            <i class="bi bi-file-earmark-richtext text-primary me-2"></i>
                            Informasi Template
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($pk->template)
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">Kode</td>
                                    <td class="fw-semibold">{{ $pk->template->kode_template }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Nama</td>
                                    <td>{{ $pk->template->nama_template }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Versi</td>
                                    <td>{{ $pk->template->versi }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status</td>
                                    <td>
                                        <span
                                            class="badge {{ $pk->template->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $pk->template->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        @else
                            <p class="text-muted mb-0">Template tidak tersedia</p>
                        @endif
                    </div>
                </div>

                <!-- TTD Info -->
                @if ($pk->tanggal_ttd)
                    <div class="card shadow-sm border-0 mb-4 border-success">
                        <div class="card-header bg-success bg-opacity-10">
                            <h6 class="mb-0 text-success">
                                <i class="bi bi-pen-fill me-2"></i>
                                Informasi Tanda Tangan
                            </h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">Tanggal</td>
                                    <td class="fw-semibold">{{ date('d F Y', strtotime($pk->tanggal_ttd)) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tempat</td>
                                    <td>{{ $pk->tempat_ttd }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Document History -->
                @if ($pk->dokumen->count() > 0)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent">
                            <h6 class="mb-0">
                                <i class="bi bi-clock-history text-primary me-2"></i>
                                Riwayat Dokumen
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @foreach ($pk->dokumen->take(5) as $doc)
                                    <div
                                        class="list-group-item px-0 {{ $doc->is_latest ? 'border-start border-3 border-success' : '' }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 small">
                                                    Versi {{ $doc->versi }}
                                                    @if ($doc->is_latest)
                                                        <span class="badge bg-success ms-2">Latest</span>
                                                    @endif
                                                </h6>
                                                <p class="text-muted small mb-1">
                                                    {{ date('d M Y H:i', strtotime($doc->created_at)) }}
                                                </p>
                                            </div>
                                            <a href="{{ Storage::url($doc->file_path) }}"
                                                class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Notes -->
                @if ($pk->catatan)
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-transparent">
                            <h6 class="mb-0">
                                <i class="bi bi-sticky text-primary me-2"></i>
                                Catatan
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-0">{{ $pk->catatan }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Modal Generate PDF -->
    <div class="modal fade" id="modalGeneratePDF" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Dokumen PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Dokumen PDF akan dibuat berdasarkan template yang dipilih.
                    </div>
                    <p>Apakah Anda yakin ingin men-generate dokumen PDF?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnGeneratePDF">
                        <i class="bi bi-file-pdf me-1"></i> Generate PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sign Document -->
    <div class="modal fade" id="modalSignDocument" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tanda Tangan Perjanjian Kinerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Dokumen yang sudah ditandatangani akan otomatis dikunci.
                    </div>
                    <form id="formSignDocument">
                        <input type="hidden" id="sign_pk_id" value="{{ $pk->id }}">
                        <div class="mb-3">
                            <label for="tanggal_ttd" class="form-label">Tanggal Tanda Tangan</label>
                            <input type="date" class="form-control" id="tanggal_ttd" name="tanggal_ttd" required
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label for="tempat_ttd" class="form-label">Tempat Tanda Tangan</label>
                            <input type="text" class="form-control" id="tempat_ttd" name="tempat_ttd" required
                                placeholder="Contoh: Sofifi" value="Sofifi">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnSignDocument">
                        <i class="bi bi-pen me-1"></i> Tanda Tangani
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .program-item {
            border-left: 4px solid #0d6efd;
            padding-left: 1rem;
        }

        .kegiatan-item {
            position: relative;
        }

        .summary-item:last-child {
            border-bottom: none !important;
            padding-bottom: 0 !important;
        }

        .card {
            border-radius: 12px;
        }

        .card-header {
            border-radius: 12px 12px 0 0 !important;
        }

        .list-group-item {
            border-left: none;
            border-right: none;
        }

        .list-group-item:first-child {
            border-top: none;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }
    </style>
@endsection

@push('scripts')
    <script>
        function confirmGeneratePDF(id) {
            $('#modalGeneratePDF').modal('show');

            $('#btnGeneratePDF').off('click').on('click', function() {
                generatePDF(id);
            });
        }

        function generatePDF(id) {
            $.ajax({
                url: "{{ url('perjanjian-kinerja') }}/" + id + "/generate",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    $('#btnGeneratePDF').html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                    ).prop('disabled', true);
                },
                success: function(response) {
                    $('#modalGeneratePDF').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Dokumen PDF berhasil di-generate',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat generate dokumen';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errorMessage
                    });
                },
                complete: function() {
                    $('#btnGeneratePDF').html('<i class="bi bi-file-pdf me-1"></i> Generate PDF').prop(
                        'disabled', false);
                }
            });
        }

        function showSignModal(id) {
            $('#sign_pk_id').val(id);
            $('#modalSignDocument').modal('show');
        }

        $(document).ready(function() {
            $('#btnSignDocument').click(function() {
                const pkId = $('#sign_pk_id').val();
                const tanggalTtd = $('#tanggal_ttd').val();
                const tempatTtd = $('#tempat_ttd').val();

                if (!tanggalTtd || !tempatTtd) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Semua field harus diisi'
                    });
                    return;
                }

                $.ajax({
                    url: "{{ url('perjanjian-kinerja') }}/" + pkId + "/sign",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tanggal_ttd: tanggalTtd,
                        tempat_ttd: tempatTtd
                    },
                    beforeSend: function() {
                        $('#btnSignDocument').html(
                            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                        ).prop('disabled', true);
                    },
                    success: function(response) {
                        $('#modalSignDocument').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Dokumen berhasil ditandatangani',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan saat menandatangani dokumen';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMessage
                        });
                    },
                    complete: function() {
                        $('#btnSignDocument').html(
                            '<i class="bi bi-pen me-1"></i> Tanda Tangani').prop('disabled',
                            false);
                    }
                });
            });
        });
    </script>
@endpush
