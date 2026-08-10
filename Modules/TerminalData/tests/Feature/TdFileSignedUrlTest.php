<?php

namespace Modules\TerminalData\Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;
use Modules\TerminalData\Services\FileManagerService;
use Tests\TestCase;

class TdFileSignedUrlTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected TdFile $file;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        MasterJabatan::insert([
            ['id' => 1, 'kode' => 'ADMIN', 'nama' => 'Administrator', 'level' => 0],
        ]);
        MasterBidang::insert([
            ['id' => 1, 'kode' => 'APPS', 'nama' => 'Bidang Aplikasi'],
        ]);

        $this->admin = User::create([
            'nama' => 'Admin', 'email' => 'admin@test.local', 'password' => bcrypt('password'),
        ]);
        $this->admin->profile()->create([
            'nomor_identitas' => '1', 'tipe_identitas' => 'NIP', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif', 'jabatan_id' => 1, 'bidang_id' => 1,
        ]);

        $folder = TdFolder::factory()->create(['bidang_id' => 1, 'created_by' => $this->admin->id]);

        $uploadResponse = $this->actingAs($this->admin, 'sanctum')
            ->postJson(route('terminaldata.filesData.upload'), [
                'folder_id' => $folder->id,
                'file' => UploadedFile::fake()->create('rahasia.pdf', 10, 'application/pdf'),
            ]);

        $this->file = TdFile::findOrFail($uploadResponse->json('data.id'));
    }

    public function test_signed_url_bisa_diakses_tanpa_sesi_terautentikasi(): void
    {
        $service = app(FileManagerService::class);
        $url = $service->signedUrlFor('api.files.serve.signed', ['file' => $this->file->id]);

        // Tanpa actingAs() sama sekali — membuktikan akses tidak butuh sesi/token.
        $response = $this->get($url);

        $response->assertOk();
    }

    public function test_tanpa_tanda_tangan_ditolak(): void
    {
        $response = $this->get('/api/v1/files/'.$this->file->id.'/serve-signed');

        $response->assertStatus(403);
    }

    public function test_tanda_tangan_kadaluwarsa_ditolak(): void
    {
        $url = URL::temporarySignedRoute(
            'api.files.serve.signed',
            now()->subMinute(),
            ['file' => $this->file->id]
        );

        $response = $this->get($url);

        $response->assertStatus(403);
    }
}
