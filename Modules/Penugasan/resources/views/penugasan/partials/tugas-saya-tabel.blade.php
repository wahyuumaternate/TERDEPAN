@php
    $prioritasBadgeMap = [
        'rendah' => 'bg-secondary',
        'sedang' => 'bg-info text-dark',
        'tinggi' => 'bg-danger',
    ];

    /**
     * Warna badge status semantik — dok. 08 §5.3. "Selesai" punya dua varian
     * tergantung apakah realisasi_persen sudah diisi atasan (sudah dinilai) atau belum.
     */
    $warnaStatus = function (string $status, ?string $realisasi) {
        return match ($status) {
            'pending' => 'bg-secondary',
            'proses' => 'bg-primary',
            'revisi' => 'bg-warning text-dark',
            'terlambat' => 'bg-danger',
            'selesai' => $realisasi === null ? 'bg-info text-dark' : 'bg-success',
            'ditolak' => 'bg-dark',
            default => 'bg-secondary',
        };
    };

    $labelStatus = fn (string $status, ?string $realisasi) => $status === 'selesai' && $realisasi === null
        ? 'Menunggu Nilai'
        : ucfirst($status);

    $statBoxes = [
        'pending' => ['Pending', 'text-secondary'],
        'proses' => ['Proses', 'text-primary'],
        'revisi' => ['Revisi', 'text-warning-emphasis'],
        'terlambat' => ['Terlambat', 'text-danger'],
        'selesai' => ['Selesai', 'text-success'],
    ];
@endphp

<!-- Statistik Ringkas -->
<div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-2 mb-3">
    <div class="col">
        <div class="border rounded-3 text-center py-2">
            <div class="fw-bold fs-6">{{ $stats['total'] }}</div>
            <div class="text-muted stat-label">Total</div>
        </div>
    </div>
    @foreach ($statBoxes as $key => [$label, $warna])
        <div class="col">
            <div class="border rounded-3 text-center py-2">
                <div class="fw-bold fs-6 {{ $warna }}">{{ $stats[$key] }}</div>
                <div class="text-muted stat-label">{{ $label }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Nama Tugas</th>
                <th>{{ $tab === 'diberikan' ? 'Pegawai' : 'Pemberi Tugas' }}</th>
                <th>Deadline</th>
                <th>Prioritas</th>
                <th class="text-center">Status</th>
                <th class="text-center">Progress</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($penugasan as $tugas)
                @php
                    $antrianPersetujuan = $tab === 'diberikan' && $tugas->is_mandiri && $tugas->status === 'pending';
                    // Tugas yang sudah Selesai selalu 100% progress (aturan E6) — pengaman tampilan untuk
                    // data lama, akar masalahnya sudah diperbaiki di PenugasanActionService::submit().
                    // Cast ke float supaya nol di belakang koma dari kolom decimal (mis. "70.00") tidak ikut tampil.
                    $progressTampil = $tugas->status === 'selesai' ? 100 : (float) $tugas->progress_persen;
                @endphp
                <tr class="{{ $antrianPersetujuan ? 'table-warning' : '' }}">
                    <td>
                        <div class="fw-semibold">{{ Str::limit($tugas->nama_tugas, 60) }}</div>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            <span class="badge bg-info bg-opacity-10 text-info">{{ $tugas->jenis === 'pokok' ? 'Tugas Pokok' : 'Tugas Tambahan' }}</span>
                            @if ($tugas->is_mandiri)
                                <span class="badge bg-secondary bg-opacity-25 text-body"><i class="bi bi-person-check me-1"></i>Mandiri</span>
                            @endif
                            @if ($tugas->mode_grup === 'kolektif')
                                <span class="badge bg-secondary bg-opacity-25 text-body">
                                    <i class="bi bi-people me-1"></i>Kolektif{{ $tugas->is_koordinator ? ' (Koordinator)' : '' }}
                                </span>
                            @elseif ($tugas->mode_grup === 'per_orang')
                                <span class="badge bg-secondary bg-opacity-25 text-body"><i class="bi bi-people me-1"></i>Per Orang</span>
                            @endif
                            @if ($antrianPersetujuan)
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Menunggu Keputusan Anda</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        {{ $tab === 'diberikan' ? ($tugas->pegawai->nama ?? '-') : ($tugas->pemberiTugas->nama ?? 'Mandiri') }}
                    </td>
                    <td>
                        <small>{{ optional($tugas->deadline_terbaru)->format('d M Y') }}</small>
                        @if ($tugas->deadline_terbaru && $tugas->tanggal_selesai && ! $tugas->deadline_terbaru->isSameDay($tugas->tanggal_selesai))
                            <br><span class="badge bg-warning bg-opacity-25 text-warning-emphasis">Diperbarui</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $prioritasBadgeMap[$tugas->prioritas] ?? 'bg-secondary' }}">
                            {{ ucfirst($tugas->prioritas) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $warnaStatus($tugas->status, $tugas->realisasi_persen) }}">
                            {{ $labelStatus($tugas->status, $tugas->realisasi_persen) }}
                        </span>
                    </td>
                    <td class="text-center" style="min-width: 100px;">
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $progressTampil }}%"
                                aria-valuenow="{{ $progressTampil }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">{{ $progressTampil }}%</small>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('penugasan.show', $tugas->id) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">Tidak ada penugasan</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center pt-3">
    {{ $penugasan->links() }}
</div>
