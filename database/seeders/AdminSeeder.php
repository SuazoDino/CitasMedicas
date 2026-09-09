<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Administrador;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Evitar duplicados basados en el email
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@medireserva.com'],
            [
                'password' => Hash::make('admin123'),
                'rol_id' => 1, // ID 1 = Admin según tu BD
                'estado' => 'activo',
                'email_verified_at' => Carbon::now(),
            ]
        );

        // Crear el perfil de administrador si no existe
        Administrador::firstOrCreate(
            ['usuario_id' => $adminUser->id],
            [
                'nombres' => 'Administrador',
                'apellidos' => 'Sistema',
            ]
        );
    }
}
