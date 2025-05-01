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

  foreach ($idconsultas_horario as $item) {
    $parts = explode('/', $item);
    $idconsultahorario = $parts[0];
    $fecha_bloqueo = $parts[1];
    
    $sql .= "INSERT INTO consultas_horarios_bloqueos(idconsultas_horario, fecha_bloqueo, motivo) 
             VALUES (?, ?, ?);";
    array_push($params, $idconsultahorario, $fecha_bloqueo, $motivo);
  }

  $sql .= "COMMIT;";

  $resultado = $conexion->prepare($sql);
  $retorno = $resultado->execute($params);
  header("Location: ../vistas/cancelacion_consultas.php?retorno=" . $retorno);

} catch (PDOException $e) {
  echo '{"error":{"text":'. $e->getMessage() .'}}';
}
?>
