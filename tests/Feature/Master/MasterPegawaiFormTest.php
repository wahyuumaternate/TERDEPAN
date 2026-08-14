<?php

namespace Tests\Feature\Master;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\MasterSubBidang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Perbaikan halaman show-edit-pegawai: (1) field Sub Bidang cuma tampil untuk pegawai
 * di Bidang yang punya sub bidang (Sekretariat), (2) opsi Atasan Langsung disesuaikan
 * dengan jabatan & bidang pegawai (pakai Modules\Penugasan\Services\AtasanMandiriEligibility),
 * bukan lagi daftar semua pegawai.
 */
class MasterPegawaiFormTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected MasterBidang $sekretariat;

    protected MasterBidang $ipw;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sekretariat = MasterBidang::create(['kode' => 'SEKRETARIAT', 'nama' => 'Sekretariat']);
        $this->ipw = MasterBidang::create(['kode' => 'IPW', 'nama' => 'Bidang Infrastruktur']);

        foreach (['ADMIN', 'KABAN', 'SEKBAN', 'KABID', 'KASUBAG', 'JAFUNG', 'PELAKSANA', 'GATEK'] as $kode) {
            MasterJabatan::create(['kode' => $kode, 'nama' => $kode, 'level' => 1]);
        }

        $this->admin = $this->buatPegawai('ADMIN', $this->sekretariat);
    }

    protected function buatPegawai(string $kodeJabatan, MasterBidang $bidang, ?MasterSubBidang $subBidang = null): User
    {
        $jabatan = MasterJabatan::where('kode', $kodeJabatan)->first();

        $user = User::create([
            'nama' => $kodeJabatan.' '.$bidang->kode.' '.uniqid(),
            'email' => strtolower($kodeJabatan.uniqid()).'@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->profile()->create([
            'nomor_identitas' => (string) rand(100000000, 999999999),
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatan->id,
            'bidang_id' => $bidang->id,
            'sub_bidang_id' => $subBidang?->id,
        ]);

        return $user->fresh('profile');
    }

    public function test_field_sub_bidang_tampil_untuk_pegawai_sekretariat(): void
    {
        MasterSubBidang::create(['bidang_id' => $this->sekretariat->id, 'nama' => 'Sub Bagian Umum dan Kepegawaian']);
        $pegawai = $this->buatPegawai('PELAKSANA', $this->sekretariat);

        $response = $this->actingAs($this->admin)->get(route('master.pegawai.show', $pegawai->id));

        $response->assertOk();
        $response->assertSee('id="subBidangField"', false);
        $response->assertDontSee('mb-3 d-none"', false);
    }

    public function test_field_sub_bidang_tersembunyi_untuk_pegawai_non_sekretariat(): void
    {
        $pegawai = $this->buatPegawai('PELAKSANA', $this->ipw);

        $response = $this->actingAs($this->admin)->get(route('master.pegawai.show', $pegawai->id));

        $response->assertOk();
        $response->assertSee('mb-3 d-none"', false);
        $response->assertSee('id="subBidangField"', false);
    }

    public function test_kandidat_atasan_pelaksana_non_sekretariat_hanya_jafung_kabid_bidang_sendiri_dan_kaban(): void
    {
        $kaban = $this->buatPegawai('KABAN', $this->sekretariat);
        $kabidIpw = $this->buatPegawai('KABID', $this->ipw);
        $kabidLain = $this->buatPegawai('KABID', $this->sekretariat);
        $pelaksanaLain = $this->buatPegawai('PELAKSANA', $this->ipw);
        $pegawai = $this->buatPegawai('PELAKSANA', $this->ipw);

        $response = $this->actingAs($this->admin)->get(route('master.pegawai.show', $pegawai->id));

        $response->assertOk();
        $response->assertSee($kaban->nama);
        $response->assertSee($kabidIpw->nama);
        $response->assertDontSee($kabidLain->nama);
        $response->assertDontSee($pelaksanaLain->nama);
    }

    public function test_kandidat_atasan_pelaksana_sekretariat_hanya_kasubag_sekban_kaban(): void
    {
        $kaban = $this->buatPegawai('KABAN', $this->sekretariat);
        $kasubag = $this->buatPegawai('KASUBAG', $this->sekretariat);
        $kabidIpw = $this->buatPegawai('KABID', $this->ipw);
        $pegawai = $this->buatPegawai('PELAKSANA', $this->sekretariat);

        $response = $this->actingAs($this->admin)->get(route('master.pegawai.show', $pegawai->id));

        $response->assertOk();
        $response->assertSee($kaban->nama);
        $response->assertSee($kasubag->nama);
        $response->assertDontSee($kabidIpw->nama);
    }

    public function test_kaban_tidak_punya_kandidat_atasan(): void
    {
        $pegawai = $this->buatPegawai('KABAN', $this->sekretariat);

        $response = $this->actingAs($this->admin)->get(route('master.pegawai.show', $pegawai->id));

        $response->assertOk();
        $response->assertSee('Tidak ada kandidat atasan');
    }

    public function test_update_menyimpan_sub_bidang_id_untuk_pegawai_sekretariat(): void
    {
        $subBidang = MasterSubBidang::create(['bidang_id' => $this->sekretariat->id, 'nama' => 'Sub Bagian Keuangan']);
        $pegawai = $this->buatPegawai('PELAKSANA', $this->sekretariat);

        $response = $this->actingAs($this->admin)->put(route('master.pegawai.update', $pegawai->id), [
            'nomor_identitas' => $pegawai->profile->nomor_identitas,
            'tipe_identitas' => 'NIP',
            'nama' => $pegawai->nama,
            'jabatan_id' => $pegawai->profile->jabatan_id,
            'bidang_id' => $this->sekretariat->id,
            'sub_bidang_id' => $subBidang->id,
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'email' => $pegawai->email,
        ]);

        $response->assertRedirect(route('master.pegawai.show', $pegawai->id));
        $this->assertSame($subBidang->id, $pegawai->profile->fresh()->sub_bidang_id);
    }

    public function test_update_menolak_sub_bidang_yang_tidak_sesuai_bidang(): void
    {
        $subBidangSekretariat = MasterSubBidang::create(['bidang_id' => $this->sekretariat->id, 'nama' => 'Sub Bagian Keuangan']);
        $pegawai = $this->buatPegawai('PELAKSANA', $this->ipw);

        $response = $this->actingAs($this->admin)->put(route('master.pegawai.update', $pegawai->id), [
            'nomor_identitas' => $pegawai->profile->nomor_identitas,
            'tipe_identitas' => 'NIP',
            'nama' => $pegawai->nama,
            'jabatan_id' => $pegawai->profile->jabatan_id,
            'bidang_id' => $this->ipw->id,
            'sub_bidang_id' => $subBidangSekretariat->id,
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'email' => $pegawai->email,
        ]);

        $response->assertSessionHasErrors('sub_bidang_id');
    }
}
