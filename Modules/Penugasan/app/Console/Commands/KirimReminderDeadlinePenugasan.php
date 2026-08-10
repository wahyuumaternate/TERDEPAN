<?php

namespace Modules\Penugasan\Console\Commands;

use Illuminate\Console\Command;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Notifications\PenugasanDeadlineReminderNotification;

class KirimReminderDeadlinePenugasan extends Command
{
    protected $signature = 'penugasan:kirim-reminder-deadline';

    protected $description = 'Kirim push notification reminder untuk tugas yang deadline-nya besok (H-1), sekali per tugas';

    public function handle(): int
    {
        $besok = now()->addDay()->toDateString();

        $kandidat = Penugasan::whereIn('status', [
            Penugasan::STATUS_PENDING,
            Penugasan::STATUS_PROSES,
            Penugasan::STATUS_REVISI,
            Penugasan::STATUS_TERLAMBAT,
        ])
            ->whereDate('deadline_terbaru', $besok)
            ->whereNull('deadline_reminder_sent_at')
            ->get();

        foreach ($kandidat as $penugasan) {
            $penugasan->pegawai->notify(new PenugasanDeadlineReminderNotification($penugasan));
            $penugasan->update(['deadline_reminder_sent_at' => now()]);
        }

        $this->info("Mengirim {$kandidat->count()} reminder deadline.");

        return self::SUCCESS;
    }
}
