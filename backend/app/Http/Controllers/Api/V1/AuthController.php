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
            "SELECT CODUSER, CLAVE, CLAVEWEB,
                    ISNULL(VALVENDEDOR,'')   AS VALVENDEDOR,
                    ISNULL(VALDEPOSITO,'')   AS VALDEPOSITO,
                    ISNULL(ACTPRECIO,0)      AS ACTPRECIO,
                    ISNULL(VALPRECIO,0)      AS VALPRECIO,
                    ISNULL(CREACLIENTE,0)    AS CREACLIENTE,
                    ISNULL(ACTCLIENTE,0)     AS ACTCLIENTE,
                    ISNULL(VALCLIENTE,'')    AS VALCLIENTE,
                    ISNULL(ACTDESCTOPAR,0)   AS ACTDESCTOPAR,
                    ISNULL(ACTDESCTOGLOBAL,0) AS ACTDESCTOGLOBAL,
                    ISNULL(CAMBIARPRECIO,0)  AS CAMBIARPRECIO,
                    ISNULL(VENTAMENOS,0)     AS VENTAMENOS,
                    ISNULL(ACTFACEXI,0)      AS ACTFACEXI,
                    ISNULL(ACTDEPOSITO,0)    AS ACTDEPOSITO,
                    ISNULL(VALDIASVENC,0)    AS VALDIASVENC,
                    ISNULL(CLIRAPMTOCREDI,0) AS CLIRAPMTOCREDI
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

        // Permisos de módulos desde BASEUSUARIOSEXT
        $ext = DB::selectOne(
            "SELECT ISNULL(VEN_PRESUPUESTO,0) AS VEN_PRESUPUESTO,
                    ISNULL(VEN_PEDIDOS,0)     AS VEN_PEDIDOS,
                    ISNULL(VEN_VENTAS,0)      AS VEN_VENTAS,
                    ISNULL(ADM_CXC,0)         AS ADM_CXC
             FROM BASEUSUARIOSEXT WHERE UPPER(CODUSER) = ?",
            [$usuario->CODUSER]
        );

        // Precio mode (mirror de grabar_login.php)
        $actPrecio  = (int)($usuario->ACTPRECIO ?? 0);
        $valPrecio  = (int)($usuario->VALPRECIO ?? 0);
        if ($actPrecio === 0 && $valPrecio === 0) {
            $precioMode = 'libre';
        } elseif ($actPrecio === 0 && $valPrecio > 0) {
            $precioMode = $valPrecio;          // número de lista, ej. 1 → PRECIO1
        } else {
            $precioMode = 'no_definido';       // usar precio del cliente en BCP
        }

        return response()->json([
            'token'      => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
            'usuario'    => [
                'codigo'       => $usuario->CODUSER,
                'codvendedor'  => trim($usuario->VALVENDEDOR ?? ''),
                'codalmacen'   => trim($usuario->VALDEPOSITO ?? ''),
                'permisos'     => [
                    // módulos (de BASEUSUARIOSEXT)
                    'ver_factura'     => (int)($ext?->VEN_VENTAS       ?? 1),
                    'ver_presupuesto' => (int)($ext?->VEN_PRESUPUESTO  ?? 1),
                    'ver_pedido'      => (int)($ext?->VEN_PEDIDOS       ?? 1),
                    'ver_cobro'       => (int)($ext?->ADM_CXC           ?? 1),
                    // inventario / venta
                    'ventamenos'      => (int)($usuario->VENTAMENOS      ?? 0),
                    'actfacexi'       => (int)($usuario->ACTFACEXI       ?? 0),
                    // descuentos
                    'desctopar'       => (int)($usuario->ACTDESCTOPAR   ?? 0),
                    'desctoglo'       => (int)($usuario->ACTDESCTOGLOBAL ?? 0),
                    // precio
                    'cambiarprecio'   => (int)($usuario->CAMBIARPRECIO  ?? 0),
                    'precio_mode'     => $precioMode,
                    // clientes
                    'creacliente'     => (int)($usuario->CREACLIENTE    ?? 0),
                    'actcliente'      => (int)($usuario->ACTCLIENTE     ?? 0),
                    // otros
                    'valdiasvenc'     => (int)($usuario->VALDIASVENC    ?? 0),
                ],
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
