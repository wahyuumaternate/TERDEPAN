<?php

namespace Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappTestPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        $bidang = MasterBidang::create(['kode' => 'APPS', 'nama' => 'Bidang Aplikasi']);
        $jabatanAdmin = MasterJabatan::create(['kode' => 'ADMIN', 'nama' => 'Admin', 'level' => 1]);
        $jabatanPelaksana = MasterJabatan::create(['kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5]);

        $this->admin = User::create(['nama' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
        $this->admin->profile()->create([
            'nomor_identitas' => '1', 'tipe_identitas' => 'NIP', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatanAdmin->id, 'bidang_id' => $bidang->id,
        ]);

        $this->pegawai = User::create(['nama' => 'Pegawai', 'email' => 'pegawai@test.com', 'password' => bcrypt('password')]);
        $this->pegawai->profile()->create([
            'nomor_identitas' => '2', 'tipe_identitas' => 'NIP', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatanPelaksana->id, 'bidang_id' => $bidang->id,
            'no_telepon' => '081234567890',
        ]);
    }

    public function test_admin_bisa_akses_halaman_testing_whatsapp(): void
    {
        $response = $this->actingAs($this->admin)->get(route('testing.whatsapp'));

        $response->assertOk();
        $response->assertSee('Pegawai');
    }

    public function test_pegawai_biasa_tidak_bisa_akses_halaman_testing_whatsapp(): void
    {
        $response = $this->actingAs($this->pegawai)->get(route('testing.whatsapp'));

        $response->assertForbidden();
    }

    public function test_kirim_tanpa_kredensial_twilio_menampilkan_error_bukan_crash(): void
    {
        config(['services.twilio.sid' => null]);

        $response = $this->actingAs($this->admin)->post(route('testing.whatsapp.send'), [
            'nomor_tujuan' => '081234567890',
            'pesan' => 'Halo, ini pesan uji.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_pegawai_biasa_tidak_bisa_mengirim_whatsapp(): void
    {
        $response = $this->actingAs($this->pegawai)->post(route('testing.whatsapp.send'), [
            'nomor_tujuan' => '081234567890',
            'pesan' => 'Halo',
        ]);

        $response->assertForbidden();
    }

    public function test_validasi_field_wajib(): void
    {
        $response = $this->actingAs($this->admin)->post(route('testing.whatsapp.send'), []);

        $response->assertSessionHasErrors(['nomor_tujuan', 'pesan']);
    }
}
