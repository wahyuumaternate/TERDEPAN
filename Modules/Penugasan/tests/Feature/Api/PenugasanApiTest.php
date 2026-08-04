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

    protected MasterBidang $bidangUtama;

    protected MasterBidang $bidangSekretariat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bidangUtama = MasterBidang::firstOrCreate(['id' => 1], ['nama' => 'Bidang Ekonomi', 'kode' => 'EKONOMI']);
        $this->bidangSekretariat = MasterBidang::firstOrCreate(['id' => 2], ['nama' => 'Sekretariat', 'kode' => 'SEKRETARIAT']);

        foreach ([
            ['id' => 1, 'kode' => 'ADMIN', 'nama' => 'Admin', 'level' => 1],
            ['id' => 2, 'kode' => 'KABAN', 'nama' => 'Kepala Badan', 'level' => 1],
            ['id' => 3, 'kode' => 'SEKBAN', 'nama' => 'Sekretaris Badan', 'level' => 2],
            ['id' => 4, 'kode' => 'KABID', 'nama' => 'Kepala Bidang', 'level' => 3],
            ['id' => 5, 'kode' => 'KASUBAG', 'nama' => 'Kepala Sub Bagian', 'level' => 3],
            ['id' => 6, 'kode' => 'JAFUNG', 'nama' => 'Pejabat Fungsional', 'level' => 4],
            ['id' => 7, 'kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5],
            ['id' => 8, 'kode' => 'GATEK', 'nama' => 'Tenaga Teknis', 'level' => 6],
        ] as $jabatan) {
            MasterJabatan::firstOrCreate(['id' => $jabatan['id']], $jabatan);
        }
    }

    protected function createUserWithJabatan(string $kodeJabatan, ?User $atasan = null, ?MasterBidang $bidang = null): User
    {
        $jabatan = MasterJabatan::where('kode', $kodeJabatan)->firstOrFail();
        $bidang ??= $this->bidangUtama;

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
            'is_mandiri' => $pemberi === null,
            'jenis' => Penugasan::JENIS_TAMBAHAN,
            'prioritas' => Penugasan::PRIORITAS_SEDANG,
            'nama_tugas' => 'Tugas Test',
            'deskripsi' => 'Deskripsi test',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'deadline_terbaru' => now()->addDays(7),
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
            'prioritas' => 'tinggi',
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
            'prioritas' => 'tinggi',
            'status' => 'pending',
        ]);
    }

    public function test_store_without_prioritas_fails(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);

        $this->actingAs($atasan, 'sanctum')->postJson('/api/v1/penugasan', [
            'pegawai_id' => $bawahan->id,
            'jenis' => 'tambahan',
            'nama_tugas' => 'Tanpa prioritas',
            'deskripsi' => 'x',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'bobot_persen' => 15,
        ])->assertStatus(422);
    }

    public function test_pelaksana_cannot_create_penugasan_for_others(): void
    {
        $pelaksana = $this->createUserWithJabatan('PELAKSANA');
        $lain = $this->createUserWithJabatan('PELAKSANA');

        $response = $this->actingAs($pelaksana, 'sanctum')->postJson('/api/v1/penugasan', [
            'pegawai_id' => $lain->id,
            'jenis' => 'tambahan',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Tugas ke orang lain',
            'deskripsi' => 'x',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'bobot_persen' => 10,
        ]);

        $response->assertStatus(403);
    }

    public function test_full_workflow_terima_submit_nilai_menghasilkan_nilai_akhir_tanpa_keterlambatan(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, ['bobot_persen' => 20]);

        $this->actingAs($bawahan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/terima")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'proses');

        // submit tanpa bukti pengerjaan harus ditolak
        $this->actingAs($bawahan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/submit")
            ->assertStatus(422);

        // Simulasikan bukti sudah ada dengan langsung set status selesai (attachedFiles di luar cakupan test ini)
        $tugas->update(['status' => Penugasan::STATUS_SELESAI, 'tanggal_diselesaikan' => now()]);

        $response = $this->actingAs($atasan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/nilai", [
                'realisasi_persen' => 90,
                'catatan_validasi' => 'Bagus',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'selesai')
            ->assertJsonPath('data.realisasi_persen', '90.00')
            ->assertJsonPath('data.persentase_terlambat', '0.00');

        // nilai_awal = bobot_persen(20) x realisasi_persen(90) / 100 = 18, tanpa keterlambatan nilai_akhir sama
        $tugas->refresh();
        $this->assertEquals(18.0, (float) $tugas->nilai_awal);
        $this->assertEquals(18.0, (float) $tugas->nilai_akhir);
    }

    public function test_bobot_persen_boleh_ditunda_sampai_saat_nilai(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);

        $store = $this->actingAs($atasan, 'sanctum')->postJson('/api/v1/penugasan', [
            'pegawai_id' => $bawahan->id,
            'jenis' => 'tambahan',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Bobot ditunda',
            'deskripsi' => 'x',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
        ]);

        $store->assertStatus(201);
        $this->assertDatabaseHas('knj_penugasan', ['id' => $store->json('data.id'), 'bobot_persen' => null]);

        $tugas = Penugasan::findOrFail($store->json('data.id'));
        $tugas->update(['status' => Penugasan::STATUS_SELESAI, 'tanggal_diselesaikan' => now()]);

        $this->actingAs($atasan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/nilai", ['realisasi_persen' => 90])
            ->assertStatus(422);

        $response = $this->actingAs($atasan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/nilai", ['realisasi_persen' => 90, 'bobot_persen' => 50]);

        $response->assertStatus(200);
        $this->assertEquals(45.0, (float) $tugas->fresh()->nilai_akhir);
    }

    public function test_nilai_menghitung_potongan_keterlambatan(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, [
            'bobot_persen' => 80,
            'status' => Penugasan::STATUS_SELESAI,
            'deadline_terbaru' => now()->subDays(5),
            'tanggal_diselesaikan' => now(),
        ]);

        $response = $this->actingAs($atasan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/nilai", ['realisasi_persen' => 90]);

        // terlambat 5 hari -> 10%. nilai_awal = 0.8 x 90 = 72. nilai_akhir = 72 x 0.9 = 64.8
        $response->assertStatus(200)
            ->assertJsonPath('data.persentase_terlambat', '10.00');

        $this->assertEquals(64.8, (float) $tugas->fresh()->nilai_akhir);
    }

    public function test_nilai_tidak_bisa_dipanggil_dua_kali(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, [
            'status' => Penugasan::STATUS_SELESAI,
            'tanggal_diselesaikan' => now(),
        ]);

        $this->actingAs($atasan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/nilai", ['realisasi_persen' => 90])
            ->assertStatus(200);

        $this->actingAs($atasan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/nilai", ['realisasi_persen' => 80])
            ->assertStatus(403);
    }

    public function test_pegawai_cannot_nilai_own_task(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, ['status' => Penugasan::STATUS_SELESAI, 'tanggal_diselesaikan' => now()]);

        $this->actingAs($bawahan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/nilai", ['realisasi_persen' => 100])
            ->assertStatus(403);
    }

    public function test_revisi_pasca_selesai_sebelum_dinilai_lalu_kembali_ke_selesai(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, ['status' => Penugasan::STATUS_SELESAI, 'tanggal_diselesaikan' => now()]);

        $deadlineBaru = now()->addDays(3)->toDateString();

        $response = $this->actingAs($atasan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/revisi", [
                'catatan_revisi' => 'Perbaiki lampiran',
                'deadline_baru' => $deadlineBaru,
            ]);

        $response->assertStatus(200)->assertJsonPath('data.status', 'revisi');
        $this->assertDatabaseHas('knj_histori_revisi', ['penugasan_id' => $tugas->id, 'revisi_ke' => 1]);
        $this->assertEquals($deadlineBaru, $tugas->fresh()->deadline_terbaru->toDateString());
    }

    public function test_revisi_ditolak_jika_sudah_dinilai(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, [
            'status' => Penugasan::STATUS_SELESAI,
            'tanggal_diselesaikan' => now(),
            'realisasi_persen' => 90,
        ]);

        $this->actingAs($atasan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/revisi", [
                'catatan_revisi' => 'x',
                'deadline_baru' => now()->addDays(3)->toDateString(),
            ])
            ->assertStatus(403);
    }

    public function test_update_progress_records_entry_and_updates_progress_persen(): void
    {
        $bawahan = $this->createUserWithJabatan('PELAKSANA');
        $tugas = $this->createPenugasan($bawahan, null, ['status' => Penugasan::STATUS_PROSES, 'pemberi_tugas_id' => null, 'is_mandiri' => true]);

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

    public function test_prioritas_bisa_diubah_saat_pending_maupun_proses(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, ['status' => Penugasan::STATUS_PROSES]);

        $this->actingAs($atasan, 'sanctum')
            ->putJson("/api/v1/penugasan/{$tugas->id}", ['prioritas' => 'tinggi'])
            ->assertStatus(200)
            ->assertJsonPath('data.prioritas', 'tinggi');

        $tugas->update(['status' => Penugasan::STATUS_SELESAI]);

        $this->actingAs($atasan, 'sanctum')
            ->putJson("/api/v1/penugasan/{$tugas->id}", ['prioritas' => 'rendah'])
            ->assertStatus(403);
    }

    // --- Tugas Mandiri (Fase 2) ---

    public function test_atasan_mandiri_endpoint_returns_kandidat_sesuai_jabatan(): void
    {
        $kabid = $this->createUserWithJabatan('KABID');
        $kaban = $this->createUserWithJabatan('KABAN');
        $pelaksana = $this->createUserWithJabatan('PELAKSANA', $kabid, $this->bidangUtama);
        $jafung = $this->createUserWithJabatan('JAFUNG', bidang: $this->bidangUtama);

        $response = $this->actingAs($pelaksana, 'sanctum')->getJson('/api/v1/penugasan/atasan-mandiri');

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($jafung->id));
        $this->assertTrue($ids->contains($kabid->id));
        $this->assertTrue($ids->contains($kaban->id));
    }

    public function test_kaban_tidak_punya_kandidat_atasan_mandiri(): void
    {
        $kaban = $this->createUserWithJabatan('KABAN');

        $response = $this->actingAs($kaban, 'sanctum')->getJson('/api/v1/penugasan/atasan-mandiri');

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_pegawai_can_create_mandiri_menunggu_approval_atasan_terpilih(): void
    {
        $kabid = $this->createUserWithJabatan('KABID');
        $pelaksana = $this->createUserWithJabatan('PELAKSANA', $kabid);

        $response = $this->actingAs($pelaksana, 'sanctum')->postJson('/api/v1/penugasan', [
            'pegawai_id' => $pelaksana->id,
            'atasan_id' => $kabid->id,
            'jenis' => 'pokok',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Tugas mandiri',
            'deskripsi' => 'x',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(30)->toDateString(),
            'bobot_persen' => 60,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('knj_penugasan', [
            'pegawai_id' => $pelaksana->id,
            'pemberi_tugas_id' => $kabid->id,
            'is_mandiri' => true,
            'status' => 'pending',
            'status_approval' => 'pending',
        ]);
    }

    public function test_pegawai_tidak_bisa_pilih_atasan_di_luar_kandidat(): void
    {
        $bukanKandidat = $this->createUserWithJabatan('PELAKSANA');
        $pelaksana = $this->createUserWithJabatan('PELAKSANA');

        $this->actingAs($pelaksana, 'sanctum')->postJson('/api/v1/penugasan', [
            'pegawai_id' => $pelaksana->id,
            'atasan_id' => $bukanKandidat->id,
            'jenis' => 'pokok',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Tugas mandiri',
            'deskripsi' => 'x',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(30)->toDateString(),
            'bobot_persen' => 60,
        ])->assertStatus(422);
    }

    public function test_pegawai_tidak_bisa_terima_tugas_mandiri_sendiri(): void
    {
        $kabid = $this->createUserWithJabatan('KABID');
        $pelaksana = $this->createUserWithJabatan('PELAKSANA', $kabid);
        $tugas = $this->createPenugasan($pelaksana, $kabid, [
            'is_mandiri' => true,
            'status_approval' => Penugasan::APPROVAL_PENDING,
        ]);

        $this->actingAs($pelaksana, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/terima")
            ->assertStatus(403);
    }

    public function test_atasan_terpilih_approve_mandiri(): void
    {
        $kabid = $this->createUserWithJabatan('KABID');
        $pelaksana = $this->createUserWithJabatan('PELAKSANA', $kabid);
        $tugas = $this->createPenugasan($pelaksana, $kabid, [
            'is_mandiri' => true,
            'prioritas' => Penugasan::PRIORITAS_SEDANG,
            'status_approval' => Penugasan::APPROVAL_PENDING,
        ]);

        $response = $this->actingAs($kabid, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/approve-mandiri", ['prioritas' => 'tinggi']);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'proses')
            ->assertJsonPath('data.prioritas', 'tinggi');
    }

    public function test_atasan_terpilih_reject_mandiri_lalu_pegawai_edit_dan_ajukan_ulang(): void
    {
        $kabid = $this->createUserWithJabatan('KABID');
        $pelaksana = $this->createUserWithJabatan('PELAKSANA', $kabid);
        $tugas = $this->createPenugasan($pelaksana, $kabid, [
            'is_mandiri' => true,
            'status_approval' => Penugasan::APPROVAL_PENDING,
        ]);

        $this->actingAs($kabid, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/reject-mandiri", ['alasan_reject' => 'Kurang jelas'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'ditolak');

        $response = $this->actingAs($pelaksana, 'sanctum')
            ->putJson("/api/v1/penugasan/{$tugas->id}", ['nama_tugas' => 'Tugas mandiri (revisi)']);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.nama_tugas', 'Tugas mandiri (revisi)');

        $this->assertDatabaseHas('knj_penugasan', ['id' => $tugas->id, 'status' => 'pending', 'status_approval' => 'pending', 'alasan_reject' => null]);
    }

    public function test_atasan_lain_tidak_bisa_approve_mandiri(): void
    {
        $kabid = $this->createUserWithJabatan('KABID');
        $kabidLain = $this->createUserWithJabatan('KABID');
        $pelaksana = $this->createUserWithJabatan('PELAKSANA', $kabid);
        $tugas = $this->createPenugasan($pelaksana, $kabid, [
            'is_mandiri' => true,
            'status_approval' => Penugasan::APPROVAL_PENDING,
        ]);

        $this->actingAs($kabidLain, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/approve-mandiri")
            ->assertStatus(403);
    }

    // --- Perpanjangan Waktu (Fase 4) ---

    public function test_perpanjangan_waktu_full_flow_dengan_persetujuan_atasan(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, ['status' => Penugasan::STATUS_PROSES]);

        $ajukan = $this->actingAs($bawahan, 'sanctum')->postJson("/api/v1/penugasan/{$tugas->id}/perpanjangan-waktu", [
            'deadline_diminta' => now()->addDays(14)->toDateString(),
            'alasan_pengajuan' => 'Menunggu data dari bidang lain',
        ]);

        $ajukan->assertStatus(201)->assertJsonPath('data.status', 'menunggu');
        $this->assertEquals(now()->addDays(7)->toDateString(), $tugas->fresh()->deadline_terbaru->toDateString());

        $perpanjanganId = $ajukan->json('data.id');
        $deadlineDisetujui = now()->addDays(10)->toDateString();

        $setujui = $this->actingAs($atasan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/perpanjangan-waktu/{$perpanjanganId}/setujui", [
                'deadline_disetujui' => $deadlineDisetujui,
            ]);

        $setujui->assertStatus(200)->assertJsonPath('data.status', 'disetujui');
        $this->assertEquals($deadlineDisetujui, $tugas->fresh()->deadline_terbaru->toDateString());
    }

    public function test_perpanjangan_waktu_ditolak_tidak_mengubah_deadline_dan_tidak_memotong_kuota(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, ['status' => Penugasan::STATUS_PROSES]);

        $ajukan = $this->actingAs($bawahan, 'sanctum')->postJson("/api/v1/penugasan/{$tugas->id}/perpanjangan-waktu", [
            'deadline_diminta' => now()->addDays(14)->toDateString(),
            'alasan_pengajuan' => 'x',
        ]);

        $this->actingAs($atasan, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/perpanjangan-waktu/{$ajukan->json('data.id')}/tolak")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'ditolak');

        $this->assertEquals(now()->addDays(7)->toDateString(), $tugas->fresh()->deadline_terbaru->toDateString());

        $ajukanLagi = $this->actingAs($bawahan, 'sanctum')->postJson("/api/v1/penugasan/{$tugas->id}/perpanjangan-waktu", [
            'deadline_diminta' => now()->addDays(14)->toDateString(),
            'alasan_pengajuan' => 'x',
        ]);

        $ajukanLagi->assertStatus(201)->assertJsonPath('data.ke_berapa', 1);
    }

    public function test_perpanjangan_waktu_batas_3x_disetujui(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, ['status' => Penugasan::STATUS_PROSES]);

        for ($i = 1; $i <= 3; $i++) {
            $ajukan = $this->actingAs($bawahan, 'sanctum')->postJson("/api/v1/penugasan/{$tugas->id}/perpanjangan-waktu", [
                'deadline_diminta' => now()->addDays(7 + $i)->toDateString(),
                'alasan_pengajuan' => 'x',
            ])->assertStatus(201);

            $this->actingAs($atasan, 'sanctum')
                ->postJson("/api/v1/penugasan/{$tugas->id}/perpanjangan-waktu/{$ajukan->json('data.id')}/setujui", [
                    'deadline_disetujui' => now()->addDays(7 + $i)->toDateString(),
                ])->assertStatus(200);
        }

        $this->actingAs($bawahan, 'sanctum')->postJson("/api/v1/penugasan/{$tugas->id}/perpanjangan-waktu", [
            'deadline_diminta' => now()->addDays(20)->toDateString(),
            'alasan_pengajuan' => 'x',
        ])->assertStatus(422);
    }

    public function test_atasan_lain_tidak_bisa_menyetujui_perpanjangan(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $atasanLain = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, ['status' => Penugasan::STATUS_PROSES]);

        $ajukan = $this->actingAs($bawahan, 'sanctum')->postJson("/api/v1/penugasan/{$tugas->id}/perpanjangan-waktu", [
            'deadline_diminta' => now()->addDays(14)->toDateString(),
            'alasan_pengajuan' => 'x',
        ]);

        $this->actingAs($atasanLain, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugas->id}/perpanjangan-waktu/{$ajukan->json('data.id')}/setujui", [
                'deadline_disetujui' => now()->addDays(14)->toDateString(),
            ])
            ->assertStatus(403);
    }

    // --- Status Terlambat Otomatis (Fase 5) ---

    public function test_command_tandai_terlambat_menandai_tugas_lewat_deadline(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $lewatDeadline = $this->createPenugasan($bawahan, $atasan, [
            'status' => Penugasan::STATUS_PROSES,
            'deadline_terbaru' => now()->subDay(),
        ]);
        $belumLewat = $this->createPenugasan($bawahan, $atasan, [
            'status' => Penugasan::STATUS_PROSES,
            'deadline_terbaru' => now()->addDay(),
        ]);

        $this->artisan('penugasan:tandai-terlambat')->assertSuccessful();

        $this->assertEquals(Penugasan::STATUS_TERLAMBAT, $lewatDeadline->fresh()->status);
        $this->assertEquals(Penugasan::STATUS_PROSES, $belumLewat->fresh()->status);
    }

    public function test_tugas_terlambat_masih_bisa_disubmit(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $tugas = $this->createPenugasan($bawahan, $atasan, ['status' => Penugasan::STATUS_TERLAMBAT]);

        $this->assertTrue(app(\Illuminate\Contracts\Auth\Access\Gate::class)->forUser($bawahan)->allows('submit', $tugas));
    }

    // --- Penugasan Grup (Fase 6) ---

    public function test_berikan_tugas_grup_mode_per_orang_independen(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $pegawai1 = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $pegawai2 = $this->createUserWithJabatan('PELAKSANA', $atasan);

        $response = $this->actingAs($atasan, 'sanctum')->postJson('/api/v1/penugasan/berikan-tugas-grup', [
            'pegawai_ids' => [$pegawai1->id, $pegawai2->id],
            'mode_grup' => 'per_orang',
            'jenis' => 'tambahan',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Survei lapangan',
            'deskripsi' => 'x',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'bobot_persen' => 20,
        ]);

        $response->assertStatus(201)->assertJsonCount(2, 'data');
        $rows = Penugasan::where('pegawai_id', $pegawai1->id)->orWhere('pegawai_id', $pegawai2->id)->get();
        $this->assertCount(2, $rows);
        $this->assertEquals($rows->first()->grup_id, $rows->last()->grup_id);

        $tugasPegawai1 = $rows->firstWhere('pegawai_id', $pegawai1->id);
        $this->actingAs($pegawai1, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugasPegawai1->id}/terima")
            ->assertStatus(200);

        $tugasPegawai2 = $rows->firstWhere('pegawai_id', $pegawai2->id);
        $this->assertEquals('pending', $tugasPegawai2->fresh()->status);
    }

    public function test_berikan_tugas_grup_mode_kolektif_hanya_koordinator_bisa_bertindak(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $koordinator = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $anggota = $this->createUserWithJabatan('PELAKSANA', $atasan);

        $response = $this->actingAs($atasan, 'sanctum')->postJson('/api/v1/penugasan/berikan-tugas-grup', [
            'pegawai_ids' => [$koordinator->id, $anggota->id],
            'mode_grup' => 'kolektif',
            'koordinator_id' => $koordinator->id,
            'jenis' => 'tambahan',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Laporan bersama',
            'deskripsi' => 'x',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'bobot_persen' => 30,
        ]);

        $response->assertStatus(201);
        $rows = Penugasan::where('grup_id', $response->json('data.0.grup_id'))->get();
        $tugasKoordinator = $rows->firstWhere('pegawai_id', $koordinator->id);
        $tugasAnggota = $rows->firstWhere('pegawai_id', $anggota->id);

        $this->actingAs($anggota, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugasAnggota->id}/terima")
            ->assertStatus(403);

        $this->actingAs($koordinator, 'sanctum')
            ->postJson("/api/v1/penugasan/{$tugasKoordinator->id}/terima")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'proses');

        // Cascade: anggota ikut berubah status meski yang bertindak koordinator
        $this->assertEquals('proses', $tugasAnggota->fresh()->status);
    }

    public function test_jafung_bisa_memberi_tugas_ke_pelaksana_dan_gatek_di_bidangnya(): void
    {
        $jafung = $this->createUserWithJabatan('JAFUNG', bidang: $this->bidangUtama);
        $pelaksana = $this->createUserWithJabatan('PELAKSANA', bidang: $this->bidangUtama);
        $gatek = $this->createUserWithJabatan('GATEK', bidang: $this->bidangUtama);

        foreach ([$pelaksana, $gatek] as $target) {
            $response = $this->actingAs($jafung, 'sanctum')->postJson('/api/v1/penugasan', [
                'pegawai_id' => $target->id,
                'jenis' => 'tambahan',
                'prioritas' => 'sedang',
                'nama_tugas' => 'Tugas dari Jafung',
                'deskripsi' => 'x',
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->addDays(7)->toDateString(),
            ]);

            $response->assertStatus(201);
        }
    }

    public function test_jafung_tidak_bisa_memberi_tugas_lintas_bidang(): void
    {
        $jafung = $this->createUserWithJabatan('JAFUNG', bidang: $this->bidangUtama);
        $pelaksanaBidangLain = $this->createUserWithJabatan('PELAKSANA', bidang: $this->bidangSekretariat);

        $response = $this->actingAs($jafung, 'sanctum')->postJson('/api/v1/penugasan', [
            'pegawai_id' => $pelaksanaBidangLain->id,
            'jenis' => 'tambahan',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Tugas lintas bidang',
            'deskripsi' => 'x',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertStatus(403);
    }
}
