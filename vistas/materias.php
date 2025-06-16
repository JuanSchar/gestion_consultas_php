<?php 
include("../auth.php");
include("../bd/conexion.php");
// Lógica de acciones antes de cualquier salida HTML
$objeto = new Conexion();
$conexion = $objeto->Conectar();
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$mensaje = '';
if ($accion == 'nueva' && $_SERVER['REQUEST_METHOD'] == 'POST') {
  $nombre = $_POST['nombre'];
  $codigo = $_POST['codigo'];
  $sql = "INSERT INTO materia (nombre_materia, cod_materia) VALUES (?, ?)";
  $stmt = $conexion->prepare($sql);
  $stmt->execute([$nombre, $codigo]);
  header("Location: materias.php?msg=creada");
  exit;
}
if ($accion == 'eliminar' && isset($_GET['id'])) {
  $id = $_GET['id'];
  $sql = "DELETE FROM materia WHERE idmateria = ?";
  $stmt = $conexion->prepare($sql);
  $stmt->execute([$id]);
  header("Location: materias.php?msg=eliminada");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Administrar Materias</title>
  <link rel="stylesheet" href="../estilos.css" type="text/css">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
</head>
<body>
  <?php include("componentes/sidebar.php") ?>
  <div class="main-content" id="panel">
    <?php include("componentes/navbar.php") ?>
    <?php $title = "Materias"; include("componentes/header.php") ?>
    <?php
      if (isset($_GET['msg']) && $_GET['msg'] == 'creada') {
        echo '<div class="alert alert-success">Materia creada con éxito.</div>';
      }
      if (isset($_GET['msg']) && $_GET['msg'] == 'eliminada') {
        echo '<div class="alert alert-success">Materia eliminada con éxito.</div>';
      }
      // Mostrar formulario de nueva materia antes de cualquier otro contenido si corresponde
      if ($accion == 'nueva') {
        echo '<div class="container-fluid pt-4">';
          echo '<div class="row">';
            echo '<div class="col">';
              echo '<div class="card">';
                echo '<div class="card-header border-0">';
                  echo '<h3 class="mb-0">Nueva Materia</h3>';
                echo '</div>';
                echo '<div class="card-body">';
                  echo '<form method="POST" action="materias.php?accion=nueva">';
                    echo '<div class="pl-lg-12">';
                      echo '<div class="row">';
                        echo '<div class="col-lg-6">';
                          echo '<div class="form-group">';
                            echo '<label>Nombre</label>';
                            echo '<input type="text" name="nombre" class="form-control" required>';
                          echo '</div>';
                        echo '</div>';
                        echo '<div class="col-lg-6">';
                          echo '<div class="form-group">';
                            echo '<label>Código</label>';
                            echo '<input type="text" name="codigo" class="form-control" required>';
                          echo '</div>';
                        echo '</div>';
                        echo '<div class="col-12 col-sm-12 col-lg-2">';
                          echo '<div class="form-group">';
                            echo '<button type="submit" class="btn btn-outline-primary">Guardar</button>';
                            echo '<a href="materias.php" class="btn btn-outline-secondary">Volver</a>';
                          echo '</div>';
                        echo '</div>';
                      echo '</div>';
                    echo '</div>';
                  echo '</form>';
                echo '</div>';
              echo '</div>';
            echo '</div>';
          echo '</div>';
        echo '</div>';
        exit;
      }
    ?>
    <div class="container-fluid pt-4">
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-header border-0 d-md-flex justify-content-between ">
              <h3 class="mb-0">Listado de materias</h3>
              <div class="mt-2 mt-sm-0">
                <a href="materias.php?accion=nueva" class="btn btn-outline-primary btn-sm">Nueva Materia</a>
                <a href="asignar_profesores.php" class="btn btn-outline-warning btn-sm">Asignar Profesor</a>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Nombre</th>
                    <th scope="col">Código</th>
                    <th scope="col">Acción</th>
                  </tr>
                </thead>
                <tbody class="list">
                  <?php
                    // Listado de materias
                    $sql = "SELECT * FROM materia";
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute();
                    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($materias as $m) {
                      echo '<tr>';
                      echo '<td>' . $m['nombre_materia'] . '</td>';
                      echo '<td>' . $m['cod_materia'] . '</td>';
                      echo '<td>';
                      echo '<a href="materias.php?accion=eliminar&id=' . $m['idmateria'] . '" class="btn btn-outline-danger btn-sm" onclick="return confirm(\'¿Seguro?\')">Eliminar</a> ';
                      echo '</td>';
                      echo '</tr>';
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../assets/vendor/js-cookie/js.cookie.js"></script>
  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="../assets/js/argon.js?v=1.2.0"></script>
</body>
</html>
