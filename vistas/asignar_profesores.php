<?php include("../auth.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Asignar Profesores a Materias</title>
  <link rel="stylesheet" href="../estilos.css" type="text/css">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
  <?php include("componentes/sidebar.php") ?>
  <?php include("../bd/conexion.php") ?>
  <div class="main-content" id="panel">
    <?php include("componentes/navbar.php") ?>
    <div class="container pt-4">
      <h2>Asignar Profesores a Materias</h2>
      <?php
        // Obtener materias y profesores
        $objeto = new Conexion();
        $conexion = $objeto->Conectar();
        $materias = $conexion->query("SELECT * FROM materia")->fetchAll(PDO::FETCH_ASSOC);
        $profesores = $conexion->query("SELECT * FROM profesor")->fetchAll(PDO::FETCH_ASSOC);
        // Determinar materia seleccionada
        $id_materia_seleccionada = isset($_GET['id_materia']) ? $_GET['id_materia'] : (isset($materias[0]['idmateria']) ? $materias[0]['idmateria'] : null);
        // Procesar asignación solo si es POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['asignar'])) {
            $id_materia = $_POST['id_materia'];
            $ids_profesores = $_POST['ids_profesores'];
            $conexion->prepare("DELETE FROM materias_profesores WHERE idmateria = ?")->execute([$id_materia]);
            foreach ($ids_profesores as $id_profesor) {
                $conexion->prepare("INSERT INTO materias_profesores (idmateria, idprofesor) VALUES (?, ?)")->execute([$id_materia, $id_profesor]);
            }
            echo '<div class="alert alert-success">Profesores asignados correctamente.</div>';
            $id_materia_seleccionada = $id_materia;
        }
        // Obtener profesores ya asignados a la materia seleccionada
        $profesores_asignados = [];
        if ($id_materia_seleccionada) {
            $stmt = $conexion->prepare("SELECT idprofesor FROM materias_profesores WHERE idmateria = ?");
            $stmt->execute([$id_materia_seleccionada]);
            $profesores_asignados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
      ?>
      <form method="GET" class="mb-3">
        <div class="form-group">
          <label>Materia</label>
          <select name="id_materia" class="form-control" onchange="this.form.submit()">
            <?php foreach ($materias as $m) {
              $selected = ($m['idmateria'] == $id_materia_seleccionada) ? 'selected' : '';
              echo '<option value="' . $m['idmateria'] . '" ' . $selected . '>' . $m['nombre_materia'] . '</option>';
            } ?>
          </select>
        </div>
      </form>
      <form method="POST">
        <input type="hidden" name="id_materia" value="<?php echo htmlspecialchars($id_materia_seleccionada); ?>">
        <div class="form-group">
          <label>Profesores</label>
          <select id="select-profesores" name="ids_profesores[]" class="form-control" multiple required>
            <?php foreach ($profesores as $p) {
              $selected = in_array($p['idprofesor'], $profesores_asignados) ? 'selected' : '';
              echo '<option value="' . $p['idprofesor'] . '" ' . $selected . '>' . $p['nombre_profesor'] . '</option>';
            } ?>
          </select>
        </div>
        <button type="submit" name="asignar" class="btn btn-success">Asignar</button>
      </form>
    </div>
  </div>
  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#select-profesores').select2({
        placeholder: 'Seleccione uno o más profesores',
        width: '100%'
      });
    });
  </script>
  <script src="../assets/vendor/js-cookie/js.cookie.js"></script>
  <script src="../assets/js/argon.js?v=1.2.0"></script>
  <script src="../codigo.js"></script>
</body>
</html>
