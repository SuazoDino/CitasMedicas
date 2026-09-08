<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MedicoHorariosTest extends TestCase
{
    use RefreshDatabase;

    private function crearMedico(): array
    {
        $user = User::factory()->create();

        $medicoId = DB::table('medicos')->insertGetId([
            'user_id' => $user->id,
            'id_doc_tipo' => 'DNI',
            'id_doc_numero' => '12345678',
            'id_doc_file' => null,
            'lic_tipo' => 'CMP',
            'lic_numero' => '0001',
            'lic_pais' => 'PE',
            'lic_file' => null,
            'verif_status' => 'provisional',
            'verif_notas' => null,
            'verified_at' => null,
            'is_searchable' => true,
            'provisional_expires_at' => null,
            'provisional_max_citas' => 5,
            'invite_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, $medicoId];
    }

    private function crearPaciente(): User
    {
        $user = User::factory()->create();

        DB::table('pacientes')->insert([
            'user_id' => $user->id,
            'doc_tipo' => 'DNI',
            'doc_numero' => '87654321',
            'birthdate' => '1990-01-01',
            'gender' => 'X',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function crearEspecialidad(): int
    {
        return DB::table('especialidades')->insertGetId([
            'nombre' => 'Medicina General',
            'slug' => Str::slug('Medicina General'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_medico_puede_gestionar_horarios()
    {
        [$medicoUser, $medicoId] = $this->crearMedico();

        Sanctum::actingAs($medicoUser, ['*']);

        $this->getJson('/api/medico/horarios')
            ->assertOk()
            ->assertJson([
                'medico_id' => $medicoId,
                'horarios' => [],
            ]);

        $created = $this->postJson('/api/medico/horarios', [
            'dia_semana' => 1,
            'hora_inicio' => '09:00',
            'hora_fin' => '12:00',
            'slot_min' => 30,
            'activo' => true,
        ])->assertCreated()
          ->assertJsonPath('horario.dia_semana', 1);

        $horarioId = $created['horario']['id'];

        $this->assertDatabaseHas('medico_horarios', [
            'id' => $horarioId,
            'medico_id' => $medicoId,
            'activo' => true,
        ]);

        $this->putJson("/api/medico/horarios/{$horarioId}", [
            'hora_fin' => '13:00',
            'slot_min' => 40,
        ])->assertOk()
          ->assertJsonPath('horario.hora_fin', '13:00')
          ->assertJsonPath('horario.slot_min', 40);

        $this->deleteJson("/api/medico/horarios/{$horarioId}")
            ->assertOk();

        $this->assertDatabaseHas('medico_horarios', [
            'id' => $horarioId,
            'activo' => false,
        ]);

        $this->getJson('/api/medico/horarios')
            ->assertOk()
            ->assertJsonPath('horarios.0.activo', false);
    }

    public function test_horarios_actualizados_afectan_reservas_de_citas()
    {
        [$medicoUser, $medicoId] = $this->crearMedico();
        $pacienteUser = $this->crearPaciente();
        $especialidadId = $this->crearEspecialidad();

        Sanctum::actingAs($medicoUser, ['*']);

        $this->postJson('/api/medico/horarios', [
            'dia_semana' => 1,
            'hora_inicio' => '09:00',
            'hora_fin' => '12:00',
            'slot_min' => 30,
        ])->assertCreated();

        $horarioId = DB::table('medico_horarios')->where('medico_id', $medicoId)->value('id');

        $slotDentro = Carbon::create(2025, 1, 6, 9, 0, 0)->toISOString(); // lunes
        $slotConflicto = Carbon::create(2025, 1, 6, 11, 30, 0)->toISOString();
        $slotPosterior = Carbon::create(2025, 1, 13, 9, 0, 0)->toISOString();

        Sanctum::actingAs($pacienteUser, ['*']);

        $this->postJson('/api/paciente/citas', [
            'medico_id' => $medicoId,
            'especialidad_id' => $especialidadId,
            'starts_at' => $slotDentro,
        ])->assertCreated()
          ->assertJsonStructure(['id']);

        $this->postJson('/api/paciente/citas', [
            'medico_id' => $medicoId,
            'especialidad_id' => $especialidadId,
            'starts_at' => $slotDentro,
        ])->assertStatus(422)
          ->assertJson(['message' => 'Horario ocupado']);

        Sanctum::actingAs($medicoUser, ['*']);

        $this->putJson("/api/medico/horarios/{$horarioId}", [
            'hora_fin' => '10:00',
        ])->assertOk();

        Sanctum::actingAs($pacienteUser, ['*']);

        $this->postJson('/api/paciente/citas', [
            'medico_id' => $medicoId,
            'especialidad_id' => $especialidadId,
            'starts_at' => $slotConflicto,
        ])->assertStatus(422)
          ->assertJson(['message' => 'El horario seleccionado no está disponible']);

        Sanctum::actingAs($medicoUser, ['*']);
        $this->deleteJson("/api/medico/horarios/{$horarioId}")->assertOk();

        Sanctum::actingAs($pacienteUser, ['*']);
        $this->postJson('/api/paciente/citas', [
            'medico_id' => $medicoId,
            'especialidad_id' => $especialidadId,
            'starts_at' => $slotPosterior,
        ])->assertStatus(422)
          ->assertJson(['message' => 'El horario seleccionado no está disponible']);
    }
}
