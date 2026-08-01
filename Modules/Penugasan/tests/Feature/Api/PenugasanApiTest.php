<?php

namespace Modules\Penugasan\Tests\Feature\Api;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Penugasan\Models\Penugasan;
use Tests\TestCase;

class PenugasanApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        MasterBidang::firstOrCreate(['id' => 1], ['nama' => 'Bidang Test', 'kode' => 'TEST']);

        foreach ([
            ['id' => 1, 'kode' => 'ADMIN', 'nama' => 'Admin', 'level' => 1],
            ['id' => 2, 'kode' => 'KABID', 'nama' => 'Kepala Bidang', 'level' => 4],
            ['id' => 3, 'kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5],
        ] as $jabatan) {
            MasterJabatan::firstOrCreate(['id' => $jabatan['id']], $jabatan);
        }
    }

    protected function createUserWithJabatan(string $kodeJabatan, ?User $atasan = null): User
    {
        $jabatan = MasterJabatan::where('kode', $kodeJabatan)->firstOrFail();
        $bidang = MasterBidang::firstOrFail();

        $user = User::create([
            'nama' => 'User '.$kodeJabatan.' '.uniqid(),
            'email' => strtolower($kodeJabatan).uniqid().'@test.com',
            'password' => bcrypt('password'),
        ]);

        $user->profile()->create([
            'nomor_identitas' => '1990'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT).'001',
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatan->id,
            'bidang_id' => $bidang->id,
            'atasan_langsung_id' => $atasan?->id,
        ]);

        return $user->fresh('profile');
    }

    protected function createPenugasan(User $pegawai, ?User $pemberi, array $overrides = []): Penugasan
    {
        return Penugasan::create(array_merge([
            'pegawai_id' => $pegawai->id,
            'pemberi_tugas_id' => $pemberi?->id,
            'jenis' => Penugasan::JENIS_TAMBAHAN,
            'nama_tugas' => 'Tugas Test',
            'deskripsi' => 'Deskripsi test',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'bobot_persen' => 20,
            'status' => Penugasan::STATUS_PENDING,
        ], $overrides));
    }

    public function test_unauthenticated_request_gets_401(): void
    {
        $this->getJson('/api/v1/penugasan')->assertStatus(401);
    }

    public function test_atasan_can_create_penugasan_for_bawahan(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);

        $response = $this->actingAs($atasan, 'sanctum')->postJson('/api/v1/penugasan', [
            'pegawai_id' => $bawahan->id,
            'jenis' => 'tambahan',
            'nama_tugas' => 'Menyusun laporan',
            'deskripsi' => 'Deskripsi laporan',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'bobot_persen' => 15,
        ]);

        $response->assertStatus(201)->assertJsonPath('status', true);
        $this->assertDatabaseHas('knj_penugasan', [
            'pegawai_id' => $bawahan->id,
            'pemberi_tugas_id' => $atasan->id,
            'jenis' => 'tambahan',
            'status' => 'pending',
        ]);
    }

    public function test_pelaksana_cannot_create_penugasan_for_others(): void
    {
        $pelaksana = $this->createUserWithJabatan('PELAKSANA');
        $lain = $this->createUserWithJabatan('PELAKSANA');

        $response = $this->actingAs($pelaksana, 'sanctum')->postJson('/api/v1/penugasan', [
            'pegawai_id' => $lain->id,
            'jenis' => 'tambahan',
            'nama_tugas' => 'Tugas ke orang lain',
            'deskripsi' => 'x',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'bobot_persen' => 10,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_create_mandiri_penugasan_for_self(): void
    {
        $pelaksana = $this->createUserWithJabatan('PELAKSANA');

        $response = $this->actingAs($pelaksana, 'sanctum')->postJson('/api/v1/penugasan', [
            'pegawai_id' => $pelaksana->id,
            'jenis' => 'pokok',
            'nama_tugas' => 'Tugas mandiri',
            'deskripsi' => 'x',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(30)->toDateString(),
            'bobot_persen' => 60,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('knj_penugasan', [
            'pegawai_id' => $pelaksana->id,
            'pemberi_tugas_id' => null,
            'status_approval' => 'pending',
        ]);
    }

    public function test_full_workflow_terima_submit_validasi_menghasilkan_nilai_akhir(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, ['bobot_persen' => 20]);

        $this->actingAs($bawahan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/terima")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'dikerjakan');

        // submit tanpa bukti pengerjaan harus ditolak
        $this->actingAs($bawahan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/submit")
            ->assertStatus(422);

        // Simulasikan bukti sudah ada dengan langsung set status validasi (attachedFiles di luar cakupan test ini)
        $tugas->update(['status' => Penugasan::STATUS_VALIDASI]);

        $response = $this->actingAs($atasan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/validasi", [
                'hasil_validasi' => 'diterima',
                'realisasi_persen' => 90,
                'catatan_validasi' => 'Bagus',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'selesai')
            ->assertJsonPath('data.realisasi_persen', '90.00');

        // nilai_akhir = bobot_persen(20) x realisasi_persen(90) / 100 = 18
        $this->assertEquals(18.0, (float) $tugas->fresh()->nilai_akhir);
    }

    public function test_pegawai_cannot_validate_own_task(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, ['status' => Penugasan::STATUS_VALIDASI]);

        $this->actingAs($bawahan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/validasi", [
                'hasil_validasi' => 'diterima',
                'realisasi_persen' => 100,
            ])
            ->assertStatus(403);
    }

    public function test_validasi_revisi_creates_histori_revisi(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, ['status' => Penugasan::STATUS_VALIDASI]);

        $response = $this->actingAs($atasan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/validasi", [
                'hasil_validasi' => 'revisi',
                'catatan_validasi' => 'Perbaiki bagian ini',
            ]);

        $response->assertStatus(200)->assertJsonPath('data.status', 'revisi');
        $this->assertDatabaseHas('knj_histori_revisi', [
            'penugasan_id' => $tugas->id,
            'revisi_ke' => 1,
        ]);
    }

    public function test_update_progress_records_entry_and_updates_progress_persen(): void
    {
        $bawahan = $this->createUserWithJabatan('PELAKSANA');
        $tugas = $this->createPenugasan($bawahan, null, ['status' => Penugasan::STATUS_DIKERJAKAN, 'pemberi_tugas_id' => null]);

        $response = $this->actingAs($bawahan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/progress", [
                'progress_persen' => 40,
                'deskripsi_kegiatan' => 'Mengumpulkan data awal',
            ]);

        $response->assertStatus(200)->assertJsonPath('data.progress_persen', '40.00');
        $this->assertDatabaseHas('knj_progress', [
            'penugasan_id' => $tugas->id,
            'pegawai_id' => $bawahan->id,
        ]);
    }

    public function test_crud_show_update_destroy(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan);

        $this->actingAs($atasan, 'sanctum')
            ->getJson("/api/v1/penugasan/{$tugas->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $tugas->id);

        $this->actingAs($atasan, 'sanctum')
            ->putJson("/api/v1/penugasan/{$tugas->id}", ['nama_tugas' => 'Nama Diupdate'])
            ->assertStatus(200)
            ->assertJsonPath('data.nama_tugas', 'Nama Diupdate');

        $this->actingAs($atasan, 'sanctum')
            ->deleteJson("/api/v1/penugasan/{$tugas->id}")
            ->assertStatus(200);

        $this->assertSoftDeleted('knj_penugasan', ['id' => $tugas->id]);
    }
}
