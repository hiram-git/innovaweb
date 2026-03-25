<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ConfiguracionController
 *
 * Gestiona la configuración de la empresa y de Facturación Electrónica DGI.
 * Lee/escribe en BASEEMPRESA y FELINNOVA del ERP Clarion.
 */
class ConfiguracionController extends Controller
{
    // ─── FE / DGI ─────────────────────────────────────────────────────────────

    public function getFE(): JsonResponse
    {
        $this->ensureFelinnova();

        $row = DB::selectOne(
            "SELECT
                A.FACELECT,
                A.TIPO_FACTURA,
                A.AMBIENTE,
                A.PAC,
                A.USUARIO_RUC            AS token_empresa,
                A.CODSUC                 AS codigo_sucursal,
                A.CODPFACT               AS punto_facturacion,
                A.DIRECCIONENVIO         AS direccion_envio,
                A.TIPOEMISION            AS tipo_emision,
                A.TIPOSUCURSAL           AS tipo_sucursal,
                A.NATURALEZAOPERACION    AS naturaleza_operacion,
                A.TIPOOPERACION          AS tipo_operacion,
                A.DESTINOOPERACION       AS destino_operacion,
                A.FORMATOCAFE            AS formato_cafe,
                A.ENTREGACAFE            AS entrega_cafe,
                A.USUARIO_DIGI           AS usuario_digi,
                A.RUC_DIGI               AS ruc_digi,
                A.DV_DIGI                AS dv_digi,
                A.NEMPRESA_DIGI          AS nombre_digi,
                A.EMAIL_DIGI             AS email_digi,
                A.TEL_DIGI               AS tel_digi,
                A.DIRECCION_DIGI         AS direccion_digi,
                A.COORDENADAS_DIGI       AS coordenadas_digi,
                A.UBICACION_DIGI         AS ubicacion_digi,
                A.JURIDICO_DIGI          AS juridico_digi,
                A.CODIGOSUCURSALEMISOR   AS codigo_sucursal_digi,
                A.PUNTOFACTURACIONFISCAL AS punto_facturacion_digi,
                B.NROINIFAC              AS num_doc_fiscal
             FROM FELINNOVA A
             LEFT JOIN BASEEMPRESA B ON B.CONTROL = A.PARCONTROL
             WHERE A.PARCONTROL = 1"
        );

        if (! $row) {
            return response()->json($this->feDefaults());
        }

        $data = (array) $row;
        // Never return passwords
        $data['token_password'] = '';
        $data['password_digi']  = '';

        return response()->json($data);
    }

    public function updateFE(Request $request): JsonResponse
    {
        $this->ensureFelinnova();

        $data = $request->validate([
            'facelect'               => ['required', 'boolean'],
            'tipo_factura'           => ['required', 'in:PDF,Ticket'],
            'pac'                    => ['required', 'integer', 'in:1,2,3'],
            'ambiente'               => ['required', 'integer', 'in:1,2'],
            'token_empresa'          => ['nullable', 'string', 'max:640'],
            'token_password'         => ['nullable', 'string', 'max:640'],
            'codigo_sucursal'        => ['nullable', 'string', 'max:640'],
            'punto_facturacion'      => ['nullable', 'string', 'max:640'],
            'direccion_envio'        => ['nullable', 'string', 'max:255'],
            'tipo_emision'           => ['nullable', 'string', 'max:10'],
            'tipo_sucursal'          => ['nullable', 'string', 'max:10'],
            'naturaleza_operacion'   => ['nullable', 'string', 'max:10'],
            'tipo_operacion'         => ['nullable', 'string', 'max:10'],
            'destino_operacion'      => ['nullable', 'string', 'max:10'],
            'formato_cafe'           => ['nullable', 'string', 'max:10'],
            'entrega_cafe'           => ['nullable', 'string', 'max:10'],
            'usuario_digi'           => ['nullable', 'string', 'max:255'],
            'password_digi'          => ['nullable', 'string', 'max:255'],
            'codigo_sucursal_digi'   => ['nullable', 'string', 'max:255'],
            'punto_facturacion_digi' => ['nullable', 'string', 'max:255'],
            'ruc_digi'               => ['nullable', 'string', 'max:20'],
            'dv_digi'                => ['nullable', 'string', 'max:5'],
            'nombre_digi'            => ['nullable', 'string', 'max:640'],
            'email_digi'             => ['nullable', 'email', 'max:640'],
            'tel_digi'               => ['nullable', 'string', 'max:640'],
            'direccion_digi'         => ['nullable', 'string', 'max:640'],
            'coordenadas_digi'       => ['nullable', 'string', 'max:640'],
            'ubicacion_digi'         => ['nullable', 'string', 'max:640'],
            'juridico_digi'          => ['nullable', 'boolean'],
        ]);

        $exists = DB::selectOne("SELECT 1 AS n FROM FELINNOVA WHERE PARCONTROL = 1");

        $params = [
            'facelect'               => $data['facelect'] ? '1' : '0',
            'tipo_factura'           => $data['tipo_factura'],
            'pac'                    => $data['pac'],
            'ambiente'               => $data['ambiente'],
            'usuario_ruc'            => $data['token_empresa'] ?? '',
            'codsuc'                 => $data['codigo_sucursal'] ?? '',
            'codpfact'               => $data['punto_facturacion'] ?? '',
            'direccionenvio'         => $data['direccion_envio'] ?? '',
            'tipoemision'            => $data['tipo_emision'] ?? '01',
            'tiposucursal'           => $data['tipo_sucursal'] ?? '',
            'codigosucursalemisor'   => $data['codigo_sucursal_digi'] ?? '',
            'puntofacturacionfiscal' => $data['punto_facturacion_digi'] ?? '',
            'naturalezaoperacion'    => $data['naturaleza_operacion'] ?? '01',
            'tipooperacion'          => $data['tipo_operacion'] ?? '1',
            'destinooperacion'       => $data['destino_operacion'] ?? '1',
            'formatocafe'            => $data['formato_cafe'] ?? '1',
            'entregacafe'            => $data['entrega_cafe'] ?? '1',
            'usuario_digi'           => $data['usuario_digi'] ?? '',
            'ruc_digi'               => $data['ruc_digi'] ?? '',
            'dv_digi'                => $data['dv_digi'] ?? '',
            'nempresa_digi'          => $data['nombre_digi'] ?? '',
            'email_digi'             => $data['email_digi'] ?? '',
            'tel_digi'               => $data['tel_digi'] ?? '',
            'direccion_digi'         => $data['direccion_digi'] ?? '',
            'coordenadas_digi'       => $data['coordenadas_digi'] ?? '',
            'ubicacion_digi'         => $data['ubicacion_digi'] ?? '',
            'juridico_digi'          => ($data['juridico_digi'] ?? false) ? 1 : 0,
        ];

        if ($exists) {
            $set = [
                'FACELECT = :facelect',
                'TIPO_FACTURA = :tipo_factura',
                'PAC = :pac',
                'AMBIENTE = :ambiente',
                'USUARIO_RUC = :usuario_ruc',
                'CODSUC = :codsuc',
                'CODPFACT = :codpfact',
                'DIRECCIONENVIO = :direccionenvio',
                'TIPOEMISION = :tipoemision',
                'TIPOSUCURSAL = :tiposucursal',
                'CODIGOSUCURSALEMISOR = :codigosucursalemisor',
                'PUNTOFACTURACIONFISCAL = :puntofacturacionfiscal',
                'NATURALEZAOPERACION = :naturalezaoperacion',
                'TIPOOPERACION = :tipooperacion',
                'DESTINOOPERACION = :destinooperacion',
                'FORMATOCAFE = :formatocafe',
                'ENTREGACAFE = :entregacafe',
                'USUARIO_DIGI = :usuario_digi',
                'RUC_DIGI = :ruc_digi',
                'DV_DIGI = :dv_digi',
                'NEMPRESA_DIGI = :nempresa_digi',
                'EMAIL_DIGI = :email_digi',
                'TEL_DIGI = :tel_digi',
                'DIRECCION_DIGI = :direccion_digi',
                'COORDENADAS_DIGI = :coordenadas_digi',
                'UBICACION_DIGI = :ubicacion_digi',
                'JURIDICO_DIGI = :juridico_digi',
            ];

            // Only update passwords when a new value is provided
            if (! empty($data['token_password'])) {
                $set[]                = 'CONTRASENA = :contrasena';
                $params['contrasena'] = $data['token_password'];
            }
            if (! empty($data['password_digi'])) {
                $set[]                   = 'PASSWORD_DIGI = :password_digi';
                $params['password_digi'] = $data['password_digi'];
            }

            DB::statement(
                "UPDATE FELINNOVA SET " . implode(', ', $set) . " WHERE PARCONTROL = 1",
                $params
            );
        } else {
            $params['contrasena']    = $data['token_password'] ?? '';
            $params['password_digi'] = $data['password_digi'] ?? '';

            DB::statement(
                "INSERT INTO FELINNOVA (
                    PARCONTROL, FACELECT, TIPO_FACTURA, PAC, AMBIENTE,
                    USUARIO_RUC, CONTRASENA, CODSUC, CODPFACT,
                    DIRECCIONENVIO, TIPOEMISION, TIPOSUCURSAL,
                    CODIGOSUCURSALEMISOR, PUNTOFACTURACIONFISCAL,
                    NATURALEZAOPERACION, TIPOOPERACION, DESTINOOPERACION,
                    FORMATOCAFE, ENTREGACAFE,
                    USUARIO_DIGI, PASSWORD_DIGI, RUC_DIGI, DV_DIGI,
                    NEMPRESA_DIGI, EMAIL_DIGI, TEL_DIGI,
                    DIRECCION_DIGI, COORDENADAS_DIGI, UBICACION_DIGI, JURIDICO_DIGI
                ) VALUES (
                    1, :facelect, :tipo_factura, :pac, :ambiente,
                    :usuario_ruc, :contrasena, :codsuc, :codpfact,
                    :direccionenvio, :tipoemision, :tiposucursal,
                    :codigosucursalemisor, :puntofacturacionfiscal,
                    :naturalezaoperacion, :tipooperacion, :destinooperacion,
                    :formatocafe, :entregacafe,
                    :usuario_digi, :password_digi, :ruc_digi, :dv_digi,
                    :nempresa_digi, :email_digi, :tel_digi,
                    :direccion_digi, :coordenadas_digi, :ubicacion_digi, :juridico_digi
                )",
                $params
            );
        }

        return response()->json(['message' => 'Configuración FE guardada correctamente.']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Ensure FELINNOVA table exists and all required columns are present.
     * Mirrors the auto-migration logic from ajax/obtenerConfigFacElec.php.
     */
    private function ensureFelinnova(): void
    {
        DB::statement("
            IF NOT EXISTS (
                SELECT * FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_NAME = 'FELINNOVA'
            )
            BEGIN
                CREATE TABLE FELINNOVA (
                    PARCONTROL             int             NOT NULL DEFAULT 1,
                    USUARIO_RUC            varchar(640)    NULL,
                    CONTRASENA             varchar(640)    NULL,
                    CODSUC                 varchar(640)    NULL,
                    CODPFACT               varchar(640)    NULL,
                    NEMPRESA_DIGI          varchar(640)    NULL,
                    TEL_DIGI               varchar(640)    NULL,
                    RUC_DIGI               varchar(640)    NULL,
                    DV_DIGI                varchar(640)    NULL,
                    JURIDICO_DIGI          int             NULL,
                    DIRECCION_DIGI         varchar(640)    NULL,
                    UBICACION_DIGI         varchar(640)    NULL,
                    COORDENADAS_DIGI       varchar(640)    NULL,
                    EMAIL_DIGI             varchar(640)    NULL,
                    FACELECT               varchar(255)    NULL,
                    TIPO_FACTURA           varchar(255)    NULL,
                    DIRECCIONENVIO         varchar(255)    NULL,
                    NUMERODOCUMENTOFISCAL  varchar(255)    NULL,
                    TIPOEMISION            varchar(255)    NULL,
                    TIPOSUCURSAL           varchar(255)    NULL,
                    CODIGOSUCURSALEMISOR   varchar(255)    NULL,
                    PUNTOFACTURACIONFISCAL varchar(255)    NULL,
                    NATURALEZAOPERACION    varchar(255)    NULL,
                    TIPOOPERACION          varchar(255)    NULL,
                    DESTINOOPERACION       varchar(255)    NULL,
                    FORMATOCAFE            varchar(255)    NULL,
                    ENTREGACAFE            varchar(255)    NULL,
                    AMBIENTE               int             NULL,
                    PAC                    int             NULL,
                    USUARIO_DIGI           varchar(255)    NULL,
                    PASSWORD_DIGI          varchar(255)    NULL,
                    TOKEN_DIGI             text            NULL,
                    FEXPIRA_DIGI           datetime        NULL,
                    OTORGADO               varchar(255)    NULL,
                    CONSTRAINT PK_FELINNOVA PRIMARY KEY (PARCONTROL)
                )
            END
        ");

        // Ensure columns added in later legacy migrations exist
        $optionalCols = [
            'AMBIENTE'               => 'int NULL',
            'PAC'                    => 'int NULL',
            'TIPO_FACTURA'           => 'varchar(255) NULL',
            'USUARIO_DIGI'           => 'varchar(255) NULL',
            'PASSWORD_DIGI'          => 'varchar(255) NULL',
            'TOKEN_DIGI'             => 'text NULL',
            'FEXPIRA_DIGI'           => 'datetime NULL',
            'OTORGADO'               => 'varchar(255) NULL',
            'CODIGOSUCURSALEMISOR'   => 'varchar(255) NULL',
            'PUNTOFACTURACIONFISCAL' => 'varchar(255) NULL',
            'NUMERODOCUMENTOFISCAL'  => 'varchar(255) NULL',
            'UBICACION_DIGI'         => 'varchar(640) NULL',
        ];

        foreach ($optionalCols as $col => $type) {
            DB::statement("
                IF NOT EXISTS (
                    SELECT * FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_NAME = 'FELINNOVA' AND COLUMN_NAME = '{$col}'
                )
                BEGIN
                    ALTER TABLE FELINNOVA ADD {$col} {$type}
                END
            ");
        }
    }

    private function feDefaults(): array
    {
        return [
            'FACELECT'               => '0',
            'TIPO_FACTURA'           => 'PDF',
            'AMBIENTE'               => 1,
            'PAC'                    => null,
            'token_empresa'          => '',
            'token_password'         => '',
            'codigo_sucursal'        => '',
            'punto_facturacion'      => '',
            'num_doc_fiscal'         => '',
            'direccion_envio'        => 'https://demoemision.thefactoryhka.com.pa/ws/obj/v1.0/Service.svc?wsdl',
            'tipo_emision'           => '01',
            'tipo_sucursal'          => '',
            'naturaleza_operacion'   => '01',
            'tipo_operacion'         => '1',
            'destino_operacion'      => '1',
            'formato_cafe'           => '1',
            'entrega_cafe'           => '1',
            'usuario_digi'           => '',
            'password_digi'          => '',
            'codigo_sucursal_digi'   => '',
            'punto_facturacion_digi' => '',
            'ruc_digi'               => '',
            'dv_digi'                => '',
            'nombre_digi'            => '',
            'email_digi'             => '',
            'tel_digi'               => '',
            'direccion_digi'         => '',
            'coordenadas_digi'       => '',
            'ubicacion_digi'         => '',
            'juridico_digi'          => 0,
        ];
    }
}
