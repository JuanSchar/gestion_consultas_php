<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>UTN - Módulo gestión consultas</title>

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
                echo '<div class="correcto">Consulta registrada correctamente.</div>';
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
                    $date = strtotime($fila["fecha"]);
                    $new_date = date('d-m-Y', $date);
                    echo '<tr>';
                    echo '<td><b>' . $fila["nombre_materia"] . '</b></td>';
                    echo '<td>' .   $new_date . '</td>';
                    echo '<td>' . $fila["nombre_profesor"] . '</td>';
                    echo '<td>' . $fila["dia"] . '</td>';
                    echo '<td>' . $fila["hora_ini"] . '</td>';
                    echo ' <td> <input type="button" 
                                id="aceptar' . $fila["fecha"] . $fila["hora_ini"] . $fila["idconsultas_horario"] . '" 
                                name="aceptar' . $fila["fecha"] . $fila["hora_ini"] . $fila["idconsultas_horario"] . '" 
                                data-fecha=' . $fila["fecha"] . ' 
                                data-idconsultas_horario=' . $fila["idconsultas_horario"] . ' 
                                class="btn btn-outline-primary btn-sm openModal" value="ANOTARME"
                                data-toggle="modal" data-target="#ModalDatosAlumnos" />  
                            </td>';
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
      $(".modal-body #fecha").val( fecha );
      $(".modal-body #idconsultas_horario").val( idconsultas_horario );
    });
  </script>

  <?php include("vistas/componentes/footer.php") ?>
</body>
</html>
