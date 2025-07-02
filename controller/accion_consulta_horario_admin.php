<?php
include("../auth.php");
include_once '../bd/conexion.php';

$objeto = new Conexion();
$conexion = $objeto->Conectar();

function show_alert($msg, $type = 'success') {
  echo "<script src='../plugins/sweetalert2/sweetalert2.all.min.js'></script>";
  echo "<script>Swal.fire({icon: '$type', title: '$msg', showConfirmButton: false, timer: 1800});</script>";
}

if (isset($_GET['msg'])) {
  if ($_GET['msg'] === 'editado') show_alert('Consulta actualizada con éxito', 'success');
  if ($_GET['msg'] === 'borrado') show_alert('Consulta eliminada con éxito', 'success');
  if ($_GET['msg'] === 'error') show_alert('Ocurrió un error', 'error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['borrar']) && isset($_POST['idconsultas_horario'])) {
    $id = $_POST['idconsultas_horario'];
    $stmt = $conexion->prepare('DELETE FROM consultas_horario WHERE idconsultas_horario = ?');
    if ($stmt->execute([$id])) {
      header('Location: ../vistas/listado_consultas_admin.php?msg=borrado');
    } else {
      header('Location: ../vistas/listado_consultas_admin.php?msg=error');
    }
    exit();
  }
  if (isset($_POST['editar']) && isset($_POST['idconsultas_horario'])) {
    $id = $_POST['idconsultas_horario'];
    $stmt = $conexion->prepare('SELECT * FROM consultas_horario WHERE idconsultas_horario = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($fila) {
      $materias = $conexion->query('SELECT idmateria, nombre_materia FROM materia')->fetchAll(PDO::FETCH_ASSOC);
      $profesores = $conexion->query('SELECT idprofesor, nombre_profesor FROM profesor')->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <!DOCTYPE html>
      <html lang="es">
      <head>
        <meta charset="UTF-8">
        <title>Editar consulta de profesor</title>
        <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="../estilos.css" type="text/css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
        <link rel="stylesheet" href="../plugins/sweetalert2/sweetalert2.min.css">
      </head>
      <body>
        <?php include("../vistas/componentes/sidebar.php"); ?>
        <div class="main-content" id="panel">
          <?php include("../vistas/componentes/navbar.php"); ?>
          <?php $title = "Editar consulta"; include("../vistas/componentes/header.php"); ?>
          <div class="container-fluid pt-4">
            <div class="row">
              <div class="col">
                <div class="card">
                  <div class="card-header border-0">
                    <h3 class="mb-0">Editar consulta de profesor</h3>
                  </div>
                  <div class="card-body">
                    <form method="POST" action="accion_consulta_horario_admin.php">
                      <div class="pl-lg-12">
                        <input type="hidden" name="idconsultas_horario" value="<?php echo $fila['idconsultas_horario']; ?>">
                        <div class="row">
                          <div class="col-lg-6">
                            <div class="form-group">
                              <label>Materia</label>
                              <select name="idmateria" class="form-control" required>
                                <?php foreach($materias as $m) {
                                  $sel = $m['idmateria'] == $fila['idmateria'] ? 'selected' : '';
                                  echo "<option value='{$m['idmateria']}' $sel>{$m['nombre_materia']}</option>";
                                } ?>
                              </select>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group">
                              <label>Profesor</label>
                              <select name="idprofesor" class="form-control" required>
                                <?php foreach($profesores as $p) {
                                  $sel = $p['idprofesor'] == $fila['idprofesor'] ? 'selected' : '';
                                  echo "<option value='{$p['idprofesor']}' $sel>{$p['nombre_profesor']}</option>";
                                } ?>
                              </select>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group">
                              <label>Día</label>
                              <select name="dia" id="dia-select" class="form-control" required onchange="actualizarIdDia()">
                                <?php
                                $dias_semana = [
                                  0 => 'lunes',
                                  1 => 'martes',
                                  2 => 'miércoles',
                                  3 => 'jueves',
                                  4 => 'viernes',
                                  5 => 'sábado',
                                  6 => 'domingo'
                                ];
                                $dia_actual = $fila['dia'];
                                $id_dia_actual = array_search($dia_actual, $dias_semana);
                                foreach ($dias_semana as $id => $nombre) {
                                  $sel = $nombre == $dia_actual ? 'selected' : '';
                                  echo "<option value='$nombre' data-id='$id' $sel>$nombre</option>";
                                }
                                ?>
                              </select>
                              <input type="hidden" name="id_dia" id="id-dia" value="<?php echo $id_dia_actual ? $id_dia_actual : '' ?>">
                            </div>
                          </div>
                          <div class="col-lg-3">
                            <div class="form-group">
                              <label>Hora inicio</label>
                              <input type="time" name="hora_ini" class="form-control" value="<?php echo substr($fila['hora_ini'], 0, 5); ?>" required>
                            </div>
                          </div>
                          <div class="col-lg-3">
                            <div class="form-group">
                              <label>Hora fin</label>
                              <input type="time" name="hora_fin" class="form-control" value="<?php echo substr($fila['hora_fin'], 0, 5); ?>" required>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group">
                              <label>Fecha consulta (opcional)</label>
                              <input type="date" name="fecha_consulta" class="form-control" value="<?php echo $fila['fecha_consulta']; ?>">
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group">
                              <label>Estado</label>
                              <select name="estado" class="form-control" required>
                                <?php
                                $estados = ['Activo','Inactivo','Pendiente','Aceptada','Rechazada'];
                                foreach($estados as $e) {
                                  $sel = $e == $fila['estado'] ? 'selected' : '';
                                  echo "<option value='$e' $sel>$e</option>";
                                }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="col-12 col-sm-12 col-lg-2">
                            <div class="form-group">
                              <button type="submit" name="guardar" class="btn btn-outline-primary">Guardar cambios</button>
                              <a href="../vistas/listado_consultas_admin.php" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                          </div>
                        </div>
                      </div>
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
        <script src="../plugins/sweetalert2/sweetalert2.all.min.js"></script>
        <script>
        function actualizarIdDia() {
          var select = document.getElementById('dia-select');
          var selectedOption = select.options[select.selectedIndex];
          var idDia = selectedOption.getAttribute('data-id');
          document.getElementById('id-dia').value = idDia;
        }
        // Al cargar la página, asegurarse de que el id-dia esté correcto
        window.onload = function() {
          actualizarIdDia();
        };
        </script>
      </body>
      </html>
      <?php
      exit();
    }
  }
  if (isset($_POST['guardar']) && isset($_POST['idconsultas_horario'])) {
    $id = $_POST['idconsultas_horario'];
    $idmateria = $_POST['idmateria'];
    $idprofesor = $_POST['idprofesor'];
    $dia = $_POST['dia'];
    $id_dia = isset($_POST['id_dia']) ? $_POST['id_dia'] : null;
    $hora_ini = $_POST['hora_ini'];
    $hora_fin = $_POST['hora_fin'];
    $fecha_consulta = $_POST['fecha_consulta'] ? $_POST['fecha_consulta'] : null;
    $estado = $_POST['estado'];
    $stmt = $conexion->prepare('UPDATE consultas_horario SET idmateria=?, idprofesor=?, dia=?, id_dia=?, hora_ini=?, hora_fin=?, fecha_consulta=?, estado=? WHERE idconsultas_horario=?');
    if ($stmt->execute([$idmateria, $idprofesor, $dia, $id_dia, $hora_ini, $hora_fin, $fecha_consulta, $estado, $id])) {
      header('Location: ../vistas/listado_consultas_admin.php?msg=editado');
    } else {
      header('Location: ../vistas/listado_consultas_admin.php?msg=error');
    }
    exit();
  }
}
// Si no es POST, redirigir
header('Location: ../vistas/listado_consultas_admin.php');
exit();
