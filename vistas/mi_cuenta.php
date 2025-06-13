<?php include("../auth.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Gestor de consultas UTN</title>
  <meta name="description" content="Mi cuenta - Gestor de consultas UTN">
  <link rel="stylesheet" href="..\estilos.css" type="text/css">
  <link rel="stylesheet" href="\bootstrap/css/bootstrap.min.css">
</head>

<body>
  <?php include("componentes/sidebar.php") ?>
  <?php include("../bd/conexion.php");
  $objeto = new Conexion();
  $conexion = $objeto->Conectar(); ?>

  <div class="main-content" id="panel">
    <?php include("componentes/navbar.php") ?>
    <?php 
      $resultado = $conexion->prepare('SELECT * FROM profesor WHERE idprofesor = ?;');
      $resultado->execute([$_SESSION["s_profesor"]]);
      $data = $resultado->fetch(PDO::FETCH_ASSOC);
    ?>
    <div class="container-fluid pt-4">
      <div class="row">
        <div class="col-xl-12 order-xl-1">
          <div class="card">
            <div class="card-header">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3>Editar perfil</h3>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form method="POST" action="<?php $_SERVER['PHP_SELF'] ?>">
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label class="form-control-label" for="input-email">Dirección de correo</label>
                        <input name="correo" type="email" id="input-email" class="form-control" placeholder="Ingrese su email" value="<?php echo $data["correo"] ?>">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="input-first-name">Nombre</label>
                        <?php
                          $nombre_completo = explode(", ", $data["nombre_profesor"]);
                          $nombre = isset($nombre_completo[1]) ? $nombre_completo[1] : "";
                        ?>
                        <input name="nombre" type="text" id="input-first-name" class="form-control" placeholder="Nombre" value="<?php echo $nombre ?>">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="input-last-name">Apellido</label>
                        <input name="apellido" type="text" id="input-last-name" class="form-control" placeholder="Apellido" value="<?php echo explode(", ", $data["nombre_profesor"])[0] ?>">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="pl-lg-4">
                  <div class="form-group">
                    <label class="form-control-label" for="input-observaciones">Observaciones</label>
                    <textarea name="observaciones" id="input-observaciones" rows="4" class="form-control" placeholder="Ingrese observaciones"><?php echo $data["observaciones"] ?></textarea>
                  </div>
                </div>
                <div class="pl-lg-4">
                  <div class="form-group">
                    <input value="Guardar" type="submit" class="btn btn-outline-primary" id="guardar" name="guardar">
                  </div>
                </div>
                <?php 
                  if(isset($_POST['guardar']) ) {
                    $nombre_profesor = $_POST['apellido'] . ", " . $_POST['nombre'];
                    $resultado = $conexion->prepare("
                    START TRANSACTION;
                      UPDATE profesor p 
                      SET p.nombre_profesor = ?, p.observaciones = ?, p.correo = ? 
                      WHERE p.idprofesor = ?;
                    COMMIT;");
                    $retorno = $resultado->execute([$nombre_profesor, $_POST['observaciones'], $_POST['correo'], $_SESSION['s_profesor']]);
                    if ($retorno) {
                      echo '<div class="p-2 alert-success rounded">Datos guardados correctamente.</div>';
                    }
                  }
                ?>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="../assets/vendor/js-cookie/js.cookie.js"></script>
  <script src="../assets/js/argon.js?v=1.2.0"></script>
</body>

</html>