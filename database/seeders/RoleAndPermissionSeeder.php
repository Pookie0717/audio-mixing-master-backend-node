<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $adminGuardPermissions = ['orders', 'admins', 'users', 'assign_orders', 'order_status_change'];
        foreach ($adminGuardPermissions as $permission) Permission::create(['name' => $permission]);

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'engineer']);

        Role::findByName('admin')->givePermissionTo($adminGuardPermissions);
        Role::findByName('engineer')->givePermissionTo(['orders', 'assign_orders']);

        // ==============================================
        Role::create(['name' => 'user']);
    }
}
