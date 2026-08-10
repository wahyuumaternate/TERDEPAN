<?php

namespace Modules\Penugasan\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Penugasan\Models\Penugasan;

/**
 * @extends Factory<Penugasan>
 */
class PenugasanFactory extends Factory
{
    protected $model = Penugasan::class;

    public function definition(): array
    {
        $tanggalMulai = fake()->dateTimeBetween('-1 month', 'now');
        $tanggalSelesai = (clone $tanggalMulai)->modify('+'.fake()->numberBetween(7, 30).' days');

        return [
            'pegawai_id' => User::factory(),
            'pemberi_tugas_id' => User::factory(),
            'is_mandiri' => false,
            'jenis' => fake()->randomElement(Penugasan::JENISES),
            'prioritas' => fake()->randomElement(Penugasan::PRIORITASES),
            'nama_tugas' => ucfirst(fake()->sentence(4)),
            'deskripsi' => fake()->paragraph(),
            'alasan_penugasan' => fake()->sentence(),
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'deadline_terbaru' => $tanggalSelesai,
            'target_value' => fake()->randomElement([null, 1, 5, 10]),
            'satuan' => fake()->randomElement([null, 'dokumen', 'laporan', 'kegiatan']),
            'bobot_persen' => fake()->numberBetween(10, 30),
            'progress_persen' => 0,
            'status' => Penugasan::STATUS_PENDING,
        ];
    }

    /**
     * Tugas mandiri (self-initiated), menunggu persetujuan atasan terpilih.
     */
    public function mandiri(?User $atasan = null): static
    {
        return $this->state(function (array $attributes) use ($atasan) {
            return [
                'pegawai_id' => $attributes['pegawai_id'] ?? User::factory(),
                'pemberi_tugas_id' => $atasan?->id ?? User::factory(),
                'is_mandiri' => true,
                'status' => Penugasan::STATUS_PENDING,
                'status_approval' => Penugasan::APPROVAL_PENDING,
            ];
        });
    }

    /**
     * Tugas mandiri yang ditolak atasan (wajib alasan_reject).
     */
    public function mandiriDitolak(?string $alasan = null): static
    {
        return $this->state(fn (array $attributes) => [
            'is_mandiri' => true,
            'status' => Penugasan::STATUS_DITOLAK,
            'status_approval' => Penugasan::APPROVAL_DITOLAK,
            'alasan_reject' => $alasan ?? 'Deskripsi tugas kurang jelas, mohon dilengkapi.',
        ]);
    }

    public function proses(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Penugasan::STATUS_PROSES,
            'diterima_at' => now(),
            'progress_persen' => fake()->numberBetween(10, 70),
        ]);
    }

    public function revisi(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Penugasan::STATUS_REVISI,
            'diterima_at' => now(),
            'deadline_terbaru' => now()->addDays(3),
            'progress_persen' => fake()->numberBetween(50, 90),
        ]);
    }

    /**
     * Lewat deadline tanpa diselesaikan — konsisten dengan yang ditandai
     * command `penugasan:tandai-terlambat`.
     */
    public function terlambat(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Penugasan::STATUS_TERLAMBAT,
            'diterima_at' => now()->subDays(10),
            'deadline_terbaru' => now()->subDays(3),
            'progress_persen' => fake()->numberBetween(30, 80),
        ]);
    }

    /**
     * Selesai, menunggu dinilai atasan (realisasi/nilai masih kosong).
     */
    public function selesai(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Penugasan::STATUS_SELESAI,
            'diterima_at' => now()->subDays(5),
            'progress_persen' => 100,
            'tanggal_diselesaikan' => now(),
        ]);
    }

    /**
     * Selesai dan sudah dinilai atasan (final, tidak bisa direvisi lagi).
     */
    public function selesaiDinilai(?float $bobot = null, ?float $realisasi = null): static
    {
        return $this->state(function (array $attributes) use ($bobot, $realisasi) {
            $bobotPersen = $bobot ?? ($attributes['bobot_persen'] ?? 20);
            $realisasiPersen = $realisasi ?? 90;
            $nilaiAwal = round(($bobotPersen * $realisasiPersen) / 100, 2);

            return [
                'status' => Penugasan::STATUS_SELESAI,
                'diterima_at' => now()->subDays(10),
                'progress_persen' => 100,
                'tanggal_diselesaikan' => now()->subDays(2),
                'bobot_persen' => $bobotPersen,
                'realisasi_persen' => $realisasiPersen,
                'nilai_awal' => $nilaiAwal,
                'persentase_terlambat' => 0,
                'nilai_akhir' => $nilaiAwal,
                'hasil_validasi' => Penugasan::VALIDASI_DITERIMA,
                'catatan_validasi' => 'Hasil pekerjaan sudah sesuai target.',
                'validated_at' => now()->subDays(2),
            ];
        });
    }

    /**
     * Bagian dari penugasan grup (Kolektif/Per Orang) — dipakai bersama helper
     * seeder yang membuat beberapa baris sekaligus dengan grup_id yang sama.
     */
    public function dalamGrup(string $grupId, string $mode, bool $koordinator = false): static
    {
        return $this->state(fn (array $attributes) => [
            'grup_id' => $grupId,
            'mode_grup' => $mode,
            'is_koordinator' => $koordinator,
        ]);
    }
}
