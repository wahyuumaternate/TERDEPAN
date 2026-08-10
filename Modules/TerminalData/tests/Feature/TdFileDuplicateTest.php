<?php

namespace Modules\TerminalData\Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;
use Tests\TestCase;

class TdFileDuplicateTest extends TestCase
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

        $this->actingAs($this->admin);
    }

    public function test_duplicate_menghasilkan_dua_file_fisik_yang_independen(): void
    {
        // Regresi: sebelumnya str_replace($this->id, $newFile->id, $this->storage_path)
        // tidak pernah match (UUID file tidak pernah muncul di storage_path, path
        // dibangun dari folder_id/bidang_id) — jadi dua baris DB berbeda menunjuk ke
        // SATU file fisik yang sama, dan menghapus salah satu ikut menghapus yang lain.
        //
        // Sengaja pakai file yang diupload lewat controller (path nested asli:
        // terminal-data/{bidang}/{folder}/...), BUKAN row hasil factory polos —
        // TdFileFactory menghasilkan storage_path flat yang KEBETULAN mengandung UUID
        // file-nya sendiri, jadi tidak benar-benar menguji bug yang sama seperti di
        // data produksi nyata.
        $folder = TdFolder::factory()->create(['bidang_id' => 1, 'created_by' => $this->admin->id]);

        $uploadResponse = $this->postJson(route('terminaldata.filesData.upload'), [
            'folder_id' => $folder->id,
            'file' => UploadedFile::fake()->create('asli.pdf', 10, 'application/pdf'),
        ]);
        $uploadResponse->assertOk();

        $original = TdFile::findOrFail($uploadResponse->json('data.id'));
        $originalPath = $original->storage_path;

        $duplicate = $original->duplicate();

        $this->assertNotSame($originalPath, $duplicate->storage_path);
        Storage::disk('local')->assertExists($originalPath);
        Storage::disk('local')->assertExists($duplicate->storage_path);

        // Menghapus duplikat secara permanen TIDAK BOLEH menghapus file asli.
        $duplicate->forceDelete();

        Storage::disk('local')->assertExists($originalPath);
        Storage::disk('local')->assertMissing($duplicate->storage_path);

        $this->assertNotNull(TdFile::find($original->id));
    }
}
