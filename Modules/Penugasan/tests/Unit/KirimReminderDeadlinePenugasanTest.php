<?php

namespace Modules\Penugasan\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Notifications\PenugasanDeadlineReminderNotification;
use Tests\TestCase;

class KirimReminderDeadlinePenugasanTest extends TestCase
{
    use RefreshDatabase;

    public function test_mengirim_reminder_untuk_tugas_yang_deadline_besok_dan_belum_diingatkan(): void
    {
        Notification::fake();

        $pegawai = User::factory()->create();
        $penugasan = Penugasan::factory()->create([
            'pegawai_id' => $pegawai->id,
            'status' => Penugasan::STATUS_PROSES,
            'deadline_terbaru' => now()->addDay(),
            'deadline_reminder_sent_at' => null,
        ]);

        $this->artisan('penugasan:kirim-reminder-deadline')->assertSuccessful();

        Notification::assertSentTo($pegawai, PenugasanDeadlineReminderNotification::class);
        $this->assertNotNull($penugasan->fresh()->deadline_reminder_sent_at);
    }

    public function test_tidak_mengirim_ulang_untuk_tugas_yang_sudah_pernah_diingatkan(): void
    {
        Notification::fake();

        $pegawai = User::factory()->create();
        Penugasan::factory()->create([
            'pegawai_id' => $pegawai->id,
            'status' => Penugasan::STATUS_PROSES,
            'deadline_terbaru' => now()->addDay(),
            'deadline_reminder_sent_at' => now()->subHour(),
        ]);

        $this->artisan('penugasan:kirim-reminder-deadline')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_tidak_mengirim_untuk_tugas_yang_deadline_bukan_besok(): void
    {
        Notification::fake();

        $pegawai = User::factory()->create();
        Penugasan::factory()->create([
            'pegawai_id' => $pegawai->id,
            'status' => Penugasan::STATUS_PROSES,
            'deadline_terbaru' => now()->addDays(5),
            'deadline_reminder_sent_at' => null,
        ]);

        $this->artisan('penugasan:kirim-reminder-deadline')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_tidak_mengirim_untuk_tugas_yang_sudah_selesai(): void
    {
        Notification::fake();

        $pegawai = User::factory()->create();
        Penugasan::factory()->create([
            'pegawai_id' => $pegawai->id,
            'status' => Penugasan::STATUS_SELESAI,
            'deadline_terbaru' => now()->addDay(),
            'deadline_reminder_sent_at' => null,
        ]);

        $this->artisan('penugasan:kirim-reminder-deadline')->assertSuccessful();

        Notification::assertNothingSent();
    }
}
