<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\FE\FEOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

/**
 * Feature tests para FEController
 */
class FETest extends TestCase
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

    // ─── Stats ────────────────────────────────────────────────────────────────

    public function test_stats_devuelve_conteos_por_estado(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) [
                'pendientes' => 5,
                'enviados'   => 3,
                'aceptados'  => 10,
                'rechazados' => 1,
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/facturacion-electronica/stats');

        $response->assertStatus(200)
            ->assertJson([
                'pendientes' => 5,
                'enviados'   => 3,
                'aceptados'  => 10,
                'rechazados' => 1,
            ]);
    }

    public function test_stats_devuelve_ceros_si_no_hay_datos(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)->getJson('/api/v1/facturacion-electronica/stats');

        $response->assertStatus(200)
            ->assertJson([
                'pendientes' => 0,
                'enviados'   => 0,
                'aceptados'  => 0,
                'rechazados' => 0,
            ]);
    }

    // ─── Documentos ──────────────────────────────────────────────────────────

    public function test_documentos_devuelve_lista(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([
                (object) [
                    'CONTROLMAESTRO' => 'FAC2025001',
                    'NROFAC'         => 'FAC-0001',
                    'NOMCLIENTE'     => 'Empresa Test',
                    'FECHA'          => '2025-01-10',
                    'MONTOTOT'       => 107.00,
                    'CUFE'           => 'cufe-abc123',
                    'FE_ESTADO'      => 'ACEPTADO',
                    'FE_MENSAJE'     => null,
                ],
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/facturacion-electronica/documentos');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(1, 'data');
    }

    public function test_documentos_acepta_filtro_de_estado(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn([]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/facturacion-electronica/documentos?estado=RECHAZADO');

        $response->assertStatus(200)->assertJsonPath('data', []);
    }

    // ─── Consultar Estado ─────────────────────────────────────────────────────

    public function test_consultar_estado_devuelve_404_si_cufe_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/facturacion-electronica/estado/cufe-inexistente');

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'no encontrado'));
    }

    public function test_consultar_estado_devuelve_documento(): void
    {
        $doc = (object) [
            'CODIGO'                     => 'FAC-0001',
            'RESULTADO'                  => 'ACEPTADO',
            'MENSAJE'                    => 'OK',
            'CUFE'                       => 'cufe-abc123',
            'QR'                         => 'data:image/png;base64,...',
            'FECHARECEPCIONDGI'          => '2025-01-10',
            'NROPROTOCOLOAYTORIZACION'   => 'PROT-0001',
            'FECHALIMITE'                => '2025-01-11',
            'NUMDOCFISCAL'               => '001-001-01-10-0001',
        ];

        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn($doc);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/facturacion-electronica/estado/cufe-abc123');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonPath('data.RESULTADO', 'ACEPTADO');
    }

    // ─── Descargar PDF ────────────────────────────────────────────────────────

    public function test_descargar_pdf_devuelve_404_si_no_existe(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/facturacion-electronica/pdf/cufe-sin-pdf');

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'PDF') || str_contains($v, 'disponible'));
    }

    // ─── getConfig ────────────────────────────────────────────────────────────

    public function test_get_config_devuelve_404_si_no_configurado(): void
    {
        // BASEUSUARIOS lookup (erp_coduser del user)
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) ['id_control' => 1]);

        // FELINNOVA lookup
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/facturacion-electronica/config');

        $response->assertStatus(404);
    }

    // ─── Enviar ───────────────────────────────────────────────────────────────

    public function test_enviar_devuelve_error_si_orquestador_falla(): void
    {
        $mock = Mockery::mock(FEOrchestrator::class);
        $mock->shouldReceive('enviarFactura')
            ->once()
            ->withAnyArgs()
            ->andThrow(new \RuntimeException('PAC no disponible'));

        $this->app->instance(FEOrchestrator::class, $mock);

        $id = base64_encode('FAC2025001');
        $response = $this->withToken($this->token)
            ->postJson("/api/v1/facturacion-electronica/enviar/{$id}");

        $response->assertStatus(500)
            ->assertJsonPath('estado', 0)
            ->assertJsonPath('mensaje', fn ($v) => str_contains($v, 'PAC no disponible'));
    }
}
