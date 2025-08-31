<?php 
session_start();

include_once '../bd/conexion.php';

try {
  $objeto = new Conexion();
  $conexion = $objeto->Conectar();

  $idconsultas_horario = $_POST['idconsultas_horario'] ?? '';
  $motivo = $_POST['motivo'] ?? '';
  $sql = "START TRANSACTION;";
  $params = array();

  require_once 'enviar_mail.php';
  foreach ($idconsultas_horario as $item) {
    $parts = explode('/', $item);
    $idconsultahorario = $parts[0];
    $fecha_bloqueo = $parts[1];

    $sql .= "INSERT INTO consultas_horarios_bloqueos(idconsultas_horario, fecha_bloqueo, motivo) 
             VALUES (?, ?, ?);";
    array_push($params, $idconsultahorario, $fecha_bloqueo, $motivo);

    // Obtener alumnos anotados en esa consulta y fecha
    $stmt = $conexion->prepare('
      SELECT a.correo, a.nombre, a.apellido, m.nombre_materia, p.nombre_profesor, ch.hora_ini, ch.hora_fin
      FROM consultas c
      INNER JOIN alumno a ON a.idalumno = c.idalumno
      INNER JOIN consultas_horario ch ON ch.idconsultas_horario = c.idconsultas_horario
      INNER JOIN materia m ON m.idmateria = ch.idmateria
      INNER JOIN profesor p ON p.idprofesor = ch.idprofesor
      WHERE c.idconsultas_horario = ? AND c.fecha = ? AND (c.estado = "Pendiente" OR c.estado = "Confirmado")
    ');
    $stmt->execute([$idconsultahorario, $fecha_bloqueo]);
    $alumnos = $stmt->fetchAll(PDO::FETCH_OBJ);

    foreach ($alumnos as $alumno) {
      $asunto = "=?UTF-8?B?" . base64_encode("Cancelación de consulta UTN") . "?=";
      $cuerpo = '
        <html><body>
          <h2>Cancelación de consulta UTN</h2>
          <p>Estimado/a <b>' . htmlspecialchars($alumno->nombre) . ' ' . htmlspecialchars($alumno->apellido) . '</b>,<br>
          Lamentamos informarte que la consulta de <b>' . htmlspecialchars($alumno->nombre_materia) . '</b> con el profesor <b>' . htmlspecialchars($alumno->nombre_profesor) . '</b> prevista para el día <b>' . htmlspecialchars($fecha_bloqueo) . '</b> en el horario <b>' . htmlspecialchars($alumno->hora_ini) . ' - ' . htmlspecialchars($alumno->hora_fin) . '</b> ha sido cancelada.<br>
          Motivo: <b>' . htmlspecialchars($motivo) . '</b></p>
        </body></html>';
      $resultado_envio = enviarMail($alumno->correo, $asunto, $cuerpo);
      $log_line = date('Y-m-d H:i:s') . " | " . $alumno->correo . " | " . ($resultado_envio ? "OK" : "ERROR") . "\n";
      file_put_contents(__DIR__ . '/../logs/cancelacion_mails.log', $log_line, FILE_APPEND);
    }
  }

  $sql .= "COMMIT;";

  $resultado = $conexion->prepare($sql);
  $retorno = $resultado->execute($params);
  header("Location: ../vistas/cancelacion_consultas.php?retorno=" . $retorno);

} catch (PDOException $e) {
  echo '{"error":{"text":'. $e->getMessage() .'}}';
}
?>
