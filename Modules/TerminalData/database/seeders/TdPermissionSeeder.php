<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TerminalDataPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ===================================
        // PERMISSIONS - Terminal Data
        // ===================================

        // Folder Permissions (Sesuai Policy)
        $folderPermissions = [
            'td_folder_view',                // Lihat folder (semua pegawai)
            'td_folder_create',              // Buat folder baru
            'td_folder_update',              // Update folder (general)
            'td_folder_rename',              // Ganti nama folder
            'td_folder_delete',              // Hapus folder (soft delete)
            'td_folder_restore',             // Restore folder dari sampah
            'td_folder_force_delete',        // Hapus permanen
            'td_folder_view_trashed',        // Lihat semua sampah (ADMIN/KABAN/SEKBAN)
        ];

        // File Permissions (Sesuai Policy)
        $filePermissions = [
            'td_file_view',                  // Lihat file (semua pegawai)
            'td_file_upload',                // Upload file
            'td_file_download',              // Download file (semua pegawai)
            'td_file_update',                // Update/ganti nama file
            'td_file_move',                  // Pindahkan file
            'td_file_delete',                // Hapus file (soft delete)
            'td_file_restore',               // Restore file dari sampah
            'td_file_force_delete',          // Hapus permanen
            'td_file_view_trashed',          // Lihat semua sampah (ADMIN/KABAN/SEKBAN)
        ];

        // Create all permissions
        $allPermissions = array_merge(
            $folderPermissions,
            $filePermissions
        );

        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // ===================================
        // ROLES - Berdasarkan Kode Jabatan di Policy
        // ===================================

        // 1. ADMIN - Full Access (Super Admin)
        $admin = Role::create(['name' => 'ADMIN']);
        $admin->givePermissionTo(Permission::all());

        // 2. KABAN (Kepala Dinas) - Akses Semua
        $kaban = Role::create(['name' => 'KABAN']);
        $kaban->givePermissionTo([
            // Folder permissions - Full access
            'td_folder_view',
            'td_folder_create',
            'td_folder_update',
            'td_folder_rename',
            'td_folder_delete',
            'td_folder_restore',
            'td_folder_force_delete',
            'td_folder_view_trashed',

            // File permissions - Full access
            'td_file_view',
            'td_file_upload',
            'td_file_download',
            'td_file_update',
            'td_file_move',
            'td_file_delete',
            'td_file_restore',
            'td_file_force_delete',
            'td_file_view_trashed',
        ]);

        // 3. SEKBAN (Sekretaris) - Akses Semua
        $sekban = Role::create(['name' => 'SEKBAN']);
        $sekban->givePermissionTo([
            // Folder permissions - Full access
            'td_folder_view',
            'td_folder_create',
            'td_folder_update',
            'td_folder_rename',
            'td_folder_delete',
            'td_folder_restore',
            'td_folder_force_delete',
            'td_folder_view_trashed',

            // File permissions - Full access
            'td_file_view',
            'td_file_upload',
            'td_file_download',
            'td_file_update',
            'td_file_move',
            'td_file_delete',
            'td_file_restore',
            'td_file_force_delete',
            'td_file_view_trashed',
        ]);

        // 4. KABID (Kepala Bidang) - Akses Bidang
        $kabid = Role::create(['name' => 'KABID']);
        $kabid->givePermissionTo([
            // Folder permissions - bidang scope
            'td_folder_view',
            'td_folder_create',
            'td_folder_update',
            'td_folder_rename',          // Rename semua folder di bidangnya
            'td_folder_delete',          // Delete folder sendiri
            'td_folder_restore',         // Restore folder sendiri
            'td_folder_force_delete',    // Force delete folder sendiri

            // File permissions - bidang scope for upload, own for edit/delete
            'td_file_view',
            'td_file_upload',            // Upload di folder bidangnya
            'td_file_download',
            'td_file_update',            // Update file sendiri
            'td_file_move',              // Move file sendiri
            'td_file_delete',            // Delete file sendiri
            'td_file_restore',           // Restore file sendiri
            'td_file_force_delete',      // Force delete file sendiri
        ]);

        // 5. KASUBAG (Kepala Sub Bidang) - Akses Sub Bidang
        $kasubag = Role::create(['name' => 'KASUBAG']);
        $kasubag->givePermissionTo([
            // Folder permissions - own items only
            'td_folder_view',
            'td_folder_create',
            'td_folder_update',          // Update folder sendiri
            'td_folder_rename',          // Rename folder sendiri
            'td_folder_delete',          // Delete folder sendiri
            'td_folder_restore',         // Restore folder sendiri
            'td_folder_force_delete',    // Force delete folder sendiri

            // File permissions - bidang scope for upload, own for edit/delete
            'td_file_view',
            'td_file_upload',            // Upload di folder bidangnya
            'td_file_download',
            'td_file_update',            // Update file sendiri
            'td_file_move',              // Move file sendiri
            'td_file_delete',            // Delete file sendiri
            'td_file_restore',           // Restore file sendiri
            'td_file_force_delete',      // Force delete file sendiri
        ]);

        // 6. PELAKSANA - Akses Terbatas (Own Items)
        $pelaksana = Role::create(['name' => 'PELAKSANA']);
        $pelaksana->givePermissionTo([
            // Folder permissions - own items only
            'td_folder_view',
            'td_folder_create',
            'td_folder_update',          // Update folder sendiri
            'td_folder_rename',          // Rename folder sendiri
            'td_folder_delete',          // Delete folder sendiri
            'td_folder_restore',         // Restore folder sendiri
            'td_folder_force_delete',    // Force delete folder sendiri

            // File permissions - bidang scope for upload, own for edit/delete
            'td_file_view',
            'td_file_upload',            // Upload di folder bidangnya
            'td_file_download',
            'td_file_update',            // Update file sendiri
            'td_file_move',              // Move file sendiri
            'td_file_delete',            // Delete file sendiri
            'td_file_restore',           // Restore file sendiri
            'td_file_force_delete',      // Force delete file sendiri
        ]);

        // 7. JAFUNG (Jabatan Fungsional) - Akses Terbatas (Own Items)
        $jafung = Role::create(['name' => 'JAFUNG']);
        $jafung->givePermissionTo([
            // Folder permissions - own items only
            'td_folder_view',
            'td_folder_create',
            'td_folder_update',          // Update folder sendiri
            'td_folder_rename',          // Rename folder sendiri
            'td_folder_delete',          // Delete folder sendiri
            'td_folder_restore',         // Restore folder sendiri
            'td_folder_force_delete',    // Force delete folder sendiri

            // File permissions - bidang scope for upload, own for edit/delete
            'td_file_view',
            'td_file_upload',            // Upload di folder bidangnya
            'td_file_download',
            'td_file_update',            // Update file sendiri
            'td_file_move',              // Move file sendiri
            'td_file_delete',            // Delete file sendiri
            'td_file_restore',           // Restore file sendiri
            'td_file_force_delete',      // Force delete file sendiri
        ]);

        // 8. GATEK (Gabungan Teknisi) - Akses Terbatas (Own Items)
        $gatek = Role::create(['name' => 'GATEK']);
        $gatek->givePermissionTo([
            // Folder permissions - own items only
            'td_folder_view',
            'td_folder_create',
            'td_folder_update',          // Update folder sendiri
            'td_folder_rename',          // Rename folder sendiri
            'td_folder_delete',          // Delete folder sendiri
            'td_folder_restore',         // Restore folder sendiri
            'td_folder_force_delete',    // Force delete folder sendiri

            // File permissions - bidang scope for upload, own for edit/delete
            'td_file_view',
            'td_file_upload',            // Upload di folder bidangnya
            'td_file_download',
            'td_file_update',            // Update file sendiri
            'td_file_move',              // Move file sendiri
            'td_file_delete',            // Delete file sendiri
            'td_file_restore',           // Restore file sendiri
            'td_file_force_delete',      // Force delete file sendiri
        ]);
    }
}
