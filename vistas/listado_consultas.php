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
                    <th scope="col">Fecha</th>
                    <th scope="col">Inicio - Fin</th>
                    <th scope="col">Alumno</th>
                    <th scope="col">Acción</th>
                  
                  </tr>
                </thead>
                <tbody class="list">
                <?php
                  $Cant_por_Pag = 5;
                  $objeto = new Conexion();
                  $conexion = $objeto->Conectar();
                  $resultado = $conexion->prepare('SELECT * FROM consultas_pendientes_aprobacion;');
                  $resultado->execute();
                  $pagina = isset ( $_GET['pagina']) ? $_GET['pagina'] : null ;
                  if (!$pagina) {
                  $inicio = 0;
                  $pagina=1;
                  }
                  else {
                  $inicio = ($pagina - 1) * $Cant_por_Pag;
                  }
                  $total_registros= $resultado->rowCount();
                  $total_paginas = ceil($total_registros/ $Cant_por_Pag);
                  $resultado = $conexion->prepare('SELECT * FROM consultas_pendientes_aprobacion LIMIT ' . $inicio . ',' . $Cant_por_Pag . ';');
                  $resultado->execute();
                  $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

                  if ($resultado->rowCount() > 0) {
                    echo '<form id="accionBoton" class="form" action="" method="POST">';
                    foreach($data as $fila) {
                      $date = strtotime($fila["fecha"]);
                      $new_date = date('d-m-Y', $date);
                      echo '<tr>';
                        echo '<td><b>' . $fila["nombre_materia"] . '</b></td>';
                        echo '<td>' . $new_date . '</td>';
                        echo '<td>' . $fila["hora_ini_fin"] . '</td>';
                        echo '<td>' . $fila["nombre"] . '</td>';
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

for ($i=1;$i<=$total_paginas;$i++){
  
  echo '<li class="page-item ';
  echo ($pagina == $i) ?  'active': '';
  echo '">';
  echo '<a class="page-link" href="listado_consultas.php?pagina=' . $i . '">' . $i . '</a>';
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
    
  </div>
  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/js-cookie/js.cookie.js"></script>
  <script src="../assets/vendor/jquery.scrollbar/jquery.scrollbar.min.js"></script>
  <script src="../assets/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js"></script>
  <script src="../assets/js/argon.js?v=1.2.0"></script>
  <script src="../codigo.js"></script>


</body>




</html>