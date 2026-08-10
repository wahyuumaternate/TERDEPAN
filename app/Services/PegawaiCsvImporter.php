<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Import pegawai dari CSV (header baris pertama, pemisah ";") — dipakai bersama oleh
 * Database\Seeders\DataPegawaiSeeder (data pengembangan) dan fitur import di halaman
 * Kelola Data Pegawai (docs/flow/02-alur-autentikasi.md §Kelola Data Pegawai poin 3),
 * supaya logic mapping jabatan/bidang/atasan tidak dobel ditulis di dua tempat.
 *
 * Kolom yang dibaca: Nama, Email, Jabatan, Bidang, "NIP / ID", "Gelar Depan",
 * "Gelar Belakang", "No Telpon", "Jenis Kelamin", "Tanggal Lahir", Alamat,
 * "Status Kepeg", "Status Aktif", Pangkat, Golongan, "Tanggal Masuk", "Atasan Langsung".
 *
 * Kolom opsional tambahan (mengikuti data DUK/Daftar Urut Kepangkatan, lihat
 * docs/DUK..BEZZETING BAPPEDA PER. MARET 2026 (1).csv): "Tempat Lahir", Agama,
 * "TMT CPNS", "TMT PNS", "TMT Golongan", Eselon, "Pendidikan Terakhir", "Jenjang Pendidikan".
 * Semua boleh kosong — CSV lama tanpa kolom-kolom ini tetap berfungsi normal.
 *
 * Password pegawai hasil import selalu default 'password' + must_change_password=true,
 * mengikuti alur "Login Awal - Mandiri" di dokumen.
 */
class PegawaiCsvImporter
{
    /**
     * @return array{berhasil: array<int, User>, dilewati: array<int, array{baris: int, nama: string, alasan: string}>}
     */
    public function importFromFile(string $filePath): array
    {
        return $this->import($this->parseCsv($filePath));
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     * @return array{berhasil: array<int, User>, dilewati: array<int, array{baris: int, nama: string, alasan: string}>}
     */
    public function import(array $rows): array
    {
        $berhasil = [];
        $dilewati = [];
        $pegawaiMap = []; // nama -> User, untuk lookup atasan

        DB::transaction(function () use ($rows, &$berhasil, &$dilewati, &$pegawaiMap) {
            // Pass 1: buat semua pegawai tanpa atasan dulu.
            foreach ($rows as $index => $row) {
                $baris = $index + 2; // +1 header, +1 karena index 0-based

                $nama = trim($row['Nama'] ?? '');
                if ($nama === '') {
                    $dilewati[] = ['baris' => $baris, 'nama' => '(kosong)', 'alasan' => 'Kolom Nama kosong'];

                    continue;
                }

                $email = $this->generateEmail($row);
                if (User::where('email', $email)->exists()) {
                    $dilewati[] = ['baris' => $baris, 'nama' => $nama, 'alasan' => "Email {$email} sudah terdaftar"];

                    continue;
                }

                $jabatanName = trim($row['Jabatan'] ?? '');
                if ($jabatanName === '' && stripos($nama, 'admin') !== false) {
                    $jabatanName = 'Pelaksana';
                }

                $jabatanId = $this->getJabatanIdFromName($jabatanName);
                $bidangId = $this->getBidangIdFromName($row['Bidang'] ?? '');

                if (! $jabatanId || ! $bidangId) {
                    $dilewati[] = [
                        'baris' => $baris,
                        'nama' => $nama,
                        'alasan' => "Jabatan/Bidang tidak dikenali (Jabatan: {$jabatanName}, Bidang: ".($row['Bidang'] ?? '').')',
                    ];

                    continue;
                }

                $nip = trim($row['NIP / ID'] ?? '');
                $tipeIdentitas = (strlen($nip) > 0 && $nip !== 'ADMIN') ? 'NIP' : 'ID';
                if ($nip === 'ADMIN' || $nip === '') {
                    $nip = 'ADMIN001';
                    $tipeIdentitas = 'ID';
                }

                if ($nip !== 'ADMIN001' && \App\Models\UserProfile::where('nomor_identitas', $nip)->exists()) {
                    $dilewati[] = ['baris' => $baris, 'nama' => $nama, 'alasan' => "Nomor identitas {$nip} sudah terdaftar"];

                    continue;
                }

                $pegawai = User::create([
                    'nama' => $nama,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'must_change_password' => true,
                ]);

                $pegawai->profile()->create([
                    'nomor_identitas' => $nip,
                    'tipe_identitas' => $tipeIdentitas,
                    'gelar_depan' => trim($row['Gelar Depan'] ?? ''),
                    'gelar_belakang' => trim($row['Gelar Belakang'] ?? ''),
                    'no_telepon' => trim($row['No Telpon'] ?? ''),
                    'jenis_kelamin' => $this->parseJenisKelamin($row['Jenis Kelamin'] ?? ''),
                    'tanggal_lahir' => $this->parseTanggal($row['Tanggal Lahir'] ?? ''),
                    'alamat' => trim($row['Alamat'] ?? ''),
                    'jabatan_id' => $jabatanId,
                    'bidang_id' => $bidangId,
                    'status_kepegawaian' => trim($row['Status Kepeg'] ?? '') ?: 'PNS',
                    'status_aktif' => trim($row['Status Aktif'] ?? '') ?: 'Aktif',
                    'pangkat' => trim($row['Pangkat'] ?? ''),
                    'golongan' => trim($row['Golongan'] ?? ''),
                    'tanggal_masuk' => $this->parseTanggal($row['Tanggal Masuk'] ?? '') ?? now()->toDateString(),
                    'tempat_lahir' => trim($row['Tempat Lahir'] ?? ''),
                    'agama' => trim($row['Agama'] ?? ''),
                    'tmt_cpns' => $this->parseTanggal($row['TMT CPNS'] ?? ''),
                    'tmt_pns' => $this->parseTanggal($row['TMT PNS'] ?? ''),
                    'tmt_golongan' => $this->parseTanggal($row['TMT Golongan'] ?? ''),
                    'eselon' => trim($row['Eselon'] ?? ''),
                    'pendidikan_terakhir' => trim($row['Pendidikan Terakhir'] ?? ''),
                    'jenjang_pendidikan' => trim($row['Jenjang Pendidikan'] ?? ''),
                ]);

                $berhasil[] = $pegawai;
                $pegawaiMap[$nama] = $pegawai;
            }

            // Pass 2: link atasan_langsung_id sekarang semua pegawai baru sudah ada.
            $rowsByNama = [];
            foreach ($rows as $row) {
                $rowsByNama[trim($row['Nama'] ?? '')] = $row;
            }

            foreach ($berhasil as $pegawai) {
                $atasanName = trim($rowsByNama[$pegawai->nama]['Atasan Langsung'] ?? '');
                if ($atasanName === '') {
                    continue;
                }

                $atasan = $pegawaiMap[$atasanName] ?? null;
                if (! $atasan) {
                    foreach ($pegawaiMap as $mapNama => $mapPegawai) {
                        if (strtoupper(trim($mapNama)) === strtoupper($atasanName)) {
                            $atasan = $mapPegawai;
                            break;
                        }
                    }
                }

                if ($atasan) {
                    $pegawai->profile()->update(['atasan_langsung_id' => $atasan->id]);
                }
            }
        });

        return ['berhasil' => $berhasil, 'dilewati' => $dilewati];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function parseCsv(string $filePath): array
    {
        $data = [];
        $file = fopen($filePath, 'r');

        $header = fgetcsv($file, 0, ';');

        while (($row = fgetcsv($file, 0, ';')) !== false) {
            if (count($row) === count($header)) {
                $data[] = array_combine($header, $row);
            }
        }

        fclose($file);

        return $data;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function generateEmail(array $row): string
    {
        $email = trim($row['Email'] ?? '');

        if ($email === '') {
            $nama = strtolower(trim($row['Nama'] ?? ''));
            $nama = str_replace(' ', '.', $nama);
            $email = $nama.'@bappeda.local';
        }

        return $email;
    }

    private function parseJenisKelamin(string $value): string
    {
        $value = strtoupper(trim($value));

        return ($value === 'P' || $value === 'PEREMPUAN') ? 'P' : 'L';
    }

    private function parseTanggal(string $value): ?string
    {
        $value = str_replace(' ', '', trim($value));

        if ($value === '') {
            return null;
        }

        $parts = explode('-', $value);

        if (count($parts) === 3) {
            return sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);
        }

        return null;
    }

    private function getJabatanIdFromName(string $jabatanName): ?int
    {
        $jabatanName = strtolower(trim($jabatanName));

        $mapping = [
            'plh kabid' => 'KABID',
            'kepala badan' => 'KABAN',
            'sekretari badan' => 'SEKBAN',
            'kepala bidang' => 'KABID',
            'kabid ipw' => 'KABID',
            'kabid' => 'KABID',
            'kepala sub bagian' => 'KASUBAG',
            'kasubag' => 'KASUBAG',
            'pelaksana' => 'PELAKSANA',
        ];

        $kode = null;
        foreach ($mapping as $key => $value) {
            if (stripos($jabatanName, $key) !== false) {
                $kode = $value;
                break;
            }
        }

        if (! $kode && (stripos($jabatanName, 'jf ') !== false || stripos($jabatanName, 'jabatan fungsional') !== false)) {
            $kode = 'JAFUNG';
        }

        if (! $kode) {
            return null;
        }

        return DB::table('master_jabatan')->where('kode', $kode)->value('id');
    }

    private function getBidangIdFromName(string $bidangName): ?int
    {
        $bidangName = trim($bidangName);

        if ($bidangName === '') {
            return null;
        }

        $id = DB::table('master_bidang')->where('nama', $bidangName)->value('id');
        if ($id) {
            return $id;
        }

        $id = DB::table('master_bidang')->where('kode', strtoupper($bidangName))->value('id');
        if ($id) {
            return $id;
        }

        return DB::table('master_bidang')->where('nama', 'like', "%{$bidangName}%")->value('id');
    }
}
