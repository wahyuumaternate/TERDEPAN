<?php

namespace Modules\PerjanjianKinerja\Policies;

use App\Models\MasterPegawai;
use Modules\PerjanjianKinerja\Models\PkPerjanjianKinerja;
use Illuminate\Auth\Access\HandlesAuthorization;

class PkPerjanjianKinerjaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any PK
     */
    public function viewAny(MasterPegawai $user)
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID']);
    }

    /**
     * Determine if user can view specific PK
     */
    public function view(MasterPegawai $user, PkPerjanjianKinerja $pk)
    {
        $kodeJabatan = $user->jabatan?->kode;

        // Admin, Kaban, Sekban can view all
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // Kabid can view in their bidang
        if ($kodeJabatan === 'KABID' && $pk->pegawai->bidang_id === $user->bidang_id) {
            return true;
        }

        // User can view their own PK
        if ($pk->pegawai_id === $user->id) {
            return true;
        }

        // Atasan can view their bawahan's PK
        if ($pk->atasan_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can create PK
     */
    public function create(MasterPegawai $user)
    {
        // Check if there's active periode
        $periodeAktif = \Modules\PerjanjianKinerja\Models\PkPeriode::getPeriodeAktif();
        if (!$periodeAktif) {
            return false;
        }

        $kodeJabatan = $user->jabatan?->kode;

        // Admin, Kaban, Sekban, Kabid can create for others
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID'])) {
            return true;
        }

        // Regular users can create for themselves if they don't have PK yet
        // and they're not tenaga teknis
        if ($user->status_kepegawaian !== 'Kontrak' && $user->atasan_langsung_id) {
            $existingPk = PkPerjanjianKinerja::where('pegawai_id', $user->id)
                ->where('tahun', date('Y'))
                ->where('periode_id', $periodeAktif->id)
                ->exists();

            return !$existingPk;
        }

        return false;
    }

    /**
     * Determine if user can update PK
     */
    public function update(MasterPegawai $user, PkPerjanjianKinerja $pk)
    {
        // Cannot update if locked
        if ($pk->is_locked) {
            return false;
        }

        // Cannot update if already validated (disetujui)
        if ($pk->status_validasi === 'Disetujui') {
            return false;
        }

        $kodeJabatan = $user->jabatan?->kode;

        // Admin, Kaban, Sekban can update any
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // Owner can update their own PK if status is Draft or Revisi
        if ($pk->pegawai_id === $user->id && in_array($pk->status_validasi, ['Menunggu', 'Revisi'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can delete PK
     */
    public function delete(MasterPegawai $user, PkPerjanjianKinerja $pk)
    {
        // Cannot delete if locked or already validated
        if ($pk->is_locked || $pk->status_validasi !== 'Menunggu') {
            return false;
        }

        $kodeJabatan = $user->jabatan?->kode;

        // Only Admin, Kaban, Sekban can delete
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can validate PK
     */
    public function validate(MasterPegawai $user, PkPerjanjianKinerja $pk)
    {
        // Must be atasan langsung
        if ($pk->atasan_id !== $user->id) {
            return false;
        }

        // PK must be in Menunggu status
        if ($pk->status_validasi !== 'Menunggu') {
            return false;
        }

        // Must be authorized role (KABAN, SEKBAN, KABID only)
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['KABAN', 'SEKBAN', 'KABID']);
    }

    /**
     * Determine if user can generate PDF
     */
    public function generatePdf(MasterPegawai $user, PkPerjanjianKinerja $pk)
    {
        $kodeJabatan = $user->jabatan?->kode;

        // Admin, Kaban, Sekban can generate any
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // Owner can generate if validated
        if ($pk->pegawai_id === $user->id && $pk->status_validasi === 'Disetujui') {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can sign PK
     */
    public function sign(MasterPegawai $user, PkPerjanjianKinerja $pk)
    {
        // Must be owner
        if ($pk->pegawai_id !== $user->id) {
            return false;
        }

        // Must be validated
        if ($pk->status_validasi !== 'Disetujui') {
            return false;
        }

        // Must not be already signed
        if ($pk->tanggal_ttd) {
            return false;
        }

        return true;
    }

    /**
     * Determine if user can download PK
     */
    public function download(MasterPegawai $user, PkPerjanjianKinerja $pk)
    {
        return $this->view($user, $pk);
    }
}
