<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'aandradevrb@gmail.com'], // clave de búsqueda
            [
                'name'      => 'Administrador EnigmaCero',
                'password'  => Hash::make('aandradevrb'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );
    }
}
