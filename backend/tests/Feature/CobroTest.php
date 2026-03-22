<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests para CobroController
 */
class CobroTest extends TestCase
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

    public function test_index_devuelve_facturas_credito_pendientes(): void
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
                    'CONTROLMAESTRO' => 'FAC2025001',
                    'NROFAC'         => 'FAC-0001',
                    'CODCLIENTE'     => 'CLI001',
                    'NOMCLIENTE'     => 'Cliente Crédito S.A.',
                    'FECHA'          => '2025-01-01',
                    'MONTOTOT'       => 500.00,
                    'MONTOSAL'       => 250.00,
                    'FECVENCS'       => '2025-02-01',
                    'DIASVEN'        => 30,
                ],
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/cobros');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.NROFAC', 'FAC-0001');
    }

    public function test_index_acepta_filtro_de_busqueda(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['total' => 0]);

        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([]);

        $response = $this->withToken($this->token)->getJson('/api/v1/cobros?q=cliente');

        $response->assertStatus(200)->assertJsonPath('data', []);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function test_show_devuelve_404_si_factura_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $id = base64_encode('NO-EXISTE');
        $response = $this->withToken($this->token)->getJson("/api/v1/cobros/{$id}");

        $response->assertStatus(404);
    }

    public function test_show_devuelve_factura_con_cobros(): void
    {
        $factura = (object) [
            'CONTROLMAESTRO' => 'FAC2025001',
            'NROFAC'         => 'FAC-0001',
            'NOMCLIENTE'     => 'Empresa ABC',
            'MONTOTOT'       => 500.00,
            'MONTOSAL'       => 250.00,
        ];

        DB::shouldReceive('selectOne')->once()->withAnyArgs()->andReturn($factura);
        DB::shouldReceive('select')->once()->withAnyArgs()->andReturn([]);

        $id = base64_encode('FAC2025001');
        $response = $this->withToken($this->token)->getJson("/api/v1/cobros/{$id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['factura', 'cobros']]);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function test_store_requiere_campos_obligatorios(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/cobros', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['controlmaestro', 'instrumento', 'monto']);
    }

    public function test_store_requiere_monto_positivo(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/cobros', [
            'controlmaestro' => 'FAC2025001',
            'instrumento'    => 'EFE',
            'monto'          => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['monto']);
    }

    public function test_store_devuelve_404_si_factura_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)->postJson('/api/v1/cobros', [
            'controlmaestro' => 'NOEXISTE',
            'instrumento'    => 'EFE',
            'monto'          => 100,
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'no encontrada') || str_contains($v, 'saldada'));
    }

    public function test_store_falla_si_monto_supera_saldo(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) [
                'CONTROL'   => 'FAC2025001',
                'NUMREF'    => 'FAC-0001',
                'MONTOTOT'  => 500.00,
                'MONTOSAL'  => 100.00,
                'CODIGO'    => 'CLI001',
            ]);

        $response = $this->withToken($this->token)->postJson('/api/v1/cobros', [
            'controlmaestro' => 'FAC2025001',
            'instrumento'    => 'EFE',
            'monto'          => 999.99,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'saldo'));
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function test_update_siempre_devuelve_422(): void
    {
        $id = base64_encode('COBRO001');
        $response = $this->withToken($this->token)->putJson("/api/v1/cobros/{$id}", []);

        $response->assertStatus(422);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_devuelve_404_si_cobro_no_existe(): void
    {
        DB::shouldReceive('beginTransaction')->once()->withNoArgs();
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);
        DB::shouldReceive('rollBack')->once()->withNoArgs();

        $id = base64_encode('NOEXISTE');
        $response = $this->withToken($this->token)->deleteJson("/api/v1/cobros/{$id}");

        $response->assertStatus(404);
    }
}
