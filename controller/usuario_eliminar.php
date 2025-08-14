<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
  header('Location: ../index.php');
  exit();
}
require_once '../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
  $sql = "DELETE FROM usuarios WHERE idusuario = ?";
  $stmt = $conexion->prepare($sql);
  $stmt->execute([$id]);
  $stmt = null;
}
$conexion = null;
header('Location: ../vistas/usuarios.php');
