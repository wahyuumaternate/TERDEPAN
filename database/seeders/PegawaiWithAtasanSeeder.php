<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PegawaiWithAtasanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->command->info('🌱 Seeding Pegawai with Hierarki Atasan...');

        DB::beginTransaction();
        try {
            // Get jabatan IDs
            $jabatans = $this->getJabatanIds();
            if (!$jabatans) {
                return;
            }

            // Get bidang IDs
            $bidangs = $this->getBidangIds();
            if (!$bidangs) {
                return;
            }

            // 1. Kepala Bappeda (Level 1 - Paling Atas, No Atasan)
            $kaban = $this->createPegawai([
                'nomor_identitas' => '199001012015011001',
                'nama' => 'Dr. Ahmad Suryanto',
                'jabatan_id' => $jabatans['KABAN'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'kaban@bappeda.go.id',
                'pangkat' => 'Pembina Utama Muda',
                'golongan' => 'IV/c',
                'gelar_depan' => 'Dr.',
                'gelar_belakang' => 'M.Si.',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => null, // Kepala Badan tidak punya atasan
            ]);
            $this->command->info('   ✓ Created: Kepala Bappeda (No Atasan)');

            // 2. Sekretaris Bappeda (Level 2 - Atasan: Kaban)
            $sekban = $this->createPegawai([
                'nomor_identitas' => '199002022015011002',
                'nama' => 'Ir. Budi Santoso',
                'jabatan_id' => $jabatans['SEKBAN'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'sekban@bappeda.go.id',
                'pangkat' => 'Pembina Tk. I',
                'golongan' => 'IV/b',
                'gelar_depan' => 'Ir.',
                'gelar_belakang' => 'M.T.',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kaban,
            ]);
            $this->command->info('   ✓ Created: Sekretaris Bappeda (Atasan: Kaban)');

            // 3. Kepala Bidang Perencanaan (Level 3 - Atasan: Kaban)
            $kabidPlan = $this->createPegawai([
                'nomor_identitas' => '199103032015011003',
                'nama' => 'Siti Nurhaliza',
                'jabatan_id' => $jabatans['KABID'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'kabid.plan@bappeda.go.id',
                'pangkat' => 'Pembina',
                'golongan' => 'IV/a',
                'gelar_depan' => null,
                'gelar_belakang' => 'S.T., M.Si.',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kaban,
            ]);
            $this->command->info('   ✓ Created: Kabid Perencanaan (Atasan: Kaban)');

            // 4. Kepala Bidang Evaluasi (Level 3 - Atasan: Kaban)
            $kabidEval = $this->createPegawai([
                'nomor_identitas' => '199204042015011004',
                'nama' => 'Drs. Hendra Wijaya',
                'jabatan_id' => $jabatans['KABID'],
                'bidang_id' => $bidangs['EVAL'],
                'email' => 'kabid.eval@bappeda.go.id',
                'pangkat' => 'Pembina',
                'golongan' => 'IV/a',
                'gelar_depan' => 'Drs.',
                'gelar_belakang' => 'M.M.',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kaban,
            ]);
            $this->command->info('   ✓ Created: Kabid Evaluasi (Atasan: Kaban)');

            // 5. Kepala Bidang Data (Level 3 - Atasan: Kaban)
            $kabidData = $this->createPegawai([
                'nomor_identitas' => '199305052015011005',
                'nama' => 'Muhammad Ridwan',
                'jabatan_id' => $jabatans['KABID'],
                'bidang_id' => $bidangs['DATA'],
                'email' => 'kabid.data@bappeda.go.id',
                'pangkat' => 'Pembina',
                'golongan' => 'IV/a',
                'gelar_depan' => null,
                'gelar_belakang' => 'S.Kom., M.Kom.',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kaban,
            ]);
            $this->command->info('   ✓ Created: Kabid Data (Atasan: Kaban)');

            // 6-8. Staff Bidang Perencanaan (Atasan: Kabid Plan)
            $this->createPegawai([
                'nomor_identitas' => '199406062016011006',
                'nama' => 'Andi Prasetyo',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'andi.prasetyo@bappeda.go.id',
                'pangkat' => 'Penata Muda Tk. I',
                'golongan' => 'III/b',
                'gelar_belakang' => 'S.E.',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kabidPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '199507072016012007',
                'nama' => 'Dewi Lestari',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'dewi.lestari@bappeda.go.id',
                'pangkat' => 'Penata Muda',
                'golongan' => 'III/a',
                'gelar_belakang' => 'S.T.',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kabidPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '199608082017011008',
                'nama' => 'Rudi Hartono',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'rudi.hartono@bappeda.go.id',
                'pangkat' => 'Penata Muda Tk. I',
                'golongan' => 'III/b',
                'gelar_belakang' => 'S.Si.',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kabidPlan,
            ]);
            $this->command->info('   ✓ Created: 3 Staff Bidang Perencanaan (Atasan: Kabid Plan)');

            // 9-11. Staff Bidang Evaluasi (Atasan: Kabid Eval)
            $this->createPegawai([
                'nomor_identitas' => '199709092017012009',
                'nama' => 'Sari Wulandari',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['EVAL'],
                'email' => 'sari.wulandari@bappeda.go.id',
                'pangkat' => 'Penata Muda',
                'golongan' => 'III/a',
                'gelar_belakang' => 'S.Sos.',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kabidEval,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '199810102018011010',
                'nama' => 'Agus Setiawan',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['EVAL'],
                'email' => 'agus.setiawan@bappeda.go.id',
                'pangkat' => 'Penata Muda',
                'golongan' => 'III/a',
                'gelar_belakang' => 'S.Pd.',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kabidEval,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '199911112018012011',
                'nama' => 'Linda Permata',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['EVAL'],
                'email' => 'linda.permata@bappeda.go.id',
                'pangkat' => 'Penata Muda Tk. I',
                'golongan' => 'III/b',
                'gelar_belakang' => 'S.E.',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kabidEval,
            ]);
            $this->command->info('   ✓ Created: 3 Staff Bidang Evaluasi (Atasan: Kabid Eval)');

            // 12-13. Staff Bidang Data (Atasan: Kabid Data)
            $this->createPegawai([
                'nomor_identitas' => '200012122019011012',
                'nama' => 'Yudi Pratama',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['DATA'],
                'email' => 'yudi.pratama@bappeda.go.id',
                'pangkat' => 'Penata Muda',
                'golongan' => 'III/a',
                'gelar_belakang' => 'S.Kom.',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kabidData,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '200101132019012013',
                'nama' => 'Fitri Handayani',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['DATA'],
                'email' => 'fitri.handayani@bappeda.go.id',
                'pangkat' => 'Penata Muda',
                'golongan' => 'III/a',
                'gelar_belakang' => 'S.Si.',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kabidData,
            ]);
            $this->command->info('   ✓ Created: 2 Staff Bidang Data (Atasan: Kabid Data)');

            // 14-15. Jabatan Fungsional (Atasan: Kaban)
            $this->createPegawai([
                'nomor_identitas' => '199802142019011014',
                'nama' => 'Dr. Bambang Setiadi',
                'jabatan_id' => $jabatans['JAFUNG'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'bambang.setiadi@bappeda.go.id',
                'pangkat' => 'Pembina',
                'golongan' => 'IV/a',
                'gelar_depan' => 'Dr.',
                'gelar_belakang' => 'M.Si.',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kaban,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '199903152020012015',
                'nama' => 'Maya Kusuma',
                'jabatan_id' => $jabatans['JAFUNG'],
                'bidang_id' => $bidangs['DATA'],
                'email' => 'maya.kusuma@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'S.Si., M.Si.',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kaban,
            ]);
            $this->command->info('   ✓ Created: 2 Jabatan Fungsional (Atasan: Kaban)');

            // 16. Tenaga Teknis (Bebas Nilai, Atasan: Sekban)
            $this->createPegawai([
                'nomor_identitas' => '200004162021011016',
                'nama' => 'Arif Budiman',
                'jabatan_id' => $jabatans['TT'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'arif.budiman@bappeda.go.id',
                'pangkat' => null,
                'golongan' => null,
                'gelar_belakang' => 'S.Kom.',
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => 'Kontrak',
                'atasan_langsung_id' => $sekban,
            ]);
            $this->command->info('   ✓ Created: 1 Tenaga Teknis (Atasan: Sekban, Bebas Nilai)');

            DB::commit();
            
            $totalPegawai = DB::table('master_pegawai')->count();
            $withAtasan = DB::table('master_pegawai')->whereNotNull('atasan_langsung_id')->count();
            
            $this->command->info('');
            $this->command->info("✅ Total Pegawai: {$totalPegawai}");
            $this->command->info("✅ Pegawai dengan Atasan: {$withAtasan}");
            $this->command->info('');
            $this->command->line('Hierarki Organisasi:');
            $this->command->line('└── Kepala Bappeda (1)');
            $this->command->line('    ├── Sekretaris Bappeda (1)');
            $this->command->line('    │   └── Tenaga Teknis (1)');
            $this->command->line('    ├── Kepala Bidang (3)');
            $this->command->line('    │   ├── Bidang Perencanaan → Staff (3)');
            $this->command->line('    │   ├── Bidang Evaluasi → Staff (3)');
            $this->command->line('    │   └── Bidang Data → Staff (2)');
            $this->command->line('    └── Jabatan Fungsional (2)');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('Line: ' . $e->getLine());
            throw $e;
        }
    }

    /**
     * Get Jabatan IDs - Sesuai dengan kode yang digunakan
     */
    private function getJabatanIds()
    {
        $jabatans = [
            'KABAN' => DB::table('master_jabatan')->where('kode', 'KABAN')->value('id'),
            'SEKBAN' => DB::table('master_jabatan')->where('kode', 'SEKBAN')->value('id'),
            'KABID' => DB::table('master_jabatan')->where('kode', 'KABID')->value('id'),
            'JAFUNG' => DB::table('master_jabatan')->where('kode', 'JAFUNG')->value('id'),
            'STF' => DB::table('master_jabatan')->where('kode', 'STF')->value('id'),
            'TT' => DB::table('master_jabatan')->where('kode', 'TT')->value('id'),
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
     * Get Bidang IDs
     */
    private function getBidangIds()
    {
        // Check if bidang exists
        $bidangCount = DB::table('master_bidang')->count();
        if ($bidangCount === 0) {
            $this->command->error('❌ Table master_bidang is empty!');
            $this->command->warn('Creating default bidang...');
            $this->createDefaultBidang();
        }

        $bidangs = [
            'SEKRET' => DB::table('master_bidang')->where('kode', 'SEKRET')->value('id'),
            'PLAN' => DB::table('master_bidang')->where('kode', 'PLAN')->value('id'),
            'EVAL' => DB::table('master_bidang')->where('kode', 'EVAL')->value('id'),
            'DATA' => DB::table('master_bidang')->where('kode', 'DATA')->value('id'),
        ];

        // Validate all bidang IDs exist
        foreach ($bidangs as $kode => $id) {
            if (!$id) {
                $this->command->error("❌ Bidang {$kode} not found!");
                $this->command->warn('Available bidang codes:');
                $availableBidangs = DB::table('master_bidang')->pluck('kode')->toArray();
                $this->command->warn(implode(', ', $availableBidangs));
                
                // Use first available bidang as fallback
                $firstBidang = DB::table('master_bidang')->first();
                if ($firstBidang) {
                    $this->command->warn("Using fallback bidang: {$firstBidang->kode}");
                    $bidangs[$kode] = $firstBidang->id;
                } else {
                    return null;
                }
            }
        }

        return $bidangs;
    }

    /**
     * Create default bidang if not exists
     */
    private function createDefaultBidang()
    {
        $bidangs = [
            ['kode' => 'SEKRET', 'nama' => 'Sekretariat', 'warna' => '#3B82F6'],
            ['kode' => 'PLAN', 'nama' => 'Bidang Perencanaan', 'warna' => '#10B981'],
            ['kode' => 'EVAL', 'nama' => 'Bidang Evaluasi', 'warna' => '#F59E0B'],
            ['kode' => 'DATA', 'nama' => 'Bidang Data dan Statistik', 'warna' => '#8B5CF6'],
        ];

        foreach ($bidangs as $bidang) {
            DB::table('master_bidang')->insert(array_merge($bidang, [
                'deskripsi' => 'Deskripsi ' . $bidang['nama'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('   ✓ Created 4 default bidang');
    }

    /**
     * Create Pegawai
     */
    private function createPegawai($data)
    {
        $defaults = [
            'tipe_identitas' => 'NIP',
            'password' => Hash::make('password'),
            'no_telepon' => '0812' . rand(10000000, 99999999),
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Sofifi, Maluku Utara',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'tanggal_masuk' => '2015-01-01',
            'tanggal_keluar' => null,
            'foto_profile_path' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];

        $pegawaiData = array_merge($defaults, $data);

        // Format tanggal_lahir from NIP (YYYYMMDD)
        if (isset($pegawaiData['nomor_identitas']) && strlen($pegawaiData['nomor_identitas']) >= 8) {
            $nipDate = substr($pegawaiData['nomor_identitas'], 0, 8);
            $year = substr($nipDate, 0, 4);
            $month = substr($nipDate, 4, 2);
            $day = substr($nipDate, 6, 2);
            $pegawaiData['tanggal_lahir'] = "{$year}-{$month}-{$day}";
        }

        // Check if pegawai already exists
        $exists = DB::table('master_pegawai')
            ->where('nomor_identitas', $pegawaiData['nomor_identitas'])
            ->exists();

        if ($exists) {
            $this->command->warn('   ⚠️  Pegawai ' . $pegawaiData['nama'] . ' already exists, skipping...');
            return DB::table('master_pegawai')
                ->where('nomor_identitas', $pegawaiData['nomor_identitas'])
                ->value('id');
        }

        return DB::table('master_pegawai')->insertGetId($pegawaiData);
    }
}