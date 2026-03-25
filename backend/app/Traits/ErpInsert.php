<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * ErpInsert — helpers para INSERT en tablas transaccionales del ERP Clarion.
 *
 * Todas las columnas siguen exactamente el formato de grabar_presupuesto.php
 * y grabar_factura.php para garantizar compatibilidad con el motor Clarion.
 *
 * IMPORTANTE: TRANSACCDETALLES y TRANSACCPAGOS NO tienen columna INTEGRADO.
 */
trait ErpInsert
{
    /**
     * Inserta una fila en TRANSACCDETALLES con el esquema completo del ERP.
     *
     * @param string $control      CONTROL del maestro (TRANSACCMAESTRO)
     * @param string $fechora      Clave única de este detalle (FECHORA / FHPRODBASE)
     * @param string $codpro       Código de producto
     * @param string $descrip      Descripción del producto
     * @param float  $cantidad     Cantidad
     * @param float  $precio       Precio unitario (PRECOSUNI)
     * @param float  $descuento    Descuento por ítem (monto)
     * @param float  $imppor       Porcentaje de impuesto (7, 10, 15, 0)
     * @param float  $montoimp     Monto de impuesto calculado
     * @param float  $total        Subtotal de línea (base − descuento ítem)
     * @param string $tiptran      'FAC' | 'PRE' | 'PEDxCLI'
     * @param int    $fecemis      Fecha en formato integer YYYYMMDD
     * @param string $fecemiss     Fecha en string YYYYMMDD
     * @param string $codigo       Código de cliente
     * @param string $codalmacen   Código de almacén (CODALENT)
     * @param string $codven       Código de vendedor
     * @param float  $pordes       Porcentaje de descuento por ítem
     * @param float  $pordesglo    Porcentaje de descuento global
     * @param float  $montodescglo Monto de descuento global para esta línea
     */
    protected function insertDetalle(
        string $control,
        string $fechora,
        string $codpro,
        string $descrip,
        float  $cantidad,
        float  $precio,
        float  $descuento,
        float  $imppor,
        float  $montoimp,
        float  $total,
        string $tiptran,
        int    $fecemis,
        string $fecemiss,
        string $codigo,
        string $codalmacen,
        string $codven      = '',
        float  $pordes      = 0.0,
        float  $pordesglo   = 0.0,
        float  $montodescglo = 0.0,
    ): void {
        DB::statement(
            "INSERT INTO TRANSACCDETALLES (
                CONTROL,CODPRO,CANTIDAD,PRECOSUNI,COSTOACT,COSTOPRO,
                IMPPOR,MONTOIMP,TOTAL,DESCRIP1,TIPTRAN,FECEMIS,FECEMISS,
                PRECIO1,UTILPRECIO1,PRECIO2,UTILPRECIO2,PRECIO3,UTILPRECIO3,
                MONTOCOS,TIPINV,FACCAM1,VALFOB1,COSTOFLE1,COSTOSEG1,VALORCIF1,
                COSTOARA1,COSTONAC1,COSTOADU1,PAGOCOM1,GASTOADU1,OTROGAS1,COSTOFIN1,
                FACCAM2,VALFOB2,COSTOFLE2,COSTOSEG2,VALORCIF2,COSTOARA2,COSTONAC2,
                COSTOADU2,PAGOCOM2,GASTOADU2,OTROGAS2,COSTOFIN2,
                PRECIOE1,PRECIOE2,PRECIOE3,TIPODET,TIPPRO,TIPREG,CODIGO,
                COMISVEN,COMISCOB,COMISTIP,CODALENT,FECHORA,PORDES,MONTODESCUENTO,
                COMPONENTE,FHPRODBASE,PORCOMISION,MONTOCOMISION,DEVUELTA,CANTIDADDEV,
                ORIGEN,PRECIO,FACTORCAMBIO,IMPPOR2,MONTOIMP2,IMPPOR3,MONTOIMP3,
                PROCESADO,CODIGODEP,GRUPOINV,CONLINEA,CODVEN,
                PORRETTAR,MONTORETTAR,MESVENC,FECHAVENCE,LINEAOINV,NOMBRE,
                PORCOMISDETAIL,PORDESGLO,MONTODESCUENTOGLO,CANTIDADFAC,
                PORREC,MONTORECARGA,PORRECARGOGLO,MONTORECARGOGLO,
                CONSIGA,PARCONTROL,CANTIDADEMP,PRECOSUNIEMP
            ) VALUES (
                :ctrl,:codpro,:cant,:prec,0,0,
                :imppor,:montoimp,:total,:descrip,:tiptran,:fecemis,:fecemiss,
                0,0,0,0,0,0,
                0,0,0,0,0,0,0,0,0,0,0,0,0,0,
                0,0,0,0,0,0,0,0,0,0,0,0,
                0,0,0,0,0,1,:codigo,
                0,0,0,:codalmacen,:fechora,:pordes,:descuento,
                0,:fechora,0,0,0,0,
                0,3,0,0,0,0,0,
                0,'','',0,:codven,
                0,0,0,0,'','',
                0,:pordesglo,:montodescglo,0,
                0,0,0,0,
                0,1,0,0
            )",
            [
                'ctrl'        => $control,
                'codpro'      => $codpro,
                'cant'        => $cantidad,
                'prec'        => $precio,
                'imppor'      => $imppor,
                'montoimp'    => round($montoimp, 2),
                'total'       => round($total, 2),
                'descrip'     => $descrip,
                'tiptran'     => $tiptran,
                'fecemis'     => $fecemis,
                'fecemiss'    => $fecemiss,
                'codigo'      => $codigo,
                'codalmacen'  => $codalmacen,
                'fechora'     => $fechora,
                'pordes'      => round($pordes, 4),
                'descuento'   => round($descuento, 2),
                'codven'      => $codven,
                'pordesglo'   => round($pordesglo, 4),
                'montodescglo'=> round($montodescglo, 2),
            ]
        );
    }

    /**
     * Inserta una fila en TRANSACCPAGOS con el esquema completo del ERP.
     *
     * @param string $control     CONTROL del maestro
     * @param string $controlPago Clave única de este pago
     * @param string $codtar      Código del instrumento de pago (BASEINSTRUMENTOS.CODTAR)
     * @param float  $montopag    Monto pagado
     * @param int    $fecemis     Fecha en formato integer YYYYMMDD
     * @param int    $funcion     Tipo de instrumento (0=tarjeta,1=cheque,2=transf,3=dep,6=efectivo)
     */
    protected function insertPago(
        string $control,
        string $controlPago,
        string $codtar,
        float  $montopag,
        int    $fecemis,
        int    $funcion = 6,
    ): void {
        DB::statement(
            "INSERT INTO TRANSACCPAGOS (
                CONTROL,CONTROLPAGO,CODTAR,MONTOPAG,FECEMIS,FUNCION,
                EXPRESADOEN,PORRET,PORIMP,MONTOPAGREF,DESDEMODULO,PORIGTF,TASABC,IGTF
            ) VALUES (
                :ctrl,:ctrlpago,:codtar,:monto,:fecemis,:funcion,
                0,0,0,0,'RETAILSPOS',0,0,0
            )",
            [
                'ctrl'     => $control,
                'ctrlpago' => $controlPago,
                'codtar'   => $codtar,
                'monto'    => round($montopag, 2),
                'fecemis'  => $fecemis,
                'funcion'  => $funcion,
            ]
        );
    }

    /**
     * Busca el código FUNCION de un instrumento de pago.
     * Devuelve 6 (efectivo) si no se encuentra.
     */
    protected function getFuncionInstrumento(string $codtar): int
    {
        $inst = DB::selectOne(
            "SELECT FUNCION FROM BASEINSTRUMENTOS WHERE CODTAR = ?", [$codtar]
        );
        return (int) ($inst?->FUNCION ?? 6);
    }

    /**
     * Obtiene VALVENDEDOR y VALDEPOSITO del usuario autenticado.
     *
     * @return array{codven: string, codalmacen: string}
     */
    protected function getErpUserData(string $coduser): array
    {
        $erpUser = DB::selectOne(
            "SELECT ISNULL(VALVENDEDOR,'') AS VALVENDEDOR,
                    ISNULL(VALDEPOSITO,'') AS VALDEPOSITO
             FROM BASEUSUARIOS WHERE CODUSER = ?",
            [$coduser]
        );
        return [
            'codven'     => trim($erpUser?->VALVENDEDOR ?? ''),
            'codalmacen' => trim($erpUser?->VALDEPOSITO ?? ''),
        ];
    }
}
