<?php

namespace Modules\Penugasan\Services;

use App\Models\User;
use Carbon\Carbon;
use Modules\TerminalData\Models\TdFolder;

/**
 * Menentukan folder penyimpanan eviden kinerja secara otomatis, menggantikan
 * pemilihan folder manual oleh pegawai. Struktur: Eviden Kinerja > {Pegawai} > {Tahun} > {Bulan}.
 *
 * "Eviden Kinerja" sengaja dibuat sebagai folder level-atas yang TIDAK terikat bidang
 * (bidang_id selalu null di seluruh cabang ini) — supaya saat pegawai rotasi antar
 * bidang setiap tahun, riwayat evidennya tetap berada di satu pohon folder yang sama,
 * tidak terpecah mengikuti bidang tempat mereka bertugas saat upload dilakukan.
 */
class EvidenFolderResolver
{
    private const BULAN_INDONESIA = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function resolveForPegawai(User $pegawai, ?Carbon $tanggal = null): TdFolder
    {
        $tanggal ??= now();

        $root = TdFolder::firstOrCreate(
            ['parent_id' => null, 'name' => 'Eviden Kinerja'],
            ['is_system' => true, 'created_by' => $pegawai->id]
        );

        $folderPegawai = TdFolder::firstOrCreate(
            ['parent_id' => $root->id, 'name' => $pegawai->nama],
            ['is_system' => true, 'created_by' => $pegawai->id]
        );

        $folderTahun = TdFolder::firstOrCreate(
            ['parent_id' => $folderPegawai->id, 'name' => $tanggal->format('Y')],
            ['is_system' => true, 'created_by' => $pegawai->id]
        );

        return TdFolder::firstOrCreate(
            ['parent_id' => $folderTahun->id, 'name' => self::BULAN_INDONESIA[(int) $tanggal->format('n')]],
            ['is_system' => true, 'created_by' => $pegawai->id]
        );
    }
}
