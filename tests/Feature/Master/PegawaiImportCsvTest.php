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
        $bidang = MasterBidang::create(['kode' => 'SEKRETARIAT', 'nama' => 'Sekretariat']);

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

        $response->assertRedirect(route('master.pegawai.import'));
        $response->assertSessionHas('success');

        $budi = User::where('email', 'budi@test.local')->with('profile')->first();
        $siti = User::where('email', 'siti@test.local')->first();

        $this->assertNotNull($budi);
        $this->assertNotNull($siti);
        $this->assertTrue($budi->must_change_password);
        $this->assertSame($siti->id, $budi->profile->atasan_langsung_id);
    }

    public function test_import_csv_dengan_kolom_duk_opsional(): void
    {
        $header = 'Nama;Email;Jabatan;Bidang;NIP / ID;Gelar Depan;Gelar Belakang;No Telpon;Jenis Kelamin;Tanggal Lahir;Alamat;Status Kepeg;Status Aktif;Pangkat;Golongan;Tanggal Masuk;Atasan Langsung;Tempat Lahir;Agama;TMT CPNS;TMT PNS;TMT Golongan;Eselon;Pendidikan Terakhir;Jenjang Pendidikan';
        $baris = 'Iksan;iksan@test.local;Pelaksana;Sekretariat;197906102005011017;Dr;SE, M.Si;;L;10-06-1979;;PNS;Aktif;;;01-01-2005;;PULAU MAKIAN;ISLAM;01-01-2005;01-11-2006;01-04-2020;II/b;S-3 DOKTOR;Pasca Sarja (S.3)';
        $csv = implode("\n", [$header, $baris]);

        $this->actingAs($this->admin)
            ->post(route('master.pegawai.import.store'), ['file_csv' => $this->buatCsv($csv)]);

        $iksan = User::where('email', 'iksan@test.local')->with('profile')->first();

        $this->assertNotNull($iksan);
        $this->assertSame('PULAU MAKIAN', $iksan->profile->tempat_lahir);
        $this->assertSame('ISLAM', $iksan->profile->agama);
        $this->assertSame('II/b', $iksan->profile->eselon);
        $this->assertSame('S-3 DOKTOR', $iksan->profile->pendidikan_terakhir);
        $this->assertSame('Pasca Sarja (S.3)', $iksan->profile->jenjang_pendidikan);
        $this->assertSame('2005-01-01', $iksan->profile->tmt_cpns->toDateString());
        $this->assertSame('2006-11-01', $iksan->profile->tmt_pns->toDateString());
        $this->assertSame('2020-04-01', $iksan->profile->tmt_golongan->toDateString());
    }

    public function test_baris_dengan_bidang_tidak_dikenali_dilewati(): void
    {
        $header = 'Nama;Email;Jabatan;Bidang;NIP / ID;Gelar Depan;Gelar Belakang;No Telpon;Jenis Kelamin;Tanggal Lahir;Alamat;Status Kepeg;Status Aktif;Pangkat;Golongan;Tanggal Masuk;Atasan Langsung';
        $barisRusak = 'Anonim;anonim@test.local;Jabatan Aneh;Bidang Aneh;199001012020011003;;;08125;L;;;PNS;Aktif;;;;';
        $csv = implode("\n", [$header, $barisRusak]);

        $this->actingAs($this->admin)
            ->post(route('master.pegawai.import.store'), ['file_csv' => $this->buatCsv($csv)]);

        $this->assertDatabaseMissing('users', ['email' => 'anonim@test.local']);
    }

    public function test_jabatan_yang_tidak_dikenali_default_pelaksana_bukan_dilewati(): void
    {
        // Judul jabatan literal dari sumber data asli (mis. DUK: "PENELAAH TEKNIS
        // KEBIJAKAN") tidak boleh bikin baris dilewati — harus tetap masuk sebagai
        // PELAKSANA (akses minim), dengan judul aslinya tersimpan di jabatan_asli.
        $header = 'Nama;Email;Jabatan;Bidang;NIP / ID;Gelar Depan;Gelar Belakang;No Telpon;Jenis Kelamin;Tanggal Lahir;Alamat;Status Kepeg;Status Aktif;Pangkat;Golongan;Tanggal Masuk;Atasan Langsung';
        $baris = 'Rosihan Thamrin;rosihan@test.local;PENELAAH TEKNIS KEBIJAKAN;Sekretariat;198001012020011099;;;;L;;;PNS;Aktif;;;;';
        $csv = implode("\n", [$header, $baris]);

        $this->actingAs($this->admin)
            ->post(route('master.pegawai.import.store'), ['file_csv' => $this->buatCsv($csv)]);

        $pegawai = User::where('email', 'rosihan@test.local')->with('profile.jabatan')->first();

        $this->assertNotNull($pegawai);
        $this->assertSame('PELAKSANA', $pegawai->profile->jabatan->kode);
        $this->assertSame('PENELAAH TEKNIS KEBIJAKAN', $pegawai->profile->jabatan_asli);
    }

    public function test_bidang_kosong_default_sekretariat(): void
    {
        $header = 'Nama;Email;Jabatan;Bidang;NIP / ID;Gelar Depan;Gelar Belakang;No Telpon;Jenis Kelamin;Tanggal Lahir;Alamat;Status Kepeg;Status Aktif;Pangkat;Golongan;Tanggal Masuk;Atasan Langsung';
        $baris = 'Tanpa Bidang;tanpabidang@test.local;Pelaksana;;198001012020011098;;;;L;;;PNS;Aktif;;;;';
        $csv = implode("\n", [$header, $baris]);

        $this->actingAs($this->admin)
            ->post(route('master.pegawai.import.store'), ['file_csv' => $this->buatCsv($csv)]);

        $pegawai = User::where('email', 'tanpabidang@test.local')->with('profile.bidang')->first();

        $this->assertNotNull($pegawai);
        $this->assertSame('Sekretariat', $pegawai->profile->bidang->nama);
    }

    public function test_nama_bidang_dicocokkan_case_insensitive(): void
    {
        $header = 'Nama;Email;Jabatan;Bidang;NIP / ID;Gelar Depan;Gelar Belakang;No Telpon;Jenis Kelamin;Tanggal Lahir;Alamat;Status Kepeg;Status Aktif;Pangkat;Golongan;Tanggal Masuk;Atasan Langsung';
        $baris = 'Beda Kapital;bedakapital@test.local;Pelaksana;sekretariat;198001012020011097;;;;L;;;PNS;Aktif;;;;';
        $csv = implode("\n", [$header, $baris]);

        $this->actingAs($this->admin)
            ->post(route('master.pegawai.import.store'), ['file_csv' => $this->buatCsv($csv)]);

        $pegawai = User::where('email', 'bedakapital@test.local')->with('profile.bidang')->first();

        $this->assertNotNull($pegawai);
        $this->assertSame('Sekretariat', $pegawai->profile->bidang->nama);
    }

    public function test_status_kepegawaian_cpns_diterima(): void
    {
        $header = 'Nama;Email;Jabatan;Bidang;NIP / ID;Gelar Depan;Gelar Belakang;No Telpon;Jenis Kelamin;Tanggal Lahir;Alamat;Status Kepeg;Status Aktif;Pangkat;Golongan;Tanggal Masuk;Atasan Langsung';
        $baris = 'Calon Pegawai;calonpegawai@test.local;Pelaksana;Sekretariat;198001012020011096;;;;L;;;CPNS;Aktif;;;;';
        $csv = implode("\n", [$header, $baris]);

        $this->actingAs($this->admin)
            ->post(route('master.pegawai.import.store'), ['file_csv' => $this->buatCsv($csv)]);

        $pegawai = User::where('email', 'calonpegawai@test.local')->with('profile')->first();

        $this->assertNotNull($pegawai);
        $this->assertSame('CPNS', $pegawai->profile->status_kepegawaian);
    }

    public function test_csv_dengan_bom_utf8_tetap_terbaca(): void
    {
        // Regresi: file CSV yang disimpan dari Excel sering diawali BOM UTF-8 (EF BB BF),
        // yang kalau tidak dilewati akan menempel di nama kolom header pertama sehingga
        // $row['Nama'] selalu kosong dan SEMUA baris dianggap "Kolom Nama kosong".
        $header = 'Nama;Email;Jabatan;Bidang;NIP / ID;Gelar Depan;Gelar Belakang;No Telpon;Jenis Kelamin;Tanggal Lahir;Alamat;Status Kepeg;Status Aktif;Pangkat;Golongan;Tanggal Masuk;Atasan Langsung';
        $baris = 'Pegawai Bom;pegawaibom@test.local;Pelaksana;Sekretariat;198001012020011095;;;;L;;;PNS;Aktif;;;;';
        $csv = "\xEF\xBB\xBF".implode("\n", [$header, $baris]);

        $this->actingAs($this->admin)
            ->post(route('master.pegawai.import.store'), ['file_csv' => $this->buatCsv($csv)]);

        $this->assertDatabaseHas('users', ['email' => 'pegawaibom@test.local']);
    }

    public function test_pesan_sukses_import_tampil_di_halaman_setelah_redirect(): void
    {
        // Regresi: sebelumnya importStore() redirect ke master.pegawai.index, dan flash
        // session('success') tidak pernah dirender di halaman mana pun — jadi user tidak
        // melihat apa-apa setelah import walau datanya sebenarnya berhasil masuk.
        $header = 'Nama;Email;Jabatan;Bidang;NIP / ID;Gelar Depan;Gelar Belakang;No Telpon;Jenis Kelamin;Tanggal Lahir;Alamat;Status Kepeg;Status Aktif;Pangkat;Golongan;Tanggal Masuk;Atasan Langsung';
        $baris = 'Rina Wulandari;rina@test.local;Pelaksana;Sekretariat;198005052020011005;;;08126;P;05-05-1980;;PNS;Aktif;;;01-01-2020;';
        $csv = implode("\n", [$header, $baris]);

        $response = $this->actingAs($this->admin)
            ->followingRedirects()
            ->post(route('master.pegawai.import.store'), ['file_csv' => $this->buatCsv($csv)]);

        $response->assertOk();
        $response->assertSee('1 pegawai berhasil diimpor', false);
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
