<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests para PresupuestoController
 */
class PresupuestoTest extends TestCase
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

    public function test_index_devuelve_lista_de_presupuestos(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([
                (object) [
                    'CONTROL'   => 'PRE2025001',
                    'NUMREF'    => 'PRE-0001',
                    'CODIGO'    => 'CLI001',
                    'NOMBRE'    => 'Cliente Test',
                    'FECEMIS'   => '2025-01-15',
                    'MONTOBRU'  => 100.00,
                    'MONTOIMP'  => 7.00,
                    'MONTODES'  => 0.00,
                    'MONTOTOT'  => 107.00,
                ],
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/presupuestos');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.NUMREF', 'PRE-0001');
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function test_show_devuelve_404_si_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $id = base64_encode('NO-EXISTE');
        $response = $this->withToken($this->token)->getJson("/api/v1/presupuestos/{$id}");

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'no encontrado'));
    }

    public function test_show_devuelve_presupuesto_con_detalles(): void
    {
        $maestro = (object) [
            'CONTROL'  => 'PRE2025001',
            'NUMREF'   => 'PRE-0001',
            'NOMBRE'   => 'Cliente Test',
            'MONTOTOT' => 107.00,
        ];

        DB::shouldReceive('selectOne')->once()->withAnyArgs()->andReturn($maestro);
        DB::shouldReceive('select')->once()->withAnyArgs()->andReturn([]);

        $id = base64_encode('PRE2025001');
        $response = $this->withToken($this->token)->getJson("/api/v1/presupuestos/{$id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['maestro', 'detalles']]);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function test_store_requiere_campos_obligatorios(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/presupuestos', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['codcliente', 'items']);
    }

    public function test_store_falla_si_cliente_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)->postJson('/api/v1/presupuestos', [
            'codcliente' => 'NOEXISTE',
            'items'      => [['codpro' => 'P001', 'descrip' => 'Test', 'cantidad' => 1, 'precio' => 100, 'imppor' => 7]],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'no encontrado'));
    }

    public function test_store_requiere_items_validos(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/presupuestos', [
            'codcliente' => 'CLI001',
            'items'      => [['codpro' => 'P001']],  // Faltan campos
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.descrip', 'items.0.cantidad', 'items.0.precio', 'items.0.imppor']);
    }

    public function test_store_falla_si_imppor_invalido(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['NOMBRE' => 'Cliente Test', 'TIPOCLI' => '01']);

        $response = $this->withToken($this->token)->postJson('/api/v1/presupuestos', [
            'codcliente' => 'CLI001',
            'items'      => [['codpro' => 'P001', 'descrip' => 'Test', 'cantidad' => 1, 'precio' => 100, 'imppor' => 99]],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.imppor']);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_elimina_presupuesto(): void
    {
        DB::shouldReceive('statement')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        $id = base64_encode('PRE2025001');
        $response = $this->withToken($this->token)->deleteJson("/api/v1/presupuestos/{$id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'eliminado'));
    }

    // ─── Convertir a Factura ──────────────────────────────────────────────────

    public function test_convertir_requiere_tipo_factura_y_formas_pago(): void
    {
        $id = base64_encode('PRE2025001');
        $response = $this->withToken($this->token)->postJson("/api/v1/presupuestos/{$id}/convertir-a-factura", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tipoFactura', 'formasPago']);
    }

    public function test_convertir_falla_si_tipo_factura_invalido(): void
    {
        $id = base64_encode('PRE2025001');
        $response = $this->withToken($this->token)->postJson("/api/v1/presupuestos/{$id}/convertir-a-factura", [
            'tipoFactura' => 'INVALIDO',
            'formasPago'  => [['instrumento' => 'EFE', 'monto' => 100]],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tipoFactura']);
    }

    public function test_convertir_devuelve_404_si_presupuesto_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $id = base64_encode('NOEXISTE');
        $response = $this->withToken($this->token)->postJson("/api/v1/presupuestos/{$id}/convertir-a-factura", [
            'tipoFactura' => 'CONTADO',
            'formasPago'  => [['instrumento' => 'EFE', 'monto' => 100]],
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'no encontrado'));
    }
}
