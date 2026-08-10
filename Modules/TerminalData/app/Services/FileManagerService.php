<?php

namespace Modules\TerminalData\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Satu-satunya titik masuk untuk operasi file fisik (upload, hapus, salin, sajikan) di
 * TerminalData dan modul lain yang menulis ke td_files (mis. Penugasan) — supaya semua
 * jalur upload/delete konsisten dan tidak ada yang mengasumsikan disk lokal
 * (docs/plan/09-audit-storage.md). Disk selalu eksplisit lewat parameter/kolom DB, tidak
 * pernah di-hardcode di sini, supaya berpindah FILESYSTEM_DISK tidak perlu ubah logic.
 */
class FileManagerService
{
    protected string $defaultDisk;

    public function __construct(?string $defaultDisk = null)
    {
        $this->defaultDisk = $defaultDisk ?? config('filesystems.default');
    }

    /**
     * Simpan file yang diupload ke bawah $pathPrefix pada disk default aplikasi.
     * Hash dihitung dari file upload SEBELUM dipindah (bukan setelah, supaya tidak
     * bergantung pada file yang sudah tidak lagi ada di lokasi sementaranya).
     *
     * @return array{disk: string, path: string, original_name: string, mime_type: string, size: int, hash: string, extension: string}
     */
    public function store(UploadedFile $file, string $pathPrefix): array
    {
        $hash = hash_file('sha256', $file->getRealPath());
        $extension = $file->getClientOriginalExtension();
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'_'.time().'.'.$extension;

        $path = $file->storeAs($pathPrefix, $filename, $this->defaultDisk);

        return [
            'disk' => $this->defaultDisk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'hash' => $hash,
            'extension' => $extension,
        ];
    }

    /**
     * Hapus file fisik dengan aman — no-op kalau path kosong atau file sudah tidak ada.
     */
    public function deletePhysical(?string $path, ?string $disk = null): void
    {
        if (! $path) {
            return;
        }

        $disk ??= $this->defaultDisk;

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * Sajikan file inline (preview) — disk-agnostic lewat Storage::response(), bukan
     * Storage::path()+file_exists() (S3-breaking). MIME type diambil dari kolom DB yang
     * sudah tersimpan, bukan tebakan dari ekstensi file.
     */
    public function serveInline(string $disk, string $path, string $filename, string $mimeType): StreamedResponse
    {
        if (! Storage::disk($disk)->exists($path)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::disk($disk)->response($path, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }

    /**
     * Unduh file — disk-agnostic (Storage::download() sudah streaming, tidak perlu
     * Storage::path()).
     */
    public function download(string $disk, string $path, string $filename): StreamedResponse
    {
        if (! Storage::disk($disk)->exists($path)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::disk($disk)->download($path, $filename);
    }

    /**
     * Salin file fisik ke path baru yang benar-benar berbeda (dipakai TdFile::duplicate()
     * — sebelumnya path baru dihasilkan lewat str_replace(fileId) yang tidak pernah match
     * karena UUID file tidak pernah muncul di storage_path, sehingga dua baris DB berbeda
     * berakhir menunjuk ke satu file fisik yang sama).
     */
    public function copyPhysical(string $disk, string $sourcePath, string $newPathPrefix, string $filename): string
    {
        $newPath = trim($newPathPrefix, '/').'/'.$filename;

        Storage::disk($disk)->copy($sourcePath, $newPath);

        return $newPath;
    }

    /**
     * Hitung ulang mime type & ukuran dari file yang sudah tersimpan, disk-agnostic
     * (dipakai sebagai pengganti mime_content_type()/filesize() pada path lokal mentah).
     *
     * @return array{mime_type: string, size: int}
     */
    public function metadataFor(string $disk, string $path): array
    {
        return [
            'mime_type' => Storage::disk($disk)->mimeType($path),
            'size' => Storage::disk($disk)->size($path),
        ];
    }

    /**
     * Bungkus URL::temporarySignedRoute() — cara disk-agnostic untuk memberi akses
     * sementara ke file privat tanpa bergantung pada Storage::temporaryUrl() (tidak
     * didukung disk 'local', dan disk 's3' sengaja belum dipasang di project ini).
     */
    public function signedUrlFor(string $routeName, array $params, int $minutes = 60): string
    {
        return URL::temporarySignedRoute($routeName, now()->addMinutes($minutes), $params);
    }
}
