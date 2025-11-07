<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterPacienteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::query()->create([
            'name' => 'paciente',
            'display_name' => 'Paciente',
            'description' => 'Paciente',
        ]);
    }

    public function test_register_with_slash_birthdate(): void
    {
        $response = $this->postJson('/api/auth/register/paciente', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'birthdate' => '24/8/2006',
            'gender' => 'masculino',
        ]);

        $response->assertStatus(201);
    }
}