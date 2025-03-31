<?php 
session_start();

include_once '../bd/conexion.php';
try {
$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Recepcion de datos enviados mediante POST dsd ajax 
$fila = (isset($_POST['fila'])) ? $_POST['fila'] : '';
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : '';

$estado = $accion==1? 'Aceptada': 'Rechazada';
$resultado = $conexion->prepare('UPDATE consultas_horario c SET c.estado = ? WHERE c.idconsultas_horario = ?');
$retorno = $resultado->execute([$estado, $fila]);

print $retorno;
$conexion=null;

} catch (PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
 
}
?>

