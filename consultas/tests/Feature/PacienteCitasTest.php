<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PacienteCitasTest extends TestCase
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
        static $counter = 0;
        $counter++;

        $user = User::factory()->create();

        DB::table('pacientes')->insert([
            'user_id' => $user->id,
            'doc_tipo' => 'DNI',
            'doc_numero' => str_pad((string) (80000000 + $counter), 8, '0', STR_PAD_LEFT),
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

    private function crearHorario(int $medicoId, int $diaSemana = 1, string $horaInicio = '09:00', string $horaFin = '12:00', int $slot = 30): void
    {
        DB::table('medico_horarios')->insert([
            'medico_id' => $medicoId,
            'dia_semana' => $diaSemana,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'slot_min' => $slot,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_paciente_puede_cancelar_cita(): void
    {
        [$medicoUser, $medicoId] = $this->crearMedico();
        $pacienteUser = $this->crearPaciente();
        $especialidadId = $this->crearEspecialidad();
        $this->crearHorario($medicoId);

        $slot = Carbon::create(2025, 1, 6, 9, 0, 0)->toISOString();

        Sanctum::actingAs($pacienteUser, ['*']);

        $created = $this->postJson('/api/paciente/citas', [
            'medico_id' => $medicoId,
            'especialidad_id' => $especialidadId,
            'starts_at' => $slot,
        ])->assertCreated();

        $citaId = $created['id'];

        $this->postJson("/api/paciente/citas/{$citaId}/cancelar")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('citas', [
            'id' => $citaId,
            'estado' => 'cancelada',
            'canceled_by_user_id' => $pacienteUser->id,
        ]);
    }

    public function test_paciente_puede_reprogramar_cita_validando_solapamientos(): void
    {
        [$medicoUser, $medicoId] = $this->crearMedico();
        $pacienteUser = $this->crearPaciente();
        $especialidadId = $this->crearEspecialidad();
        $this->crearHorario($medicoId);

        $slotInicial = Carbon::create(2025, 1, 6, 9, 0, 0)->toISOString();
        $slotOcupado = Carbon::create(2025, 1, 6, 9, 30, 0)->toISOString();
        $slotLibre = Carbon::create(2025, 1, 6, 10, 30, 0)->toISOString();

        Sanctum::actingAs($pacienteUser, ['*']);

        $cita = $this->postJson('/api/paciente/citas', [
            'medico_id' => $medicoId,
            'especialidad_id' => $especialidadId,
            'starts_at' => $slotInicial,
        ])->assertCreated();

        $citaId = $cita['id'];

        $otroPaciente = $this->crearPaciente();

        Sanctum::actingAs($otroPaciente, ['*']);

        $this->postJson('/api/paciente/citas', [
            'medico_id' => $medicoId,
            'especialidad_id' => $especialidadId,
            'starts_at' => $slotOcupado,
        ])->assertCreated();

        Sanctum::actingAs($pacienteUser, ['*']);

        $this->putJson("/api/paciente/citas/{$citaId}", [
            'starts_at' => $slotOcupado,
            'medico_id' => $medicoId,
            'especialidad_id' => $especialidadId,
        ])->assertStatus(422)
            ->assertJson(['message' => 'Horario ocupado']);

        $this->putJson("/api/paciente/citas/{$citaId}", [
            'starts_at' => $slotLibre,
            'medico_id' => $medicoId,
            'especialidad_id' => $especialidadId,
        ])->assertOk()
            ->assertJsonPath('estado', 'pendiente')
            ->assertJsonPath('starts_at', Carbon::parse($slotLibre)->toIso8601String());

        $this->assertDatabaseHas('citas', [
            'id' => $citaId,
            'starts_at' => Carbon::parse($slotLibre)->toDateTimeString(),
            'estado' => 'pendiente',
        ]);
    }
}