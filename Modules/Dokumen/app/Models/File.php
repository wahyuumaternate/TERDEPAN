<?php

namespace Modules\Dokumen\Models;

use App\Models\MasterPegawai;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;

    protected $table = 'doc_file';

    protected $fillable = [
        'dokumen_id',
        'version',
        'nama_file',
        'file_path',
        'extension',
        'size_kb',
        'hash',
        'keterangan',
        'is_current',
        'uploaded_by'
    ];

    protected $casts = [
        'dokumen_id' => 'integer',
        'version' => 'integer',
        'size_kb' => 'integer',
        'is_current' => 'boolean',
        'uploaded_by' => 'integer',
    ];

    protected $appends = ['formatted_size', 'file_url'];

    // Relationships
    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class, 'dokumen_id');
    }

    public function uploader()
    {
        return $this->belongsTo(MasterPegawai::class, 'uploaded_by');
    }

    // Scopes
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeByDokumen($query, $dokumenId)
    {
        return $query->where('dokumen_id', $dokumenId);
    }

    public function scopeLatestVersion($query)
    {
        return $query->orderBy('version', 'desc');
    }

    // Accessors
    public function getFormattedSizeAttribute()
    {
        $sizeKb = $this->size_kb;
        $units = ['KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;

        while ($sizeKb >= 1024 && $unitIndex < count($units) - 1) {
            $sizeKb /= 1024;
            $unitIndex++;
        }

        return number_format($sizeKb, 2) . ' ' . $units[$unitIndex];
    }

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    public function getDownloadUrlAttribute()
    {
        return route('dokumen.download', $this->id);
    }

    public function getFileIconAttribute()
    {
        return match (strtolower($this->extension)) {
            'pdf' => 'bi-file-pdf',
            'doc', 'docx' => 'bi-file-word',
            'xls', 'xlsx' => 'bi-file-excel',
            'ppt', 'pptx' => 'bi-file-ppt',
            'jpg', 'jpeg', 'png', 'gif' => 'bi-file-image',
            'zip', 'rar', '7z' => 'bi-file-zip',
            'txt' => 'bi-file-text',
            default => 'bi-file-earmark'
        };
    }

    public function getMimeTypeAttribute()
    {
        return match (strtolower($this->extension)) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'application/octet-stream'
        };
    }

    // Methods
    public function exists()
    {
        return Storage::disk('public')->exists($this->file_path);
    }

    public function getFullPath()
    {
        return storage_path('app/public/' . $this->file_path);
    }

    public function verifyHash()
    {
        if (!$this->exists()) {
            return false;
        }

        $currentHash = hash_file('sha256', $this->getFullPath());
        return $currentHash === $this->hash;
    }

    public function makeNotCurrent()
    {
        return $this->update(['is_current' => false]);
    }

    public function makeCurrent()
    {
        // Set all other versions as not current
        self::where('dokumen_id', $this->dokumen_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);

        // Set this as current
        return $this->update(['is_current' => true]);
    }

    public function deleteFile()
    {
        if ($this->exists()) {
            Storage::disk('public')->delete($this->file_path);
        }

        return $this->delete();
    }

    public function getContent()
    {
        if (!$this->exists()) {
            return null;
        }

        return Storage::disk('public')->get($this->file_path);
    }

    public function download()
    {
        if (!$this->exists()) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($this->file_path, $this->nama_file);
    }

    public static function createFromUpload($dokumenId, $uploadedFile, $version = 1, $keterangan = null)
    {
        $fileName = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->getClientOriginalExtension();
        $sizeKb = round($uploadedFile->getSize() / 1024);

        // Store file
        $path = $uploadedFile->store('dokumen/' . date('Y/m'), 'public');

        // Calculate hash
        $hash = hash_file('sha256', $uploadedFile->getRealPath());

        return self::create([
            'dokumen_id' => $dokumenId,
            'version' => $version,
            'nama_file' => $fileName,
            'file_path' => $path,
            'extension' => $extension,
            'size_kb' => $sizeKb,
            'hash' => $hash,
            'keterangan' => $keterangan,
            'is_current' => true,
            'uploaded_by' => auth()->id(),
        ]);
    }
}
