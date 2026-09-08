<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'paciente']);
        Role::create(['name' => 'medico']);
    }

    public function test_patient_registration_persists_full_profile(): void
    {
        $payload = [
            'full_name' => 'Laura Fernandez',
            'email' => 'laura@example.com',
            'phone' => '+51 900 000 000',
            'password' => 'secret123',
            'role' => 'paciente',
            'doc_tipo' => 'DNI',
            'doc_numero' => '12345678',
            'birthdate' => '1994-05-18',
            'gender' => 'femenino',
        ];

        $response = $this->postJson('/api/auth/register/paciente', $payload);

        $response->assertCreated()->assertJsonStructure([
            'token',
            'user' => ['id', 'email', 'name', 'phone'],
            'roles',
        ]);

        $user = User::whereEmail('laura@example.com')->firstOrFail();
        $this->assertSame('+51 900 000 000', $user->phone);
        $this->assertTrue($user->roles()->where('name', 'paciente')->exists());

        $this->assertDatabaseHas('pacientes', [
            'user_id' => $user->id,
            'doc_tipo' => 'DNI',
            'doc_numero' => '12345678',
        ]);
    }

    public function test_registration_rejects_unknown_role(): void
    {
        $payload = [
            'full_name' => 'Pepe',
            'email' => 'pepe@example.com',
            'phone' => null,
            'password' => 'secret123',
            'role' => 'admin',
        ];

        $this->postJson('/api/auth/register/paciente', $payload)
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'No pudimos asignar el rol solicitado.',
            ]);
    }

    public function test_medico_registration_stores_license_information(): void
    {
        $payload = [
            'full_name' => 'Dr. Luis Rojas',
            'email' => 'luis@example.com',
            'phone' => '+51 955 444 333',
            'password' => 'secret123',
            'role' => 'medico',
            'id_doc_tipo' => 'DNI',
            'id_doc_numero' => '99999999',
            'lic_tipo' => 'CMP',
            'lic_numero' => '54321',
            'lic_pais' => 'PE',
        ];

        $response = $this->postJson('/api/auth/register/medico', $payload);

        $response->assertCreated()->assertJsonStructure([
            'token',
            'user' => ['id', 'email', 'name', 'phone'],
            'roles',
        ]);

        $user = User::whereEmail('luis@example.com')->firstOrFail();
        $this->assertTrue($user->roles()->where('name', 'medico')->exists());

        $this->assertDatabaseHas('medicos', [
            'user_id' => $user->id,
            'id_doc_numero' => '99999999',
            'lic_numero' => '54321',
        ]);
    }

    public function test_login_returns_structured_error(): void
    {
        $user = User::factory()->create([
            'email' => 'valid@example.com',
            'password' => Hash::make('secret123'),
        ]);
        $user->roles()->attach(Role::where('name', 'paciente')->first()->id);

        $this->postJson('/api/auth/login', [
            'email' => 'valid@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['email'],
                'hints' => ['reset'],
            ]);
    }
}