<?php 
session_start();
include_once '../bd/conexion.php';

try {
  $objeto = new Conexion();
  $conexion = $objeto->Conectar();
  if ($conexion === null) {
    throw new Exception("No se pudo conectar a la base de datos.");
  }
  $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Obtener datos del formulario
  $idconsultas_horario = $_POST['idconsultas_horario'] ?? '';
  $idtiempo = $_POST['fecha'] ?? null;
  if ($idtiempo === '' || $idtiempo === 'null') {
    $idtiempo = null;  // Pasa valor NULL en vez de string vacío
  }
  $legajo = $_POST['legajo'] ?? '';
  $nombre = $_POST['nombre'] ?? '';
  $apellido = $_POST['apellido'] ?? '';
  $correo = $_POST['correo'] ?? '';

  // Iniciar transacción
  $conexion->beginTransaction();

  // 1. Insertar alumno si no existe
  $stmt1 = $conexion->prepare("
    INSERT INTO alumno (legajo, nombre, apellido, correo)
    SELECT j.legajo, j.nombre, j.apellido, j.correo
    FROM (
      SELECT ? AS legajo, UPPER(?) AS nombre, UPPER(?) AS apellido, ? AS correo
    ) j
    LEFT JOIN alumno a ON j.legajo = a.legajo
    WHERE a.legajo IS NULL
  ");
  $stmt1->execute([$legajo, $nombre, $apellido, $correo]);

  // 2. Actualizar si ya existe
  $stmt2 = $conexion->prepare("
    UPDATE alumno a
    JOIN (
      SELECT ? AS legajo, UPPER(?) AS nombre, UPPER(?) AS apellido, ? AS correo
    ) j ON j.legajo = a.legajo
    SET a.nombre = j.nombre,
      a.apellido = j.apellido,
      a.correo = j.correo
  ");
  $stmt2->execute([$legajo, $nombre, $apellido, $correo]);

  // 3. Obtener idalumno
  $stmt3 = $conexion->prepare("SELECT idalumno FROM alumno WHERE legajo = ?");
  $stmt3->execute([$legajo]);
  $idalumno = $stmt3->fetchColumn();

  if (!$idalumno) {
    throw new Exception("No se encontró el alumno con legajo: $legajo");
  }

  // 4. Insertar la consulta
  $stmt4 = $conexion->prepare("
    INSERT INTO consultas(idalumno, estado, idconsultas_horario, fecha)
    VALUES (?, 'Pendiente', ?, ?)
  ");
  $retorno = $stmt4->execute([$idalumno, $idconsultas_horario, $idtiempo]);

  // Confirmar transacción
  $conexion->commit();

  // Enviar email al profesor y copia al alumno
  require_once 'enviar_mail.php';
  $stmt5 = $conexion->prepare('
    SELECT ch.dia, ch.hora_ini, ch.hora_fin, m.nombre_materia, p.nombre_profesor, p.correo as correo_profesor
    FROM consultas_horario ch
    INNER JOIN materia m ON m.idmateria = ch.idmateria
    INNER JOIN profesor p ON p.idprofesor = ch.idprofesor
    WHERE ch.idconsultas_horario = ?
  ');
  $stmt5->execute([$idconsultas_horario]);
  $info = $stmt5->fetch(PDO::FETCH_OBJ);

  $asunto = "=?UTF-8?B?" . base64_encode("Nueva inscripción a consulta") . "?=";
  $cuerpo = '
    <html><body>
      <h2>Inscripción a consulta UTN</h2>
      <p>El alumno <b>' . htmlspecialchars($nombre) . ' ' . htmlspecialchars($apellido) . '</b> (Legajo: ' . htmlspecialchars($legajo) . ', Email: ' . htmlspecialchars($correo) . ') se inscribió en la consulta de <b>' . htmlspecialchars($info->nombre_materia) . '</b> con el profesor <b>' . htmlspecialchars($info->nombre_profesor) . '</b>.<br>
      Día: <b>' . htmlspecialchars($info->dia) . '</b><br>
      Horario: <b>' . htmlspecialchars($info->hora_ini) . ' - ' . htmlspecialchars($info->hora_fin) . '</b><br>
      Fecha: <b>' . htmlspecialchars($idtiempo) . '</b></p>
    </body></html>';

  enviarMail($info->correo_profesor, $asunto, $cuerpo, $correo);

  header("Location: ../index.php?retorno=1");
  exit;

} catch (Exception $e) {
  if (isset($conexion) && $conexion instanceof PDO && $conexion->inTransaction()) {
    $conexion->rollBack();
  }
  echo '{"error":{"text":' . json_encode($e->getMessage()) . '}}';
}
?>
