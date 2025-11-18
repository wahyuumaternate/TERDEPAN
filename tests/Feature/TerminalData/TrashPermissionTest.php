<?php

namespace Tests\Feature\TerminalData;

use Tests\TestCase;
use App\Models\MasterPegawai;
use App\Models\MasterJabatan;
use App\Models\MasterBidang;
use Modules\TerminalData\Models\TdFolder;
use Modules\TerminalData\Models\TdFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrashPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed master jabatan
        MasterJabatan::insert([
            ['id' => 1, 'kode' => 'ADMIN', 'nama' => 'Administrator', 'level' => 0],
            ['id' => 2, 'kode' => 'KABAN', 'nama' => 'Kepala Badan', 'level' => 1],
            ['id' => 3, 'kode' => 'SEKBAN', 'nama' => 'Sekretaris Badan', 'level' => 2],
            ['id' => 4, 'kode' => 'KABID', 'nama' => 'Kepala Bidang', 'level' => 3],
            ['id' => 5, 'kode' => 'KASUBAG', 'nama' => 'Kepala Sub Bagian', 'level' => 3],
            ['id' => 6, 'kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5],
            ['id' => 7, 'kode' => 'JAFUNG', 'nama' => 'Jabatan Fungsional', 'level' => 4],
            ['id' => 8, 'kode' => 'GATEK', 'nama' => 'Penggerak Teknologi', 'level' => 6],
        ]);

        // Seed master bidang
        MasterBidang::insert([
            ['id' => 1, 'kode' => 'APPS', 'nama' => 'Bidang Aplikasi dan Statistik'],
            ['id' => 2, 'kode' => 'INFRA', 'nama' => 'Bidang Infrastruktur dan Keamanan'],
        ]);

        // Create pegawai for created_by constraint
        MasterPegawai::create([
            'nomor_identitas' => '199001012020011001',
            'tipe_identitas' => 'NIP',
            'nama' => 'Test User',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'jabatan_id' => 1,
            'bidang_id' => 1,
        ]);
    }

    /**
     * Test restore file permission by jabatan
     * 
     * @test
     * @dataProvider jabatanRestoreDeleteProvider
     */
    public function test_1_restore_file_permission_by_jabatan($jabatanKode, $fileBidangId, $userBidangId, $canRestore)
    {
        $user = $this->createUserWithJabatan($jabatanKode, $userBidangId);

        // Buat owner untuk file
        $owner = $this->createUserWithJabatan('PELAKSANA', $fileBidangId);

        // Buat folder
        $folder = TdFolder::factory()->create([
            'bidang_id' => $fileBidangId,
            'created_by' => $owner->id,
        ]);

        // Buat file yang sudah di-delete (soft delete)
        $file = TdFile::factory()->create([
            'folder_id' => $folder->id,
            'bidang_id' => $fileBidangId,
            'created_by' => $canRestore && in_array($jabatanKode, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])
                ? $user->id
                : $owner->id
        ]);

        // Soft delete file
        $file->delete();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('terminaldata.filesData.restore', $file->id));

        if ($canRestore) {
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'File berhasil dipulihkan'
                ]);
        } else {
            $response->assertStatus(403)
                ->assertJson([
                    'success' => false
                ]);
        }
    }

    /**
     * Test force delete file permission by jabatan
     * 
     * @test
     * @dataProvider jabatanRestoreDeleteProvider
     */
    public function test_2_force_delete_file_permission_by_jabatan($jabatanKode, $fileBidangId, $userBidangId, $canForceDelete)
    {
        $user = $this->createUserWithJabatan($jabatanKode, $userBidangId);

        // Buat owner untuk file
        $owner = $this->createUserWithJabatan('PELAKSANA', $fileBidangId);

        // Buat folder
        $folder = TdFolder::factory()->create([
            'bidang_id' => $fileBidangId,
            'created_by' => $owner->id,
        ]);

        // Buat file yang sudah di-delete (soft delete)
        $file = TdFile::factory()->create([
            'folder_id' => $folder->id,
            'bidang_id' => $fileBidangId,
            'created_by' => $canForceDelete && in_array($jabatanKode, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])
                ? $user->id
                : $owner->id
        ]);

        // Soft delete file
        $file->delete();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson(route('terminaldata.filesData.forceDelete', $file->id));

        if ($canForceDelete) {
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'File berhasil dihapus permanen'
                ]);
        } else {
            $response->assertStatus(403)
                ->assertJson([
                    'success' => false
                ]);
        }
    }

    /**
     * Test restore folder permission by jabatan
     * 
     * @test
     * @dataProvider jabatanRestoreDeleteProvider
     */
    public function test_3_restore_folder_permission_by_jabatan($jabatanKode, $folderBidangId, $userBidangId, $canRestore)
    {
        $user = $this->createUserWithJabatan($jabatanKode, $userBidangId);

        // Buat owner untuk folder
        $owner = $this->createUserWithJabatan('PELAKSANA', $folderBidangId);

        // Buat folder yang sudah di-delete (soft delete)
        $folder = TdFolder::factory()->create([
            'bidang_id' => $folderBidangId,
            'created_by' => $canRestore && in_array($jabatanKode, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])
                ? $user->id
                : $owner->id
        ]);

        // Soft delete folder
        $folder->delete();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('terminaldata.foldersData.restore', $folder->id));

        if ($canRestore) {
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Folder berhasil dipulihkan'
                ]);
        } else {
            $response->assertStatus(403)
                ->assertJson([
                    'success' => false
                ]);
        }
    }

    /**
     * Test force delete folder permission by jabatan
     * Hanya ADMIN, KABAN, SEKBAN yang bisa force delete folder
     * 
     * @test
     * @dataProvider jabatanForceDeleteFolderProvider
     */
    public function test_4_force_delete_folder_permission_by_jabatan($jabatanKode, $folderBidangId, $userBidangId, $canForceDelete)
    {
        $user = $this->createUserWithJabatan($jabatanKode, $userBidangId);

        // Buat owner untuk folder
        $owner = $this->createUserWithJabatan('PELAKSANA', $folderBidangId);

        // Buat folder yang sudah di-delete (soft delete)
        $folder = TdFolder::factory()->create([
            'bidang_id' => $folderBidangId,
            'created_by' => $owner->id
        ]);

        // Soft delete folder
        $folder->delete();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson(route('terminaldata.foldersData.forceDelete', $folder->id));

        if ($canForceDelete) {
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Folder berhasil dihapus permanen'
                ]);
        } else {
            $response->assertStatus(403)
                ->assertJson([
                    'success' => false
                ]);
        }
    }

    /**
     * Test tidak bisa restore file di folder Eviden Kinerja yang sudah dihapus
     * File di Eviden Kinerja tidak bisa dihapus, tapi jika somehow terhapus,
     * seharusnya juga tidak bisa direstore
     * 
     * @test
     */
    public function test_5_cannot_restore_file_in_eviden_kinerja()
    {
        $admin = $this->createUserWithJabatan('ADMIN');

        // Buat folder Eviden Kinerja
        $evidenFolder = TdFolder::factory()->create([
            'name' => 'Eviden Kinerja',
            'created_by' => $admin->id,
        ]);

        // Buat file di Eviden Kinerja
        $file = TdFile::factory()->create([
            'folder_id' => $evidenFolder->id,
            'created_by' => $admin->id,
        ]);

        // Force soft delete (bypass policy untuk testing)
        $file->delete();

        // Bahkan admin tidak bisa restore file di Eviden Kinerja
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson(route('terminaldata.filesData.restore', $file->id));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    // ==================== DATA PROVIDERS ====================

    /**
     * Data provider untuk test restore dan force delete
     * Format: [$jabatanKode, $fileBidangId, $userBidangId, $canRestoreDelete]
     */
    public static function jabatanRestoreDeleteProvider()
    {
        return [
            'ADMIN can restore/delete any file' => ['ADMIN', 1, 1, true],
            'KABAN can restore/delete any file' => ['KABAN', 1, 2, true],
            'SEKBAN can restore/delete any file' => ['SEKBAN', 1, 2, true],
            'KABID can restore/delete same bidang' => ['KABID', 1, 1, true],
            'KABID cannot restore/delete other bidang' => ['KABID', 2, 1, false],
            'KASUBAG can restore/delete own file' => ['KASUBAG', 1, 1, true],
            'PELAKSANA can restore/delete own file' => ['PELAKSANA', 1, 1, true],
            'JAFUNG can restore/delete own file' => ['JAFUNG', 1, 1, true],
            'GATEK can restore/delete own file' => ['GATEK', 1, 1, true],
        ];
    }

    /**
     * Data provider untuk test force delete folder
     * Hanya ADMIN, KABAN, SEKBAN yang bisa force delete folder
     * Format: [$jabatanKode, $folderBidangId, $userBidangId, $canForceDelete]
     */
    public static function jabatanForceDeleteFolderProvider()
    {
        return [
            'ADMIN can force delete folder' => ['ADMIN', 1, 1, true],
            'KABAN can force delete folder' => ['KABAN', 1, 2, true],
            'SEKBAN can force delete folder' => ['SEKBAN', 1, 2, true],
            'KABID cannot force delete folder' => ['KABID', 1, 1, false],
            'KASUBAG cannot force delete folder' => ['KASUBAG', 1, 1, false],
            'PELAKSANA cannot force delete folder' => ['PELAKSANA', 1, 1, false],
            'JAFUNG cannot force delete folder' => ['JAFUNG', 1, 1, false],
            'GATEK cannot force delete folder' => ['GATEK', 1, 1, false],
        ];
    }

    // ==================== HELPER METHODS ====================

    protected function createUserWithJabatan(string $kodeJabatan, ?int $bidangId = null): MasterPegawai
    {
        $jabatan = MasterJabatan::where('kode', $kodeJabatan)->firstOrFail();
        $nip = '1990' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT) . '001';

        return MasterPegawai::create([
            'nomor_identitas' => $nip,
            'tipe_identitas' => 'NIP',
            'nama' => 'User ' . $kodeJabatan,
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'email' => strtolower($kodeJabatan) . rand(1, 9999) . '@test.com',
            'password' => bcrypt('password'),
            'jabatan_id' => $jabatan->id,
            'bidang_id' => $bidangId ?? MasterBidang::first()->id,
        ]);
    }
}
