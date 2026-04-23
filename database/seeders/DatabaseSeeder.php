<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Criar roles
        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }

        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@segue-me.app'],
            [
                'name' => 'Administrador Geral',
                'password' => bcrypt('admin@2026'),
                'active' => true,
            ],
        );
        $superAdmin->assignRole(UserRole::SuperAdmin->value);
    }
}
