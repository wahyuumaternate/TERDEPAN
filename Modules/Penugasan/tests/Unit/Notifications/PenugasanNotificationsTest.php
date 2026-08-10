<?php

namespace Modules\Penugasan\Tests\Unit\Notifications;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Notifications\PenugasanBaruNotification;
use Modules\Penugasan\Notifications\PenugasanDeadlineReminderNotification;
use Modules\Penugasan\Notifications\PenugasanDinilaiNotification;
use Modules\Penugasan\Notifications\PenugasanRevisiNotification;
use NotificationChannels\WebPush\WebPushChannel;
use Tests\TestCase;

class PenugasanNotificationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array<int, string>>
     */
    public static function notificationClasses(): array
    {
        return [
            'baru' => [PenugasanBaruNotification::class],
            'revisi' => [PenugasanRevisiNotification::class],
            'dinilai' => [PenugasanDinilaiNotification::class],
            'deadline reminder' => [PenugasanDeadlineReminderNotification::class],
        ];
    }

    /**
     * @dataProvider notificationClasses
     */
    public function test_notification_dikirim_lewat_webpush_dan_berisi_link_ke_penugasan(string $notificationClass): void
    {
        $pegawai = User::factory()->create();
        $penugasan = Penugasan::factory()->create(['pegawai_id' => $pegawai->id]);

        $notification = new $notificationClass($penugasan);

        $this->assertSame([WebPushChannel::class], $notification->via($pegawai));

        $message = $notification->toWebPush($pegawai, $notification)->toArray();

        $this->assertNotEmpty($message['title']);
        $this->assertNotEmpty($message['body']);
        $this->assertSame(route('penugasan.show', $penugasan->id), $message['data']['url']);
    }
}
