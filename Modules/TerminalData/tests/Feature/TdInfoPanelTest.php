<?php

namespace Modules\TerminalData\Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;
use Tests\TestCase;

class TdInfoPanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $pegawai;

    protected TdFolder $folder;

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

        $this->folder = TdFolder::factory()->create([
            'bidang_id' => $bidang->id,
            'created_by' => $this->pegawai->id,
        ]);
    }

    public function test_upload_mencatat_aktivitas_dan_endpoint_detail_mengembalikan_riwayatnya(): void
    {
        $upload = $this->actingAs($this->pegawai)->postJson(route('terminaldata.filesData.upload'), [
            'folder_id' => $this->folder->id,
            'file' => UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf'),
        ]);
        $upload->assertStatus(200);
        $fileId = $upload->json('data.id');

        $this->assertDatabaseHas('td_activities', [
            'trackable_type' => 'td_file',
            'trackable_id' => $fileId,
            'action' => 'uploaded',
            'user_id' => $this->pegawai->id,
        ]);

        $response = $this->actingAs($this->pegawai)
            ->getJson(route('terminaldata.filesData.detail', $fileId));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $fileId)
            ->assertJsonPath('data.original_name', 'laporan.pdf')
            ->assertJsonCount(1, 'activities')
            ->assertJsonPath('activities.0.action', 'uploaded');
    }

    public function test_rename_dan_hapus_file_tercatat_di_aktivitas(): void
    {
        $file = TdFile::factory()->inFolder($this->folder->id)->create(['created_by' => $this->pegawai->id]);

        $this->actingAs($this->pegawai)
            ->putJson(route('terminaldata.filesData.update', $file->id), ['name' => 'Nama Baru'])
            ->assertStatus(200);

        $response = $this->actingAs($this->pegawai)
            ->getJson(route('terminaldata.filesData.detail', $file->id));
        $this->assertGreaterThanOrEqual(1, count($response->json('activities')));

        $this->actingAs($this->pegawai)
            ->deleteJson(route('terminaldata.filesData.destroy', $file->id))
            ->assertStatus(200);

        $this->assertDatabaseHas('td_activities', [
            'trackable_id' => $file->id,
            'action' => 'renamed',
        ]);
        $this->assertDatabaseHas('td_activities', [
            'trackable_id' => $file->id,
            'action' => 'trashed',
        ]);
    }

    public function test_folder_dibuat_dan_ditandai_favorit_tercatat_di_aktivitas(): void
    {
        $create = $this->actingAs($this->pegawai)->postJson(route('terminaldata.foldersData.store'), [
            'name' => 'Folder Baru',
        ]);
        $create->assertStatus(201);
        $folderId = $create->json('data.id');

        $this->actingAs($this->pegawai)
            ->postJson(route('api.folders.toggle-star', $folderId))
            ->assertStatus(200);

        $this->assertDatabaseHas('td_activities', [
            'trackable_type' => 'td_folder',
            'trackable_id' => $folderId,
            'action' => 'created',
        ]);
        $this->assertDatabaseHas('td_activities', [
            'trackable_id' => $folderId,
            'action' => 'starred',
        ]);

        $response = $this->actingAs($this->pegawai)
            ->getJson(route('terminaldata.foldersData.detail', $folderId));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $folderId)
            ->assertJsonCount(2, 'activities');
    }

    public function test_halaman_folder_detail_tampil_dengan_tombol_info_dan_toggle_view(): void
    {
        $response = $this->actingAs($this->pegawai)
            ->get(route('terminaldata.folder.detail', $this->folder->id));

        $response->assertStatus(200);
        $response->assertSee('openInfoPanel(\'folder\', \''.$this->folder->id.'\')', false);
        $response->assertSee('id="viewGrid"', false);
        $response->assertSee('id="viewTable"', false);
        $response->assertSee('id="infoPanel"', false);
    }

    public function test_halaman_folder_detail_punya_dropdown_baru_filter_dan_sort(): void
    {
        $response = $this->actingAs($this->pegawai)
            ->get(route('terminaldata.folder.detail', $this->folder->id));

        $response->assertStatus(200);
        $response->assertSee('id="filterJenisPdf"', false);
        $response->assertSee('id="filterOrangList"', false);
        $response->assertSee('id="filterModifikasi"', false);
        $response->assertSee('sort-option', false);
        $response->assertSee('id="btnTriggerUpload"', false);
        $response->assertSee('terminal-data-scroll', false);

        // Filter Status+Pencarian lama (dead code) harus sudah tidak ada sama sekali
        $response->assertDontSee('id="filterSection"', false);
        $response->assertDontSee('id="filterStatus"', false);
        $response->assertDontSee('id="searchDokumen"', false);
        $response->assertDontSee('id="toggleFilter"', false);
    }
}
