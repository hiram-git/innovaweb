<?php

Class Digifact{
    public $base_de_datos;
  
    public function __construct(PDO $base_de_datos) {
      $this->base_de_datos = $base_de_datos;
    }
    
    public function validarToken($fecha_expiracion){
        if(!$fecha_expiracion)
          $fecha_expiracion = "1900-01-01";
        $fecha = explode(" ", $fecha_expiracion);
        if($fecha[0] == "0000-00-00")
        {
            return  false;
        }
        $fecha_hoy = new Datetime();
        $fecha_exp = new Datetime($fecha_expiracion);
        $diferencia = $fecha_exp->diff( $fecha_hoy );
        if( $diferencia -> days>0 )
        {
            return  true;
        }
        else{
            return  false;
        }
    }
    
    public function validarAuthToken($CONFIG) {
      if($CONFIG["TOKEN_DIGI"] == "" OR $CONFIG["TOKEN_DIGI"] == null OR $CONFIG["TOKEN_DIGI"] == "null" OR 
      $CONFIG["FEXPIRA_DIGI"] == "" OR $CONFIG["FEXPIRA_DIGI"] == null OR $CONFIG["FEXPIRA_DIGI"] == "null"){
        return true;
      }else{
        return false;
      }

    }
    public function obtenerToken(  $data ){  
        $curl = curl_init();     
        curl_setopt_array($curl, [
        CURLOPT_URL => $data["url"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => '{"Username": "'.$data["user"].'","Password": "'.$data["psw"].'"}',
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
        ]);

        $response = curl_exec($curl);
        if(!$response){
          return "Credenciales incorrectas";
        }
        $respuesta = json_decode($response, 1);
        if(isset($respuesta["response"]) AND $respuesta["response"] == "Credenciales incorrectas"){
          return $respuesta["response"];
        }else{
          curl_close($curl);
          $expira_en = isset($$respuesta["expira_en"]) ? $respuesta["expira_en"] : date("n/j/Y g:i:s A");
          $dateTime = DateTime::createFromFormat('n/j/Y g:i:s A', $expira_en);
          $tiempo = $dateTime->format('Y-m-d H:i:s');
  
          $sql_update = "UPDATE FELINNOVA SET TOKEN_DIGI = :TOKEN_DIGI, FEXPIRA_DIGI = :FEXPIRA_DIGI, OTORGADO = :OTORGADO WHERE PARCONTROL = :PARCONTROL;";
          $stmt = $this->base_de_datos->prepare($sql_update);
          $stmt->bindParam(':TOKEN_DIGI', $respuesta['Token'], PDO::PARAM_STR);
          $stmt->bindParam(':FEXPIRA_DIGI', $tiempo, PDO::PARAM_STR);
          $stmt->bindParam(':OTORGADO', $respuesta['otorgado_a'], PDO::PARAM_STR);
          $stmt->bindParam(':PARCONTROL', $data["parcontrol"], PDO::PARAM_STR);
          $stmt->execute();
      
          return $respuesta['Token'];

        }
        
        
    }

}