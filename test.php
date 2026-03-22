<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Featured Items Template | PrepBootstrap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" /> -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css" /> -->
    <link href="https://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous"> -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <!-- <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script> -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <!-- <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script> -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</head>
<body>

<div class="container">

<div class="page-header">
    <h3><i class="fas fa-user-friends" style='font-size:50px;float:left;padding-right:10px;'></i><small>Módulo de Emisión de Pedidos y Presupuestos</small></h3>
    <br />
    <form class="form-inline" action='clientes.php' method='GET'>
          <div class="input-group mb-3">
              <div class="autocomplete">
              <input autocomplete="off" type="text" class="form-control" name="input_buscar" id="input_buscar" value="<?php echo $input_buscar;?>">
              </div>
              <div class="input-group-append">
              
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
              </div>&nbsp;
              <div class="input-group-append">
                <a href='form_nuevo_cliente.php'>
                      <button class="btn btn-primary" type="button"><i class="fa fa-plus-circle" aria-hidden="true"></i></button>
                </a>&nbsp;
                <button class="btn btn-outline-secondary" type="button" id="clean" onClick="input_buscar.value='';"><span class="fas fa-eraser"></span></button>
                <button class="btn btn-outline-secondary" type="button" id="signout" onClick="window.location='cerrar_session.php'"><span class="fa fa-sign-out"></span></button>
              </div> 
              <script>
              autocomplete(document.getElementById("input_buscar"));
              </script>
          </div>
        </form>
</div>

<!-- Featured Items - START -->


  <div class="container">
        <div class="row style_featured">
            <div class="col-md-4">
                <div>
                    <img src="http://www.prepbootstrap.com/Content/images/template/featureditems/product_001.jpg" alt="" class="img-rounded img-thumbnail" />
                    <h2>Item A</h2>
                    <p style="text-align: left;">
                        <span class="fa fa-info-circle"></span>
                        A quality item for purchase. Features state of the art technology
                    </p>
                    <p style="text-align: left;">
                        <span class="fa fa-bank "></span>
                        0% Financing available
                    </p>
                    <p style="text-align: left;">
                        <span class="fa fa-calendar "></span>
                        12 months full warranty
                    </p>
                    <a href="#" class="btn btn-success" title="More">more »</a>
                </div>
            </div>
            <div class="col-md-4">
                <div>
                    <img src="http://www.prepbootstrap.com/Content/images/template/featureditems/product_002.jpg" alt="" class="img-rounded img-thumbnail"/>
                    <h2>Item A</h2>
                    <p style="text-align: left;">
                        <span class="fa fa-info-circle"></span>
                        A quality item for purchase. Features state of the art technology
                    </p>
                    <p style="text-align: left;">
                        <span class="fa fa-bank "></span>
                        0% Financing available
                    </p>
                    <p style="text-align: left;">
                        <span class="fa fa-calendar "></span>
                        21 months full warranty
                    </p>
                    <a href="#" class="btn btn-success" title="More">more »</a>
                </div>
            </div>
            <div class="col-md-4">
                <div>
                    <img src="http://www.prepbootstrap.com/Content/images/template/featureditems/product_003.jpg" alt="" class="img-rounded img-thumbnail"/>
                    <h2>Item A</h2>
                    <p style="text-align: left;">
                        <span class="fa fa-info-circle"></span>
                        A quality item for purchase. Features state of the art technology
                    </p>
                    <p style="text-align: left;">
                        <span class="fa fa-bank "></span>
                        0% Financing available
                    </p>
                    <p style="text-align: left;">
                        <span class="fa fa-calendar "></span>
                        4 months full warranty
                    </p>
                    <a href="#" class="btn btn-success" title="More">more »</a>
                </div>
            </div>
        </div>
    </div>

<style>
.style_featured{
    padding: 20px 0;
    text-align: center;
}
.style_featured > div > div{
    padding: 10px;
    border: 1px solid transparent;
    border-radius: 4px;
    transition: 0.5s;
}
.style_featured > div:hover > div{
    margin-top: +19px;
    border: 1px solid rgb(153, 200, 250);
    box-shadow: rgba(0, 0, 0, 0.1) 0px 9px 9px 9px;
    background: rgba(153, 200, 250, 0.1);
    transition: 0.99s;
}
</style>

<!-- Featured Items - END -->

</div>

</body>
</html>