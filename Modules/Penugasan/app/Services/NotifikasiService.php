<?php

namespace Modules\Penugasan\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Models\PerpanjanganWaktu;

/**
 * Notifikasi dihitung langsung dari kondisi data Penugasan terkini — bukan
 * disimpan/ditandai-dibaca. Konsekuensinya: satu item otomatis hilang begitu
 * ditindaklanjuti (tugas diterima, dinilai, perpanjangan diputuskan, dst),
 * tanpa perlu tabel/skema baru. Dipakai bersama oleh dropdown header
 * (Api\PenugasanController tidak relevan di sini — lihat ApiController::notifikasi())
 * dan halaman notifikasi penuh, supaya kedua tempat selalu konsisten.
 */
class NotifikasiService
{
    /**
     * @return Collection<int, array{tipe: string, ikon: string, pesan: string, link: string}>
     */
    public function untuk(User $user): Collection
    {
        return collect([
            ...$this->sisiPegawai($user),
            ...$this->sisiAtasan($user),
        ])->values();
    }

    /**
     * @return array<int, array{tipe: string, ikon: string, pesan: string, link: string}>
     */
    private function sisiPegawai(User $user): array
    {
        $notifikasi = [];

        $tugasBaru = Penugasan::where('pegawai_id', $user->id)
            ->where('is_mandiri', false)
            ->where('status', Penugasan::STATUS_PENDING)
            ->count();
        if ($tugasBaru > 0) {
            $notifikasi[] = $this->item(
                'info', 'bi-inbox',
                "{$tugasBaru} tugas baru menunggu Anda terima",
                route('penugasan.tugas-saya', ['status' => Penugasan::STATUS_PENDING])
            );
        }

        $mandiriDitolak = Penugasan::where('pegawai_id', $user->id)
            ->where('is_mandiri', true)
            ->where('status', Penugasan::STATUS_DITOLAK)
            ->count();
        if ($mandiriDitolak > 0) {
            $notifikasi[] = $this->item(
                'danger', 'bi-x-circle',
                "{$mandiriDitolak} tugas mandiri Anda ditolak atasan",
                route('penugasan.tugas-saya', ['status' => Penugasan::STATUS_DITOLAK])
            );
        }

        $terlambat = Penugasan::where('pegawai_id', $user->id)
            ->where('status', Penugasan::STATUS_TERLAMBAT)
            ->count();
        if ($terlambat > 0) {
            $notifikasi[] = $this->item(
                'danger', 'bi-exclamation-triangle',
                "{$terlambat} tugas Anda sudah melewati deadline",
                route('penugasan.tugas-saya', ['status' => Penugasan::STATUS_TERLAMBAT])
            );
        }

        $perluRevisi = Penugasan::where('pegawai_id', $user->id)
            ->where('status', Penugasan::STATUS_REVISI)
            ->count();
        if ($perluRevisi > 0) {
            $notifikasi[] = $this->item(
                'warning', 'bi-arrow-repeat',
                "{$perluRevisi} tugas Anda diminta revisi",
                route('penugasan.tugas-saya', ['status' => Penugasan::STATUS_REVISI])
            );
        }

        $segeraJatuhTempo = Penugasan::where('pegawai_id', $user->id)
            ->whereIn('status', [Penugasan::STATUS_PROSES, Penugasan::STATUS_REVISI])
            ->whereNotNull('deadline_terbaru')
            ->whereBetween('deadline_terbaru', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
            ->count();
        if ($segeraJatuhTempo > 0) {
            $notifikasi[] = $this->item(
                'warning', 'bi-clock-history',
                "{$segeraJatuhTempo} tugas akan jatuh tempo dalam 3 hari ke depan",
                route('penugasan.tugas-saya')
            );
        }

        $baruDinilai = Penugasan::where('pegawai_id', $user->id)
            ->whereNotNull('realisasi_persen')
            ->where('validated_at', '>=', now()->subDays(7))
            ->count();
        if ($baruDinilai > 0) {
            $notifikasi[] = $this->item(
                'success', 'bi-star',
                "{$baruDinilai} tugas Anda baru saja dinilai atasan",
                route('penugasan.tugas-saya', ['status' => Penugasan::STATUS_SELESAI])
            );
        }

        return $notifikasi;
    }

    /**
     * @return array<int, array{tipe: string, ikon: string, pesan: string, link: string}>
     */
    private function sisiAtasan(User $user): array
    {
        $notifikasi = [];

        $mandiriMenunggu = Penugasan::where('pemberi_tugas_id', $user->id)
            ->where('is_mandiri', true)
            ->where('status', Penugasan::STATUS_PENDING)
            ->count();
        if ($mandiriMenunggu > 0) {
            $notifikasi[] = $this->item(
                'info', 'bi-person-check',
                "{$mandiriMenunggu} tugas mandiri menunggu persetujuan Anda",
                route('penugasan.tugas-saya', ['tab' => 'diberikan'])
            );
        }

        $perpanjanganMenunggu = PerpanjanganWaktu::where('status', PerpanjanganWaktu::STATUS_MENUNGGU)
            ->whereHas('penugasan', fn ($q) => $q->where('pemberi_tugas_id', $user->id))
            ->count();
        if ($perpanjanganMenunggu > 0) {
            $notifikasi[] = $this->item(
                'warning', 'bi-hourglass-split',
                "{$perpanjanganMenunggu} pengajuan perpanjangan waktu menunggu keputusan Anda",
                route('penugasan.tugas-saya', ['tab' => 'diberikan'])
            );
        }

        $menungguDinilai = Penugasan::where('pemberi_tugas_id', $user->id)
            ->where('status', Penugasan::STATUS_SELESAI)
            ->whereNull('realisasi_persen')
            ->count();
        if ($menungguDinilai > 0) {
            $notifikasi[] = $this->item(
                'info', 'bi-clipboard-check',
                "{$menungguDinilai} tugas yang Anda berikan selesai & menunggu dinilai",
                route('penugasan.tugas-saya', ['tab' => 'diberikan', 'status' => Penugasan::STATUS_SELESAI])
            );
        }

        return $notifikasi;
    }

    /**
     * @return array{tipe: string, ikon: string, pesan: string, link: string}
     */
    private function item(string $tipe, string $ikon, string $pesan, string $link): array
    {
        return compact('tipe', 'ikon', 'pesan', 'link');
    }
}
