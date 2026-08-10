<?php

namespace Modules\Penugasan\Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Models\PerpanjanganWaktu;
use Modules\Penugasan\Services\NotifikasiService;
use Tests\TestCase;

class NotifikasiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $atasan;

    protected User $bawahan;

    protected MasterBidang $bidang;

    protected function setUp(): void
    {
        parent::setUp();

        MasterJabatan::insert([
            ['id' => 1, 'kode' => 'KABAN', 'nama' => 'Kepala Badan', 'level' => 1],
            ['id' => 2, 'kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5],
        ]);

        $this->bidang = MasterBidang::create(['kode' => 'APPS', 'nama' => 'Bidang Aplikasi']);

        $this->atasan = User::create([
            'nama' => 'Atasan Notif Test', 'email' => 'atasan-notif@test.com', 'password' => bcrypt('password'),
        ]);
        $this->atasan->profile()->create([
            'nomor_identitas' => '1990000000000101', 'tipe_identitas' => 'NIP', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif', 'jabatan_id' => 1, 'bidang_id' => $this->bidang->id,
        ]);

        $this->bawahan = User::create([
            'nama' => 'Bawahan Notif Test', 'email' => 'bawahan-notif@test.com', 'password' => bcrypt('password'),
        ]);
        $this->bawahan->profile()->create([
            'nomor_identitas' => '1990000000000102', 'tipe_identitas' => 'NIP', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif', 'jabatan_id' => 2, 'bidang_id' => $this->bidang->id,
            'atasan_langsung_id' => $this->atasan->id,
        ]);

        $this->atasan->refresh()->load('profile');
        $this->bawahan->refresh()->load('profile');
    }

    public function test_tidak_ada_notifikasi_saat_tidak_ada_yang_perlu_ditindaklanjuti(): void
    {
        $notifikasi = app(NotifikasiService::class)->untuk($this->bawahan);

        $this->assertTrue($notifikasi->isEmpty());
    }

    public function test_pegawai_melihat_notifikasi_tugas_baru_dan_terlambat(): void
    {
        Penugasan::factory()->create([
            'pegawai_id' => $this->bawahan->id,
            'pemberi_tugas_id' => $this->atasan->id,
            'is_mandiri' => false,
            'status' => Penugasan::STATUS_PENDING,
        ]);

        Penugasan::factory()->terlambat()->create([
            'pegawai_id' => $this->bawahan->id,
            'pemberi_tugas_id' => $this->atasan->id,
        ]);

        Penugasan::factory()->create([
            'pegawai_id' => $this->bawahan->id,
            'pemberi_tugas_id' => $this->atasan->id,
            'is_mandiri' => false,
            'status' => Penugasan::STATUS_DITOLAK,
            'ditolak_pada' => now(),
        ]);

        $notifikasi = app(NotifikasiService::class)->untuk($this->bawahan);

        $this->assertTrue($notifikasi->contains(fn ($n) => str_contains($n['pesan'], 'tugas baru')));
        $this->assertTrue($notifikasi->contains(fn ($n) => str_contains($n['pesan'], 'melewati deadline')));
        $this->assertTrue($notifikasi->contains(fn ($n) => str_contains($n['pesan'], 'ditolak — masih bisa dibatalkan')));
    }

    public function test_atasan_melihat_notifikasi_mandiri_menunggu_dan_perpanjangan_menunggu(): void
    {
        Penugasan::factory()->mandiri($this->atasan)->create([
            'pegawai_id' => $this->bawahan->id,
        ]);

        $tugasProses = Penugasan::factory()->proses()->create([
            'pegawai_id' => $this->bawahan->id,
            'pemberi_tugas_id' => $this->atasan->id,
        ]);
        PerpanjanganWaktu::factory()->create([
            'penugasan_id' => $tugasProses->id,
            'pegawai_id' => $this->bawahan->id,
            'status' => PerpanjanganWaktu::STATUS_MENUNGGU,
        ]);

        $notifikasi = app(NotifikasiService::class)->untuk($this->atasan);

        $this->assertTrue($notifikasi->contains(fn ($n) => str_contains($n['pesan'], 'menunggu persetujuan Anda')));
        $this->assertTrue($notifikasi->contains(fn ($n) => str_contains($n['pesan'], 'perpanjangan waktu menunggu keputusan Anda')));
    }

    public function test_halaman_notifikasi_dan_endpoint_json_bisa_diakses(): void
    {
        $this->actingAs($this->bawahan)->get(route('notifications.index'))->assertStatus(200);

        $this->actingAs($this->bawahan)
            ->getJson(route('penugasan.api.notifikasi'))
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data', 'count']);
    }
}
