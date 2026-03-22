<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests para OrdenTrabajoController
 */
class OrdenTrabajoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = User::factory()->create(['erp_coduser' => 'TESTVENDEDOR']);
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    public function test_index_devuelve_lista_paginada(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['total' => 1]);

        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([
                (object) [
                    'CONTROLOT'    => 'OT2025001',
                    'CODCLIENTE'   => 'CLI001',
                    'NOMCLIENTE'   => 'Empresa Test',
                    'ATENDIDO'     => 'Juan Técnico',
                    'FECHAOT'      => '2025-01-10',
                    'FECHA_ENTREGA'=> '2025-01-20',
                    'ESTADO'       => 0,
                    'DESCRIPCION'  => 'Mantenimiento equipo',
                    'CONTROLPRES'  => null,
                ],
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/ordenes-trabajo');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.CONTROLOT', 'OT2025001');
    }

    public function test_index_filtra_por_estado(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['total' => 0]);

        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([]);

        $response = $this->withToken($this->token)->getJson('/api/v1/ordenes-trabajo?estado=1');

        $response->assertStatus(200)->assertJsonPath('data', []);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function test_show_devuelve_404_si_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)->getJson('/api/v1/ordenes-trabajo/NOEXISTE');

        $response->assertStatus(404);
    }

    public function test_show_devuelve_ot_con_detalles(): void
    {
        $ot = (object) [
            'CONTROLOT'   => 'OT2025001',
            'CODCLIENTE'  => 'CLI001',
            'NOMCLIENTE'  => 'Empresa Test',
            'ESTADO'      => 0,
            'DESCRIPCION' => 'Mantenimiento',
        ];

        DB::shouldReceive('selectOne')->once()->withAnyArgs()->andReturn($ot);
        DB::shouldReceive('select')->once()->withAnyArgs()->andReturn([]);

        $response = $this->withToken($this->token)->getJson('/api/v1/ordenes-trabajo/OT2025001');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['ot', 'detalles']]);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function test_store_requiere_campos_obligatorios(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/ordenes-trabajo', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['codcliente', 'atendido', 'fecha_entrega', 'descripcion']);
    }

    public function test_store_falla_si_fecha_entrega_invalida(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/ordenes-trabajo', [
            'codcliente'    => 'CLI001',
            'atendido'      => 'Técnico',
            'fecha_entrega' => 'no-es-una-fecha',
            'descripcion'   => 'Trabajo de prueba',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fecha_entrega']);
    }

    public function test_store_falla_si_cliente_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)->postJson('/api/v1/ordenes-trabajo', [
            'codcliente'    => 'NOEXISTE',
            'atendido'      => 'Juan',
            'fecha_entrega' => '2025-12-31',
            'descripcion'   => 'Trabajo de prueba',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'no encontrado'));
    }

    public function test_store_crea_ot_exitosamente(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['NOMBRE' => 'Empresa Test S.A.']);

        DB::shouldReceive('statement')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        $response = $this->withToken($this->token)->postJson('/api/v1/ordenes-trabajo', [
            'codcliente'    => 'CLI001',
            'atendido'      => 'Juan Técnico',
            'fecha_entrega' => '2025-12-31',
            'descripcion'   => 'Mantenimiento preventivo de equipo.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'creada'))
            ->assertJsonStructure(['data' => ['CONTROLOT', 'NOMCLIENTE']]);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function test_update_cierra_ot_con_estado(): void
    {
        DB::shouldReceive('statement')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        $response = $this->withToken($this->token)->patchJson('/api/v1/ordenes-trabajo/OT2025001', [
            'estado' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'actualizada'));
    }

    public function test_update_devuelve_422_si_nada_que_actualizar(): void
    {
        $response = $this->withToken($this->token)->patchJson('/api/v1/ordenes-trabajo/OT2025001', []);

        $response->assertStatus(422);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_anula_ot(): void
    {
        DB::shouldReceive('statement')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        $response = $this->withToken($this->token)->deleteJson('/api/v1/ordenes-trabajo/OT2025001');

        $response->assertStatus(200)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'anulada'));
    }
}
