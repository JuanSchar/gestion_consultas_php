<?php include("../auth.php"); ?>
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
  <?php include("../bd/conexion.php") ?>

  <div class="main-content" id="panel">
    <?php include("componentes/navbar.php") ?>
    <?php $title = "Listado de consultas"; include("componentes/header.php") ?>

    <div class="container-fluid pt-4">
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-header border-0">
              <h3 class="mb-0">Listado de consultas pendientes de aprobación</h3>
            </div>
    
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Materia</th>
                    <th scope="col">Profesor</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Inicio - Fin</th>
                    <th scope="col">Acción</th>
                  </tr>
                </thead>
                <tbody class="list">
                <?php
                  $Cant_por_Pag = 5;
                  $objeto = new Conexion();
                  $conexion = $objeto->Conectar();
                  $resultado = $conexion->prepare('SELECT * FROM consultas_pendientes_aprobacion_admin;');
                  $resultado->execute();
                  $pagina = isset ( $_GET['pagina']) ? $_GET['pagina'] : null ;
                  if (!$pagina) {
                    $inicio = 0;
                    $pagina=1;
                  } else {
                    $inicio = ($pagina - 1) * $Cant_por_Pag;
                  }
                  $total_registros= $resultado->rowCount();
                  $total_paginas = ceil($total_registros/ $Cant_por_Pag);
                  $resultado = $conexion->prepare('SELECT * FROM consultas_pendientes_aprobacion_admin LIMIT ' . $inicio . ',' . $Cant_por_Pag . ';');
                  $resultado->execute();
                  $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

                  if ($resultado->rowCount() > 0) {
                    echo '<form id="accionBotonAdmin" class="form" action="" method="POST">';
                    foreach($data as $fila) {
                      $date = '';
                      $new_date = '';
                      if ( $fila["fecha_consulta"] ) {
                        $date = strtotime($fila["fecha_consulta"]);
                        $new_date = date('d-m-Y', $date);
                      } else {
                        $new_date = 'Todos los ' . $fila["dia"];
                      }

                      echo '<tr>';
                        echo '<td><b>' . $fila["nombre_materia"] . '</b></td>';
                        echo '<td><b>' . $fila["nombre_profesor"] . '</b></td>';
                        echo '<td>' . $new_date . '</td>';
                        echo '<td>' . $fila["hora_ini_fin"] . '</td>';
                        echo '
                        <td>
                          <input type="submit" id="aceptar' . $fila["id"] . '1" name="aceptar' . $fila["id"] . '1" data-accion=1 data-fila=' . $fila["id"] . ' class="btn btn-success btn-sm" value="ACEPTAR" />
                          <input type="submit" id="rechazar' . $fila["id"] . '2" name="rechazar' . $fila["id"] . '2" data-accion=2 data-fila=' . $fila["id"] . ' class="btn btn-danger btn-sm" value="RECHAZAR" />
                        </td>';
                      echo '</tr>';
                    }
                    echo '</form>';
                  } else {
                    echo '<th colspan=5>No hay consultas pendientes de aprobación.</th>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <?php
              if ($total_paginas > 1){
                echo '<div class="card-footer py-4">';
                echo '  <nav aria-label="...">';
                echo '    <ul class="pagination justify-content-end mb-0">';

                for ($i=1;$i<=$total_paginas;$i++) {
                  echo '<li class="page-item ';
                  echo ($pagina == $i) ?  'active': '';
                  echo '">';
                  echo '<a class="page-link" href="listado_consultas_admin.php?pagina=' . $i . '">' . $i . '</a>';
                  echo'</li>';
                }
                echo '    </ul>';
                echo '  </nav>';
                echo '</div>';
              }
            ?>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-header border-0">
              <h3 class="mb-0">Gestión de consultas de profesores</h3>
              <!-- Formulario de filtros -->
              <form method="GET" class="form-inline my-3">
                <select name="f_materia" class="form-control mr-lg-2">
                  <option value="">Materia</option>
                  <?php
                  $materias = $conexion->query('SELECT DISTINCT m.nombre_materia FROM materia m INNER JOIN consultas_horario ch ON m.idmateria = ch.idmateria')->fetchAll(PDO::FETCH_COLUMN);
                  foreach($materias as $mat) {
                    $selected = (isset($_GET['f_materia']) && $_GET['f_materia'] == $mat) ? 'selected' : '';
                    echo "<option value=\"$mat\" $selected>$mat</option>";
                  }
                  ?>
                </select>
                <select name="f_profesor" class="form-control mt-2 mt-lg-0 mr-lg-2">
                  <option value="">Profesor</option>
                  <?php
                  $profesores = $conexion->query('SELECT DISTINCT p.nombre_profesor FROM profesor p INNER JOIN consultas_horario ch ON p.idprofesor = ch.idprofesor')->fetchAll(PDO::FETCH_COLUMN);
                  foreach($profesores as $prof) {
                    $selected = (isset($_GET['f_profesor']) && $_GET['f_profesor'] == $prof) ? 'selected' : '';
                    echo "<option value=\"$prof\" $selected>$prof</option>";
                  }
                  ?>
                </select>
                <select name="f_dia" class="form-control mt-2 mt-lg-0 mr-lg-2">
                  <option value="">Día</option>
                  <?php
                  $dias = $conexion->query('SELECT DISTINCT dia FROM consultas_horario')->fetchAll(PDO::FETCH_COLUMN);
                  foreach($dias as $dia) {
                    $selected = (isset($_GET['f_dia']) && $_GET['f_dia'] == $dia) ? 'selected' : '';
                    echo "<option value=\"$dia\" $selected>$dia</option>";
                  }
                  ?>
                </select>
                <select name="f_estado" class="form-control mt-2 mt-lg-0 mr-lg-2">
                  <option value="">Estado</option>
                  <?php
                  $estados = $conexion->query('SELECT DISTINCT estado FROM consultas_horario')->fetchAll(PDO::FETCH_COLUMN);
                  foreach($estados as $estado) {
                    $selected = (isset($_GET['f_estado']) && $_GET['f_estado'] == $estado) ? 'selected' : '';
                    echo "<option value=\"$estado\" $selected>$estado</option>";
                  }
                  ?>
                </select>
                <button type="submit" class="btn btn-primary mt-2 mt-lg-0">Filtrar</button>
              </form>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th>Materia</th>
                    <th>Profesor</th>
                    <th>Día</th>
                    <th>Hora inicio</th>
                    <th>Hora fin</th>
                    <th>Fecha consulta</th>
                    <th>Estado</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                  // Filtros
                  $where = [];
                  $params = [];
                  if (!empty($_GET['f_materia'])) {
                    $where[] = 'm.nombre_materia = :materia';
                    $params[':materia'] = $_GET['f_materia'];
                  }
                  if (!empty($_GET['f_profesor'])) {
                    $where[] = 'p.nombre_profesor = :profesor';
                    $params[':profesor'] = $_GET['f_profesor'];
                  }
                  if (!empty($_GET['f_dia'])) {
                    $where[] = 'ch.dia = :dia';
                    $params[':dia'] = $_GET['f_dia'];
                  }
                  if (!empty($_GET['f_estado'])) {
                    $where[] = 'ch.estado = :estado';
                    $params[':estado'] = $_GET['f_estado'];
                  }
                  $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
                  // Paginación
                  $por_pagina = 5;
                  $pagina2 = isset($_GET['pagina2']) ? (int)$_GET['pagina2'] : 1;
                  $inicio2 = ($pagina2 - 1) * $por_pagina;
                  // Total registros para paginación
                  $sql_count = "SELECT COUNT(*) FROM consultas_horario ch INNER JOIN materia m ON ch.idmateria = m.idmateria INNER JOIN profesor p ON ch.idprofesor = p.idprofesor $where_sql";
                  $stmt_count = $conexion->prepare($sql_count);
                  $stmt_count->execute($params);
                  $total_registros2 = $stmt_count->fetchColumn();
                  $total_paginas2 = ceil($total_registros2 / $por_pagina);
                  // Consulta principal con filtros y paginación
                  $sql = "SELECT ch.idconsultas_horario, m.nombre_materia, p.nombre_profesor, ch.dia, ch.hora_ini, ch.hora_fin, ch.fecha_consulta, ch.estado FROM consultas_horario ch INNER JOIN materia m ON ch.idmateria = m.idmateria INNER JOIN profesor p ON ch.idprofesor = p.idprofesor $where_sql ORDER BY ch.idconsultas_horario DESC LIMIT $inicio2, $por_pagina";
                  $stmt = $conexion->prepare($sql);
                  $stmt->execute($params);
                  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  foreach($data as $fila) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($fila["nombre_materia"]) . '</td>';
                    echo '<td>' . htmlspecialchars($fila["nombre_profesor"]) . '</td>';
                    echo '<td>' . htmlspecialchars($fila["dia"]) . '</td>';
                    echo '<td>' . htmlspecialchars($fila["hora_ini"]) . '</td>';
                    echo '<td>' . htmlspecialchars($fila["hora_fin"]) . '</td>';
                    echo '<td>' . ($fila["fecha_consulta"] ? htmlspecialchars($fila["fecha_consulta"]) : '-') . '</td>';
                    echo '<td>' . htmlspecialchars($fila["estado"]) . '</td>';
                    echo '<td>';
                    echo '<form method="POST" action="../controller/accion_consulta_horario_admin.php" style="display:inline-block;">';
                    echo '<input type="hidden" name="idconsultas_horario" value="' . $fila["idconsultas_horario"] . '">';
                    echo '<button type="submit" name="editar" class="btn btn-outline-warning btn-sm">Editar</button>';
                    echo '</form> ';
                    echo '<form method="POST" action="../controller/accion_consulta_horario_admin.php" style="display:inline-block;" onsubmit="return confirm(\'¿Seguro que desea borrar esta consulta?\');">';
                    echo '<input type="hidden" name="idconsultas_horario" value="' . $fila["idconsultas_horario"] . '">';
                    echo '<button type="submit" name="borrar" class="btn btn-outline-danger btn-sm">Borrar</button>';
                    echo '</form>';
                    echo '</td>';
                    echo '</tr>';
                  }
                ?>
                </tbody>
              </table>
            </div>
            <?php
              // Paginación para gestión de consultas de profesores
              if ($total_paginas2 > 1) {
                echo '<div class="card-footer py-4">';
                echo '  <nav aria-label="...">';
                echo '    <ul class="pagination justify-content-end mb-0">';
                $queryString = $_GET;
                unset($queryString['pagina2']);
                $baseUrl = 'listado_consultas_admin.php?'.http_build_query($queryString);
                for ($i=1; $i<=$total_paginas2; $i++) {
                  $active = ($pagina2 == $i) ? 'active' : '';
                  echo '<li class="page-item ' . $active . '">';
                  echo '<a class="page-link" href="' . $baseUrl . ($baseUrl ? '&' : '') . 'pagina2=' . $i . '">' . $i . '</a>';
                  echo '</li>';
                }
                echo '    </ul>';
                echo '  </nav>';
                echo '</div>';
              }
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php
  // Mostrar mensajes flash de éxito o error al volver al listado
  if (isset($_GET['msg'])) {
      echo "<script src='../plugins/sweetalert2/sweetalert2.all.min.js'></script>";
      if ($_GET['msg'] === 'editado') {
          echo "<script>Swal.fire({icon: 'success', title: 'Consulta actualizada con éxito', showConfirmButton: false, timer: 1800});</script>";
      }
      if ($_GET['msg'] === 'borrado') {
          echo "<script>Swal.fire({icon: 'success', title: 'Consulta eliminada con éxito', showConfirmButton: false, timer: 1800});</script>";
      }
      if ($_GET['msg'] === 'error') {
          echo "<script>Swal.fire({icon: 'error', title: 'Ocurrió un error', showConfirmButton: false, timer: 1800});</script>";
      }
  }
  ?>

  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="../assets/vendor/js-cookie/js.cookie.js"></script>
  <script src="../assets/js/argon.js?v=1.2.0"></script>
  <script src="../codigo.js"></script>
</body>

</html>