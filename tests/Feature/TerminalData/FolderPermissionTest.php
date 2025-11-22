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

        // Tentukan owner berdasarkan skenario
        if (in_array($jabatanKode, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            // Admin/Kaban/Sekban bisa update folder siapa saja
            $owner = $this->createUserWithJabatan('PELAKSANA', $folderBidangId);
        } elseif ($jabatanKode === 'KABID' && $canUpdate) {
            // KABID update folder di bidangnya (bisa punya orang lain)
            $owner = $this->createUserWithJabatan('PELAKSANA', $folderBidangId);
        } elseif ($canUpdate) {
            // User lain yang canUpdate = folder sendiri
            $owner = $user;
        } else {
            // Tidak bisa update = folder orang lain
            $owner = $this->createUserWithJabatan('PELAKSANA', $folderBidangId);
        }

        // Buat folder untuk di-update
        $folder = TdFolder::factory()->create([
            'bidang_id' => $folderBidangId,
            'created_by' => $owner->id
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

    /**
     * Test delete folder permission by jabatan
     * 
     * @test
     * @dataProvider jabatanDeleteProvider
     */
    public function test_delete_folder_permission_by_jabatan($jabatanKode, $canDeleteOthers)
    {
        $user = $this->createUserWithJabatan($jabatanKode, 1);

        // Test delete folder sendiri (semua bisa)
        $ownFolder = TdFolder::factory()->create(['created_by' => $user->id, 'bidang_id' => 1]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson(route('terminaldata.foldersData.destroy', $ownFolder->id));

        $response->assertStatus(200);
        $this->assertSoftDeleted('td_folders', ['id' => $ownFolder->id]);

        // Test delete folder orang lain
        $otherUser = $this->createUserWithJabatan('PELAKSANA', 1);
        $othersFolder = TdFolder::factory()->create(['created_by' => $otherUser->id, 'bidang_id' => 1]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson(route('terminaldata.foldersData.destroy', $othersFolder->id));

        if ($canDeleteOthers) {
            $response->assertStatus(200);
        } else {
            $response->assertStatus(403);
        }
    }

    /**
     * Test rename folder permission by jabatan
     * Policy rename() berbeda dengan update()
     * 
     * @test
     * @dataProvider jabatanRenameProvider
     */
    public function test_rename_folder_permission_by_jabatan($jabatanKode, $folderBidangId, $userBidangId, $canRename)
    {
        $user = $this->createUserWithJabatan($jabatanKode, $userBidangId);

        // Tentukan owner
        if (in_array($jabatanKode, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            // Bisa rename folder siapa saja
            $owner = $this->createUserWithJabatan('PELAKSANA', $folderBidangId);
        } elseif ($jabatanKode === 'KABID' && $canRename) {
            // KABID bisa rename semua folder di bidangnya (milik orang lain juga)
            $owner = $this->createUserWithJabatan('PELAKSANA', $folderBidangId);
        } elseif ($canRename) {
            // User lain hanya bisa rename folder sendiri
            $owner = $user;
        } else {
            $owner = $this->createUserWithJabatan('PELAKSANA', $folderBidangId);
        }

        $folder = TdFolder::factory()->create([
            'bidang_id' => $folderBidangId,
            'created_by' => $owner->id
        ]);

        // Test policy rename langsung
        $result = $user->can('rename', $folder);

        $this->assertEquals(
            $canRename,
            $result,
            "{$jabatanKode} " . ($canRename ? 'harus bisa' : 'tidak bisa') . " rename folder"
        );
    }

    /**
     * Test restore folder permission
     * 
     * @test
     */
    public function test_restore_folder_permission()
    {
        $admin = $this->createUserWithJabatan('ADMIN');
        $user = $this->createUserWithJabatan('PELAKSANA', 1);

        // Admin restore folder sendiri
        $adminFolder = TdFolder::factory()->create(['created_by' => $admin->id]);
        $adminFolder->delete();
        $this->assertTrue($admin->can('restore', $adminFolder));

        // User restore folder sendiri
        $userFolder = TdFolder::factory()->create(['created_by' => $user->id]);
        $userFolder->delete();
        $this->assertTrue($user->can('restore', $userFolder));

        // User tidak bisa restore folder orang lain
        $this->assertFalse($user->can('restore', $adminFolder));

        // Admin bisa restore folder orang lain
        $this->assertTrue($admin->can('restore', $userFolder));
    }

    /**
     * Test viewTrashed permission
     * 
     * @test
     */
    public function test_view_trashed_permission()
    {
        // ADMIN, KABAN, SEKBAN bisa view semua trash
        $admin = $this->createUserWithJabatan('ADMIN');
        $kaban = $this->createUserWithJabatan('KABAN');
        $sekban = $this->createUserWithJabatan('SEKBAN');

        $this->assertTrue($admin->can('viewTrashed', TdFolder::class));
        $this->assertTrue($kaban->can('viewTrashed', TdFolder::class));
        $this->assertTrue($sekban->can('viewTrashed', TdFolder::class));

        // User lain tidak bisa view semua trash
        $kabid = $this->createUserWithJabatan('KABID');
        $pelaksana = $this->createUserWithJabatan('PELAKSANA');

        $this->assertFalse($kabid->can('viewTrashed', TdFolder::class));
        $this->assertFalse($pelaksana->can('viewTrashed', TdFolder::class));
    }

    // ==================== DATA PROVIDERS ====================

    public static function jabatanViewAccessProvider()
    {
        // Berdasarkan policy view(): semua user yang terautentikasi bisa melihat folder
        // Policy viewAny(): semua pegawai bisa view folders
        return [
            'ADMIN sees all folders' => ['ADMIN', 1, 10, true],
            'KABAN sees all folders' => ['KABAN', 1, 10, true],
            'SEKBAN sees all folders' => ['SEKBAN', 1, 10, true],
            'KABID sees all folders' => ['KABID', 1, 10, true],  // Changed: semua user bisa view
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
        // Berdasarkan policy update():
        // - ADMIN, KABAN, SEKBAN: Full Access (semua folder)
        // - KABID: Edit folder bidangnya
        // - KASUBAG, PELAKSANA, JAFUNG, GATEK: Edit folder sendiri (created_by)
        return [
            'ADMIN can update any folder' => ['ADMIN', 1, 1, true],
            'KABAN can update any folder' => ['KABAN', 1, 2, true],
            'SEKBAN can update any folder' => ['SEKBAN', 1, 2, true],
            'KABID can update same bidang folder' => ['KABID', 1, 1, true],
            'KABID cannot update other bidang folder' => ['KABID', 2, 1, false],
            'KASUBAG can update own folder' => ['KASUBAG', 1, 1, true],
            'KASUBAG cannot update other folder' => ['KASUBAG', 1, 2, false],
            'PELAKSANA can update own folder' => ['PELAKSANA', 1, 1, true],
            'PELAKSANA cannot update other folder' => ['PELAKSANA', 1, 2, false],
            'JAFUNG can update own folder' => ['JAFUNG', 1, 1, true],
            'GATEK can update own folder' => ['GATEK', 1, 1, true],
        ];
    }

    public static function jabatanDeleteProvider()
    {
        // Berdasarkan policy delete():
        // - ADMIN, KABAN, SEKBAN: Full Access
        // - Semua user lain: Delete folder sendiri saja
        return [
            'ADMIN can delete others folder' => ['ADMIN', true],
            'KABAN can delete others folder' => ['KABAN', true],
            'SEKBAN can delete others folder' => ['SEKBAN', true],
            'KABID can only delete own' => ['KABID', false],
            'KASUBAG can only delete own' => ['KASUBAG', false],
            'PELAKSANA can only delete own' => ['PELAKSANA', false],
            'JAFUNG can only delete own' => ['JAFUNG', false],
            'GATEK can only delete own' => ['GATEK', false],
        ];
    }

    public static function jabatanRenameProvider()
    {
        // Berdasarkan policy rename():
        // - ADMIN, KABAN, SEKBAN: Rename semua folder
        // - KABID: Rename semua folder di bidangnya
        // - User lain: Rename folder sendiri saja
        return [
            'ADMIN can rename any folder' => ['ADMIN', 1, 1, true],
            'KABAN can rename any folder' => ['KABAN', 1, 2, true],
            'SEKBAN can rename any folder' => ['SEKBAN', 1, 2, true],
            'KABID can rename all in bidang' => ['KABID', 1, 1, true],
            'KABID cannot rename other bidang' => ['KABID', 2, 1, false],
            'KASUBAG can rename own only' => ['KASUBAG', 1, 1, true],
            'PELAKSANA can rename own only' => ['PELAKSANA', 1, 1, true],
            'JAFUNG can rename own only' => ['JAFUNG', 1, 1, true],
            'GATEK can rename own only' => ['GATEK', 1, 1, true],
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
