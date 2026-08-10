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

class TdFileTrashRestoreTest extends TestCase
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

    public function test_file_fisik_masih_ada_setelah_dipindah_ke_sampah_dan_bisa_dipulihkan(): void
    {
        // Regresi: sebelumnya hook TdFile::deleting menghapus file fisik pada SETIAP
        // delete (termasuk soft delete "pindah ke sampah"), padahal restore() cuma
        // mengembalikan baris DB — hasilnya record yang dipulihkan menunjuk ke file
        // yang sudah tidak ada.
        $folder = TdFolder::factory()->create(['bidang_id' => 1, 'created_by' => $this->admin->id]);

        $uploadResponse = $this->actingAs($this->admin, 'sanctum')
            ->postJson(route('terminaldata.filesData.upload'), [
                'folder_id' => $folder->id,
                'file' => UploadedFile::fake()->create('penting.pdf', 10, 'application/pdf'),
            ]);
        $uploadResponse->assertOk();

        $file = TdFile::findOrFail($uploadResponse->json('data.id'));
        $storagePath = $file->storage_path;

        Storage::disk('local')->assertExists($storagePath);

        // Pindah ke sampah (soft delete).
        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson(route('terminaldata.filesData.destroy', $file->id))
            ->assertOk();

        $this->assertSoftDeleted('td_files', ['id' => $file->id]);
        Storage::disk('local')->assertExists($storagePath);

        // Pulihkan dari sampah — file fisik harus tetap ada & bisa diakses.
        $this->actingAs($this->admin, 'sanctum')
            ->postJson(route('terminaldata.filesData.restore', $file->id))
            ->assertOk();

        $this->assertDatabaseHas('td_files', ['id' => $file->id, 'deleted_at' => null]);
        Storage::disk('local')->assertExists($storagePath);

        $this->actingAs($this->admin, 'sanctum')
            ->get(route('terminaldata.filesData.serve', $file->id))
            ->assertOk();
    }

    public function test_force_delete_dari_sampah_benar_benar_menghapus_file_fisik(): void
    {
        $folder = TdFolder::factory()->create(['bidang_id' => 1, 'created_by' => $this->admin->id]);

        $uploadResponse = $this->actingAs($this->admin, 'sanctum')
            ->postJson(route('terminaldata.filesData.upload'), [
                'folder_id' => $folder->id,
                'file' => UploadedFile::fake()->create('hapus.pdf', 10, 'application/pdf'),
            ]);

        $file = TdFile::findOrFail($uploadResponse->json('data.id'));
        $storagePath = $file->storage_path;

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson(route('terminaldata.filesData.destroy', $file->id))
            ->assertOk();

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson(route('terminaldata.filesData.forceDelete', $file->id))
            ->assertOk();

        $this->assertDatabaseMissing('td_files', ['id' => $file->id]);
        Storage::disk('local')->assertMissing($storagePath);
    }
}
