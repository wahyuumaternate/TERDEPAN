<?php

namespace Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\Panduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PanduanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $bidang = MasterBidang::create(['kode' => 'APPS', 'nama' => 'Bidang Aplikasi']);
        $jabatanAdmin = MasterJabatan::create(['kode' => 'ADMIN', 'nama' => 'Admin', 'level' => 1]);
        $jabatanPelaksana = MasterJabatan::create(['kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5]);

        $this->admin = User::create(['nama' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
        $this->admin->profile()->create([
            'nomor_identitas' => '1', 'tipe_identitas' => 'NIP', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatanAdmin->id, 'bidang_id' => $bidang->id,
        ]);

        $this->pegawai = User::create(['nama' => 'Pegawai', 'email' => 'pegawai@test.com', 'password' => bcrypt('password')]);
        $this->pegawai->profile()->create([
            'nomor_identitas' => '2', 'tipe_identitas' => 'NIP', 'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS', 'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatanPelaksana->id, 'bidang_id' => $bidang->id,
        ]);
    }

    public function test_admin_bisa_membuat_panduan(): void
    {
        $response = $this->actingAs($this->admin)->post(route('panduan.store'), [
            'judul' => 'Panduan Penugasan',
            'deskripsi' => 'Cara menggunakan modul penugasan',
            'file' => UploadedFile::fake()->create('panduan.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('panduan.index'));

        $panduan = Panduan::first();
        $this->assertNotNull($panduan);
        $this->assertSame('Panduan Penugasan', $panduan->judul);
        $this->assertSame($this->admin->id, $panduan->diunggah_oleh_id);
        Storage::disk('local')->assertExists($panduan->path);
    }

    public function test_pegawai_biasa_tidak_bisa_membuat_panduan(): void
    {
        $response = $this->actingAs($this->pegawai)->post(route('panduan.store'), [
            'judul' => 'Panduan Ilegal',
            'file' => UploadedFile::fake()->create('panduan.pdf', 100, 'application/pdf'),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('panduans', 0);
    }

    public function test_pegawai_biasa_bisa_melihat_daftar_panduan(): void
    {
        Panduan::create([
            'judul' => 'Panduan A', 'disk' => 'local', 'path' => 'panduan/a.pdf',
            'nama_file' => 'a.pdf', 'mime_type' => 'application/pdf', 'size' => 100,
        ]);

        $response = $this->actingAs($this->pegawai)->get(route('panduan.index'));

        $response->assertOk();
        $response->assertSee('Panduan A');
    }

    public function test_admin_bisa_update_panduan_tanpa_ganti_file(): void
    {
        $stored = UploadedFile::fake()->create('lama.pdf', 100, 'application/pdf')->store('panduan', 'local');
        $panduan = Panduan::create([
            'judul' => 'Judul Lama', 'disk' => 'local', 'path' => $stored,
            'nama_file' => 'lama.pdf', 'mime_type' => 'application/pdf', 'size' => 100,
        ]);

        $response = $this->actingAs($this->admin)->put(route('panduan.update', $panduan), [
            'judul' => 'Judul Baru',
            'deskripsi' => 'Deskripsi baru',
        ]);

        $response->assertRedirect(route('panduan.index'));

        $panduan->refresh();
        $this->assertSame('Judul Baru', $panduan->judul);
        $this->assertSame($stored, $panduan->path);
        Storage::disk('local')->assertExists($stored);
    }

    public function test_admin_bisa_update_panduan_dengan_ganti_file_dan_hapus_file_lama(): void
    {
        $pathLama = UploadedFile::fake()->create('lama.pdf', 100, 'application/pdf')->store('panduan', 'local');
        $panduan = Panduan::create([
            'judul' => 'Judul Lama', 'disk' => 'local', 'path' => $pathLama,
            'nama_file' => 'lama.pdf', 'mime_type' => 'application/pdf', 'size' => 100,
        ]);

        $response = $this->actingAs($this->admin)->put(route('panduan.update', $panduan), [
            'judul' => 'Judul Baru',
            'file' => UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('panduan.index'));

        $panduan->refresh();
        $this->assertNotSame($pathLama, $panduan->path);
        Storage::disk('local')->assertMissing($pathLama);
        Storage::disk('local')->assertExists($panduan->path);
    }

    public function test_pegawai_biasa_tidak_bisa_update_panduan(): void
    {
        $panduan = Panduan::create([
            'judul' => 'Judul', 'disk' => 'local', 'path' => 'panduan/x.pdf',
            'nama_file' => 'x.pdf', 'mime_type' => 'application/pdf', 'size' => 100,
        ]);

        $response = $this->actingAs($this->pegawai)->put(route('panduan.update', $panduan), [
            'judul' => 'Judul Baru',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_menghapus_panduan_menghapus_file_fisik(): void
    {
        $path = UploadedFile::fake()->create('panduan.pdf', 100, 'application/pdf')->store('panduan', 'local');
        $panduan = Panduan::create([
            'judul' => 'Judul', 'disk' => 'local', 'path' => $path,
            'nama_file' => 'panduan.pdf', 'mime_type' => 'application/pdf', 'size' => 100,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('panduan.destroy', $panduan));

        $response->assertRedirect(route('panduan.index'));
        $this->assertDatabaseMissing('panduans', ['id' => $panduan->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_pegawai_biasa_tidak_bisa_menghapus_panduan(): void
    {
        $panduan = Panduan::create([
            'judul' => 'Judul', 'disk' => 'local', 'path' => 'panduan/x.pdf',
            'nama_file' => 'x.pdf', 'mime_type' => 'application/pdf', 'size' => 100,
        ]);

        $response = $this->actingAs($this->pegawai)->delete(route('panduan.destroy', $panduan));

        $response->assertForbidden();
        $this->assertDatabaseHas('panduans', ['id' => $panduan->id]);
    }

    public function test_pegawai_biasa_bisa_preview_dan_download_panduan(): void
    {
        $path = UploadedFile::fake()->create('panduan.pdf', 100, 'application/pdf')->store('panduan', 'local');
        $panduan = Panduan::create([
            'judul' => 'Judul', 'disk' => 'local', 'path' => $path,
            'nama_file' => 'panduan.pdf', 'mime_type' => 'application/pdf', 'size' => 100,
        ]);

        $this->actingAs($this->pegawai)->get(route('panduan.preview', $panduan))->assertOk();
        $this->actingAs($this->pegawai)->get(route('panduan.download', $panduan))->assertOk();
    }

    public function test_file_bukan_pdf_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post(route('panduan.store'), [
            'judul' => 'Panduan Salah Format',
            'file' => UploadedFile::fake()->create('panduan.docx', 100, 'application/msword'),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('panduans', 0);
    }
}
