<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissionCollection = collect([
            'fields' => [
                'create fields',
                'read fields',
                'update fields',
                'delete fields',
            ],
            'quarters' => [
                'create quarters',
                'read quarters',
                'update quarters',
                'delete quarters',
            ],
            'plants' => [
                'create plants',
                'read plants',
                'update plants',
                'delete plants',
            ],
            'harvests' => [
                'create harvests',
                'read harvests',
                'update harvests',
                'delete harvests',
            ],
        ]);

        $allPermissions = $permissionCollection->collapse();
        $allPermissions->map(function (string $permission) {
            Permission::create(['name' => $permission]);
        });

        Role::create(['name' => 'Agricultor'])
            ->givePermissionTo($allPermissions);
        Role::create(['name' => 'Técnico'])
            ->givePermissionTo($allPermissions);
        Role::create(['name' => 'Administrador'])
            ->givePermissionTo($allPermissions);
        Role::create(['name' => 'Super Admin'])
            ->givePermissionTo($allPermissions);
    }
}
