<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessRolesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function () {
            $roles = [
                'cliente' => ['profile.update-own', 'reservations.view-own'],
                'recepcion' => ['profile.update-own', 'reservations.view-all', 'sessions.view-all'],
                'game_master' => ['profile.update-own', 'sessions.view-assigned'],
                'mantenimiento' => ['profile.update-own', 'incidents.view-assigned'],
                'administrador' => [
                    'profile.update-own', 'reservations.view-own', 'reservations.view-all',
                    'sessions.view-assigned', 'sessions.view-all',
                    'incidents.view-assigned', 'incidents.view-all', 'administration.view',
                ],
            ];

            foreach (array_unique(array_merge(...array_values($roles))) as $permission) {
                Permission::findOrCreate($permission, 'web');
            }

            // DatabaseSeeder suppresses model events; refresh newly created permissions explicitly.
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            foreach ($roles as $name => $permissions) {
                Role::findOrCreate($name, 'web')->syncPermissions($permissions);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
