<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests para InstrumentosController
 */
class InstrumentosTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    public function test_index_devuelve_instrumentos(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([
                (object) ['CODINSTRUMENTO' => 'EFE',  'DESCRINSTRUMENTO' => 'Efectivo',       'FUNCION' => 6],
                (object) ['CODINSTRUMENTO' => 'VISA', 'DESCRINSTRUMENTO' => 'Tarjeta Visa',   'FUNCION' => 0],
                (object) ['CODINSTRUMENTO' => 'BANC', 'DESCRINSTRUMENTO' => 'Transferencia',  'FUNCION' => 2],
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/instrumentos');

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure(['*' => ['CODINSTRUMENTO', 'DESCRINSTRUMENTO', 'FUNCION']]);
    }

    public function test_index_devuelve_lista_vacia_si_no_hay_instrumentos(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([]);

        $response = $this->withToken($this->token)->getJson('/api/v1/instrumentos');

        $response->assertStatus(200)->assertJson([]);
    }

    public function test_index_requiere_autenticacion(): void
    {
        $response = $this->getJson('/api/v1/instrumentos');

        $response->assertStatus(401);
    }
}
