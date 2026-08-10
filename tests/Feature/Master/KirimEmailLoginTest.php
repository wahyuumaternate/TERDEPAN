<?php

namespace Tests\Feature\Master;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use App\Notifications\ResetPasswordNotification as ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class KirimEmailLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_kirim_email_login_ke_pegawai_terpilih(): void
    {
        Notification::fake();

        MasterJabatan::create(['id' => 1, 'kode' => 'ADMIN', 'nama' => 'Admin', 'level' => 1]);
        $bidang = MasterBidang::create(['kode' => 'SEKRE', 'nama' => 'Sekretariat']);

        $admin = User::factory()->create();
        $admin->profile()->create([
            'nomor_identitas' => 'ADMIN001', 'tipe_identitas' => 'ID', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif', 'jabatan_id' => 1,
            'bidang_id' => $bidang->id,
        ]);

        $pegawai1 = User::factory()->mustChangePassword()->create();
        $pegawai2 = User::factory()->mustChangePassword()->create();
        $pegawaiLain = User::factory()->create();

        $response = $this->actingAs($admin->fresh())->post(route('master.pegawai.kirim-email-login'), [
            'user_ids' => [$pegawai1->id, $pegawai2->id],
        ]);

        $response->assertRedirect(route('master.pegawai.index'));

        Notification::assertSentTo($pegawai1, ResetPassword::class);
        Notification::assertSentTo($pegawai2, ResetPassword::class);
        Notification::assertNotSentTo($pegawaiLain, ResetPassword::class);
    }
}
