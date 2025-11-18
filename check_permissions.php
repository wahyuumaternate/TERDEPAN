<?php

/**
 * Terminal Data Permission Matrix Checker
 * 
 * File ini menampilkan matrix permission untuk semua level jabatan
 * pada module Terminal Data (Folder & File)
 * 
 * Usage: php check_permissions.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MasterJabatan;
use App\Models\MasterPegawai;
use Modules\TerminalData\Models\TdFolder;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Policies\TdFolderPolicy;
use Modules\TerminalData\Policies\TdFilePolicy;

// Colors for terminal output
class Color
{
    public static $GREEN = "\033[0;32m";
    public static $RED = "\033[0;31m";
    public static $YELLOW = "\033[1;33m";
    public static $BLUE = "\033[0;34m";
    public static $CYAN = "\033[0;36m";
    public static $MAGENTA = "\033[0;35m";
    public static $RESET = "\033[0m";
    public static $BOLD = "\033[1m";
}

function printHeader($title)
{
    $width = 120;
    echo "\n";
    echo Color::$BOLD . Color::$CYAN;
    echo str_repeat("=", $width) . "\n";
    echo str_pad(" " . $title, $width, " ", STR_PAD_BOTH) . "\n";
    echo str_repeat("=", $width) . "\n";
    echo Color::$RESET;
}

function printSubHeader($title)
{
    echo "\n" . Color::$BOLD . Color::$MAGENTA . $title . Color::$RESET . "\n";
    echo str_repeat("-", 120) . "\n";
}

function printPermission($allowed, $scope = null)
{
    if ($allowed === true) {
        echo Color::$GREEN . "✓ YES (All)" . Color::$RESET;
    } elseif ($allowed === false) {
        echo Color::$RED . "✗ NO" . Color::$RESET;
    } else {
        echo Color::$YELLOW . "⚠ " . strtoupper($allowed) . Color::$RESET;
    }
}

function getScopeDescription($kodeJabatan, $action, $context = null)
{
    $scopes = [
        'ADMIN' => [
            'view' => true,
            'download' => true,
            'create' => true,
            'upload' => true,
            'update' => true,
            'delete' => true,
        ],
        'KABAN' => [
            'view' => true,
            'download' => true,
            'create' => true,
            'upload' => true,
            'update' => true,
            'delete' => true,
        ],
        'SEKBAN' => [
            'view' => true,
            'download' => true,
            'create' => true,
            'upload' => true,
            'update' => true,
            'delete' => true,
        ],
        'KABID' => [
            'view' => true,
            'download' => true,
            'create' => true,
            'upload' => 'bidang',
            'update' => 'bidang',
            'delete' => 'bidang',
        ],
        'KASUBAG' => [
            'view' => true,
            'download' => true,
            'create' => true,
            'upload' => 'sub_bidang',
            'update' => 'own',
            'delete' => 'own',
        ],
        'JAFUNG' => [
            'view' => true,
            'download' => true,
            'create' => true,
            'upload' => 'bidang',
            'update' => 'own',
            'delete' => 'own',
        ],
        'PELAKSANA' => [
            'view' => true,
            'download' => true,
            'create' => true,
            'upload' => 'bidang',
            'update' => 'own',
            'delete' => 'own',
        ],
        'GATEK' => [
            'view' => true,
            'download' => true,
            'create' => true,
            'upload' => 'bidang',
            'update' => 'own',
            'delete' => 'own',
        ],
    ];

    return $scopes[$kodeJabatan][$action] ?? false;
} // Main execution
printHeader("TERMINAL DATA - PERMISSION MATRIX");

echo Color::$BOLD . "\nGenerated at: " . Color::$RESET . date('Y-m-d H:i:s') . "\n";
echo Color::$BOLD . "Total Folders: " . Color::$RESET . TdFolder::count() . "\n";
echo Color::$BOLD . "Total Files: " . Color::$RESET . TdFile::count() . "\n";

// Get all jabatan
$jabatanList = MasterJabatan::orderBy('level')->get();

// Create policies
$folderPolicy = new TdFolderPolicy();
$filePolicy = new TdFilePolicy();

// Get sample data for testing
$sampleFolder = TdFolder::first();
$sampleFile = TdFile::first();

// ==================== FOLDER PERMISSIONS ====================
printSubHeader("FOLDER PERMISSIONS");

// Table header
printf(
    "%-25s | %-8s | %-15s | %-15s | %-15s | %-15s | %-15s\n",
    "JABATAN",
    "LEVEL",
    "VIEW ANY",
    "VIEW",
    "CREATE",
    "UPDATE",
    "DELETE"
);
echo str_repeat("-", 120) . "\n";

foreach ($jabatanList as $jabatan) {
    // Create mock user
    $mockUser = new MasterPegawai([
        'id' => 999,
        'jabatan_id' => $jabatan->id,
        'bidang_id' => 1,
        'sub_bidang_id' => 1,
    ]);
    $mockUser->setRelation('jabatan', $jabatan);

    // Check permissions
    $viewAny = $folderPolicy->viewAny($mockUser);
    $view = getScopeDescription($jabatan->kode, 'view');
    $create = $folderPolicy->create($mockUser);
    $update = getScopeDescription($jabatan->kode, 'update');
    $delete = getScopeDescription($jabatan->kode, 'delete');

    // Print row
    printf(
        "%-25s | %-8s | ",
        $jabatan->nama . " ({$jabatan->kode})",
        $jabatan->level
    );

    printPermission($viewAny);
    echo str_pad("", 7);
    echo "| ";

    printPermission($view);
    echo str_pad("", 7);
    echo "| ";

    printPermission($create);
    echo str_pad("", 7);
    echo "| ";

    printPermission($update);
    echo str_pad("", 7);
    echo "| ";

    printPermission($delete);
    echo "\n";
}

// ==================== FILE PERMISSIONS ====================
printSubHeader("FILE PERMISSIONS");

// Table header
printf(
    "%-25s | %-8s | %-12s | %-12s | %-12s | %-12s | %-12s | %-12s\n",
    "JABATAN",
    "LEVEL",
    "VIEW ANY",
    "VIEW",
    "UPLOAD",
    "DOWNLOAD",
    "UPDATE",
    "DELETE"
);
echo str_repeat("-", 120) . "\n";

foreach ($jabatanList as $jabatan) {
    // Create mock user
    $mockUser = new MasterPegawai([
        'id' => 999,
        'jabatan_id' => $jabatan->id,
        'bidang_id' => 1,
        'sub_bidang_id' => 1,
    ]);
    $mockUser->setRelation('jabatan', $jabatan);

    // Check permissions
    $viewAny = $filePolicy->viewAny($mockUser);
    $view = getScopeDescription($jabatan->kode, 'view');
    $upload = getScopeDescription($jabatan->kode, 'upload');
    $download = getScopeDescription($jabatan->kode, 'download');
    $update = getScopeDescription($jabatan->kode, 'update');
    $delete = getScopeDescription($jabatan->kode, 'delete');

    // Print row
    printf(
        "%-25s | %-8s | ",
        $jabatan->nama . " ({$jabatan->kode})",
        $jabatan->level
    );

    printPermission($viewAny);
    echo str_pad("", 4);
    echo "| ";

    printPermission($view);
    echo str_pad("", 4);
    echo "| ";

    printPermission($upload);
    echo str_pad("", 4);
    echo "| ";

    printPermission($download);
    echo str_pad("", 4);
    echo "| ";

    printPermission($update);
    echo str_pad("", 4);
    echo "| ";

    printPermission($delete);
    echo "\n";
}

// ==================== LEGEND ====================
printSubHeader("LEGEND");

echo Color::$GREEN . "✓ YES (All)" . Color::$RESET . "     = Memiliki akses penuh untuk semua data\n";
echo Color::$YELLOW . "⚠ BIDANG" . Color::$RESET . "       = Akses terbatas pada folder/file dalam bidang yang sama\n";
echo Color::$YELLOW . "⚠ SUB_BIDANG" . Color::$RESET . "   = Akses terbatas pada folder/file dalam sub bidang yang sama\n";
echo Color::$YELLOW . "⚠ OWN" . Color::$RESET . "          = Hanya dapat mengakses folder/file yang dibuat sendiri\n";
echo Color::$RED . "✗ NO" . Color::$RESET . "            = Tidak memiliki akses\n";

// ==================== NOTES ====================
printSubHeader("CATATAN PENTING");

echo "1. " . Color::$BOLD . "VIEW & DOWNLOAD:" . Color::$RESET . " Semua user yang terautentikasi dapat melihat dan download SEMUA folder/file\n";
echo "   → Mendukung transparansi dan kolaborasi antar bagian\n\n";

echo "2. " . Color::$BOLD . "CREATE:" . Color::$RESET . " Semua user dapat membuat folder dan upload file baru\n\n";

echo "3. " . Color::$BOLD . "UPLOAD FILE:" . Color::$RESET . " Upload file dibatasi berdasarkan scope:\n";
echo "   → " . Color::$GREEN . "ADMIN, KABAN, SEKBAN:" . Color::$RESET . " Bisa upload di semua folder\n";
echo "   → " . Color::$YELLOW . "KABID:" . Color::$RESET . " Hanya di folder bidangnya\n";
echo "   → " . Color::$YELLOW . "KASUBAG:" . Color::$RESET . " Hanya di folder sub bidangnya\n";
echo "   → " . Color::$YELLOW . "PELAKSANA, JAFUNG, GATEK:" . Color::$RESET . " Hanya di folder bidang/sub bidang mereka\n";
echo "   → " . Color::$RED . "FOLDER EVIDEN KINERJA:" . Color::$RESET . " Hanya pemilik folder yang bisa upload\n\n";

echo "4. " . Color::$BOLD . "UPDATE & DELETE:" . Color::$RESET . " Dibatasi berdasarkan level jabatan:\n";
echo "   → " . Color::$GREEN . "ADMIN, KABAN, SEKBAN:" . Color::$RESET . " Full access (semua folder/file)\n";
echo "   → " . Color::$YELLOW . "KABID:" . Color::$RESET . " Hanya dalam bidangnya\n";
echo "   → " . Color::$YELLOW . "KASUBAG, PELAKSANA, JAFUNG, GATEK:" . Color::$RESET . " Hanya milik sendiri\n\n";

echo "5. " . Color::$BOLD . "HAPUS FOLDER:" . Color::$RESET . " Folder " . Color::$RED . "TIDAK BISA DIHAPUS" . Color::$RESET . " jika:\n";
echo "   → Masih ada file (dokumen) di dalamnya\n";
echo "   → Masih ada subfolder di dalamnya\n\n";

echo "6. " . Color::$BOLD . "HAPUS FILE EVIDEN:" . Color::$RESET . " File di folder " . Color::$RED . "Eviden Kinerja TIDAK BISA DIHAPUS\n\n";

echo "7. " . Color::$BOLD . "FORCE DELETE:" . Color::$RESET . " Hanya ADMIN, KABAN, dan SEKBAN yang dapat force delete (hapus permanen)\n\n";

// ==================== DETAILED TEST ====================
printSubHeader("DETAILED PERMISSION TEST WITH REAL DATA");

if ($sampleFolder) {
    echo "\n" . Color::$BOLD . "Testing with Folder: " . Color::$RESET . $sampleFolder->name . "\n";
    echo Color::$BOLD . "  - Created by: " . Color::$RESET . ($sampleFolder->creator ? $sampleFolder->creator->nama : 'N/A') . "\n";
    echo Color::$BOLD . "  - Bidang ID: " . Color::$RESET . $sampleFolder->bidang_id . "\n";
    echo Color::$BOLD . "  - Sub Bidang ID: " . Color::$RESET . ($sampleFolder->sub_bidang_id ?? 'N/A') . "\n\n";

    foreach ($jabatanList->take(3) as $jabatan) {
        $mockUser = new MasterPegawai([
            'id' => 999,
            'jabatan_id' => $jabatan->id,
            'bidang_id' => $sampleFolder->bidang_id,
            'sub_bidang_id' => $sampleFolder->sub_bidang_id,
        ]);
        $mockUser->setRelation('jabatan', $jabatan);

        echo "  " . Color::$CYAN . $jabatan->nama . ":" . Color::$RESET . "\n";
        echo "    - Can view: ";
        printPermission($folderPolicy->view($mockUser, $sampleFolder));
        echo "\n";
        echo "    - Can update: ";
        printPermission($folderPolicy->update($mockUser, $sampleFolder));
        echo "\n";
        echo "    - Can delete: ";
        printPermission($folderPolicy->delete($mockUser, $sampleFolder));
        echo "\n\n";
    }
}

if ($sampleFile) {
    echo "\n" . Color::$BOLD . "Testing with File: " . Color::$RESET . $sampleFile->name . "\n";
    echo Color::$BOLD . "  - Created by: " . Color::$RESET . ($sampleFile->creator ? $sampleFile->creator->nama : 'N/A') . "\n";
    echo Color::$BOLD . "  - Bidang ID: " . Color::$RESET . $sampleFile->bidang_id . "\n";
    echo Color::$BOLD . "  - Sub Bidang ID: " . Color::$RESET . ($sampleFile->sub_bidang_id ?? 'N/A') . "\n\n";

    foreach ($jabatanList->take(3) as $jabatan) {
        $mockUser = new MasterPegawai([
            'id' => 999,
            'jabatan_id' => $jabatan->id,
            'bidang_id' => $sampleFile->bidang_id,
            'sub_bidang_id' => $sampleFile->sub_bidang_id,
        ]);
        $mockUser->setRelation('jabatan', $jabatan);

        echo "  " . Color::$CYAN . $jabatan->nama . ":" . Color::$RESET . "\n";
        echo "    - Can view: ";
        printPermission($filePolicy->view($mockUser, $sampleFile));
        echo "\n";
        echo "    - Can download: ";
        printPermission($filePolicy->download($mockUser, $sampleFile));
        echo "\n";
        echo "    - Can update: ";
        printPermission($filePolicy->update($mockUser, $sampleFile));
        echo "\n";
        echo "    - Can delete: ";
        printPermission($filePolicy->delete($mockUser, $sampleFile));
        echo "\n\n";
    }
}

printHeader("END OF PERMISSION MATRIX");
echo "\n";
