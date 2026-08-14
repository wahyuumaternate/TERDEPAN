<?php

namespace Tests\Feature\Master;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\MasterSubBidang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kasubag Umum dan Kepegawaian sengaja diberi akses kelola Master Data Pegawai
 * (lihat UserPolicy::bolehKelolaMasterDataPegawai()) tanpa ikut mendapat akses
 * Master Data Bidang/Sub Bidang/Jabatan yang tetap khusus ADMIN/KABAN/SEKBAN.
 * Kasubag di sub bagian lain (mis. Keuangan) TIDAK ikut mendapat akses ini.
 */
class MasterPegawaiKasubagKepegawaianTest extends TestCase
{
    use RefreshDatabase;

    protected MasterBidang $sekretariat;

    protected MasterJabatan $jabatanKasubag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sekretariat = MasterBidang::create(['kode' => 'SEKRETARIAT', 'nama' => 'Sekretariat']);
        $this->jabatanKasubag = MasterJabatan::create(['kode' => 'KASUBAG', 'nama' => 'Kepala Sub Bagian', 'level' => 3]);
    }

    protected function buatKasubag(string $namaSubBidang): User
    {
        $subBidang = MasterSubBidang::create(['bidang_id' => $this->sekretariat->id, 'nama' => $namaSubBidang]);

        $user = User::create([
            'nama' => 'Kasubag '.$namaSubBidang,
            'email' => strtolower(str_replace(' ', '', $namaSubBidang)).'@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->profile()->create([
            'nomor_identitas' => '199001012020011001',
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => $this->jabatanKasubag->id,
            'bidang_id' => $this->sekretariat->id,
            'sub_bidang_id' => $subBidang->id,
        ]);

        return $user->fresh('profile');
    }

    public function test_kasubag_kepegawaian_bisa_melihat_daftar_pegawai(): void
    {
        $user = $this->buatKasubag('Sub Bagian Umum dan Kepegawaian');

        $this->actingAs($user)->get(route('master.pegawai.index'))->assertOk();
    }

    public function test_kasubag_kepegawaian_bisa_membuat_pegawai(): void
    {
        $user = $this->buatKasubag('Sub Bagian Umum dan Kepegawaian');

        $response = $this->actingAs($user)->post(route('master.pegawai.store'), [
            'nomor_identitas' => '199001012020011099',
            'tipe_identitas' => 'NIP',
            'nama' => 'Pegawai Baru',
            'jabatan_id' => $this->jabatanKasubag->id,
            'bidang_id' => $this->sekretariat->id,
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'email' => 'pegawaibaru@test.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertContains($response->status(), [200, 201, 302]);
        $this->assertDatabaseHas('users', ['email' => 'pegawaibaru@test.local']);
    }

    public function test_kasubag_kepegawaian_tidak_bisa_kelola_master_data_bidang(): void
    {
        $user = $this->buatKasubag('Sub Bagian Umum dan Kepegawaian');

        $this->actingAs($user)->get(route('master.bidang.index'))->assertForbidden();
        $this->actingAs($user)->post(route('master.bidang.store'), ['kode' => 'X', 'nama' => 'X'])->assertForbidden();
    }

    public function test_kasubag_sub_bagian_lain_tidak_bisa_kelola_pegawai(): void
    {
        $user = $this->buatKasubag('Sub Bagian Keuangan');

        $this->actingAs($user)->get(route('master.pegawai.index'))->assertForbidden();
    }

    public function test_kasubag_tanpa_sub_bidang_tidak_bisa_kelola_pegawai(): void
    {
        $user = User::create([
            'nama' => 'Kasubag Tanpa Sub Bidang',
            'email' => 'kasubag.kosong@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->profile()->create([
            'nomor_identitas' => '199001012020011002',
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => $this->jabatanKasubag->id,
            'bidang_id' => $this->sekretariat->id,
        ]);

        $this->actingAs($user->fresh('profile'))->get(route('master.pegawai.index'))->assertForbidden();
    }
}
