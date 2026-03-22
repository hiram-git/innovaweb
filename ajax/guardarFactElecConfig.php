<?php
session_start();
include_once "../config/db.php";
ob_clean();
// Verificar si se recibieron los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Obtener los valores enviados por AJAX
  $FacElect               = $_POST['FacElect'];
  $tipo_factura           = $_POST['tipo_factura'];
  $pac                    = $_POST['pac'];
  $tipo_factura           = $_POST['tipo_factura'];
  $DireccionEnvio         = $_POST['DireccionEnvio'];
  $tipoEmision            = $_POST['tipoEmision'];
  $tipoSucursal           = $_POST['tipoSucursal'];
  $codigoSucursal         = $_POST['codigoSucursal'];
  $numDocFiscal           = $_POST['numDocFiscal'];
  $puntoFacturacionFiscal = $_POST['puntoFacturacionFiscal'];
  $naturalezaOperacion    = $_POST['naturalezaOperacion'];
  $tipoOperacion          = $_POST['tipoOperacion'];
  $destinoOperacion       = $_POST['destinoOperacion'];
  $formatoCAFE            = $_POST['formatoCAFE'];
  $entregaCAFE            = $_POST['entregaCAFE'];
  $tokenEmpresa           = $_POST['tokenEmpresa'];
  $tokenPassword          = $_POST['tokenPassword'];
  $Ambiente               = $_POST['ambientePac'];
  $PAC                    = $_POST['pac'];
  $Parcontrol             = trim($_SESSION['id_control']);
  $USUARIO_DIGI           = $_POST['tokenEmpresaDigifact'];
  $PASSWORD_DIGI          = $_POST['tokenPasswordDigifact'];
  $RUC_DIGI               = $_POST['taxIdDigifact'];
  $DV_DIGI                = $_POST['dvFigifact'];
  $CODIGOSUCURSALEMISOR   = $_POST['codigoSucursalDigifact'];
  $PUNTOFACTURACIONFISCAL = $_POST['puntoFacturacionFiscalDigifact'];

  $NOMBRE      = $_POST['nombreDigifact'];
  $EMAIL       = $_POST['emailFigifact'];
  $TELEFONO    = $_POST['tlfIdDigifact'];
  $DIRECCION   = $_POST['direcdigifact'];
  $COORDENADAS = $_POST['coordDigifact'];
  $UBICACION   = $_POST['ubiDigifact'];
  $ESJURIDICO  = $_POST['esJuridicoDigifact'];

  // Aquí puedes realizar las operaciones de actualización en la base de datos o cualquier otra acción que desees
  // Consulta para verificar si la columna "TIPO_FACTURA" existe en la tabla
  // Verificar si la tabla "FELINNOVA" existe en la base de datos
  $checkTableSQL = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'FELINNOVA'";
  $stmt = $base_de_datos->query($checkTableSQL);
  $result = $stmt->fetch();

  if ($result['count'] === '0') {
      // La tabla "FELINNOVA" no existe, ejecutar el CREATE TABLE para crearla con los campos necesarios
      $createTableSQL = "CREATE TABLE FELINNOVA (
        USUARIO_RUC varchar(640) ,
        CONTRASEÑA varchar(640) ,
        CODSUC varchar(640) ,
        CODPFACT varchar(640) ,
        NEMPRESA_DIGI varchar(640) ,
        TEL_DIGI varchar(640) ,
        RUC_DIGI varchar(640) ,
        DV_DIGI varchar(640) ,
        JURIDICO_DIGI int NULL,
        DIRECCION_DIGI varchar(640) ,
        UBICACION_DIGI varchar(640) ,
        COORDENADAS_DIGI varchar(640) ,
        EMAIL_DIGI varchar(640) ,
        FACELECT varchar(255) ,
        TIPO_FACTURA varchar(255) ,
        DIRECCIONENVIO varchar(255) ,
        NUMERODOCUMENTOFISCAL varchar(255) ,
        TIPOEMISION varchar(255) ,
        TIPOSUCURSAL varchar(255) ,
        CODIGOSUCURSALEMISOR varchar(255) ,
        PUNTOFACTURACIONFISCAL varchar(255) ,
        NATURALEZAOPERACION varchar(255) ,
        TIPOOPERACION varchar(255) ,
        DESTINOOPERACION varchar(255) ,
        FORMATOCAFE varchar(255) ,
        ENTREGACAFE varchar(255) ,
        AMBIENTE int NULL,
        PAC int NULL,
        PARCONTROL int DEFAULT 1 NOT NULL,
        USUARIO_DIGI varchar(255) ,
        PASSWORD_DIGI varchar(255) ,
        TOKEN_DIGI text ,
        FEXPIRA_DIGI datetime ,
        OTORGADO varchar(255) ,
        CONSTRAINT PK_FELINNOVA PRIMARY KEY (PARCONTROL)
      );";
      // Ejecutar el CREATE TABLE
      $base_de_datos->exec($createTableSQL);
      
     // $base_de_datos->exec("ALTER TABLE FELINNOVA ADD CONSTRAINT FELINNOVA_PK PRIMARY KEY (PARCONTROL);");
      echo "Se ha creado la tabla FELINNOVA correctamente.";
  } 

    $query = "
    SELECT COUNT(*) as count
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'FELINNOVA' 
    AND COLUMN_NAME LIKE '%CONTRASE%'
    ";

    $stmt = $base_de_datos->prepare($query);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($result['count'] > 1) {
    $base_de_datos->exec("DROP TABLE FELINNOVA;");
    $createTableSQL = "CREATE TABLE FELINNOVA (
      USUARIO_RUC varchar(640) ,
      CONTRASEÑA varchar(640) ,
      CODSUC varchar(640) ,
      CODPFACT varchar(640) ,
      NEMPRESA_DIGI varchar(640) ,
      TEL_DIGI varchar(640) ,
      RUC_DIGI varchar(640) ,
      DV_DIGI varchar(640) ,
      JURIDICO_DIGI int NULL,
      DIRECCION_DIGI varchar(640) ,
      UBICACION_DIGI varchar(640) ,
      COORDENADAS_DIGI varchar(640) ,
      EMAIL_DIGI varchar(640) ,
      FACELECT varchar(255) ,
      TIPO_FACTURA varchar(255) ,
      DIRECCIONENVIO varchar(255) ,
      NUMERODOCUMENTOFISCAL varchar(255) ,
      TIPOEMISION varchar(255) ,
      TIPOSUCURSAL varchar(255) ,
      CODIGOSUCURSALEMISOR varchar(255) ,
      PUNTOFACTURACIONFISCAL varchar(255) ,
      NATURALEZAOPERACION varchar(255) ,
      TIPOOPERACION varchar(255) ,
      DESTINOOPERACION varchar(255) ,
      FORMATOCAFE varchar(255) ,
      ENTREGACAFE varchar(255) ,
      AMBIENTE int NULL,
      PAC int NULL,
      PARCONTROL int DEFAULT 1 NOT NULL,
      USUARIO_DIGI varchar(255) ,
      PASSWORD_DIGI varchar(255) ,
      TOKEN_DIGI text ,
      FEXPIRA_DIGI datetime ,
      OTORGADO varchar(255) ,
      CONSTRAINT PK_FELINNOVA PRIMARY KEY (PARCONTROL)
    );";
    // Ejecutar el CREATE TABLE
    $base_de_datos->exec($createTableSQL); 

  }
  $query = "
  SELECT COUNT(*) as count
  FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_NAME = 'FELINNOVA' 
  AND COLUMN_NAME LIKE '%CONTRASEÑA%'
  ";

  $stmt = $base_de_datos->prepare($query);
  $stmt->execute();

  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($result['count'] == 0) {
    $base_de_datos->exec("DROP TABLE FELINNOVA;");
    $createTableSQL = "CREATE TABLE FELINNOVA (
      USUARIO_RUC varchar(640) ,
      CONTRASEÑA varchar(640) ,
      CODSUC varchar(640) ,
      CODPFACT varchar(640) ,
      NEMPRESA_DIGI varchar(640) ,
      TEL_DIGI varchar(640) ,
      RUC_DIGI varchar(640) ,
      DV_DIGI varchar(640) ,
      JURIDICO_DIGI int NULL,
      DIRECCION_DIGI varchar(640) ,
      UBICACION_DIGI varchar(640) ,
      COORDENADAS_DIGI varchar(640) ,
      EMAIL_DIGI varchar(640) ,
      FACELECT varchar(255) ,
      TIPO_FACTURA varchar(255) ,
      DIRECCIONENVIO varchar(255) ,
      NUMERODOCUMENTOFISCAL varchar(255) ,
      TIPOEMISION varchar(255) ,
      TIPOSUCURSAL varchar(255) ,
      CODIGOSUCURSALEMISOR varchar(255) ,
      PUNTOFACTURACIONFISCAL varchar(255) ,
      NATURALEZAOPERACION varchar(255) ,
      TIPOOPERACION varchar(255) ,
      DESTINOOPERACION varchar(255) ,
      FORMATOCAFE varchar(255) ,
      ENTREGACAFE varchar(255) ,
      AMBIENTE int NULL,
      PAC int NULL,
      PARCONTROL int DEFAULT 1 NOT NULL,
      USUARIO_DIGI varchar(255) ,
      PASSWORD_DIGI varchar(255) ,
      TOKEN_DIGI text ,
      FEXPIRA_DIGI datetime ,
      OTORGADO varchar(255) ,
      CONSTRAINT PK_FELINNOVA PRIMARY KEY (PARCONTROL)
    );";
    // Ejecutar el CREATE TABLE
    $base_de_datos->exec($createTableSQL); 

  }

  $checkColumnSQL = "SELECT COLUMN_NAME 
                      FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_NAME = 'FELINNOVA' 
                      AND COLUMN_NAME = 'TIPO_FACTURA'";

  $stmt = $base_de_datos->query($checkColumnSQL);
  $result = $stmt->fetch();

  if (!$result) {
    // La columna "TIPO_FACTURA" no existe, ejecutar el ALTER TABLE para agregar las nuevas columnas
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD FACELECT varchar(255) ;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD TIPO_FACTURA varchar(255) ;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD DIRECCIONENVIO varchar(255)  DEFAULT 'https://demoemision.thefactoryhka.com.pa/ws/obj/v1.0/Service.svc?wsdl';");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD NUMERODOCUMENTOFISCAL varchar(255) ;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD TIPOEMISION varchar(255) ;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD TIPOSUCURSAL varchar(255) ;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD CODIGOSUCURSALEMISOR varchar(255) ;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD PUNTOFACTURACIONFISCAL varchar(255) ;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD NATURALEZAOPERACION varchar(255) ;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD TIPOOPERACION varchar(255) ;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD DESTINOOPERACION varchar(255) ;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD FORMATOCAFE varchar(255) ;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD ENTREGACAFE varchar(255) ;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD AMBIENTE int NULL;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD PAC int NULL;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD PARCONTROL int NOT NULL DEFAULT 1;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD USUARIO_DIGI varchar(255);");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD PASSWORD_DIGI varchar(255);");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD TOKEN_DIGI text;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD FEXPIRA_DIGI datetime;");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD OTORGADO varchar(255);");
    $base_de_datos->exec("ALTER TABLE FELINNOVA ADD CONSTRAINT PK_FELINNOVA PRIMARY KEY (PARCONTROL);");
  } 

  $checkColumnSQL = "SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'FELINNOVA' 
    AND COLUMN_NAME = 'PARCONTROL'";

  $stmt = $base_de_datos->query($checkColumnSQL);
  $result = $stmt->fetch();
  if (!$result) 
  {
  $base_de_datos->exec("ALTER TABLE FELINNOVA ADD PARCONTROL int NOT NULL DEFAULT 1;");  
  $base_de_datos->exec("ALTER TABLE FELINNOVA ADD CONSTRAINT PK_FELINNOVA PRIMARY KEY (PARCONTROL);");

  } 

  $campos = [
    'FACELECT'               => 'varchar(255)',
    'TIPO_FACTURA'           => 'varchar(255)',
    'DIRECCIONENVIO'         => "varchar(255)  DEFAULT 'https://demoemision.thefactoryhka.com.pa/ws/obj/v1.0/Service.svc?wsdl'",
    'NUMERODOCUMENTOFISCAL'  => 'varchar(255)',
    'TIPOEMISION'            => 'varchar(255)',
    'TIPOSUCURSAL'           => 'varchar(255)',
    'CODIGOSUCURSALEMISOR'   => 'varchar(255)',
    'PUNTOFACTURACIONFISCAL' => 'varchar(255)',
    'NATURALEZAOPERACION'    => 'varchar(255)',
    'TIPOOPERACION'          => 'varchar(255)',
    'DESTINOOPERACION'       => 'varchar(255)',
    'FORMATOCAFE'            => 'varchar(255)',
    'ENTREGACAFE'            => 'varchar(255)',
    'AMBIENTE'               => 'int NULL',
    'PAC'                    => 'int NULL',
    'PARCONTROL'             => 'int DEFAULT 1 NOT NULL',
    'USUARIO_DIGI'           => 'varchar(255)',
    'PASSWORD_DIGI'          => 'varchar(255)',
    'TOKEN_DIGI'             => 'text',
    'FEXPIRA_DIGI'           => 'datetime',
    'OTORGADO'               => 'varchar(255)',
    'UBICACION_DIGI'         => 'varchar(255)'
  ];
  foreach ($campos as $campo => $type) {
    // Comprobar si el campo existe
    $stmt = $base_de_datos->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'FELINNOVA' AND COLUMN_NAME = :campo");
    $stmt->execute([ 'campo' => $campo]);
  
    if ($stmt->rowCount() == 0) {
        // El campo no existe, agregarlo
        $alterSQL = "ALTER TABLE FELINNOVA ADD $campo $type";
        
        if ($campo == 'PARCONTROL') {
            $alterSQL .= " DEFAULT 1 NOT NULL";
        }
  
        $base_de_datos->exec($alterSQL);
    }
  }

  echo "Se han agregado las columnas a la tabla FELINNOVA correctamente.";
    $checkRecordsSQL = "SELECT COUNT(*) as count FROM FELINNOVA WHERE PARCONTROL = '{$Parcontrol}'";
    $stmt = $base_de_datos->query($checkRecordsSQL);
    $result = $stmt->fetch();

     // Obtener los nombres de las columnas y tipos de datos de la tabla FELINNOVA
    $getColumnsSQL = "SELECT COLUMN_NAME, DATA_TYPE
                      FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_NAME = 'FELINNOVA'";

    $stmt = $base_de_datos->query($getColumnsSQL);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Crear un array asociativo con los valores predeterminados según el tipo de dato
    $defaultValues = array();
    foreach ($columns as $column) {
        switch ($column['DATA_TYPE']) {
            case 'varchar':
            case 'nvarchar':
            case 'char':
            case 'nchar':
            case 'text':
                $defaultValues[$column['COLUMN_NAME']] = '';
                break;
            case 'int':
            case 'tinyint':
            case 'smallint':
            case 'bigint':
            case 'bit':
                $defaultValues[$column['COLUMN_NAME']] = 0;
                break;
            case 'float':
            case 'real':
            case 'numeric':
            case 'decimal':
                $defaultValues[$column['COLUMN_NAME']] = 0.0;
                break;
            case 'date':
            case 'datetime':
            case 'datetime2':
            case 'smalldatetime':
                $defaultValues[$column['COLUMN_NAME']] = null; // Fecha NULL por defecto
                break;
            default:
                $defaultValues[$column['COLUMN_NAME']] = null; // Valor NULL por defecto para otros tipos de datos
                break;
        }
    }

    // Vincular los parámetros utilizando un array asociativo
    $defaultValues['FACELECT']               = $FacElect != "false" ? 1:0;
    $defaultValues['TIPO_FACTURA']           = $tipo_factura ?? '';
    $defaultValues['DIRECCIONENVIO']         = $DireccionEnvio ?? '';
    $defaultValues['TIPOEMISION']            = $tipoEmision ?? '';
    $defaultValues['TIPOSUCURSAL']           = $tipoSucursal ?? '';
    $defaultValues['CODSUC']                 = $codigoSucursal ?? '';
    $defaultValues['NUMERODOCUMENTOFISCAL']  = $numDocFiscal ?? '';
    $defaultValues['CODPFACT']               = $puntoFacturacionFiscal ?? '';
    $defaultValues['NATURALEZAOPERACION']    = $naturalezaOperacion ?? '';
    $defaultValues['TIPOOPERACION']          = $tipoOperacion ?? '';
    $defaultValues['DESTINOOPERACION']       = $destinoOperacion ?? '';
    $defaultValues['FORMATOCAFE']            = $formatoCAFE ?? '';
    $defaultValues['ENTREGACAFE']            = $entregaCAFE ?? '';
    $defaultValues['USUARIO_RUC']            = $tokenEmpresa ?? '';
    $defaultValues['CONTRASEÑA']             = $tokenPassword ?? '';
    $defaultValues['AMBIENTE']               = $Ambiente ?? 0;
    $defaultValues['PARCONTROL']             = $Parcontrol ?? 0;
    $defaultValues['PAC']                    = $PAC ?? 0;
    $defaultValues['USUARIO_DIGI']           = $USUARIO_DIGI ?? 0;
    $defaultValues['PASSWORD_DIGI']          = $PASSWORD_DIGI ?? 0;
    $defaultValues['RUC_DIGI']               = $RUC_DIGI ?? 0;
    $defaultValues['DV_DIGI']                = $DV_DIGI ?? 0;
    $defaultValues['CODIGOSUCURSALEMISOR']   = $CODIGOSUCURSALEMISOR ?? 0;
    $defaultValues['PUNTOFACTURACIONFISCAL'] = $PUNTOFACTURACIONFISCAL ?? 0;

    $defaultValues['NEMPRESA_DIGI']    = $NOMBRE ?? '';
    $defaultValues['TEL_DIGI']         = $TELEFONO ?? '';
    $defaultValues['JURIDICO_DIGI']    = $ESJURIDICO ? '1' : '0';
    $defaultValues['DIRECCION_DIGI']   = $DIRECCION ?? '';
    $defaultValues['UBICACION_DIGI']   = $UBICACION ?? '';
    $defaultValues['COORDENADAS_DIGI'] = $COORDENADAS ?? '';
    $defaultValues['EMAIL_DIGI']       = $EMAIL ?? '';
    unset($defaultValues['TOKEN_DIGI'] );
    unset($defaultValues['FEXPIRA_DIGI'] );
    unset($defaultValues['UBUCACION_DIGI'] );

    if ($result['count'] === '0') {
        // No hay registros, realizar una inserción (INSERT) en la tabla FELINNOVA
        
        // Insertar los valores predeterminados en la tabla FELINNOVA
        $insertSQL = "INSERT INTO FELINNOVA (".implode(', ', array_keys($defaultValues)).") VALUES (".implode(', ', array_fill(0, count($defaultValues), '?')).")";
        $stmt = $base_de_datos->prepare($insertSQL);
        $queryValues = array_values($defaultValues);
        
      
        echo "Se ha insertado un nuevo registro en la tabla FELINNOVA.";
    } else {
        // Ya hay registros, realizar una actualización (UPDATE) en la tabla FELINNOVA
    
        // Construir la consulta SQL de UPDATE con marcadores de posición
        $updateSQL = "UPDATE FELINNOVA SET ";
        $updateColumns = array();
        foreach ($defaultValues as $columnName => $defaultValue) {
            $updateColumns[] = "$columnName = '".$defaultValue."'";
        }

        $updateSQL .= implode(", ", $updateColumns);
        $updateSQL .= " WHERE PARCONTROL = '".$Parcontrol."'";
        // Preparar la consulta
        $stmt = $base_de_datos->prepare($updateSQL);
        //$defaultValues['PARCONTROL']           = $Parcontrol ?? 0;
    
        // Enlazar los valores de los parámetros a los marcadores de posición
        $queryValues = array_values($defaultValues);
        echo "Se ha actualizado el registro existente en la tabla FELINNOVA.";
    }

  ob_clean();
  // Ejecutar la consulta con los parámetros
  if ($stmt->execute($queryValues)  ) {
    echo 'success'; // Envía una respuesta al cliente indicando que los datos se actualizaron correctamente
  } else {
    echo 'error'; // Envía una respuesta al cliente indicando que ocurrió un error al actualizar los datos
  }

  // Cerrar la conexión
  $base_de_datos = null;

} else {
  // Si no se recibieron los datos por POST, muestra un mensaje de error
  echo 'No se recibieron los datos del formulario.';
}
?>