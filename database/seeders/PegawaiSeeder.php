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

            // 1. Kepala Badan (Level 1 - Paling Atas, No Atasan)
            $kaban = $this->createPegawai([
                'nomor_identitas' => '198104201999121001',
                'nama' => 'MUHAMMAD SARMIN S. ADAM',
                'jabatan_id' => $jabatans['KABAN'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'kaban@bappeda.go.id',
                'pangkat' => 'Pembina Tk. I',
                'golongan' => 'IV/b',
                'gelar_depan' => 'Dr.',
                'gelar_belakang' => 'S.STP, M.Si',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => null,
            ]);
            $this->command->info('   ✓ Created: Kepala Badan (No Atasan)');

            // 2. Sekretaris Badan (Level 2 - Atasan: Kaban)
            $sekban = $this->createPegawai([
                'nomor_identitas' => '197302232002121006',
                'nama' => 'HERIFAL NALY THOMAS',
                'jabatan_id' => $jabatans['SEKBAN'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'sekban@bappeda.go.id',
                'pangkat' => 'Pembina Tk. I',
                'golongan' => 'IV/b',
                'gelar_belakang' => 'ST',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kaban,
            ]);
            $this->command->info('   ✓ Created: Sekretaris Badan (Atasan: Kaban)');

            // 3. Penelaah Teknis Kebijakan (Level 2 - Atasan: Kaban)
            $penelaah = $this->createPegawai([
                'nomor_identitas' => '197901051997121000',
                'nama' => 'JUNAIDI SOAMOLE',
                'jabatan_id' => $jabatans['JAFUNG'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'junaidi.soamole@bappeda.go.id',
                'pangkat' => 'Pembina Tk. I',
                'golongan' => 'IV/b',
                'gelar_belakang' => 'S.STP, M.Si',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kaban,
            ]);
            $this->command->info('   ✓ Created: Penelaah Teknis Kebijakan (Atasan: Kaban)');

            // 4. JF. Analis Keuangan (Level 2 - Atasan: Kaban)
            $this->createPegawai([
                'nomor_identitas' => '197403282003122009',
                'nama' => 'KURAISIN A.HASAN',
                'jabatan_id' => $jabatans['JAFUNG'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'kuraisin.hasan@bappeda.go.id',
                'pangkat' => 'Pembina',
                'golongan' => 'IV/a',
                'gelar_belakang' => 'SP',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kaban,
            ]);
            $this->command->info('   ✓ Created: JF. Analis Keuangan (Atasan: Kaban)');

            // 5. Kasubbag Kepegawaian dan Umum (Level 3 - Atasan: Sekban)
            $kasubbagKepeg = $this->createPegawai([
                'nomor_identitas' => '198505132006021001',
                'nama' => 'HENDRA PERDANA',
                'jabatan_id' => $jabatans['KABID'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'hendra.perdana@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'S.STP',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $sekban,
            ]);
            $this->command->info('   ✓ Created: Kasubbag Kepegawaian dan Umum (Atasan: Sekban)');

            // 6. Kasubbag Perencanaan dan Program (Level 3 - Atasan: Sekban)
            $kasubbagPlan = $this->createPegawai([
                'nomor_identitas' => '199001022010102001',
                'nama' => 'RIZKA WURI HANDAYANI',
                'jabatan_id' => $jabatans['KABID'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'rizka.handayani@bappeda.go.id',
                'pangkat' => 'Penata',
                'golongan' => 'III/c',
                'gelar_belakang' => 'S.IP',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $sekban,
            ]);
            $this->command->info('   ✓ Created: Kasubbag Perencanaan dan Program (Atasan: Sekban)');

            // 7-12. Staff Analis (Atasan sesuai bidang)
            $this->createPegawai([
                'nomor_identitas' => '196909282005012016',
                'nama' => 'NURMIATY ISMAIL',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'nurmiaty.ismail@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'SP',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kasubbagKepeg,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '197404272001122001',
                'nama' => 'NURLAILA B. HI. IBRAHIM',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'nurlaila.ibrahim@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'SP',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '197904192000082001',
                'nama' => 'SAIBA MUHAMMAD ZEN',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['EVAL'],
                'email' => 'saiba.zen@bappeda.go.id',
                'pangkat' => 'Penata',
                'golongan' => 'III/c',
                'gelar_belakang' => 'SE',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kaban,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '198004112006042012',
                'nama' => 'RIRIZ OLIVIA PANDJAB',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'ririz.pandjab@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'S.IK',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '197103272002121007',
                'nama' => 'MAHMUD AHMAD',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['EVAL'],
                'email' => 'mahmud.ahmad@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'S.Sos',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kaban,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '198003092009022001',
                'nama' => 'AMALIYAH DJAFAR',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'amaliyah.djafar@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'SE',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kasubbagKepeg,
            ]);

            $this->command->info('   ✓ Created: 6 Staff Analis');

            // 13-17. Staff Level III/c - III/d
            $this->createPegawai([
                'nomor_identitas' => '198104172011012003',
                'nama' => 'IRMAWATI KARIM',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'irmawati.karim@bappeda.go.id',
                'pangkat' => 'Penata',
                'golongan' => 'III/c',
                'gelar_belakang' => 'SE',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kasubbagKepeg,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '198703042010012002',
                'nama' => 'NURYANA',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['DATA'],
                'email' => 'nuryana@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'S.SI',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kaban,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '198012072010011007',
                'nama' => 'LUKMAN DJABID',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'lukman.djabid@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'S.HUT',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '198205012009031001',
                'nama' => 'RUSLAN ABDUL KADIR',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'ruslan.kadir@bappeda.go.id',
                'pangkat' => 'Penata',
                'golongan' => 'III/c',
                'gelar_belakang' => 'A.MD',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kasubbagKepeg,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '199204182014062001',
                'nama' => 'RAHMAWATI UMAR',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'rahmawati.umar@bappeda.go.id',
                'pangkat' => 'Penata',
                'golongan' => 'III/c',
                'gelar_belakang' => 'S.STP',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->command->info('   ✓ Created: 5 Staff Level III/c - III/d');

            // 18-20. Staff Level III/a - III/b
            $this->createPegawai([
                'nomor_identitas' => '198201272003122003',
                'nama' => 'AFI A.DO YUNAN',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'afi.yunan@bappeda.go.id',
                'pangkat' => 'Penata Muda Tk. I',
                'golongan' => 'III/b',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kasubbagKepeg,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '199709082019081001',
                'nama' => 'ACHMAD QABIRRUL RIFAI',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'achmad.rifai@bappeda.go.id',
                'pangkat' => 'Penata Muda Tk. I',
                'golongan' => 'III/b',
                'gelar_belakang' => 'S.STP',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kasubbagKepeg,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '200201072024092001',
                'nama' => 'FITRI SYAIROH NURSHANY',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'fitri.nurshany@bappeda.go.id',
                'pangkat' => 'Penata Muda',
                'golongan' => 'III/a',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->command->info('   ✓ Created: 3 Staff Level III/a - III/b');

            // 21-23. Staff Level II/d dan JF/PPPK
            $this->createPegawai([
                'nomor_identitas' => '198203082007011011',
                'nama' => 'ARI JOKJA',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'ari.jokja@bappeda.go.id',
                'pangkat' => 'Pengatur',
                'golongan' => 'II/d',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kasubbagKepeg,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '198301182023212019',
                'nama' => 'ERNAWATY',
                'jabatan_id' => $jabatans['JAFUNG'],
                'bidang_id' => $bidangs['DATA'],
                'email' => 'ernawaty@bappeda.go.id',
                'pangkat' => 'IX',
                'golongan' => 'IX',
                'gelar_belakang' => 'ST',
                'jenis_kelamin' => 'P',
                'status_kepegawaian' => 'PNS',
                'atasan_langsung_id' => $kaban,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '198312182025211000',
                'nama' => 'FARDI A.HI.DJAUHAR',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['SEKRET'],
                'email' => 'fardi.djauhar@bappeda.go.id',
                'pangkat' => 'V',
                'golongan' => 'V',
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => 'PPPK',
                'atasan_langsung_id' => $kasubbagKepeg,
            ]);

            $this->command->info('   ✓ Created: 3 Staff Level II/d, JF, dan PPPK');

            // 24-32. Tenaga Teknis (Non-PNS)
            $tenagaTeknis = [
                ['nama' => 'RAMDANI', 'gelar' => 'SE', 'jk' => 'L'],
                ['nama' => 'SARTIKA MUDRIK', 'gelar' => 'S.Sos', 'jk' => 'P'],
                ['nama' => 'KURNIAWATI F. ABDULLAH', 'gelar' => 'ST', 'jk' => 'P'],
                ['nama' => 'RIAN IRAWAN', 'gelar' => 'ST', 'jk' => 'L'],
                ['nama' => 'MUSLIM MUSTAAN', 'gelar' => 'ST', 'jk' => 'L'],
                ['nama' => 'RIFYAL KA\'BAH ODE IYA', 'gelar' => 'ST', 'jk' => 'L'],
                ['nama' => 'MUHAMMAD REZA PAHLEVI TANASSY', 'gelar' => 'ST', 'jk' => 'L'],
                ['nama' => 'IRAWAN WAHYU PANGESTU', 'gelar' => 'S.Ak', 'jk' => 'L'],
                ['nama' => 'CHICI NURUL MARYAM', 'gelar' => 'S.Ak', 'jk' => 'P'],
            ];

            $nomorTT = 10001;
            foreach ($tenagaTeknis as $tt) {
                $this->createPegawai([
                    'nomor_identitas' => 'TT' . $nomorTT++,
                    'nama' => $tt['nama'],
                    'jabatan_id' => $jabatans['TT'],
                    'bidang_id' => $bidangs['SEKRET'],
                    'email' => strtolower(str_replace([' ', '.', '\''], ['', '', ''], $tt['nama'])) . '@bappeda.go.id',
                    'pangkat' => null,
                    'golongan' => null,
                    'gelar_belakang' => $tt['gelar'],
                    'jenis_kelamin' => $tt['jk'],
                    'status_kepegawaian' => 'Kontrak',
                    'atasan_langsung_id' => $sekban,
                ]);
            }
            $this->command->info('   ✓ Created: 9 Tenaga Teknis (Atasan: Sekban)');

            // 33-43. Pegawai Bidang Perencanaan (Data Tambahan)
            $this->createPegawai([
                'nomor_identitas' => '198408202011011002',
                'nama' => 'ZULKARNAIN ABD. LATIF',
                'jabatan_id' => $jabatans['JAFUNG'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'zulkarnain.latif@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'ST',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kaban,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '196908042001121005',
                'nama' => 'ROSIHAN THAMRIN',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'rosihan.thamrin@bappeda.go.id',
                'pangkat' => 'Pembina',
                'golongan' => 'IV/d',
                'gelar_belakang' => 'SH',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '197609092003122008',
                'nama' => 'NURHAYA UMASANGAJI',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'nurhaya.umasangaji@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'SE',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '198303172009032002',
                'nama' => 'NURMARDIYANTI',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'nurmardiyanti@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'ST',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '197601252009031001',
                'nama' => 'MUHAMMAD YAMIN NOH BAILUSY',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'yamin.bailusy@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'SH',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '198606082010012037',
                'nama' => 'ROSNIA HI.SALAM',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'rosnia.salam@bappeda.go.id',
                'pangkat' => 'Penata Tk. I',
                'golongan' => 'III/d',
                'gelar_belakang' => 'S.Pt',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '199407112019031004',
                'nama' => 'RAHMAT JULASRI WANBOKO',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'rahmat.wanboko@bappeda.go.id',
                'pangkat' => 'Penata Muda',
                'golongan' => 'III/a',
                'gelar_belakang' => 'A.Md',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '198509012010012001',
                'nama' => 'SITI SARAH HI. SALIM HAMDJA',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'siti.hamdja@bappeda.go.id',
                'pangkat' => 'Pengatur',
                'golongan' => 'II/c',
                'jenis_kelamin' => 'P',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '199704212025041004',
                'nama' => 'SURYA PRATAMA JUMSAR',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'surya.jumsar@bappeda.go.id',
                'pangkat' => 'Penata Muda',
                'golongan' => 'III/a',
                'gelar_belakang' => 'S.T',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => '199708312025041004',
                'nama' => 'MUHAMMAD SYAHRUL',
                'jabatan_id' => $jabatans['STF'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'muhammad.syahrul@bappeda.go.id',
                'pangkat' => 'Penata Muda',
                'golongan' => 'III/a',
                'gelar_belakang' => 'S.T',
                'jenis_kelamin' => 'L',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->createPegawai([
                'nomor_identitas' => 'TT10010',
                'nama' => 'SAHDIRAN M. SALEH',
                'jabatan_id' => $jabatans['TT'],
                'bidang_id' => $bidangs['PLAN'],
                'email' => 'sahdiran.saleh@bappeda.go.id',
                'pangkat' => null,
                'golongan' => null,
                'gelar_belakang' => 'ST',
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => 'Kontrak',
                'atasan_langsung_id' => $kasubbagPlan,
            ]);

            $this->command->info('   ✓ Created: 11 Pegawai Bidang Perencanaan (10 PNS + 1 TT)');

            DB::commit();

            $totalPegawai = DB::table('master_pegawai')->count();
            $withAtasan = DB::table('master_pegawai')->whereNotNull('atasan_langsung_id')->count();

            $this->command->info('');
            $this->command->info("✅ Total Pegawai: {$totalPegawai}");
            $this->command->info("✅ Pegawai dengan Atasan: {$withAtasan}");
            $this->command->info('');
            $this->command->line('Hierarki Organisasi:');
            $this->command->line('└── Kepala Badan (1) - IV/b');
            $this->command->line('    ├── Sekretaris Badan (1) - IV/b');
            $this->command->line('    │   ├── Kasubbag Kepegawaian (1) - III/d');
            $this->command->line('    │   │   └── Staff (6)');
            $this->command->line('    │   ├── Kasubbag Perencanaan (1) - III/c');
            $this->command->line('    │   │   └── Staff (16) - 15 PNS + 1 TT');
            $this->command->line('    │   └── Tenaga Teknis (9)');
            $this->command->line('    ├── Jabatan Fungsional (4)');
            $this->command->line('    └── Staff Analis Langsung (3)');
            $this->command->info('');
            $this->command->info('📊 Ringkasan Status Kepegawaian:');
            $this->command->info('   • PNS: 33 orang');
            $this->command->info('   • PPPK: 1 orang');
            $this->command->info('   • Kontrak/Tenaga Teknis: 10 orang');
            $this->command->info('   • TOTAL: 44 pegawai');
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
        $bidangCount = DB::table('master_bidang')->count();
        if ($bidangCount === 0) {
            $this->command->error('❌ Table master_bidang is empty!');
            $this->command->warn('Creating default bidang...');
            $this->createDefaultBidang();
        }

        $bidangs = [
            'SEKRETARIAT' => DB::table('master_bidang')->where('kode', 'SEKRETARIAT')->value('id'),
            'EKONOMI' => DB::table('master_bidang')->where('kode', 'EKONOMI')->value('id'),
            'IPW' => DB::table('master_bidang')->where('kode', 'IPW')->value('id'),
            'SOSBUD' => DB::table('master_bidang')->where('kode', 'SOSBUD')->value('id'),
            'PERAN' => DB::table('master_bidang')->where('kode', 'PERAN')->value('id'),
        ];

        foreach ($bidangs as $kode => $id) {
            if (!$id) {
                $this->command->error("❌ Bidang {$kode} not found!");
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
        if (isset($pegawaiData['nomor_identitas']) && strlen($pegawaiData['nomor_identitas']) >= 8 && is_numeric(substr($pegawaiData['nomor_identitas'], 0, 8))) {
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
