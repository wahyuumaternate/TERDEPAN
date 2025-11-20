<?php

namespace Modules\Penugasan\Tests\Feature;

use Tests\TestCase;
use App\Models\MasterPegawai;
use App\Models\MasterJabatan;
use App\Models\MasterBidang;
use Modules\Penugasan\Models\TugasHarian;
use Modules\Penugasan\Models\TugasTambahan;
use Modules\Penugasan\Models\TugasPokok;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class PenugasanPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed master data secara manual untuk test
        $this->seedMasterData();
    }

    /**
     * Seed master data yang diperlukan untuk testing
     */
    protected function seedMasterData(): void
    {
        // Create MasterBidang
        MasterBidang::firstOrCreate(
            ['id' => 1],
            ['nama' => 'Bidang Test', 'kode' => 'TEST']
        );

        // Create MasterJabatan
        $jabatanData = [
            ['id' => 1, 'kode' => 'ADMIN', 'nama' => 'Admin', 'level' => 1],
            ['id' => 2, 'kode' => 'KABAN', 'nama' => 'Kepala Badan', 'level' => 2],
            ['id' => 3, 'kode' => 'SEKBAN', 'nama' => 'Sekretaris Badan', 'level' => 3],
            ['id' => 4, 'kode' => 'KABID', 'nama' => 'Kepala Bidang', 'level' => 4],
            ['id' => 5, 'kode' => 'KASUBAG', 'nama' => 'Kepala Sub Bagian', 'level' => 4],
            ['id' => 6, 'kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5],
            ['id' => 7, 'kode' => 'JAFUNG', 'nama' => 'Jabatan Fungsional', 'level' => 6],
            ['id' => 8, 'kode' => 'GATEK', 'nama' => 'Tenaga Teknis', 'level' => 7],
        ];

        foreach ($jabatanData as $jabatan) {
            MasterJabatan::firstOrCreate(
                ['id' => $jabatan['id']],
                $jabatan
            );
        }
    }

    // ==================== TUGAS HARIAN TESTS ====================

    /**
     * Test create tugas harian permission by jabatan
     */
    #[Test]
    #[DataProvider('jabatanCreateProvider')]
    public function test_1_create_tugas_harian_permission($jabatanKode, $canCreate)
    {
        $atasan = $this->createUserWithJabatan($jabatanKode);
        $bawahan = $this->createUserWithJabatan('PELAKSANA');
        $tugasPokok = $this->createTugasPokok($bawahan);

        $response = $this->actingAs($atasan)
            ->post(route('penugasan.berikan-tugas'), [
                'jenis_tugas' => 'tugas_harian',
                'pegawai_id' => $bawahan->id,
                'tugas_pokok_id' => $tugasPokok->id,
                'nama_tugas' => 'Test Task',
                'deskripsi' => 'Test Description',
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->addDays(7)->toDateString(),
                'prioritas' => 'medium',
                'target_value' => 100,
                'satuan' => 'dokumen',
            ]);

        if ($canCreate) {
            $this->assertContains(
                $response->status(),
                [200, 201, 302],
                "{$jabatanKode} should be able to create tugas harian"
            );
        } else {
            $this->assertEquals(
                403,
                $response->status(),
                "{$jabatanKode} should NOT be able to create tugas harian"
            );
        }
    }

    /**
     * Test update tugas harian permission
     */
    #[Test]
    #[DataProvider('statusPendingProvider')]
    public function test_2_update_tugas_harian_permission($status, $canUpdate)
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA');
        $otherUser = $this->createUserWithJabatan('KABID');

        $tugas = $this->createTugasHarian($atasan, $bawahan, $status);

        // Test: Pemberi tugas bisa update jika status pending
        $response = $this->actingAs($atasan)
            ->put(route('penugasan.tugas-harian.update', $tugas->id), [
                'nama_tugas' => 'Updated Task',
                'deskripsi' => 'Updated Description',
                'tanggal_mulai' => $tugas->tanggal_mulai,
                'tanggal_selesai' => $tugas->tanggal_selesai,
                'target_value' => $tugas->target_value ?? 100,
                'satuan' => $tugas->satuan ?? 'dokumen',
            ]);

        if ($canUpdate) {
            $this->assertContains($response->status(), [200, 302]);
        } else {
            $this->assertEquals(403, $response->status());
        }

        // Test: User lain tidak bisa update
        $response = $this->actingAs($otherUser)
            ->put(route('penugasan.tugas-harian.update', $tugas->id), [
                'nama_tugas' => 'Updated by Other',
                'deskripsi' => 'Updated Description',
                'tanggal_mulai' => $tugas->tanggal_mulai,
                'tanggal_selesai' => $tugas->tanggal_selesai,
                'target_value' => $tugas->target_value ?? 100,
                'satuan' => $tugas->satuan ?? 'dokumen',
            ]);

        $this->assertEquals(403, $response->status(), "Other user should NOT be able to update");
    }

    /**
     * Test delete tugas harian permission
     */
    #[Test]
    #[DataProvider('statusPendingProvider')]
    public function test_3_delete_tugas_harian_permission($status, $canDelete)
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA');

        $tugas = $this->createTugasHarian($atasan, $bawahan, $status);

        $response = $this->actingAs($atasan)
            ->delete(route('penugasan.tugas-harian.destroy', $tugas->id));

        if ($canDelete) {
            $this->assertContains($response->status(), [200, 302]);
        } else {
            $this->assertEquals(403, $response->status());
        }
    }

    /**
     * Test update status tugas harian (terima/tolak/kerjakan)
     */
    #[Test]
    public function test_4_update_status_tugas_harian_permission()
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA');
        $otherUser = $this->createUserWithJabatan('PELAKSANA');

        $tugas = $this->createTugasHarian($atasan, $bawahan, 'pending');

        // Test: Pegawai penerima bisa update status
        $response = $this->actingAs($bawahan)
            ->post(route('penugasan.tugas-harian.update-status', $tugas->id), [
                'status' => 'dikerjakan',
            ]);

        $this->assertContains($response->status(), [200, 302], "Pegawai should be able to update status");

        // Test: Atasan tidak bisa update status (ini hak pegawai)
        $tugas2 = $this->createTugasHarian($atasan, $bawahan, 'pending');
        $response = $this->actingAs($atasan)
            ->post(route('penugasan.tugas-harian.update-status', $tugas2->id), [
                'status' => 'dikerjakan',
            ]);

        $this->assertEquals(403, $response->status(), "Atasan should NOT be able to update status");

        // Test: User lain tidak bisa update status
        $tugas3 = $this->createTugasHarian($atasan, $bawahan, 'pending');
        $response = $this->actingAs($otherUser)
            ->post(route('penugasan.tugas-harian.update-status', $tugas3->id), [
                'status' => 'dikerjakan',
            ]);

        $this->assertEquals(403, $response->status(), "Other user should NOT be able to update status");
    }

    /**
     * Test upload eviden permission
     */
    #[Test]
    #[DataProvider('statusUploadEvidenProvider')]
    public function test_5_upload_eviden_permission($status, $canUpload)
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA');
        $otherUser = $this->createUserWithJabatan('PELAKSANA');

        $tugas = $this->createTugasHarian($atasan, $bawahan, $status);

        // Test: Pegawai penerima - GET upload eviden page
        $response = $this->actingAs($bawahan)
            ->get(route('penugasan.tugas-harian.upload-eviden', $tugas->id));

        if ($canUpload) {
            $this->assertEquals(200, $response->status(), "Pegawai should see upload page for status: {$status}");
        } else {
            $this->assertContains($response->status(), [403, 302], "Pegawai should NOT see upload page for status: {$status}");
        }

        // Test: User lain tidak bisa akses
        $response = $this->actingAs($otherUser)
            ->get(route('penugasan.tugas-harian.upload-eviden', $tugas->id));

        $this->assertContains($response->status(), [403, 302], "Other user should NOT access upload page");
    }

    /**
     * Test validasi tugas permission
     */
    #[Test]
    public function test_6_validasi_tugas_harian_permission()
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA');
        $otherUser = $this->createUserWithJabatan('KABID');

        $tugas = $this->createTugasHarian($atasan, $bawahan, 'validasi');

        // Test: Atasan pemberi tugas bisa validasi
        $response = $this->actingAs($atasan)
            ->post(route('penugasan.validasi-tugas', $tugas->id), [
                'jenis_tugas' => 'tugas_harian',
                'status_validasi' => 'diterima',
                'penilaian_kualitas' => 85,
                'catatan_validasi' => 'Bagus',
                'progress_update_type' => 'otomatis',
            ]);

        $this->assertContains($response->status(), [200, 302], "Atasan should be able to validate");

        // Test: Pegawai tidak bisa validasi tugasnya sendiri
        $tugas2 = $this->createTugasHarian($atasan, $bawahan, 'validasi');
        $response = $this->actingAs($bawahan)
            ->post(route('penugasan.validasi-tugas', $tugas2->id), [
                'jenis_tugas' => 'tugas_harian',
                'status_validasi' => 'diterima',
                'penilaian_kualitas' => 85,
                'progress_update_type' => 'otomatis',
            ]);

        $this->assertEquals(403, $response->status(), "Pegawai should NOT validate own task");

        // Test: Atasan lain tidak bisa validasi
        $tugas3 = $this->createTugasHarian($atasan, $bawahan, 'validasi');
        $response = $this->actingAs($otherUser)
            ->post(route('penugasan.validasi-tugas', $tugas3->id), [
                'jenis_tugas' => 'tugas_harian',
                'status_validasi' => 'diterima',
                'penilaian_kualitas' => 85,
                'progress_update_type' => 'otomatis',
            ]);

        $this->assertEquals(403, $response->status(), "Other atasan should NOT validate");
    }

    // ==================== TUGAS TAMBAHAN TESTS ====================

    /**
     * Test create tugas tambahan permission
     */
    #[Test]
    #[DataProvider('jabatanCreateProvider')]
    public function test_7_create_tugas_tambahan_permission($jabatanKode, $canCreate)
    {
        $atasan = $this->createUserWithJabatan($jabatanKode);
        $bawahan = $this->createUserWithJabatan('PELAKSANA');

        $response = $this->actingAs($atasan)
            ->post(route('penugasan.berikan-tugas'), [
                'jenis_tugas' => 'tugas_tambahan',
                'pegawai_id' => $bawahan->id,
                'nama_tugas' => 'Test Additional Task',
                'deskripsi' => 'Test Description',
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->addDays(7)->toDateString(),
                'prioritas' => 'medium',
                'target_value' => 100,
                'satuan' => 'dokumen',
            ]);

        if ($canCreate) {
            $this->assertContains($response->status(), [200, 201, 302]);
        } else {
            $this->assertEquals(403, $response->status());
        }
    }

    /**
     * Test update tugas tambahan permission (dari atasan)
     */
    #[Test]
    #[DataProvider('statusPendingProvider')]
    public function test_8_update_tugas_tambahan_from_atasan($status, $canUpdate)
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA');

        $tugas = $this->createTugasTambahan($atasan, $bawahan, $status);

        $response = $this->actingAs($atasan)
            ->put(route('penugasan.tugas-tambahan.update', $tugas->id), [
                'nama_tugas' => 'Updated Task',
                'deskripsi' => 'Updated Description',
                'tanggal_mulai' => $tugas->tanggal_mulai,
                'deadline' => $tugas->tanggal_selesai,
                'target_value' => $tugas->target_value ?? 100,
                'satuan' => $tugas->satuan ?? 'dokumen',
            ]);

        if ($canUpdate) {
            $this->assertContains($response->status(), [200, 302]);
        } else {
            $this->assertEquals(403, $response->status());
        }
    }

    /**
     * Test upload eviden tugas tambahan permission
     */
    #[Test]
    #[DataProvider('statusUploadEvidenProvider')]
    public function test_9_upload_eviden_tugas_tambahan_permission($status, $canUpload)
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $bawahan = $this->createUserWithJabatan('PELAKSANA');

        $tugas = $this->createTugasTambahan($atasan, $bawahan, $status);

        $response = $this->actingAs($bawahan)
            ->get(route('penugasan.tugas-tambahan.upload-eviden', $tugas->id));

        if ($canUpload) {
            $this->assertEquals(200, $response->status());
        } else {
            $this->assertContains($response->status(), [403, 302]);
        }
    }

    // ==================== DATA PROVIDERS ====================

    /**
     * Data provider untuk test create permission
     * Hanya jabatan struktural (ADMIN-KASUBAG) yang bisa buat tugas
     */
    public static function jabatanCreateProvider()
    {
        return [
            'ADMIN can create' => ['ADMIN', true],
            'KABAN can create' => ['KABAN', true],
            'SEKBAN can create' => ['SEKBAN', true],
            'KABID can create' => ['KABID', true],
            'KASUBAG can create' => ['KASUBAG', true],
            'PELAKSANA cannot create' => ['PELAKSANA', false],
            'JAFUNG cannot create' => ['JAFUNG', false],
            'GATEK cannot create' => ['GATEK', false],
        ];
    }

    /**
     * Data provider untuk test update/delete permission berdasarkan status
     * Hanya status pending yang bisa di-update/delete
     */
    public static function statusPendingProvider()
    {
        return [
            'status pending can update/delete' => ['pending', true],
            'status dikerjakan cannot update/delete' => ['dikerjakan', false],
            'status validasi cannot update/delete' => ['validasi', false],
            'status selesai cannot update/delete' => ['selesai', false],
            'status revisi cannot update/delete' => ['revisi', false],
        ];
    }

    /**
     * Data provider untuk test upload eviden permission
     * Hanya status dikerjakan dan revisi yang bisa upload
     */
    public static function statusUploadEvidenProvider()
    {
        return [
            'status pending cannot upload' => ['pending', false],
            'status dikerjakan can upload' => ['dikerjakan', true],
            'status validasi cannot upload' => ['validasi', false],
            'status selesai cannot upload' => ['selesai', false],
            'status revisi can upload' => ['revisi', true],
        ];
    }

    // ==================== HELPER METHODS ====================

    protected function createUserWithJabatan(string $kodeJabatan): MasterPegawai
    {
        $jabatan = MasterJabatan::where('kode', $kodeJabatan)->firstOrFail();
        $bidang = MasterBidang::firstOrFail(); // Gunakan bidang pertama yang pasti ada
        $nip = '1990' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT) . '001';

        return MasterPegawai::create([
            'nomor_identitas' => $nip,
            'tipe_identitas' => 'NIP',
            'nama' => "User {$kodeJabatan}",
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'email' => strtolower($kodeJabatan) . rand(1000, 9999) . '@test.com',
            'password' => bcrypt('password'),
            'jabatan_id' => $jabatan->id,
            'bidang_id' => $bidang->id,
        ]);
    }

    protected function createTugasPokok(MasterPegawai $pegawai): TugasPokok
    {
        // Create dummy Perjanjian Kinerja hierarchy for testing
        // atasan_id is required - use atasan_langsung_id if exists, otherwise use pegawai itself (for top level)
        $atasanId = $pegawai->atasan_langsung_id ?? $pegawai->id;

        // Create dummy PK template with unique kode
        $randomId = rand(1000, 9999);
        $template = \Modules\PerjanjianKinerja\Models\PkTemplate::create([
            'kode_template' => 'TPK-TEST-' . now()->year . '-' . $pegawai->jabatan_id . '-' . $randomId,
            'jabatan_id' => $pegawai->jabatan_id,
            'tahun' => now()->year,
            'nama_template' => 'Template Test ' . now()->year,
            'kop_surat_html' => '<div>Kop Surat Test</div>',
            'header_template' => 'Header Test',
            'pernyataan_pembuka' => 'Pembuka Test',
            'pernyataan_penutup' => 'Penutup Test',
            'footer_template' => 'Footer Test',
            'is_active' => true,
        ]);

        $pk = \Modules\PerjanjianKinerja\Models\PkPerjanjianKinerja::create([
            'nomor_perjanjian' => 'PK/TEST/' . now()->year . '/' . str_pad($pegawai->id, 4, '0', STR_PAD_LEFT) . '-' . uniqid(),
            'pegawai_id' => $pegawai->id,
            'atasan_id' => $atasanId,
            'template_id' => $template->id,
            'tahun' => now()->year,
            'periode_mulai' => now()->startOfYear(),
            'periode_selesai' => now()->endOfYear(),
            'status_dokumen' => 'Draft',
            'is_locked' => false,
        ]);

        $sasaran = \Modules\PerjanjianKinerja\Models\PkSasaran::create([
            'perjanjian_kinerja_id' => $pk->id,
            'sasaran_strategis' => 'Sasaran Test',
            'urutan' => 1,
        ]);

        $indikator = \Modules\PerjanjianKinerja\Models\PkIndikator::create([
            'sasaran_id' => $sasaran->id,
            'indikator_sasaran' => 'Indikator Test',
            'target_value' => 100,
            'satuan' => 'dokumen',
        ]);

        return TugasPokok::create([
            'pegawai_id' => $pegawai->id,
            'perjanjian_kinerja_id' => $pk->id,
            'indikator_id' => $indikator->id,
            'nama_tugas' => 'Tugas Pokok Test',
            'deskripsi' => 'Test Description',
            'bobot_persen' => 60,
            'target_value' => 100,
            'satuan' => 'dokumen',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonths(3),
            'status' => 'pending',
        ]);
    }

    protected function createTugasHarian(MasterPegawai $atasan, MasterPegawai $bawahan, string $status): TugasHarian
    {
        $tugasPokok = $this->createTugasPokok($bawahan);

        return TugasHarian::create([
            'pegawai_id' => $bawahan->id,
            'pemberi_tugas_id' => $atasan->id,
            'tugas_pokok_id' => $tugasPokok->id,
            'nama_tugas' => 'Test Tugas Harian',
            'deskripsi' => 'Test Description',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'status' => $status,
            'prioritas' => 'medium',
            'target_value' => 100,
            'satuan' => 'dokumen',
        ]);
    }

    protected function createTugasTambahan(MasterPegawai $atasan, MasterPegawai $bawahan, string $status): TugasTambahan
    {
        return TugasTambahan::create([
            'pegawai_id' => $bawahan->id,
            'pemberi_tugas_id' => $atasan->id,
            'nama_tugas' => 'Test Tugas Tambahan',
            'deskripsi' => 'Test Description',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'status' => $status,
            'prioritas' => 'medium',
            'target_value' => 100,
            'satuan' => 'dokumen',
        ]);
    }
}
