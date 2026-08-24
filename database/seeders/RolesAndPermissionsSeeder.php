<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'access admin panel',
            'manage users',
            'manage rates',
            'manage crypto assets',
            'manage fiat currencies',
            'manage feature toggles',
            'manage transactions',
            'manage gift cards',
            'manage payment gateways',
            'manage system settings',
            'view system logs',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $superAdmin = Role::findOrCreate('super-admin');
        $superAdmin->syncPermissions($permissions);

        $admin = Role::findOrCreate('admin');
        $admin->syncPermissions([
            'access admin panel',
            'manage users',
            'manage rates',
            'manage crypto assets',
            'manage fiat currencies',
            'manage transactions',
            'manage gift cards',
            'view system logs',
        ]);

        Role::findOrCreate('user');
    }
}
