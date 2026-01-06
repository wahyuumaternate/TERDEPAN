<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MasterPegawai;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class DataPegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $this->command->info('🔄 Memulai import data pegawai dari CSV files...');

            // Get base path for CSV files
            $basePath = base_path('FILE KEPERLUAN RANCANGAN');

            // Read all CSV files
            $csvFiles = [
                $basePath . '/TERDEPAN Data Pegawai 1.csv',
                $basePath . '/TERDEPAN Data Pegawai 2.csv',
                $basePath . '/TERDEPAN Data Pegawai 3.csv',
            ];

            // Parse all CSV data
            $allData = [];
            foreach ($csvFiles as $file) {
                if (!file_exists($file)) {
                    $this->command->warn("⚠️  File not found: {$file}");
                    continue;
                }

                $data = $this->parseCSV($file);
                $allData = array_merge($allData, $data);
            }

            if (empty($allData)) {
                $this->command->error('❌ No data found in CSV files!');
                return;
            }

            // Process and create pegawai
            $createdPegawai = $this->processPegawaiData($allData);

            DB::commit();

            // Display table
            $this->displayPegawaiTable($createdPegawai);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('Line: ' . $e->getLine());
            throw $e;
        }
    }

    /**
     * Parse CSV file
     */
    private function parseCSV(string $filePath): array
    {
        $data = [];
        $file = fopen($filePath, 'r');

        // Read header
        $header = fgetcsv($file, 0, ';');

        // Read data rows
        while (($row = fgetcsv($file, 0, ';')) !== false) {
            if (count($row) === count($header)) {
                $rowData = array_combine($header, $row);
                $data[] = $rowData;
            }
        }

        fclose($file);
        return $data;
    }

    /**
     * Process pegawai data from CSV and create records
     */
    private function processPegawaiData(array $data): array
    {
        $createdPegawai = [];
        $pegawaiMap = []; // Map nama -> pegawai object for atasan lookup

        // First pass: Create all pegawai without atasan
        foreach ($data as $row) {
            $nama = trim($row['Nama']);
            $email = $this->generateEmail($row);

            // Handle ADMIN SISTEM special case
            $jabatanName = trim($row['Jabatan']);
            if (empty($jabatanName) && stripos($nama, 'admin') !== false) {
                $jabatanName = 'Pelaksana'; // Default untuk admin sistem
            }

            $jabatanId = $this->getJabatanIdFromName($jabatanName);
            $bidangId = $this->getBidangIdFromName($row['Bidang']);

            if (!$jabatanId || !$bidangId) {
                $this->command->warn("⚠️  Skipping {$nama}: Jabatan or Bidang not found (Jabatan: {$jabatanName}, Bidang: {$row['Bidang']})");
                continue;
            }

            // Determine tipe_identitas
            $nip = trim($row['NIP / ID']);
            $tipeIdentitas = (strlen($nip) > 0 && $nip !== 'ADMIN') ? 'NIP' : 'ID';
            if ($nip === 'ADMIN' || empty($nip)) {
                $nip = 'ADMIN001';
                $tipeIdentitas = 'ID';
            }

            $pegawai = MasterPegawai::create([
                'nomor_identitas' => $nip,
                'tipe_identitas' => $tipeIdentitas,
                'gelar_depan' => trim($row['Gelar Depan'] ?? ''),
                'nama' => $nama,
                'gelar_belakang' => trim($row['Gelar Belakang'] ?? ''),
                'email' => $email,
                'no_telepon' => trim($row['No Telpon'] ?? ''),
                'jenis_kelamin' => $this->parseJenisKelamin($row['Jenis Kelamin']),
                'tanggal_lahir' => $this->parseTanggalLahir($row['Tanggal Lahir'] ?? ''),
                'alamat' => trim($row['Alamat'] ?? ''),
                'jabatan_id' => $jabatanId,
                'bidang_id' => $bidangId,
                'status_kepegawaian' => trim($row['Status Kepeg']) ?: 'PNS',
                'status_aktif' => trim($row['Status Aktif']) ?: 'Aktif',
                'pangkat' => trim($row['Pangkat'] ?? ''),
                'golongan' => trim($row['Golongan'] ?? ''),
                'tanggal_masuk' => $this->parseTanggalMasuk($row['Tanggal Masuk'] ?? ''),
                'password' => Hash::make('password'),
            ]);

            $createdPegawai[] = $pegawai;

            // Store in map using exact nama for lookup
            $pegawaiMap[$nama] = $pegawai;
        }

        // Second pass: Update atasan_langsung_id
        foreach ($data as $index => $row) {
            $atasanName = trim($row['Atasan Langsung'] ?? '');

            if (!empty($atasanName) && isset($createdPegawai[$index])) {
                // Try exact match first
                $atasan = $pegawaiMap[$atasanName] ?? null;

                // If not found, try to find by partial match (case-insensitive)
                if (!$atasan) {
                    foreach ($pegawaiMap as $mapNama => $mapPegawai) {
                        // Check if atasan name matches (ignoring case and extra spaces)
                        if (strtoupper(trim($mapNama)) === strtoupper(trim($atasanName))) {
                            $atasan = $mapPegawai;
                            break;
                        }
                    }
                }

                if ($atasan) {
                    $createdPegawai[$index]->update([
                        'atasan_langsung_id' => $atasan->id
                    ]);
                    $this->command->info("✓ {$createdPegawai[$index]->nama} → Atasan: {$atasan->nama}");
                } else {
                    $this->command->warn("⚠️  Atasan tidak ditemukan untuk {$createdPegawai[$index]->nama}: '{$atasanName}'");
                }
            }
        }

        return $createdPegawai;
    }

    /**
     * Generate email from CSV row
     */
    private function generateEmail(array $row): string
    {
        $email = trim($row['Email'] ?? '');

        if (empty($email)) {
            // Generate email from nama
            $nama = strtolower(trim($row['Nama']));
            $nama = str_replace(' ', '.', $nama);
            $email = $nama . '@bappeda.local';
        }

        return $email;
    }

    /**
     * Parse jenis kelamin
     */
    private function parseJenisKelamin(string $value): string
    {
        $value = strtoupper(trim($value));

        if ($value === 'P' || $value === 'PEREMPUAN') {
            return 'P';
        }

        return 'L';
    }

    /**
     * Parse tanggal lahir
     */
    private function parseTanggalLahir(string $value): ?string
    {
        $value = trim($value);

        if (empty($value)) {
            return null;
        }

        // Format: "20 - 04 - 1981"
        $value = str_replace(' ', '', $value);
        $parts = explode('-', $value);

        if (count($parts) === 3) {
            return sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);
        }

        return null;
    }

    /**
     * Parse tanggal masuk
     */
    private function parseTanggalMasuk(string $value): ?string
    {
        $value = trim($value);

        if (empty($value)) {
            return now()->toDateString();
        }

        return $this->parseTanggalLahir($value);
    }

    /**
     * Get Jabatan ID from jabatan name
     */
    private function getJabatanIdFromName(string $jabatanName): ?int
    {
        $jabatanName = strtolower(trim($jabatanName));

        // Map jabatan names to codes (order matters - check specific patterns first)
        $mapping = [
            'plh kabid' => 'KABID',  // PLH KABID = Pelaksana Harian Kepala Bidang
            'kepala badan' => 'KABAN',
            'sekretari badan' => 'SEKBAN',
            'kepala bidang' => 'KABID',
            'kabid ipw' => 'KABID',
            'kabid' => 'KABID',
            'kepala sub bagian' => 'KASUBAG',
            'kasubag' => 'KASUBAG',
            'pelaksana' => 'PELAKSANA',
        ];

        // Check mapping first (this handles PLH KABID before JF check)
        $kode = null;
        foreach ($mapping as $key => $value) {
            if (stripos($jabatanName, $key) !== false) {
                $kode = $value;
                break;
            }
        }

        // If not found, check for JF (Jabatan Fungsional)
        if (!$kode && (stripos($jabatanName, 'jf ') !== false || stripos($jabatanName, 'jabatan fungsional') !== false)) {
            $kode = 'JAFUNG';
        }

        if (!$kode) {
            return null;
        }

        return DB::table('master_jabatan')->where('kode', $kode)->value('id');
    }

    /**
     * Get Bidang ID from bidang name
     */
    private function getBidangIdFromName(string $bidangName): ?int
    {
        $bidangName = trim($bidangName);

        if (empty($bidangName)) {
            return null;
        }

        // Try exact match first
        $id = DB::table('master_bidang')
            ->where('nama', $bidangName)
            ->value('id');

        if ($id) {
            return $id;
        }

        // Try by kode (uppercase)
        $kode = strtoupper($bidangName);
        $id = DB::table('master_bidang')
            ->where('kode', $kode)
            ->value('id');

        if ($id) {
            return $id;
        }

        // Try partial match
        $id = DB::table('master_bidang')
            ->where('nama', 'like', "%{$bidangName}%")
            ->value('id');

        return $id;
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
        $pegawaiWithRelations = MasterPegawai::with(['jabatan', 'bidang', 'atasanLangsung'])
            ->whereIn('id', $pegawaiIds)
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        // Table header
        $headers = ['No', 'Nama', 'Jabatan', 'Bidang', 'NIP/ID', 'Email', 'Atasan'];
        $rows = [];

        foreach ($pegawaiList as $index => $pegawai) {
            $pegawaiLoaded = $pegawaiWithRelations->get($pegawai->id);

            $jabatan = $pegawaiLoaded->jabatan->nama ?? '-';
            $bidang = $pegawaiLoaded->bidang->nama ?? '-';
            $atasan = $pegawaiLoaded->atasanLangsung->nama ?? '-';

            $rows[] = [
                $index + 1,
                $pegawaiLoaded->nama,
                $jabatan,
                $bidang,
                $pegawaiLoaded->nomor_identitas,
                $pegawaiLoaded->email,
                $atasan,
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
