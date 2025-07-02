<?php include("../auth.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>UTN - Módulo gestión consultas</title>
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../plugins/sweetalert2/sweetalert2.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
</head>

<body>
  <?php include("componentes/sidebar.php") ?>
  <?php include("../bd/conexion.php") ?>

  <div class="main-content" id="panel">
    <?php include("componentes/navbar.php") ?>
    <?php $title = "Nueva consulta";
    include("componentes/header.php") ?>

    <div class="container-fluid pt-4">
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-header border-0">
              <h3 class="mb-0">Nueva consulta</h3>
            </div>

            <div class="card-body">
              <form class="filter" id="nueva_consulta" action="../controller/solicitar_consulta.php" method="POST">
                <div class="pl-lg-12">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <?php
                        $objeto = new Conexion();
                        $conexion = $objeto->Conectar();
                        $resultado = $conexion->prepare('
                                    SELECT materia.idmateria, materia.nombre_materia 
                                    FROM materias_profesores 
                                    INNER JOIN materia on materia.idmateria = materias_profesores.idmateria
                                    WHERE idprofesor = ?;');
                        $resultado->execute([$_SESSION["s_profesor"]]);
                        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

                        if ($resultado->rowCount() > 0) {
                          echo '<label class="form-control-label" for="materia">Materia</label>
                                  <select name="materia" id="materia" class="form-control" >';
                          foreach ($data as $fila) {
                            echo '<option value=' . $fila['idmateria'] . '>' . $fila['nombre_materia'] . '</option>';
                          }
                        } else {
                          echo 'No tiene materias asignadas.';
                        }
                        ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="dia">Día</label>
                        <select name="dia" id="dia" class="form-control">
                          <option value=-1>Seleccione dia</option>
                          <option value=0>Lunes</option>
                          <option value=1>Martes</option>
                          <option value=2>Miercoles</option>
                          <option value=3>Jueves</option>
                          <option value=4>Viernes</option>
                        </select>
                      </div>
                      <div class="form-group ml-4">
                        <input name="se_repite" class="form-check-input" type="checkbox" checked id="se_repite">
                        <label class="form-check-label" for="se_repite">Se repite</label>
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div class="form-group">
                        <label class="form-control-label" for="hora_desde">Hora desde:</label>
                        <input id="hora_desde" type="time" name="hora_desde" value="00:00" class="form-control">
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div class="form-group">
                        <label class="form-control-label" for="hora_hasta">Hora hasta:</label>
                        <input id="hora_hasta" type="time" name="hora_hasta" value="00:00" class="form-control">
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div id="selecciona_fecha" style="display: none">
                        <div class="form-group">
                          <label class="form-control-label" for="fecha">Fecha</label>
                          <input type="date" id="fecha" name="fecha" class="form-control">
                        </div>
                      </div>
                    </div>
                    <div class="col-12 col-sm-12 col-lg-2">
                      <div class="form-group">
                        <br>
                        <input value="Solicitar" type="submit" class="btn btn-outline-primary" id="solicitar" name="solicitar">
                      </div>
                    </div>
                  </div>
                </div>
              </form>
              <div class="alert alert-danger" id="error"></div>
              <?php
              if (isset($_GET['retorno']) && $_GET['retorno'] == 1) {
                echo '<div class="alert alert-success">Solicitud enviada al administrador.</div>';
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid">
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-header border-0">
              <h3 class="mb-0">Consultas existentes</h3>
            </div>

            <div class="card-body">
              <div class="pl-lg-12">
                <div class="row">
                  <div class="col-lg-12">
                    <div class="form-group">
                      <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                          <thead class="thead-light">
                            <tr>
                              <th scope="col">Materia</th>
                              <th scope="col">Fecha</th>
                              <th scope="col">Inicio - Fin</th>
                              <th scope="col">Estado</th>
                            </tr>
                          </thead>
                          <tbody class="list">
                            <?php
                            $resultado = $conexion->prepare('
                              select distinct idprofesor, dia, fecha_consulta, CONCAT(hora_ini, " - ", hora_fin) "hora_ini_fin", materia.idmateria, materia.nombre_materia, estado 
                              from consultas_horario 
                              inner join materia on materia.idmateria = consultas_horario.idmateria 
                              where idprofesor = ?;');
                            $resultado->execute([$_SESSION["s_profesor"]]);
                            $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

                            if ($resultado->rowCount() > 0) {
                              foreach ($data as $fila) {
                                $date = '';
                                $new_date = '';
                                if ($fila["fecha_consulta"]) {
                                  $date = strtotime($fila["fecha_consulta"]);
                                  $new_date = date('d-m-Y', $date);
                                } else {
                                  $new_date = 'Todos los ' . $fila["dia"];
                                }
                                echo '<tr>';
                                echo '<td><b>' . $fila["nombre_materia"] . '</b></td>';
                                echo '<td>' . $new_date . '</td>';
                                echo '<td>' . $fila["hora_ini_fin"] . '</td>';
                                echo '<td>' . $fila["estado"] . '</td>';
                                echo '</tr>';
                              }
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
    form = document.getElementById("nueva_consulta");
    form.addEventListener('submit', function (event) {
      // si el campo de correo electrónico es válido, dejamos que el formulario se envíe
      if ( document.getElementById("dia").value  == -1) {
        document.getElementById("error").innerHTML = '<b>Debe seleccionar un dia</b>';
        event.preventDefault();
      }

      if ( document.getElementById("fecha").value  == '' && !document.getElementById("se_repite").checked ) {
        document.getElementById("error").innerHTML = '<b>Debe seleccionar una fecha</b>';
        event.preventDefault();
      }

      if ( document.getElementById("hora_hasta").value < document.getElementById("hora_desde").value) { 
        document.getElementById("error").innerHTML = '<b>La hora hasta no puede ser menor a la hora desde</b>d';
        event.preventDefault();
      }
    });
    fecha.min = new Date().toISOString().split("T")[0]

    $('#se_repite').on('change', function() {
      if ( this.checked ) {
        document.getElementById("selecciona_fecha").style.display = 'none'
      } else {
        document.getElementById("selecciona_fecha").style.display = 'block'
      }
    });
  </script>
</body>

</html>