<?php

namespace App\Services;

use App\Models\Panduan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Satu-satunya titik masuk untuk simpan/hapus/serve dokumen panduan — disk-agnostic
 * lewat Storage facade (docs/plan/09-audit-storage.md). Privat by default (bukan disk
 * 'public'), karena preview/download digerbangi auth lewat PanduanPolicy, bukan diakses
 * langsung lewat <img src> seperti foto profil.
 */
class PanduanService
{
    protected string $pathPrefix = 'panduan';

    /**
     * @return array{disk: string, path: string, nama_file: string, mime_type: string, size: int}
     */
    public function store(UploadedFile $file): array
    {
        $disk = config('filesystems.default');
        $filename = time().'_'.$file->hashName();
        $path = $file->storeAs($this->pathPrefix, $filename, $disk);

        return [
            'disk' => $disk,
            'path' => $path,
            'nama_file' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ];
    }

    public function deletePhysical(?string $path, ?string $disk = null): void
    {
        if (! $path) {
            return;
        }

        $disk ??= config('filesystems.default');

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    public function serveInline(Panduan $panduan): StreamedResponse
    {
        return Storage::disk($panduan->disk)->response(
            $panduan->path,
            $panduan->nama_file,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function download(Panduan $panduan): StreamedResponse
    {
        return Storage::disk($panduan->disk)->download($panduan->path, $panduan->nama_file);
    }
}
