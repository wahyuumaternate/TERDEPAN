<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Folder pegawai di bawah "Eviden Kinerja" sebelumnya dinamai "{nama} ({tipe}: {nomor identitas})"
     * (lihat EvidenFolderResolver sebelum diperbaiki). Nama itu diringkas jadi nama pegawai saja —
     * migrasi ini menyamakan folder yang sudah terlanjur dibuat dengan nama lama, supaya
     * EvidenFolderResolver::resolveForPegawai() (yang mencari folder lewat nama persis) tetap
     * menemukan folder yang sama, bukan membuat folder baru dan membuat eviden lama "hilang".
     */
    public function up(): void
    {
        $root = DB::table('td_folders')->whereNull('parent_id')->where('name', 'Eviden Kinerja')->first();

        if (! $root) {
            return;
        }

        DB::table('td_folders')
            ->where('parent_id', $root->id)
            ->join('users', 'users.id', '=', 'td_folders.created_by')
            ->select('td_folders.id', 'users.nama')
            ->get()
            ->each(function ($folder) {
                DB::table('td_folders')->where('id', $folder->id)->update(['name' => $folder->nama]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nama lama (dengan NIP) tidak disimpan, tidak bisa dikembalikan.
    }
};
