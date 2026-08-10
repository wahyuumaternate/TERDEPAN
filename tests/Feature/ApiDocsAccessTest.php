<?php

namespace Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDocsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        MasterJabatan::insert([
            ['id' => 1, 'kode' => 'ADMIN', 'nama' => 'Administrator', 'level' => 0],
            ['id' => 2, 'kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5],
        ]);

        MasterBidang::create(['kode' => 'APPS', 'nama' => 'Bidang Aplikasi dan Statistik']);
    }

    public function test_guest_gets_404_on_api_documentation(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertNotFound();
    }

    public function test_non_admin_gets_404_on_api_documentation(): void
    {
        $user = $this->createUserWithJabatan('PELAKSANA');

        $response = $this->actingAs($user)->get('/api/documentation');

        $response->assertNotFound();
    }

    public function test_admin_can_access_api_documentation(): void
    {
        $admin = $this->createUserWithJabatan('ADMIN');

        $response = $this->actingAs($admin)->get('/api/documentation');

        $response->assertOk();
    }

    protected function createUserWithJabatan(string $kodeJabatan): User
    {
        $jabatan = MasterJabatan::where('kode', $kodeJabatan)->firstOrFail();
        $nip = '1990'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT).'001';

        $user = User::create([
            'nama' => 'User '.$kodeJabatan,
            'email' => strtolower($kodeJabatan).rand(1, 9999).'@test.com',
            'password' => bcrypt('password'),
            'must_change_password' => false,
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
