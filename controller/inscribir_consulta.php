<?php 
session_start();

include_once '../bd/conexion.php';
try {
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$idconsultas_horario = (isset($_POST['idconsultas_horario'])) ? $_POST['idconsultas_horario'] : '';
$idtiempo = (isset($_POST['fecha'])) ? $_POST['fecha'] : '';
$legajo =(isset($_POST['legajo'])) ? $_POST['legajo'] : '' ;
$nombre =(isset($_POST['nombre'])) ? $_POST['nombre'] : '' ;
$apellido =(isset($_POST['apellido'])) ? $_POST['apellido'] : '' ;
$correo =(isset($_POST['correo'])) ? $_POST['correo'] : '' ; 

$resultado = $conexion->prepare(' 
START TRANSACTION;

insert into alumno(legajo, nombre, correo)
select j.legajo, j.nombre, j.correo from (select ? legajo, upper(concat(?, " ", ? ))nombre, ? correo) j
left join alumno a on j.legajo=a.legajo
where a.legajo is null;

update  alumno a
join  (select ? legajo, upper(concat(?, " ", ? ))nombre, ? correo) j on j.legajo=a.legajo
set a.nombre= j.nombre, 
a.correo= j.correo;

COMMIT;
START TRANSACTION;
set @idalumno = (select idalumno from alumno where legajo = ?);

insert into consultas( idalumno, estado, idconsultas_horario, fecha)
select @idalumno, "Pendiente", ? , ?
from alumno a 
Where a.legajo=?;
COMMIT;

');
$retorno = $resultado->execute([$legajo, $nombre, $apellido, $correo, $legajo, $nombre, $apellido, $correo, $legajo, $idconsultas_horario, $idtiempo, $legajo ]);

print $retorno;

header("Location: ../index.php?retorno=" . $retorno);

} catch (PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
 
}
?>

