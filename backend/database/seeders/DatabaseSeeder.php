<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $managerRole      = Role::firstOrCreate(['name' => 'manager']);
        $salesOfficerRole = Role::firstOrCreate(['name' => 'sales_officer']);

        $manager = User::updateOrCreate(
            ['email' => 'manager@semre.com'],
            [
                'name'              => 'Manager User',
                'password'          => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $manager->syncRoles($managerRole);

        $salesOfficer = User::updateOrCreate(
            ['email' => 'sales@semre.com'],
            [
                'name'              => 'Sales Officer',
                'password'          => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $salesOfficer->syncRoles($salesOfficerRole);
    }
}
