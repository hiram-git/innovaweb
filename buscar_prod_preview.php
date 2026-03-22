<?php
include_once "permiso.php";
include_once "config/db.php";
$txt_buscar=trim($_GET['txt_buscar']);
$txt_precio=trim($_GET['precio']);
$txt_CodAlmacen=(int)trim($_GET['CodAlmacen']);
if($txt_CodAlmacen<=1){
    $txt_CodAlmacen="";
}

$txt_buscar = str_replace("|", "+", $txt_buscar);
$txt_buscar = str_replace("", "*", $txt_buscar);
$txt_buscar = str_replace("'", "", $txt_buscar);
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

SET  @Busqueda = CONCAT( @Busqueda , ' (CODPRO LIKE ''%'+@key+'%'' OR   CODREF LIKE ''%'+@key+'%'' OR
CODREF2 LIKE ''%'+@key+'%'' OR   CODREF3 LIKE ''%'+@key+'%'' OR   CODREF4 LIKE ''%'+@key+'%'' OR
CODREF5 LIKE ''%'+@key+'%'' OR   CODREF6 LIKE ''%'+@key+'%'' OR   CODREF7 LIKE ''%'+@key+'%'' OR
CODREF8 LIKE ''%'+@key+'%'' OR DESCRIP1 LIKE ''%'+@key+'%'' OR   DESCRIP2 LIKE ''%'+@key+'%'' OR
DESCRIP3 LIKE ''%'+@key+'%'') ')
SET  @Busqueda = @Busqueda +IIF((@conteo=@keys),'',' AND ')

FETCH NEXT FROM @keywords
INTO @key,@keys
END
CLOSE @keywords;
DEALLOCATE @keywords;


EXEC ('SELECT CODPRO, CODIGO AS CODDEP, DESCRIP1, DESCRIP2, DESCRIP3, COSTOACT, COSTOPRO, EXISTENCIA$txt_CodAlmacen AS EXISTENCIA, CANRESERVADA$txt_CodAlmacen AS RESERVADA, EXENTO, CANVEN, PRECIO1, PRECIO2, PRECIO3, PRECIO4, PRECIO5, IMPPOR, GRUPOINV, LINEAINV
FROM INVENTARIO
WHERE '+@Busqueda+''
)
";
/*
EXEC ('SELECT CODPRO, CODIGO AS CODDEP, DESCRIP1, DESCRIP2, DESCRIP3, COSTOACT, COSTOPRO, EXISTENCIA$txt_CodAlmacen AS EXISTENCIA, CANRESERVADA$txt_CodAlmacen AS RESERVADA, EXENTO, CANVEN, $txt_precio AS Precio_fijo, IMPPOR, GRUPOINV, LINEAINV
FROM INVENTARIO
WHERE '+@Busqueda+''
)
";*/
}else{
    //$cad_sql=" AND CODPRO='$txt_buscar'";
    $sql3="SELECT CODPRO, CODIGO AS CODDEP, DESCRIP1, DESCRIP2, DESCRIP3, COSTOACT, COSTOPRO, EXISTENCIA$txt_CodAlmacen AS EXISTENCIA, CANRESERVADA$txt_CodAlmacen AS RESERVADA, EXENTO, CANVEN, PRECIO1, PRECIO2, PRECIO3, PRECIO4, PRECIO5, IMPPOR, GRUPOINV, LINEAINV  FROM INVENTARIO ORDER BY DESCRIP1 ASC";
}

$result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
$total_reg = $result->fetchColumn();
if($total_reg!=''){
$sentencia4 = $base_de_datos->prepare($sql3, [
PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
]);

$sentencia4->execute();
$contador=0;

echo "
<div style='text-align:center;padding-top:10px;'><a onClick=\"$('#mymodelprod').modal('hide');\"><i class='fa fa-times-circle-o' aria-hidden='true' style='font-size:40px;color:#FF5001'></i></a></div>
<div class='content_scroll' id='content_scroll' style='position:fixed;top:70px;z-index: 1000;height:550px;width:100%;overflow-y: scroll;border:0px solid #ccc;'>

    <div id='bton' class='tabcontent'>
        <div class='contenedor_prod'>
        "; 
            $f=0;
            while ($data2 = $sentencia4->fetchObject()){
                $txt_completo=$data2->DESCRIP1."".$data2->DESCRIP2."".$data2->DESCRIP3;
                $nombre_formateado = str_replace("'", "&prime;", $data2->DESCRIP1);
                $nombre_formateado = str_replace("\"", "&quot;", $nombre_formateado);
                $disponible=$data2->EXISTENCIA-$data2->RESERVADA;
                //$precio_=$data2->Precio_fijo;
                $precio_1=$data2->PRECIO1;
                $precio_2=$data2->PRECIO2;
                $precio_3=$data2->PRECIO3;
                $precio_4=$data2->PRECIO4;
                $precio_5=$data2->PRECIO5;

                $f++;
                if($f % 2 == 0){
                    $bgcolor='#fff';
                }else{
                    $bgcolor='#EDEDF5';
                }

                //if($_SESSION['tipo_tarea']=='presupuesto'){
                    ?>
                        <button type='button' class='boton_prod' style='background-color:<?php echo $bgcolor;?>;' >                  
                            <div style='font-size:16px;text-align:left;color:black;'><b><?php echo $data2->CODPRO;?> <i class="fa fa-long-arrow-right" aria-hidden="true"></i> <?php echo $data2->DESCRIP1;?></b></div>
                            <div style='font-size:16px;text-align:center;' class='titulo_prod'>
                            <b style='font-size:16px;'>
                            <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i> 
                            Vendedores: 
                            <span><?php echo round($data2->CANVEN);?></span> 
                            <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i> 
                            Existencias: 
                            <span><?php echo round($data2->EXISTENCIA);?></span>
                            <br /> 
                            <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i> 
                            Reservado: 
                            <span><?php echo round($data2->RESERVADA);?></span> 
                            <i class="fa fa-check" aria-hidden="true" style="color:#1ab744;"></i> 
                            Disponible: 
                            <span><?php echo $disponible;?></span>
                            </b><br />
                            
                            <div class='titulo_prod' style='border:0px solid #ccc;'><b>
                                <div style="background-color:#dcddd4;width:80px;border-radius:10px;display: inline-grid;padding:5px;">Precio 1: <?php echo "<span style='color:#fa6630;'>".number_format(round($precio_1, 2), 2)."</span>";?></div> 
                                <div style="background-color:#dcddd4;width:80px;border-radius:10px;display: inline-grid;padding:5px;">Precio 2: <?php echo "<span style='color:#fa6630;'>".number_format(round($precio_2, 2), 2)."</span>";?></div>
                                <div style="background-color:#dcddd4;width:80px;border-radius:10px;display: inline-grid;padding:5px;">Precio 3: <?php echo "<span style='color:#fa6630;'>".number_format(round($precio_3, 2), 2)."</span>";?></div> 
                                <div style="background-color:#dcddd4;width:80px;border-radius:10px;display: inline-grid;padding:5px;">Precio 4: <?php echo "<span style='color:#fa6630;'>".number_format(round($precio_4, 2), 2)."</span>";?></div> 
                                <div style="background-color:#dcddd4;width:80px;border-radius:10px;display: inline-grid;padding:5px;">Precio 5: <?php echo "<span style='color:#fa6630;'>".number_format(round($precio_5, 2), 2)."</span>";?></div>
                            </b></div>
                            </div>  
                        
                        
                    <?php
                    $contador++;

                    /** aqui empieza la busqueda de lotes*/
                    $sql20="SELECT CODPRO, NUMLOTE, EXISTENCIA$txt_CodAlmacen AS EXISTENCIA, Convert(DATE, DateAdd(day, FECHAVENCELOTE  - 4, '1801-01-01')) AS VENCE FROM INVENTARIOLOTES WHERE CODPRO='".$data2->CODPRO."' AND EXISTENCIA$txt_CodAlmacen>0 ORDER BY VENCE ASC";
                    $result20 = $base_de_datos->query($sql20); //$pdo sería el objeto conexión
                    $total_reg20 = $result20->fetchColumn();
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

                //echo "<hr style='color:white;'/>";
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