<?php

namespace Modules\Penugasan\Helpers;

use Carbon\Carbon;
use Modules\Penugasan\Models\TugasHarian;
use Modules\Penugasan\Models\TugasTambahan;

class PenilaianHelper
{
    /**
     * Bobot penilaian
     */
    const BOBOT_WAKTU = 80; // 80%
    const BOBOT_KUALITAS = 20; // 20%

    /**
     * Hitung nilai waktu berdasarkan keterlambatan
     * 
     * Kategori:
     * - Tepat waktu atau lebih cepat: 100
     * - Terlambat 1 hari: -10 (90)
     * - Terlambat 2 hari: -20 (80)
     * - Terlambat 3 hari: -30 (70)
     * - Terlambat 4+ hari: -50 (50)
     * 
     * @param Carbon $tanggalDeadline
     * @param Carbon $tanggalSelesai
     * @return array ['nilai' => float, 'keterlambatan_hari' => int, 'status' => string]
     */
    public static function hitungNilaiWaktu(Carbon $tanggalDeadline, Carbon $tanggalSelesai): array
    {
        // Hitung selisih hari (positif = terlambat, negatif = lebih cepat)
        $keterlambatan = $tanggalSelesai->startOfDay()->diffInDays($tanggalDeadline->startOfDay(), false);

        // Jika negatif (submit lebih lambat dari deadline), ubah jadi positif
        $keterlambatan = $keterlambatan < 0 ? abs($keterlambatan) : 0;

        // Tentukan nilai berdasarkan keterlambatan
        $nilai = match (true) {
            $keterlambatan === 0 => 100,  // Tepat waktu
            $keterlambatan === 1 => 90,   // -10
            $keterlambatan === 2 => 80,   // -20
            $keterlambatan === 3 => 70,   // -30
            $keterlambatan >= 4 => 50,    // -50
            default => 100
        };

        // Status text
        $status = match (true) {
            $keterlambatan === 0 => 'Tepat Waktu',
            $keterlambatan === 1 => 'Terlambat 1 Hari (-10 poin)',
            $keterlambatan === 2 => 'Terlambat 2 Hari (-20 poin)',
            $keterlambatan === 3 => 'Terlambat 3 Hari (-30 poin)',
            $keterlambatan >= 4 => "Terlambat {$keterlambatan} Hari (-50 poin)",
            default => 'Unknown'
        };

        return [
            'nilai' => $nilai,
            'keterlambatan_hari' => $keterlambatan,
            'status' => $status,
            'badge_class' => self::getBadgeKeterlambatan($keterlambatan),
        ];
    }

    /**
     * Hitung nilai akhir dengan bobot waktu 80% dan kualitas 20%
     * 
     * @param float $nilaiWaktu (0-100)
     * @param float $nilaiKualitas (0-100)
     * @return float
     */
    public static function hitungNilaiAkhir(float $nilaiWaktu, float $nilaiKualitas): float
    {
        // Validasi input
        $nilaiWaktu = max(0, min(100, $nilaiWaktu));
        $nilaiKualitas = max(0, min(100, $nilaiKualitas));

        // Hitung dengan bobot
        $kontribusiWaktu = ($nilaiWaktu * self::BOBOT_WAKTU) / 100;
        $kontribusiKualitas = ($nilaiKualitas * self::BOBOT_KUALITAS) / 100;

        $nilaiAkhir = $kontribusiWaktu + $kontribusiKualitas;

        return round($nilaiAkhir, 2);
    }

    /**
     * Get breakdown detail penilaian
     * 
     * @param float $nilaiWaktu
     * @param float $nilaiKualitas
     * @return array
     */
    public static function getBreakdownPenilaian(float $nilaiWaktu, float $nilaiKualitas): array
    {
        $kontribusiWaktu = ($nilaiWaktu * self::BOBOT_WAKTU) / 100;
        $kontribusiKualitas = ($nilaiKualitas * self::BOBOT_KUALITAS) / 100;
        $nilaiAkhir = $kontribusiWaktu + $kontribusiKualitas;

        return [
            'nilai_waktu' => round($nilaiWaktu, 2),
            'bobot_waktu_persen' => self::BOBOT_WAKTU,
            'kontribusi_waktu' => round($kontribusiWaktu, 2),

            'nilai_kualitas' => round($nilaiKualitas, 2),
            'bobot_kualitas_persen' => self::BOBOT_KUALITAS,
            'kontribusi_kualitas' => round($kontribusiKualitas, 2),

            'nilai_akhir' => round($nilaiAkhir, 2),
        ];
    }

    /**
     * Get badge CSS class berdasarkan keterlambatan
     * 
     * @param int $keterlambatanHari
     * @return string
     */
    public static function getBadgeKeterlambatan(int $keterlambatanHari): string
    {
        return match (true) {
            $keterlambatanHari === 0 => 'bg-success',
            $keterlambatanHari <= 2 => 'bg-warning',
            default => 'bg-danger'
        };
    }

    /**
     * Get grade/kategori berdasarkan nilai akhir
     * 
     * @param float $nilaiAkhir
     * @return array ['grade' => string, 'kategori' => string, 'badge_class' => string]
     */
    public static function getGrade(float $nilaiAkhir): array
    {
        return match (true) {
            $nilaiAkhir >= 90 => [
                'grade' => 'A',
                'kategori' => 'Sangat Baik',
                'badge_class' => 'bg-success'
            ],
            $nilaiAkhir >= 80 => [
                'grade' => 'B',
                'kategori' => 'Baik',
                'badge_class' => 'bg-primary'
            ],
            $nilaiAkhir >= 70 => [
                'grade' => 'C',
                'kategori' => 'Cukup',
                'badge_class' => 'bg-info'
            ],
            $nilaiAkhir >= 60 => [
                'grade' => 'D',
                'kategori' => 'Kurang',
                'badge_class' => 'bg-warning'
            ],
            default => [
                'grade' => 'E',
                'kategori' => 'Sangat Kurang',
                'badge_class' => 'bg-danger'
            ]
        };
    }

    /**
     * Hitung dan simpan penilaian untuk tugas
     * 
     * @param TugasHarian|TugasTambahan $tugas
     * @param float $nilaiKualitas
     * @param Carbon|null $tanggalSelesai
     * @return array
     */
    public static function hitungDanSimpanPenilaian($tugas, float $nilaiKualitas, ?Carbon $tanggalSelesai = null): array
    {
        // Gunakan tanggal sekarang jika tidak diberikan
        $tanggalSelesai = $tanggalSelesai ?? now();

        // Hitung nilai waktu
        $dataWaktu = self::hitungNilaiWaktu(
            Carbon::parse($tugas->tanggal_selesai),
            $tanggalSelesai
        );

        // Hitung nilai akhir
        $nilaiAkhir = self::hitungNilaiAkhir($dataWaktu['nilai'], $nilaiKualitas);

        // Get breakdown
        $breakdown = self::getBreakdownPenilaian($dataWaktu['nilai'], $nilaiKualitas);

        // Get grade
        $grade = self::getGrade($nilaiAkhir);

        // Update tugas
        $tugas->update([
            'penilaian_kualitas' => round($nilaiKualitas, 2),
            'nilai_akhir' => $nilaiAkhir,
        ]);

        return [
            'waktu' => $dataWaktu,
            'breakdown' => $breakdown,
            'grade' => $grade,
            'nilai_akhir' => $nilaiAkhir,
        ];
    }

    /**
     * Get info penilaian lengkap untuk ditampilkan
     * 
     * @param TugasHarian|TugasTambahan $tugas
     * @return array|null
     */
    public static function getInfoPenilaian($tugas): ?array
    {
        if ($tugas->status !== 'selesai' || !$tugas->nilai_akhir) {
            return null;
        }

        // Hitung ulang nilai waktu berdasarkan deadline dan validated_at
        $tanggalSelesai = $tugas->validated_at ?? now();
        $dataWaktu = self::hitungNilaiWaktu(
            Carbon::parse($tugas->tanggal_selesai),
            $tanggalSelesai
        );

        $nilaiKualitas = $tugas->penilaian_kualitas ?? 0;
        $breakdown = self::getBreakdownPenilaian($dataWaktu['nilai'], $nilaiKualitas);
        $grade = self::getGrade($tugas->nilai_akhir);

        return [
            'tanggal_deadline' => Carbon::parse($tugas->tanggal_selesai)->format('d/m/Y'),
            'tanggal_selesai' => $tanggalSelesai->format('d/m/Y H:i'),
            'keterlambatan' => $dataWaktu,
            'breakdown' => $breakdown,
            'grade' => $grade,
        ];
    }

    /**
     * Preview penilaian sebelum disimpan (untuk modal validasi)
     * 
     * @param Carbon $tanggalDeadline
     * @param float $nilaiKualitas
     * @param Carbon|null $tanggalSelesai
     * @return array
     */
    public static function previewPenilaian(Carbon $tanggalDeadline, float $nilaiKualitas, ?Carbon $tanggalSelesai = null): array
    {
        $tanggalSelesai = $tanggalSelesai ?? now();

        $dataWaktu = self::hitungNilaiWaktu($tanggalDeadline, $tanggalSelesai);
        $nilaiAkhir = self::hitungNilaiAkhir($dataWaktu['nilai'], $nilaiKualitas);
        $breakdown = self::getBreakdownPenilaian($dataWaktu['nilai'], $nilaiKualitas);
        $grade = self::getGrade($nilaiAkhir);

        return [
            'keterlambatan' => $dataWaktu,
            'breakdown' => $breakdown,
            'grade' => $grade,
            'nilai_akhir' => $nilaiAkhir,
        ];
    }
}
