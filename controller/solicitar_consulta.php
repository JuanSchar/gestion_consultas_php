<?php 
session_start();

include_once '../bd/conexion.php';
try {
$objeto = new Conexion();
$conexion = $objeto->Conectar();
$materia = (isset($_POST['materia'])) ? $_POST['materia'] : '';
$dia = (isset($_POST['dia'])) ? $_POST['dia'] : '';
$idprofesor = $_SESSION["s_profesor"];
$nombre_dia = '';
$hora_desde = (isset($_POST['hora_desde'])) ? $_POST['hora_desde'] : '';
$hora_hasta = (isset($_POST['hora_hasta'])) ? $_POST['hora_hasta'] : '';
$fecha = (isset($_POST['fecha'])) ? $_POST['fecha'] : NULL;
$retorno = 1;
switch ($dia) {
    case 0:
        $nombre_dia = 'Lunes';
        break;
    case 1:
        $nombre_dia = 'Martes';
        break;
    case 2:
        $nombre_dia = 'Miercoles';
        break;
    case 3:
        $nombre_dia = 'Jueves';
        break;
    case 4:
        $nombre_dia = 'Viernes';
        break;
}

$sql = "START TRANSACTION;";
$sql .= "INSERT INTO 
            consultas_horario(dia, id_dia, idprofesor," . ($fecha? 'fecha_consulta,': '') . "hora_ini, hora_fin, idmateria, estado ) 
            VALUES( '". $nombre_dia . "'," . $dia . "," . $idprofesor . ",'" . ($fecha? $fecha. "','": "") . $hora_desde . "','" . $hora_hasta . "'," . $materia . ",'" . 'Pendiente' . "');";
$sql .= "COMMIT;"; 

$resultado = $conexion->prepare($sql);
$retorno = $resultado->execute();
header("Location: ../vistas/nueva_consulta.php?retorno=" . $retorno);

} catch (PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
 
}
