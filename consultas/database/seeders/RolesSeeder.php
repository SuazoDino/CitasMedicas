<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder {
    public function run(): void {
        DB::table('roles')->upsert([
            ['id'=>1,'name'=>'admin'],
            ['id'=>2,'name'=>'paciente'],
            ['id'=>3,'name'=>'medico'],
        ], ['id'], ['name']);
    }
}
