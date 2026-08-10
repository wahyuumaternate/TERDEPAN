<?php

namespace Modules\Penugasan\Database\Seeders;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Penugasan\Models\HistoriRevisi;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Models\PerpanjanganWaktu;
use Modules\Penugasan\Models\Progress;

/**
 * Seeder khusus development/testing: membuat pegawai sintetis (idempotent, tidak
 * menyentuh data pegawai riil dari DataPegawaiSeeder) beserta contoh Penugasan yang
 * mencakup seluruh status & fitur di aturan-penugasan-&-penilaian.md, supaya
 * REST API (api/v1/penugasan/*) bisa langsung dicoba manual (Postman/curl) tanpa
 * harus membangun data lewat endpoint terlebih dahulu.
 */
class PenugasanDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawai = $this->buatPegawaiTest();

        if ($pegawai === null) {
            $this->command?->warn('PenugasanDatabaseSeeder: master jabatan/bidang belum lengkap, dilewati.');

            return;
        }

        // Sudah pernah di-seed sebelumnya (idempotent check sederhana berbasis nama_tugas unik)
        if (Penugasan::where('nama_tugas', 'like', '[Seed]%')->exists()) {
            $this->command?->info('PenugasanDatabaseSeeder: data contoh sudah ada, dilewati.');

            return;
        }

        $this->buatTugasBiasa($pegawai);
        $this->buatTugasMandiri($pegawai);
        $this->buatPerpanjanganWaktu($pegawai);
        $this->buatPenugasanGrup($pegawai);
    }

    /**
     * @return array<string, User>|null
     */
    private function buatPegawaiTest(): ?array
    {
        $kodeJabatan = MasterJabatan::whereIn('kode', [
            'KABAN', 'SEKBAN', 'KASUBAG', 'JAFUNG', 'PELAKSANA', 'GATEK', 'KABID',
        ])->pluck('id', 'kode');

        $kodeBidang = MasterBidang::whereIn('kode', ['BAPPEDA', 'SEKRETARIAT', 'EKONOMI', 'IPW'])->pluck('id', 'kode');

        if ($kodeJabatan->count() < 7 || $kodeBidang->count() < 4) {
            return null;
        }

        $buat = function (string $nama, string $email, string $nomorIdentitas, string $kodeJab, string $kodeBid, ?User $atasan = null) use ($kodeJabatan, $kodeBidang): User {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['nama' => $nama, 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );

            $user->profile()->firstOrCreate([], [
                'nomor_identitas' => $nomorIdentitas,
                'tipe_identitas' => 'NIP',
                'jabatan_id' => $kodeJabatan[$kodeJab],
                'bidang_id' => $kodeBidang[$kodeBid],
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => 'PNS',
                'status_aktif' => 'Aktif',
                'tanggal_masuk' => now()->subYears(5)->toDateString(),
                'atasan_langsung_id' => $atasan?->id,
            ]);

            return $user->fresh('profile');
        };

        $kaban = $buat('Kaban Seed Penugasan', 'kaban.seed.penugasan@test.local', '9000000000000001', 'KABAN', 'BAPPEDA');
        $sekban = $buat('Sekban Seed Penugasan', 'sekban.seed.penugasan@test.local', '9000000000000002', 'SEKBAN', 'SEKRETARIAT', $kaban);
        $kasubag = $buat('Kasubag Seed Penugasan', 'kasubag.seed.penugasan@test.local', '9000000000000003', 'KASUBAG', 'SEKRETARIAT', $sekban);
        $jafungSekretariat = $buat('Jafung Sekretariat Seed Penugasan', 'jafung.sekretariat.seed.penugasan@test.local', '9000000000000004', 'JAFUNG', 'SEKRETARIAT', $sekban);
        $pelaksanaSekretariat = $buat('Pelaksana Sekretariat Seed Penugasan', 'pelaksana.sekretariat.seed.penugasan@test.local', '9000000000000005', 'PELAKSANA', 'SEKRETARIAT', $kasubag);
        $kabidEkonomi = $buat('Kabid Ekonomi Seed Penugasan', 'kabid.ekonomi.seed.penugasan@test.local', '9000000000000006', 'KABID', 'EKONOMI', $kaban);
        $jafungEkonomi = $buat('Jafung Ekonomi Seed Penugasan', 'jafung.ekonomi.seed.penugasan@test.local', '9000000000000007', 'JAFUNG', 'EKONOMI', $kabidEkonomi);
        $pelaksanaEkonomi1 = $buat('Pelaksana Ekonomi 1 Seed Penugasan', 'pelaksana.ekonomi1.seed.penugasan@test.local', '9000000000000008', 'PELAKSANA', 'EKONOMI', $kabidEkonomi);
        $pelaksanaEkonomi2 = $buat('Pelaksana Ekonomi 2 Seed Penugasan', 'pelaksana.ekonomi2.seed.penugasan@test.local', '9000000000000009', 'PELAKSANA', 'EKONOMI', $kabidEkonomi);
        $gatekEkonomi = $buat('Gatek Ekonomi Seed Penugasan', 'gatek.ekonomi.seed.penugasan@test.local', '9000000000000010', 'GATEK', 'EKONOMI', $kabidEkonomi);
        $kabidIpw = $buat('Kabid IPW Seed Penugasan', 'kabid.ipw.seed.penugasan@test.local', '9000000000000011', 'KABID', 'IPW', $kaban);
        $pelaksanaIpw = $buat('Pelaksana IPW Seed Penugasan', 'pelaksana.ipw.seed.penugasan@test.local', '9000000000000012', 'PELAKSANA', 'IPW', $kabidIpw);

        return [
            'kaban' => $kaban,
            'sekban' => $sekban,
            'kasubag' => $kasubag,
            'jafung_sekretariat' => $jafungSekretariat,
            'pelaksana_sekretariat' => $pelaksanaSekretariat,
            'kabid_ekonomi' => $kabidEkonomi,
            'jafung_ekonomi' => $jafungEkonomi,
            'pelaksana_ekonomi_1' => $pelaksanaEkonomi1,
            'pelaksana_ekonomi_2' => $pelaksanaEkonomi2,
            'gatek_ekonomi' => $gatekEkonomi,
            'kabid_ipw' => $kabidIpw,
            'pelaksana_ipw' => $pelaksanaIpw,
        ];
    }

    /**
     * @param  array<string, User>  $p
     */
    private function buatTugasBiasa(array $p): void
    {
        // Pending — menunggu diterima/ditolak pegawai
        Penugasan::factory()->create([
            'pegawai_id' => $p['pelaksana_ekonomi_1']->id,
            'pemberi_tugas_id' => $p['kabid_ekonomi']->id,
            'jenis' => Penugasan::JENIS_TAMBAHAN,
            'nama_tugas' => '[Seed] Menyusun laporan triwulan (Pending)',
        ]);

        // Proses — sudah diterima & sedang dikerjakan, dengan riwayat progress
        $proses = Penugasan::factory()->proses()->create([
            'pegawai_id' => $p['pelaksana_ekonomi_1']->id,
            'pemberi_tugas_id' => $p['kabid_ekonomi']->id,
            'jenis' => Penugasan::JENIS_POKOK,
            'nama_tugas' => '[Seed] Rekapitulasi data ekonomi bidang (Proses)',
        ]);
        Progress::factory()->count(2)->create(['penugasan_id' => $proses->id, 'pegawai_id' => $p['pelaksana_ekonomi_1']->id]);

        // Revisi — dikembalikan atasan, dengan riwayat revisi
        $revisi = Penugasan::factory()->revisi()->create([
            'pegawai_id' => $p['pelaksana_ekonomi_2']->id,
            'pemberi_tugas_id' => $p['kabid_ekonomi']->id,
            'nama_tugas' => '[Seed] Survei harga pasar (Revisi)',
        ]);
        HistoriRevisi::factory()->create([
            'penugasan_id' => $revisi->id,
            'pegawai_id' => $p['pelaksana_ekonomi_2']->id,
            'direvisi_oleh' => $p['kabid_ekonomi']->id,
        ]);

        // Terlambat — lewat deadline_terbaru tanpa diselesaikan
        Penugasan::factory()->terlambat()->create([
            'pegawai_id' => $p['pelaksana_ekonomi_2']->id,
            'pemberi_tugas_id' => $p['kabid_ekonomi']->id,
            'nama_tugas' => '[Seed] Verifikasi lapangan (Terlambat)',
        ]);

        // Selesai, belum dinilai — siap dicoba lewat POST .../nilai atau .../revisi
        Penugasan::factory()->selesai()->create([
            'pegawai_id' => $p['pelaksana_sekretariat']->id,
            'pemberi_tugas_id' => $p['kasubag']->id,
            'jenis' => Penugasan::JENIS_TAMBAHAN,
            'nama_tugas' => '[Seed] Pengarsipan surat masuk (Selesai, belum dinilai)',
        ]);

        // Selesai & sudah dinilai — final, contoh nilai_akhir lengkap
        Penugasan::factory()->selesaiDinilai(bobot: 20, realisasi: 90)->create([
            'pegawai_id' => $p['pelaksana_sekretariat']->id,
            'pemberi_tugas_id' => $p['kasubag']->id,
            'jenis' => Penugasan::JENIS_POKOK,
            'nama_tugas' => '[Seed] Notulensi rapat bulanan (Selesai & Dinilai)',
        ]);
    }

    /**
     * @param  array<string, User>  $p
     */
    private function buatTugasMandiri(array $p): void
    {
        // Jafung Sekretariat -> pilih Sekban (aturan §2.2), menunggu approval
        Penugasan::factory()->mandiri($p['sekban'])->create([
            'pegawai_id' => $p['jafung_sekretariat']->id,
            'nama_tugas' => '[Seed] Usulan pelatihan internal (Mandiri, menunggu approval)',
        ]);

        // Pelaksana Ekonomi 1 -> pilih Kabid Ekonomi, disetujui -> proses
        Penugasan::factory()->mandiri($p['kabid_ekonomi'])->proses()->create([
            'pegawai_id' => $p['pelaksana_ekonomi_1']->id,
            'status_approval' => Penugasan::APPROVAL_DITERIMA,
            'nama_tugas' => '[Seed] Inisiatif digitalisasi arsip (Mandiri, disetujui)',
        ]);

        // Pelaksana IPW -> pilih Kabid IPW, ditolak
        Penugasan::factory()->mandiri($p['kabid_ipw'])->mandiriDitolak()->create([
            'pegawai_id' => $p['pelaksana_ipw']->id,
            'nama_tugas' => '[Seed] Studi banding infrastruktur (Mandiri, Ditolak)',
        ]);
    }

    /**
     * @param  array<string, User>  $p
     */
    private function buatPerpanjanganWaktu(array $p): void
    {
        $tugas = Penugasan::factory()->proses()->create([
            'pegawai_id' => $p['pelaksana_ekonomi_1']->id,
            'pemberi_tugas_id' => $p['kabid_ekonomi']->id,
            'nama_tugas' => '[Seed] Analisis inflasi daerah (contoh Perpanjangan Waktu)',
        ]);

        PerpanjanganWaktu::factory()->create([
            'penugasan_id' => $tugas->id,
            'pegawai_id' => $p['pelaksana_ekonomi_1']->id,
            'deadline_lama' => $tugas->deadline_terbaru,
            'ke_berapa' => 1,
        ]);

        PerpanjanganWaktu::factory()->disetujui($p['kabid_ekonomi'])->create([
            'penugasan_id' => $tugas->id,
            'pegawai_id' => $p['pelaksana_ekonomi_1']->id,
            'deadline_lama' => $tugas->deadline_terbaru,
            'ke_berapa' => 2,
        ]);

        PerpanjanganWaktu::factory()->ditolak($p['kabid_ekonomi'])->create([
            'penugasan_id' => $tugas->id,
            'pegawai_id' => $p['pelaksana_ekonomi_1']->id,
            'deadline_lama' => $tugas->deadline_terbaru,
            'ke_berapa' => 3,
        ]);
    }

    /**
     * @param  array<string, User>  $p
     */
    private function buatPenugasanGrup(array $p): void
    {
        // Kolektif: Pelaksana Ekonomi 1 (koordinator) + Pelaksana Ekonomi 2
        $grupKolektif = (string) Str::uuid();
        $atributGrupKolektif = [
            'pemberi_tugas_id' => $p['kabid_ekonomi']->id,
            'jenis' => Penugasan::JENIS_TAMBAHAN,
            'nama_tugas' => '[Seed] Penyusunan laporan bersama (Grup Kolektif)',
        ];
        Penugasan::factory()->proses()->dalamGrup($grupKolektif, Penugasan::MODE_GRUP_KOLEKTIF, koordinator: true)->create([
            ...$atributGrupKolektif,
            'pegawai_id' => $p['pelaksana_ekonomi_1']->id,
        ]);
        Penugasan::factory()->proses()->dalamGrup($grupKolektif, Penugasan::MODE_GRUP_KOLEKTIF, koordinator: false)->create([
            ...$atributGrupKolektif,
            'pegawai_id' => $p['pelaksana_ekonomi_2']->id,
        ]);

        // Per Orang: Pelaksana IPW + Gatek Ekonomi (lintas bidang, boleh karena Gatek)
        $grupPerOrang = (string) Str::uuid();
        $atributGrupPerOrang = [
            'pemberi_tugas_id' => $p['kabid_ipw']->id,
            'jenis' => Penugasan::JENIS_TAMBAHAN,
            'nama_tugas' => '[Seed] Survei lapangan gabungan (Grup Per Orang)',
        ];
        Penugasan::factory()->dalamGrup($grupPerOrang, Penugasan::MODE_GRUP_PER_ORANG)->create([
            ...$atributGrupPerOrang,
            'pegawai_id' => $p['pelaksana_ipw']->id,
        ]);
        Penugasan::factory()->dalamGrup($grupPerOrang, Penugasan::MODE_GRUP_PER_ORANG)->create([
            ...$atributGrupPerOrang,
            'pegawai_id' => $p['gatek_ekonomi']->id,
        ]);
    }
}
