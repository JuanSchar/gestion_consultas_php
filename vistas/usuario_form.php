<?php include("../auth.php"); ?>
<?php
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
  header('Location: ../index.php');
  exit();
}
require_once '../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Recuperar valores si hay error
$usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';
$rol = isset($_GET['rol']) ? $_GET['rol'] : '';
$idprofesor = isset($_GET['idprofesor']) ? $_GET['idprofesor'] : '';
$idalumno = isset($_GET['idalumno']) ? $_GET['idalumno'] : '';
if ($id && !isset($_GET['usuario'])) {
  $sql = "SELECT * FROM usuarios WHERE idusuario = $id";
  $res = $conexion->query($sql);
  if ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $usuario = $row['usuario'];
    $idprofesor = $row['idprofesor'];
    $idalumno = $row['idalumno'];
    $rol = $row['rol'];
  }
}
$profesores = $conexion->query("SELECT idprofesor, nombre_profesor FROM profesor");
$alumnos = $conexion->query("SELECT idalumno, legajo, nombre, apellido FROM alumno");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= $id ? 'Editar Usuario' : 'Nuevo Usuario' ?></title>
  <link rel="stylesheet" href="../estilos.css" type="text/css">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
  <?php include("componentes/sidebar.php") ?>
  <div class="main-content" id="panel">
    <?php include("componentes/navbar.php") ?>
    <?php $title = $id ? 'Editar Usuario' : 'Nuevo Usuario' ?>
    <?php include("componentes/header.php") ?>
    <div class="container-fluid pt-4">
      <?php if (isset($_GET['error']) && $_GET['error'] === 'usuario'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong>¡Error!</strong> El nombre de usuario ya existe. Por favor, elige otro nombre de usuario.
          <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-header border-0">
              <h3 class="mb-0"><?= $id ? 'Editar Usuario' : 'Nuevo Usuario' ?></h3>
            </div>

            <div class="card-body pt-1">
              <form method="post" action="../controller/usuario_guardar.php">
                <input type="hidden" name="idusuario" value="<?= $id ?>">
                <div class="pl-lg-12">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label>Usuario</label>
                        <input type="text" name="usuario" class="form-control" required value="<?= htmlspecialchars($usuario) ?>">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label>Contraseña <?= $id ? '(dejar en blanco para no cambiar)' : '' ?></label>
                        <input type="password" name="password" class="form-control" <?= $id ? '' : 'required' ?> >
                      </div>
                    </div>
                  
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label>Rol</label>
                        <select name="rol" class="form-control" required>
                          <option value="1" <?= $rol==1?'selected':'' ?>>Admin</option>
                          <option value="2" <?= $rol==2?'selected':'' ?>>Profesor</option>
                          <option value="3" <?= $rol==3||$rol==''?'selected':'' ?>>Alumno</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group" id="campo-profesor">
                        <label>Profesor (solo si es profesor)</label>
                        <select name="idprofesor" class="form-control">
                          <option value="">-- Ninguno --</option>
                          <?php $profesores->execute(); while($p = $profesores->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?= $p['idprofesor'] ?>" <?= $idprofesor==$p['idprofesor']?'selected':'' ?>><?= htmlspecialchars($p['nombre_profesor']) ?></option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <div class="form-group" id="campo-alumno">
                        <label>Alumno (solo si es alumno)</label>
                        <select name="idalumno" class="form-control">
                          <option value="">-- Ninguno --</option>
                          <?php while($a = $alumnos->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?= $a['idalumno'] ?>" <?= $idalumno==$a['idalumno']?'selected':'' ?>>Legajo: <?= htmlspecialchars($a['legajo']) ?> - <?= htmlspecialchars($a['nombre']) ?> <?= htmlspecialchars($a['apellido']) ?></option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                    </div>
                    
                    <div class="col-12 col-sm-12 col-lg-2">
                      <div class="form-group">
                        <button type="submit" name="guardar" class="btn btn-outline-primary">Guardar</button>
                        <a href="usuarios.php" class="btn btn-outline-secondary">Volver</a>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="../assets/vendor/js-cookie/js.cookie.js"></script>
  <script src="../assets/js/argon.js?v=1.2.0"></script>
  <script src="../codigo.js"></script>
  <script src="../vistas/js/usuario_form.js"></script>
</body>
</html>
