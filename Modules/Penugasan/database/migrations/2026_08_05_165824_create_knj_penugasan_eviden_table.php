<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Menggantikan pola lama: satu upload eviden pada penugasan grup kolektif menduplikasi
     * baris TdFile (satu per anggota, menunjuk file fisik yang sama di disk) karena
     * TdFile.attachable_id cuma bisa menunjuk SATU entity. Duplikasi itu akar dari beberapa
     * bug sinkron/hapus yang berulang muncul (lihat docs/analysis/rekomendasi-arsitektur-eviden-kinerja.md
     * §2.1). Tabel pivot ini membolehkan SATU baris TdFile ditautkan ke BANYAK penugasan.
     */
    public function up(): void
    {
        Schema::create('knj_penugasan_eviden', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('penugasan_id')->constrained('knj_penugasan')->cascadeOnDelete();
            $table->foreignUuid('td_file_id')->constrained('td_files')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['penugasan_id', 'td_file_id']);
        });

        $this->migrasikanDataLama();
    }

    /**
     * Migrasi satu-kali: kelompokkan baris TdFile lama (attachable_type = 'penugasan') yang
     * menunjuk storage_path fisik yang sama (hasil duplikasi cara lama), jadikan baris paling
     * awal sebagai kanonis, buat baris pivot untuk setiap penugasan yang tadinya punya baris
     * duplikat sendiri, lalu hapus baris duplikat (BUKAN file fisiknya — masih dipakai baris
     * kanonis). Tidak reversible dengan presisi penuh, karena itu down() tidak mencoba
     * merekonstruksi baris duplikat yang sudah dihapus.
     */
    private function migrasikanDataLama(): void
    {
        $rows = DB::table('td_files')
            ->where('attachable_type', 'penugasan')
            ->whereNotNull('attachable_id')
            ->orderBy('created_at')
            ->get();

        $pasanganTersimpan = [];

        foreach ($rows->groupBy('storage_path') as $grup) {
            $kanonis = $grup->first();

            foreach ($grup as $row) {
                $kunci = $row->attachable_id.'|'.$kanonis->id;
                if (isset($pasanganTersimpan[$kunci])) {
                    continue;
                }
                $pasanganTersimpan[$kunci] = true;

                DB::table('knj_penugasan_eviden')->insert([
                    'id' => (string) Str::uuid(),
                    'penugasan_id' => $row->attachable_id,
                    'td_file_id' => $kanonis->id,
                    'created_by' => $row->created_by,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            $duplikatIds = $grup->where('id', '!=', $kanonis->id)->pluck('id');
            if ($duplikatIds->isNotEmpty()) {
                DB::table('td_files')->whereIn('id', $duplikatIds)->delete();
            }

            // Baris kanonis tidak lagi memakai attachable_* untuk kaitan ke Penugasan —
            // kepemilikannya sekarang sepenuhnya lewat knj_penugasan_eviden.
            DB::table('td_files')->where('id', $kanonis->id)->update([
                'attachable_type' => null,
                'attachable_id' => null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('knj_penugasan_eviden');
    }
};
