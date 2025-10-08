<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Define roles
        $roles = [
            'admin',
            'kaban',
            'sekban',
            'kabid',
            'jafung',
            'pelaksana',
            'tenaga_teknis',
        ];

        // Define permissions
        $permissions = [
            // Tugas Pokok
            'tugas_pokok.create',
            'tugas_pokok.read',
            'tugas_pokok.update',
            'tugas_pokok.delete',
            'tugas_pokok.approve',
            // Tugas Harian
            'tugas_harian.create',
            'tugas_harian.read',
            'tugas_harian.update',
            'tugas_harian.delete',
            // Tugas Tambahan
            'tugas_tambahan.create',
            'tugas_tambahan.read',
            'tugas_tambahan.update',
            'tugas_tambahan.delete',
            // Penugasan Mandiri
            'penugasan_mandiri.create',
            'penugasan_mandiri.read',
            'penugasan_mandiri.update',
            'penugasan_mandiri.delete',
            'penugasan_mandiri.approve',
            // Validasi & Revisi
            'validasi_tugas',
            'revisi_tugas',
            // Delegasi
            'delegasi_tugas',
            // Monitoring
            'monitoring_beban_kerja',
            // Dashboard & Laporan
            'dashboard.view',
            'laporan.generate',
            // Notifikasi
            'notifikasi.send',
            'notifikasi.manage',
            // Integrasi
            'integrasi.simpeg',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $rolePermissions = [
            'admin' => $permissions,
            'kaban' => [
                'tugas_pokok.create',
                'tugas_pokok.read',
                'tugas_pokok.update',
                'tugas_pokok.delete',
                'tugas_pokok.approve',
                'dashboard.view',
                'laporan.generate',
                'monitoring_beban_kerja',
                'delegasi_tugas',
                'notifikasi.send',
                'integrasi.simpeg',
            ],
            'sekban' => [
                'tugas_pokok.read',
                'tugas_pokok.approve',
                'dashboard.view',
                'laporan.generate',
                'monitoring_beban_kerja',
                'delegasi_tugas',
            ],
            'kabid' => [
                'tugas_pokok.create',
                'tugas_pokok.read',
                'tugas_pokok.update',
                'tugas_pokok.approve',
                'tugas_harian.create',
                'tugas_harian.read',
                'tugas_harian.update',
                'tugas_tambahan.create',
                'tugas_tambahan.read',
                'tugas_tambahan.update',
                'dashboard.view',
                'laporan.generate',
                'monitoring_beban_kerja',
                'delegasi_tugas',
            ],
            'jafung' => [
                'tugas_pokok.read',
                'tugas_harian.read',
                'tugas_tambahan.read',
                'penugasan_mandiri.create',
                'penugasan_mandiri.read',
                'penugasan_mandiri.update',
                'dashboard.view',
            ],
            'pelaksana' => [
                'tugas_pokok.read',
                'tugas_harian.read',
                'tugas_tambahan.read',
                'penugasan_mandiri.create',
                'penugasan_mandiri.read',
                'penugasan_mandiri.update',
                'dashboard.view',
            ],
            'tenaga_teknis' => [
                'dashboard.view',
            ],
        ];

        foreach ($roles as $role) {
            $roleModel = Role::firstOrCreate(['name' => $role]);
            $roleModel->syncPermissions($rolePermissions[$role] ?? []);
        }
    }
}
