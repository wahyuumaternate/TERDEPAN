@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Daftar Penugasan Pegawai</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tugas-pokok.index') }}">Penugasan</a></li>
                <li class="breadcrumb-item active">Daftar Pegawai</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Total Pegawai <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10">
                                <i class="bi bi-people text-primary"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $stats['total_pegawai'] }}</h6>
                                <span class="text-muted small pt-1">pegawai aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Dengan Tugas <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10">
                                <i class="bi bi-person-check text-success"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $stats['pegawai_dengan_tugas'] }}</h6>
                                <span class="text-muted small pt-1">pegawai</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Total Tugas <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10">
                                <i class="bi bi-file-earmark-text text-info"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $stats['total_tugas'] }}</h6>
                                <span class="text-muted small pt-1">tugas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card info-card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Status Tugas <span>| {{ $tahun }}</span></h5>
                        <div class="d-flex align-items-center">
                            <div
                                class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10">
                                <i class="bi bi-graph-up text-warning"></i>
                            </div>
                            <div class="ps-3">
                                <h6 class="mb-0 fw-bold">{{ $stats['dikerjakan'] }}/{{ $stats['selesai'] }}</h6>
                                <span class="text-muted small pt-1">proses/selesai</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="card-title mb-1 d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 rounded-3 p-2 me-3">
                                        <i class="bi bi-people text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Pegawai</span>
                                        <small class="d-block text-muted fw-normal mt-1">Kelola tugas pokok per
                                            pegawai</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <button class="btn btn-info btn-lg shadow-sm px-4 py-2" onclick="sinkronData()">
                                    <i class="bi bi-arrow-repeat me-1"></i> Sinkron Data
                                </button>
                            </div>
                        </div>

                        <!-- Filter Form -->
                        <form id="filterForm" action="{{ route('penugasan.tugas-pokok.index') }}" method="GET">
                            <div class="row mb-4">
                                <div class="col-lg-2 col-md-3 mb-3">
                                    <label class="form-label">Tahun</label>
                                    <select class="form-select" id="filterTahun" name="tahun"
                                        onchange="this.form.submit()">
                                        @foreach ($tahuns as $t)
                                            <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>
                                                {{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-4 mb-3">
                                    <label class="form-label">Jabatan</label>
                                    <select class="form-select" id="filterJabatan" name="jabatan_id">
                                        <option value="">Semua Jabatan</option>
                                        @foreach ($jabatans as $jabatan)
                                            <option value="{{ $jabatan->id }}"
                                                {{ request()->jabatan_id == $jabatan->id ? 'selected' : '' }}>
                                                {{ $jabatan->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-4 mb-3">
                                    <label class="form-label">Bidang</label>
                                    <select class="form-select" id="filterBidang" name="bidang_id">
                                        <option value="">Semua Bidang</option>
                                        @foreach ($bidangs as $bidang)
                                            <option value="{{ $bidang->id }}"
                                                {{ request()->bidang_id == $bidang->id ? 'selected' : '' }}>
                                                {{ $bidang->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-3 mb-3">
                                    <label class="form-label">Tampilkan</label>
                                    <select class="form-select" id="filterHasTugas" name="has_tugas">
                                        <option value="">Semua Pegawai</option>
                                        <option value="1" {{ request()->has_tugas ? 'selected' : '' }}>
                                            Dengan Tugas Saja</option>
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-3 mb-3">
                                    <label class="form-label">Per Halaman</label>
                                    <select class="form-select" name="per_page" onchange="this.form.submit()">
                                        <option value="15" {{ request()->per_page == 15 ? 'selected' : '' }}>15
                                        </option>
                                        <option value="25" {{ request()->per_page == 25 ? 'selected' : '' }}>25
                                        </option>
                                        <option value="50" {{ request()->per_page == 50 ? 'selected' : '' }}>50
                                        </option>
                                        <option value="100" {{ request()->per_page == 100 ? 'selected' : '' }}>100
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="d-flex gap-2 justify-content-between">
                                        <div class="input-group" style="max-width: 400px;">
                                            <input type="text" class="form-control" id="searchPegawai" name="search"
                                                value="{{ request()->search }}"
                                                placeholder="Cari nama atau NIP pegawai...">
                                            <button class="btn btn-outline-secondary" type="submit">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="bi bi-filter me-1"></i> Filter
                                            </button>
                                            <a href="{{ route('penugasan.tugas-pokok.index', ['tahun' => $tahun]) }}"
                                                class="btn btn-outline-secondary px-3">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- View Toggle -->
                        <div class="mb-4">
                            <div class="btn-group shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="viewMode" id="viewTable" checked>
                                <label class="btn btn-outline-primary px-4" for="viewTable">
                                    <i class="bi bi-table me-2"></i>Tampilan Tabel
                                </label>
                                <input type="radio" class="btn-check" name="viewMode" id="viewGrid">
                                <label class="btn btn-outline-primary px-4" for="viewGrid">
                                    <i class="bi bi-grid-3x3-gap me-2"></i>Tampilan Kartu
                                </label>
                            </div>
                        </div>

                        <!-- Table View -->
                        <div id="tableView" class="mb-3">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped" id="pegawaiTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" width="50">#</th>
                                            <th width="280">Nama Pegawai</th>
                                            <th width="220">Bidang / Jabatan</th>
                                            <th class="text-center" width="90">Pokok</th>
                                            <th class="text-center" width="90">Harian</th>
                                            <th class="text-center" width="100">Tambahan</th>
                                            <th class="text-center" width="120">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pegawaiList as $index => $pegawai)
                                            <tr>
                                                <td class="text-center align-middle">
                                                    {{ ($pegawaiList->currentPage() - 1) * $pegawaiList->perPage() + $index + 1 }}
                                                </td>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle me-3 flex-shrink-0">
                                                            {{ strtoupper(substr($pegawai->nama, 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold text-dark">{{ $pegawai->nama }}</div>
                                                            <small
                                                                class="text-muted">{{ $pegawai->nomor_identitas ?? '-' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div>
                                                        @if ($pegawai->bidang)
                                                            <span class="badge mb-1"
                                                                style="background-color: {{ $pegawai->bidang->warna ?? '#6c757d' }};">
                                                                {{ $pegawai->bidang->kode }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary mb-1">-</span>
                                                        @endif
                                                        <br>
                                                        <small
                                                            class="text-muted">{{ $pegawai->jabatan->nama ?? '-' }}</small>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span
                                                        class="badge bg-primary rounded-pill">{{ $pegawai->tugas_pokok_count ?? 0 }}</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span
                                                        class="badge bg-info rounded-pill">{{ $pegawai->tugas_harian_count ?? 0 }}</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span
                                                        class="badge bg-warning rounded-pill">{{ $pegawai->tugas_tambahan_count ?? 0 }}</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <a href="{{ route('penugasan.tugas-pokok.show', $pegawai->id) }}"
                                                        class="btn btn-sm btn-primary" title="Lihat Tugas Pokok">
                                                        <i class="bi bi-eye me-1"></i> Lihat Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                                    <p class="text-muted mt-2">Tidak ada data pegawai</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $pegawaiList->withQueryString()->links() }}
                            </div>
                        </div>

                        <!-- Grid View -->
                        <div id="gridView" style="display: none;">
                            <div class="row">
                                @forelse($pegawaiList as $pegawai)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card pegawai-card h-100 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start pt-3 mb-3">
                                                    <div class="avatar-circle-lg me-3">
                                                        {{ strtoupper(substr($pegawai->nama, 0, 2)) }}
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 fw-bold">{{ $pegawai->nama }}</h6>
                                                        <p class="text-muted mb-1 small">{{ $pegawai->nomor_identitas ?? '-' }}</p>
                                                        <span
                                                            class="badge bg-info text-white">{{ $pegawai->jabatan->nama ?? '-' }}</span>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <small class="text-muted d-flex align-items-center">
                                                        <i class="bi bi-building me-2"></i>
                                                        <span>{{ $pegawai->bidang->kode ?? '-' }}</span>
                                                    </small>
                                                </div>

                                                <hr>

                                                <div class="row text-center mb-3">
                                                    <div class="col-4 mb-2">
                                                        <div class="stat-box">
                                                            <h5 class="mb-0 text-primary">
                                                                {{ $pegawai->tugas_pokok_count ?? 0 }}</h5>
                                                            <small class="text-muted">Tugas Pokok</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4 mb-2">
                                                        <div class="stat-box">
                                                            <h5 class="mb-0 text-info">
                                                                {{ $pegawai->tugas_harian_count ?? 0 }}</h5>
                                                            <small class="text-muted">Tugas Harian</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="stat-box">
                                                            <h5 class="mb-0 text-warning">
                                                                {{ $pegawai->tugas_tambahan_count ?? 0 }}</h5>
                                                            <small class="text-muted">Tugas Tambahan</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <a href="{{ route('penugasan.tugas-pokok.show', $pegawai->id) }}"
                                                    class="btn btn-primary w-100">
                                                    <i class="bi bi-eye me-1"></i> Detail
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">Tidak ada data pegawai</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Pagination for Grid View -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $pegawaiList->withQueryString()->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <style>
        /* Card Styles */
        .pegawai-card {
            position: relative;
            overflow: hidden;
            border-radius: 12px !important;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
        }

        .pegawai-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        /* Avatar Styles - Fixed size and circular */
        .avatar-circle {
            width: 40px;
            height: 40px;
            min-width: 40px;
            min-height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }

        .avatar-circle-lg {
            width: 56px;
            height: 56px;
            min-width: 56px;
            min-height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            flex-shrink: 0;
        }

        /* Table Styles */
        #pegawaiTable thead th {
            font-weight: 600;
            color: #495057;
            vertical-align: middle;
        }

        #pegawaiTable tbody tr {
            transition: background-color 0.2s ease;
        }

        #pegawaiTable tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        #pegawaiTable tbody td {
            vertical-align: middle;
            padding: 0.75rem;
        }

        /* Ensure columns maintain width */
        #pegawaiTable th,
        #pegawaiTable td {
            white-space: nowrap;
        }

        #pegawaiTable th:nth-child(2),
        #pegawaiTable td:nth-child(2) {
            white-space: normal;
        }

        #pegawaiTable th:nth-child(3),
        #pegawaiTable td:nth-child(3) {
            white-space: normal;
        }

        /* Card Icon */
        .card-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }

        /* Icon Box */
        .icon-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
        }

        /* Dashboard cards */
        .info-card {
            border-radius: 12px;
            overflow: hidden;
        }

        .info-card .card-body {
            padding: 1.25rem;
        }

        .info-card h6 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Stat Box */
        .stat-box {
            padding: 0.5rem;
        }

        .stat-box h5 {
            font-weight: 700;
        }

        /* Badge spacing */
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // View mode toggle
            $('input[name="viewMode"]').change(function() {
                let viewMode = $(this).attr('id');
                if (viewMode === 'viewGrid') {
                    $('#gridView').show();
                    $('#tableView').hide();
                    localStorage.setItem('pegawai_view_mode', 'grid');
                } else {
                    $('#tableView').show();
                    $('#gridView').hide();
                    localStorage.setItem('pegawai_view_mode', 'table');
                }
            });

            // Restore view mode from localStorage
            const savedViewMode = localStorage.getItem('pegawai_view_mode');
            if (savedViewMode === 'grid') {
                $('#viewGrid').prop('checked', true).trigger('change');
            }
        });

        // Function to sync data
        function sinkronData() {
            Swal.fire({
                title: 'Sinkronisasi Data',
                text: 'Apakah Anda yakin ingin mensinkronkan data tugas pokok dari Perjanjian Kinerja?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Sinkronkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('penugasan.tugas-pokok.sinkron') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Memproses...',
                                text: 'Sedang mensinkronkan data',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: `
                                    <p>${response.message}</p>
                                    <p class="mb-0">
                                        <strong>Dibuat:</strong> ${response.created} tugas<br>
                                        <strong>Dilewati:</strong> ${response.skipped} tugas
                                    </p>
                                `,
                                timer: 3000,
                                showConfirmButton: true
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'Terjadi kesalahan saat sinkronisasi';
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
    </script>
@endpush
