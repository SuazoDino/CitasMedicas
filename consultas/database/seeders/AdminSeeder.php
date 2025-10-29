<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder {
    public function run(): void {
        $email = 'admin@demo.local';
        $id = DB::table('users')->where('email',$email)->value('id');
        if (!$id) {
            $id = DB::table('users')->insertGetId([
                'name' => 'Admin Demo',
                'email'=> $email,
                'password' => Hash::make('1234'), // cámbialo luego
                'verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        // rol admin (1)
        DB::table('user_role')->updateOrInsert(
            ['user_id'=>$id,'role_id'=>1],
            []
        );
    }
}
