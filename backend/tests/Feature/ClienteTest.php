<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests para ClienteController
 */
class ClienteTest extends TestCase
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

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function clienteObj(array $overrides = []): object
    {
        return (object) array_merge([
            'CODIGO'       => 'CLI001',
            'NOMBRE'       => 'Empresa Demo S.A.',
            'RIF'          => '8-123-456',
            'NIT'          => '01',
            'TIPOCLI'      => 'Contribuyente',
            'TIPOCOMERCIO' => 1,
            'DIRECC1'      => 'Calle 50 local 3',
            'NUMTEL'       => '507-123-4567',
            'DIRCORREO'    => 'demo@empresa.pa',
            'DIASCRE'      => 30,
            'CONESPECIAL'  => 0,
            'PORRETIMP'    => 0,
            'provincia'     => 'Panamá',
            'distrito'      => 'Panamá',
            'corregimiento' => 'Bella Vista',
        ], $overrides);
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    public function test_index_devuelve_lista_paginada(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['total' => 2]);

        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([
                $this->clienteObj(),
                $this->clienteObj(['CODIGO' => 'CLI002', 'NOMBRE' => 'Otro Cliente']),
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/clientes');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta' => ['total', 'per_page', 'current_page', 'last_page']])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_acepta_busqueda_por_nombre(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['total' => 1]);

        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([$this->clienteObj()]);

        $response = $this->withToken($this->token)->getJson('/api/v1/clientes?q=Empresa');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_index_requiere_autenticacion(): void
    {
        $this->getJson('/api/v1/clientes')->assertStatus(401);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function test_show_devuelve_cliente_existente(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn($this->clienteObj());

        $response = $this->withToken($this->token)->getJson('/api/v1/clientes/CLI001');

        $response->assertStatus(200)
            ->assertJsonPath('data.CODIGO', 'CLI001')
            ->assertJsonPath('data.NOMBRE', 'Empresa Demo S.A.')
            ->assertJsonPath('data.NUMTEL', '507-123-4567')
            ->assertJsonPath('data.provincia', 'Panamá');
    }

    public function test_show_devuelve_404_si_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)->getJson('/api/v1/clientes/NOEXISTE');

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'no encontrado'));
    }

    // ─── BuscarPorRuc ─────────────────────────────────────────────────────────

    public function test_buscar_por_ruc_devuelve_cliente(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn($this->clienteObj(['RIF' => '8-123-456']));

        $response = $this->withToken($this->token)->getJson('/api/v1/clientes/buscar/ruc/8-123-456');

        $response->assertStatus(200)
            ->assertJsonPath('data.RIF', '8-123-456')
            ->assertJsonPath('data.CODIGO', 'CLI001');
    }

    public function test_buscar_por_ruc_devuelve_404_si_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)->getJson('/api/v1/clientes/buscar/ruc/0-000-000');

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'RUC'));
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function test_store_requiere_campos_obligatorios(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/clientes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['codigo', 'nombre', 'tipocli']);
    }

    public function test_store_valida_formato_email(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/clientes', [
            'codigo'    => 'CLI001',
            'nombre'    => 'Test',
            'tipocli'   => '01',
            'dircorreo' => 'no-es-un-email',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['dircorreo']);
    }

    public function test_store_crea_cliente_exitosamente(): void
    {
        DB::shouldReceive('statement')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        $response = $this->withToken($this->token)->postJson('/api/v1/clientes', [
            'codigo'    => 'CLI999',
            'nombre'    => 'Cliente Nuevo S.A.',
            'tipocli'   => 'Contribuyente',
            'rif'       => '9-999-9999',
            'nit'       => '01',
            'dircorreo' => 'nuevo@cliente.pa',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'creado'))
            ->assertJsonPath('codigo', 'CLI999');
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function test_update_actualiza_cliente(): void
    {
        DB::shouldReceive('statement')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        $response = $this->withToken($this->token)->putJson('/api/v1/clientes/CLI001', [
            'nombre'    => 'Empresa Actualizada S.A.',
            'dircorreo' => 'actualizado@empresa.pa',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'actualizado'));
    }

    public function test_update_devuelve_422_si_no_hay_campos(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/v1/clientes/CLI001', []);

        $response->assertStatus(422);
    }

    public function test_update_valida_email_en_actualizacion(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/v1/clientes/CLI001', [
            'dircorreo' => 'no-es-email',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['dircorreo']);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_elimina_cliente_soft(): void
    {
        DB::shouldReceive('statement')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        $response = $this->withToken($this->token)->deleteJson('/api/v1/clientes/CLI001');

        $response->assertStatus(200)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'eliminado'));
    }
}
