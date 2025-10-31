<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EspecialidadesSeeder extends Seeder {
  public function run(): void {
    $items = ['Cardiología','Neurología','Pediatría','Traumatología','Odontología','Oftalmología'];
    foreach ($items as $nom) {
      DB::table('especialidades')->updateOrInsert(
        ['slug' => Str::slug($nom)],
        ['nombre' => $nom, 'created_at'=>now(), 'updated_at'=>now()]
      );
    }
  }
}
