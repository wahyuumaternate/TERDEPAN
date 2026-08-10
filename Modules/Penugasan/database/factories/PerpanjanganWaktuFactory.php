<?php

namespace Modules\Penugasan\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Models\PerpanjanganWaktu;

/**
 * @extends Factory<PerpanjanganWaktu>
 */
class PerpanjanganWaktuFactory extends Factory
{
    protected $model = PerpanjanganWaktu::class;

    public function definition(): array
    {
        $deadlineLama = fake()->dateTimeBetween('-1 week', 'now');

        return [
            'penugasan_id' => Penugasan::factory(),
            'pegawai_id' => User::factory(),
            'deadline_lama' => $deadlineLama,
            'deadline_diminta' => (clone $deadlineLama)->modify('+7 days'),
            'alasan_pengajuan' => fake()->sentence(10),
            'status' => PerpanjanganWaktu::STATUS_MENUNGGU,
            'ke_berapa' => 1,
        ];
    }

    public function disetujui(?User $atasan = null): static
    {
        return $this->state(function (array $attributes) use ($atasan) {
            $deadlineDiminta = $attributes['deadline_diminta'] ?? now()->addDays(7);

            return [
                'status' => PerpanjanganWaktu::STATUS_DISETUJUI,
                'deadline_disetujui' => $deadlineDiminta,
                'catatan_atasan' => 'Disetujui, harap segera diselesaikan.',
                'disetujui_oleh_id' => $atasan?->id ?? User::factory(),
            ];
        });
    }

    public function ditolak(?User $atasan = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PerpanjanganWaktu::STATUS_DITOLAK,
            'catatan_atasan' => 'Alasan kurang kuat, harap selesaikan sesuai deadline semula.',
            'disetujui_oleh_id' => $atasan?->id ?? User::factory(),
        ]);
    }
}
