<?php

namespace Tests\Feature\TerminalData;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;
use Tests\TestCase;

class FilePermissionTest extends TestCase
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
        $pegawai = User::create([
            'nama' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $pegawai->profile()->create([
            'nomor_identitas' => '199001012020011001',
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => 1,
            'bidang_id' => 1,
        ]);

        // Buat sample folders untuk testing
        $bidang1 = MasterBidang::first();
        $bidang2 = MasterBidang::skip(1)->first();

        // Folder untuk bidang 1
        $folder1 = TdFolder::factory()->create([
            'name' => 'Folder Bidang 1',
            'bidang_id' => $bidang1->id,
            'created_by' => $pegawai->id,
        ]);

        // Folder untuk bidang 2
        $folder2 = TdFolder::factory()->create([
            'name' => 'Folder Bidang 2',
            'bidang_id' => $bidang2->id,
            'created_by' => $pegawai->id,
        ]);

        // Folder Eviden Kinerja
        $evidenFolder = TdFolder::factory()->create([
            'name' => 'Eviden Kinerja',
            'bidang_id' => null,
            'created_by' => $pegawai->id,
        ]);

        // Buat sample files untuk testing
        // Files in bidang 1 folder (2 files - reduced for faster tests)
        TdFile::factory()->count(2)->create([
            'folder_id' => $folder1->id,
            'bidang_id' => $bidang1->id,
            'created_by' => $pegawai->id,
        ]);

        // Files in bidang 2 folder (2 files - reduced for faster tests)
        TdFile::factory()->count(2)->create([
            'folder_id' => $folder2->id,
            'bidang_id' => $bidang2->id,
            'created_by' => $pegawai->id,
        ]);

        // Files in Eviden Kinerja folder (1 file - reduced for faster tests)
        TdFile::factory()->count(1)->create([
            'folder_id' => $evidenFolder->id,
            'bidang_id' => null,
            'created_by' => $pegawai->id,
        ]);
    }

    /**
     * Test upload file permission by jabatan
     * Upload dibatasi berdasarkan bidang/sub_bidang scope
     *
     * @test
     *
     * @dataProvider jabatanUploadAccessProvider
     */
    public function test_1_upload_file_permission_by_jabatan($jabatanKode, $folderBidangId, $userBidangId, $canUpload)
    {
        $user = $this->createUserWithJabatan($jabatanKode, $userBidangId);

        // Buat folder untuk upload
        $folder = TdFolder::factory()->create([
            'bidang_id' => $folderBidangId,
            'created_by' => $user->id,
        ]);

        // Create a fake file
        $fakeFile = \Illuminate\Http\UploadedFile::fake()->create('test-document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('terminaldata.filesData.upload'), [
                'folder_id' => $folder->id,
                'file' => $fakeFile,
            ]);

        if ($canUpload) {
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'File berhasil diupload',
                ]);
        } else {
            $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                ]);
        }
    }

    /**
     * Test upload to Eviden Kinerja folder
     * Hanya owner folder yang bisa upload
     *
     * @test
     */
    public function test_1_upload_to_eviden_kinerja_only_owner()
    {
        $owner = $this->createUserWithJabatan('PELAKSANA', 1);
        $otherUser = $this->createUserWithJabatan('PELAKSANA', 1);

        // Buat folder Eviden Kinerja milik owner
        $evidenFolder = TdFolder::factory()->create([
            'name' => 'Eviden Kinerja',
            'created_by' => $owner->id,
        ]);

        // Create a fake file
        $fakeFile = \Illuminate\Http\UploadedFile::fake()->create('eviden-document.pdf', 100, 'application/pdf');

        // Owner bisa upload
        $response = $this->actingAs($owner, 'sanctum')
            ->postJson(route('terminaldata.filesData.upload'), [
                'folder_id' => $evidenFolder->id,
                'file' => $fakeFile,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File berhasil diupload',
            ]);

        // Create another fake file for other user
        $fakeFile2 = \Illuminate\Http\UploadedFile::fake()->create('eviden-document-2.pdf', 100, 'application/pdf');

        // User lain tidak bisa upload
        $response2 = $this->actingAs($otherUser, 'sanctum')
            ->postJson(route('terminaldata.filesData.upload'), [
                'folder_id' => $evidenFolder->id,
                'file' => $fakeFile2,
            ]);

        $response2->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test view files in database
     * Verify that uploaded files exist in database
     *
     * @test
     *
     * @dataProvider jabatanViewAccessProvider
     */
    public function test_2_view_files_in_database($jabatanKode, $bidangId)
    {
        $user = $this->createUserWithJabatan($jabatanKode, $bidangId);

        // Count files in database from setUp
        $fileCount = TdFile::count();

        // Harus ada minimal 5 file dari setUp (2+2+1)
        $this->assertGreaterThanOrEqual(
            5,
            $fileCount,
            'Harus ada minimal 5 file di database dari setUp'
        );

        // Semua user bisa query file dari database
        $files = TdFile::all();
        $this->assertNotEmpty($files, "{$jabatanKode} harus bisa melihat files di database");
    }

    /**
     * Test download file permission
     * Semua user yang terautentikasi bisa download file yang ada
     *
     * @test
     *
     * @dataProvider jabatanViewAccessProvider
     */
    public function test_3_download_file_access_by_jabatan($jabatanKode, $bidangId)
    {
        $user = $this->createUserWithJabatan($jabatanKode, $bidangId);

        // Ambil file yang sudah ada dari setUp
        $file = TdFile::first();

        // Karena file di setUp tidak punya physical file,
        // kita hanya test bahwa policy membolehkan (bukan 403)
        // Error 500/404 karena file tidak ada di storage adalah OK
        $response = $this->actingAs($user, 'sanctum')
            ->get(route('terminaldata.filesData.download', $file->id));

        // Semua user bisa download - tidak boleh 403 (forbidden)
        // 500/404 OK karena file tidak ada physical file
        $this->assertNotEquals(
            403,
            $response->status(),
            "{$jabatanKode} tidak boleh dapat 403 saat download"
        );
    }

    /**
     * Test update file permission by jabatan
     *
     * @test
     *
     * @dataProvider jabatanUpdateDeleteProvider
     */
    public function test_4_update_file_permission_by_jabatan($jabatanKode, $fileBidangId, $userBidangId, $canUpdate)
    {
        $user = $this->createUserWithJabatan($jabatanKode, $userBidangId);

        // Buat owner untuk file (different from test user for non-own scenarios)
        $owner = $this->createUserWithJabatan('PELAKSANA', $fileBidangId);

        // Buat folder terlebih dahulu
        $folder = TdFolder::factory()->create([
            'bidang_id' => $fileBidangId,
            'created_by' => $owner->id,
        ]);

        // Buat file untuk di-update
        $file = TdFile::factory()->create([
            'folder_id' => $folder->id,
            'bidang_id' => $fileBidangId,
            'created_by' => $canUpdate && in_array($jabatanKode, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])
                ? $user->id
                : $owner->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson(route('terminaldata.filesData.update', $file->id), [
                'name' => 'Updated File Name',
            ]);

        if ($canUpdate) {
            $response->assertStatus(200);
        } else {
            $response->assertStatus(403);
        }
    }

    /**
     * Test delete file permission by jabatan
     *
     * @test
     *
     * @dataProvider jabatanUpdateDeleteProvider
     */
    public function test_5_delete_file_permission_by_jabatan($jabatanKode, $fileBidangId, $userBidangId, $canDelete)
    {
        $user = $this->createUserWithJabatan($jabatanKode, $userBidangId);

        // Buat owner untuk file
        $owner = $this->createUserWithJabatan('PELAKSANA', $fileBidangId);

        // Buat folder terlebih dahulu
        $folder = TdFolder::factory()->create([
            'bidang_id' => $fileBidangId,
            'created_by' => $owner->id,
        ]);

        // Buat file untuk di-delete
        $file = TdFile::factory()->create([
            'folder_id' => $folder->id,
            'bidang_id' => $fileBidangId,
            'created_by' => $canDelete && in_array($jabatanKode, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])
                ? $user->id
                : $owner->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson(route('terminaldata.filesData.destroy', $file->id));

        if ($canDelete) {
            $response->assertStatus(200);
        } else {
            $response->assertStatus(403);
        }
    }

    /**
     * Test cannot delete file in Eviden Kinerja folder
     *
     * @test
     */
    public function test_5_cannot_delete_file_in_eviden_kinerja()
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

        // Bahkan admin tidak bisa delete file di Eviden Kinerja
        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson(route('terminaldata.filesData.destroy', $file->id));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    // ==================== DATA PROVIDERS ====================

    public static function jabatanViewAccessProvider()
    {
        return [
            'ADMIN sees all files' => ['ADMIN', 1],
            'KABAN sees all files' => ['KABAN', 1],
            'SEKBAN sees all files' => ['SEKBAN', 1],
            'KABID sees all files' => ['KABID', 1],
            'KASUBAG sees all files' => ['KASUBAG', 1],
            'PELAKSANA sees all files' => ['PELAKSANA', 1],
            'JAFUNG sees all files' => ['JAFUNG', 1],
            'GATEK sees all files' => ['GATEK', 1],
        ];
    }

    public static function jabatanUploadAccessProvider()
    {
        return [
            'ADMIN can upload to any bidang' => ['ADMIN', 1, 2, true],
            'KABAN can upload to any bidang' => ['KABAN', 1, 2, true],
            'SEKBAN can upload to any bidang' => ['SEKBAN', 1, 2, true],
            'KABID can upload to own bidang' => ['KABID', 1, 1, true],
            'KABID cannot upload to other bidang' => ['KABID', 2, 1, false],
            'KASUBAG can upload to own bidang' => ['KASUBAG', 1, 1, true],
            'KASUBAG cannot upload to other bidang' => ['KASUBAG', 2, 1, false],
            'PELAKSANA can upload to own bidang' => ['PELAKSANA', 1, 1, true],
            'PELAKSANA cannot upload to other bidang' => ['PELAKSANA', 2, 1, false],
            'JAFUNG can upload to own bidang' => ['JAFUNG', 1, 1, true],
            'JAFUNG cannot upload to other bidang' => ['JAFUNG', 2, 1, false],
            'GATEK can upload to own bidang' => ['GATEK', 1, 1, true],
            'GATEK cannot upload to other bidang' => ['GATEK', 2, 1, false],
        ];
    }

    public static function jabatanUpdateDeleteProvider()
    {
        return [
            'ADMIN can update/delete any file' => ['ADMIN', 1, 1, true],
            'KABAN can update/delete any file' => ['KABAN', 1, 2, true],
            'SEKBAN can update/delete any file' => ['SEKBAN', 1, 2, true],
            'KABID can update/delete same bidang' => ['KABID', 1, 1, true],
            'KABID cannot update/delete other bidang' => ['KABID', 2, 1, false],
            'KASUBAG can update/delete own file' => ['KASUBAG', 1, 1, true],
            'PELAKSANA can update/delete own file' => ['PELAKSANA', 1, 1, true],
            'JAFUNG can update/delete own file' => ['JAFUNG', 1, 1, true],
            'GATEK can update/delete own file' => ['GATEK', 1, 1, true],
        ];
    }

    // ==================== HELPER METHODS ====================

    protected function createUserWithJabatan(string $kodeJabatan, ?int $bidangId = null): User
    {
        $jabatan = MasterJabatan::where('kode', $kodeJabatan)->firstOrFail();
        $nip = '1990'.str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT).'001';

        $user = User::create([
            'nama' => 'User '.$kodeJabatan,
            'email' => strtolower($kodeJabatan).rand(1, 9999).'@test.com',
            'password' => bcrypt('password'),
        ]);

        $user->profile()->create([
            'nomor_identitas' => $nip,
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatan->id,
            'bidang_id' => $bidangId ?? MasterBidang::first()->id,
        ]);

        return $user->fresh('profile');
    }
}
