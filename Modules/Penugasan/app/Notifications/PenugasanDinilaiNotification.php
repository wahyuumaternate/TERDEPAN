<?php

namespace Modules\Penugasan\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Penugasan\Models\Penugasan;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PenugasanDinilaiNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Penugasan $penugasan) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, self $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Tugas Sudah Dinilai')
            ->icon('/assets/img/logo.webp')
            ->body("Tugas \"{$this->penugasan->nama_tugas}\" sudah dinilai dengan nilai akhir {$this->penugasan->nilai_akhir}.")
            ->data(['url' => route('penugasan.show', $this->penugasan->id)]);
    }
}
