<?php 
session_start();

include_once 'conexion.php';
try {
  $objeto = new Conexion();
  $conexion = $objeto->Conectar();

  // Recepcion de datos enviados mediante POST dsd ajax 
  $usuario = (isset($_POST['usuario'])) ? $_POST['usuario'] : '';
  $password = (isset($_POST['password'])) ? $_POST['password'] : '';

  $pass = md5($password); //encripto la clave enviada por el usuario para compararla con la clave encriptada y almacenada en la BD

  $resultado = $conexion->prepare('SELECT u.idusuario, u.usuario, u.rol, p.legajo, p.nombre_profesor, p.correo, p.idprofesor FROM usuarios u LEFT JOIN profesor p on p.idprofesor = u.idprofesor WHERE u.usuario = ? AND u.password = ?');
  $resultado->execute([$usuario, $pass]);
  $_SESSION["s_usuario"] = null;
  $_SESSION["s_profesor"] = null;
  $_SESSION["s_nombre_profesor"] = null;
  $_SESSION["rol"] = null;

  $data = $resultado->fetch(PDO::FETCH_OBJ);

  if ( $resultado->rowCount() == 1 ) {
    $_SESSION["s_usuario"] = $data->usuario;
    $_SESSION["s_profesor"] = $data->idprofesor;
    $_SESSION["s_nombre_profesor"] = $data->nombre_profesor;
    $_SESSION["rol"] = $data->rol;
  }

  print json_encode($data);
  $conexion=null;
} catch (PDOException $e) {
  echo '{"error":{"text":'. $e->getMessage() .'}}';
}
?>

