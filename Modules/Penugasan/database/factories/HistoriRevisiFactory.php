<?php

namespace Modules\Penugasan\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Penugasan\Models\HistoriRevisi;
use Modules\Penugasan\Models\Penugasan;

/**
 * @extends Factory<HistoriRevisi>
 */
class HistoriRevisiFactory extends Factory
{
    protected $model = HistoriRevisi::class;

    public function definition(): array
    {
        return [
            'penugasan_id' => Penugasan::factory(),
            'revisi_ke' => 1,
            'tanggal_revisi' => now(),
            'catatan_revisi' => fake()->sentence(10),
            'deadline_revisi' => now()->addDays(3),
            'direvisi_oleh' => User::factory(),
            'pegawai_id' => User::factory(),
        ];
    }
}
