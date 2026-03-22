<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\FE\FEOrchestrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FEController extends Controller
{
    public function __construct(
        private readonly FEOrchestrator $orchestrator
    ) {}

    /**
     * Obtener configuración de Facturación Electrónica
     */
    public function getConfig(Request $request): JsonResponse
    {
        $parcontrol = $request->user()->erp_coduser
            ? DB::selectOne("SELECT id_control FROM BASEUSUARIOS WHERE CODUSER = ?", [
                $request->user()->erp_coduser
            ])?->id_control ?? 1
            : 1;

        $config = DB::selectOne(
            "SELECT
                A.CODSUC, A.CODPFACT, A.NEMPRESA_DIGI,
                A.TEL_DIGI, A.RUC_DIGI, A.DV_DIGI,
                A.JURIDICO_DIGI, A.DIRECCION_DIGI,
                A.UBUCACION_DIGI, A.COORDENADAS_DIGI,
                A.EMAIL_DIGI, A.FACELECT, A.TIPOEMISION,
                A.TIPOSUCURSAL, A.NATURALEZAOPERACION,
                A.TIPOOPERACION, A.FORMATOCAFE, A.ENTREGACAFE,
                A.DIRECCIONENVIO,
                B.NROINIFAC
             FROM FELINNOVA A
             LEFT JOIN BASEEMPRESA B ON B.CONTROL = A.PARCONTROL
             WHERE A.PARCONTROL = ?",
            [(int) $parcontrol]
        );

        if (! $config) {
            return response()->json(['message' => 'Configuración FE no encontrada.'], 404);
        }

        // Nunca devolver credenciales (USUARIO_RUC, CONTRASEÑA)
        return response()->json(['data' => $config]);
    }

    /**
     * Actualizar configuración de Facturación Electrónica
     */
    public function updateConfig(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codsuc'              => ['required', 'string', 'max:10'],
            'codpfact'            => ['required', 'string', 'max:10'],
            'facelect'            => ['required', 'boolean'],
            'tipoemision'         => ['required', 'string', 'max:5'],
            'tiposucursal'        => ['required', 'string', 'max:5'],
            'naturalezaoperacion' => ['required', 'string', 'max:5'],
            'tipooperacion'       => ['required', 'string', 'max:5'],
            'formatocafe'         => ['required', 'string', 'max:5'],
            'entregacafe'         => ['required', 'string', 'max:5'],
            'direccionenvio'      => ['required', 'url', 'max:255'],
            'usuario_ruc'         => ['required', 'string', 'max:100'],
            'password_ruc'        => ['required', 'string', 'max:100'],
            'nempresa_digi'       => ['nullable', 'string', 'max:100'],
            'ruc_digi'            => ['nullable', 'string', 'max:20'],
            'dv_digi'             => ['nullable', 'string', 'max:5'],
            'email_digi'          => ['nullable', 'email', 'max:100'],
        ]);

        DB::statement(
            "MERGE INTO FELINNOVA AS target
             USING (SELECT 1 AS PARCONTROL) AS source ON (target.PARCONTROL = source.PARCONTROL)
             WHEN MATCHED THEN
                UPDATE SET
                    CODSUC = :codsuc, CODPFACT = :codpfact,
                    FACELECT = :facelect, TIPOEMISION = :tipoemision,
                    TIPOSUCURSAL = :tiposucursal,
                    NATURALEZAOPERACION = :naturalezaoperacion,
                    TIPOOPERACION = :tipooperacion,
                    FORMATOCAFE = :formatocafe, ENTREGACAFE = :entregacafe,
                    DIRECCIONENVIO = :direccionenvio,
                    USUARIO_RUC = :usuario_ruc, CONTRASEÑA = :password_ruc,
                    NEMPRESA_DIGI = :nempresa_digi, RUC_DIGI = :ruc_digi,
                    DV_DIGI = :dv_digi, EMAIL_DIGI = :email_digi
             WHEN NOT MATCHED THEN
                INSERT (PARCONTROL, CODSUC, CODPFACT, FACELECT, TIPOEMISION,
                        TIPOSUCURSAL, NATURALEZAOPERACION, TIPOOPERACION,
                        FORMATOCAFE, ENTREGACAFE, DIRECCIONENVIO,
                        USUARIO_RUC, CONTRASEÑA, NEMPRESA_DIGI, RUC_DIGI,
                        DV_DIGI, EMAIL_DIGI)
                VALUES (1, :codsuc2, :codpfact2, :facelect2, :tipoemision2,
                        :tiposucursal2, :naturalezaoperacion2, :tipooperacion2,
                        :formatocafe2, :entregacafe2, :direccionenvio2,
                        :usuario_ruc2, :password_ruc2, :nempresa_digi2,
                        :ruc_digi2, :dv_digi2, :email_digi2);",
            array_merge(
                $this->bindFEConfig($data),
                $this->bindFEConfig($data, '2')
            )
        );

        return response()->json(['message' => 'Configuración FE actualizada exitosamente.']);
    }

    /**
     * Enviar factura a la DGI via el PAC configurado
     */
    public function enviar(Request $request, string $facturaId): JsonResponse
    {
        try {
            $control = base64_decode($facturaId);
            $resultado = $this->orchestrator->enviarFactura($control);

            return response()->json([
                'estado'  => $resultado['estado'],
                'mensaje' => $resultado['mensaje'],
                'cufe'    => $resultado['cufe'] ?? null,
                'qr'      => $resultado['qr'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'estado'  => 0,
                'mensaje' => 'Error al enviar la factura electrónica: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Consultar estado de un documento FE por CUFE
     */
    public function consultarEstado(string $cufe): JsonResponse
    {
        $documento = DB::selectOne(
            "SELECT CODIGO, RESULTADO, MENSAJE, CUFE, QR,
                    FECHARECEPCIONDGI, NROPROTOCOLOAYTORIZACION,
                    FECHALIMITE, NUMDOCFISCAL
             FROM Documentos
             WHERE CUFE = ?",
            [$cufe]
        );

        if (! $documento) {
            return response()->json(['message' => 'Documento no encontrado.'], 404);
        }

        return response()->json(['data' => $documento]);
    }

    /**
     * Descargar PDF del documento FE
     */
    public function descargarPDF(string $cufe): JsonResponse
    {
        $documento = DB::selectOne(
            "SELECT PDF, NUMDOCFISCAL FROM Documentos WHERE CUFE = ?",
            [$cufe]
        );

        if (! $documento || ! $documento->PDF) {
            return response()->json(['message' => 'PDF no disponible.'], 404);
        }

        return response()->json([
            'pdf'            => $documento->PDF,        // base64
            'numdocfiscal'   => $documento->NUMDOCFISCAL,
        ]);
    }

    /**
     * Reenviar factura (por si falló el primer intento)
     */
    public function reenviar(Request $request, string $facturaId): JsonResponse
    {
        return $this->enviar($request, $facturaId);
    }

    /**
     * Nota de Crédito
     */
    public function notaCredito(Request $request, string $facturaId): JsonResponse
    {
        $control = base64_decode($facturaId);
        $resultado = $this->orchestrator->enviarNotaCredito($control);

        return response()->json($resultado);
    }

    /**
     * Nota de Débito
     */
    public function notaDebito(Request $request, string $facturaId): JsonResponse
    {
        $control = base64_decode($facturaId);
        $resultado = $this->orchestrator->enviarNotaDebito($control);

        return response()->json($resultado);
    }

    // ─── Stats y documentos (usados por FEPage del frontend) ─────────────────

    /**
     * Estadísticas de FE: conteo por estado
     */
    public function stats(): JsonResponse
    {
        $row = DB::selectOne(
            "SELECT
                SUM(CASE WHEN d.RESULTADO IS NULL OR d.RESULTADO = '' THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN d.RESULTADO = 'ENVIADO'   THEN 1 ELSE 0 END) AS enviados,
                SUM(CASE WHEN d.RESULTADO = 'ACEPTADO'  THEN 1 ELSE 0 END) AS aceptados,
                SUM(CASE WHEN d.RESULTADO = 'RECHAZADO' THEN 1 ELSE 0 END) AS rechazados
             FROM TRANSACCMAESTRO m
             LEFT JOIN Documentos d ON d.CONTROL = m.CONTROL
             WHERE m.TIPTRAN = 'FAC' AND m.INTEGRADO = 0
               AND m.FECEMIS >= DATEADD(month, -3, GETDATE())"
        );

        return response()->json([
            'pendientes' => (int) ($row->pendientes ?? 0),
            'enviados'   => (int) ($row->enviados   ?? 0),
            'aceptados'  => (int) ($row->aceptados  ?? 0),
            'rechazados' => (int) ($row->rechazados ?? 0),
        ]);
    }

    /**
     * Listado de documentos FE con filtro de estado
     */
    public function documentos(Request $request): JsonResponse
    {
        $estado  = $request->query('estado', '');
        $perPage = min((int) $request->query('per_page', 50), 200);
        $page    = max(1, (int) $request->query('page', 1));
        $offset  = ($page - 1) * $perPage;

        $where  = ["m.TIPTRAN = 'FAC'", "m.INTEGRADO = 0"];
        $params = [];

        if ($estado === '') {
            // Sin filtro: mostrar todos (incluye los sin Documentos)
        } elseif ($estado === 'PENDIENTE') {
            $where[] = "(d.RESULTADO IS NULL OR d.RESULTADO = '' OR d.RESULTADO = 'PENDIENTE')";
        } else {
            $where[] = "d.RESULTADO = :estado";
            $params['estado'] = $estado;
        }

        $whereStr = implode(' AND ', $where);
        $params['limit']  = $perPage;
        $params['offset'] = $offset;

        $docs = DB::select(
            "SELECT m.CONTROL AS CONTROLMAESTRO, m.NUMREF AS NROFAC,
                m.NOMBRE AS NOMCLIENTE, m.FECEMIS AS FECHA, m.MONTOTOT,
                d.CUFE, d.RESULTADO AS FE_ESTADO, d.MENSAJE AS FE_MENSAJE
             FROM TRANSACCMAESTRO m
             LEFT JOIN Documentos d ON d.CONTROL = m.CONTROL
             WHERE {$whereStr}
             ORDER BY m.FECEMIS DESC
             OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY", $params
        );

        return response()->json(['data' => $docs]);
    }

    private function bindFEConfig(array $data, string $suffix = ''): array
    {
        return [
            "codsuc{$suffix}"              => $data['codsuc'],
            "codpfact{$suffix}"            => $data['codpfact'],
            "facelect{$suffix}"            => $data['facelect'] ? '1' : '0',
            "tipoemision{$suffix}"         => $data['tipoemision'],
            "tiposucursal{$suffix}"        => $data['tiposucursal'],
            "naturalezaoperacion{$suffix}" => $data['naturalezaoperacion'],
            "tipooperacion{$suffix}"       => $data['tipooperacion'],
            "formatocafe{$suffix}"         => $data['formatocafe'],
            "entregacafe{$suffix}"         => $data['entregacafe'],
            "direccionenvio{$suffix}"      => $data['direccionenvio'],
            "usuario_ruc{$suffix}"         => $data['usuario_ruc'],
            "password_ruc{$suffix}"        => $data['password_ruc'],
            "nempresa_digi{$suffix}"       => $data['nempresa_digi'] ?? null,
            "ruc_digi{$suffix}"            => $data['ruc_digi'] ?? null,
            "dv_digi{$suffix}"             => $data['dv_digi'] ?? null,
            "email_digi{$suffix}"          => $data['email_digi'] ?? null,
        ];
    }
}
