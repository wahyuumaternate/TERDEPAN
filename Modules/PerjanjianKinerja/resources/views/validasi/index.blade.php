@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Validasi Perjanjian Kinerja</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.pk-saya') }}">Perjanjian Kinerja</a>
                </li>
                <li class="breadcrumb-item active">Validasi</li>
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
                                <h5 class="card-title mb-1">Daftar PK Menunggu Validasi</h5>
                                <p class="text-muted mb-0 small">Validasi PK dari bawahan Anda</p>
                            </div>
                            <span class="badge bg-warning fs-5">{{ $pkList->total() }} PK</span>
                        </div>

                        <!-- Filter -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <form method="GET" action="{{ route('perjanjian-kinerja.daftar-validasi') }}">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Tahun</label>
                                            <select class="form-select" name="tahun" onchange="this.form.submit()">
                                                <option value="">Semua Tahun</option>
                                                @foreach ($tahuns as $t)
                                                    <option value="{{ $t }}"
                                                        {{ request('tahun') == $t ? 'selected' : '' }}>
                                                        {{ $t }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Status</label>
                                            <select class="form-select" name="status" onchange="this.form.submit()">
                                                <option value=""
                                                    {{ request('status') === '' && request()->has('status') ? 'selected' : '' }}>
                                                    Semua Status</option>
                                                <option value="Menunggu"
                                                    {{ request('status') == 'Menunggu' || (!request()->has('status') && request('status') !== '') ? 'selected' : '' }}>
                                                    Menunggu
                                                </option>
                                                <option value="Disetujui"
                                                    {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui
                                                </option>
                                                <option value="Ditolak"
                                                    {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak
                                                </option>
                                                <option value="Revisi"
                                                    {{ request('status') == 'Revisi' ? 'selected' : '' }}>Revisi</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Bidang</label>
                                            <select class="form-select" name="bidang" onchange="this.form.submit()">
                                                <option value="">Semua Bidang</option>
                                                @foreach ($bidangs as $bidang)
                                                    <option value="{{ $bidang->id }}"
                                                        {{ request('bidang') == $bidang->id ? 'selected' : '' }}>
                                                        {{ $bidang->nama }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Tampilkan</label>
                                            <select class="form-select" name="per_page" onchange="this.form.submit()">
                                                <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15
                                                </option>
                                                <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30
                                                </option>
                                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ route('perjanjian-kinerja.daftar-validasi') }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="20%">Pegawai</th>
                                        <th width="15%">Jabatan</th>
                                        <th width="10%">Bidang</th>
                                        <th width="10%">Tahun</th>
                                        <th width="10%">Periode</th>
                                        <th width="10%" class="text-center">Status</th>
                                        <th width="10%">Tgl Dibuat</th>
                                        <th width="10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pkList as $index => $pk)
                                        <tr>
                                            <td>{{ $pkList->firstItem() + $index }}</td>
                                            <td>
                                                <strong>{{ $pk->pegawai->nama }}</strong>
                                                <br><small class="text-muted">NIP: {{ $pk->pegawai->nip }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $pk->pegawai->jabatan->nama_jabatan ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $pk->pegawai->bidang->nama ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $pk->tahun }}</span>
                                            </td>
                                            <td>
                                                <small>{{ $pk->periode->nama_periode ?? '-' }}</small>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $statusClass = match ($pk->status_validasi) {
                                                        'Menunggu' => 'bg-warning',
                                                        'Disetujui' => 'bg-success',
                                                        'Ditolak' => 'bg-danger',
                                                        'Revisi' => 'bg-info',
                                                        default => 'bg-secondary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $pk->status_validasi }}</span>
                                            </td>
                                            <td>
                                                <small>{{ $pk->created_at->format('d M Y') }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('perjanjian-kinerja.show', $pk->id) }}"
                                                        class="btn btn-info" title="Detail" target="_blank">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if ($pk->status_validasi == 'Menunggu')
                                                        <button type="button" class="btn btn-success btn-validasi"
                                                            data-id="{{ $pk->id }}"
                                                            data-pegawai="{{ $pk->pegawai->nama }}" title="Validasi">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                                <p class="text-muted mt-3">Tidak ada PK yang perlu divalidasi</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if ($pkList->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted small">
                                    Menampilkan {{ $pkList->firstItem() }} - {{ $pkList->lastItem() }} dari
                                    {{ $pkList->total() }} data
                                </div>
                                <div>
                                    {{ $pkList->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Validasi -->
    <div class="modal fade" id="validasiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formValidasi">
                    @csrf
                    <input type="hidden" id="pk_id" name="pk_id">

                    <div class="modal-header">
                        <h5 class="modal-title">Validasi Perjanjian Kinerja</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-1"></i>
                            PK dari: <strong id="nama_pegawai"></strong>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Validasi <span class="text-danger">*</span></label>
                            <select class="form-select" name="status_validasi" id="status_validasi" required>
                                <option value="">Pilih Status</option>
                                <option value="Disetujui">Disetujui</option>
                                <option value="Ditolak">Ditolak</option>
                                <option value="Revisi">Perlu Revisi</option>
                            </select>
                        </div>

                        <div class="mb-3" id="catatanGroup" style="display:none;">
                            <label class="form-label">Catatan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="catatan_validasi" id="catatan_validasi" rows="3"
                                placeholder="Berikan catatan untuk pegawai..."></textarea>
                            <small class="text-muted">Wajib diisi jika status Ditolak atau Perlu Revisi</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Simpan Validasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Open validasi modal
        $(document).on('click', '.btn-validasi', function() {
            const id = $(this).data('id');
            const pegawai = $(this).data('pegawai');

            $('#pk_id').val(id);
            $('#nama_pegawai').text(pegawai);
            $('#status_validasi').val('');
            $('#catatan_validasi').val('');
            $('#catatanGroup').hide();

            $('#validasiModal').modal('show');
        });

        // Show/hide catatan field
        $('#status_validasi').on('change', function() {
            const status = $(this).val();
            if (status === 'Ditolak' || status === 'Revisi') {
                $('#catatanGroup').show();
                $('#catatan_validasi').prop('required', true);
            } else {
                $('#catatanGroup').hide();
                $('#catatan_validasi').prop('required', false);
            }
        });

        // Submit validasi
        $('#formValidasi').on('submit', function(e) {
            e.preventDefault();

            const pkId = $('#pk_id').val();
            const status = $('#status_validasi').val();
            const catatan = $('#catatan_validasi').val();

            if (!status) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Pilih status validasi terlebih dahulu'
                });
                return;
            }

            if ((status === 'Ditolak' || status === 'Revisi') && !catatan) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Catatan wajib diisi untuk status ' + status
                });
                return;
            }

            // Confirm
            Swal.fire({
                title: 'Konfirmasi Validasi',
                html: `Status: <strong>${status}</strong><br>` +
                    (catatan ? `Catatan: ${catatan}` : ''),
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Validasi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/perjanjian-kinerja/${pkId}/validasi`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status_validasi: status,
                            catatan_validasi: catatan
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                $('#validasiModal').modal('hide');
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush
