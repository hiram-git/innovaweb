<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests para AuthController
 *
 * Mockeamos DB::selectOne para las consultas al ERP Clarion (BASEUSUARIOS)
 * ya que en tests usamos SQLite en memoria, sin las tablas del ERP.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Simula un usuario en BASEUSUARIOS con contraseña plain-text legacy */
    private function mockErpUser(
        string $coduser   = 'ADMIN',
        string $clave     = 'secret',
        string $claveWeb  = '',
        bool   $vendedor  = false,
        bool   $deposito  = false,
    ): object {
        return (object) [
            'CODUSER'     => $coduser,
            'CLAVE'       => $clave,
            'CLAVEWEB'    => $claveWeb,
            'VALVENDEDOR' => (int) $vendedor,
            'VALDEPOSITO' => (int) $deposito,
        ];
    }

    // ─── Login ────────────────────────────────────────────────────────────────

    public function test_login_exitoso_con_clave_plain_text(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->with(\Mockery::pattern('/BASEUSUARIOS/'), ['ADMIN'])
            ->andReturn($this->mockErpUser('ADMIN', 'secret'));

        $response = $this->postJson('/api/v1/login', [
            'usuario'  => 'admin',
            'password' => 'secret',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'expires_at', 'usuario' => ['codigo']]);
    }

    public function test_login_exitoso_con_clave_web_bcrypt(): void
    {
        $hash = bcrypt('newpass');

        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn($this->mockErpUser('JUAN', '', $hash));

        $response = $this->postJson('/api/v1/login', [
            'usuario'  => 'juan',
            'password' => 'newpass',
        ]);

        $response->assertStatus(200)->assertJsonPath('usuario.codigo', 'JUAN');
    }

    public function test_login_falla_con_usuario_inexistente(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $response = $this->postJson('/api/v1/login', [
            'usuario'  => 'noexiste',
            'password' => 'cualquiera',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.usuario.0', fn ($v) => str_contains($v, 'incorrectas'));
    }

    public function test_login_falla_con_password_incorrecta(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn($this->mockErpUser('ADMIN', 'correcta'));

        $response = $this->postJson('/api/v1/login', [
            'usuario'  => 'admin',
            'password' => 'incorrecta',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_valida_campos_requeridos(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['usuario', 'password']);
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function test_logout_revoca_token(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'cerrada'));

        // El token ya no debe funcionar
        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertStatus(401);
    }

    // ─── Me ───────────────────────────────────────────────────────────────────

    public function test_me_devuelve_datos_usuario_autenticado(): void
    {
        $user = User::factory()->create(['erp_coduser' => 'TESTUSER']);
        $token = $user->createToken('test')->plainTextToken;

        DB::shouldReceive('selectOne')
            ->once()
            ->withAnyArgs()
            ->andReturn((object) [
                'CODUSER'     => 'TESTUSER',
                'VALVENDEDOR' => 1,
                'VALDEPOSITO' => 0,
                'VALCONTADOR' => 0,
            ]);

        $response = $this->withToken($token)->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJsonPath('codigo', 'TESTUSER')
            ->assertJsonStructure(['id', 'codigo', 'email', 'roles', 'permisos', 'erp']);
    }

    public function test_rutas_protegidas_requieren_token(): void
    {
        $this->getJson('/api/v1/me')->assertStatus(401);
        $this->getJson('/api/v1/clientes')->assertStatus(401);
        $this->getJson('/api/v1/facturas')->assertStatus(401);
    }

    // ─── Rate limiting ────────────────────────────────────────────────────────

    public function test_ping_responde_correctamente(): void
    {
        $response = $this->getJson('/api/v1/ping');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'app', 'version', 'time'])
            ->assertJsonPath('status', 'ok');
    }
}
