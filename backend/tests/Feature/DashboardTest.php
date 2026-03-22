<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests para DashboardController
 */
class DashboardTest extends TestCase
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

    public function test_stats_requiere_autenticacion(): void
    {
        $this->getJson('/api/v1/dashboard/stats')->assertStatus(401);
    }

    public function test_stats_devuelve_estructura_correcta(): void
    {
        // 4 consultas selectOne (kpis) + 1 select (ultimas_facturas)
        DB::shouldReceive('selectOne')
            ->times(4)
            ->withAnyArgs()
            ->andReturn((object) ['total' => 0]);

        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([]);

        $response = $this->withToken($this->token)->getJson('/api/v1/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'kpis' => ['facturas_hoy', 'total_cobrar', 'fe_aceptadas_mes', 'clientes_activos'],
                'ultimas_facturas',
            ]);
    }

    public function test_stats_devuelve_kpis_reales(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['total' => 5]);    // facturas_hoy

        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['total' => 1250.75]); // total_cobrar

        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['total' => 12]);   // fe_aceptadas_mes

        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['total' => 38]);   // clientes_activos

        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([]);

        $response = $this->withToken($this->token)->getJson('/api/v1/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonPath('kpis.facturas_hoy',     5)
            ->assertJsonPath('kpis.total_cobrar',     1250.75)
            ->assertJsonPath('kpis.fe_aceptadas_mes', 12)
            ->assertJsonPath('kpis.clientes_activos', 38);
    }

    public function test_stats_devuelve_ultimas_facturas(): void
    {
        DB::shouldReceive('selectOne')
            ->times(4)
            ->withAnyArgs()
            ->andReturn((object) ['total' => 0]);

        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([
                (object) [
                    'CONTROLMAESTRO' => 'FAC2025001',
                    'NROFAC'         => 'FAC-0001',
                    'NOMCLIENTE'     => 'Empresa Test',
                    'FECHA'          => '2025-03-20',
                    'MONTOTOT'       => 107.00,
                    'MONTOSAL'       => 0.00,
                    'TIPTRAN'        => 'CONTADO',
                    'FE_ESTADO'      => 'ACEPTADO',
                    'CUFE'           => 'cufe-abc123',
                ],
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'ultimas_facturas')
            ->assertJsonPath('ultimas_facturas.0.NROFAC', 'FAC-0001')
            ->assertJsonPath('ultimas_facturas.0.FE_ESTADO', 'ACEPTADO');
    }

    public function test_stats_devuelve_ceros_cuando_no_hay_datos(): void
    {
        DB::shouldReceive('selectOne')
            ->times(4)
            ->withAnyArgs()
            ->andReturn(null);

        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([]);

        $response = $this->withToken($this->token)->getJson('/api/v1/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonPath('kpis.facturas_hoy',     0)
            ->assertJsonPath('kpis.total_cobrar',     0.0)
            ->assertJsonPath('kpis.fe_aceptadas_mes', 0)
            ->assertJsonPath('kpis.clientes_activos', 0)
            ->assertJsonPath('ultimas_facturas',      []);
    }
}
