<?php
class Conexion {
  public static function Conectar() {
    define('servidor', $_ENV['MYSQL_HOST'] ?? 'db');
    define('nombre_bd', $_ENV['MYSQL_DATABASE'] ?? 'gestion_consultas');
    define('usuario', $_ENV['MYSQL_USER'] ?? 'root');
    define('password', $_ENV['MYSQL_PASSWORD'] ?? 'secret');
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
