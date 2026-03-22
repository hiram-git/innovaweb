<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests para ConfiguracionController
 */
class ConfiguracionTest extends TestCase
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

    // ─── Empresa ──────────────────────────────────────────────────────────────

    public function test_get_empresa_devuelve_404_si_no_configurada(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)->getJson('/api/v1/configuracion/empresa');

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'no encontrada'));
    }

    public function test_get_empresa_devuelve_datos(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) [
                'RAZONSOCIAL' => 'Mi Empresa S.A.',
                'RUC'         => '8-888-8888',
                'DV'          => '01',
                'DIRECCION'   => 'Calle Principal 123',
                'TEL'         => '507-000-0000',
                'EMAIL'       => 'empresa@test.com',
                'LOGO_URL'    => null,
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/configuracion/empresa');

        $response->assertStatus(200)
            ->assertJsonPath('RAZONSOCIAL', 'Mi Empresa S.A.')
            ->assertJsonPath('RUC', '8-888-8888');
    }

    public function test_update_empresa_requiere_campos_obligatorios(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/v1/configuracion/empresa', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['RAZONSOCIAL', 'RUC', 'DV']);
    }

    public function test_update_empresa_valida_email(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/v1/configuracion/empresa', [
            'RAZONSOCIAL' => 'Mi Empresa S.A.',
            'RUC'         => '8-888-8888',
            'DV'          => '01',
            'EMAIL'       => 'no-es-un-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['EMAIL']);
    }

    public function test_update_empresa_guarda_correctamente(): void
    {
        DB::shouldReceive('statement')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        $response = $this->withToken($this->token)->putJson('/api/v1/configuracion/empresa', [
            'RAZONSOCIAL' => 'Mi Empresa S.A.',
            'RUC'         => '8-888-8888',
            'DV'          => '01',
            'EMAIL'       => 'empresa@test.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'actualizados'));
    }

    // ─── FE / DGI ─────────────────────────────────────────────────────────────

    public function test_get_fe_devuelve_defaults_si_no_configurada(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->withToken($this->token)->getJson('/api/v1/configuracion/fe');

        $response->assertStatus(200)
            ->assertJson([
                'ambiente'   => 'sandbox',
                'pac'        => 'TFHKA',
                'ruc_emisor' => '',
                'dv_emisor'  => '',
            ]);
    }

    public function test_get_fe_devuelve_configuracion_existente(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) [
                'ambiente'   => 'produccion',
                'pac'        => 'DIGIFACT',
                'ruc_emisor' => '8-888-8888',
                'dv_emisor'  => '01',
            ]);

        $response = $this->withToken($this->token)->getJson('/api/v1/configuracion/fe');

        $response->assertStatus(200)
            ->assertJsonPath('ambiente', 'produccion')
            ->assertJsonPath('pac', 'DIGIFACT');
    }

    public function test_update_fe_requiere_campos_obligatorios(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/v1/configuracion/fe', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ambiente', 'pac', 'ruc_emisor', 'dv_emisor']);
    }

    public function test_update_fe_valida_ambiente(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/v1/configuracion/fe', [
            'ambiente'   => 'desarrollo',
            'pac'        => 'TFHKA',
            'ruc_emisor' => '8-888-8888',
            'dv_emisor'  => '01',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ambiente']);
    }

    public function test_update_fe_guarda_correctamente(): void
    {
        DB::shouldReceive('statement')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        $response = $this->withToken($this->token)->putJson('/api/v1/configuracion/fe', [
            'ambiente'   => 'produccion',
            'pac'        => 'TFHKA',
            'ruc_emisor' => '8-888-8888',
            'dv_emisor'  => '01',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'actualizada'));
    }

    // ─── Auth ─────────────────────────────────────────────────────────────────

    public function test_rutas_requieren_autenticacion(): void
    {
        $this->getJson('/api/v1/configuracion/empresa')->assertStatus(401);
        $this->putJson('/api/v1/configuracion/empresa', [])->assertStatus(401);
        $this->getJson('/api/v1/configuracion/fe')->assertStatus(401);
        $this->putJson('/api/v1/configuracion/fe', [])->assertStatus(401);
    }
}
