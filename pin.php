<?php 
$file ='config/db.php';
$exists = is_file($file);
if($exists){
  
}else{
  ?>
  <!DOCTYPE html>
  <html>
  <head>
  <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
  <LINK href="css/estilo.css" rel="stylesheet" type="text/css">
  <link rel="shortcut icon" type="image/jpg" href="imgs/logo.ico"/>
  <!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" /> -->
  <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous"> -->

  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <script src="jquery/jquery-3.2.1.slim.min.js" ></script>
  <script src="bootstrap/js/bootstrap.min.js"></script>
  <link href="font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">

  <style>
  body { 
    border: 0px solid black;
    padding: 0px;
    background: url(imgs/fondo.jpg) no-repeat fixed center;
    background-repeat: no-repeat;
    /*background-size: 100%;*/
    background-size: cover;
  }

.fade-in-text {
  display: inline-block;
  /*font-family: Arial, Helvetica, sans-serif;*/
  font-size: 150px;
  /*margin-top:130%;*/
  color: black;
  animation: fadeIn linear 2s infinite;
  -webkit-animation: fadeIn linear 2s infinite;
  -moz-animation: fadeIn linear 2s infinite;
  -o-animation: fadeIn linear 2s infinite;
  -ms-animation: fadeIn linear 2s infinite;

}

@keyframes fadeIn {
  0% {opacity:0;}
  100% {opacity:1;}
}

@-moz-keyframes fadeIn {
  0% {opacity:0;}
  100% {opacity:1;}
}

@-webkit-keyframes fadeIn {
  0% {opacity:0;}
  100% {opacity:1;}
}

@-o-keyframes fadeIn {
  0% {opacity:0;}
  100% {opacity:1;}
}

@-ms-keyframes fadeIn {
  0% {opacity:0;}
  100% {opacity:1;}
}


.fade-in-image {
  position: absolute;
  
  animation-name: animar;
  animation-duration: 2s;
  -webkit-animation-duration: 2s;
  -moz-animation-duration: 2s;
  -o-animation-duration: 2s;
  -ms-animation-duration: 2s;

  /*animation: fadeIn linear 2s infinite;
  -webkit-animation: fadeIn linear 2s infinite;
  -moz-animation: fadeIn linear 2s infinite;
  -o-animation: fadeIn linear 2s infinite;
  -ms-animation: fadeIn linear 2s infinite;*/

}



@media only screen and (min-width:320px) and (max-width:480px){
  .fade-in-text {
    margin-top:15em;
  }

  .fade-in-image {
    width:8em;
    left: 35.5%;
    top:25%;
  }

  @keyframes animar{
    100% { top: 25%; left: 35.5%;opacity:1;}
    0% { top: 45px; left: 35.5%; opacity:0;}
  }
  @-webkit-keyframes animar{
    100% { top: 25%; left: 35.5%;opacity:1;}
    0% { top: 45px; left: 35.5%; opacity:0;}
  }

  @-moz-keyframes animar{
    100% { top: 25%; left: 35.5%;opacity:1;}
    0% { top: 45px; left: 35.5%; opacity:0;}
  }

  @-o-keyframes animar{
    100% { top: 25%; left: 35.5%;opacity:1;}
    0% { top: 45px; left: 35.5%; opacity:0;}
  }

  @-ms-keyframes animar{
    100% { top: 25%; left: 35.5%;opacity:1;}
    0% { top: 45px; left: 35.5%; opacity:0;}
  }
}

@media only screen and (min-width:768px){

  .fade-in-text {
    margin-top:20em;
  }

  .fade-in-image {
    width:8em;
    left: 45.8%;
    top:25%;
  }

  @keyframes animar{
    100% { top: 25%; left: 45.8%;opacity:1;}
    /*30% { top: 10px; left: 50px; }
    60% { top: 30px; left: 50px;opacity:0.6;}*/
    0% { top: 45px; left: 45.8%; opacity:0;}
  }
  @-webkit-keyframes animar{
    100% { top: 25%; left: 45.8%;opacity:1;}
    0% { top: 45px; left: 45.8%; opacity:0;}
  }

  @-moz-keyframes animar{
    100% { top: 25%; left: 45.8%;opacity:1;}
    0% { top: 45px; left: 45.8%; opacity:0;}
  }

  @-o-keyframes animar{
    100% { top: 25%; left: 45.8%;opacity:1;}
    0% { top: 45px; left: 45.8%; opacity:0;}
  }

  @-ms-keyframes animar{
    100% { top: 25%; left: 45.8%;opacity:1;}
    0% { top: 45px; left: 45.8%; opacity:0;}
  }
}

  </style>
  </head>
  <body>
    <main>
      <div>
        <div>
          <center>
            <img class='fade-in-image' src='imgs/logo1.png'><br />
            <label class='fade-in-text' style='color:#ffffff;font-size:18px;'>Iniciando Configuración...<label>
            <br /><br />
          </center>
        </div>
      </div>
    </main>
  </body>
  </html> 
  <?php
  echo "<script type='text/javascript'>
    setTimeout(function(){ window.location='pin_register.php'; }, 5000);
  </script>";
  exit();
}

?>