<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create the 4 fundamental roles
        $adminRole = Role::firstOrCreate(
            ['name' => Role::ROLE_ADMIN],
            ['permissions' => ['all' => true, 'erp' => true, 'users' => true, 'finance' => true]]
        );

        $warehouseRole = Role::firstOrCreate(
            ['name' => Role::ROLE_WAREHOUSE],
            ['permissions' => ['erp' => true, 'inventory' => true, 'restock' => true, 'serials' => true]]
        );

        $techRole = Role::firstOrCreate(
            ['name' => Role::ROLE_TECH],
            ['permissions' => ['erp' => true, 'support' => true, 'rma' => true, 'schedules' => true]]
        );

        $customerRole = Role::firstOrCreate(
            ['name' => Role::ROLE_CUSTOMER],
            ['permissions' => ['storefront' => true, 'builds' => true, 'orders' => true]]
        );

        // 2. Default Seed Users
        $defaultPassword = Hash::make('password123');

        User::firstOrCreate(
            ['email' => 'admin@custompc.local'],
            [
                'role_id' => $adminRole->id,
                'username' => 'admin',
                'name' => 'System Administrator',
                'phone' => '+6012-3456789',
                'password' => $defaultPassword,
            ]
        );

        User::firstOrCreate(
            ['email' => 'warehouse@custompc.local'],
            [
                'role_id' => $warehouseRole->id,
                'username' => 'warehouse_staff',
                'name' => 'Warehouse Manager',
                'phone' => '+6012-9876543',
                'password' => $defaultPassword,
            ]
        );

        User::firstOrCreate(
            ['email' => 'tech@custompc.local'],
            [
                'role_id' => $techRole->id,
                'username' => 'tech_specialist',
                'name' => 'Lead Technician',
                'phone' => '+6013-1122334',
                'password' => $defaultPassword,
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@custompc.local'],
            [
                'role_id' => $customerRole->id,
                'username' => 'demo_customer',
                'name' => 'Demo Customer',
                'phone' => '+6017-5566778',
                'password' => $defaultPassword,
            ]
        );
    }
}
