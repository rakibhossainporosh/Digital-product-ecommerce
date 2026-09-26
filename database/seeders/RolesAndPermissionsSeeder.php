<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Standard Shield permissions for User and Role
        $entities = ['User', 'Role'];
        $abilities = [
            'ViewAny',
            'View',
            'Create',
            'Update',
            'Delete',
            'DeleteAny',
            'Restore',
            'RestoreAny',
            'ForceDelete',
            'ForceDeleteAny',
            'Replicate',
            'Reorder',
        ];

        foreach ($entities as $entity) {
            foreach ($abilities as $ability) {
                Permission::firstOrCreate([
                    'name' => "{$ability}:{$entity}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // 1. Super Admin Role (all permissions)
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);
        $superAdminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        // 2. Admin Role (standard administrative CRUD access)
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $adminPermissions = Permission::where('guard_name', 'web')
            ->where(function ($query) {
                $query->where('name', 'like', 'View%')
                    ->orWhere('name', 'like', 'Create%')
                    ->orWhere('name', 'like', 'Update%')
                    ->orWhere('name', 'like', 'Delete:User');
            })
            ->get();
        $adminRole->syncPermissions($adminPermissions);

        // 3. Manager Role (read users & roles)
        $managerRole = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web',
        ]);
        $managerRole->syncPermissions(
            Permission::where('guard_name', 'web')
                ->where('name', 'like', 'View%')
                ->get()
        );

        // 4. Viewer Role (read-only)
        $viewerRole = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'web',
        ]);
        $viewerRole->syncPermissions(
            Permission::where('guard_name', 'web')
                ->whereIn('name', ['ViewAny:User', 'View:User', 'ViewAny:Role', 'View:Role'])
                ->get()
        );
    }
}
