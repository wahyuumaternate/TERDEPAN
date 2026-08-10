<?php

namespace Modules\Penugasan\Tests\Feature;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Penugasan\Models\Penugasan;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;
use Tests\TestCase;

class EvidenKinerjaReportTest extends TestCase
{
    use RefreshDatabase;

    protected MasterBidang $bidang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bidang = MasterBidang::create(['kode' => 'APPS', 'nama' => 'Bidang Aplikasi']);

        foreach ([
            ['id' => 1, 'kode' => 'KABAN', 'nama' => 'Kepala Badan', 'level' => 1],
            ['id' => 2, 'kode' => 'KABID', 'nama' => 'Kepala Bidang', 'level' => 3],
            ['id' => 3, 'kode' => 'PELAKSANA', 'nama' => 'Pelaksana', 'level' => 5],
        ] as $jabatan) {
            MasterJabatan::create($jabatan);
        }
    }

    protected function createUserWithJabatan(string $kodeJabatan, ?User $atasan = null): User
    {
        $jabatan = MasterJabatan::where('kode', $kodeJabatan)->firstOrFail();

        $user = User::create([
            'nama' => 'User '.$kodeJabatan.' '.uniqid(),
            'email' => strtolower($kodeJabatan).uniqid().'@test.com',
            'password' => bcrypt('password'),
        ]);

        $user->profile()->create([
            'nomor_identitas' => '1990'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT).'001',
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatan->id,
            'bidang_id' => $this->bidang->id,
            'atasan_langsung_id' => $atasan?->id,
        ]);

        return $user->fresh('profile');
    }

    protected function lampirkanEviden(Penugasan $tugas, User $pegawai, string $extension): TdFile
    {
        $folder = TdFolder::factory()->create(['created_by' => $pegawai->id]);
        $file = TdFile::factory()->create([
            'folder_id' => $folder->id,
            'created_by' => $pegawai->id,
            'extension' => $extension,
        ]);
        $tugas->eviden()->attach($file->id, ['created_by' => $pegawai->id]);

        return $file;
    }

    public function test_pegawai_melihat_laporan_eviden_bulan_berjalan_miliknya_sendiri(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $pegawai = $this->createUserWithJabatan('PELAKSANA', $atasan);

        $tugas = Penugasan::create([
            'pegawai_id' => $pegawai->id, 'pemberi_tugas_id' => $atasan->id, 'is_mandiri' => false,
            'jenis' => 'tambahan', 'prioritas' => 'sedang', 'nama_tugas' => 'Tugas Uji Laporan',
            'deskripsi' => 'x', 'tanggal_mulai' => now(), 'tanggal_selesai' => now()->addDays(3), 'status' => 'proses',
        ]);

        $this->lampirkanEviden($tugas, $pegawai, 'jpg');
        $this->lampirkanEviden($tugas, $pegawai, 'pdf');

        $response = $this->actingAs($pegawai)->getJson(route('penugasan.api.laporan-eviden'));

        $response->assertStatus(200);
        $this->assertSame(2, $response->json('data.total'));
    }

    public function test_filter_tipe_foto_hanya_mengembalikan_gambar(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $pegawai = $this->createUserWithJabatan('PELAKSANA', $atasan);

        $tugas = Penugasan::create([
            'pegawai_id' => $pegawai->id, 'pemberi_tugas_id' => $atasan->id, 'is_mandiri' => false,
            'jenis' => 'tambahan', 'prioritas' => 'sedang', 'nama_tugas' => 'Tugas Uji Foto',
            'deskripsi' => 'x', 'tanggal_mulai' => now(), 'tanggal_selesai' => now()->addDays(3), 'status' => 'proses',
        ]);

        $this->lampirkanEviden($tugas, $pegawai, 'jpg');
        $this->lampirkanEviden($tugas, $pegawai, 'pdf');
        $this->lampirkanEviden($tugas, $pegawai, 'png');

        $response = $this->actingAs($pegawai)->getJson(route('penugasan.api.laporan-eviden', ['tipe' => 'foto']));

        $response->assertStatus(200);
        $this->assertSame(2, $response->json('data.total'));
        foreach ($response->json('data.eviden') as $item) {
            $this->assertContains($item['extension'], TdFile::EXTENSI_GAMBAR);
        }
    }

    public function test_pegawai_tidak_bisa_lihat_laporan_pegawai_lain(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $pegawaiA = $this->createUserWithJabatan('PELAKSANA', $atasan);
        $pegawaiB = $this->createUserWithJabatan('PELAKSANA', $atasan);

        $response = $this->actingAs($pegawaiA)
            ->getJson(route('penugasan.api.laporan-eviden', ['pegawai_id' => $pegawaiB->id]));

        $response->assertStatus(403);
    }

    public function test_atasan_langsung_bisa_lihat_laporan_bawahannya(): void
    {
        $atasan = $this->createUserWithJabatan('KABID');
        $pegawai = $this->createUserWithJabatan('PELAKSANA', $atasan);

        $tugas = Penugasan::create([
            'pegawai_id' => $pegawai->id, 'pemberi_tugas_id' => $atasan->id, 'is_mandiri' => false,
            'jenis' => 'tambahan', 'prioritas' => 'sedang', 'nama_tugas' => 'Tugas Uji Atasan',
            'deskripsi' => 'x', 'tanggal_mulai' => now(), 'tanggal_selesai' => now()->addDays(3), 'status' => 'proses',
        ]);
        $this->lampirkanEviden($tugas, $pegawai, 'jpg');

        $response = $this->actingAs($atasan)
            ->getJson(route('penugasan.api.laporan-eviden', ['pegawai_id' => $pegawai->id]));

        $response->assertStatus(200);
        $this->assertSame(1, $response->json('data.total'));
    }
}
