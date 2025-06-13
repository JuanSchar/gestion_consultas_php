<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>UTN - Módulo gestión consultas</title>
  <meta name="description" content="Sistema para gestionar consultas en la UTN. Filtrá por profesor, materia y reservá tu lugar fácilmente.">

  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/sweetalert2/sweetalert2.min.css">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <link rel="stylesheet" href="/../estilos.css" type="text/css"> 
</head>

<body>
  <?php include("vistas/componentes/navbar_alumno.php") ?>
  <?php include("bd/conexion.php") ?>

  <div class="main-content" id="panel">
    <div class="header bg-primary pb-2">
      <div class="container-fluid">
        <div class="header-body">
          <div style="padding: 16px" class="col-lg-6 col-7">
            <h2 class="text-white">Listado de consultas</h2>
          </div>
          <p class="text-white">En este apartado encontrarás los horarios de consultas disponibles.<br>
            Se puede filtrar por profesor y por materia. Una vez que envíes la solicitud recordá revisar tu casilla de correo.
          </p>
        </div>
      </div>
    </div>

    <div class="container-fluid pt-4">
      <div class="card">
        <div class="card-header border-0">
          <h3 class="mb-0">Filtros</h3>
        </div>
        <div class="card-body">
          <form class="filter" id="buscar" method="GET">
            <div class="row">
              <div class="col-12 col-sm-12 col-lg-3 p-2">
                <label for="materia">Materia</label>
                <?php
                $objeto = new Conexion();
                $conexion = $objeto->Conectar();
                $sql = 'SELECT DISTINCT m.idmateria, m.nombre_materia 
                        FROM consultas_horario ch 
                        INNER JOIN materia m ON ch.idmateria = m.idmateria ';

                $resultado = $conexion->prepare($sql);
                $resultado->execute();

                $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

                if ($resultado->rowCount() > 0) {
                  echo '<select onchange="filtrarPorMateria(' . !isset($_GET['id_profesor_filtro']) . ')" name="materia" id="materia"  class="form-control" >';
                  echo '<option value=-1>Seleccione...</option>';
                  foreach ($data as $fila) {
                    echo ' <option value="' . $fila["idmateria"] . '">' . $fila["nombre_materia"] . '</option>';
                  }
                  echo ' </select>';
                } else {
                  echo 'No hay materias.';
                }
                ?>
              </div>
              <div class="col-12 col-sm-12 col-lg-3 p-2">
                <label for="profesor">Profesor</label>
                <?php
                $sql = 'SELECT DISTINCT p.idprofesor, p.nombre_profesor 
                    FROM consultas_horario ch 
                    RIGHT JOIN profesor p ON ch.idprofesor = p.idprofesor ';
                if (isset($_GET['id_materia_filtro'])) {
                    $sql .= 'WHERE ch.idmateria = ?';
                }
                $resultado = $conexion->prepare($sql);
                if (isset($_GET['id_materia_filtro'])) {
                    $resultado->execute([$_GET['id_materia_filtro']]);
                } else {
                    $resultado->execute();
                }
                $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

                if ($resultado->rowCount() > 0) {
                  echo ' <select id="profesor" name="profesor" class="form-control">';
                  echo '<option value=-1>Seleccione...</option>';
                  foreach ($data as $fila) {
                    echo ' <option value="' . $fila["idprofesor"] . '">' . $fila["nombre_profesor"] . '</option>';
                  }
                  echo ' </select>';
                } else {
                  echo 'No hay profesores.';
                }
                ?>
              </div>

              <div class="col-12 col-sm-12 col-lg-3 p-2">
                <br>
                <input value="Buscar" type="submit" class="btn btn-outline-primary" id="buscar3" name="buscar">
                <input value="Borrar" type="button" class="btn btn-outline-danger" onclick="resetFiltros();">
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="row">
        <div class="col">
          <div class="card">
            <div class="table-responsive">
              <?php
              if (isset($_GET['retorno']) && $_GET['retorno'] == 1) {
                echo '<div class="p-2 alert-success rounded">Consulta registrada correctamente.</div>';
              }

              if (isset($_GET['buscar'])) {
                echo '<table class="table align-items-center table-flush">';
                echo '<thead class="thead-white">';
                echo '<tr>';
                  echo '<th scope="col">Materia</th>';
                  echo '<th scope="col">Fecha</th>';
                  echo '<th scope="col">Profesor</th>';
                  echo '<th scope="col">Día</th>';
                  echo '<th scope="col">Inicio - Fin</th>';
                  echo '<th scope="col">Anotarme</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody class="list">';

                $resultado = $conexion->prepare('CALL filtro_consultas( ?, ? )');
                $resultado->execute([$_GET['materia'], $_GET['profesor'] ]);
                $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

                if ($resultado->rowCount() > 0) {
                  echo ' <form class="form" action="" method="POST">';
                  foreach ($data as $fila) {
                    if ($fila["fecha"] == null) {
                      $new_date = 'Todos los ' . $fila["dia"];
                      $fecha_data = 'null';
                      $fecha_id = 'null';
                      $dia_data = $fila["dia"];
                      $hora_fin_data = $fila["hora_fin"];
                    } else {
                      $date = strtotime($fila["fecha"]);
                      $new_date = date('d-m-Y', $date);
                      $fecha_data = $fila["fecha"];
                      $fecha_id = str_replace('-', '', $fila["fecha"]);
                      $dia_data = '';
                      $hora_fin_data = '';
                    }

                    echo '<tr>';
                    echo '<td><b>' . htmlspecialchars($fila["nombre_materia"]) . '</b></td>';
                    echo '<td>' . htmlspecialchars($new_date) . '</td>';
                    echo '<td>' . htmlspecialchars($fila["nombre_profesor"]) . '</td>';
                    echo '<td>' . htmlspecialchars($fila["dia"]) . '</td>';
                    echo '<td>' . htmlspecialchars($fila["hora_ini"]) . ' - ' . htmlspecialchars($fila["hora_fin"]) .'</td>';

                    echo '<td> <input type="button" '
                                . 'id="aceptar' . $fecha_id . $fila["hora_ini"] . $fila["idconsultas_horario"] . '" '
                                . 'name="aceptar' . $fecha_id . $fila["hora_ini"] . $fila["idconsultas_horario"] . '" '
                                . 'data-fecha="' . htmlspecialchars($fecha_data) . '" '
                                . 'data-idconsultas_horario="' . htmlspecialchars($fila["idconsultas_horario"]) . '" '
                                . ($fecha_data === 'null' ? 'data-dia="' . htmlspecialchars($dia_data) . '" data-hora_fin="' . htmlspecialchars($hora_fin_data) . '" ' : '')
                                . 'class="btn btn-outline-primary btn-sm openModal" value="ANOTARME" '
                                . 'data-toggle="modal" data-target="#ModalDatosAlumnos" />'
                          . '</td>';
                    echo '</tr>';
                  }
                  echo '</form>';
                } else {
                  echo '<th colspan=6>No hay consultas para los filtros ingresados.</th>';
                }
                echo '</tbody>';
                echo '</table>';
              }
              ?>

              <div class="modal fade" id="ModalDatosAlumnos" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Datos alumnos </h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form action="controller/inscribir_consulta.php" method="POST">
                        <input type="hidden" id="fecha" name="fecha" value="">
                        <input type="hidden" id="idconsultas_horario" name="idconsultas_horario" value="">
                        <h6 class="heading-small text-muted mb-4">Información Alumno</h6>
                        <div class="pl-lg-4">
                          <div class="row">
                            <div class="col-lg-12">
                              <div class="form-group">
                                <label class="form-control-label" for="input-email">Dirección de correo</label>
                                <input required type="email" id="input-email" name="correo" class="form-control" placeholder="Ingrese su email">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-lg-6">
                              <div class="form-group">
                                <label class="form-control-label" for="input-legajo">Legajo</label>
                                <input required type="number" name="legajo" id="input-legajo" class="form-control" placeholder="Ingrese Legajo">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-lg-6">
                              <div class="form-group">
                                <label class="form-control-label" for="input-first-name">Nombre</label>
                                <input required type="text"  name="nombre" id="input-first-name" class="form-control" placeholder="Ingrese su nombre" >
                              </div>
                            </div>
                            <div class="col-lg-6">
                              <div class="form-group">
                                <label class="form-control-label" for="input-last-name">Apellido</label>
                                <input required type="text"  name="apellido" id="input-last-name" class="form-control" placeholder="Ingrese su apellido">
                              </div>
                            </div>
                          </div>
                          </div>
                          <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal" name="cancelar">Cancelar</button>
                          <button type="submit" class="btn btn-primary" name="terminar">Terminar inscripcion</button>
                        </div>
                      </form>
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
  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/js-cookie/js.cookie.js"></script>
  <script src="../assets/vendor/jquery.scrollbar/jquery.scrollbar.min.js"></script>
  <script src="../assets/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js"></script>
  <script src="../plugins/sweetalert2/sweetalert2.all.min.js"></script>
  <script src="../assets/js/argon.js?v=1.2.0"></script>
  <script>
    function resetFiltros() {
      document.getElementById('buscar').reset();
      document.location.href = "index.php";
    }
    function filtrarPorMateria( filtrar ) {
      var id_materia = document.activeElement.value;
      $.get('index.php', { id_materia_filtro: id_materia }, function(data){
        $('#profesor').html($(data).find('#profesor').html());
      });
    }

    $(document).on("click", ".openModal", function () {
      var idconsultas_horario = document.activeElement.dataset.idconsultas_horario;
      var fecha = document.activeElement.dataset.fecha;
      // Si la fecha es null, calcular la próxima fecha del día correspondiente
      if (fecha === 'null') {
        var dia = document.activeElement.dataset.dia;
        var horaFin = document.activeElement.dataset.hora_fin;
        var diasSemana = {
          'lunes': 1,
          'martes': 2,
          'miércoles': 3,
          'miercoles': 3,
          'jueves': 4,
          'viernes': 5,
          'sábado': 6,
          'sabado': 6,
          'domingo': 0
        };
        var hoy = new Date();
        var diaActual = hoy.getDay(); // 0=domingo, 1=lunes, ...
        var diaConsulta = diasSemana[dia.toLowerCase()];
        var fechaConsulta = new Date(hoy);
        var diferencia = (diaConsulta - diaActual + 7) % 7;
        if (diferencia === 0) {
          // Es hoy, comparar hora fin
          var horaFinSplit = horaFin.split(":");
          var horaFinDate = new Date(hoy);
          horaFinDate.setHours(parseInt(horaFinSplit[0]), parseInt(horaFinSplit[1]), 0, 0);
          if (hoy < horaFinDate) {
            // Todavía no terminó, es hoy
            // fechaConsulta ya es hoy
          } else {
            // Ya pasó, es el próximo martes
            fechaConsulta.setDate(hoy.getDate() + 7);
          }
        } else {
          fechaConsulta.setDate(hoy.getDate() + diferencia);
        }
        // Formatear a yyyy-mm-dd
        var yyyy = fechaConsulta.getFullYear();
        var mm = (fechaConsulta.getMonth() + 1).toString().padStart(2, '0');
        var dd = fechaConsulta.getDate().toString().padStart(2, '0');
        fecha = yyyy + '-' + mm + '-' + dd;
      }
      $(".modal-body #fecha").val( fecha );
      $(".modal-body #idconsultas_horario").val( idconsultas_horario );
    });
  </script>

  <?php include("vistas/componentes/footer.php") ?>
</body>
</html>
