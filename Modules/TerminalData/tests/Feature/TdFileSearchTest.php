<?php

namespace Modules\TerminalData\Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;
use Tests\TestCase;

class TdFileSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        $bidang = MasterBidang::create(['kode' => 'APPS', 'nama' => 'Bidang Aplikasi']);
        MasterJabatan::create(['id' => 1, 'kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5]);

        $this->pegawai = User::create([
            'nama' => 'Pegawai Test',
            'email' => 'pegawai@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->pegawai->profile()->create([
            'nomor_identitas' => '1990000000000001',
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => 1,
            'bidang_id' => $bidang->id,
        ]);
        $this->pegawai->refresh()->load('profile');
    }

    public function test_search_menyaring_berdasarkan_kata_kunci(): void
    {
        $folder = TdFolder::factory()->create(['created_by' => $this->pegawai->id]);
        TdFile::factory()->inFolder($folder->id)->create([
            'name' => 'Laporan Bulanan Agustus',
            'created_by' => $this->pegawai->id,
        ]);
        TdFile::factory()->inFolder($folder->id)->create([
            'name' => 'Notulen Rapat',
            'created_by' => $this->pegawai->id,
        ]);

        $response = $this->actingAs($this->pegawai)
            ->getJson(route('terminaldata.filesData.search', ['q' => 'Laporan']));

        $response->assertStatus(200);
        $hasil = collect($response->json('data.data'));
        $this->assertCount(1, $hasil);
        $this->assertSame('Laporan Bulanan Agustus', $hasil->first()['name']);
    }

    public function test_search_menyaring_berdasarkan_creator_dan_rentang_hari(): void
    {
        $lain = User::create(['nama' => 'Pegawai Lain', 'email' => 'lain@test.com', 'password' => bcrypt('password')]);

        $folder = TdFolder::factory()->create(['created_by' => $this->pegawai->id]);
        $milikSaya = TdFile::factory()->inFolder($folder->id)->create(['created_by' => $this->pegawai->id]);
        TdFile::factory()->inFolder($folder->id)->create(['created_by' => $lain->id]);

        $response = $this->actingAs($this->pegawai)
            ->getJson(route('terminaldata.filesData.search', ['creator_id' => $this->pegawai->id]));

        $hasil = collect($response->json('data.data'));
        $this->assertCount(1, $hasil);
        $this->assertSame($milikSaya->id, $hasil->first()['id']);
    }

    public function test_search_tanpa_filter_mengembalikan_semua_file(): void
    {
        $folder = TdFolder::factory()->create(['created_by' => $this->pegawai->id]);
        TdFile::factory()->count(3)->inFolder($folder->id)->create(['created_by' => $this->pegawai->id]);

        $response = $this->actingAs($this->pegawai)
            ->getJson(route('terminaldata.filesData.search'));

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }
}
