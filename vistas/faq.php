<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>UTN - Módulo gestión consultas</title>
  <link rel="stylesheet" href="..\estilos.css" type="text/css">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">

</head>

<body>

  <?php include("componentes/sidebar.php") ?>

  <div class="main-content" id="panel">

    <?php include("componentes/navbar.php") ?>
    <?php $title = "Preguntas frecuentes"; include("componentes/header.php") ?>


    <div class="container-fluid pt-4">
      <div class="row">
        <div class="col">
          <div class="card">
   
            <div class="card-body">
              <?php 
                if (isset($_SESSION["s_profesor"])) { 
                  echo '<div>';
                  echo '<label><b>¿Cómo cancelo una consulta?</b></label>';
                  echo '<p>Para cancelar una consulta se debe seleccionar de la menú izquierdo la opción “cancelación de consultas”. En la misma se podrá realizar dos acciones:</p>';
                  echo '<ul>';
                 echo '     <li>Cancelación por día: Se selecciona un día y se cancelan las materias que se seleccionan</li>';
                  echo '    <li>Cancelación por semana: Se selecciona una semana y se cancelan todas las consultas de la misma</li>';
                  echo '</ul>';
                  echo '<p>Luego de completar el tipo de cancelación, se llenar el campo motivo de la cancelación y hacer click sobre "Cancelar consulta"</p>';
             
              echo '</div>';
                }
                if (!isset($_SESSION["s_profesor"])) { 
                  echo '<div>';
                  echo '<label><b>¿Cómo actualizo las horas de consulta desde un Excel?</b></label>';
                  echo '<p>Las horas deben estar cargadas en un Excel, como el adjuntado llamado “Excel_value”. Siempre se debe respetar su forma de carga. ';
                  echo 'Una vez completo dicho Excel, se debe ingresar desde el menú izquierdo a la opción carga de horas. Desde “seleccionar archivo” levantar el excel y hacer click sobre el botón “Actualizar horas de consulta”. ';
                 echo ' </p>';
             echo ' </div>';
                }

                if (isset($_SESSION["s_profesor"])) { 
                echo '<div>';
                    echo '<label><b>¿Cómo acepto o rechazo una consulta?</b></label>';
                    echo '<p>Para aceptar una consulta seleccionar de la barra izquierda el sector “Listado de consulta” visualizar una consulta, en la parte derecha de la fila se encuentran dos botones: </p>';
                   echo ' <ul>';
                   echo '     <li>Aceptar</li>';
                   echo '     <li>Rechazar</li>';
                   echo ' </ul>';
                   echo ' <p> Al seleccionar cada uno acepta o rechaza, respectivamente, una consulta pedida por un alimno </p>';
                echo '</div>';

               echo ' <div>';
               echo '     <label><b>¿Qué pasa al aceptar o rechazar una consulta?</b></label>';
               echo '     <p> Al aceptar o rechazar una consulta, se procede a enviar un mail al alumno que la solicito, informándole del estado de la consulta</p>';
               echo ' </div>';

              }

               
                echo '<div>';
                    echo '<label><b>¿Cómo veo el mapa del sitio?</b></label>';
                    echo '<p>El mapa del sitio se puede visualizar ingresando desde <b><a href="site_map.php">AQUI</a></b>';
               

              ?>

            </div>
    

          </div>
        </div>
      </div>
    </div>
  </div>

  
  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/js-cookie/js.cookie.js"></script>
  <script src="../assets/vendor/jquery.scrollbar/jquery.scrollbar.min.js"></script>
  <script src="../assets/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js"></script>
  <script src="../assets/js/argon.js?v=1.2.0"></script>
  <script src="../codigo.js"></script>


</body>

</html>