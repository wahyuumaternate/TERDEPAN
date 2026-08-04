@extends('layouts.main')

@section('main')
    <div class="penugasan-pegawai-page">
        <div class="pagetitle">
            <h1>Penugasan Pegawai</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Penugasan Pegawai</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <!-- Baris Tab + Tombol Buat Tugas (satu baris, tombol rata kanan) -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <ul class="nav nav-tabs mb-0 flex-grow-1">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'saya' ? 'active' : '' }}"
                            href="{{ route('penugasan.tugas-saya', ['tab' => 'saya']) }}">
                            <i class="bi bi-person-check me-1"></i>Tugas Saya
                        </a>
                    </li>
                    @if ($bisaMemberi)
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'diberikan' ? 'active' : '' }}"
                                href="{{ route('penugasan.tugas-saya', ['tab' => 'diberikan']) }}">
                                <i class="bi bi-send me-1"></i>Penugasan Pegawai
                                @if ($tab === 'diberikan' && $perpanjanganMenunggu->isNotEmpty())
                                    <span class="badge bg-danger ms-1">{{ $perpanjanganMenunggu->count() }}</span>
                                @endif
                            </a>
                        </li>
                    @endif
                </ul>
                <a href="{{ route('penugasan.create') }}" class="btn btn-primary btn-sm flex-shrink-0">
                    <i class="bi bi-plus-circle me-1"></i>Buat Tugas
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($tab === 'diberikan' && $perpanjanganMenunggu->isNotEmpty())
                <div class="alert alert-warning" role="alert">
                    <h6 class="alert-heading"><i class="bi bi-hourglass-split me-2"></i>Menunggu Keputusan Anda</h6>
                    <p class="mb-2 small">{{ $perpanjanganMenunggu->count() }} pengajuan perpanjangan waktu menunggu persetujuan:</p>
                    <ul class="mb-0 small">
                        @foreach ($perpanjanganMenunggu as $pengajuan)
                            <li>
                                <a href="{{ route('penugasan.show', $pengajuan->penugasan_id) }}">
                                    {{ $pengajuan->penugasan->nama_tugas ?? '-' }}
                                </a>
                                — {{ $pengajuan->penugasan->pegawai->nama ?? '-' }}
                                (minta sampai {{ optional($pengajuan->deadline_diminta)->format('d M Y') }})
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Satu card: statistik ringkas + filter/sort + tabel responsive -->
            <div class="card shadow-sm border-0">
                <div class="card-body py-2">
                    <form method="GET" class="row g-2 align-items-end mb-1">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <div class="col-6 col-lg">
                            <label class="form-label small mb-1">Status</label>
                            <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                @foreach ($statusOptions as $s)
                                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-lg">
                            <label class="form-label small mb-1">Prioritas</label>
                            <select class="form-select form-select-sm" name="prioritas" onchange="this.form.submit()">
                                <option value="">Semua Prioritas</option>
                                @foreach ($prioritasOptions as $p)
                                    <option value="{{ $p }}" {{ request('prioritas') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-lg">
                            <label class="form-label small mb-1">Jenis</label>
                            <select class="form-select form-select-sm" name="jenis" onchange="this.form.submit()">
                                <option value="">Semua Jenis</option>
                                <option value="pokok" {{ request('jenis') === 'pokok' ? 'selected' : '' }}>Tugas Pokok</option>
                                <option value="tambahan" {{ request('jenis') === 'tambahan' ? 'selected' : '' }}>Tugas Tambahan</option>
                            </select>
                        </div>
                        <div class="col-6 col-lg">
                            <label class="form-label small mb-1">Urutkan</label>
                            <select class="form-select form-select-sm" name="sort" onchange="this.form.submit()">
                                <option value="urgensi" {{ request('sort', 'urgensi') === 'urgensi' ? 'selected' : '' }}>Paling Mendesak</option>
                                <option value="prioritas" {{ request('sort') === 'prioritas' ? 'selected' : '' }}>Prioritas Tertinggi</option>
                                <option value="terbaru" {{ request('sort') === 'terbaru' ? 'selected' : '' }}>Terbaru Dibuat</option>
                                <option value="nama" {{ request('sort') === 'nama' ? 'selected' : '' }}>Nama Tugas (A-Z)</option>
                            </select>
                        </div>
                        <div class="col-6 col-lg-auto">
                            <a href="{{ route('penugasan.tugas-saya', ['tab' => $tab]) }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>

                <hr class="m-0">

                <!-- Statistik ringkas + tabel — diperbarui berkala lewat polling AJAX tanpa reload halaman -->
                <div class="card-body pt-3" id="tabelPenugasanWrapper">
                    @include('penugasan::penugasan.partials.tugas-saya-tabel', ['penugasan' => $penugasan, 'tab' => $tab, 'stats' => $stats])
                </div>
            </div>
        </section>
    </div>

    @push('styles')
        <style>
            /* Ukuran tipografi & tombol sedikit dikecilkan khusus halaman ini (dok. permintaan UI) */
            .penugasan-pegawai-page {
                font-size: 0.925rem;
            }

            .penugasan-pegawai-page .pagetitle h1 {
                font-size: 1.6rem;
            }

            .penugasan-pegawai-page .nav-tabs .nav-link {
                font-size: 0.9rem;
                padding: 0.5rem 0.9rem;
            }

            .penugasan-pegawai-page .btn {
                font-size: 0.825rem;
            }

            .penugasan-pegawai-page .table {
                font-size: 0.875rem;
            }

            .penugasan-pegawai-page .badge {
                font-size: 0.7rem;
                font-weight: 600;
            }

            .penugasan-pegawai-page .stat-label {
                font-size: 0.72rem;
                text-transform: uppercase;
                letter-spacing: 0.02em;
            }

            .penugasan-pegawai-page .form-label {
                font-size: 0.8rem;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                const wrapper = document.getElementById('tabelPenugasanWrapper');
                const dataUrl = "{{ route('penugasan.tugas-saya.data') }}";
                const intervalMs = 10000;
                let timer = null;
                let sedangMuat = false;

                function muatUlangTabel() {
                    if (sedangMuat || document.hidden) {
                        return;
                    }
                    sedangMuat = true;

                    fetch(dataUrl + window.location.search, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.ok ? r.text() : Promise.reject())
                        .then(html => {
                            wrapper.innerHTML = html;
                        })
                        .catch(() => {
                            // Diamkan kegagalan polling (mis. koneksi terputus sesaat) — tabel
                            // yang sudah tampil tetap dipertahankan, dicoba lagi di interval berikutnya.
                        })
                        .finally(() => {
                            sedangMuat = false;
                        });
                }

                function mulaiPolling() {
                    if (!timer) {
                        timer = setInterval(muatUlangTabel, intervalMs);
                    }
                }

                function hentikanPolling() {
                    if (timer) {
                        clearInterval(timer);
                        timer = null;
                    }
                }

                // Jeda polling saat tab browser tidak aktif supaya tidak boros request.
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        hentikanPolling();
                    } else {
                        muatUlangTabel();
                        mulaiPolling();
                    }
                });

                mulaiPolling();
            })();
        </script>
    @endpush
@endsection
