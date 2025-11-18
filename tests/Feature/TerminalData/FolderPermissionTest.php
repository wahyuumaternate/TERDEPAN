<?php

namespace Tests\Feature\TerminalData;

use Tests\TestCase;
use App\Models\MasterPegawai;
use App\Models\MasterJabatan;
use App\Models\MasterBidang;
use Modules\TerminalData\Models\TdFolder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FolderPermissionTest extends TestCase
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

        // Seed master bidang - HARUS SEBELUM SUB BIDANG
        MasterBidang::insert([
            ['id' => 1, 'kode' => 'APPS', 'nama' => 'Bidang Aplikasi dan Statistik'],
            ['id' => 2, 'kode' => 'INFRA', 'nama' => 'Bidang Infrastruktur dan Keamanan'],
        ]);

        // Buat sample folders untuk testing
        $bidang1 = MasterBidang::first();
        $bidang2 = MasterBidang::skip(1)->first();

        // Create pegawai for created_by constraint
        $pegawai = MasterPegawai::create([
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

        // Folder untuk bidang 1 (5 folders)
        TdFolder::factory()->count(5)->create([
            'bidang_id' => $bidang1->id,
            'created_by' => $pegawai->id,
        ]);

        // Folder untuk bidang 2 (3 folders)
        TdFolder::factory()->count(3)->create([
            'bidang_id' => $bidang2->id,
            'created_by' => $pegawai->id,
        ]);

        // Folder tanpa bidang spesifik (2 folders)
        TdFolder::factory()->count(2)->create([
            'bidang_id' => null,
            'created_by' => $pegawai->id,
        ]);
    }

    /**
     * Test view all folders permission by jabatan
     * 
     * @test
     * @dataProvider jabatanViewAccessProvider
     */
    public function test_view_folders_access_by_jabatan($jabatanKode, $bidangId, $expectedMinCount, $expectAll)
    {
        // Create user dengan jabatan tertentu
        $user = $this->createUserWithJabatan($jabatanKode, $bidangId);

        // Hit API endpoint
        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('terminaldata.foldersData.index'));

        $response->assertStatus(200);

        $folders = $response->json();
        $actualCount = is_array($folders) ? count($folders) : 0;

        if ($expectAll) {
            // Admin, Kaban, Sekban harus lihat semua (10 folders)
            $this->assertEquals(
                10,
                $actualCount,
                "{$jabatanKode} harus dapat melihat semua 10 folder"
            );
        } else {
            // Role lain minimal sesuai scope mereka
            $this->assertGreaterThanOrEqual(
                $expectedMinCount,
                $actualCount,
                "{$jabatanKode} harus dapat melihat minimal {$expectedMinCount} folder"
            );
        }
    }

    /**
     * Test create folder permission by jabatan
     * 
     * @test
     * @dataProvider jabatanCreateAccessProvider
     */
    public function test_create_folder_permission_by_jabatan($jabatanKode, $canCreate)
    {
        $user = $this->createUserWithJabatan($jabatanKode);

        $folderData = [
            'name' => 'Test Folder ' . $jabatanKode,
            'bidang_id' => MasterBidang::first()->id,
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('terminaldata.foldersData.store'), $folderData);

        if ($canCreate) {
            $response->assertStatus(201);
            $this->assertDatabaseHas('td_folders', ['name' => 'Test Folder ' . $jabatanKode]);
        } else {
            $response->assertStatus(403);
        }
    }

    /**
     * Test update folder permission by jabatan
     * 
     * @test
     * @dataProvider jabatanUpdateDeleteProvider
     */
    public function test_update_folder_permission_by_jabatan($jabatanKode, $folderBidangId, $userBidangId, $canUpdate)
    {
        $user = $this->createUserWithJabatan($jabatanKode, $userBidangId);

        // Buat owner untuk folder (different from test user for non-own scenarios)
        $owner = $this->createUserWithJabatan('PELAKSANA', $folderBidangId);

        // Buat folder untuk di-update
        $folder = TdFolder::factory()->create([
            'bidang_id' => $folderBidangId,
            'created_by' => $canUpdate && in_array($jabatanKode, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])
                ? $user->id
                : $owner->id
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson(route('terminaldata.foldersData.update', $folder->id), [
                'name' => 'Updated Folder Name'
            ]);

        if ($canUpdate) {
            $response->assertStatus(200);
        } else {
            $response->assertStatus(403);
        }
    }

    /**
     * Test delete folder with files (should fail)
     * 
     * @test
     */
    public function test_cannot_delete_folder_with_files()
    {
        $admin = $this->createUserWithJabatan('ADMIN');

        // Buat folder dengan file
        $folder = TdFolder::factory()->create(['created_by' => $admin->id]);

        // Buat file di dalam folder
        \Modules\TerminalData\Models\TdFile::factory()->create([
            'folder_id' => $folder->id,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson(route('terminaldata.foldersData.destroy', $folder->id));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Tidak dapat menghapus folder yang masih memiliki file'
            ]);
    }

    /**
     * Test delete folder with subfolders (should fail)
     * 
     * @test
     */
    public function test_cannot_delete_folder_with_subfolders()
    {
        $admin = $this->createUserWithJabatan('ADMIN');

        // Buat parent folder dengan subfolder
        $parentFolder = TdFolder::factory()->create(['created_by' => $admin->id]);
        TdFolder::factory()->create(['parent_id' => $parentFolder->id, 'created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson(route('terminaldata.foldersData.destroy', $parentFolder->id));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Tidak dapat menghapus folder yang masih memiliki subfolder'
            ]);
    }

    /**
     * Test delete empty folder (should success for admin)
     * 
     * @test
     */
    public function test_can_delete_empty_folder()
    {
        $admin = $this->createUserWithJabatan('ADMIN');

        $emptyFolder = TdFolder::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson(route('terminaldata.foldersData.destroy', $emptyFolder->id));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Folder berhasil dipindahkan ke sampah'
            ]);

        $this->assertSoftDeleted('td_folders', ['id' => $emptyFolder->id]);
    }

    // ==================== DATA PROVIDERS ====================

    public static function jabatanViewAccessProvider()
    {
        return [
            'ADMIN sees all folders' => ['ADMIN', 1, 10, true],
            'KABAN sees all folders' => ['KABAN', 1, 10, true],
            'SEKBAN sees all folders' => ['SEKBAN', 1, 10, true],
            'KABID sees bidang folders' => ['KABID', 1, 5, false],
            'KASUBAG sees all folders' => ['KASUBAG', 1, 10, true],
            'PELAKSANA sees all folders' => ['PELAKSANA', 1, 10, true],
            'JAFUNG sees all folders' => ['JAFUNG', 1, 10, true],
            'GATEK sees all folders' => ['GATEK', 1, 10, true],
        ];
    }

    public static function jabatanCreateAccessProvider()
    {
        return [
            'ADMIN can create' => ['ADMIN', true],
            'KABAN can create' => ['KABAN', true],
            'SEKBAN can create' => ['SEKBAN', true],
            'KABID can create' => ['KABID', true],
            'KASUBAG can create' => ['KASUBAG', true],
            'PELAKSANA can create' => ['PELAKSANA', true],
            'JAFUNG can create' => ['JAFUNG', true],
            'GATEK can create' => ['GATEK', true],
        ];
    }

    public static function jabatanUpdateDeleteProvider()
    {
        return [
            'ADMIN can update any folder' => ['ADMIN', 1, 1, true],
            'KABAN can update any folder' => ['KABAN', 1, 2, true],
            'SEKBAN can update any folder' => ['SEKBAN', 1, 2, true],
            'KABID can update same bidang' => ['KABID', 1, 1, true],
            'KABID cannot update other bidang' => ['KABID', 2, 1, false],
            'KASUBAG can update own folder' => ['KASUBAG', 1, 1, true],
            'PELAKSANA can update own folder' => ['PELAKSANA', 1, 1, true],
            'JAFUNG can update own folder' => ['JAFUNG', 1, 1, true],
            'GATEK can update own folder' => ['GATEK', 1, 1, true],
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
