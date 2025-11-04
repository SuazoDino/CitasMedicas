<?php

namespace Tests\Feature;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_paciente_can_opt_in_to_sms_and_push(): void
    {
        $user = User::factory()->create(['phone' => '+51999111222']);

        DB::table('pacientes')->insert([
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/paciente/notificaciones/preferencias', [
            'sms_opt_in' => true,
            'sms_number' => '+51999111222',
            'push_opt_in' => true,
            'push_token' => 'device-token-123',
            'push_platform' => 'ios',
            'push_metadata' => ['app_version' => '1.0.0'],
        ]);

        $response->assertOk()->assertJsonFragment([
            'sms_opt_in' => true,
            'push_opt_in' => true,
            'push_platform' => 'ios',
        ]);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'sms_opt_in' => true,
            'push_opt_in' => true,
            'push_token' => 'device-token-123',
        ]);

        $response = $this->patchJson('/api/paciente/notificaciones/preferencias', [
            'sms_opt_in' => false,
            'push_opt_in' => false,
        ]);

        $response->assertOk()->assertJsonFragment([
            'sms_opt_in' => false,
            'push_opt_in' => false,
        ]);

        $this->assertFalse(NotificationPreference::where('user_id', $user->id)->value('sms_opt_in'));
    }

    public function test_medico_preferences_require_membership(): void
    {
        $user = User::factory()->create(['phone' => '+34123123123']);

        DB::table('medicos')->insert([
            'user_id' => $user->id,
            'id_doc_tipo' => 'DNI',
            'id_doc_numero' => '12345678',
            'lic_tipo' => 'CMP',
            'lic_numero' => '87654321',
            'lic_pais' => 'PE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/medico/notificaciones/preferencias', [
            'sms_opt_in' => true,
            'sms_number' => '+34123123123',
        ]);

        $response->assertOk()->assertJsonFragment([
            'sms_opt_in' => true,
        ]);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'sms_opt_in' => true,
        ]);
    }
}