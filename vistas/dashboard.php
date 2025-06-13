<?php include("../auth.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Gestor de consultas UTN</title>
  <meta name="description" content="Dashboard para gestionar consultas en la UTN. Mira las consultas disponibles o carga nuevas.">
  <link rel="stylesheet" href="..\estilos.css" type="text/css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <link rel="stylesheet" href="\bootstrap/css/bootstrap.min.css">
</head>

<body>
  <?php include("componentes/sidebar.php") ?>
  <?php include("../bd/conexion.php");
  $objeto = new Conexion();
  $conexion = $objeto->Conectar(); ?>

  <div class="main-content" id="panel">
    <?php include("componentes/navbar.php") ?>

    <div>
      <div class="container-fluid pt-4">
        <div class="row">
          <?php $cardnum = "1";
          include("componentes/dashboard_cards.php") ?>

          <?php
          $cardnum = "2";
          include("componentes/dashboard_cards.php") ?>

          <?php
          $cardnum = "3";
          include("componentes/dashboard_cards.php") ?>

          <?php
          $cardnum = "4";
          include("componentes/dashboard_cards.php") ?>
        </div>
      </div>
    </div>

    <div class="container-fluid">
      <div class="row">
        <div class="col-xl-12">
          <div class="card">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Próximas consultas</h3>
                </div>
                <div class="col text-right">
                  <a href="listado_consultas.php" class="btn btn-sm btn-primary">Ver más</a>
                </div>
              </div>
            </div>
            <div class="table-responsive pb-4">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Materia</th>
                    <?php
                    if (!isset($_SESSION["s_profesor"])) {
                      echo '<th scope="col">Profesor</th>';
                    }
                    ?>
                    <th scope="col">Fecha </th>
                    <th scope="col">Hora inicio - fin</th>
                    <th scope="col">Cantidad de alumnos</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $Cant_por_Pag = 5;
                  $resultado = $conexion->prepare('CALL proximas_consultas(?, 0, 100000);');
                  $resultado->execute([$_SESSION["s_profesor"]]);
                  $pagina = isset($_GET['pagina']) ? $_GET['pagina'] : null;
                  if (!$pagina) {
                    $inicio = 0;
                    $pagina = 1;
                  } else {
                    $inicio = ($pagina - 1) * $Cant_por_Pag;
                  }
                  $total_registros = $resultado->rowCount();
                  $total_paginas = ceil($total_registros / $Cant_por_Pag);
                  $resultado = $conexion->prepare('CALL proximas_consultas(?, ?, ?);');
                  $resultado->execute([$_SESSION["s_profesor"], $inicio, $Cant_por_Pag]);
                  $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

                  if ($resultado->rowCount() > 0) {
                    foreach ($data as $fila) {
                      echo '<tr>';
                        echo '<td><b>' . $fila["nombre_materia"] . '</b></td>';
                        if (!isset($_SESSION["s_profesor"])) {
                          echo '<td>' . $fila["nombre_profesor"] . '</td>';
                        }
                        if ($fila["fecha_gen"] == null) {
                          $new_date = $fila["dia"];
                        } else {
                          $date = strtotime($fila["fecha_gen"]);
                          $new_date = date('d-m-Y', $date);
                        }
                        echo '<td>' .   $new_date . '</td>';
                        echo '<td>' . $fila["hora_ini"] . ' - ' . $fila["hora_fin"] . '</td>';
                        echo '<td>' . $fila["cantidad_alumnos"] . '</td>';
                      echo '</tr>';
                    }
                  } else {
                    echo '<th colspan=4>No hay próximas consultas</th>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <?php
            if ($total_paginas > 1) {
              echo '<div class="card-footer py-4">';
              echo '<nav aria-label="...">';
              echo '<ul class="pagination justify-content-end mb-0">';

              for ($i = 1; $i <= $total_paginas; $i++) {
                echo '<li class="page-item ';
                echo ($pagina == $i) ?  'active' : '';
                echo '">';
                echo '        <a class="page-link" href="dashboard.php?pagina=' . $i . '">' . $i . '</a>';
                echo '       </li>';
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
        <div class="col-xl-12">
          <div class="card">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Consultas canceladas</h3>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Materia</th>
                    <?php
                    if (!isset($_SESSION["s_profesor"])) {
                      echo '<th scope="col">Profesor</th>';
                    }
                    ?>
                    <th scope="col">Fecha </th>
                    <th scope="col">Hora inicio - fin</th>
                    <th scope="col">Motivo</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $Cant_por_Pag = 3;
                  $resultado = $conexion->prepare('CALL consultas_canceladas(?, 0, 100000);');
                  $resultado->execute([$_SESSION["s_profesor"]]);
                  $pagina_canc = isset($_GET['pagina_canc']) ? $_GET['pagina_canc'] : null;
                  if (!$pagina_canc) {
                    $inicio = 0;
                    $pagina_canc = 1;
                  } else {
                    $inicio = ($pagina_canc - 1) * $Cant_por_Pag;
                  }
                  $total_registros = $resultado->rowCount();
                  $total_paginas_canc = ceil($total_registros / $Cant_por_Pag);
                  $resultado = $conexion->prepare('CALL consultas_canceladas(?, ?, ?);');
                  $resultado->execute([$_SESSION["s_profesor"], $inicio, $Cant_por_Pag]);
                  $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

                  if ($resultado->rowCount() > 0) {
                    foreach ($data as $fila) {
                      echo '<tr>';
                      echo '<td><b>' . $fila["nombre_materia"] . '</b></td>';
                      if (!isset($_SESSION["s_profesor"])) {
                        echo '<td>' . $fila["nombre_profesor"] . '</td>';
                      }

                      $date = strtotime($fila["fecha_bloqueo"]);
                      $new_date = date('d-m-Y', $date);
                      echo '<td>' .   $new_date . '</td>';
                      echo '<td>' . $fila["hora_ini"] . ' - ' . $fila["hora_fin"] . '</td>';
                      echo '<td>' . $fila["motivo"] . '</td>';
                      echo '</tr>';
                    }
                  } else {
                    echo '<th colspan=4>No hay consultas canceladas recientemente.</th>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <?php
            if ($total_paginas_canc > 1) {
              echo '<div class="card-footer py-4">';
              echo '<nav aria-label="...">';
              echo '<ul class="pagination justify-content-end mb-0">';

              for ($i = 1; $i <= $total_paginas_canc; $i++) {
                echo '<li class="page-item ';
                echo ($pagina_canc == $i) ?  'active' : '';
                echo '">';
                echo '        <a class="page-link" href="dashboard.php?pagina_canc=' . $i . '">' . $i . '</a>';
                echo '       </li>';
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
  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="../assets/vendor/js-cookie/js.cookie.js"></script>
  <script src="../assets/js/argon.js?v=1.2.0"></script>
</body>

</html>
