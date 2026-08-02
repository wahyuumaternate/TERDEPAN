<?php

namespace Modules\Penugasan\Console\Commands;

use Illuminate\Console\Command;
use Modules\Penugasan\Models\Penugasan;

class TandaiTugasTerlambat extends Command
{
    protected $signature = 'penugasan:tandai-terlambat';

    protected $description = 'Tandai tugas Proses/Revisi yang sudah melewati deadline_terbaru sebagai Terlambat (aturan E7)';

    public function handle(): int
    {
        $jumlah = Penugasan::whereIn('status', [Penugasan::STATUS_PROSES, Penugasan::STATUS_REVISI])
            ->whereDate('deadline_terbaru', '<', now()->toDateString())
            ->update(['status' => Penugasan::STATUS_TERLAMBAT]);

        $this->info("Menandai {$jumlah} tugas sebagai Terlambat.");

        return self::SUCCESS;
    }
}
