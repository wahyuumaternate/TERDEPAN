<?php

namespace Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        MasterJabatan::insert([
            ['id' => 1, 'kode' => 'ADMIN', 'nama' => 'Administrator', 'level' => 0],
        ]);

        MasterBidang::insert([
            ['id' => 1, 'kode' => 'APPS', 'nama' => 'Bidang Aplikasi dan Statistik'],
        ]);
    }

    protected function createUser(): User
    {
        $user = User::create([
            'nama' => 'User Test',
            'email' => 'dashboard-test@test.com',
            'password' => bcrypt('password'),
        ]);

        $user->profile()->create([
            'nomor_identitas' => '1990000000000001',
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => 1,
            'bidang_id' => 1,
        ]);

        return $user->fresh('profile');
    }

    public function test_guest_is_redirected_away_from_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_minimal_dashboard(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee($user->nama);
    }

    public function test_e_kinerja_alias_route_still_works(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('e-kinerja.index'));

        $response->assertStatus(200);
    }

    public function test_welcome_route_no_longer_exists(): void
    {
        $this->assertFalse(\Route::has('welcome'));
    }
}
