<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Dokumen\Models\JenisDokumen;
use Modules\Dokumen\Models\NomorCounter;
use App\Models\MasterBidang;

class NomorDokumenService
{
    /**
     * Generate nomor dokumen otomatis
     * 
     * @param int $jenisId
     * @param int $bidangId
     * @param \Carbon\Carbon|null $tanggal
     * @return array|null
     */
    public function generateNomorDokumen($jenisId, $bidangId, $tanggal = null)
    {
        try {
            $tanggal = $tanggal ?? now();
            $tahun = $tanggal->year;

            Log::info('Generate Nomor Dokumen', [
                'jenis_id' => $jenisId,
                'bidang_id' => $bidangId,
                'tahun' => $tahun
            ]);

            // Get jenis dokumen
            $jenis = JenisDokumen::findOrFail($jenisId);

            // Check apakah jenis ini perlu nomor
            if (!$jenis->perlu_nomor) {
                Log::info('Jenis dokumen tidak perlu nomor otomatis');
                return null;
            }

            // Validasi format nomor
            if (empty($jenis->nomor_format)) {
                throw new \Exception('Format nomor belum diatur untuk jenis dokumen ini');
            }

            // Get bidang
            $bidang = MasterBidang::findOrFail($bidangId);

            Log::info('Jenis & Bidang found', [
                'jenis' => $jenis->nama,
                'bidang' => $bidang->nama,
                'format' => $jenis->nomor_format
            ]);

            // Get atau create counter dengan transaction
            $nomorUrut = DB::transaction(function () use ($jenisId, $bidangId, $tahun) {
                // Get atau create counter
                $counter = NomorCounter::firstOrCreate(
                    [
                        'jenis_id' => $jenisId,
                        'bidang_id' => $bidangId,
                        'tahun' => $tahun
                    ],
                    ['counter' => 0]
                );

                Log::info('Counter sebelum increment', ['counter' => $counter->counter]);

                // Lock row untuk mencegah race condition
                $counter = NomorCounter::where('id', $counter->id)
                    ->lockForUpdate()
                    ->first();

                // Increment counter
                $counter->increment('counter');
                $counter->refresh();

                Log::info('Counter setelah increment', ['counter' => $counter->counter]);

                return $counter->counter;
            });

            // Generate nomor berdasarkan format
            $nomorFormatted = $this->formatNomor(
                $jenis->nomor_format,
                $nomorUrut,
                $jenis->kode,
                $bidang->kode,
                $tanggal
            );

            Log::info('Nomor dokumen generated', ['nomor' => $nomorFormatted]);

            return [
                'nomor' => $nomorFormatted,
                'nomor_urut' => $nomorUrut,
                'tahun' => $tahun,
                'jenis' => $jenis->nama,
                'jenis_kode' => $jenis->kode,
                'bidang' => $bidang->nama,
                'bidang_kode' => $bidang->kode,
                'format' => $jenis->nomor_format
            ];
        } catch (\Exception $e) {
            Log::error('Error generating nomor dokumen: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Format nomor dokumen sesuai template
     * 
     * @param string $format
     * @param int $nomor
     * @param string $kodeJenis
     * @param string $kodeBidang
     * @param \Carbon\Carbon $tanggal
     * @return string
     */
    protected function formatNomor($format, $nomor, $kodeJenis, $kodeBidang, $tanggal)
    {
        // Pad nomor dengan leading zeros (contoh: 1 -> 001, default 3 digit)
        $nomorPadded = str_pad($nomor, 3, '0', STR_PAD_LEFT);

        // Support untuk format 4 digit jika ada {nomor4}
        $nomorPadded4 = str_pad($nomor, 4, '0', STR_PAD_LEFT);

        // Support untuk bulan romawi
        $bulanRomawi = $this->getBulanRomawi($tanggal->month);

        // Replace placeholders dengan nilai aktual
        $replacements = [
            '{nomor}' => $nomorPadded,
            '{nomor3}' => $nomorPadded,
            '{nomor4}' => $nomorPadded4,
            '{kode}' => strtoupper($kodeJenis),
            '{bidang}' => strtoupper($kodeBidang),
            '{tahun}' => $tanggal->year,
            '{bulan}' => str_pad($tanggal->month, 2, '0', STR_PAD_LEFT),
            '{bulan_romawi}' => $bulanRomawi,
            '{tanggal}' => $tanggal->format('d'),
            '{yy}' => $tanggal->format('y'), // 2 digit year
        ];

        $result = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $format
        );

        Log::info('Format nomor', [
            'format' => $format,
            'result' => $result,
            'replacements' => $replacements
        ]);

        return $result;
    }

    /**
     * Get bulan dalam format romawi
     * 
     * @param int $bulan
     * @return string
     */
    protected function getBulanRomawi($bulan)
    {
        $romawiMap = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];

        return $romawiMap[$bulan] ?? 'I';
    }

    /**
     * Get counter info untuk preview
     * 
     * @param int $jenisId
     * @param int $bidangId
     * @param int|null $tahun
     * @return array
     */
    public function getCounterInfo($jenisId, $bidangId, $tahun = null)
    {
        $tahun = $tahun ?? now()->year;

        $counter = NomorCounter::where([
            'jenis_id' => $jenisId,
            'bidang_id' => $bidangId,
            'tahun' => $tahun
        ])->first();

        return [
            'current_counter' => $counter ? $counter->counter : 0,
            'next_number' => $counter ? $counter->counter + 1 : 1,
            'tahun' => $tahun
        ];
    }

    /**
     * Preview nomor dokumen tanpa menyimpan
     * 
     * @param int $jenisId
     * @param int $bidangId
     * @param \Carbon\Carbon|null $tanggal
     * @return string|null
     */
    public function previewNomor($jenisId, $bidangId, $tanggal = null)
    {
        try {
            $tanggal = $tanggal ?? now();
            $tahun = $tanggal->year;

            $jenis = JenisDokumen::findOrFail($jenisId);

            if (!$jenis->perlu_nomor) {
                return null;
            }

            if (empty($jenis->nomor_format)) {
                throw new \Exception('Format nomor belum diatur');
            }

            $bidang = MasterBidang::findOrFail($bidangId);

            // Get counter tanpa increment
            $counter = NomorCounter::where([
                'jenis_id' => $jenisId,
                'bidang_id' => $bidangId,
                'tahun' => $tahun
            ])->first();

            $nextNumber = $counter ? $counter->counter + 1 : 1;

            return $this->formatNomor(
                $jenis->nomor_format,
                $nextNumber,
                $jenis->kode,
                $bidang->kode,
                $tanggal
            );
        } catch (\Exception $e) {
            Log::error('Error preview nomor: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reset counter (admin only)
     * 
     * @param int $jenisId
     * @param int $bidangId
     * @param int $tahun
     * @return bool
     */
    public function resetCounter($jenisId, $bidangId, $tahun)
    {
        return NomorCounter::where([
            'jenis_id' => $jenisId,
            'bidang_id' => $bidangId,
            'tahun' => $tahun
        ])->update(['counter' => 0]);
    }

    /**
     * Get all counters untuk report
     * 
     * @param int|null $tahun
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCounters($tahun = null)
    {
        $tahun = $tahun ?? now()->year;

        return NomorCounter::with(['jenis', 'bidang'])
            ->where('tahun', $tahun)
            ->orderBy('counter', 'desc')
            ->get();
    }
}
