<?php

namespace Database\Seeders;

use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
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
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@segue-me.app',
            'password' => bcrypt('password'),
            'active' => true,
        ]);
        $superAdmin->assignRole(UserRole::SuperAdmin->value);

        // Diocese exemplo
        $diocese = Diocese::create([
            'name' => 'Diocese de Exemplo',
            'slug' => 'diocese-exemplo',
        ]);

        // Setor exemplo
        $sector = Sector::create([
            'diocese_id' => $diocese->id,
            'name' => 'Setor Norte',
            'slug' => 'setor-norte',
        ]);

        // Paróquia exemplo
        $parish = Parish::create([
            'sector_id' => $sector->id,
            'name' => 'Paróquia São João',
            'slug' => 'paroquia-sao-joao',
            'primary_color' => '#2e6da4',
            'secondary_color' => '#4a9fd4',
        ]);

        // Parish Admin
        $parishAdmin = User::factory()->create([
            'name' => 'Admin da Paróquia',
            'email' => 'parish@segue-me.app',
            'password' => bcrypt('password'),
            'parish_id' => $parish->id,
            'active' => true,
        ]);
        $parishAdmin->assignRole(UserRole::ParishAdmin->value);

        // Coordinator
        $coordinator = User::factory()->create([
            'name' => 'Coordenador',
            'email' => 'coord@segue-me.app',
            'password' => bcrypt('password'),
            'parish_id' => $parish->id,
            'active' => true,
        ]);
        $coordinator->assignRole(UserRole::Coordinator->value);
    }
}
