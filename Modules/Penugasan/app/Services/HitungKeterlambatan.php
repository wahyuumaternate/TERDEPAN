<?php

namespace Modules\Penugasan\Services;

use Carbon\Carbon;

/**
 * Menghitung persentase terlambat sesuai tabel 2.B docs/analysis/aturan-penugasan-&-penilaian.md,
 * berdasar selisih hari antara deadline terakhir/terbaru tugas dan tanggal diselesaikan.
 */
class HitungKeterlambatan
{
    public function persentase(Carbon $deadlineTerbaru, Carbon $tanggalDiselesaikan): int
    {
        $hari = (int) $deadlineTerbaru->startOfDay()->diffInDays($tanggalDiselesaikan->copy()->startOfDay(), false);

        return match (true) {
            $hari <= 0 => 0,
            $hari <= 3 => 5,
            $hari <= 7 => 10,
            $hari <= 14 => 15,
            default => 20,
        };
    }
}
