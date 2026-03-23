<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
     * La tabla BASEUSUARIOS usa su propio sistema de contraseñas (CLAVE/CLAVEWEB),
     * por lo que se mantiene compatibilidad con el ERP Clarion.
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

        // Verificar contraseña (CLAVEWEB tiene preferencia si está definida)
        $claveValida    = false;
        $necesitaRehash = false;
        $claveWeb       = trim($usuario->CLAVEWEB ?? '');
        $claveErp       = trim($usuario->CLAVE ?? '');
        $passwordIn     = trim($request->password);

        if ($claveWeb !== '') {
            if (Hash::isHashed($claveWeb)) {
                // Ya está en bcrypt — verificación normal
                $claveValida = Hash::check($passwordIn, $claveWeb);
            } else {
                // Clave legacy plain-text: comparar directo y marcar para rehash
                $claveValida    = $passwordIn === $claveWeb;
                $necesitaRehash = $claveValida;
            }
        } elseif ($claveErp !== '') {
            // Sólo tiene CLAVE del ERP (sin CLAVEWEB): comparar y crear CLAVEWEB
            $claveValida    = $passwordIn === $claveErp;
            $necesitaRehash = $claveValida;
        }

        if (! $claveValida) {
            throw ValidationException::withMessages([
                'usuario' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Migración gradual: si la clave era plain-text, actualizar a bcrypt en BASEUSUARIOS
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
