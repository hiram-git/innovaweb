<?php 

class DocumentoElectronico

{

  public $codigoSucursalEmisor  = "0000";

  public $tipoSucursal = "1";

  public $datosTransaccion;

  public $listaItems;

  public $totalesSubTotales;

}

class Cliente

{

  public $tipoClienteFE = "01";

  public $tipoContribuyente = "2";

  public $numeroRUC = "155596713-2-2015";

  public $digitoVerificadorRUC = "59";

  public $razonSocial = "FE general";

  public $direccion = "Av. Balboa";

  public $codigoUbicacion = "1-2-3";

  public $corregimiento = "Guabito";

  public $distrito = "Changuinola";

  public $provincia = "Bocas del Toro";

  public $telefono1 = "997-8243";

  public $telefono2 = "";

  public $telefono3 = "";

  public $correoElectronico1 = "fep@gmail.com";

  public $pais = "PA";

  public $paisOtro = "";

}

class DatosTransaccion

{

  public $tipoEmision = "01";

  public $tipoDocumento = "01";

  public $numeroDocumentoFiscal;

  public $puntoFacturacionFiscal = "001";

  public $fechaEmision;

  public $naturalezaOperacion = "01";

  public $tipoOperacion = "1";

  public $destinoOperacion = "1";

  public $formatoCAFE = "1";

  public $entregaCAFE = "1";

  public $envioContenedor = "1";

  public $procesoGeneracion = "1";

  public $tipoVenta = "1";

  public $informacionInteres = "Prueba de Información de interés";

  public $cliente;

}

class Item

{

  public $descripcion = "Cuadernos";

  public $codigo = "T";

  public $unidadMedida = "und";

  public $cantidad = "2.00";

  public $fechaFabricacion = "2020-12-25";

  public $unidadMedidaCPBS = "cm";

  public $precioUnitario = "69.00";

  public $precioUnitarioDescuento = "0.00";

  public $precioAcarreo = "1.01";

  public $precioSeguro = "12.01";

  public $precioItem = "138.00";

  public $valorTotal = "171.72";

  public $codigoGTIN = "0";

  public $cantGTINCom = "0.99";

  public $codigoGTINInv = "0";

  public $cantGTINComInv = "1.00";

  public $tasaITBMS = "03";

  public $valorITBMS = "20.70";

  public $tasaISC = "0.00";

  public $valorISC = "0.00";

  public $codigoCPBS = "1410";

}

class Totales

{

  public $totalPrecioNeto = "138.00";

  public $totalITBMS = "20.70";

  public $totalISC = "0.00";

  public $totalMontoGravado = "20.70";

  public $totalDescuento = "";

  public $totalAcarreoCobrado = "";

  public $valorSeguroCobrado = "";

  public $totalFactura = "171.72";

  public $totalValorRecibido = "171.72";

  public $vuelto = "0.00";

  public $tiempoPago = "1";

  public $nroItems = "1";

  public $totalTodosItems = "171.72";

  public $listaFormaPago;

}

class FormaPago

{

  public $formaPagoFact = "02";

  public $valorCuotaPagada = "171.72";

  public $descFormaPago = "";

}