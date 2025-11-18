<?php

namespace Modules\TerminalData\Database\Factories;

use Modules\TerminalData\Models\TdFolder;
use Illuminate\Database\Eloquent\Factories\Factory;

class TdFolderFactory extends Factory
{
    protected $model = TdFolder::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'path' => '/' . $this->faker->slug(),
            'level' => $this->faker->numberBetween(0, 3),
            'parent_id' => null,
            'bidang_id' => null,
            'is_public' => $this->faker->boolean(30),
            'is_system' => false,
            'created_by' => 1,
            'updated_by' => null,
        ];
    }

    public function forBidang($bidangId)
    {
        return $this->state(function (array $attributes) use ($bidangId) {
            return [
                'bidang_id' => $bidangId,
            ];
        });
    }

    public function public()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_public' => true,
            ];
        });
    }

    public function withParent($parentId)
    {
        return $this->state(function (array $attributes) use ($parentId) {
            return [
                'parent_id' => $parentId,
                'level' => 1,
            ];
        });
    }
}
