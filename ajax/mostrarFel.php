<?php
include_once "../config/db.php";
$accion = isset( $_POST["accion"] ) ? $_POST["accion"] : '' ;
// Número de registros por página
$length = $_POST['length'];
// Índice de la primera fila a mostrar
$start = $_POST['start'];
function tableExists($pdo, $tableName) {
    $checkTableSQL = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = :tableName";
    $stmt = $pdo->prepare($checkTableSQL);
    $stmt->bindParam(':tableName', $tableName, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] > 0 ? true : false;
}
if($accion == "mostrarFel"){
    $parcontrol = isset( $_POST["parcontrol"] ) ? $_POST["parcontrol"] : '' ;
    $tableName = "Documentos";

    // Verificar si la tabla existe
    if (!tableExists($base_de_datos, $tableName)) {
        // Si la tabla no existe, ejecutar el script de creación
        $createTableSQL = "
            CREATE TABLE $tableName (
                CODIGO int NULL,
                CONTROL varchar(19) NULL,
                RESULTADO varchar(100) NULL,
                MENSAJE varchar(MAX) NULL,
                CUFE varchar(100) NULL,
                QR varchar(MAX) NULL,
                FECHARECEPCIONDGI datetime NULL,
                NROPROTOCOLOAYTORIZACION varchar(100) NULL,
                FECHALIMITE datetime NULL,
                PDF nvarchar(MAX) NULL,
                [XML] nvarchar(MAX) NULL,
                NUMDOCFISCAL varchar(100) NULL
            );
        ";

        // Ejecutar el script de creación
        $base_de_datos->exec($createTableSQL);
        echo "Se ha creado la tabla $tableName correctamente.";
    }

    // Consulta SQL para obtener los datos paginados
     $sql3 = "SELECT t.CONTROL, t.FECEMISS, t.NOMBRE, t.MONTOBRU, t.MONTOIMP, t.MONTOTOT, d.RESULTADO, t.NUMREF
            FROM TRANSACCMAESTRO t
            LEFT JOIN Documentos d ON d.CONTROL = t.CONTROL
            WHERE t.TIPTRAN IN ('FAC')
            AND t.TIPREG = '1'
            AND YEAR(t.FECEMISS) = YEAR(GETDATE())
            AND t.FECEMISS >= DATEADD(MONTH, -3, GETDATE())
            AND t.parcontrol = {$parcontrol}
            ORDER BY t.FECEMISS DESC
            OFFSET {$start} ROWS FETCH NEXT {$length} ROWS ONLY;";

    $stmt = $base_de_datos->prepare($sql3);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();

 // Consulta para obtener el número total de registros sin límite de paginación
    $countQuery = "SELECT COUNT(*) AS total FROM TRANSACCMAESTRO t 
                    WHERE t.TIPTRAN IN ('FAC') 
                    AND t.TIPREG = '1' 
                    AND YEAR(t.FECEMISS) = YEAR(GETDATE()) 
                    AND t.FECEMISS >= DATEADD(MONTH, -3, GETDATE()) 
                    AND t.parcontrol = {$parcontrol};";

    $stmt = $base_de_datos->prepare($countQuery);
    $stmt->execute();
    $totalCountRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalCount = $totalCountRow['total'];


    /*for ($i=0; $i < count($rows) ; $i++) 
    {

        $acciones="<div class='btn-group'>";
        $estado="";
        if($value["RESULTADO"] !="procesado")
        {
            $estado = "<span class='badge badge-warning'>Pendiente</span>";
            $acciones.= "<button class='btn btn-primary btnReenviarDocumento' CONTROL = '".$value["CONTROL"]."' ><i class='fa fa-sync text-white'></i></button>";
        }else{
            $acciones.= "<button class='btn btn-danger btnImprimirDocumento'  CONTROL = '".$value["CONTROL"]."' ><i class='fa fa-file-pdf text-white'></i></button>"; 
            $estado = "<span class='badge badge-success'>Procesado</span>";

        }
        $acciones.="</div>";
        $estado.="";
        $rows[$i]["ESTADO"] = $estado;
        $rows[$i]["ACCIONES"] = $acciones;

    }*/
    $dataArray = array();
    foreach ($rows as $key => $value) 
    {
        
        $acciones="<div class='btn-group'>";
        $estado="";
        if($value["RESULTADO"] !="procesado")
        {
            $estado = "<span class='badge badge-warning'>Pendiente</span>";
            $acciones.= "<button class='btn btn-primary btnReenviarDocumento' CONTROL = '".$value["CONTROL"]."' ><i class='fa fa-sync text-white'></i></button>";
        }else{
            $acciones.= "<button class='btn btn-danger btnImprimirDocumento'  CONTROL = '".$value["CONTROL"]."' ><i class='fa fa-file-pdf text-white'></i></button>"; 
            $estado = "<span class='badge badge-success'>Procesado</span>";

        }
        $acciones.="</div>";
        $estado.="";
        $rows[$key]["ACCIONES"]= $acciones;

        $rows[$key]["ESTADO"]= $estado;
        unset($rows[$key]["RESULTADO"]);



    }

    //$datosJson = substr($datosJson, 0, -1);


    $output = array(
        "draw" => intval($_POST['draw']),
        "recordsTotal" => $totalCount,
        "recordsFiltered" => $totalCount,
        "data" => $rows
    );

    echo json_encode($output);

    /*$datosJson = '{

    "data": [ ';

    foreach ($sentencia4 as $key => $value) 
    {
        
        $acciones="<div class='btn-group'>";
        $estado="";
        if($value["RESULTADO"] !="procesado")
        {
            $estado = "<span class='badge badge-warning'>Pendiente</span>";
            $acciones.= "<button class='btn btn-primary btnReenviarDocumento' CONTROL = '".$value["CONTROL"]."' ><i class='fa fa-sync text-white'></i></button>";
        }else{
            $acciones.= "<button class='btn btn-danger btnImprimirDocumento'  CONTROL = '".$value["CONTROL"]."' ><i class='fa fa-file-pdf text-white'></i></button>"; 
            $estado = "<span class='badge badge-success'>Procesado</span>";

        }
        $acciones.="</div>";
        $estado.="";

        $datosJson.= '[                        
            "'.$value["NUMREF"].'",
            "'.$value["NOMBRE"].'",
            "'.$value["MONTOBRU"].'",
            "'.$value["MONTOIMP"].'",
            "'.$value["MONTOBRU"].'",
            "'.$estado.'",
            "'.$acciones.'"
            ],';
    }

    $datosJson = substr($datosJson, 0, -1);

    $datosJson.=  ']

    }';

    echo $datosJson;exit;*/

}