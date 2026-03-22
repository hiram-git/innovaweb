<style>
    .swal-modal {
        width: 80% !important;
    }
</style>
<?php
include_once "permiso.php";
include_once "config/db.php";
$txt_buscar=trim($_GET['txt_buscar']);
$txt_precio=trim($_GET['precio']);
$txt_CodAlmacen=(int)trim($_GET['CodAlmacen']);
if($txt_CodAlmacen<=1){
    $txt_CodAlmacen="";
}
if($txt_precio == "precio"){

     $sql_EMP = "SELECT PRECIOVENTAD
     FROM BASEEMPRESA WHERE CONTROL='" . $_SESSION['id_control'] . "'";    

    $sentencia4 = $base_de_datos->prepare($sql_EMP);

    $sentencia4->execute();
    $empresa = $sentencia4->fetch(PDO::FETCH_ASSOC);
    $txt_precio  = "PRECIO".$empresa["PRECIOVENTAD"];
}
$txt_buscar = str_replace("|", "+", $txt_buscar);
$txt_buscar = str_replace("", "*", $txt_buscar);
$cad_sql="";

if(($txt_buscar!='') AND ($txt_buscar!='*')){
$sql3="
DECLARE @Busqueda Nvarchar(max)
DECLARE  @key VARCHAR(80)
DECLARE  @keys int
DECLARE  @conteo int
DECLARE  @tipo varchar(1)
DECLARE	 @string    nvarchar(MAX)
DECLARE  @separator nvarchar(MAX)


SET @key=0
SET @keys=0
SET @conteo=0
SET  @tipo=0
SET @string='$txt_buscar'
SET @separator='*'

SET NOCOUNT ON;
declare @keywords cursor

set @keywords = CURSOR FOR
WITH X(N) AS (SELECT 'Table1' FROM (VALUES (0),(0),(0),(0),(0),(0),(0),(0),(0),(0),(0),(0),(0),(0),(0),(0)) T(C)),
Y(N) AS (SELECT 'Table2' FROM X A1, X A2, X A3, X A4, X A5, X A6, X A7, X A8) , -- Up to 16^8 = 4 billion
T(N) AS (SELECT TOP(ISNULL(LEN(@string),0)) ROW_NUMBER() OVER (ORDER BY (SELECT NULL)) -1 N FROM Y),
Delim(Pos) AS (SELECT t.N FROM T WHERE (SUBSTRING(@string, t.N, LEN(@separator+'x')-1) LIKE @separator OR t.N = 0)),
Separated(value) AS (SELECT SUBSTRING(@string, d.Pos + LEN(@separator+'x')-1, LEAD(d.Pos,1,2147483647) OVER (ORDER BY (SELECT NULL)) - d.Pos - LEN(@separator))
FROM Delim d
WHERE @string IS NOT NULL)
SELECT s.value,COUNT(*) OVER () as Keys
FROM Separated s
WHERE s.value <> @separator



OPEN @keywords

FETCH NEXT FROM @keywords
INTO @key,@keys

WHILE @@FETCH_STATUS = 0
BEGIN
SET @Conteo=@Conteo+1
SET  @Busqueda = IIF(@Conteo=1,'',@Busqueda)

SET  @Busqueda = CONCAT( @Busqueda , ' (INVENTARIO.CODPRO LIKE ''%'+@key+'%'' OR   INVENTARIO.CODREF LIKE ''%'+@key+'%'' OR
INVENTARIO.CODREF2 LIKE ''%'+@key+'%'' OR   INVENTARIO.CODREF3 LIKE ''%'+@key+'%'' OR   INVENTARIO.CODREF4 LIKE ''%'+@key+'%'' OR
INVENTARIO.CODREF5 LIKE ''%'+@key+'%'' OR   INVENTARIO.CODREF6 LIKE ''%'+@key+'%'' OR  INVENTARIO.CODREF7 LIKE ''%'+@key+'%'' OR
INVENTARIO.CODREF8 LIKE ''%'+@key+'%'' OR INVENTARIO.DESCRIP1 LIKE ''%'+@key+'%'' OR  INVENTARIO. DESCRIP2 LIKE ''%'+@key+'%'' OR
INVENTARIO.DESCRIP3 LIKE ''%'+@key+'%'') ')
SET  @Busqueda = @Busqueda +IIF((@conteo=@keys),'',' AND ')

FETCH NEXT FROM @keywords
INTO @key,@keys
END
CLOSE @keywords;
DEALLOCATE @keywords;


EXEC ('SELECT INVENTARIO.CODPRO
, INVENTARIO.CODIGO AS CODDEP
, INVENTARIO.DESCRIP1
, INVENTARIO.DESCRIP2
, INVENTARIO.DESCRIP3
, INVENTARIO.COSTOACT
, INVENTARIO.COSTOPRO
, MAX(INVENTARIO.EXISTENCIA$txt_CodAlmacen) AS EXISTENCIA
, MAX(INVENTARIO.CANRESERVADA$txt_CodAlmacen) AS RESERVADA
, MAX(INVENTARIO.EXENTO) AS EXENTO
, MAX(INVENTARIO.CANVEN) AS CANVEN
, MAX(INVENTARIO.$txt_precio) AS Precio_fijo 
, MAX(INVENTARIO.IMPPOR) AS IMPPOR
, MAX(INVENTARIO.GRUPOINV) AS GRUPOINV
, MAX(INVENTARIO.LINEAINV) AS LINEAINV
, MAX(INVENTARIO.PROCOMPUESTO) AS PROCOMPUESTO
, SUM(INVENTARIOEMPAQUESV.CANTIDAD_EMP) AS CANTIDAD_EMP
, SUM(INVENTARIOEMPAQUESV.PRECIO_EMPAQUE) AS PRECIO_EMPAQUE
FROM
INVENTARIO
LEFT JOIN INVENTARIOEMPAQUESV on
INVENTARIO.CODPRO = INVENTARIOEMPAQUESV.CODPRO
WHERE 
INVENTARIO.ACTIVO=0 AND '+@Busqueda+'
GROUP BY INVENTARIO.CODPRO
, INVENTARIO.CODIGO
, INVENTARIO.DESCRIP1
, INVENTARIO.DESCRIP2
, INVENTARIO.DESCRIP3
, INVENTARIO.COSTOACT
, INVENTARIO.COSTOPRO
ORDER BY 
INVENTARIO.DESCRIP1 ASC');   ";

    /*$APPEND = " AND (INVENTARIO.CODPRO LIKE '%".$txt_buscar."%' OR   INVENTARIO.CODREF LIKE '%".$txt_buscar."%' OR
    INVENTARIO.CODREF2 LIKE '%".$txt_buscar."%' OR   INVENTARIO.CODREF3 LIKE '%".$txt_buscar."%' OR  INVENTARIO.CODREF4 LIKE '%".$txt_buscar."%' OR
    INVENTARIO.CODREF5 LIKE '%".$txt_buscar."%' OR   INVENTARIO.CODREF6 LIKE '%".$txt_buscar."%' OR  INVENTARIO. CODREF7 LIKE '%".$txt_buscar."%' OR
    INVENTARIO.CODREF8 LIKE '%".$txt_buscar."%' OR INVENTARIO.DESCRIP1 LIKE '%".$txt_buscar."%' OR   INVENTARIO.DESCRIP2 LIKE '%".$txt_buscar."%' OR
    INVENTARIO.DESCRIP3 LIKE '%".$txt_buscar."%') 
    ";*/

    $cad_sql=" AND (INVENTARIO.CODPRO LIKE '%".$txt_buscar."%' OR   INVENTARIO.CODREF LIKE '%".$txt_buscar."%' OR
    INVENTARIO.CODREF2 LIKE '%".$txt_buscar."%' OR   INVENTARIO.CODREF3 LIKE '%".$txt_buscar."%' OR  INVENTARIO.CODREF4 LIKE '%".$txt_buscar."%' OR
    INVENTARIO.CODREF5 LIKE '%".$txt_buscar."%' OR   INVENTARIO.CODREF6 LIKE '%".$txt_buscar."%' OR  INVENTARIO. CODREF7 LIKE '%".$txt_buscar."%' OR
    INVENTARIO.CODREF8 LIKE '%".$txt_buscar."%' OR INVENTARIO.DESCRIP1 LIKE '%".$txt_buscar."%' OR   INVENTARIO.DESCRIP2 LIKE '%".$txt_buscar."%' OR
    INVENTARIO.DESCRIP3 LIKE '%".$txt_buscar."%')";
}else{
    $cad_sql=" AND (INVENTARIO.CODPRO LIKE '%".$txt_buscar."%' OR   INVENTARIO.CODREF LIKE '%".$txt_buscar."%' OR
    INVENTARIO.CODREF2 LIKE '%".$txt_buscar."%' OR   INVENTARIO.CODREF3 LIKE '%".$txt_buscar."%' OR  INVENTARIO.CODREF4 LIKE '%".$txt_buscar."%' OR
    INVENTARIO.CODREF5 LIKE '%".$txt_buscar."%' OR   INVENTARIO.CODREF6 LIKE '%".$txt_buscar."%' OR  INVENTARIO. CODREF7 LIKE '%".$txt_buscar."%' OR
    INVENTARIO.CODREF8 LIKE '%".$txt_buscar."%' OR INVENTARIO.DESCRIP1 LIKE '%".$txt_buscar."%' OR   INVENTARIO.DESCRIP2 LIKE '%".$txt_buscar."%' OR
    INVENTARIO.DESCRIP3 LIKE '%".$txt_buscar."%')";
    $sql3="SELECT CODPRO, CODIGO AS CODDEP, DESCRIP1, DESCRIP2, DESCRIP3, COSTOACT, COSTOPRO, EXISTENCIA$txt_CodAlmacen AS EXISTENCIA, CANRESERVADA$txt_CodAlmacen AS RESERVADA, EXENTO, CANVEN,
     $txt_precio AS Precio_fijo, IMPPOR, GRUPOINV, LINEAINV, PROCOMPUESTO FROM INVENTARIO WHERE ACTIVO=0 ORDER BY DESCRIP1 ASC";

    //$APPEND = "";
}

/*$sql3="SELECT INVENTARIO.CODPRO
, INVENTARIO.CODIGO AS CODDEP
, INVENTARIO.DESCRIP1
, INVENTARIO.DESCRIP2
, INVENTARIO.DESCRIP3
, INVENTARIO.COSTOACT
, INVENTARIO.COSTOPRO
, MAX(INVENTARIO.EXISTENCIA$txt_CodAlmacen) AS EXISTENCIA
, MAX(INVENTARIO.CANRESERVADA$txt_CodAlmacen) AS RESERVADA
, MAX(INVENTARIO.EXENTO) AS EXENTO
, MAX(INVENTARIO.CANVEN) AS CANVEN
, MAX(INVENTARIO.$txt_precio) AS Precio_fijo 
, MAX(INVENTARIO.IMPPOR) AS IMPPOR
, MAX(INVENTARIO.GRUPOINV) AS GRUPOINV
, MAX(INVENTARIO.LINEAINV) AS LINEAINV
, MAX(INVENTARIO.PROCOMPUESTO) AS PROCOMPUESTO
, SUM(INVENTARIOEMPAQUESV.CANTIDAD_EMP) AS CANTIDAD_EMP
, SUM(INVENTARIOEMPAQUESV.PRECIO_EMPAQUE) AS PRECIO_EMPAQUE
FROM
INVENTARIO
LEFT JOIN INVENTARIOEMPAQUESV on
INVENTARIO.CODPRO = INVENTARIOEMPAQUESV.CODPRO
WHERE 
INVENTARIO.ACTIVO=0 $APPEND
GROUP BY INVENTARIO.CODPRO
, INVENTARIO.CODIGO
, INVENTARIO.DESCRIP1
, INVENTARIO.DESCRIP2
, INVENTARIO.DESCRIP3
, INVENTARIO.COSTOACT
, INVENTARIO.COSTOPRO
ORDER BY 
INVENTARIO.DESCRIP1 ASC";*/
//echo $sql3;exit;
$result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
$total_reg = $result->fetchColumn();
if($total_reg!=''){
    $sentencia4 = $base_de_datos->prepare($sql3, [
    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);

    $sentencia4->execute();
    $contador=0;

    echo "
    <style>
        .gray-button {
            display: inline-block;
            background-color: gray;
            color: black;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }

        .gray-button:hover {
            background-color: darkgray;
        }
    </style>
    <div style='text-align:center;padding-top:10px;'><a onClick=\"$('#mymodeltask').modal('hide');\"><i class='fa fa-times-circle-o' aria-hidden='true' style='font-size:40px;color:#FF5001'></i></a></div>
    <div class='content_scroll' id='content_scroll' style='position:fixed;top:70px;z-index: 1000;height:580px;width:100%;overflow-y: scroll;border:0px solid #ccc;'>

    <div id='bton' class='tabcontent'>
        <div class='contenedor_prod'>
        "; 
            $f=0;
            while ($data2 = $sentencia4->fetchObject()){
                $txt_completo=$data2->DESCRIP1."".$data2->DESCRIP2."".$data2->DESCRIP3;
                $nombre_formateado = str_replace("'", "&prime;", $data2->DESCRIP1);
                $nombre_formateado = str_replace("\"", "&quot;", $nombre_formateado);
                //$nombre_formateado = str_replace("#", "%23", $nombre_formateado);
                $disponible=$data2->EXISTENCIA-$data2->RESERVADA;
                $precio_=$data2->Precio_fijo;
                $f++;
                if($f % 2 == 0){
                    $bgcolor='#fff';
                }else{
                    $bgcolor='#EDEDF5';
                }

                /** aqui empieza la busqueda de lotes*/
                $sql20="SELECT CODPRO, NUMLOTE, EXISTENCIA$txt_CodAlmacen AS EXISTENCIA, Convert(DATE, DateAdd(day, FECHAVENCELOTE  - 4, '1801-01-01')) AS VENCE FROM INVENTARIOLOTES WHERE CODPRO='".$data2->CODPRO."' AND EXISTENCIA$txt_CodAlmacen>0 ORDER BY VENCE ASC";
                //echo "$sql20";
                $result20 = $base_de_datos->query($sql20); //$pdo sería el objeto conexión
                $total_reg20 = $result20->fetchColumn();
                /** aqui empieza la busqueda de lotes*/

                if($_SESSION['tipo_tarea']=='presupuesto'){
                    ?>
                        <button type='button' class='boton_prod' style='background-color:<?php echo $bgcolor;?>;' onClick="cargar_data('<?php echo $data2->CODPRO;?>', '<?php echo $nombre_formateado;?>', '<?php echo round($data2->Precio_fijo, 2);?>', '<?php echo $data2->IMPPOR;?>', '<?php echo $data2->COSTOACT;?>', '<?php echo $data2->COSTOPRO;?>', '<?php echo $data2->GRUPOINV;?>', '<?php echo $data2->CODDEP;?>', '<?php echo $data2->LINEAINV;?>', '<?php echo $data2->Precio_fijo;?>', '<?php echo $disponible;?>', '<?php echo $_SESSION['tipo_tarea'];?>', '<?php echo $data2->EXENTO;?>')">                  
                            <div style='font-size:16px;text-align:left;color:black;'><b><?php echo $data2->CODPRO;?> <i class="fa fa-long-arrow-right" aria-hidden="true"></i> <?php echo $data2->DESCRIP1;?></b></div>
                            <div style='font-size:16px;text-align:center;' class='titulo_prod'>
                            <b style='font-size:16px;'>
                            <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i> 
                            Vendedores: <span><?php echo round($data2->CANVEN);?></span> 
                            <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i> 
                            Existencias: <span><?php echo round($data2->EXISTENCIA);?></span><br />
                            <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i> 
                            Reservado: <span><?php echo round($data2->RESERVADA);?></span>
                            <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i>  
                            Disponible: <span><?php echo $disponible;?></span></b>
                                <br />
                                <div class='titulo_prod' style='border:0px solid #ccc;'><b>
                                    <div style="background-color:#dcddd4;width:80px;border-radius:10px;display: inline-grid;padding:5px;">Precio: <?php echo "<span style='color:#fa6630;'>".number_format(round($precio_, 2), 2)."</span>";?></div>
                                </b></div>
                            </div>   
                        
                    <?php
                    $contador++;

                    /** aqui empieza la busqueda de lotes*/
                    if($total_reg20!=''){
                        $sentencia20 = $base_de_datos->prepare($sql20, [
                        PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                        ]);

                        $sentencia20->execute();
                        echo "<div style='font-size:16px;text-align:center;'>LOTES<br />";
                        while ($data20 = $sentencia20->fetchObject()){
                            //$txt_completo=$data20->DESCRIP1."".$data2->DESCRIP2."".$data2->DESCRIP3;
                            $vence=fechaEspanol($data20->VENCE);
                            ?>    
                            <b style='font-size:16px;'><span><?php echo $data20->NUMLOTE;?>:</span> <i class="fa fa-chevron-right" aria-hidden="true" style="color:#1ab744;"></i> Existencias: <span><?php echo round($data20->EXISTENCIA);?></span> <i class="fa fa-chevron-right" aria-hidden="true" style="color:#1ab744;"></i> Vence: <span><?php echo $vence;?></span></b><br />
                            <?php
                        }
                        echo "</div>";
                    }
                    /** aqui empieza la busqueda de lotes*/
                    echo "</button>";
                }else{
                    //if($total_reg20==''){
                        if($disponible>0 OR $_SESSION["ventamenos"] == "1"){
                            ?>
                            <button type='button' class='boton_prod' style='background-color:<?php echo $bgcolor;?>;' onClick="cargar_data('<?php echo $data2->CODPRO;?>', '<?php echo $nombre_formateado;?>', '<?php echo number_format(round($data2->Precio_fijo, 2), 2, '.', '');?>', '<?php echo $data2->IMPPOR;?>', '<?php echo $data2->COSTOACT;?>', '<?php echo $data2->COSTOPRO;?>', '<?php echo $data2->GRUPOINV;?>', '<?php echo $data2->CODDEP;?>', '<?php echo $data2->LINEAINV;?>', '<?php echo $data2->Precio_fijo;?>', '<?php echo $disponible;?>', '<?php echo $_SESSION['tipo_tarea'];?>', '<?php echo $data2->EXENTO;?>')">                  
                            <div style='font-size:16px;text-align:left;color:black;'><b><?php echo $data2->CODPRO;?> <i class="fa fa-long-arrow-right" aria-hidden="true"></i> <?php echo $data2->DESCRIP1;?></b></div>
                                <div style='font-size:16px;text-align:center;' class='titulo_prod'>
                                <b style='font-size:16px;'>
                                <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i>
                                Vendedores: <span><?php echo round($data2->CANVEN);?></span>
                                <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i>
                                Existencias: <span><?php echo round($data2->EXISTENCIA);?></span><br />
                                <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i>
                                Reservado: <span><?php echo round($data2->RESERVADA);?></span> 
                                <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i>
                                Disponible: <span><?php echo $disponible;?></span></b>
                                <br />
                                <div class='titulo_prod'><b>
                                <div style="background-color:#dcddd4;width:80px;border-radius:10px;display: inline-grid;padding:5px;">Precio: <?php echo "<span style='color:#fa6630;'>".number_format(round($precio_, 2), 2)."</span>";?></div> 
                                </b>
                                <?php if($data2->CANTIDAD_EMP > 0){ ?>
                                <div style="background-color:#dcddd4;width:80px;border-radius:10px;display: inline-grid;padding:5px;" class="gray-button" id="btn_empaque" data-id="<?php echo $data2->CODPRO;?>" data-almacen="<?php echo $_GET["CodAlmacen"]?>" ><b>Empaque</b></div> 
                                <?php } ?></div>
                                </div>
                                
                            
                            
                            <?php
                            $contador++;
                            
                            /** aqui empieza la busqueda de lotes*/
                            if($total_reg20!=''){
                                $sentencia20 = $base_de_datos->prepare($sql20, [
                                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                                ]);

                                $sentencia20->execute();
                                echo "<div style='font-size:16px;text-align:center;'>LOTES<br />";
                                while ($data20 = $sentencia20->fetchObject()){
                                    //$txt_completo=$data20->DESCRIP1."".$data2->DESCRIP2."".$data2->DESCRIP3;
                                    $vence=fechaEspanol($data20->VENCE);
                                    ?>    
                                    <b style='font-size:16px;'><span><?php echo $data20->NUMLOTE;?>:</span> <i class="fa fa-chevron-right" aria-hidden="true" style="color:#1ab744;"></i> Existencias: <span><?php echo round($data20->EXISTENCIA);?></span> <i class="fa fa-chevron-right" aria-hidden="true" style="color:#1ab744;"></i> Vence: <span><?php echo $vence;?></span></b><br />
                                    <?php
                                }
                                echo "</div>";
                            }
                            /** aqui empieza la busqueda de lotes*/
                            echo "</button>";
                        }else{
                            ?>
                            <button type='button' class='boton_prod' style='background-color:<?php echo $bgcolor;?>;' onClick="swal('Alerta!', 'Producto sin existencia ', {buttons:false,});">                  
                            <div style='font-size:16px;text-align:left;color:black;'><b><?php echo $data2->CODPRO;?> <i class="fa fa-long-arrow-right" aria-hidden="true"></i> <?php echo $data2->DESCRIP1;?></b></div>
                                <div style='font-size:16px;text-align:center;' class='titulo_prod'>
                                <b style='font-size:16px;'>
                                <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i>
                                Vendedores: <span><?php echo round($data2->CANVEN);?></span> 
                                <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i>
                                Existencias: <span><?php echo round($data2->EXISTENCIA);?></span><br />
                                <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i>
                                Reservado: <span><?php echo round($data2->RESERVADA);?></span> 
                                <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i>
                                Disponible: <span><?php echo $disponible;?></span></b>
                                <br /><div class='titulo_prod'><b>
                                <div style="background-color:#dcddd4;width:80px;border-radius:10px;display: inline-grid;padding:5px;">Precio: <?php echo "<span style='color:#fa6630;'>".number_format(round($precio_, 2), 2)."</span>";?></div> 
                                </b></div>
                                </div>
                                
                            
                            
                            <?php
                            $contador++;

                            /** aqui empieza la busqueda de lotes*/
                            if($total_reg20!=''){
                                $sentencia20 = $base_de_datos->prepare($sql20, [
                                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                                ]);

                                $sentencia20->execute();
                                echo "<div style='font-size:16px;text-align:center;'>LOTES<br />";
                                while ($data20 = $sentencia20->fetchObject()){
                                    //$txt_completo=$data20->DESCRIP1."".$data2->DESCRIP2."".$data2->DESCRIP3;
                                    $vence=fechaEspanol($data20->VENCE);
                                    ?>    
                                    <b style='font-size:16px;'><span><?php echo $data20->NUMLOTE;?>:</span> <i class="fa fa-chevron-right" aria-hidden="true" style="color:#1ab744;"></i> Existencias: <span><?php echo round($data20->EXISTENCIA);?></span> <i class="fa fa-chevron-right" aria-hidden="true" style="color:#1ab744;"></i> Vence: <span><?php echo $vence;?></span></b><br />
                                    <?php
                                }
                                echo "</div>";
                            }
                            /** aqui empieza la busqueda de lotes*/
                            echo "</button>";
                        }
                    //}
                }
            }
            
    echo "
        </div>
    </div>";
    //echo "<div  style='position:fixed;top:0px;color:black;border:0px solid #ccc;margin:15px 10px 10px 10px;' ><a onClick=\"document.getElementById('layer_prod').innerHTML='';\">Cerrar X</a></div>";
    /*if($contador>1){
        echo "
        <a onClick=\"document.getElementById('layer_prod').innerHTML='';\" style='color:black;border:0px solid #ccc;margin:15px 10px 10px 10px;'>Cerrar X</a>";
    }*/
    
    echo "</div><br /><br /><br /><br /><br /><br />";
}else{
    echo "
    <div class='content_scroll' id='content_scroll' style='position:fixed;top:0;z-index: 1000;height:50px;width:100%;overflow-y: scroll;border:0px solid #ccc;'>
    
    <div id='bton' class='tabcontent'>
        <div class='contenedor_prod'><br />
    No se encontro coincidencia<br />";
    echo "</div>
        </div>
    </div><br /><br />";
}

function fechaEspanol($fecha) {
    $fecha = substr($fecha, 0, 10);
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));
    $dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
    $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $nombredia = str_replace($dias_EN, $dias_ES, $dia);
    $meses_ES = array("Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Agost", "Sept", "Oct", "Nov", "Dic");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
    return $numeroDia."-".$nombreMes."-".$anio;
}
?>