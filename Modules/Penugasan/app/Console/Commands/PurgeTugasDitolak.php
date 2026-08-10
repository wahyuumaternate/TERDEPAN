<?php

namespace Modules\Penugasan\Console\Commands;

use Illuminate\Console\Command;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Models\RiwayatPenolakan;

class PurgeTugasDitolak extends Command
{
    protected $signature = 'penugasan:purge-ditolak';

    protected $description = 'Hapus (soft delete) tugas berstatus Ditolak yang sudah melewati masa tenggang pembatalan';

    public function handle(): int
    {
        $batasWaktu = now()->subHours(Penugasan::MASA_TENGGANG_PENOLAKAN_JAM);

        $kandidat = Penugasan::where('status', Penugasan::STATUS_DITOLAK)
            ->where('is_mandiri', false)
            ->whereNotNull('ditolak_pada')
            ->where('ditolak_pada', '<=', $batasWaktu)
            ->get();

        $jumlahDihapus = 0;
        $grupSudahDiproses = [];

        foreach ($kandidat as $penugasan) {
            // Grup kolektif: cascade sebelumnya menyalin status Ditolak ke setiap anggota, jadi
            // satu grup bisa muncul sebagai beberapa baris kandidat sekaligus (koordinator +
            // anggota) — proses & catat riwayatnya sekali saja per grup supaya tidak dobel.
            $kunciGrup = $penugasan->grup_id ?? $penugasan->id;
            if (in_array($kunciGrup, $grupSudahDiproses, true)) {
                continue;
            }
            $grupSudahDiproses[] = $kunciGrup;

            if ($penugasan->mode_grup === Penugasan::MODE_GRUP_KOLEKTIF) {
                foreach ($penugasan->grupAnggota as $anggota) {
                    $anggota->delete();
                    $jumlahDihapus++;
                }
            }

            $penugasan->delete();
            $jumlahDihapus++;

            RiwayatPenolakan::whereJsonContains('penugasan_ids', $penugasan->id)
                ->whereNull('dibatalkan_pada')
                ->whereNull('dieksekusi_pada')
                ->latest('ditolak_pada')
                ->first()
                ?->update(['dieksekusi_pada' => now()]);
        }

        $this->info("Menghapus {$jumlahDihapus} tugas yang sudah melewati masa tenggang penolakan.");

        return self::SUCCESS;
    }
}
