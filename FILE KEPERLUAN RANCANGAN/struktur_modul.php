Modules/
│
├── Inti/ # Data Master & Sumber Daya Bersama
│ ├── Config/
│ ├── Database/
│ │ ├── Migrations/
│ │ │ ├── 2024_01_01_buat_tabel_master_jabatan.php
│ │ │ ├── 2024_01_01_buat_tabel_master_bidang.php
│ │ │ ├── 2024_01_01_buat_tabel_master_pegawai.php
│ │ │ └── 2024_01_01_buat_tabel_master_ttd_digital.php
│ │ ├── Seeders/
│ │ │ ├── JabatanSeeder.php
│ │ │ ├── BidangSeeder.php
│ │ │ └── PegawaiSeeder.php
│ │ └── Factories/
│ ├── Entities/
│ │ ├── Jabatan.php
│ │ ├── Bidang.php
│ │ ├── Pegawai.php
│ │ └── TtdDigital.php
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── JabatanController.php
│ │ │ ├── BidangController.php
│ │ │ ├── PegawaiController.php
│ │ │ └── TtdDigitalController.php
│ │ ├── Requests/
│ │ │ ├── JabatanRequest.php
│ │ │ ├── PegawaiRequest.php
│ │ │ └── TtdDigitalRequest.php
│ │ └── Middleware/
│ ├── Repositories/
│ │ ├── JabatanRepository.php
│ │ ├── BidangRepository.php
│ │ ├── PegawaiRepository.php
│ │ └── TtdDigitalRepository.php
│ ├── Services/
│ │ ├── JabatanService.php
│ │ ├── PegawaiService.php
│ │ └── TtdDigitalService.php
│ ├── Resources/
│ │ └── views/
│ │ ├── jabatan/
│ │ ├── bidang/
│ │ ├── pegawai/
│ │ └── ttd-digital/
│ ├── Routes/
│ │ ├── web.php
│ │ └── api.php
│ └── Providers/
│ └── IntiServiceProvider.php
│
├── Dokumen/ # Subsistem Manajemen File
│ ├── Config/
│ ├── Database/
│ │ ├── Migrations/
│ │ │ ├── 2024_01_02_buat_tabel_doc_kategori.php
│ │ │ └── 2024_01_02_buat_tabel_doc_dokumen.php
│ │ ├── Seeders/
│ │ │ └── KategoriDokumenSeeder.php
│ │ └── Factories/
│ ├── Entities/
│ │ ├── Kategori.php
│ │ └── Dokumen.php
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── KategoriController.php
│ │ │ ├── DokumenController.php
│ │ │ └── UnggahController.php
│ │ ├── Requests/
│ │ │ ├── KategoriRequest.php
│ │ │ └── DokumenRequest.php
│ │ └── Middleware/
│ ├── Repositories/
│ │ ├── KategoriRepository.php
│ │ └── DokumenRepository.php
│ ├── Services/
│ │ ├── DokumenService.php
│ │ ├── UnggahFileService.php
│ │ └── PenyimpananFileService.php
│ ├── Resources/
│ │ └── views/
│ │ ├── kategori/
│ │ ├── dokumen/
│ │ └── unggah/
│ ├── Routes/
│ │ ├── web.php
│ │ └── api.php
│ └── Providers/
│ └── DokumenServiceProvider.php
│
├── PerjanjianKinerja/ # Perjanjian Kinerja
│ ├── Config/
│ ├── Database/
│ │ ├── Migrations/
│ │ │ ├── 2024_01_03_buat_tabel_pk_template.php
│ │ │ ├── 2024_01_03_buat_tabel_pk_template_section.php
│ │ │ ├── 2024_01_03_buat_tabel_pk_perjanjian_kinerja.php
│ │ │ ├── 2024_01_03_buat_tabel_pk_dokumen.php
│ │ │ ├── 2024_01_03_buat_tabel_pk_sasaran.php
│ │ │ ├── 2024_01_03_buat_tabel_pk_indikator.php
│ │ │ ├── 2024_01_03_buat_tabel_pk_program.php
│ │ │ ├── 2024_01_03_buat_tabel_pk_kegiatan.php
│ │ │ └── 2024_01_03_buat_tabel_pk_sub_kegiatan.php
│ │ ├── Seeders/
│ │ └── Factories/
│ ├── Entities/
│ │ ├── Template.php
│ │ ├── TemplateSection.php
│ │ ├── PerjanjianKinerja.php
│ │ ├── Dokumen.php
│ │ ├── Sasaran.php
│ │ ├── Indikator.php
│ │ ├── Program.php
│ │ ├── Kegiatan.php
│ │ └── SubKegiatan.php
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── TemplateController.php
│ │ │ ├── PerjanjianKinerjaController.php
│ │ │ ├── SasaranController.php
│ │ │ └── ProgramController.php
│ │ ├── Requests/
│ │ └── Middleware/
│ ├── Repositories/
│ │ ├── TemplateRepository.php
│ │ ├── PerjanjianKinerjaRepository.php
│ │ └── SasaranRepository.php
│ ├── Services/
│ │ ├── PerjanjianKinerjaService.php
│ │ ├── TemplateService.php
│ │ ├── GeneratorDokumenService.php
│ │ └── GeneratorPdfService.php
│ ├── Resources/
│ │ └── views/
│ │ ├── template/
│ │ ├── perjanjian/
│ │ └── pdf/
│ ├── Routes/
│ │ ├── web.php
│ │ └── api.php
│ └── Providers/
│ └── PerjanjianKinerjaServiceProvider.php
│
├── Penugasan/ # Penugasan & Progress
│ ├── Config/
│ ├── Database/
│ │ ├── Migrations/
│ │ │ ├── 2024_01_04_buat_tabel_knj_tugas_pokok.php
│ │ │ ├── 2024_01_04_buat_tabel_knj_indikator_tugas.php
│ │ │ ├── 2024_01_04_buat_tabel_knj_tugas_harian.php
│ │ │ ├── 2024_01_04_buat_tabel_knj_tugas_tambahan.php
│ │ │ ├── 2024_01_04_buat_tabel_knj_penugasan_mandiri.php
│ │ │ ├── 2024_01_04_buat_tabel_knj_progress.php
│ │ │ ├── 2024_01_04_buat_tabel_knj_foto_bukti.php
│ │ │ ├── 2024_01_04_buat_tabel_knj_delegasi.php
│ │ │ └── 2024_01_04_buat_tabel_knj_workload.php
│ │ ├── Seeders/
│ │ └── Factories/
│ ├── Entities/
│ │ ├── TugasPokok.php
│ │ ├── IndikatorTugas.php
│ │ ├── TugasHarian.php
│ │ ├── TugasTambahan.php
│ │ ├── PenugasanMandiri.php
│ │ ├── Progress.php
│ │ ├── FotoBukti.php
│ │ ├── Delegasi.php
│ │ └── Workload.php
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── TugasPokokController.php
│ │ │ ├── TugasHarianController.php
│ │ │ ├── TugasTambahanController.php
│ │ │ ├── PenugasanMandiriController.php
│ │ │ ├── ProgressController.php
│ │ │ ├── DelegasiController.php
│ │ │ └── WorkloadController.php
│ │ ├── Requests/
│ │ └── Middleware/
│ ├── Repositories/
│ │ ├── TugasPokokRepository.php
│ │ ├── TugasHarianRepository.php
│ │ ├── ProgressRepository.php
│ │ ├── DelegasiRepository.php
│ │ └── WorkloadRepository.php
│ ├── Services/
│ │ ├── TugasService.php
│ │ ├── ProgressService.php
│ │ ├── DelegasiService.php
│ │ ├── WorkloadService.php
│ │ └── KalkulatorBobotService.php
│ ├── Resources/
│ │ └── views/
│ │ ├── tugas-pokok/
│ │ ├── tugas-harian/
│ │ ├── tugas-tambahan/
│ │ ├── progress/
│ │ ├── delegasi/
│ │ └── workload/
│ ├── Routes/
│ │ ├── web.php
│ │ └── api.php
│ └── Providers/
│ └── PenugasanServiceProvider.php
│
├── Evaluasi/ # Validasi & Penilaian
│ ├── Config/
│ ├── Database/
│ │ ├── Migrations/
│ │ │ ├── 2024_01_05_buat_tabel_knj_validasi.php
│ │ │ ├── 2024_01_05_buat_tabel_knj_revisi.php
│ │ │ ├── 2024_01_05_buat_tabel_knj_nilai_bulanan.php
│ │ │ └── 2024_01_05_buat_tabel_knj_nilai_tahunan.php
│ │ ├── Seeders/
│ │ └── Factories/
│ ├── Entities/
│ │ ├── Validasi.php
│ │ ├── Revisi.php
│ │ ├── NilaiBulanan.php
│ │ └── NilaiTahunan.php
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── ValidasiController.php
│ │ │ ├── RevisiController.php
│ │ │ ├── NilaiBulananController.php
│ │ │ └── NilaiTahunanController.php
│ │ ├── Requests/
│ │ └── Middleware/
│ ├── Repositories/
│ │ ├── ValidasiRepository.php
│ │ ├── RevisiRepository.php
│ │ └── NilaiRepository.php
│ ├── Services/
│ │ ├── ValidasiService.php
│ │ ├── RevisiService.php
│ │ ├── PenilaianService.php
│ │ ├── KalkulatorNilaiService.php
│ │ └── PenaltiService.php
│ ├── Resources/
│ │ └── views/
│ │ ├── validasi/
│ │ ├── revisi/
│ │ └── penilaian/
│ ├── Routes/
│ │ ├── web.php
│ │ └── api.php
│ └── Providers/
│ └── EvaluasiServiceProvider.php
│
├── Sistem/ # Sistem Pendukung
│ ├── Config/
│ ├── Database/
│ │ ├── Migrations/
│ │ │ ├── 2024_01_06_buat_tabel_sys_notifikasi.php
│ │ │ ├── 2024_01_06_buat_tabel_sys_preferensi_notifikasi.php
│ │ │ ├── 2024_01_06_buat_tabel_sys_audit_log.php
│ │ │ └── 2024_01_06_buat_tabel_sys_config.php
│ │ ├── Seeders/
│ │ │ └── ConfigSeeder.php
│ │ └── Factories/
│ ├── Entities/
│ │ ├── Notifikasi.php
│ │ ├── PreferensiNotifikasi.php
│ │ ├── AuditLog.php
│ │ └── Config.php
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── NotifikasiController.php
│ │ │ ├── AuditLogController.php
│ │ │ └── ConfigController.php
│ │ ├── Requests/
│ │ └── Middleware/
│ │ └── AuditLogMiddleware.php
│ ├── Repositories/
│ │ ├── NotifikasiRepository.php
│ │ ├── AuditLogRepository.php
│ │ └── ConfigRepository.php
│ ├── Services/
│ │ ├── NotifikasiService.php
│ │ ├── AuditLogService.php
│ │ └── ConfigService.php
│ ├── Resources/
│ │ └── views/
│ │ ├── notifikasi/
│ │ ├── audit-log/
│ │ └── config/
│ ├── Routes/
│ │ ├── web.php
│ │ └── api.php
│ └── Providers/
│ └── SistemServiceProvider.php
│
└── Dashboard/ # Dasbor & Beranda
├── Config/
├── Http/
│ └── Controllers/
│ └── DashboardController.php
├── Services/
│ ├── DashboardService.php
│ └── StatistikService.php
├── Resources/
│ └── views/
│ └── index.blade.php
├── Routes/
│ └── web.php
└── Providers/
└── DashboardServiceProvider.php