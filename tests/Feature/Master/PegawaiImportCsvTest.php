<?php

namespace Tests\Feature\Master;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PegawaiImportCsvTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        MasterJabatan::create(['id' => 1, 'kode' => 'ADMIN', 'nama' => 'Admin', 'level' => 1]);
        MasterJabatan::create(['id' => 2, 'kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5]);
        $bidang = MasterBidang::create(['kode' => 'SEKRE', 'nama' => 'Sekretariat']);

        $this->admin = User::factory()->create(['nama' => 'Admin Test']);
        $this->admin->profile()->create([
            'nomor_identitas' => 'ADMIN001',
            'tipe_identitas' => 'ID',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => 1,
            'bidang_id' => $bidang->id,
        ]);
        $this->admin->refresh()->load('profile');
    }

    private function buatCsv(string $isi): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('pegawai.csv', $isi);
    }

    public function test_import_csv_membuat_pegawai_dan_link_atasan(): void
    {
        $header = 'Nama;Email;Jabatan;Bidang;NIP / ID;Gelar Depan;Gelar Belakang;No Telpon;Jenis Kelamin;Tanggal Lahir;Alamat;Status Kepeg;Status Aktif;Pangkat;Golongan;Tanggal Masuk;Atasan Langsung';
        $baris1 = 'Budi Santoso;budi@test.local;Pelaksana;Sekretariat;198001012020011001;;;08123;L;01-01-1980;;PNS;Aktif;;;01-01-2020;Siti Aminah';
        $baris2 = 'Siti Aminah;siti@test.local;Pelaksana;Sekretariat;198002022020011002;;;08124;P;02-02-1980;;PNS;Aktif;;;01-01-2020;';
        $csv = implode("\n", [$header, $baris1, $baris2]);

        $response = $this->actingAs($this->admin)
            ->post(route('master.pegawai.import.store'), ['file_csv' => $this->buatCsv($csv)]);

        $response->assertRedirect(route('master.pegawai.index'));

        $budi = User::where('email', 'budi@test.local')->with('profile')->first();
        $siti = User::where('email', 'siti@test.local')->first();

        $this->assertNotNull($budi);
        $this->assertNotNull($siti);
        $this->assertTrue($budi->must_change_password);
        $this->assertSame($siti->id, $budi->profile->atasan_langsung_id);
    }

    public function test_baris_dengan_jabatan_tidak_dikenali_dilewati(): void
    {
        $header = 'Nama;Email;Jabatan;Bidang;NIP / ID;Gelar Depan;Gelar Belakang;No Telpon;Jenis Kelamin;Tanggal Lahir;Alamat;Status Kepeg;Status Aktif;Pangkat;Golongan;Tanggal Masuk;Atasan Langsung';
        $barisRusak = 'Anonim;anonim@test.local;Jabatan Aneh;Bidang Aneh;199001012020011003;;;08125;L;;;PNS;Aktif;;;;';
        $csv = implode("\n", [$header, $barisRusak]);

        $this->actingAs($this->admin)
            ->post(route('master.pegawai.import.store'), ['file_csv' => $this->buatCsv($csv)]);

        $this->assertDatabaseMissing('users', ['email' => 'anonim@test.local']);
    }

    public function test_bukan_admin_tidak_bisa_import(): void
    {
        $bidang = MasterBidang::firstOrCreate(['kode' => 'X'], ['nama' => 'X']);
        $pegawaiBiasa = User::factory()->create();
        $pegawaiBiasa->profile()->create([
            'nomor_identitas' => '111', 'tipe_identitas' => 'NIP', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif', 'jabatan_id' => 2,
            'bidang_id' => $bidang->id,
        ]);

        $response = $this->actingAs($pegawaiBiasa)->get(route('master.pegawai.import'));

        $response->assertStatus(403);
    }
}
