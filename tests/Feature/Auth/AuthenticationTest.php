<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_privacy_policy_can_be_rendered_by_guests(): void
    {
        $response = $this->get(route('privacy-policy'));

        $response->assertOk()
            ->assertSee('Kebijakan Privasi')
            ->assertSee('WhatsApp Cloud API')
            ->assertSee(route('privacy-policy', absolute: false));
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_session_bertahan_7_hari_jika_ingat_saya_aktif(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $this->assertAuthenticated();

        $cookie = $response->headers->getCookies()[array_search(
            config('session.cookie'),
            array_map(fn ($c) => $c->getName(), $response->headers->getCookies())
        )];

        $sisaDetik = $cookie->getExpiresTime() - time();

        // Toleransi beberapa detik dari waktu eksekusi test, harus mendekati 7 hari
        // (604800 detik), jauh lebih besar dari default lama (120 menit).
        $this->assertGreaterThan(60 * 60 * 24 * 6, $sisaDetik);
        $this->assertLessThanOrEqual(60 * 60 * 24 * 7, $sisaDetik);
    }

    public function test_session_hanya_bertahan_1_hari_jika_ingat_saya_tidak_aktif(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        $cookie = $response->headers->getCookies()[array_search(
            config('session.cookie'),
            array_map(fn ($c) => $c->getName(), $response->headers->getCookies())
        )];

        $sisaDetik = $cookie->getExpiresTime() - time();

        $this->assertGreaterThan(60 * 60 * 23, $sisaDetik);
        $this->assertLessThanOrEqual(60 * 60 * 24, $sisaDetik);
    }

    public function test_session_tetap_7_hari_di_request_berikutnya_setelah_login(): void
    {
        // Bukan cuma di response login — cookie remember yang sudah dikirim balik oleh
        // browser di request-request SETELAHNYA juga harus tetap membuat sesi diperbarui
        // dengan masa berlaku 7 hari, bukan kembali ke default.
        $user = User::factory()->create();

        $loginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $recallerCookie = collect($loginResponse->headers->getCookies())
            ->first(fn ($c) => str_starts_with($c->getName(), 'remember_web_'));

        $this->assertNotNull($recallerCookie);

        $response = $this->withCookie($recallerCookie->getName(), $recallerCookie->getValue())
            ->get('/');

        $cookie = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === config('session.cookie'));

        $sisaDetik = $cookie->getExpiresTime() - time();

        $this->assertGreaterThan(60 * 60 * 24 * 6, $sisaDetik);
        $this->assertLessThanOrEqual(60 * 60 * 24 * 7, $sisaDetik);
    }
}
