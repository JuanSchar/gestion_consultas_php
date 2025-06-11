<?php 
session_start();
include_once '../bd/conexion.php';

try {
  $objeto = new Conexion();
  $conexion = $objeto->Conectar();
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

  header("Location: ../index.php?retorno=1");
  exit;

} catch (Exception $e) {
  if ($conexion->inTransaction()) {
    $conexion->rollBack();
  }
  echo '{"error":{"text":' . json_encode($e->getMessage()) . '}}';
}
?>
