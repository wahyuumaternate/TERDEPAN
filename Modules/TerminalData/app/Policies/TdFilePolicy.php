<?php

namespace Modules\TerminalData\Policies;

use App\Models\MasterPegawai;
use Modules\TerminalData\Models\TdFile;
use Illuminate\Auth\Access\HandlesAuthorization;

class TdFilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any files
     */
    public function viewAny(MasterPegawai $user): bool
    {
        // Semua pegawai bisa view files sesuai level mereka
        return $user->jabatan !== null;
    }

    /**
     * Determine if user can view specific file
     */
    public function view(MasterPegawai $user, TdFile $file): bool
    {
        // Semua user yang terautentikasi bisa melihat file
        return true;
    }
    /**
     * Determine if user can upload files
     * Upload ke folder tertentu dibatasi sesuai scope user
     */
    public function upload(MasterPegawai $user, $folder = null): bool
    {
        // Basic check - user harus punya jabatan
        if (!$user->jabatan) {
            return false;
        }

        // Jika tidak ada folder context, return true (general check)
        if (!$folder) {
            return true;
        }

        $kodeJabatan = $user->jabatan->kode;
        $folderName = strtolower($folder->name ?? '');

        // Folder Eviden Kinerja - hanya pemilik file yang bisa upload
        if (str_contains($folderName, 'eviden') && str_contains($folderName, 'kinerja')) {
            // Hanya pemilik folder yang bisa upload
            return $folder->created_by === $user->id;
        }

        // ADMIN, KABAN, SEKBAN - bisa upload dimana saja
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID - hanya bisa upload di folder bidangnya
        if ($kodeJabatan === 'KABID') {
            return $folder->bidang_id === $user->bidang_id;
        }

        // KASUBAG - hanya bisa upload di folder sub bidangnya
        // Jika KASUBAG punya sub_bidang_id, harus match dengan folder
        // Jika tidak punya sub_bidang_id, fallback ke bidang_id check
        if ($kodeJabatan === 'KASUBAG') {
            if ($user->sub_bidang_id && $folder->sub_bidang_id) {
                return $folder->sub_bidang_id === $user->sub_bidang_id;
            }
            // Fallback: bisa upload di folder bidangnya
            return $folder->bidang_id === $user->bidang_id;
        }

        // PELAKSANA, JAFUNG, GATEK - hanya bisa upload di folder bidang/sub bidang mereka
        if (in_array($kodeJabatan, ['PELAKSANA', 'JAFUNG', 'GATEK'])) {
            // Bisa upload di folder bidang atau sub bidang mereka
            if ($user->sub_bidang_id && $folder->sub_bidang_id === $user->sub_bidang_id) {
                return true;
            }
            if ($user->bidang_id && $folder->bidang_id === $user->bidang_id) {
                return true;
            }
            // TIDAK bisa upload ke folder yang bukan bidang/sub_bidang mereka
            // meskipun mereka adalah created_by folder tersebut
            return false;
        }

        return false;
    }

    /**
     * Determine if user can download file
     */
    public function download(MasterPegawai $user, TdFile $file): bool
    {
        // Semua user yang terautentikasi bisa download file
        return true;
    }
    /**
     * Determine if user can update file
     */
    public function update(MasterPegawai $user, TdFile $file): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID - Edit file bidangnya
        if (in_array($kodeJabatan, ['KABID'])) {
            return $file->bidang_id === $user->bidang_id;
        }

        // KASUBAG, PELAKSANA, JAFUNG, GATEK - Edit file sendiri
        if (in_array($kodeJabatan, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $file->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine if user can delete file
     */
    public function delete(MasterPegawai $user, TdFile $file): bool
    {
        // Tidak bisa hapus file di folder eviden kinerja
        if ($file->folder) {
            $folderName = strtolower($file->folder->name ?? '');
            if (str_contains($folderName, 'eviden') && str_contains($folderName, 'kinerja')) {
                return false; // Tidak bisa hapus file eviden
            }
        }

        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID - Delete file bidangnya
        if (in_array($kodeJabatan, ['KABID'])) {
            return $file->bidang_id === $user->bidang_id;
        }

        // KASUBAG, PELAKSANA, JAFUNG, GATEK - Delete file sendiri
        if (in_array($kodeJabatan, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $file->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine if user can restore file from trash
     * Restore permission sama dengan delete permission
     */
    public function restore(MasterPegawai $user, TdFile $file): bool
    {
        return $this->delete($user, $file);
    }

    /**
     * Determine if user can permanently delete file
     * Force delete permission sama dengan delete permission
     */
    public function forceDelete(MasterPegawai $user, TdFile $file): bool
    {
        return $this->delete($user, $file);
    }
}
