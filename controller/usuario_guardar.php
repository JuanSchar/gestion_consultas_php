<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
  header('Location: ../index.php');
  exit();
}
require_once '../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();
$id = isset($_POST['idusuario']) ? intval($_POST['idusuario']) : 0;
$usuario = trim($_POST['usuario']);
$password = trim($_POST['password']);
$rol = intval($_POST['rol']);
$idprofesor = $_POST['idprofesor'] !== '' ? intval($_POST['idprofesor']) : 'NULL';
$idalumno = $_POST['idalumno'] !== '' ? intval($_POST['idalumno']) : 'NULL';

try {
  // Limpiar campos según el rol
  if ($rol == 2) { // Profesor
    $idalumno = null;
  } elseif ($rol == 3) { // Alumno
    $idprofesor = null;
  } else { // Admin
    $idprofesor = null;
    $idalumno = null;
  }

  if ($id) {
    // Editar
    if ($password) {
      $hash = md5($password);
      $sql = "UPDATE usuarios SET usuario=?, password=?, rol=?, idprofesor=?, idalumno=? WHERE idusuario=?";
      $stmt = $conexion->prepare($sql);
      $stmt->execute([$usuario, $hash, $rol,
        $idprofesor !== 'NULL' ? $idprofesor : null,
        $idalumno !== 'NULL' ? $idalumno : null,
        $id
      ]);
    } else {
      $sql = "UPDATE usuarios SET usuario=?, rol=?, idprofesor=?, idalumno=? WHERE idusuario=?";
      $stmt = $conexion->prepare($sql);
      $stmt->execute([$usuario, $rol,
        $idprofesor !== 'NULL' ? $idprofesor : null,
        $idalumno !== 'NULL' ? $idalumno : null,
        $id
      ]);
    }
  } else {
    // Nuevo
    $hash = md5($password);
    $sql = "INSERT INTO usuarios (usuario, password, rol, idprofesor, idalumno) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$usuario, $hash, $rol,
      $idprofesor !== 'NULL' ? $idprofesor : null,
      $idalumno !== 'NULL' ? $idalumno : null
    ]);
  }
  $stmt = null;
  $conexion = null;
  header('Location: ../vistas/usuarios.php');
  exit();
} catch (PDOException $e) {
  if ($e->getCode() == 23000) { // Duplicado
    $params = http_build_query([
      'error' => 'usuario',
      'usuario' => $usuario,
      'rol' => $rol,
      'idprofesor' => $idprofesor !== 'NULL' ? $idprofesor : '',
      'idalumno' => $idalumno !== 'NULL' ? $idalumno : ''
    ]);
    $redir = '../vistas/usuario_form.php' . ($id ? ('?id=' . $id . '&' . $params) : ('?' . $params));
    header('Location: ' . $redir);
    exit();
  } else {
    throw $e;
  }
}
