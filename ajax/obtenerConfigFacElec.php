<?php
session_start();
include_once "../config/db.php";

ob_clean();
try {
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
  
    //Se chequea si existe más de un campo contraseña
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
    //Se chequea si existe el campo contraseña completamente
      $query = " SELECT COUNT(*) as count
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
      $base_de_datos->exec("ALTER TABLE FELINNOVA ADD CONSTRAINT PK_FELINNOVA PRIMARY KEY (PARCONTROL);");
  
      echo "Se han agregado las columnas a la tabla FELINNOVA correctamente.";
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
    'OTORGADO'               => 'varchar(255)'
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

    
    // Consulta SQL para obtener los campos de la tabla
    $sql = "SELECT A.*, B.NROINIFAC FROM FELINNOVA as A LEFT JOIN BASEEMPRESA AS B ON B.CONTROL = A.PARCONTROL WHERE  A.PARCONTROL = '".trim($_SESSION["id_control"])."'";;

    // Ejecutar la consulta
    $stmt = $base_de_datos->query($sql);
    $campos = $stmt->fetch(PDO::FETCH_ASSOC);
    ob_clean();
    // Devolver los campos como JSON con estado 200 OK
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $campos]);
} catch (PDOException $e) {
    // Manejar errores de la base de datos y devolver JSON con estado 500 Internal Server Error
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}