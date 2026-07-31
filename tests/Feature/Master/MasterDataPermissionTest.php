<?php

namespace Tests\Feature\Master;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataPermissionTest extends TestCase
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
    }

    // ==================== BIDANG TESTS ====================

    /**
     * Test create bidang permission by jabatan
     * Hanya ADMIN, KABAN, SEKBAN yang bisa create
     *
     * @test
     *
     * @dataProvider jabatanMasterDataProvider
     */
    public function test_1_create_bidang_permission_by_jabatan($jabatanKode, $canManage)
    {
        $user = $this->createUserWithJabatan($jabatanKode);

        $response = $this->actingAs($user)
            ->post(route('master.bidang.store'), [
                'kode' => 'TEST',
                'nama' => 'Bidang Test',
            ]);

        if ($canManage) {
            $this->assertContains(
                $response->status(),
                [200, 201, 302],
                "{$jabatanKode} should be able to create bidang"
            );
        } else {
            $this->assertEquals(
                403,
                $response->status(),
                "{$jabatanKode} should NOT be able to create bidang"
            );
        }
    }

    /**
     * Test update bidang permission by jabatan
     *
     * @test
     *
     * @dataProvider jabatanMasterDataProvider
     */
    public function test_2_update_bidang_permission_by_jabatan($jabatanKode, $canManage)
    {
        $user = $this->createUserWithJabatan($jabatanKode);
        $bidang = MasterBidang::first();

        $response = $this->actingAs($user)
            ->put(route('master.bidang.update', $bidang->id), [
                'kode' => $bidang->kode,
                'nama' => 'Updated Bidang Name',
            ]);

        if ($canManage) {
            $this->assertContains(
                $response->status(),
                [200, 302],
                "{$jabatanKode} should be able to update bidang"
            );
        } else {
            $this->assertEquals(
                403,
                $response->status(),
                "{$jabatanKode} should NOT be able to update bidang"
            );
        }
    }

    /**
     * Test delete bidang permission by jabatan
     *
     * @test
     *
     * @dataProvider jabatanMasterDataProvider
     */
    public function test_3_delete_bidang_permission_by_jabatan($jabatanKode, $canManage)
    {
        $user = $this->createUserWithJabatan($jabatanKode);

        // Create new bidang for deletion with unique kode using microtime
        $bidang = MasterBidang::create([
            'kode' => 'DEL'.substr(str_replace('.', '', microtime(true)), 0, 17),
            'nama' => 'Bidang To Delete',
        ]);

        $response = $this->actingAs($user)
            ->delete(route('master.bidang.destroy', $bidang->id));

        if ($canManage) {
            $this->assertContains(
                $response->status(),
                [200, 302],
                "{$jabatanKode} should be able to delete bidang"
            );
        } else {
            $this->assertEquals(
                403,
                $response->status(),
                "{$jabatanKode} should NOT be able to delete bidang"
            );
        }
    }

    // ==================== JABATAN TESTS ====================

    /**
     * Test create jabatan permission by jabatan
     *
     * @test
     *
     * @dataProvider jabatanMasterDataProvider
     */
    public function test_4_create_jabatan_permission_by_jabatan($jabatanKode, $canManage)
    {
        $user = $this->createUserWithJabatan($jabatanKode);

        $response = $this->actingAs($user)
            ->post(route('master.jabatan.store'), [
                'kode' => 'TEST',
                'nama' => 'Jabatan Test',
                'level' => 10,
            ]);

        if ($canManage) {
            $this->assertContains(
                $response->status(),
                [200, 201, 302],
                "{$jabatanKode} should be able to create jabatan"
            );
        } else {
            $this->assertEquals(
                403,
                $response->status(),
                "{$jabatanKode} should NOT be able to create jabatan"
            );
        }
    }

    /**
     * Test update jabatan permission by jabatan
     *
     * @test
     *
     * @dataProvider jabatanMasterDataProvider
     */
    public function test_5_update_jabatan_permission_by_jabatan($jabatanKode, $canManage)
    {
        $user = $this->createUserWithJabatan($jabatanKode);
        $jabatan = MasterJabatan::where('kode', 'GATEK')->first();

        $response = $this->actingAs($user)
            ->put(route('master.jabatan.update', $jabatan->id), [
                'kode' => $jabatan->kode,
                'nama' => 'Updated Jabatan Name',
                'level' => $jabatan->level,
            ]);

        if ($canManage) {
            $this->assertContains(
                $response->status(),
                [200, 302],
                "{$jabatanKode} should be able to update jabatan"
            );
        } else {
            $this->assertEquals(
                403,
                $response->status(),
                "{$jabatanKode} should NOT be able to update jabatan"
            );
        }
    }

    /**
     * Test delete jabatan permission by jabatan
     *
     * @test
     *
     * @dataProvider jabatanMasterDataProvider
     */
    public function test_6_delete_jabatan_permission_by_jabatan($jabatanKode, $canManage)
    {
        $user = $this->createUserWithJabatan($jabatanKode);

        // Use existing jabatan instead of creating new ones to avoid ID conflict
        // Get last jabatan that's safe to test (GATEK, which won't have pegawai)
        $jabatan = MasterJabatan::where('kode', 'GATEK')->first();

        $response = $this->actingAs($user)
            ->delete(route('master.jabatan.destroy', $jabatan->id));

        if ($canManage) {
            // Authorized users can attempt deletion (may fail if has pegawai, but auth passes)
            $this->assertContains(
                $response->status(),
                [200, 302, 422], // 422 if has pegawai
                "{$jabatanKode} should be able to attempt delete jabatan"
            );
        } else {
            $this->assertEquals(
                403,
                $response->status(),
                "{$jabatanKode} should NOT be able to delete jabatan"
            );
        }
    }

    // ==================== PEGAWAI TESTS ====================

    /**
     * Test create pegawai permission by jabatan
     *
     * @test
     *
     * @dataProvider jabatanMasterDataProvider
     */
    public function test_7_create_pegawai_permission_by_jabatan($jabatanKode, $canManage)
    {
        $user = $this->createUserWithJabatan($jabatanKode);

        $response = $this->actingAs($user)
            ->post(route('master.pegawai.store'), [
                'nomor_identitas' => '199001012020011099',
                'tipe_identitas' => 'NIP',
                'nama' => 'Test Pegawai',
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => 'PNS',
                'email' => 'testpegawai'.rand(1000, 9999).'@test.com',
                'password' => 'password123',
                'jabatan_id' => 6,
                'bidang_id' => 1,
            ]);

        if ($canManage) {
            $this->assertContains(
                $response->status(),
                [200, 201, 302],
                "{$jabatanKode} should be able to create pegawai"
            );
        } else {
            $this->assertEquals(
                403,
                $response->status(),
                "{$jabatanKode} should NOT be able to create pegawai"
            );
        }
    }

    /**
     * Test update pegawai permission by jabatan
     *
     * @test
     *
     * @dataProvider jabatanMasterDataProvider
     */
    public function test_8_update_pegawai_permission_by_jabatan($jabatanKode, $canManage)
    {
        $user = $this->createUserWithJabatan($jabatanKode);

        // Create pegawai to update
        $pegawai = User::create([
            'nama' => 'Pegawai To Update',
            'email' => 'update'.rand(1000, 9999).'@test.com',
            'password' => bcrypt('password'),
        ]);
        $pegawai->profile()->create([
            'nomor_identitas' => '199001012020011098',
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => 6,
            'bidang_id' => 1,
        ]);
        $pegawai->load('profile');

        $response = $this->actingAs($user)
            ->put(route('master.pegawai.update', $pegawai->id), [
                'nomor_identitas' => $pegawai->profile->nomor_identitas,
                'tipe_identitas' => $pegawai->profile->tipe_identitas,
                'nama' => 'Updated Pegawai Name',
                'jenis_kelamin' => $pegawai->profile->jenis_kelamin,
                'status_kepegawaian' => $pegawai->profile->status_kepegawaian,
                'email' => $pegawai->email,
                'jabatan_id' => $pegawai->profile->jabatan_id,
                'bidang_id' => $pegawai->profile->bidang_id,
            ]);

        if ($canManage) {
            $this->assertContains(
                $response->status(),
                [200, 302],
                "{$jabatanKode} should be able to update pegawai"
            );
        } else {
            $this->assertEquals(
                403,
                $response->status(),
                "{$jabatanKode} should NOT be able to update pegawai"
            );
        }
    }

    /**
     * Test delete pegawai permission by jabatan
     *
     * @test
     *
     * @dataProvider jabatanMasterDataProvider
     */
    public function test_9_delete_pegawai_permission_by_jabatan($jabatanKode, $canManage)
    {
        $user = $this->createUserWithJabatan($jabatanKode);

        // Create pegawai for deletion with unique identifiers
        $microtime = str_replace('.', '', microtime(true));
        $pegawai = User::create([
            'nama' => 'Pegawai To Delete',
            'email' => 'del'.$microtime.'@test.com',
            'password' => bcrypt('password'),
        ]);
        $pegawai->profile()->create([
            'nomor_identitas' => substr($microtime, 0, 18),
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => 6,
            'bidang_id' => 1,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('master.pegawai.destroy', $pegawai->id));

        if ($canManage) {
            $this->assertContains(
                $response->status(),
                [200, 302],
                "{$jabatanKode} should be able to delete pegawai"
            );
        } else {
            $this->assertEquals(
                403,
                $response->status(),
                "{$jabatanKode} should NOT be able to delete pegawai"
            );
        }
    }

    // ==================== VIEW TESTS ====================

    /**
     * Test hanya ADMIN, KABAN, SEKBAN yang bisa view master data
     *
     * @test
     *
     * @dataProvider jabatanMasterDataProvider
     */
    public function test_10_view_master_data_permission($jabatanKode, $canManage)
    {
        $user = $this->createUserWithJabatan($jabatanKode);

        // Test view bidang index
        $response = $this->actingAs($user)->get(route('master.bidang.index'));
        if ($canManage) {
            $response->assertStatus(200);
        } else {
            $this->assertContains($response->status(), [403, 302, 500]);
        }

        // Test view jabatan index
        $response = $this->actingAs($user)->get(route('master.jabatan.index'));
        if ($canManage) {
            $response->assertStatus(200);
        } else {
            $this->assertContains($response->status(), [403, 302, 500]);
        }

        // Test view pegawai index
        $response = $this->actingAs($user)->get(route('master.pegawai.index'));
        if ($canManage) {
            $response->assertStatus(200);
        } else {
            $this->assertContains($response->status(), [403, 302, 500]);
        }
    }

    // ==================== DATA PROVIDERS ====================

    /**
     * Data provider untuk test master data permission
     * Format: [$jabatanKode, $canManage]
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public static function jabatanMasterDataProvider()
    {
        return [
            'ADMIN can manage master data' => ['ADMIN', true],
            'KABAN can manage master data' => ['KABAN', true],
            'SEKBAN can manage master data' => ['SEKBAN', true],
            'KABID cannot manage master data' => ['KABID', false],
            'KASUBAG cannot manage master data' => ['KASUBAG', false],
            'PELAKSANA cannot manage master data' => ['PELAKSANA', false],
            'JAFUNG cannot manage master data' => ['JAFUNG', false],
            'GATEK cannot manage master data' => ['GATEK', false],
        ];
    }

    // ==================== HELPER METHODS ====================

    protected function createUserWithJabatan(string $kodeJabatan): User
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
            'bidang_id' => MasterBidang::first()->id,
        ]);

        return $user->fresh('profile');
    }
}
