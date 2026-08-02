<?php

namespace Modules\Penugasan\Services;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Menentukan kandidat atasan yang boleh dipilih pegawai saat mengajukan tugas mandiri,
 * mengikuti tabel B "Membuat Tugas Mandiri" pada docs/analysis/aturan-penugasan-&-penilaian.md.
 */
class AtasanMandiriEligibility
{
    private const KODE_SEKRETARIAT = 'SEKRETARIAT';

    /**
     * @return Collection<int, User>
     */
    public function kandidatUntuk(User $user): Collection
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;
        $bidangId = $user->profile?->bidang_id;
        $isSekretariat = $user->profile?->bidang?->kode === self::KODE_SEKRETARIAT;

        return match (true) {
            in_array($kodeJabatan, ['SEKBAN', 'KABID']) => $this->cariUser(['KABAN']),

            $kodeJabatan === 'KASUBAG' => $this->cariUser(['SEKBAN', 'KABAN']),

            $kodeJabatan === 'JAFUNG' && $isSekretariat => $this->cariUser(['SEKBAN', 'KABAN']),

            $kodeJabatan === 'JAFUNG' => $this->cariUser(['KABID'], $bidangId)
                ->merge($this->cariUser(['KABAN'])),

            in_array($kodeJabatan, ['PELAKSANA', 'GATEK']) && $isSekretariat => $this->cariUser(['KASUBAG', 'SEKBAN', 'KABAN']),

            in_array($kodeJabatan, ['PELAKSANA', 'GATEK']) => $this->cariUser(['JAFUNG', 'KABID'], $bidangId)
                ->merge($this->cariUser(['KABAN'])),

            default => collect(),
        };
    }

    public function bolehDipilih(User $pembuat, User $atasanDipilih): bool
    {
        return $this->kandidatUntuk($pembuat)->contains('id', $atasanDipilih->id);
    }

    /**
     * @param  array<int, string>  $kodeJabatans
     * @return Collection<int, User>
     */
    private function cariUser(array $kodeJabatans, ?int $bidangId = null): Collection
    {
        return User::whereHas('profile', function ($query) use ($kodeJabatans, $bidangId) {
            $query->whereHas('jabatan', fn ($jabatanQuery) => $jabatanQuery->whereIn('kode', $kodeJabatans));

            if ($bidangId !== null) {
                $query->where('bidang_id', $bidangId);
            }
        })->get();
    }
}
