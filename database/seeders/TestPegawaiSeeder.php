<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MasterPegawai;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class TestPegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // Get jabatan and bidang IDs
            $jabatanIds = $this->getJabatanIds();
            $bidangIds = $this->getBidangIds();

            if (!$jabatanIds || !$bidangIds) {
                $this->command->error('❌ Error: Required jabatan or bidang not found!');
                return;
            }

            $allPegawai = []; // Store all created pegawai for display

            $baseIdent = 200000000000000000; // will increment for unique nomor_identitas

            // KABAN (jabatan_id from KABAN kode)
            $kaban = MasterPegawai::create([
                'nomor_identitas' => (string)($baseIdent + 1),
                'tipe_identitas' => 'NIP',
                'nama' => 'Kaban Test',
                'jabatan_id' => $jabatanIds['KABAN'],
                'bidang_id' => $bidangIds['BAPPEDA'] ?? 1,
                'email' => 'kaban@test.local',
                'password' => Hash::make('password'),
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subYears(12)->toDateString(),
                'status_aktif' => 'Aktif',
            ]);
            $allPegawai[] = $kaban;

            // SEKBAN, direct report to KABAN
            $sekban = MasterPegawai::create([
                'nomor_identitas' => (string)($baseIdent + 2),
                'tipe_identitas' => 'NIP',
                'nama' => 'Sekban Test',
                'jabatan_id' => $jabatanIds['SEKBAN'],
                'bidang_id' => $bidangIds['SEKRETARIAT'],
                'email' => 'sekban@test.local',
                'password' => Hash::make('password'),
                'jenis_kelamin' => 'P',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subYears(10)->toDateString(),
                'atasan_langsung_id' => $kaban->id,
                'status_aktif' => 'Aktif',
            ]);
            $allPegawai[] = $sekban;

            // 1 JF (Jabatan Fungsional) - report to SEKBAN
            $jfSekretariat = MasterPegawai::create([
                'nomor_identitas' => (string)($baseIdent + 3),
                'tipe_identitas' => 'NIP',
                'nama' => 'JF Sekretariat Test',
                'jabatan_id' => $jabatanIds['JAFUNG'],
                'bidang_id' => $bidangIds['SEKRETARIAT'],
                'email' => 'jf.sekretariat@test.local',
                'password' => Hash::make('password'),
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subYears(5)->toDateString(),
                'atasan_langsung_id' => $sekban->id,
                'status_aktif' => 'Aktif',
            ]);
            $allPegawai[] = $jfSekretariat;

            // 1 KASUBAG - report to SEKBAN
            $kasubag = MasterPegawai::create([
                'nomor_identitas' => (string)($baseIdent + 4),
                'tipe_identitas' => 'NIP',
                'nama' => 'Kasubag Sekretariat Test',
                'jabatan_id' => $jabatanIds['KASUBAG'],
                'bidang_id' => $bidangIds['SEKRETARIAT'],
                'sub_bidang_id' => 1,
                'email' => 'kasubag.sekretariat@test.local',
                'password' => Hash::make('password'),
                'jenis_kelamin' => 'P',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subYears(3)->toDateString(),
                'atasan_langsung_id' => $sekban->id,
                'status_aktif' => 'Aktif',
            ]);
            $allPegawai[] = $kasubag;

            // 1 PELAKSANA - assign under Kasubag
            $pelaksanaSekretariat = MasterPegawai::create([
                'nomor_identitas' => (string)($baseIdent + 5),
                'tipe_identitas' => 'NIP',
                'nama' => 'Pelaksana Sekretariat Test',
                'jabatan_id' => $jabatanIds['PELAKSANA'],
                'bidang_id' => $bidangIds['SEKRETARIAT'],
                'sub_bidang_id' => 1,
                'email' => 'pelaksana.sekretariat@test.local',
                'password' => Hash::make('password'),
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subYears(1)->toDateString(),
                'atasan_langsung_id' => $kasubag->id,
                'status_aktif' => 'Aktif',
            ]);
            $allPegawai[] = $pelaksanaSekretariat;

            // ==========================================
            // KABID Bidang IPW dengan Hierarki
            // KABID -> JF -> PELAKSANA -> GATEK
            // ==========================================
            $kabidIpw = MasterPegawai::create([
                'nomor_identitas' => (string)($baseIdent + 20),
                'tipe_identitas' => 'NIP',
                'nama' => 'Kabid IPW Test',
                'jabatan_id' => $jabatanIds['KABID'],
                'bidang_id' => $bidangIds['IPW'],
                'email' => 'kabid.ipw@test.local',
                'password' => Hash::make('password'),
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subYears(8)->toDateString(),
                'atasan_langsung_id' => $kaban->id,
                'status_aktif' => 'Aktif',
            ]);
            $allPegawai[] = $kabidIpw;

            // 1 JF (Jabatan Fungsional) - report to KABID IPW
            $jfIpw = MasterPegawai::create([
                'nomor_identitas' => (string)($baseIdent + 21),
                'tipe_identitas' => 'NIP',
                'nama' => 'JF IPW Test',
                'jabatan_id' => $jabatanIds['JAFUNG'],
                'bidang_id' => $bidangIds['IPW'],
                'email' => 'jf.ipw@test.local',
                'password' => Hash::make('password'),
                'jenis_kelamin' => 'P',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subYears(4)->toDateString(),
                'atasan_langsung_id' => $kabidIpw->id,
                'status_aktif' => 'Aktif',
            ]);
            $allPegawai[] = $jfIpw;

            // 1 PELAKSANA - report to JF IPW
            $pelaksanaIpw = MasterPegawai::create([
                'nomor_identitas' => (string)($baseIdent + 22),
                'tipe_identitas' => 'NIP',
                'nama' => 'Pelaksana IPW Test',
                'jabatan_id' => $jabatanIds['PELAKSANA'],
                'bidang_id' => $bidangIds['IPW'],
                'email' => 'pelaksana.ipw@test.local',
                'password' => Hash::make('password'),
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subYears(2)->toDateString(),
                'atasan_langsung_id' => $jfIpw->id,
                'status_aktif' => 'Aktif',
            ]);
            $allPegawai[] = $pelaksanaIpw;

            // 1 GATEK - report to Pelaksana IPW (menggunakan format GATEK001)
            $gatekIpw = MasterPegawai::create([
                'nomor_identitas' => 'GATEK001',
                'tipe_identitas' => 'NIP',
                'nama' => 'Gatek IPW Test',
                'jabatan_id' => $jabatanIds['GATEK'],
                'bidang_id' => $bidangIds['IPW'],
                'email' => 'gatek.ipw@test.local',
                'password' => Hash::make('password'),
                'jenis_kelamin' => 'P',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subMonths(3)->toDateString(),
                'atasan_langsung_id' => $pelaksanaIpw->id,
                'status_aktif' => 'Aktif',
            ]);
            $allPegawai[] = $gatekIpw;

            // 1 GATEK - assign under Pelaksana Sekretariat - menggunakan format GATEK002
            $gatekSekretariat = MasterPegawai::create([
                'nomor_identitas' => 'GATEK002',
                'tipe_identitas' => 'NIP',
                'nama' => 'Gatek Sekretariat Test',
                'jabatan_id' => $jabatanIds['GATEK'],
                'bidang_id' => $bidangIds['SEKRETARIAT'],
                'sub_bidang_id' => 1,
                'email' => 'gatek.sekretariat@test.local',
                'password' => Hash::make('password'),
                'jenis_kelamin' => 'P',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subMonths(6)->toDateString(),
                'atasan_langsung_id' => $pelaksanaSekretariat->id,
                'status_aktif' => 'Aktif',
            ]);
            $allPegawai[] = $gatekSekretariat;

            DB::commit();

            // Display table
            $this->displayPegawaiTable($allPegawai);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('Line: ' . $e->getLine());
            throw $e;
        }
    }

    /**
     * Get Jabatan IDs from database
     */
    private function getJabatanIds(): ?array
    {
        $jabatans = [
            'KABAN' => DB::table('master_jabatan')->where('kode', 'KABAN')->value('id'),
            'SEKBAN' => DB::table('master_jabatan')->where('kode', 'SEKBAN')->value('id'),
            'KABID' => DB::table('master_jabatan')->where('kode', 'KABID')->value('id'),
            'KASUBAG' => DB::table('master_jabatan')->where('kode', 'KASUBAG')->value('id'),
            'JAFUNG' => DB::table('master_jabatan')->where('kode', 'JAFUNG')->value('id'),
            'PELAKSANA' => DB::table('master_jabatan')->where('kode', 'PELAKSANA')->value('id'),
            'GATEK' => DB::table('master_jabatan')->where('kode', 'GATEK')->value('id'),
        ];

        foreach ($jabatans as $kode => $id) {
            if (!$id) {
                $this->command->error("❌ Jabatan {$kode} not found!");
                $this->command->warn('Please run: php artisan db:seed --class=MasterJabatanSeeder');
                return null;
            }
        }

        return $jabatans;
    }

    /**
     * Get Bidang IDs from database
     */
    private function getBidangIds(): ?array
    {
        $bidangs = [
            'BAPPEDA' => DB::table('master_bidang')->where('kode', 'BAPPEDA')->value('id'),
            'SEKRETARIAT' => DB::table('master_bidang')->where('kode', 'SEKRETARIAT')->value('id'),
            'IPW' => DB::table('master_bidang')->where('kode', 'IPW')->value('id'),
        ];

        // If IPW not found, try to get any bidang as fallback
        if (!$bidangs['IPW']) {
            $bidangs['IPW'] = DB::table('master_bidang')->where('is_active', true)->first()?->id ?? 1;
        }

        if (!$bidangs['IPW']) {
            $this->command->error("❌ Bidang IPW not found!");
            $this->command->warn('Please run: php artisan db:seed --class=MasterBidangSeeder');
            return null;
        }

        return $bidangs;
    }

    /**
     * Display pegawai table after seeding
     */
    private function displayPegawaiTable(array $pegawaiList): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════════════════════════════');
        $this->command->info('                        DAFTAR USER YANG BERHASIL DICREATE                               ');
        $this->command->info('═══════════════════════════════════════════════════════════════════════════════════════');
        $this->command->info('');

        // Load relations for all pegawai
        $pegawaiIds = array_map(fn($p) => $p->id, $pegawaiList);
        $pegawaiWithRelations = MasterPegawai::with(['jabatan'])
            ->whereIn('id', $pegawaiIds)
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        // Table header
        $headers = ['No', 'Nama', 'Jabatan', 'NIP/ID', 'Password'];
        $rows = [];

        foreach ($pegawaiList as $index => $pegawai) {
            $pegawaiLoaded = $pegawaiWithRelations->get($pegawai->id);
            
            $jabatan = $pegawaiLoaded->jabatan->nama ?? '-';

            $rows[] = [
                $index + 1,
                $pegawaiLoaded->nama,
                $jabatan,
                $pegawaiLoaded->nomor_identitas,
                'password', // Semua user menggunakan password default "password"
            ];
        }

        // Display table
        $this->command->table($headers, $rows);

        // Summary
        $totalCount = count($pegawaiList);
        $this->command->info('');
        $this->command->info("✅ Total {$totalCount} user berhasil dibuat!");
        $this->command->info('ℹ️  Semua user menggunakan password default: password');
        $this->command->info('');
    }
}
