<?php

namespace Modules\TerminalData\Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\TerminalData\Models\TdFolder;
use Tests\TestCase;

class TdFileServeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

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
    }

    private function uploadFile(UploadedFile $fakeFile): string
    {
        $folder = TdFolder::factory()->create(['bidang_id' => 1, 'created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson(route('terminaldata.filesData.upload'), [
                'folder_id' => $folder->id,
                'file' => $fakeFile,
            ]);

        $response->assertOk();

        return $response->json('data.id');
    }

    public function test_serve_mengembalikan_content_type_dari_kolom_mime_type(): void
    {
        $fileId = $this->uploadFile(UploadedFile::fake()->create('gambar.svg', 10, 'image/svg+xml'));

        $response = $this->actingAs($this->admin, 'sanctum')
            ->get(route('terminaldata.filesData.serve', $fileId));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_serve_pdf_content_type_sesuai_kolom_mime_type(): void
    {
        $fileId = $this->uploadFile(UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'));

        $response = $this->actingAs($this->admin, 'sanctum')
            ->get(route('terminaldata.filesData.serve', $fileId));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
