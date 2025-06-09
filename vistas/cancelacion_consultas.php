<?php include("../auth.php"); ?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>UTN - Módulo gestión consultas</title>
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../plugins/sweetalert2/sweetalert2.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <link rel="stylesheet" href="..\estilos.css" type="text/css">
</head>

<body>
  <?php include("componentes/sidebar.php") ?>
  <?php include("../bd/conexion.php");
  $objeto = new Conexion();
  $conexion = $objeto->Conectar(); ?>

  <div class="main-content" id="panel">
    <?php include("componentes/navbar.php") ?>
    <?php $title = "Cancelación de consultas";
    include("componentes/header.php") ?>

    <div class="container-fluid pt-4">
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-header border-0">
              <h3 class="mb-0">Cancelación de consultas</h3>
            </div>

            <div class="card-body">
              <form class="filter" id="buscar2" action="" method="GET">
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="form-group">
                        <label class="form-control-label" for="calendario">Seleccione tipo de cancelación</label>
                        <select id="calendario" name="calendario" class="form-control">
                          <option value=1>Día</option>
                          <option value=2>Semana</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group">
                        <input type="hidden" id="fecha_desde" name="fecha_desde" class="form-control">
                        <input type="hidden" id="fecha_hasta" name="fecha_hasta" class="form-control">

                        <label id="fechaLabel" class="form-control-label" for="fecha">Seleccione dia</label>
                        <input type="date" id="fecha" name="fecha" class="form-control">
                      </div>
                    </div>
                    <div class="col-12 col-sm-12 col-lg-2 p-2">
                      <div class="form-group">
                        <br>
                        <input value="Buscar" type="submit" class="btn btn-outline-primary" id="buscar2" name="buscar2">
                      </div>
                    </div>
                  </div>
                </div>
              </form>

              <?php
              if (isset($_GET['retorno']) && $_GET['retorno'] == 1) {
                echo '<div class="correcto">Consultas canceladas correctamente.</div>';
              }
              if (isset($_GET['buscar2'])) {
                $idprofesor = $_SESSION["s_profesor"];
                $fecha_desde = $_GET['fecha_desde'];
                $fecha_hasta = $_GET['fecha_hasta'];

                $resultado = $conexion->prepare('CALL consultas_a_cancelar(?, ?, ?)');
                $resultado->execute([$idprofesor, $fecha_desde, $fecha_hasta]);
                $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

                echo '<div class="row">';
                echo '<div class="col-lg-12">';
                echo '<div class="form-group">';
                if ($resultado->rowCount() > 0) {
                  echo '<label class="form-control-label" for="input-last-name">Horas de consulta a cancelar</label>';
                  echo ' <form class="form" action="../controller/cancelar_consultas.php" method="POST">';
                  foreach ($data as $fila) {
                    $date = strtotime($fila["fecha"]);
                    $new_date = date('d-m-Y', $date);
                    echo ' <div class="form-check">';
                    echo '<input name="idconsultas_horario[]" class="form-check-input" type="checkbox" value="' . $fila["idconsultas_horario"] . '/' . $fila["fecha"] . '" id="' . $fila["idconsultas_horario"] . '">';
                    echo '<label class="form-check-label" for="' . $fila["idconsultas_horario"] . '">' . $new_date . ' - ' . $fila["hora_ini"] . ' ' . $fila["nombre_materia"] . '</label>';
                    echo '</div>';
                  }
                  echo '<div class="pl-lg-4">';
                  echo '<div class="form-group">';
                  echo '<label class="form-control-label">Motivo de la cancelación</label>';
                  echo '<textarea name="motivo" rows="4" class="form-control" required></textarea>';
                  echo '</div>';
                  echo ' </div>';
                  echo '<div class="pl-lg-12">';
                  echo '<p><input type="submit" class="btn btn-primary btn-block" value="CANCELAR CONSULTA" /> </p>';
                  echo '</div>';
                  echo '</form>';
                } else {
                  echo '<div>No hay consultas programadas en ese rango de fechas.</div>';
                }
                echo '</div>';
                echo '</div>';
                echo '</div>';
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="../assets/vendor/js-cookie/js.cookie.js"></script>
  <script src="../assets/js/argon.js?v=1.2.0"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>

  <script>
    $('#calendario').on('change', function() {
      if (parseInt(this.value) === 1) {
        document.getElementById("fechaLabel").innerHTML = 'Seleccionar dia'
        document.getElementById("fecha").setAttribute('type', 'date')
        document.getElementById("fecha").setAttribute('min', moment().format('YYYY-MM-DD'));
        document.getElementById("fecha").setAttribute('value', moment().format('YYYY-MM-DD'));
      } else {
        document.getElementById("fechaLabel").innerHTML = 'Seleccionar semana'
        document.getElementById("fecha").setAttribute('type', 'week')
        document.getElementById("fecha").setAttribute('min', '2020-W' + moment().format('W'));
        document.getElementById("fecha").setAttribute('value', '2020-W' + moment().format('W'));
      }
    }).trigger('change');

    $('#fecha').on('change', function() {
      if (this.value.includes('W')) {
        let ano = this.value.split('-')[0];
        let semana = this.value.split('-W')[1];
        document.getElementById("fecha_desde").setAttribute('value', moment().year(ano).week(semana).startOf('week').format('YYYY-MM-DD'));
        document.getElementById("fecha_hasta").setAttribute('value', moment().year(ano).week(semana).endOf('week').format('YYYY-MM-DD'));
      } else {
        document.getElementById("fecha_desde").setAttribute('value', moment(this.value).format('YYYY-MM-DD'));
        document.getElementById("fecha_hasta").setAttribute('value', moment(this.value).format('YYYY-MM-DD'));
      }
    }).trigger('change');
  </script>
</body>

</html>
