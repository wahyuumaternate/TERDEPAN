<?php

namespace Tests\Feature\Master;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MasterPegawaiPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected int $jabatanId;

    protected int $bidangId;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $jabatan = MasterJabatan::create(['kode' => 'ADMIN', 'nama' => 'Admin', 'level' => 1]);
        $bidang = MasterBidang::create(['kode' => 'SEKRE', 'nama' => 'Sekretariat']);
        $this->jabatanId = $jabatan->id;
        $this->bidangId = $bidang->id;

        $this->admin = User::factory()->create(['nama' => 'Admin Test']);
        $this->admin->profile()->create([
            'nomor_identitas' => 'ADMIN001', 'tipe_identitas' => 'ID', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif',
            'jabatan_id' => $this->jabatanId, 'bidang_id' => $this->bidangId,
        ]);
    }

    private function payloadPegawaiBaru(array $overrides = []): array
    {
        return array_merge([
            'nomor_identitas' => '199001012020011099',
            'tipe_identitas' => 'NIP',
            'nama' => 'Pegawai Baru',
            'jabatan_id' => $this->jabatanId,
            'bidang_id' => $this->bidangId,
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'email' => 'pegawaibaru@test.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_membuat_pegawai_dengan_foto_menyimpan_file_di_disk_public(): void
    {
        $response = $this->actingAs($this->admin)->post(route('master.pegawai.store'), $this->payloadPegawaiBaru([
            'foto_profile' => UploadedFile::fake()->image('foto.jpg'),
        ]));

        $response->assertRedirect(route('master.pegawai.index'));

        $pegawai = User::where('email', 'pegawaibaru@test.local')->with('profile')->first();

        $this->assertNotNull($pegawai);
        $this->assertSame('public', $pegawai->profile->disk);
        $this->assertStringStartsWith('pegawai/foto/', $pegawai->profile->foto_profile_path);
        Storage::disk('public')->assertExists($pegawai->profile->foto_profile_path);
    }

    public function test_update_foto_menghapus_foto_lama_dan_simpan_foto_baru(): void
    {
        $create = $this->actingAs($this->admin)->post(route('master.pegawai.store'), $this->payloadPegawaiBaru([
            'foto_profile' => UploadedFile::fake()->image('lama.jpg'),
        ]));
        $create->assertRedirect(route('master.pegawai.index'));

        $pegawai = User::where('email', 'pegawaibaru@test.local')->with('profile')->first();
        $pathLama = $pegawai->profile->foto_profile_path;
        Storage::disk('public')->assertExists($pathLama);

        $updateResponse = $this->actingAs($this->admin)->put(route('master.pegawai.update', $pegawai->id), $this->payloadPegawaiBaru([
            'foto_profile' => UploadedFile::fake()->image('baru.jpg'),
        ]));
        $updateResponse->assertRedirect(route('master.pegawai.show', $pegawai->id));

        $pegawai->profile->refresh();

        $this->assertNotSame($pathLama, $pegawai->profile->foto_profile_path);
        Storage::disk('public')->assertMissing($pathLama);
        Storage::disk('public')->assertExists($pegawai->profile->foto_profile_path);
    }

    public function test_update_tanpa_foto_baru_tidak_mengubah_foto_yang_ada(): void
    {
        $create = $this->actingAs($this->admin)->post(route('master.pegawai.store'), $this->payloadPegawaiBaru([
            'foto_profile' => UploadedFile::fake()->image('foto.jpg'),
        ]));
        $create->assertRedirect(route('master.pegawai.index'));

        $pegawai = User::where('email', 'pegawaibaru@test.local')->with('profile')->first();
        $pathAsli = $pegawai->profile->foto_profile_path;

        $this->actingAs($this->admin)->put(route('master.pegawai.update', $pegawai->id), $this->payloadPegawaiBaru())
            ->assertRedirect(route('master.pegawai.show', $pegawai->id));

        $pegawai->profile->refresh();

        $this->assertSame($pathAsli, $pegawai->profile->foto_profile_path);
        Storage::disk('public')->assertExists($pathAsli);
    }

    public function test_menghapus_pegawai_menghapus_file_foto(): void
    {
        $create = $this->actingAs($this->admin)->post(route('master.pegawai.store'), $this->payloadPegawaiBaru([
            'foto_profile' => UploadedFile::fake()->image('foto.jpg'),
        ]));
        $create->assertRedirect(route('master.pegawai.index'));

        $pegawai = User::where('email', 'pegawaibaru@test.local')->with('profile')->first();
        $path = $pegawai->profile->foto_profile_path;

        $this->actingAs($this->admin)->delete(route('master.pegawai.destroy', $pegawai->id));

        Storage::disk('public')->assertMissing($path);
    }
}
