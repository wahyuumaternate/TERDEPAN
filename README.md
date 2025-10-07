# SISTEM TERDEPAN - BAPPEDA MALUKU UTARA

![Heartware Digital Logo](public/img/logo_heartware.png)

## Terintegrasi, Responsif, Dinamis, Efektif, Profesional, Akuntabel, iNovatif

---

## Tentang TERDEPAN

TERDEPAN adalah sistem manajemen terintegrasi yang dikembangkan oleh **Heartware Digital** untuk BAPPEDA Maluku Utara, yang berfokus pada dua fungsi utama:

1. **Manajemen Kinerja Pegawai** (Prioritas Utama)
2. **Penyimpanan Data & Dokumen Internal** (Sistem Pendukung)

Sistem ini dirancang untuk meningkatkan akuntabilitas, transparansi, efisiensi, dan efektivitas pekerjaan di lingkungan BAPPEDA Maluku Utara melalui digitalisasi dan otomatisasi proses manajemen kinerja.

## Fitur Utama

### 1. Manajemen Kinerja Pegawai

#### a. Penugasan Berjenjang
- **Tugas Pokok** (60-70% bobot): Tugas tahunan sesuai Perjanjian Kinerja
- **Tugas Harian/Mingguan** (20-30% bobot): Tugas turunan dari Tugas Pokok
- **Tugas Tambahan** (maks. 20% bobot): Tugas non-rutin dari atasan
- **Penugasan Mandiri**: Inisiatif pegawai untuk mendukung Tugas Pokok

#### b. Tracking & Monitoring
- Update progres real-time dengan bukti foto
- Validasi tugas dengan sistem revisi (24 jam)
- Monitoring beban kerja dengan kategori (Normal, Moderate, Heavy, Overload)
- Sistem pencegahan overload (maksimal 3 tugas aktif)

#### c. Penilaian Kinerja
- Perhitungan nilai otomatis dengan bobot proporsional
- Penerapan penalti untuk keterlambatan atau delegasi
- Evaluasi bulanan dan tahunan
- Dashboard kinerja per pegawai, bidang, dan organisasi

#### d. Manajemen Risiko
- Delegasi tugas dengan sistem penalti yang adil
- Alert otomatis untuk keterlambatan progres
- Pencatatan riwayat beban kerja

### 2. Penyimpanan Data & Dokumen

#### a. Manajemen Dokumen
- Penyimpanan terstruktur berdasarkan bidang, jenis, dan periode
- Versioning dokumen otomatis
- Pencarian cepat dan filter dokumen
- Integrasi dengan dokumen Perjanjian Kinerja

#### b. Keamanan Dokumen
- Hak akses berbasis peran
- Digital signature untuk dokumen penting
- Audit trail untuk setiap aktivitas dokumen

## Struktur Organisasi BAPPEDA Maluku Utara

Sistem TERDEPAN telah disesuaikan dengan struktur organisasi BAPPEDA Maluku Utara:

- **Kepala BAPPEDA**
- **Sekretariat**
  - Sub Bagian Umum dan Kepegawaian
  - Sub Bagian Keuangan
  - Sub Bagian Program dan Pelaporan
- **Bidang Perencanaan Ekonomi**
- **Bidang Perencanaan Sosial Budaya**
- **Bidang Perencanaan Infrastruktur dan Pengembangan Wilayah**
- **Bidang Pengendalian, Evaluasi, dan Pelaporan**
- **Kelompok Jabatan Fungsional**

## Role Pengguna

- **Kepala BAPPEDA**: Monitoring organisasi, evaluasi kinerja seluruh BAPPEDA
- **Sekretaris BAPPEDA**: Monitoring lintas bidang, koordinasi bidang
- **Kepala Bidang**: Pengelolaan kinerja bidang, penugasan, validasi
- **Jabatan Fungsional**: Pelaksanaan tugas sesuai Perjanjian Kinerja
- **Pelaksana**: Pelaksanaan tugas operasional
- **Tenaga Teknis**: Support lintas bidang, bebas dari penilaian kinerja

## Teknologi

- **Backend**: Laravel 10 (PHP Framework)
- **Mobile App**: Flutter (Cross-platform)
- **Database**: MySQL
- **Authentication**: JWT dengan NIP/NIK
- **Notifikasi**: Firebase Cloud Messaging, Email
- **Integrasi**: SIMPEG untuk data pegawai

## Persyaratan Sistem

### Server Requirements
- PHP >= 8.1
- MySQL >= 5.7
- Composer
- Node.js & NPM
- Redis (opsional untuk caching)

### Mobile Requirements
- Android 6.0 (Marshmallow) atau lebih tinggi
- iOS 12 atau lebih tinggi
- Akses kamera untuk dokumentasi progres

## Instalasi

```bash
# Clone repository
git clone https://github.com/wahyuumaternate/TERDEPAN.git
cd terdepan

# Install dependencies
composer install
npm install
npm run build

# Set environment variables
cp .env.example .env
php artisan key:generate

# Migrate database
php artisan migrate

# Seed data awal
php artisan db:seed

# Start server
php artisan serve
```

## Panduan Penggunaan


Beberapa panduan cepat:
1. Login menggunakan NIP (untuk ASN) atau NIK (untuk Tenaga Teknis)
2. Gunakan dashboard sesuai peran Anda
3. Untuk atasan: gunakan menu "Penugasan" untuk membuat tugas baru
4. Untuk pegawai: update progres tugas secara berkala
5. Untuk semua: monitor nilai kinerja pada dashboard

## Alur Kerja Utama

1. Atasan membuat Tugas Pokok berdasarkan Perjanjian Kinerja
2. Pegawai menerima dan mulai mengerjakan tugas
3. Atasan membuat Tugas Harian untuk mendukung Tugas Pokok
4. Pegawai melakukan update progres dengan bukti
5. Atasan memvalidasi progres dan hasil akhir
6. Jika perlu revisi, pegawai diberi waktu 24 jam
7. Sistem otomatis menghitung nilai kinerja
8. Dashboard menampilkan nilai dan statistik kinerja

## Manajemen Risiko

Sistem TERDEPAN memiliki beberapa fitur manajemen risiko:

1. **Pencegahan Overload**: Batasan maksimal 3 tugas aktif per pegawai
2. **Delegasi Tugas**: Jika progres lambat, tugas dapat didelegasi ke pegawai lain
3. **Penalti Nilai**: Pengurangan nilai untuk keterlambatan atau delegasi
4. **Alert Otomatis**: Peringatan untuk progres lambat
5. **Monitoring Beban**: Dashboard heatmap beban kerja tim

## Integrasi dengan Renstra BAPPEDA Maluku Utara

TERDEPAN secara otomatis mengintegrasikan Rencana Strategis (Renstra) BAPPEDA Maluku Utara ke dalam sistem manajemen kinerja untuk memastikan bahwa:

1. Tugas Pokok sesuai dengan target strategis organisasi
2. Penilaian kinerja selaras dengan indikator strategis
3. Pencapaian KPI organisasi dapat dipantau real-time
4. Program prioritas Pemprov Maluku Utara terintegrasi dalam penugasan

## Tim Pengembang

### Heartware Digital

Sistem TERDEPAN dikembangkan oleh **Heartware Digital**, perusahaan IT yang berfokus pada solusi digital pemerintahan. Tim Heartware Digital bertanggung jawab untuk:

- Analisis kebutuhan dan perancangan sistem
- Pengembangan backend (Laravel) dan frontend (Flutter)
- Pengembangan database dan API
- Deployment dan konfigurasi server
- Pelatihan dan pendampingan
- Pemeliharaan dan pengembangan lanjutan

Heartware Digital bekerja erat dengan tim BAPPEDA Maluku Utara untuk memastikan sistem TERDEPAN sesuai dengan kebutuhan spesifik organisasi dan mendukung tata kelola pemerintahan yang baik.

Komposisi tim Heartware Digital yang terlibat dalam pengembangan TERDEPAN:
- Project Manager
- Business Analyst
- Backend Developer (Laravel)
- Frontend Developer (Flutter)
- UI/UX Designer
- Database Administrator
- QA Engineer
- DevOps Engineer

## Support & Bantuan

Jika mengalami kesulitan dalam penggunaan TERDEPAN, silakan hubungi:

- **Technical Support**: support@heartware.digital
- **Administrator BAPPEDA**: admin@terdepan-bappeda.malutprov.go.id
- **Heartware Digital**: info@heartware.digital
- **Helpdesk**: (0921) 3121358

## Kontributor

- Tim Heartware Digital
- Tim IT BAPPEDA Maluku Utara
- Divisi Pengembangan Aplikasi Diskominfo Maluku Utara

---

&copy; 2025 BAPPEDA Maluku Utara | Developed by Heartware Digital | TERDEPAN v1.0