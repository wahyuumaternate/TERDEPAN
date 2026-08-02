<?php

namespace Modules\Penugasan\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Models\Progress;

/**
 * @extends Factory<Progress>
 */
class ProgressFactory extends Factory
{
    protected $model = Progress::class;

    public function definition(): array
    {
        return [
            'penugasan_id' => Penugasan::factory(),
            'pegawai_id' => User::factory(),
            'tanggal' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'progress_persen' => fake()->numberBetween(10, 90),
            'deskripsi_kegiatan' => fake()->sentence(8),
            'kendala' => fake()->optional(0.3)->sentence(),
        ];
    }
}
