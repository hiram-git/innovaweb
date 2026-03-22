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

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $user        = User::factory()->create();
        $this->token = $user->createToken('test')->plainTextToken;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function clienteObj(array $overrides = []): object
    {
        return (object) array_merge([
            'CODCLIENTE'    => 'CLI001',
            'NOMBRE'        => 'Empresa Demo S.A.',
            'RIF'           => '8-123-456',
            'NIT'           => '',
            'TIPOCLI'       => '01',
            'DIRECC1'       => 'Calle 50',
            'NUMTEL'        => '507-123-4567',
            'DIRCORREO'     => 'demo@empresa.pa',
            'DIASCRE'       => 30,
            'CONESPECIAL'   => 0,
            'PORRETIMP'     => 0,
        ], $overrides);
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    public function test_index_devuelve_lista_paginada(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([$this->clienteObj(), $this->clienteObj(['CODCLIENTE' => 'CLI002', 'NOMBRE' => 'Otro Cliente'])]);

        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['total' => 2]);

        $response = $this->withToken($this->token)->getJson('/api/v1/clientes');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonCount(2, 'data');
    }

    public function test_index_acepta_busqueda_por_nombre(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([$this->clienteObj()]);

        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['total' => 1]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/clientes?q=Empresa');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
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
            ->assertJsonPath('data.CODCLIENTE', 'CLI001')
            ->assertJsonPath('data.NOMBRE', 'Empresa Demo S.A.');
    }

    public function test_show_devuelve_404_si_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)->getJson('/api/v1/clientes/NOEXISTE');

        $response->assertStatus(404);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function test_store_requiere_campos_obligatorios(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/clientes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['codigo', 'nombre', 'tipocli']);
    }

    public function test_store_valida_email_formato(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/clientes', [
            'codigo'    => 'CLI001',
            'nombre'    => 'Test',
            'tipocli'   => '01',
            'dircorreo' => 'no-es-un-email',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['dircorreo']);
    }
}
