<?php

namespace Modules\Penugasan\Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Penugasan\Models\Penugasan;
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
        // Bawahan (Pelaksana) tidak punya hak create() -> tab diberikan otomatis jatuh ke tab saya, bukan 403.
        $this->actingAs($this->bawahan)->get(route('penugasan.tugas-saya', ['tab' => 'diberikan']))->assertStatus(200);
        $this->actingAs($this->bawahan)->get(route('penugasan.create'))->assertStatus(200);
    }

    /**
     * Regresi: endpoint AJAX yang di-polling halaman Tugas Saya (dok. 08 update — tabel
     * realtime tanpa reload) harus mengembalikan potongan HTML tabel yang valid, konsisten
     * dengan filter/tab yang sama seperti halaman utama, bukan halaman penuh ber-layout.
     */
    public function test_tugas_saya_data_endpoint_mengembalikan_potongan_tabel(): void
    {
        Penugasan::create([
            'pegawai_id' => $this->bawahan->id,
            'pemberi_tugas_id' => $this->atasan->id,
            'is_mandiri' => false,
            'jenis' => 'tambahan',
            'prioritas' => 'tinggi',
            'nama_tugas' => 'Tugas Polling Uji',
            'deskripsi' => 'x',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(5),
            'deadline_terbaru' => now()->addDays(5),
            'status' => Penugasan::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->bawahan)->get(route('penugasan.tugas-saya.data', ['tab' => 'saya']));

        $response->assertStatus(200)
            ->assertSee('Tugas Polling Uji')
            ->assertDontSee('<html', false);
    }

    public function test_atasan_can_view_team_pages(): void
    {
        $this->actingAs($this->atasan)->get(route('penugasan.index'))->assertStatus(200);
        $this->actingAs($this->atasan)->get(route('penugasan.tugas-saya', ['tab' => 'diberikan']))->assertStatus(200);
        $this->actingAs($this->atasan)->get(route('penugasan.create'))->assertStatus(200);
    }

    /**
     * Regresi: menu "Manajemen Penugasan" (Tim Saya, Monitoring Tim, Detail Anggota)
     * sudah dihapus dari sidebar & routing — pastikan rute-rutenya benar-benar hilang,
     * bukan cuma tersembunyi di navigasi.
     */
    public function test_rute_manajemen_tim_lama_sudah_tidak_terdaftar(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('penugasan.tim.index'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('penugasan.tim.monitoring'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('penugasan.tim.detail-anggota'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('penugasan.tim.overview'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('penugasan.tim.catatan-monitoring'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('penugasan.tim.preview-penilaian'));
    }

    public function test_route_lama_berikan_tugas_dan_validasi_redirect_ke_tab_diberikan(): void
    {
        $this->actingAs($this->atasan)->get(route('penugasan.tim.form-berikan-tugas'))
            ->assertRedirect(route('penugasan.tugas-saya', ['tab' => 'diberikan']));

        $this->actingAs($this->atasan)->get(route('penugasan.tim.daftar-validasi'))
            ->assertRedirect(route('penugasan.tugas-saya', ['tab' => 'diberikan']));
    }

    public function test_full_flow_berikan_tugas_sampai_dinilai(): void
    {
        // 1. Atasan memberikan tugas lewat wizard (POST penugasan.store)
        $response = $this->actingAs($this->atasan)->post(route('penugasan.store'), [
            'pegawai_id' => $this->bawahan->id,
            'jenis' => 'tambahan',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Tugas Uji Alur',
            'deskripsi' => 'Deskripsi tugas uji',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'bobot_persen' => 50,
        ]);
        $response->assertRedirect(route('penugasan.tugas-saya', ['tab' => 'diberikan']));

        $penugasan = Penugasan::where('nama_tugas', 'Tugas Uji Alur')->firstOrFail();
        $this->assertSame($this->bawahan->id, $penugasan->pegawai_id);
        $this->assertSame($this->atasan->id, $penugasan->pemberi_tugas_id);

        // 2. Bawahan melihat detail & menerima tugas
        $this->actingAs($this->bawahan)->get(route('penugasan.show', $penugasan->id))->assertStatus(200);

        $this->actingAs($this->bawahan)
            ->post(route('penugasan.terima', $penugasan->id))
            ->assertJson(['success' => true]);

        // 3. Bawahan mengupload bukti pengerjaan (folder Eviden Kinerja ditentukan otomatis)
        $file = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $uploadResponse = $this->actingAs($this->bawahan)->post(route('penugasan.upload-bukti', $penugasan->id), [
            'file' => $file,
        ]);
        $uploadResponse->assertJson(['success' => true]);
        $this->assertSame(1, $penugasan->fresh()->eviden()->count());

        // 4. Bawahan mencatat progress
        $this->actingAs($this->bawahan)
            ->post(route('penugasan.update-progress', $penugasan->id), [
                'progress_persen' => 80,
                'deskripsi_kegiatan' => 'Sudah mengerjakan sebagian besar',
            ])
            ->assertJson(['success' => true]);

        // 5. Bawahan mengajukan Selesai
        $this->actingAs($this->bawahan)
            ->post(route('penugasan.submit', $penugasan->id))
            ->assertJson(['success' => true]);
        $this->assertSame('selesai', $penugasan->fresh()->status);

        // 6. Atasan menilai (POST penugasan.nilai, bukan tim.validasi-tugas lagi)
        $nilaiResponse = $this->actingAs($this->atasan)
            ->post(route('penugasan.nilai', $penugasan->id), [
                'realisasi_persen' => 90,
            ]);
        $nilaiResponse->assertJson(['success' => true]);

        $penugasan->refresh();
        $this->assertSame('selesai', $penugasan->status);
        $this->assertEquals(90, $penugasan->realisasi_persen);
        $this->assertEquals(45.0, (float) $penugasan->nilai_akhir); // 50 * 90 / 100, tanpa potongan terlambat

        // 7. Bawahan melihat hasil akhir di halaman detail
        $this->actingAs($this->bawahan)->get(route('penugasan.show', $penugasan->id))
            ->assertStatus(200)
            ->assertSee('45');
    }

    public function test_wizard_berikan_tugas_grup_per_orang(): void
    {
        $bawahan2 = User::create([
            'nama' => 'Bawahan Kedua',
            'email' => 'bawahan2@test.com',
            'password' => bcrypt('password'),
        ]);
        $bawahan2->profile()->create([
            'nomor_identitas' => '1990000000000003',
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => 2,
            'bidang_id' => $this->bidang->id,
            'atasan_langsung_id' => $this->atasan->id,
        ]);

        $response = $this->actingAs($this->atasan)->post(route('penugasan.store-grup'), [
            'pegawai_ids' => [$this->bawahan->id, $bawahan2->id],
            'mode_grup' => 'per_orang',
            'jenis' => 'tambahan',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Tugas Grup Uji',
            'deskripsi' => 'Deskripsi',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect(route('penugasan.tugas-saya', ['tab' => 'diberikan']));
        $this->assertSame(2, Penugasan::where('nama_tugas', 'Tugas Grup Uji')->count());
    }

    public function test_wizard_mandiri_lalu_disetujui_atasan(): void
    {
        $store = $this->actingAs($this->bawahan)->post(route('penugasan.store'), [
            'pegawai_id' => $this->bawahan->id,
            'atasan_id' => $this->atasan->id,
            'jenis' => 'tambahan',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Tugas Mandiri Uji',
            'deskripsi' => 'Deskripsi',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
        ]);
        $store->assertRedirect(route('penugasan.tugas-saya', ['tab' => 'saya']));

        $penugasan = Penugasan::where('nama_tugas', 'Tugas Mandiri Uji')->firstOrFail();
        $this->assertTrue($penugasan->is_mandiri);
        $this->assertSame($this->atasan->id, $penugasan->pemberi_tugas_id);

        $this->actingAs($this->atasan)
            ->post(route('penugasan.approve-mandiri', $penugasan->id))
            ->assertJson(['success' => true]);

        $this->assertSame('proses', $penugasan->fresh()->status);
    }

    /**
     * Regresi: halaman detail sempat error "Call to a member function getKey() on array"
     * untuk tugas berstatus revisi yang belum punya log progress sama sekali. Penyebabnya
     * override map() Eloquent Collection gagal mendeteksi hasil non-Model saat collection
     * kosong, sehingga ->merge() berikutnya memanggil getKey() pada array biasa.
     */
    public function test_halaman_detail_tampil_untuk_tugas_revisi_tanpa_progress(): void
    {
        $penugasan = Penugasan::create([
            'pegawai_id' => $this->bawahan->id,
            'pemberi_tugas_id' => $this->atasan->id,
            'is_mandiri' => false,
            'jenis' => 'tambahan',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Tugas Revisi Tanpa Progress',
            'deskripsi' => 'x',
            'tanggal_mulai' => now()->subDays(5),
            'tanggal_selesai' => now()->addDays(2),
            'deadline_terbaru' => now()->addDays(2),
            'status' => Penugasan::STATUS_REVISI,
        ]);

        \Modules\Penugasan\Models\HistoriRevisi::create([
            'penugasan_id' => $penugasan->id,
            'revisi_ke' => 1,
            'tanggal_revisi' => now(),
            'catatan_revisi' => 'Mohon lengkapi data',
            'deadline_revisi' => now()->addDays(2),
            'direvisi_oleh' => $this->atasan->id,
            'pegawai_id' => $this->bawahan->id,
        ]);

        $this->actingAs($this->bawahan)->get(route('penugasan.show', $penugasan->id))->assertStatus(200);
        $this->actingAs($this->atasan)->get(route('penugasan.show', $penugasan->id))->assertStatus(200);
    }

    public function test_preview_penilaian_endpoint(): void
    {
        $penugasan = Penugasan::create([
            'pegawai_id' => $this->bawahan->id,
            'pemberi_tugas_id' => $this->atasan->id,
            'is_mandiri' => false,
            'jenis' => 'tambahan',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Tugas Preview',
            'deskripsi' => 'x',
            'tanggal_mulai' => now()->subDays(3),
            'tanggal_selesai' => now()->addDays(3),
            'deadline_terbaru' => now()->addDays(3),
            'status' => Penugasan::STATUS_SELESAI,
            'tanggal_diselesaikan' => now(),
        ]);

        $response = $this->actingAs($this->atasan)->postJson(route('penugasan.tim.preview-penilaian'), [
            'penugasan_id' => $penugasan->id,
            'bobot_persen' => 60,
            'realisasi_persen' => 80,
        ]);

        $response->assertJson([
            'success' => true,
            'data' => ['nilai_akhir' => 48.0],
        ]);
    }

    /**
     * Regresi: halaman detail dibuat dinamis lewat polling ke endpoint meta() —
     * pastikan endpointnya benar mendeteksi perubahan (status, jumlah progress).
     */
    public function test_meta_endpoint_mendeteksi_perubahan_status_dan_progress(): void
    {
        $penugasan = Penugasan::create([
            'pegawai_id' => $this->bawahan->id,
            'pemberi_tugas_id' => $this->atasan->id,
            'is_mandiri' => false,
            'jenis' => 'tambahan',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Tugas Meta Uji',
            'deskripsi' => 'x',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'deadline_terbaru' => now()->addDays(7),
            'status' => Penugasan::STATUS_PROSES,
            'progress_persen' => 20,
        ]);

        $sebelum = $this->actingAs($this->bawahan)->getJson(route('penugasan.meta', $penugasan->id))
            ->assertStatus(200)
            ->json();

        $this->assertSame('proses', $sebelum['status']);
        $this->assertSame(0, $sebelum['progress_count']);

        $this->actingAs($this->bawahan)->post(route('penugasan.update-progress', $penugasan->id), [
            'progress_persen' => 60,
            'deskripsi_kegiatan' => 'Update progress',
        ]);

        $sesudah = $this->actingAs($this->bawahan)->getJson(route('penugasan.meta', $penugasan->id))->json();

        // progress_count adalah sinyal deteksi perubahan yang diandalkan JS (bukan updated_at, karena
        // presisi kolom timestamp per-detik bisa membuat dua polling dalam detik yang sama tampak identik).
        $this->assertSame(1, $sesudah['progress_count']);
    }

    /**
     * Regresi: halaman detail sekarang pakai card biasa (bukan accordion) untuk
     * Ringkasan/Progress/Evidence, dan Progress & Evidence dipindah ke kolom kanan.
     */
    public function test_halaman_detail_tidak_lagi_pakai_accordion(): void
    {
        $penugasan = Penugasan::create([
            'pegawai_id' => $this->bawahan->id,
            'pemberi_tugas_id' => $this->atasan->id,
            'is_mandiri' => false,
            'jenis' => 'tambahan',
            'prioritas' => 'sedang',
            'nama_tugas' => 'Tugas Layout Uji',
            'deskripsi' => 'x',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'deadline_terbaru' => now()->addDays(7),
            'status' => Penugasan::STATUS_PROSES,
        ]);

        $html = $this->actingAs($this->bawahan)->get(route('penugasan.show', $penugasan->id))->getContent();

        $this->assertStringNotContainsString('accordion', $html);
        $this->assertStringContainsString('Ringkasan', $html);
        $this->assertStringContainsString('Progress &amp; Timeline', $html);
    }
}
