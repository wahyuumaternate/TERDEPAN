<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any pegawai
     * ADMIN, KABAN, SEKBAN, atau Kasubag Umum dan Kepegawaian yang bisa melihat master data
     */
    public function viewAny(User $user): bool
    {
        return $this->bolehKelolaMasterDataPegawai($user);
    }

    /**
     * Determine if user can view specific pegawai
     * ADMIN, KABAN, SEKBAN, atau Kasubag Umum dan Kepegawaian yang bisa melihat master data
     */
    public function view(User $user, User $pegawai): bool
    {
        return $this->bolehKelolaMasterDataPegawai($user);
    }

    /**
     * Determine if user can create pegawai
     * ADMIN, KABAN, SEKBAN, atau Kasubag Umum dan Kepegawaian yang bisa mengelola master data
     */
    public function create(User $user): bool
    {
        return $this->bolehKelolaMasterDataPegawai($user);
    }

    /**
     * Determine if user can update pegawai
     * ADMIN, KABAN, SEKBAN, atau Kasubag Umum dan Kepegawaian yang bisa mengelola master data
     */
    public function update(User $user, User $pegawai): bool
    {
        return $this->bolehKelolaMasterDataPegawai($user);
    }

    /**
     * Determine if user can delete pegawai
     * ADMIN, KABAN, SEKBAN, atau Kasubag Umum dan Kepegawaian yang bisa mengelola master data
     */
    public function delete(User $user, User $pegawai): bool
    {
        return $this->bolehKelolaMasterDataPegawai($user);
    }

    /**
     * Kasubag Umum dan Kepegawaian ditentukan lewat kombinasi kode jabatan KASUBAG + nama
     * sub bidang "Sub Bagian Umum dan Kepegawaian" — MasterSubBidang tidak punya kolom kode,
     * jadi identifikasi sub bagian pakai nama (lihat database/seeders/MasterBidangSeeder.php).
     */
    private function bolehKelolaMasterDataPegawai(User $user): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        return $kodeJabatan === 'KASUBAG'
            && $user->profile?->subBidang?->nama === 'Sub Bagian Umum dan Kepegawaian';
    }
}
