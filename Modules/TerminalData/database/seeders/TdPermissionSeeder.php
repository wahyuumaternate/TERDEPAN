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
        
        // Folder Permissions
        $folderPermissions = [
            // Basic CRUD
            'td_folder_view_all',           // Lihat semua folder
            'td_folder_view_own',            // Lihat folder sendiri
            'td_folder_view_bidang',         // Lihat folder bidang
            'td_folder_view_sub_bidang',     // Lihat folder sub bidang
            'td_folder_create',              // Buat folder baru
            'td_folder_edit_own',            // Edit folder sendiri
            'td_folder_edit_bidang',         // Edit folder bidang
            'td_folder_delete_own',          // Hapus folder sendiri
            'td_folder_delete_bidang',       // Hapus folder bidang
            'td_folder_restore',             // Restore folder dari sampah
            'td_folder_force_delete',        // Hapus permanen
        ];

        // File Permissions
        $filePermissions = [
            'td_file_view_all',              // Lihat semua file
            'td_file_view_own',              // Lihat file sendiri
            'td_file_view_bidang',           // Lihat file bidang
            'td_file_view_sub_bidang',       // Lihat file sub bidang
            'td_file_upload',                // Upload file
            'td_file_download_own',          // Download file sendiri
            'td_file_download_bidang',       // Download file bidang
            'td_file_download_all',          // Download semua file
            'td_file_edit_own',              // Edit file sendiri
            'td_file_edit_bidang',           // Edit file bidang
            'td_file_delete_own',            // Hapus file sendiri
            'td_file_delete_bidang',         // Hapus file bidang
            'td_file_restore',               // Restore file dari sampah
            'td_file_force_delete',          // Hapus permanen
        ];

        // Trash Permissions
        $trashPermissions = [
            'td_trash_view',                 // Lihat sampah
            'td_trash_empty',                // Kosongkan sampah
        ];

        // Share Permissions
        $sharePermissions = [
            'td_share_internal',             // Share ke pegawai internal
            'td_share_bidang',               // Share ke bidang
            'td_share_public',               // Share public
        ];

        // Create all permissions
        $allPermissions = array_merge(
            $folderPermissions,
            $filePermissions,
            $trashPermissions,
            $sharePermissions
        );

        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // ===================================
        // ROLES - Berdasarkan Level Hierarki
        // ===================================

        // 1. Super Admin - Full Access
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. Kepala Dinas - Akses Semua Bidang
        $kepalaDinas = Role::create(['name' => 'Kepala Dinas']);
        $kepalaDinas->givePermissionTo([
            'td_folder_view_all',
            'td_folder_create',
            'td_folder_edit_bidang',
            'td_folder_delete_bidang',
            'td_file_view_all',
            'td_file_upload',
            'td_file_download_all',
            'td_file_edit_bidang',
            'td_file_delete_bidang',
            'td_trash_view',
            'td_share_public',
            'td_share_bidang',
            'td_share_internal',
        ]);

        // 3. Sekretaris - Akses Semua Bidang (Limited)
        $sekretaris = Role::create(['name' => 'Sekretaris']);
        $sekretaris->givePermissionTo([
            'td_folder_view_all',
            'td_folder_create',
            'td_folder_edit_own',
            'td_file_view_all',
            'td_file_upload',
            'td_file_download_all',
            'td_file_edit_own',
            'td_trash_view',
            'td_share_bidang',
            'td_share_internal',
        ]);

        // 4. Kepala Bidang - Akses Bidang Sendiri
        $kepalaBidang = Role::create(['name' => 'Kepala Bidang']);
        $kepalaBidang->givePermissionTo([
            'td_folder_view_bidang',
            'td_folder_create',
            'td_folder_edit_bidang',
            'td_folder_delete_bidang',
            'td_file_view_bidang',
            'td_file_upload',
            'td_file_download_bidang',
            'td_file_edit_bidang',
            'td_file_delete_bidang',
            'td_trash_view',
            'td_share_bidang',
            'td_share_internal',
        ]);

        // 5. Kepala Sub Bidang - Akses Sub Bidang
        $kepalaSubBidang = Role::create(['name' => 'Kepala Sub Bidang']);
        $kepalaSubBidang->givePermissionTo([
            'td_folder_view_sub_bidang',
            'td_folder_create',
            'td_folder_edit_own',
            'td_file_view_sub_bidang',
            'td_file_upload',
            'td_file_download_bidang',
            'td_file_edit_own',
            'td_file_delete_own',
            'td_trash_view',
            'td_share_internal',
        ]);

        // 6. Staff - Akses Terbatas
        $staff = Role::create(['name' => 'Staff']);
        $staff->givePermissionTo([
            'td_folder_view_own',
            'td_folder_create',
            'td_folder_edit_own',
            'td_folder_delete_own',
            'td_file_view_own',
            'td_file_upload',
            'td_file_download_own',
            'td_file_edit_own',
            'td_file_delete_own',
            'td_share_internal',
        ]);
    }
}