<?php

namespace Tests\Feature\Api;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreApiTest extends TestCase
{
    use RefreshDatabase;

    protected MasterJabatan $jabatan;

    protected MasterBidang $bidang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jabatan = MasterJabatan::create([
            'kode' => 'ADMIN',
            'nama' => 'Administrator',
            'level' => 1,
        ]);

        $this->bidang = MasterBidang::create([
            'kode' => 'TEST',
            'nama' => 'Bidang Test',
        ]);
    }

    protected function createUserWithProfile(): User
    {
        $user = User::create([
            'nama' => 'Pegawai Test',
            'email' => 'pegawai@test.com',
            'password' => bcrypt('password'),
        ]);

        $user->profile()->create([
            'nomor_identitas' => '199001012020011001',
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => $this->jabatan->id,
            'bidang_id' => $this->bidang->id,
        ]);

        return $user->fresh('profile');
    }

    public function test_login_succeeds_with_correct_email_and_password(): void
    {
        $this->createUserWithProfile();

        $response = $this->postJson('/api/v1/login', [
            'email' => 'pegawai@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['data' => ['access_token', 'token_type', 'user']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->createUserWithProfile();

        $response = $this->postJson('/api/v1/login', [
            'email' => 'pegawai@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    public function test_authenticated_user_can_crud_users(): void
    {
        $actor = $this->createUserWithProfile();

        // Index
        $this->actingAs($actor, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertStatus(200)
            ->assertJsonPath('status', true);

        // Store
        $storeResponse = $this->actingAs($actor, 'sanctum')
            ->postJson('/api/v1/users', [
                'nomor_identitas' => '199002022020022002',
                'tipe_identitas' => 'NIP',
                'nama' => 'Pegawai Baru',
                'jabatan_id' => $this->jabatan->id,
                'bidang_id' => $this->bidang->id,
                'jenis_kelamin' => 'P',
                'status_kepegawaian' => 'PNS',
                'email' => 'baru@test.com',
                'password' => 'password',
            ]);

        $storeResponse->assertStatus(201)->assertJsonPath('status', true);
        $newUserId = $storeResponse->json('data.id');

        // Show
        $this->actingAs($actor, 'sanctum')
            ->getJson("/api/v1/users/{$newUserId}")
            ->assertStatus(200)
            ->assertJsonPath('data.email', 'baru@test.com');

        // Update
        $this->actingAs($actor, 'sanctum')
            ->putJson("/api/v1/users/{$newUserId}", [
                'nama' => 'Pegawai Diupdate',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.nama', 'Pegawai Diupdate');

        // Destroy
        $this->actingAs($actor, 'sanctum')
            ->deleteJson("/api/v1/users/{$newUserId}")
            ->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_authenticated_user_can_crud_master_sub_bidang(): void
    {
        $actor = $this->createUserWithProfile();

        $storeResponse = $this->actingAs($actor, 'sanctum')
            ->postJson('/api/v1/master-sub-bidang', [
                'bidang_id' => $this->bidang->id,
                'nama' => 'Sub Bidang Test',
            ]);

        $storeResponse->assertStatus(201)->assertJsonPath('status', true);
        $subBidangId = $storeResponse->json('data.id');

        $this->actingAs($actor, 'sanctum')
            ->getJson('/api/v1/master-sub-bidang')
            ->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->actingAs($actor, 'sanctum')
            ->putJson("/api/v1/master-sub-bidang/{$subBidangId}", [
                'nama' => 'Sub Bidang Diupdate',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.nama', 'Sub Bidang Diupdate');

        $this->actingAs($actor, 'sanctum')
            ->deleteJson("/api/v1/master-sub-bidang/{$subBidangId}")
            ->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_me_endpoint_returns_authenticated_user(): void
    {
        $actor = $this->createUserWithProfile();

        $this->actingAs($actor, 'sanctum')
            ->getJson('/api/v1/me')
            ->assertStatus(200)
            ->assertJsonPath('data.email', 'pegawai@test.com');
    }

    public function test_unauthenticated_request_gets_json_401_instead_of_login_redirect(): void
    {
        // Simulates hitting the API directly in a browser (Accept: text/html),
        // which previously redirected to the login page (302 -> 200) instead
        // of returning a proper unauthenticated error.
        $response = $this->get('/api/v1/users');

        $response->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    public function test_unauthenticated_json_request_gets_401(): void
    {
        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(401)
            ->assertJsonPath('status', false);
    }
}
