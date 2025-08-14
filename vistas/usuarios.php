<?php 
include("../auth.php");
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
  header('Location: ../index.php');
  exit();
}
require_once '../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();
$sql = "SELECT u.*, p.nombre_profesor, a.nombre, a.apellido FROM usuarios u 
        LEFT JOIN profesor p ON u.idprofesor = p.idprofesor 
        LEFT JOIN alumno a ON u.idalumno = a.idalumno";
$res = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Administrar Usuarios</title>
  <link rel="stylesheet" href="../estilos.css" type="text/css">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
</head>
<body>
  <?php include("componentes/sidebar.php") ?>
  <div class="main-content" id="panel">
    <?php include("componentes/navbar.php") ?>
    <?php $title = "Usuarios"; include("componentes/header.php") ?>
    <div class="container-fluid pt-4">
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-header border-0 d-flex justify-content-between">
              <h3 class="mb-0">Listado de usuarios</h3>
              <a href="usuario_form.php" class="btn btn-outline-primary btn-sm">Nuevo Usuario</a>
            </div>

            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Usuario</th>
                    <th scope="col">Rol</th>
                    <th scope="col">Profesor o Alumno</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody class="list">
                  <?php while($row = $res->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                      <td><?= htmlspecialchars($row['usuario'] ?? '') ?></td>
                      <td><?php
                        if ($row['rol'] == 1) echo 'Admin';
                        elseif ($row['rol'] == 2) echo 'Profesor';
                        else echo 'Alumno';
                      ?></td>
                      <td>
                        <?php
                          if (!empty($row['nombre']) && !empty($row['apellido'])) {
                            echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']);
                          } else {
                            echo htmlspecialchars($row['nombre_profesor'] ?? '');
                          }
                        ?>
                      </td>
                      <td>
                        <a href="usuario_form.php?id=<?= $row['idusuario'] ?>" class="btn btn-outline-warning btn-sm">Editar</a>
                        <a href="../controller/usuario_eliminar.php?id=<?= $row['idusuario'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Seguro que desea eliminar este usuario?');">Eliminar</a>
                      </td>
                    </tr>
                  <?php endwhile; ?>
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
