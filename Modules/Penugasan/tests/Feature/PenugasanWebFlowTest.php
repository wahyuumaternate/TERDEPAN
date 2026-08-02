<?php

namespace Modules\Penugasan\Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Penugasan\Models\Penugasan;
use Modules\TerminalData\Models\TdFolder;
use Tests\TestCase;

class PenugasanWebFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $atasan;

    protected User $bawahan;

    protected MasterBidang $bidang;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        MasterJabatan::insert([
            ['id' => 1, 'kode' => 'KABAN', 'nama' => 'Kepala Badan', 'level' => 1],
            ['id' => 2, 'kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5],
        ]);

        $this->bidang = MasterBidang::create(['kode' => 'APPS', 'nama' => 'Bidang Aplikasi']);

        $this->atasan = User::create([
            'nama' => 'Atasan Test',
            'email' => 'atasan@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->atasan->profile()->create([
            'nomor_identitas' => '1990000000000001',
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => 1,
            'bidang_id' => $this->bidang->id,
        ]);

        $this->bawahan = User::create([
            'nama' => 'Bawahan Test',
            'email' => 'bawahan@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->bawahan->profile()->create([
            'nomor_identitas' => '1990000000000002',
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => 2,
            'bidang_id' => $this->bidang->id,
            'atasan_langsung_id' => $this->atasan->id,
        ]);

        $this->atasan->refresh()->load('profile');
        $this->bawahan->refresh()->load('profile');
    }

    public function test_bawahan_can_view_tugas_saya_and_create_pages(): void
    {
        $this->actingAs($this->bawahan)->get(route('penugasan.tugas-saya'))->assertStatus(200);
        $this->actingAs($this->bawahan)->get(route('penugasan.tugas-saya', ['jenis' => 'pokok']))->assertStatus(200);
        $this->actingAs($this->bawahan)->get(route('penugasan.create'))->assertStatus(200);
    }

    public function test_atasan_can_view_team_pages(): void
    {
        $this->actingAs($this->atasan)->get(route('penugasan.tim.index'))->assertStatus(200);
        $this->actingAs($this->atasan)->get(route('penugasan.tim.form-berikan-tugas'))->assertStatus(200);
        $this->actingAs($this->atasan)->get(route('penugasan.tim.daftar-validasi'))->assertStatus(200);
        $this->actingAs($this->atasan)->get(route('penugasan.tim.monitoring'))->assertStatus(200);
        $this->actingAs($this->atasan)->get(route('penugasan.tim.detail-anggota', $this->bawahan->id))->assertStatus(200);
        $this->actingAs($this->atasan)->get(route('penugasan.index'))->assertStatus(200);
    }

    public function test_full_flow_berikan_tugas_sampai_validasi(): void
    {
        // 1. Atasan memberikan tugas via form berikan-tugas
        $response = $this->actingAs($this->atasan)->post(route('penugasan.tim.berikan-tugas'), [
            'pegawai_id' => $this->bawahan->id,
            'jenis' => 'tambahan',
            'nama_tugas' => 'Tugas Uji Alur',
            'deskripsi' => 'Deskripsi tugas uji',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'bobot_persen' => 50,
        ]);
        $response->assertRedirect();

        $penugasan = Penugasan::where('nama_tugas', 'Tugas Uji Alur')->firstOrFail();
        $this->assertSame($this->bawahan->id, $penugasan->pegawai_id);
        $this->assertSame($this->atasan->id, $penugasan->pemberi_tugas_id);

        // 2. Bawahan melihat detail & menerima tugas
        $this->actingAs($this->bawahan)->get(route('penugasan.show', $penugasan->id))->assertStatus(200);

        $this->actingAs($this->bawahan)
            ->post(route('penugasan.terima', $penugasan->id))
            ->assertJson(['success' => true]);

        // 3. Bawahan mengupload bukti pengerjaan ke folder bidangnya
        $folder = TdFolder::factory()->forBidang($this->bidang->id)->create([
            'created_by' => $this->atasan->id,
        ]);
        $file = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $uploadResponse = $this->actingAs($this->bawahan)->post(route('penugasan.upload-bukti', $penugasan->id), [
            'folder_id' => $folder->id,
            'file' => $file,
        ]);
        $uploadResponse->assertJson(['success' => true]);
        $this->assertSame(1, $penugasan->fresh()->attachedFiles()->count());

        // 4. Bawahan mencatat progress
        $this->actingAs($this->bawahan)
            ->post(route('penugasan.update-progress', $penugasan->id), [
                'progress_persen' => 80,
                'deskripsi_kegiatan' => 'Sudah mengerjakan sebagian besar',
            ])
            ->assertJson(['success' => true]);

        // 5. Bawahan submit untuk validasi
        $this->actingAs($this->bawahan)
            ->post(route('penugasan.submit', $penugasan->id))
            ->assertJson(['success' => true]);
        $this->assertSame('selesai', $penugasan->fresh()->status);

        // 6. Atasan melihat daftar validasi & memvalidasi dengan realisasi 90%
        $this->actingAs($this->atasan)->get(route('penugasan.tim.daftar-validasi'))->assertStatus(200);

        $validasiResponse = $this->actingAs($this->atasan)
            ->post(route('penugasan.tim.validasi-tugas', $penugasan->id), [
                'status_validasi' => 'diterima',
                'realisasi_persen' => 90,
            ]);
        $validasiResponse->assertJson(['success' => true]);

        $penugasan->refresh();
        $this->assertSame('selesai', $penugasan->status);
        $this->assertEquals(90, $penugasan->realisasi_persen);
        $this->assertEquals(45.0, (float) $penugasan->nilai_akhir); // 50 * 90 / 100

        // 7. Bawahan melihat hasil akhir di halaman detail
        $this->actingAs($this->bawahan)->get(route('penugasan.show', $penugasan->id))
            ->assertStatus(200)
            ->assertSee('45');
    }

    public function test_preview_penilaian_endpoint(): void
    {
        $response = $this->actingAs($this->atasan)->postJson(route('penugasan.tim.preview-penilaian'), [
            'bobot_persen' => 60,
            'realisasi_persen' => 80,
        ]);

        $response->assertJson([
            'success' => true,
            'data' => ['nilai_akhir' => 48.0],
        ]);
    }
}
