<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class FixDoubleHashedPasswords extends Command
{
    protected $signature = 'fix:double-hashed-passwords {email?}';
    protected $description = 'Corrige contraseñas que fueron hasheadas dos veces';

    public function handle()
    {
        $email = $this->argument('email');
        
        if ($email) {
            $users = User::where('email', $email)->get();
        } else {
            $users = User::all();
        }
        
        if ($users->isEmpty()) {
            $this->error('No se encontraron usuarios.');
            return 1;
        }
        
        $this->info("Se encontraron {$users->count()} usuario(s).");
        $this->warn('Este comando intentará corregir contraseñas doblemente hasheadas.');
        $this->warn('IMPORTANTE: Necesitarás proporcionar la contraseña original para cada usuario.');
        
        foreach ($users as $user) {
            $this->line("\n--- Usuario: {$user->email} (ID: {$user->id}) ---");
            
            $password = $this->secret("Ingresa la contraseña original para {$user->email}:");
            
            if (empty($password)) {
                $this->warn("Se saltó el usuario {$user->email}.");
                continue;
            }
            
            // Verificar si la contraseña actual coincide (puede estar doblemente hasheada)
            $currentMatch = Hash::check($password, $user->password);
            
            if ($currentMatch) {
                $this->info("✓ La contraseña ya está correcta para {$user->email}.");
                continue;
            }
            
            // Intentar corregir: hashear una vez más y verificar
            // Si la contraseña está doblemente hasheada, necesitamos re-hashearla correctamente
            $user->password = $password; // El cast 'hashed' lo hasheará automáticamente
            $user->save();
            
            // Verificar que ahora funciona
            $user->refresh();
            $newMatch = Hash::check($password, $user->password);
            
            if ($newMatch) {
                $this->info("✓ Contraseña corregida exitosamente para {$user->email}.");
            } else {
                $this->error("✗ No se pudo corregir la contraseña para {$user->email}.");
            }
        }
        
        $this->info("\n✓ Proceso completado.");
        return 0;
    }
}
