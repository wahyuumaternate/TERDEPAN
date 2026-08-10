<?php

namespace Modules\Penugasan\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\TerminalData\Models\TdFile;

/**
 * Laporan bulanan eviden kinerja per pegawai — dibangun di atas pivot
 * knj_penugasan_eviden (lihat Penugasan::eviden()), bukan lagi TdFile.attachable_id.
 * Karena pivot memastikan satu file fisik = satu baris TdFile (tidak terduplikasi per
 * anggota grup kolektif seperti pola lama), query di sini otomatis tidak menghitung
 * ganda eviden yang sama meski dipakai bersama oleh beberapa anggota grup.
 */
class EvidenKinerjaReportService
{
    /**
     * Semua eviden (dokumen & foto) milik penugasan seorang pegawai dalam satu bulan,
     * diurutkan terbaru dulu.
     */
    public function evidenBulan(User $pegawai, int $tahun, int $bulan): Collection
    {
        return TdFile::query()
            ->whereIn('id', function ($query) use ($pegawai, $tahun, $bulan) {
                $query->select('td_file_id')
                    ->from('knj_penugasan_eviden')
                    ->join('knj_penugasan', 'knj_penugasan.id', '=', 'knj_penugasan_eviden.penugasan_id')
                    ->where('knj_penugasan.pegawai_id', $pegawai->id)
                    ->whereYear('knj_penugasan_eviden.created_at', $tahun)
                    ->whereMonth('knj_penugasan_eviden.created_at', $bulan);
            })
            ->with(['penugasan' => fn ($q) => $q->where('pegawai_id', $pegawai->id)])
            ->latest()
            ->get();
    }

    /**
     * Sama seperti evidenBulan(), disaring hanya file gambar — dipakai fitur generate
     * laporan bulanan pegawai yang butuh foto sebagai bukti visual.
     */
    public function fotoBulan(User $pegawai, int $tahun, int $bulan): Collection
    {
        return $this->evidenBulan($pegawai, $tahun, $bulan)
            ->whereIn('extension', TdFile::EXTENSI_GAMBAR)
            ->values();
    }
}
