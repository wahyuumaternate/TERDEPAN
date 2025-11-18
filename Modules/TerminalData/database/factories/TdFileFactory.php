<?php

namespace Modules\TerminalData\Database\Factories;

use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;
use Illuminate\Database\Eloquent\Factories\Factory;

class TdFileFactory extends Factory
{
    protected $model = TdFile::class;

    public function definition()
    {
        $name = $this->faker->words(3, true);
        $extension = $this->faker->randomElement(['pdf', 'docx', 'xlsx', 'jpg', 'png']);

        return [
            'folder_id' => TdFolder::factory(),
            'name' => $name,
            'original_name' => $name . '.' . $extension,
            'description' => $this->faker->sentence(),
            'storage_path' => 'terminal-data/' . $this->faker->uuid() . '.' . $extension,
            'extension' => $extension,
            'mime_type' => $this->getMimeType($extension),
            'size' => $this->faker->numberBetween(1024, 5242880), // 1KB to 5MB
            'hash' => hash('sha256', $this->faker->uuid()),
            'version' => 1,
            'is_latest_version' => true,
            'is_public' => $this->faker->boolean(30),
            'bidang_id' => null,
            'sub_bidang_id' => null,
            'created_by' => 1,
            'updated_by' => null,
        ];
    }

    public function inFolder($folderId)
    {
        return $this->state(function (array $attributes) use ($folderId) {
            return [
                'folder_id' => $folderId,
            ];
        });
    }

    public function forBidang($bidangId)
    {
        return $this->state(function (array $attributes) use ($bidangId) {
            return [
                'bidang_id' => $bidangId,
            ];
        });
    }

    protected function getMimeType($extension)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
