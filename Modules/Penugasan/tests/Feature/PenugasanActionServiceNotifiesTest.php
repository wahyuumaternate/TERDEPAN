<?php

namespace Modules\Penugasan\Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Notifications\PenugasanBaruNotification;
use Modules\Penugasan\Notifications\PenugasanDinilaiNotification;
use Modules\Penugasan\Notifications\PenugasanRevisiNotification;
use Modules\Penugasan\Services\PenugasanActionService;
use Tests\TestCase;

class PenugasanActionServiceNotifiesTest extends TestCase
{
    use RefreshDatabase;

    protected User $atasan;

    protected User $bawahan;

    protected function setUp(): void
    {
        parent::setUp();

        $bidang = MasterBidang::create(['kode' => 'APPS', 'nama' => 'Bidang Aplikasi']);
        $jabatanAdmin = MasterJabatan::create(['kode' => 'ADMIN', 'nama' => 'Admin', 'level' => 1]);
        $jabatanPelaksana = MasterJabatan::create(['kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5]);

        $this->atasan = User::create(['nama' => 'Atasan', 'email' => 'atasan@test.com', 'password' => bcrypt('password')]);
        $this->atasan->profile()->create([
            'nomor_identitas' => '1', 'tipe_identitas' => 'NIP', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatanAdmin->id, 'bidang_id' => $bidang->id,
        ]);

        $this->bawahan = User::create(['nama' => 'Bawahan', 'email' => 'bawahan@test.com', 'password' => bcrypt('password')]);
        $this->bawahan->profile()->create([
            'nomor_identitas' => '2', 'tipe_identitas' => 'NIP', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatanPelaksana->id, 'bidang_id' => $bidang->id,
            'atasan_langsung_id' => $this->atasan->id,
        ]);
    }

    public function test_buat_mengirim_notifikasi_ke_pegawai_yang_ditugaskan(): void
    {
        Notification::fake();

        $service = app(PenugasanActionService::class);

        $penugasan = $service->buat([
            'pegawai_id' => $this->bawahan->id,
            'jenis' => Penugasan::JENIS_TAMBAHAN,
            'prioritas' => Penugasan::PRIORITAS_SEDANG,
            'nama_tugas' => 'Tugas Uji',
            'deskripsi' => 'Deskripsi tugas uji',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
        ], $this->atasan);

        Notification::assertSentTo(
            $this->bawahan,
            PenugasanBaruNotification::class,
            fn ($notification) => $notification->toWebPush($this->bawahan, $notification)->toArray()['data']['url'] === route('penugasan.show', $penugasan->id)
        );
    }

    public function test_buat_tugas_mandiri_tidak_mengirim_notifikasi_ke_pembuat(): void
    {
        Notification::fake();

        $kaban = User::create(['nama' => 'Kaban', 'email' => 'kaban@test.com', 'password' => bcrypt('password')]);
        $kaban->profile()->create([
            'nomor_identitas' => '3', 'tipe_identitas' => 'NIP', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif',
            'jabatan_id' => MasterJabatan::create(['kode' => 'KABAN', 'nama' => 'Kepala Badan', 'level' => 1])->id,
            'bidang_id' => $this->bawahan->profile->bidang_id,
        ]);

        $service = app(PenugasanActionService::class);

        $service->buat([
            'pegawai_id' => $this->bawahan->id,
            'atasan_id' => $kaban->id,
            'jenis' => Penugasan::JENIS_TAMBAHAN,
            'prioritas' => Penugasan::PRIORITAS_SEDANG,
            'nama_tugas' => 'Tugas Mandiri Uji',
            'deskripsi' => 'Deskripsi tugas uji',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
        ], $this->bawahan);

        Notification::assertNothingSent();
    }

    public function test_nilai_mengirim_notifikasi_ke_pegawai(): void
    {
        Notification::fake();

        $penugasan = Penugasan::factory()->create([
            'pegawai_id' => $this->bawahan->id,
            'pemberi_tugas_id' => $this->atasan->id,
            'status' => Penugasan::STATUS_SELESAI,
            'realisasi_persen' => null,
            'bobot_persen' => 20,
        ]);

        $service = app(PenugasanActionService::class);
        $service->nilai($penugasan, ['realisasi_persen' => 80], $this->atasan);

        Notification::assertSentTo($this->bawahan, PenugasanDinilaiNotification::class);
    }

    public function test_revisi_mengirim_notifikasi_ke_pegawai(): void
    {
        Notification::fake();

        $penugasan = Penugasan::factory()->create([
            'pegawai_id' => $this->bawahan->id,
            'pemberi_tugas_id' => $this->atasan->id,
            'status' => Penugasan::STATUS_SELESAI,
            'realisasi_persen' => null,
            'bobot_persen' => 20,
        ]);

        $service = app(PenugasanActionService::class);
        $service->revisi($penugasan, [
            'catatan_revisi' => 'Perbaiki lampiran',
            'deadline_baru' => now()->addDays(3)->toDateString(),
        ], $this->atasan);

        Notification::assertSentTo($this->bawahan, PenugasanRevisiNotification::class);
    }
}
