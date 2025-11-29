<?php

namespace Modules\TerminalData\Helpers;

class FormatHelper
{
    /**
     * Format bytes into human readable file size
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function formatFileSize($bytes, $precision = 2)
    {
        if ($bytes == 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $base = 1024;
        $i = floor(log($bytes, $base));
        
        return round($bytes / pow($base, $i), $precision) . ' ' . $units[$i];
    }
}