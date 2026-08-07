<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_wajib_ganti_password_diarahkan_ke_halaman_force_password(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('password.force'));
    }

    public function test_halaman_force_password_tetap_bisa_diakses_tanpa_redirect_loop(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $response = $this->actingAs($user)->get(route('password.force'));

        $response->assertStatus(200);
    }

    public function test_setelah_ganti_password_flag_hilang_dan_bisa_akses_halaman_lain(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'password',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
        $this->assertFalse($user->refresh()->must_change_password);

        $this->actingAs($user)->get('/')->assertStatus(200);
    }

    public function test_user_yang_sudah_ganti_password_tidak_diarahkan(): void
    {
        $user = User::factory()->create(); // default must_change_password = false

        $this->actingAs($user)->get('/')->assertStatus(200);
    }
}
