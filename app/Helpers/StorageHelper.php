<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    public static function getFolderSize(string $path = ''): int
    {
        $disk = Storage::disk('public'); // gunakan disk 'public'
        $size = 0;

        foreach ($disk->allFiles($path) as $file) {
            $size += $disk->size($file);
        }

        return $size;
    }

    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        if ($bytes <= 0) return '0 B';

        $pow = floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
