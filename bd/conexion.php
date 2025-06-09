<?php
class Conexion {
  public static function Conectar() {
    define('servidor', 'db'); // nombre del servicio en docker-compose.yml
    define('nombre_bd', 'gestion_consultas');
    define('usuario', 'userdb');
    define('password', 'userdbpass');
    $opciones = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', PDO::ATTR_PERSISTENT => true);
    try {
      $conexion = new PDO("mysql:host=".servidor.";dbname=".nombre_bd, usuario, password, $opciones);
      return $conexion;
    } catch (Exception $e) {
      die("El error de Conexión es :".$e->getMessage());
    }
  }
}
?>
