<?php

namespace Tests\Unit;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileFotoUrlAccessorTest extends TestCase
{
    use RefreshDatabase;

    protected UserProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $jabatan = MasterJabatan::create(['kode' => 'ADMIN', 'nama' => 'Admin', 'level' => 1]);
        $bidang = MasterBidang::create(['kode' => 'SEKRE', 'nama' => 'Sekretariat']);

        $user = User::factory()->create();
        $this->profile = $user->profile()->create([
            'nomor_identitas' => '1', 'tipe_identitas' => 'ID', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatan->id, 'bidang_id' => $bidang->id,
        ]);
    }

    public function test_null_kalau_path_kosong(): void
    {
        $this->assertNull($this->profile->foto_profile_url);
    }

    public function test_mengembalikan_url_kalau_file_ada_di_disk(): void
    {
        Storage::disk('public')->put('pegawai/foto/contoh.jpg', 'isi-gambar');
        $this->profile->update(['foto_profile_path' => 'pegawai/foto/contoh.jpg', 'disk' => 'public']);

        $url = $this->profile->foto_profile_url;

        $this->assertNotNull($url);
        $this->assertStringContainsString('pegawai/foto/contoh.jpg', $url);
    }

    public function test_null_kalau_path_ada_tapi_file_tidak_ada_di_disk(): void
    {
        // Mewakili foto lama era public_path() yang fisiknya di luar jangkauan
        // Storage disk 'public' — tidak boleh error, cukup null.
        $this->profile->update(['foto_profile_path' => 'uploads/pegawai/photos/lama.jpg', 'disk' => 'public']);

        $this->assertNull($this->profile->foto_profile_url);
    }
}
