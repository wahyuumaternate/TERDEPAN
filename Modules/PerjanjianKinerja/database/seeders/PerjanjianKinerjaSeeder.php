<?php

namespace Modules\PerjanjianKinerja\Database\Seeders;

use App\Models\MasterJabatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\PerjanjianKinerja\Models\PkIndikator;
use Modules\PerjanjianKinerja\Models\PkKegiatan;
use Modules\PerjanjianKinerja\Models\PkPerjanjianKinerja;
use Modules\PerjanjianKinerja\Models\PkProgram;
use Modules\PerjanjianKinerja\Models\PkSasaran;
use Modules\PerjanjianKinerja\Models\PkSubKegiatan;
use Modules\PerjanjianKinerja\Models\PkTemplate;
use Modules\PerjanjianKinerja\Models\PkTemplateSection;

class PerjanjianKinerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Check prerequisites first
        if (! $this->checkPrerequisites()) {
            return;
        }

        DB::beginTransaction();

        try {
            $this->command->info('🌱 Seeding Perjanjian Kinerja Data...');

            // 1. Seed Templates
            $templates = $this->seedTemplates();

            // 2. Seed Template Sections
            if ($templates > 0) {
                $this->seedTemplateSections();
            }

            // 3. Seed Perjanjian Kinerja
            $pks = $this->seedPerjanjianKinerja();

            // 4. Seed Sasaran & Indikator
            if ($pks > 0) {
                $this->seedSasaranIndikator();
            }

            // 5. Seed Program, Kegiatan, Sub Kegiatan
            if ($pks > 0) {
                $this->seedProgramKegiatan();
            }

            DB::commit();

            $this->command->info('✅ Seeding completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Seeding failed: '.$e->getMessage());
            $this->command->error('Line: '.$e->getLine());
            throw $e;
        }
    }

    /**
     * Check if required master data exists
     */
    private function checkPrerequisites()
    {
        $this->command->info('🔍 Checking prerequisites...');

        // Check master_jabatan
        $jabatanCount = MasterJabatan::where('is_active', true)->count();
        if ($jabatanCount === 0) {
            $this->command->error('❌ No active jabatan found! Please seed master_jabatan first.');

            return false;
        }
        $this->command->info("   ✓ Found {$jabatanCount} active jabatan");

        // Check master_pegawai
        $pegawaiCount = User::whereRelation('profile', 'status_aktif', 'Aktif')->count();
        if ($pegawaiCount === 0) {
            $this->command->warn('⚠️  No active pegawai found! Will seed templates only.');
        } else {
            $this->command->info("   ✓ Found {$pegawaiCount} active pegawai");
        }

        $pegawaiWithAtasan = User::whereHas('profile', function ($q) {
            $q->whereNotNull('atasan_langsung_id')->where('status_aktif', 'Aktif');
        })->count();

        if ($pegawaiWithAtasan === 0) {
            $this->command->warn('⚠️  No pegawai with atasan found! Only templates will be created.');
        } else {
            $this->command->info("   ✓ Found {$pegawaiWithAtasan} pegawai with atasan");
        }

        return true;
    }

    /**
     * Seed PK Templates
     */
    private function seedTemplates()
    {
        $this->command->info('📄 Seeding PK Templates...');

        $jabatans = MasterJabatan::whereIn('kode', ['KABAN', 'SEKBAN', 'KABID'])
            ->where('is_active', true)
            ->get();

        if ($jabatans->isEmpty()) {
            $this->command->warn('⚠️  Jabatan KABAN, SEKBAN, KABID not found. Using all active structural jabatan...');
            $jabatans = MasterJabatan::where('is_active', true)
                ->where('is_struktural', true)
                ->orderBy('level')
                ->limit(3)
                ->get();
        }

        $tahun = date('Y');
        $count = 0;

        foreach ($jabatans as $jabatan) {
            // Check if template already exists
            $exists = PkTemplate::where('jabatan_id', $jabatan->id)
                ->where('tahun', $tahun)
                ->exists();

            if ($exists) {
                $this->command->warn("   ⚠️  Template for {$jabatan->nama} (tahun {$tahun}) already exists, skipping...");

                continue;
            }

            PkTemplate::create([
                'kode_template' => 'TPK-'.$jabatan->kode.'-'.$tahun,
                'nama_template' => 'Template Perjanjian Kinerja '.$jabatan->nama.' '.$tahun,
                'jabatan_id' => $jabatan->id,
                'tahun' => $tahun,
                'kop_surat_html' => $this->getKopSuratHTML(),
                'header_template' => $this->getHeaderTemplate(),
                'pernyataan_pembuka' => $this->getPernyataanPembuka(),
                'pernyataan_penutup' => $this->getPernyataanPenutup(),
                'footer_template' => $this->getFooterTemplate(),
                'page_size' => 'A4',
                'orientation' => 'Portrait',
                'versi' => 1,
                'is_active' => $count === 0, // First template is active
            ]);

            $count++;
        }

        $this->command->info("   ✓ Created {$count} templates");

        return $count;
    }

    /**
     * Seed Template Sections
     */
    private function seedTemplateSections()
    {
        $this->command->info('📑 Seeding Template Sections...');

        $templates = PkTemplate::all();
        $sectionsCount = 0;

        foreach ($templates as $template) {
            // Check if sections already exist
            if ($template->sections()->count() > 0) {
                $this->command->warn("   ⚠️  Sections for template {$template->kode_template} already exist, skipping...");

                continue;
            }

            $sections = [
                [
                    'section_code' => 'KOP_SURAT',
                    'section_name' => 'Kop Surat',
                    'section_type' => 'static',
                    'content_template' => '<div class="kop-surat">{kop_surat_html}</div>',
                    'urutan' => 1,
                    'is_required' => true,
                ],
                [
                    'section_code' => 'PEMBUKA',
                    'section_name' => 'Pernyataan Pembuka',
                    'section_type' => 'static',
                    'content_template' => '<div class="pembuka">{pernyataan_pembuka}</div>',
                    'urutan' => 2,
                    'is_required' => true,
                ],
                [
                    'section_code' => 'INFO_PEGAWAI',
                    'section_name' => 'Informasi Pegawai',
                    'section_type' => 'dynamic',
                    'content_template' => $this->getInfoPegawaiTemplate(),
                    'urutan' => 3,
                    'is_required' => true,
                ],
                [
                    'section_code' => 'SASARAN',
                    'section_name' => 'Sasaran dan Indikator',
                    'section_type' => 'table',
                    'content_template' => $this->getSasaranTableTemplate(),
                    'urutan' => 4,
                    'is_required' => true,
                ],
                [
                    'section_code' => 'PROGRAM',
                    'section_name' => 'Program dan Kegiatan',
                    'section_type' => 'table',
                    'content_template' => $this->getProgramTableTemplate(),
                    'urutan' => 5,
                    'is_required' => true,
                ],
                [
                    'section_code' => 'ANGGARAN',
                    'section_name' => 'Total Anggaran',
                    'section_type' => 'dynamic',
                    'content_template' => '<div class="total-anggaran"><strong>Total Anggaran:</strong> Rp {total_anggaran}</div>',
                    'urutan' => 6,
                    'is_required' => true,
                ],
                [
                    'section_code' => 'PENUTUP',
                    'section_name' => 'Pernyataan Penutup',
                    'section_type' => 'static',
                    'content_template' => '<div class="penutup">{pernyataan_penutup}</div>',
                    'urutan' => 7,
                    'is_required' => true,
                ],
                [
                    'section_code' => 'TTD',
                    'section_name' => 'Tanda Tangan',
                    'section_type' => 'dynamic',
                    'content_template' => $this->getTTDTemplate(),
                    'urutan' => 8,
                    'is_required' => true,
                ],
            ];

            foreach ($sections as $section) {
                PkTemplateSection::create(array_merge($section, [
                    'template_id' => $template->id,
                ]));
                $sectionsCount++;
            }
        }

        $this->command->info("   ✓ Created {$sectionsCount} template sections");

        return $sectionsCount;
    }

    /**
     * Seed Perjanjian Kinerja
     */
    private function seedPerjanjianKinerja()
    {
        $this->command->info('📋 Seeding Perjanjian Kinerja...');

        // Get pegawai with atasan
        $pegawais = User::whereHas('profile', function ($q) {
            $q->whereNotNull('atasan_langsung_id')->where('status_aktif', 'Aktif');
        })
            ->with('profile')
            ->limit(10)
            ->get();

        if ($pegawais->isEmpty()) {
            $this->command->warn('   ⚠️  No pegawai with atasan found, skipping PK creation');

            return 0;
        }

        $tahun = date('Y');
        $count = 0;

        foreach ($pegawais as $pegawai) {
            // Check if PK already exists
            $exists = PkPerjanjianKinerja::where('pegawai_id', $pegawai->id)
                ->where('tahun', $tahun)
                ->exists();

            if ($exists) {
                $this->command->warn("   ⚠️  PK for {$pegawai->nama} already exists, skipping...");

                continue;
            }

            // Get template for this pegawai's jabatan
            $template = PkTemplate::where('jabatan_id', $pegawai->profile->jabatan_id)
                ->where('tahun', $tahun)
                ->first();

            if (! $template) {
                // Fallback to any active template
                $template = PkTemplate::where('is_active', true)
                    ->where('tahun', $tahun)
                    ->first();
            }

            if (! $template) {
                $this->command->warn("   ⚠️  No template found for pegawai {$pegawai->nama}, skipping...");

                continue;
            }

            $nomor = PkPerjanjianKinerja::generateNomorPerjanjian($tahun);

            PkPerjanjianKinerja::create([
                'nomor_perjanjian' => $nomor,
                'pegawai_id' => $pegawai->id,
                'atasan_id' => $pegawai->profile?->atasan_langsung_id,
                'template_id' => $template->id,
                'tahun' => $tahun,
                'periode_mulai' => Carbon::create($tahun, 1, 1),
                'periode_selesai' => Carbon::create($tahun, 12, 31),
                'tempat_ttd' => 'Sofifi',
                'tanggal_ttd' => null,
                'total_anggaran' => 0,
                'status_dokumen' => 'Draft',
                'catatan' => 'Perjanjian Kinerja Tahun '.$tahun,
                'is_locked' => false,
            ]);

            $count++;
        }

        $this->command->info("   ✓ Created {$count} perjanjian kinerja");

        return $count;
    }

    /**
     * Seed Sasaran & Indikator
     */
    private function seedSasaranIndikator()
    {
        $this->command->info('🎯 Seeding Sasaran & Indikator...');

        $perjanjians = PkPerjanjianKinerja::all();
        $sasaranCount = 0;
        $indikatorCount = 0;

        foreach ($perjanjians as $pk) {
            // Create 3 sasaran per PK
            for ($i = 1; $i <= 3; $i++) {
                $sasaran = PkSasaran::create([
                    'perjanjian_kinerja_id' => $pk->id,
                    'urutan' => $i,
                    'sasaran_strategis' => $this->getSampleSasaran($i),
                ]);
                $sasaranCount++;

                // Create 2 indikator per sasaran
                for ($j = 1; $j <= 2; $j++) {
                    PkIndikator::create([
                        'sasaran_id' => $sasaran->id,
                        'indikator_sasaran' => $this->getSampleIndikator($i, $j),
                        'satuan' => $j == 1 ? 'Dokumen' : 'Persen',
                        'target_value' => $j == 1 ? 10 : 95,
                        'keterangan' => 'Target Tahun '.date('Y'),
                    ]);
                    $indikatorCount++;
                }
            }
        }

        $this->command->info("   ✓ Created {$sasaranCount} sasaran");
        $this->command->info("   ✓ Created {$indikatorCount} indikator");
    }

    /**
     * Seed Program, Kegiatan, Sub Kegiatan
     */
    private function seedProgramKegiatan()
    {
        $this->command->info('💼 Seeding Program, Kegiatan, Sub Kegiatan...');

        $indikators = PkIndikator::all();
        $programCount = 0;
        $kegiatanCount = 0;
        $subKegiatanCount = 0;

        // Global counter untuk unique kode
        $globalProgramCounter = 1;
        $globalKegiatanCounter = 1;
        $globalSubKegiatanCounter = 1;

        foreach ($indikators as $indikator) {
            // Create 2 program per indikator
            for ($p = 1; $p <= 2; $p++) {
                $anggaran = rand(100000000, 500000000);

                // Generate unique kode program dengan global counter
                $kodeProgram = sprintf('1.%02d.%02d.%02d',
                    $indikator->id,
                    $globalProgramCounter,
                    $p
                );

                $program = PkProgram::create([
                    'indikator_id' => $indikator->id,
                    'urutan' => $p,
                    'kode_program' => $kodeProgram,
                    'nama_program' => $this->getSampleProgram($p),
                    'anggaran' => $anggaran,
                ]);
                $programCount++;
                $globalProgramCounter++;

                // Create 2 kegiatan per program
                for ($k = 1; $k <= 2; $k++) {
                    $anggaranKegiatan = $anggaran / 2;

                    // Generate unique kode kegiatan
                    $kodeKegiatan = sprintf('%s.%02d', $kodeProgram, $globalKegiatanCounter);

                    $kegiatan = PkKegiatan::create([
                        'program_id' => $program->id,
                        'urutan' => $k,
                        'kode_kegiatan' => $kodeKegiatan,
                        'nama_kegiatan' => $this->getSampleKegiatan($p, $k),
                        'anggaran' => $anggaranKegiatan,
                    ]);
                    $kegiatanCount++;
                    $globalKegiatanCounter++;

                    // Create 2 sub kegiatan per kegiatan
                    for ($sk = 1; $sk <= 2; $sk++) {
                        // Generate unique kode sub kegiatan
                        $kodeSubKegiatan = sprintf('%s.%02d', $kodeKegiatan, $globalSubKegiatanCounter);

                        PkSubKegiatan::create([
                            'kegiatan_id' => $kegiatan->id,
                            'urutan' => $sk,
                            'kode_sub_kegiatan' => $kodeSubKegiatan,
                            'nama_sub_kegiatan' => $this->getSampleSubKegiatan($p, $k, $sk),
                            'anggaran' => $anggaranKegiatan / 2,
                            'target_value' => rand(5, 20),
                            'satuan' => $sk == 1 ? 'Dokumen' : 'Kegiatan',
                        ]);
                        $subKegiatanCount++;
                        $globalSubKegiatanCounter++;
                    }
                }
            }
        }

        $this->command->info("   ✓ Created {$programCount} program");
        $this->command->info("   ✓ Created {$kegiatanCount} kegiatan");
        $this->command->info("   ✓ Created {$subKegiatanCount} sub kegiatan");

        // Update total anggaran
        $this->updateTotalAnggaran();
    }

    /**
     * Update total anggaran perjanjian kinerja
     */
    private function updateTotalAnggaran()
    {
        $this->command->info('💰 Updating total anggaran...');

        $perjanjians = PkPerjanjianKinerja::all();
        foreach ($perjanjians as $pk) {
            $pk->calculateTotalAnggaran();
        }
    }

    // ========================================
    // SAMPLE DATA HELPERS
    // ========================================

    private function getSampleSasaran($index)
    {
        $sasaran = [
            'Meningkatkan kualitas perencanaan pembangunan daerah yang akurat dan berkelanjutan',
            'Meningkatkan kualitas monitoring dan evaluasi pembangunan daerah',
            'Meningkatkan kapasitas SDM dalam bidang perencanaan pembangunan',
        ];

        return $sasaran[$index - 1] ?? $sasaran[0];
    }

    private function getSampleIndikator($sasaranIndex, $indikatorIndex)
    {
        $indikator = [
            1 => [
                'Jumlah dokumen perencanaan yang tersusun tepat waktu',
                'Persentase kesesuaian dokumen perencanaan dengan standar',
            ],
            2 => [
                'Jumlah laporan monitoring yang disusun',
                'Persentase tindak lanjut hasil evaluasi',
            ],
            3 => [
                'Jumlah pelatihan yang dilaksanakan',
                'Persentase pegawai yang mengikuti pelatihan',
            ],
        ];

        return $indikator[$sasaranIndex][$indikatorIndex - 1] ?? 'Indikator Sasaran';
    }

    private function getSampleProgram($index)
    {
        $program = [
            'Program Perencanaan Pembangunan Daerah',
            'Program Monitoring dan Evaluasi Pembangunan',
        ];

        return $program[$index - 1] ?? 'Program '.$index;
    }

    private function getSampleKegiatan($programIndex, $kegiatanIndex)
    {
        $kegiatan = [
            1 => [
                'Penyusunan Dokumen Perencanaan RPJMD',
                'Penyusunan Dokumen RKPD',
            ],
            2 => [
                'Pelaksanaan Monitoring Pembangunan',
                'Penyusunan Laporan Evaluasi RKPD',
            ],
        ];

        return $kegiatan[$programIndex][$kegiatanIndex - 1] ?? 'Kegiatan '.$programIndex.'.'.$kegiatanIndex;
    }

    private function getSampleSubKegiatan($programIndex, $kegiatanIndex, $subIndex)
    {
        return 'Sub Kegiatan '.$programIndex.'.'.$kegiatanIndex.'.'.$subIndex.' - Pelaksanaan dan Pelaporan';
    }

    // ========================================
    // TEMPLATE HELPERS
    // ========================================

    private function getKopSuratHTML()
    {
        return '<div style="text-align: center; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px;">
            <h2 style="margin: 0; font-size: 18px;">PEMERINTAH PROVINSI MALUKU UTARA</h2>
            <h3 style="margin: 5px 0; font-size: 16px;">BADAN PERENCANAAN PEMBANGUNAN DAERAH</h3>
            <p style="margin: 5px 0; font-size: 11px;">Jl. Pertamina Sofifi, Maluku Utara</p>
        </div>';
    }

    private function getHeaderTemplate()
    {
        return '<h3 style="text-align: center; margin: 20px 0; font-size: 14px; text-decoration: underline;">PERJANJIAN KINERJA TAHUN {tahun}</h3>';
    }

    private function getPernyataanPembuka()
    {
        return '<p style="text-align: justify;">Dalam rangka mewujudkan manajemen pemerintahan yang efektif, transparan, dan akuntabel serta berorientasi pada hasil, kami yang bertanda tangan di bawah ini:</p>';
    }

    private function getPernyataanPenutup()
    {
        return '<p style="text-align: justify;">Demikian Perjanjian Kinerja ini dibuat dengan penuh tanggung jawab dan akan dilaksanakan sebaik-baiknya sesuai dengan ketentuan yang berlaku.</p>';
    }

    private function getFooterTemplate()
    {
        return '<table width="100%" style="margin-top: 30px;">
            <tr>
                <td width="50%" style="text-align: center;">
                    <p>Pihak Pertama,</p>
                    <br><br><br>
                    <p style="text-decoration: underline;">{nama_pegawai}</p>
                    <p>NIP. {nip_pegawai}</p>
                </td>
                <td width="50%" style="text-align: center;">
                    <p>Pihak Kedua,</p>
                    <br><br><br>
                    <p style="text-decoration: underline;">{nama_atasan}</p>
                    <p>NIP. {nip_atasan}</p>
                </td>
            </tr>
        </table>';
    }

    private function getInfoPegawaiTemplate()
    {
        return '<table style="width: 100%; margin: 20px 0;">
            <tr><td width="150px">Nama</td><td>: {nama_pegawai}</td></tr>
            <tr><td>NIP</td><td>: {nip_pegawai}</td></tr>
            <tr><td>Jabatan</td><td>: {jabatan_pegawai}</td></tr>
            <tr><td>Unit Kerja</td><td>: {bidang_pegawai}</td></tr>
        </table>';
    }

    private function getSasaranTableTemplate()
    {
        return '<table border="1" style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th style="padding: 8px;">No</th>
                    <th style="padding: 8px;">Sasaran Strategis</th>
                    <th style="padding: 8px;">Indikator</th>
                    <th style="padding: 8px;">Target</th>
                    <th style="padding: 8px;">Satuan</th>
                </tr>
            </thead>
            <tbody>
                {sasaran_rows}
            </tbody>
        </table>';
    }

    private function getProgramTableTemplate()
    {
        return '<table border="1" style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th style="padding: 8px;">No</th>
                    <th style="padding: 8px;">Program/Kegiatan</th>
                    <th style="padding: 8px;">Anggaran (Rp)</th>
                </tr>
            </thead>
            <tbody>
                {program_rows}
            </tbody>
        </table>';
    }

    private function getTTDTemplate()
    {
        return '<div style="margin-top: 30px;">
            <p style="text-align: right; margin-bottom: 5px;">{tempat_ttd}, {tanggal_ttd}</p>
            {footer_template}
        </div>';
    }
}
