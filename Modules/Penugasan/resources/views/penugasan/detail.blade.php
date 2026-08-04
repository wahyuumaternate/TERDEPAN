@extends('layouts.main')

@php
    $warnaStatus = match ($penugasan->status) {
        'pending' => 'bg-secondary',
        'proses' => 'bg-primary',
        'revisi' => 'bg-warning text-dark',
        'terlambat' => 'bg-danger',
        'selesai' => $penugasan->realisasi_persen === null ? 'bg-info text-dark' : 'bg-success',
        'ditolak' => 'bg-dark',
        default => 'bg-secondary',
    };
    $labelStatus = $penugasan->status === 'selesai' && $penugasan->realisasi_persen === null
        ? 'Menunggu Nilai'
        : ucfirst($penugasan->status);

    $isPegawai = auth()->id() === $penugasan->pegawai_id;
    $bukanKoordinatorGrupKolektif = $penugasan->mode_grup === 'kolektif' && ! $penugasan->is_koordinator;
    $pengajuanMenunggu = $penugasan->perpanjanganWaktu->firstWhere('status', 'menunggu');

    // Gabungkan progress harian + histori revisi jadi satu timeline, urut waktu terbaru dulu.
    // Pakai collect() eksplisit (bukan langsung ->map() di atas Eloquent Collection) supaya hasil map
    // selalu jadi base Collection: ketika salah satu relasi kosong, override map() Eloquent Collection
    // gagal mendeteksi hasil non-Model (vacuously false pada collection kosong) dan tetap Eloquent
    // Collection, sehingga ->merge() berikutnya memanggil getKey() pada array biasa dan error.
    $timeline = collect($penugasan->progress)->map(fn ($p) => [
        'waktu' => $p->tanggal,
        'tipe' => 'progress',
        'item' => $p,
    ])->merge(collect($penugasan->historyRevisi)->map(fn ($r) => [
        'waktu' => $r->tanggal_revisi,
        'tipe' => 'revisi',
        'item' => $r,
    ]))->sortByDesc('waktu');
@endphp

@section('main')
    <div class="pagetitle">
        <h1>Detail Penugasan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tugas-saya') }}">Tugas Saya</a></li>
                <li class="breadcrumb-item active">{{ Str::limit($penugasan->nama_tugas, 40) }}</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                            <div>
                                <span class="badge bg-info bg-opacity-10 text-info mb-2">
                                    {{ $penugasan->jenis === 'pokok' ? 'Tugas Pokok' : 'Tugas Tambahan' }}
                                </span>
                                @if ($penugasan->is_mandiri)
                                    <span class="badge bg-secondary bg-opacity-25 text-body mb-2"><i class="bi bi-person-check me-1"></i>Mandiri</span>
                                @endif
                                @if ($penugasan->mode_grup)
                                    <span class="badge bg-secondary bg-opacity-25 text-body mb-2">
                                        <i class="bi bi-people me-1"></i>{{ $penugasan->mode_grup === 'kolektif' ? 'Kolektif' : 'Per Orang' }}
                                        {{ $penugasan->is_koordinator ? '(Koordinator)' : '' }}
                                    </span>
                                @endif
                                <h4 class="fw-bold mb-0">{{ $penugasan->nama_tugas }}</h4>
                            </div>
                            <span class="badge {{ $warnaStatus }} fs-6">{{ $labelStatus }}</span>
                        </div>

                        @if ($penugasan->deskripsi)
                            <p class="mb-3">{{ $penugasan->deskripsi }}</p>
                        @endif

                        @if ($penugasan->status === 'ditolak' && $penugasan->alasan_reject)
                            <div class="alert alert-dark mb-3">
                                <small class="text-muted d-block mb-1">Alasan Ditolak</small>
                                {{ $penugasan->alasan_reject }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Aksi Kontekstual -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Aksi</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @if ($izin['terima'] && ! $bukanKoordinatorGrupKolektif)
                                <button class="btn btn-success" onclick="aksiSederhana('terima', 'Penugasan diterima')">
                                    <i class="bi bi-check-circle me-1"></i>Terima Tugas
                                </button>
                            @endif
                            @if ($izin['tolak'] && ! $bukanKoordinatorGrupKolektif)
                                <button class="btn btn-outline-danger" onclick="tolakTugas()">
                                    <i class="bi bi-x-circle me-1"></i>Tolak Tugas
                                </button>
                            @endif
                            @if ($isPegawai && in_array($penugasan->status, ['proses', 'revisi', 'terlambat']))
                                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#progressModal">
                                    <i class="bi bi-graph-up me-1"></i>Catat Progress
                                </button>
                            @endif
                            @if ($izin['uploadEviden'])
                                <a href="{{ route('penugasan.form-upload-bukti', $penugasan->id) }}" class="btn btn-primary">
                                    <i class="bi bi-cloud-upload me-1"></i>Upload Bukti Pengerjaan
                                </a>
                            @endif
                            @if ($izin['ajukanPerpanjangan'])
                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#perpanjanganModal">
                                    <i class="bi bi-clock-history me-1"></i>Ajukan Perpanjangan
                                </button>
                            @endif
                            @if ($izin['submit'] && ! $bukanKoordinatorGrupKolektif)
                                <button class="btn btn-success" onclick="aksiSederhana('submit', 'Penugasan diajukan Selesai')">
                                    <i class="bi bi-send-check me-1"></i>Selesaikan Tugas
                                </button>
                            @endif
                            @if ($izin['approveMandiri'])
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveMandiriModal">
                                    <i class="bi bi-check-circle me-1"></i>Setujui Tugas Mandiri
                                </button>
                            @endif
                            @if ($izin['rejectMandiri'])
                                <button class="btn btn-outline-danger" onclick="rejectMandiri()">
                                    <i class="bi bi-x-circle me-1"></i>Tolak Tugas Mandiri
                                </button>
                            @endif
                            @if ($izin['putuskanPerpanjangan'] && $pengajuanMenunggu)
                                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#putuskanPerpanjanganModal">
                                    <i class="bi bi-hourglass-split me-1"></i>Putuskan Perpanjangan
                                </button>
                            @endif
                            @if ($izin['revisi'])
                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#revisiModal">
                                    <i class="bi bi-arrow-repeat me-1"></i>Berikan Revisi
                                </button>
                            @endif
                            @if ($izin['nilai'])
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nilaiModal">
                                    <i class="bi bi-star me-1"></i>Beri Penilaian
                                </button>
                            @endif
                            @if ($penugasan->is_mandiri && $penugasan->status === 'ditolak' && $izin['update'])
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#ubahAjukanUlangModal">
                                    <i class="bi bi-pencil me-1"></i>Ubah &amp; Ajukan Ulang
                                </button>
                            @endif
                            @if ($penugasan->is_mandiri && $penugasan->status === 'ditolak' && $izin['delete'])
                                <button class="btn btn-outline-danger" onclick="hapusTugas()">
                                    <i class="bi bi-trash me-1"></i>Hapus
                                </button>
                            @endif
                            @if (!$izin['terima'] && !$izin['tolak'] && !$izin['submit'] && $penugasan->status === 'pending' && $penugasan->is_mandiri)
                                <span class="text-muted"><i class="bi bi-hourglass-split me-1"></i>Menunggu persetujuan atasan</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Accordion: Ringkasan, Progress & Timeline, Evidence, Penilaian -->
                <div class="accordion" id="detailAccordion">
                    <!-- Ringkasan -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRingkasan">
                                Ringkasan
                            </button>
                        </h2>
                        <div id="collapseRingkasan" class="accordion-collapse collapse show" data-bs-parent="#detailAccordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Pegawai</small>
                                        <div class="fw-semibold">{{ $penugasan->pegawai->nama }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Pemberi Tugas</small>
                                        <div class="fw-semibold">{{ $penugasan->pemberiTugas->nama ?? 'Mandiri' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Prioritas</small>
                                        @if ($izin['update'] && in_array($penugasan->status, ['pending', 'proses']))
                                            <select class="form-select form-select-sm" style="max-width: 160px"
                                                onchange="updatePrioritas(this.value)">
                                                @foreach (['rendah', 'sedang', 'tinggi'] as $p)
                                                    <option value="{{ $p }}" {{ $penugasan->prioritas === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <div class="fw-semibold">{{ ucfirst($penugasan->prioritas) }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Tanggal Mulai</small>
                                        <div class="fw-semibold">{{ $penugasan->tanggal_mulai->format('d M Y') }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Deadline</small>
                                        <div class="fw-semibold">
                                            {{ optional($penugasan->deadline_terbaru)->format('d M Y') }}
                                            @if ($penugasan->deadline_terbaru && ! $penugasan->deadline_terbaru->isSameDay($penugasan->tanggal_selesai))
                                                <span class="badge bg-warning bg-opacity-25 text-warning-emphasis">
                                                    Diperbarui dari {{ $penugasan->tanggal_selesai->format('d M Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($penugasan->target_value)
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Target</small>
                                            <div class="fw-semibold">{{ $penugasan->target_value }} {{ $penugasan->satuan }}</div>
                                        </div>
                                    @endif
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Bobot</small>
                                        @if ($izin['update'] && $penugasan->realisasi_persen === null)
                                            <div class="input-group input-group-sm" style="max-width: 160px">
                                                <input type="number" class="form-control" id="bobotInput" min="0" max="100"
                                                    step="0.01" value="{{ $penugasan->bobot_persen }}">
                                                <button class="btn btn-outline-secondary" onclick="updateBobot()">Simpan</button>
                                            </div>
                                        @else
                                            <div class="fw-semibold">{{ $penugasan->bobot_persen ?? '-' }}%</div>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Progress</small>
                                        <div class="fw-semibold">{{ $penugasan->progress_persen }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress & Timeline -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTimeline">
                                Progress &amp; Timeline
                            </button>
                        </h2>
                        <div id="collapseTimeline" class="accordion-collapse collapse" data-bs-parent="#detailAccordion">
                            <div class="accordion-body">
                                @forelse ($timeline as $entri)
                                    @if ($entri['tipe'] === 'progress')
                                        <div class="d-flex justify-content-between border-bottom py-2">
                                            <div>
                                                <div class="fw-semibold">{{ $entri['item']->deskripsi_kegiatan }}</div>
                                                @if ($entri['item']->kendala)
                                                    <small class="text-danger">Kendala: {{ $entri['item']->kendala }}</small>
                                                @endif
                                                <small class="text-muted d-block">{{ $entri['item']->tanggal->format('d M Y') }}</small>
                                            </div>
                                            <span class="badge bg-primary align-self-start">{{ $entri['item']->progress_persen }}%</span>
                                        </div>
                                    @else
                                        <div class="border-bottom py-2">
                                            <div class="d-flex justify-content-between">
                                                <strong>Revisi ke-{{ $entri['item']->revisi_ke }}</strong>
                                                <small class="text-muted">{{ optional($entri['item']->tanggal_revisi)->format('d M Y') }}</small>
                                            </div>
                                            <p class="mb-1 small">{{ $entri['item']->catatan_revisi }}</p>
                                            <small class="text-muted">
                                                Oleh: {{ $entri['item']->direvisiOleh->nama ?? '-' }} •
                                                Deadline baru: {{ optional($entri['item']->deadline_revisi)->format('d M Y') }}
                                            </small>
                                        </div>
                                    @endif
                                @empty
                                    <p class="text-muted mb-0">Belum ada catatan progress atau revisi.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Evidence -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEvidence">
                                Evidence &amp; Perpanjangan Waktu ({{ $penugasan->attachedFiles->count() }})
                            </button>
                        </h2>
                        <div id="collapseEvidence" class="accordion-collapse collapse" data-bs-parent="#detailAccordion">
                            <div class="accordion-body">
                                <h6 class="small text-muted text-uppercase">Bukti Pengerjaan</h6>
                                @forelse ($penugasan->attachedFiles as $file)
                                    <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                                        <div><i class="bi bi-file-earmark-text text-primary me-2"></i>{{ $file->original_name }}</div>
                                        <a href="{{ route('terminaldata.filesData.download', $file->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                @empty
                                    <p class="text-muted small">Belum ada file bukti diupload.</p>
                                @endforelse

                                @if ($penugasan->perpanjanganWaktu->isNotEmpty())
                                    <h6 class="small text-muted text-uppercase mt-3">Riwayat Perpanjangan Waktu</h6>
                                    @foreach ($penugasan->perpanjanganWaktu as $pengajuan)
                                        @php
                                            $warnaPengajuan = match ($pengajuan->status) {
                                                'disetujui' => 'bg-success',
                                                'ditolak' => 'bg-dark',
                                                default => 'bg-warning text-dark',
                                            };
                                        @endphp
                                        <div class="border-bottom py-2">
                                            <div class="d-flex justify-content-between">
                                                <strong>Pengajuan ke-{{ $pengajuan->ke_berapa }}</strong>
                                                <span class="badge {{ $warnaPengajuan }}">{{ ucfirst($pengajuan->status) }}</span>
                                            </div>
                                            <small class="text-muted d-block">
                                                Minta sampai {{ optional($pengajuan->deadline_diminta)->format('d M Y') }}
                                                @if ($pengajuan->deadline_disetujui)
                                                    → disetujui sampai {{ $pengajuan->deadline_disetujui->format('d M Y') }}
                                                @endif
                                            </small>
                                            <p class="small mb-0">{{ $pengajuan->alasan_pengajuan }}</p>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Penilaian -->
                    @if ($penugasan->status === 'selesai')
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePenilaian">
                                    Penilaian
                                </button>
                            </h2>
                            <div id="collapsePenilaian" class="accordion-collapse collapse" data-bs-parent="#detailAccordion">
                                <div class="accordion-body">
                                    @if ($penugasan->realisasi_persen === null)
                                        <p class="text-muted mb-0">Belum dinilai oleh pemberi tugas.</p>
                                    @else
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <small class="text-muted d-block">Realisasi</small>
                                                <div class="fw-semibold">{{ $penugasan->realisasi_persen }}%</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block">Nilai Awal</small>
                                                <div class="fw-semibold">{{ $penugasan->nilai_awal }}</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block">Potongan Terlambat</small>
                                                <div class="fw-semibold">{{ $penugasan->persentase_terlambat }}%</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block">Nilai Akhir</small>
                                                <div class="fw-bold text-success fs-5">{{ $penugasan->nilai_akhir }}</div>
                                            </div>
                                        </div>
                                        @if ($penugasan->catatan_validasi)
                                            <div class="alert alert-success mt-3 mb-0">
                                                <small class="text-muted d-block mb-1">Catatan ({{ $penugasan->validator->nama ?? '-' }})</small>
                                                {{ $penugasan->catatan_validasi }}
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                @if ($penugasan->mode_grup && $penugasan->grupAnggota->isNotEmpty())
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0"><i class="bi bi-people me-2"></i>Anggota Grup</h6>
                        </div>
                        <div class="card-body">
                            @foreach ($penugasan->grupAnggota as $anggota)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <span>{{ $anggota->pegawai->nama ?? '-' }} {{ $anggota->is_koordinator ? '(Koordinator)' : '' }}</span>
                                    <a href="{{ route('penugasan.show', $anggota->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Modal Catat Progress -->
    <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="progressModalLabel">Catat Progress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Progress (%) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="progressPersen" min="0" max="100" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="deskripsiKegiatan" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kendala (opsional)</label>
                        <textarea class="form-control" id="kendala" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="simpanProgress()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajukan Perpanjangan -->
    <div class="modal fade" id="perpanjanganModal" tabindex="-1" aria-labelledby="perpanjanganModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="perpanjanganModalLabel">Ajukan Perpanjangan Waktu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">
                        Pengajuan disetujui sejauh ini: {{ $penugasan->perpanjanganWaktu->where('status', 'disetujui')->count() }} dari maks. 3x.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Deadline Baru yang Diminta <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="deadlineDiminta" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Pengajuan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="alasanPengajuan" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" onclick="ajukanPerpanjangan()">Kirim Pengajuan</button>
                </div>
            </div>
        </div>
    </div>

    @if ($izin['putuskanPerpanjangan'] && $pengajuanMenunggu)
        <!-- Modal Putuskan Perpanjangan -->
        <div class="modal fade" id="putuskanPerpanjanganModal" tabindex="-1" aria-labelledby="putuskanPerpanjanganModalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="putuskanPerpanjanganModalLabel">Putuskan Perpanjangan Waktu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small">
                            {{ $penugasan->pegawai->nama }} meminta perpanjangan sampai
                            <strong>{{ optional($pengajuanMenunggu->deadline_diminta)->format('d M Y') }}</strong>.
                        </p>
                        <p class="small text-muted">{{ $pengajuanMenunggu->alasan_pengajuan }}</p>
                        <div class="mb-3">
                            <label class="form-label">Deadline yang Disetujui <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="deadlineDisetujui"
                                value="{{ optional($pengajuanMenunggu->deadline_diminta)->format('Y-m-d') }}" required>
                            <small class="text-muted">Boleh diubah berbeda dari yang diminta pegawai.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan (opsional)</label>
                            <textarea class="form-control" id="catatanAtasanPerpanjangan" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" onclick="putuskanPerpanjangan(false)">Tolak</button>
                        <button type="button" class="btn btn-success" onclick="putuskanPerpanjangan(true)">Setujui</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($izin['revisi'])
        <!-- Modal Berikan Revisi -->
        <div class="modal fade" id="revisiModal" tabindex="-1" aria-labelledby="revisiModalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="revisiModalLabel">Berikan Revisi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Catatan Revisi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="catatanRevisi" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deadline Baru <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="deadlineBaruRevisi" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-warning" onclick="simpanRevisi()">Kirim Revisi</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($izin['nilai'])
        <!-- Modal Beri Penilaian -->
        <div class="modal fade" id="nilaiModal" tabindex="-1" aria-labelledby="nilaiModalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="nilaiModalLabel">Beri Penilaian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        @if ($penugasan->bobot_persen === null)
                            <div class="mb-3">
                                <label class="form-label">Bobot (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="nilaiBobot" min="0" max="100" step="0.01" required>
                                <small class="text-muted">Bobot belum pernah diisi — wajib diisi sekarang.</small>
                            </div>
                        @else
                            <p class="small text-muted">Bobot tugas: <strong>{{ $penugasan->bobot_persen }}%</strong></p>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Realisasi (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="nilaiRealisasi" min="0" max="100" step="0.01" required>
                        </div>
                        <div class="alert alert-info d-none" id="previewNilaiAlert">
                            Potongan terlambat: <strong id="previewTerlambat">-</strong>% —
                            Nilai Akhir: <strong id="previewNilaiAkhir">-</strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan Validasi</label>
                            <textarea class="form-control" id="catatanValidasi" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" onclick="simpanNilai()">Simpan Penilaian</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($izin['approveMandiri'])
        <!-- Modal Setujui Tugas Mandiri -->
        <div class="modal fade" id="approveMandiriModal" tabindex="-1" aria-labelledby="approveMandiriModalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveMandiriModalLabel">Setujui Tugas Mandiri</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">Anda bisa menyesuaikan prioritas sebelum menyetujui (opsional).</p>
                        <div class="mb-3">
                            <label class="form-label">Prioritas</label>
                            <select class="form-select" id="approvePrioritas">
                                <option value="">(gunakan usulan pegawai: {{ ucfirst($penugasan->prioritas) }})</option>
                                @foreach (['rendah', 'sedang', 'tinggi'] as $p)
                                    <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-success" onclick="approveMandiri()">Setujui</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($penugasan->is_mandiri && $penugasan->status === 'ditolak' && $izin['update'])
        <!-- Modal Ubah & Ajukan Ulang -->
        <div class="modal fade" id="ubahAjukanUlangModal" tabindex="-1" aria-labelledby="ubahAjukanUlangModalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ubahAjukanUlangModalLabel">Ubah &amp; Ajukan Ulang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Tugas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ubahNamaTugas" value="{{ $penugasan->nama_tugas }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="ubahDeskripsi" rows="3" required>{{ $penugasan->deskripsi }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="ubahTanggalSelesai"
                                    value="{{ $penugasan->tanggal_selesai->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" onclick="ubahAjukanUlang()">Ajukan Ulang</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const penugasanId = '{{ $penugasan->id }}';

        function reloadWithMessage(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => window.location.reload());
        }

        function showError(message) {
            Swal.fire('Gagal', message || 'Terjadi kesalahan', 'error');
        }

        function postJson(url, payload = {}) {
            return fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json());
        }

        function aksiSederhana(aksi, pesanSukses) {
            postJson(`{{ url('/penugasan') }}/${penugasanId}/${aksi}`)
                .then(data => data.success ? reloadWithMessage(data.message || pesanSukses) : showError(data.message));
        }

        function tolakTugas() {
            Swal.fire({
                title: 'Alasan Penolakan',
                input: 'textarea',
                inputPlaceholder: 'Jelaskan alasan menolak tugas ini...',
                showCancelButton: true,
                confirmButtonText: 'Tolak Tugas',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    postJson(`{{ url('/penugasan') }}/${penugasanId}/tolak`, {
                            alasan_penolakan: result.value
                        })
                        .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
                }
            });
        }

        function hapusTugas() {
            Swal.fire({
                title: 'Hapus Penugasan?',
                text: 'Tindakan ini tidak bisa dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('/penugasan') }}/${penugasanId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Berhasil', data.message, 'success')
                                    .then(() => window.location.href = "{{ route('penugasan.tugas-saya') }}");
                            } else {
                                showError(data.message);
                            }
                        });
                }
            });
        }

        function simpanProgress() {
            const payload = {
                progress_persen: document.getElementById('progressPersen').value,
                deskripsi_kegiatan: document.getElementById('deskripsiKegiatan').value,
                kendala: document.getElementById('kendala').value,
            };
            postJson(`{{ url('/penugasan') }}/${penugasanId}/update-progress`, payload)
                .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
        }

        function ajukanPerpanjangan() {
            const payload = {
                deadline_diminta: document.getElementById('deadlineDiminta').value,
                alasan_pengajuan: document.getElementById('alasanPengajuan').value,
            };
            postJson(`{{ url('/penugasan') }}/${penugasanId}/perpanjangan-waktu`, payload)
                .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
        }

        @if ($izin['putuskanPerpanjangan'] && $pengajuanMenunggu)
            function putuskanPerpanjangan(disetujui) {
                const url =
                    `{{ url('/penugasan') }}/${penugasanId}/perpanjangan-waktu/{{ $pengajuanMenunggu->id }}/${disetujui ? 'setujui' : 'tolak'}`;
                const payload = {
                    catatan_atasan: document.getElementById('catatanAtasanPerpanjangan').value,
                };
                if (disetujui) {
                    payload.deadline_disetujui = document.getElementById('deadlineDisetujui').value;
                }
                postJson(url, payload).then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
            }
        @endif

        @if ($izin['revisi'])
            function simpanRevisi() {
                const payload = {
                    catatan_revisi: document.getElementById('catatanRevisi').value,
                    deadline_baru: document.getElementById('deadlineBaruRevisi').value,
                };
                postJson(`{{ url('/penugasan') }}/${penugasanId}/revisi`, payload)
                    .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
            }
        @endif

        @if ($izin['nilai'])
            function hitungPreviewNilai() {
                const bobot = document.getElementById('nilaiBobot')?.value ?? '{{ $penugasan->bobot_persen }}';
                const realisasi = document.getElementById('nilaiRealisasi').value;
                if (!bobot || realisasi === '') {
                    document.getElementById('previewNilaiAlert').classList.add('d-none');
                    return;
                }

                fetch("{{ route('penugasan.tim.preview-penilaian') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            penugasan_id: penugasanId,
                            bobot_persen: bobot,
                            realisasi_persen: realisasi
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('previewTerlambat').textContent = data.data.persentase_terlambat;
                            document.getElementById('previewNilaiAkhir').textContent = data.data.nilai_akhir;
                            document.getElementById('previewNilaiAlert').classList.remove('d-none');
                        }
                    });
            }
            document.getElementById('nilaiRealisasi')?.addEventListener('input', hitungPreviewNilai);
            document.getElementById('nilaiBobot')?.addEventListener('input', hitungPreviewNilai);

            function simpanNilai() {
                const payload = {
                    realisasi_persen: document.getElementById('nilaiRealisasi').value,
                    catatan_validasi: document.getElementById('catatanValidasi').value,
                };
                const bobotInput = document.getElementById('nilaiBobot');
                if (bobotInput) {
                    payload.bobot_persen = bobotInput.value;
                }
                postJson(`{{ url('/penugasan') }}/${penugasanId}/nilai`, payload)
                    .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
            }
        @endif

        @if ($izin['rejectMandiri'])
            function rejectMandiri() {
                Swal.fire({
                    title: 'Alasan Penolakan Tugas Mandiri',
                    input: 'textarea',
                    inputPlaceholder: 'Jelaskan alasan menolak tugas mandiri ini...',
                    showCancelButton: true,
                    confirmButtonText: 'Tolak',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        postJson(`{{ url('/penugasan') }}/${penugasanId}/reject-mandiri`, {
                                alasan_reject: result.value
                            })
                            .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
                    }
                });
            }
        @endif

        @if ($izin['approveMandiri'])
            function approveMandiri() {
                const prioritas = document.getElementById('approvePrioritas').value;
                const payload = prioritas ? {
                    prioritas
                } : {};
                postJson(`{{ url('/penugasan') }}/${penugasanId}/approve-mandiri`, payload)
                    .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
            }
        @endif

        @if ($izin['update'] && in_array($penugasan->status, ['pending', 'proses']))
            function updatePrioritas(value) {
                fetch(`{{ url('/penugasan') }}/${penugasanId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            prioritas: value
                        })
                    })
                    .then(r => r.json())
                    .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
            }
        @endif

        @if ($izin['update'] && $penugasan->realisasi_persen === null)
            function updateBobot() {
                const value = document.getElementById('bobotInput').value;
                fetch(`{{ url('/penugasan') }}/${penugasanId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            bobot_persen: value
                        })
                    })
                    .then(r => r.json())
                    .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
            }
        @endif

        @if ($penugasan->is_mandiri && $penugasan->status === 'ditolak' && $izin['update'])
            function ubahAjukanUlang() {
                const payload = {
                    nama_tugas: document.getElementById('ubahNamaTugas').value,
                    deskripsi: document.getElementById('ubahDeskripsi').value,
                    tanggal_selesai: document.getElementById('ubahTanggalSelesai').value,
                };
                fetch(`{{ url('/penugasan') }}/${penugasanId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(r => r.json())
                    .then(data => data.success ? reloadWithMessage(data.message) : showError(data.message));
            }
        @endif
    </script>
@endpush
