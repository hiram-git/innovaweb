<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\VersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login — autentica contra la tabla BASEUSUARIOS del ERP
     *
     * Lógica de campos según versión del ERP (BASEEMPRESA.CTAVENIMP):
     *   - versión >= 24 → usa CLAVEWEB (bcrypt). Si está vacía el usuario debe
     *                     establecer una clave nueva antes de ingresar.
     *   - versión <  24 → usa CLAVE (plain-text legado del ERP Clarion).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'usuario'  => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        // Buscar usuario en la tabla del ERP
        $usuario = DB::selectOne(
            "SELECT CODUSER, CLAVE, CLAVEWEB, VALVENDEDOR, VALDEPOSITO
             FROM BASEUSUARIOS
             WHERE CODUSER = ?",
            [strtoupper(trim($request->usuario))]
        );

        if (! $usuario) {
            throw ValidationException::withMessages([
                'usuario' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Determinar versión del ERP para saber qué campo de clave usar
        $empresa = DB::selectOne("SELECT TOP 1 CTAVENIMP FROM BASEEMPRESA");
        $version = VersionService::parse($empresa->CTAVENIMP ?? null);

        $claveValida    = false;
        $necesitaRehash = false;
        $passwordIn     = trim($request->password);

        if ($version !== null && $version >= 24) {
            // ERP moderno: autenticar con CLAVEWEB
            $claveWeb = trim($usuario->CLAVEWEB ?? '');

            if ($claveWeb === '') {
                // Usuario existe pero aún no tiene CLAVEWEB — debe crear su clave
                return response()->json([
                    'code'    => 'password_not_set',
                    'message' => 'Debe establecer una contraseña antes de ingresar.',
                    'usuario' => strtoupper(trim($request->usuario)),
                ], 403);
            }

            if (Hash::isHashed($claveWeb)) {
                $claveValida = Hash::check($passwordIn, $claveWeb);
            } else {
                // CLAVEWEB aún es plain-text: comparar y marcar para rehash
                $claveValida    = $passwordIn === $claveWeb;
                $necesitaRehash = $claveValida;
            }
        } else {
            // ERP legado (versión < 24 o sin versión): autenticar con CLAVE
            $claveErp    = trim($usuario->CLAVE ?? '');
            $claveValida = $passwordIn === $claveErp;

            // Aprovechar para escribir CLAVEWEB en bcrypt (migración preventiva)
            $necesitaRehash = $claveValida;
        }

        if (! $claveValida) {
            throw ValidationException::withMessages([
                'usuario' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Migración gradual: escribir CLAVEWEB con bcrypt si venía en plain-text
        if ($necesitaRehash) {
            DB::statement(
                "UPDATE BASEUSUARIOS SET CLAVEWEB = ? WHERE CODUSER = ?",
                [Hash::make($passwordIn), $usuario->CODUSER]
            );
        }

        // Obtener o crear el User de Laravel correspondiente
        $user = \App\Models\User::firstOrCreate(
            ['erp_coduser' => $usuario->CODUSER],
            [
                'name'     => $usuario->CODUSER,
                'email'    => $usuario->CODUSER.'@innovaweb.local',
                'password' => bcrypt($passwordIn),
            ]
        );

        // Revocar tokens anteriores (una sesión activa por usuario)
        $user->tokens()->delete();

        $token = $user->createToken('api-token', ['*'], now()->addHours(8));

        return response()->json([
            'token'      => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
            'usuario'    => [
                'codigo'       => $usuario->CODUSER,
                'es_vendedor'  => (bool) $usuario->VALVENDEDOR,
                'es_deposito'  => (bool) $usuario->VALDEPOSITO,
            ],
        ]);
    }

    /**
     * Logout — revoca el token actual
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * Me — devuelve los datos del usuario autenticado
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $erpUser = DB::selectOne(
            "SELECT CODUSER, VALVENDEDOR, VALDEPOSITO, VALCONTADOR
             FROM BASEUSUARIOS WHERE CODUSER = ?",
            [$user->erp_coduser]
        );

        return response()->json([
            'id'          => $user->id,
            'codigo'      => $user->erp_coduser,
            'email'       => $user->email,
            'roles'       => $user->getRoleNames(),
            'permisos'    => $user->getPermissionNames(),
            'erp'         => $erpUser,
        ]);
    }
}
