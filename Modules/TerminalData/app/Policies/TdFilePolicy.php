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
     */
    public function upload(MasterPegawai $user): bool
    {
        // Semua pegawai bisa upload files
        return $user->jabatan !== null;
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
}
