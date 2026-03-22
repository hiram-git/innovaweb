<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests para InventarioController
 */
class InventarioTest extends TestCase
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

    // ─── Index ────────────────────────────────────────────────────────────────

    public function test_index_devuelve_lista_de_productos(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([
                (object) [
                    'CODPRO'       => 'PROD001',
                    'DESCRIP1'     => 'Laptop HP 15"',
                    'EXISTENCIA'   => 10.0,
                    'CANRESERVADA' => 2.0,
                    'PRECVEN1'     => 599.99,
                    'IMPPOR'       => 7,
                    'PROCOMPUESTO' => 0,
                    'TIPINV'       => 'M',
                    'UNIDAD'       => 'UN',
                ],
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/inventario');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.CODPRO', 'PROD001')
            ->assertJsonPath('data.0.DESCRIP1', 'Laptop HP 15"');
    }

    public function test_index_acepta_filtro_de_busqueda(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([]);

        $response = $this->withToken($this->token)->getJson('/api/v1/inventario?q=laptop');

        $response->assertStatus(200)->assertJsonPath('data', []);
    }

    public function test_index_requiere_autenticacion(): void
    {
        $this->getJson('/api/v1/inventario')->assertStatus(401);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function test_show_devuelve_producto_existente(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) [
                'CODPRO'       => 'PROD001',
                'DESCRIP1'     => 'Laptop HP 15"',
                'EXISTENCIA'   => 10.0,
                'CANRESERVADA' => 2.0,
                'PRECVEN1'     => 599.99,
                'IMPPOR'       => 7,
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/inventario/PROD001');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonPath('data.CODPRO', 'PROD001');
    }

    public function test_show_devuelve_404_si_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)->getJson('/api/v1/inventario/NOEXISTE');

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'no encontrado'));
    }

    // ─── Disponibilidad ───────────────────────────────────────────────────────

    public function test_disponibilidad_calcula_stock_neto(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) [
                'CODPRO'       => 'PROD001',
                'EXISTENCIA'   => 10.0,
                'CANRESERVADA' => 3.0,
            ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/inventario/PROD001/disponibilidad');

        $response->assertStatus(200)
            ->assertJsonPath('data.disponible', 7.0);
    }

    public function test_disponibilidad_devuelve_404_si_producto_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/inventario/NOEXISTE/disponibilidad');

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'no encontrado'));
    }

    public function test_disponibilidad_es_cero_cuando_todo_reservado(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) [
                'CODPRO'       => 'PROD001',
                'EXISTENCIA'   => 5.0,
                'CANRESERVADA' => 5.0,
            ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/inventario/PROD001/disponibilidad');

        $response->assertStatus(200)
            ->assertJsonPath('data.disponible', 0.0);
    }
}
