<?php
  $nombre_usuario = $_SESSION['s_usuario'];
  // Si no se pasan, usar valores por defecto
  if (!isset($rol)) $rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : 1;
  if (!isset($param)) $param = ($rol == 3) ? (isset($_SESSION['s_idalumno']) ? $_SESSION['s_idalumno'] : null) : (isset($_SESSION['s_profesor']) ? $_SESSION['s_profesor'] : -1);

  switch ($cardnum) {
    case 1:
      $classcss = "bg-gradient-red";
      $title = "Consultas Pendientes";
      $letter = "P";
      if ($rol == 3) {
        // Alumno: solo consultas donde está inscripto y pendientes (consultas.idalumno)
        $sql = "SELECT COUNT(*) FROM consultas c WHERE c.idalumno = ? AND UPPER(c.estado) LIKE '%PENDIENTE%' AND c.fecha >= CURRENT_DATE();";
        $resultado = $conexion->prepare($sql);
        $resultado->execute([$param]);
      } else {
        $sql = "SELECT COUNT(*) 
                FROM consultas c  
                JOIN consultas_horario ch ON c.idconsultas_horario = ch.idconsultas_horario 
                LEFT JOIN usuarios u ON ch.idprofesor = u.idprofesor AND u.usuario = ?
                WHERE ch.idprofesor = CASE WHEN ? = 'admin' THEN ch.idprofesor ELSE u.idprofesor END 
                AND UPPER(c.estado) LIKE '%PENDIENTE%' AND c.fecha >= CURRENT_DATE();";
        $resultado = $conexion->prepare($sql);
        $resultado->execute([$nombre_usuario, $nombre_usuario]);
      }
      $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
      break;
    case 2:
      $classcss = "bg-gradient-orange";
      $title = "Consultas para hoy";
      $letter = "H";
      if ($rol == 3) {
        $sql = "SELECT COUNT(*)
            FROM consultas c
            JOIN consultas_horario ch ON c.idconsultas_horario = ch.idconsultas_horario
            WHERE c.idalumno = ?
              AND c.fecha = CURRENT_DATE()
              AND c.estado != 'Rechazado'
              AND NOT EXISTS (
                SELECT 1 FROM consultas_horarios_bloqueos chb
                WHERE chb.idconsultas_horario = ch.idconsultas_horario
                  AND chb.fecha_bloqueo = c.fecha
              )";
        $resultado = $conexion->prepare($sql);
        $resultado->execute([$param]);
      } else {
        $sql = "SELECT COUNT(*)
                FROM consultas c
                JOIN consultas_horario ch ON c.idconsultas_horario = ch.idconsultas_horario
                JOIN profesor p ON ch.idprofesor = p.idprofesor
                LEFT JOIN usuarios u ON ch.idprofesor = u.idprofesor AND u.usuario = ?
                WHERE (? = 'admin' OR u.usuario = ?)
                AND c.fecha = CURRENT_DATE();";
        $resultado = $conexion->prepare($sql);
        $resultado->execute([$nombre_usuario, $nombre_usuario, $nombre_usuario]);
      }
      $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
      break;
    case 3:
      $classcss = "bg-gradient-green";
      $title = "Consultas canceladas";
      $letter = "C";
      if ($rol == 3) {
        $sql = "SELECT COUNT(*) FROM consultas c WHERE c.idalumno = ? AND UPPER(c.estado) LIKE '%RECHAZADO%' AND c.fecha >= CURRENT_DATE();";
        $resultado = $conexion->prepare($sql);
        $resultado->execute([$param]);
      } else {
        $sql = "
          select count(*)
          from consultas c 
          join consultas_horario ch on c.idconsultas_horario=ch.idconsultas_horario
          left join usuarios u 
            on ch.idprofesor=u.idprofesor and u.usuario= ?
          where ch.idprofesor= case when ? ='admin' then ch.idprofesor else u.idprofesor end 
          and upper(c.estado) like 'RECHAZADO' and c.fecha>=current_date();
        ";
        $resultado = $conexion->prepare($sql);
        $resultado->execute([$nombre_usuario, $nombre_usuario]);
      }
      $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
      break;
    case 4: 
      $classcss = "bg-gradient-info";
      $title = "CONSULTAS ACEPTADAS";
      $letter = "A";
      $sql = "
      select count(*)
      from consultas c 
      join consultas_horario ch on c.idconsultas_horario=ch.idconsultas_horario
      left join usuarios u 
        on ch.idprofesor=u.idprofesor and u.usuario= ?
      where ch.idprofesor= case when ? ='admin' then ch.idprofesor else u.idprofesor end 
      and upper(c.estado) like '%CONFIRMADO%' and c.fecha>=current_date();
      ";

      $resultado = $conexion->prepare($sql);
      $resultado->execute([$nombre_usuario, $nombre_usuario]);
      $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
    break;
  }
?>

<div class="col-xl-3 col-md-6">
  <div class="card card-stats">
    <div class="card-body">
      <div class="row">
        <div class="col">
          <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $title ?></h5>
          <span class="h2 font-weight-bold mb-0">
            <?php 
            foreach ($data as $fila) {
              // El nombre de la columna puede variar por COUNT(*)
              echo isset($fila['count(*)']) ? $fila['count(*)'] : (isset($fila['COUNT(*)']) ? $fila['COUNT(*)'] : reset($fila));
            }
            ?>
          </span>
        </div>
        <div class="col-auto">
          <div class="icon icon-shape text-white rounded-circle shadow <?php echo $classcss ?>">
            <?php echo $letter ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
