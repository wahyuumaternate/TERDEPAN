<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Satu-satunya titik masuk untuk upload/hapus foto profil pegawai — sebelumnya
 * MasterPegawaiController dan ProfileController masing-masing punya cara sendiri
 * (public_path()+move() vs Storage::disk('public') dengan folder berbeda) yang
 * menulis ke lokasi fisik berbeda tapi ke kolom DB yang sama
 * (docs/plan/09-audit-storage.md). Selalu disk 'public' — foto harus bisa diakses
 * langsung lewat <img src> tanpa lewat route terautentikasi, beda dari dokumen
 * TerminalData yang privat by default.
 */
class ProfilePhotoService
{
    protected string $disk = 'public';

    protected string $pathPrefix = 'pegawai/foto';

    /**
     * @return array{disk: string, path: string}
     */
    public function store(UploadedFile $file, int|string $userId): array
    {
        // Str::random() ikut disertakan (bukan cuma userId+time()) supaya dua upload
        // untuk pegawai yang sama dalam detik yang sama (mis. klik ganda) tidak collide
        // dan diam-diam saling menimpa.
        $filename = $userId.'_'.time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs($this->pathPrefix, $filename, $this->disk);

        return ['disk' => $this->disk, 'path' => $path];
    }

    public function delete(?string $path, ?string $disk = null): void
    {
        if (! $path) {
            return;
        }

        $disk ??= $this->disk;

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
