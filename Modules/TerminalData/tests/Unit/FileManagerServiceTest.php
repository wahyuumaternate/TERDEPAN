<?php

namespace Modules\TerminalData\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\TerminalData\Services\FileManagerService;
use Tests\TestCase;

class FileManagerServiceTest extends TestCase
{
    public function test_store_menyimpan_file_dan_mengembalikan_metadata(): void
    {
        Storage::fake('local');

        $service = new FileManagerService('local');
        $file = UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf');

        $result = $service->store($file, 'terminal-data/1/2');

        $this->assertSame('local', $result['disk']);
        $this->assertStringStartsWith('terminal-data/1/2/', $result['path']);
        $this->assertSame('laporan.pdf', $result['original_name']);
        $this->assertSame('application/pdf', $result['mime_type']);
        $this->assertSame('pdf', $result['extension']);
        $this->assertNotEmpty($result['hash']);
        Storage::disk('local')->assertExists($result['path']);
    }

    public function test_delete_physical_no_op_kalau_path_kosong_atau_tidak_ada(): void
    {
        Storage::fake('local');

        $service = new FileManagerService('local');

        // Tidak boleh throw apa pun.
        $service->deletePhysical(null);
        $service->deletePhysical('terminal-data/tidak-ada.pdf');

        $this->assertTrue(true);
    }

    public function test_delete_physical_menghapus_file_yang_ada(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('terminal-data/1/file.pdf', 'isi');

        $service = new FileManagerService('local');
        $service->deletePhysical('terminal-data/1/file.pdf');

        Storage::disk('local')->assertMissing('terminal-data/1/file.pdf');
    }

    public function test_copy_physical_menghasilkan_dua_file_independen(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('terminal-data/1/2/asli.pdf', 'isi asli');

        $service = new FileManagerService('local');
        $newPath = $service->copyPhysical('local', 'terminal-data/1/2/asli.pdf', 'terminal-data/1/3', 'salinan.pdf');

        $this->assertNotSame('terminal-data/1/2/asli.pdf', $newPath);
        Storage::disk('local')->assertExists('terminal-data/1/2/asli.pdf');
        Storage::disk('local')->assertExists($newPath);

        // Menghapus salinan tidak menghapus file asli — membuktikan keduanya independen.
        Storage::disk('local')->delete($newPath);
        Storage::disk('local')->assertExists('terminal-data/1/2/asli.pdf');
    }

    public function test_metadata_for_mengembalikan_mime_dan_ukuran(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('terminal-data/1/dokumen.txt', 'halo dunia');

        $service = new FileManagerService('local');
        $meta = $service->metadataFor('local', 'terminal-data/1/dokumen.txt');

        $this->assertSame(strlen('halo dunia'), $meta['size']);
        $this->assertNotEmpty($meta['mime_type']);
    }
}
