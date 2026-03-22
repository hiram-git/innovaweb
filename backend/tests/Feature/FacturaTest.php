<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests para FacturaController
 *
 * Mockeamos DB para las tablas del ERP (TRANSACCMAESTRO, etc.)
 * ya que el entorno de tests usa SQLite en memoria.
 */
class FacturaTest extends TestCase
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

    public function test_index_devuelve_lista_de_facturas(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([
                (object) [
                    'CONTROL'         => 'CTRL001',
                    'NUMREF'          => 'FAC-0001',
                    'CODIGO'          => 'CLI001',
                    'NOMBRE'          => 'Cliente Test S.A.',
                    'FECEMIS'         => '2025-01-15',
                    'TIPTRAN'         => 'FAC',
                    'TIPOFACTURA'     => 'CONTADO',
                    'MONTOBRU'        => 100.00,
                    'MONTOIMP'        => 7.00,
                    'MONTODES'        => 0.00,
                    'MONTOTOT'        => 107.00,
                    'MONTOSAL'        => 0.00,
                    'DIASVEN'         => 0,
                    'FECVENCS'        => null,
                    'COM_FISCAL'      => null,
                    'URLCONSULTAFEL'  => null,
                    'CUFE'            => null,
                    'QR'              => null,
                    'FE_RESULTADO'    => null,
                ],
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/facturas');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.NUMREF', 'FAC-0001');
    }

    public function test_index_acepta_filtro_de_busqueda(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/facturas?q=cliente');

        $response->assertStatus(200)->assertJsonPath('data', []);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function test_show_devuelve_404_si_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $id = base64_encode('CTRL-NOEXISTE');
        $response = $this->withToken($this->token)->getJson("/api/v1/facturas/{$id}");

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'no encontrada'));
    }

    public function test_show_devuelve_factura_con_detalles_y_pagos(): void
    {
        $maestro = (object) [
            'CONTROL'     => 'FAC2025001',
            'NUMREF'      => 'FAC-0001',
            'NOMBRE'      => 'Empresa ABC',
            'MONTOTOT'    => 107.00,
            'CUFE'        => null,
            'QR'          => null,
            'FE_RESULTADO' => null,
            'PDF'         => null,
        ];

        DB::shouldReceive('selectOne')->once()->withAnyArgs()->andReturn($maestro);
        DB::shouldReceive('select')->twice()->withAnyArgs()->andReturn([]);

        $id = base64_encode('FAC2025001');
        $response = $this->withToken($this->token)->getJson("/api/v1/facturas/{$id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['maestro', 'detalles', 'pagos']]);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function test_store_requiere_campos_obligatorios(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/facturas', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['codcliente', 'tipoFactura', 'items']);
    }

    public function test_store_falla_si_tipoFactura_es_invalido(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/facturas', [
            'codcliente'  => 'CLI001',
            'tipoFactura' => 'INVALIDO',
            'formasPago'  => [['instrumento' => 'EFE', 'monto' => 100]],
            'items'       => [['codpro' => 'P001', 'descrip' => 'X', 'cantidad' => 1, 'precio' => 100, 'imppor' => 7]],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['tipoFactura']);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_impide_eliminar_factura_con_cufe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) [
                'CONTROL'     => 'FAC001',
                'INTEGRADO'   => 0,
                'CUFE'        => 'cufe-dgi-1234567890abcdef',
                'MONTOSAL'    => 0,
            ]);

        $id = base64_encode('FAC001');
        $response = $this->withToken($this->token)->deleteJson("/api/v1/facturas/{$id}");

        $response->assertStatus(422)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'CUFE') || str_contains($v, 'DGI') || str_contains($v, 'crédito'));
    }

    public function test_destroy_devuelve_404_si_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $id = base64_encode('NOEXISTE');
        $response = $this->withToken($this->token)->deleteJson("/api/v1/facturas/{$id}");

        $response->assertStatus(404);
    }
}
