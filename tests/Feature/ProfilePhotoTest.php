<?php

namespace Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $jabatan = MasterJabatan::create(['kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5]);
        $bidang = MasterBidang::create(['kode' => 'SEKRE', 'nama' => 'Sekretariat']);

        $this->user = User::factory()->create();
        $this->user->profile()->create([
            'nomor_identitas' => '199001012020011001', 'tipe_identitas' => 'NIP', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatan->id, 'bidang_id' => $bidang->id,
        ]);
    }

    private function payloadProfil(array $overrides = []): array
    {
        return array_merge([
            'nomor_identitas' => '199001012020011001',
            'tipe_identitas' => 'NIP',
            'nama' => $this->user->nama,
            'email' => $this->user->email,
            'jenis_kelamin' => 'L',
        ], $overrides);
    }

    public function test_update_profil_dengan_foto_disimpan_di_konvensi_path_baru(): void
    {
        $response = $this->actingAs($this->user)->patch(route('profile.update'), $this->payloadProfil([
            'foto_profile' => UploadedFile::fake()->image('foto.jpg'),
        ]));

        $response->assertRedirect(route('profile.edit'));

        $this->user->profile->refresh();

        $this->assertSame('public', $this->user->profile->disk);
        $this->assertStringStartsWith('pegawai/foto/', $this->user->profile->foto_profile_path);
        $this->assertStringNotContainsString('uploads/pegawai/foto', $this->user->profile->foto_profile_path);
        Storage::disk('public')->assertExists($this->user->profile->foto_profile_path);
    }

    public function test_update_profil_mengganti_foto_lama(): void
    {
        $this->actingAs($this->user)->patch(route('profile.update'), $this->payloadProfil([
            'foto_profile' => UploadedFile::fake()->image('lama.jpg'),
        ]));
        $this->user->profile->refresh();
        $pathLama = $this->user->profile->foto_profile_path;

        $this->actingAs($this->user)->patch(route('profile.update'), $this->payloadProfil([
            'foto_profile' => UploadedFile::fake()->image('baru.jpg'),
        ]));
        $this->user->profile->refresh();

        $this->assertNotSame($pathLama, $this->user->profile->foto_profile_path);
        Storage::disk('public')->assertMissing($pathLama);
        Storage::disk('public')->assertExists($this->user->profile->foto_profile_path);
    }
}
