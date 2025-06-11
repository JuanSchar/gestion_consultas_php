<?php include("../auth.php"); ?>
<!DOCTYPE html>

<head>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Gestor de consultas UTN</title>
  <link rel="stylesheet" href="\bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../plugins/sweetalert2/sweetalert2.min.css">
</head>

<body>
  <link rel="stylesheet" href="../plugins/sweetalert2/sweetalert2.min.css">
  <script src="../plugins/sweetalert2/sweetalert2.all.min.js"></script>

  <?php include("../vistas/componentes/sidebar.php") ?>
  <div class="main-content" id="panel">
    <?php include("../vistas/componentes/navbar.php") ?>
    <?php $title = "Carga de horas"; include("../vistas/componentes/header.php") ?>

    <div class="container-fluid pt-4">
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-header border-0">
              <h3>Listado de consultas pendientes de aprobación</h3>
            </div>

            <div class="card-body">
              <div class="pl-lg-4">
                <div class="row">
                  <div class="col-lg-12">
                    <form action="?" method="post" enctype="multipart/form-data">
                      <label>Seleccione el archivo a subir</label>
                      <p><input class="form-control" placeholder="Seleccione el archivo a subir" type="file" name="file" /> </p>
                      <p><input type="submit" name="upload" class="btn btn-primary btn-block" onclick="swal()" value="ACTUALIZAR HORAS DE CONSULTA" /> </p>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function showModal() {
      Swal.fire({
        title: 'Excel subido!',
        text: "¡Las horas fueron actualizadas!",
        type: 'success',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK!'
      }).then((result) => {
        if (result.value) {
          window.location.href = "../vistas/dashboard.php";
        }
      })
    }
  </script>

  <?php
  // Configuración de errores
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_log', __DIR__.'/upload_errors.log');

  require '../vendor/autoload.php';
  require '../bd/conexion.php';

  if (isset($_POST['upload'])) {
    try {
      // Validación de archivo
      if (!isset($_FILES['file']['error']) || is_array($_FILES['file']['error'])) {
        throw new Exception('Parámetros de archivo inválidos');
      }

      // Mover archivo a directorio seguro
      $uploadDir = "../upload-excel/";
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      $fileName = uniqid().'_'.$_FILES['file']['name'];
      $filePath = $uploadDir.$fileName;
      
      if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        throw new Exception('Error al guardar el archivo');
      }

      // Conexión a base de datos (asegurando que $conexion esté definida)
      $objeto = new Conexion();
      $conexion = $objeto->Conectar();
      
      if (!$conexion) {
        throw new Exception('No se pudo establecer conexión con la base de datos');
      }

      // Configurar colación consistente
      $conexion->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");

      // Cargar archivo Excel
      $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
      $sheet = $spreadsheet->getActiveSheet();
      $numRows = $sheet->getHighestRow();

      // Iniciar transacción
      $conexion->beginTransaction();

      // Crear tabla temporal con colación explícita
      $conexion->exec("DROP TEMPORARY TABLE IF EXISTS TMP_consultas");
      $conexion->exec("
        CREATE TEMPORARY TABLE TMP_consultas (
          legajo INT, 
          cod_materia VARCHAR(45) COLLATE utf8mb4_general_ci, 
          dia VARCHAR(45) COLLATE utf8mb4_general_ci, 
          hora_inicio VARCHAR(45), 
          min_inicio VARCHAR(45),
          hora_fin VARCHAR(45),
          min_fin VARCHAR(45),
          id_dia INT
        ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
      ");

      // Preparar statement para inserción temporal
      $insertTemp = $conexion->prepare("
        INSERT INTO TMP_consultas 
        (legajo, cod_materia, dia, hora_inicio, min_inicio, hora_fin, min_fin, id_dia) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
      ");

      // Procesar filas del Excel
      for ($i = 2; $i <= $numRows; $i++) {
        $legajo = $sheet->getCell('A'.$i)->getCalculatedValue();
        $materia = $sheet->getCell('B'.$i)->getCalculatedValue();
        $inicioHora = $sheet->getCell('C'.$i)->getCalculatedValue();
        $inicioMin = $sheet->getCell('D'.$i)->getCalculatedValue() ?: '00';
        $finHora = $sheet->getCell('E'.$i)->getCalculatedValue();
        $finMin = $sheet->getCell('F'.$i)->getCalculatedValue() ?: '00';
        $dia = $sheet->getCell('G'.$i)->getCalculatedValue();
        $id_dia = $sheet->getCell('H'.$i)->getCalculatedValue();

        $insertTemp->execute([
          $legajo, $materia, $dia, 
          $inicioHora, $inicioMin, 
          $finHora, $finMin, $id_dia
        ]);
      }

      // Consulta final con manejo de colaciones
      $insertFinal = $conexion->prepare("
        INSERT INTO consultas_horario 
        (idprofesor, idmateria, dia, hora_ini, Hora_fin, estado, Fecha_carga, id_dia)
        SELECT 
          p.idprofesor, 
          m.idmateria,
          c.dia, 
          CONCAT(c.hora_inicio, ':', c.min_inicio), 
          CONCAT(c.hora_fin, ':', c.min_fin), 
          'Aceptada', 
          CURRENT_DATE(), 
          c.id_dia
        FROM TMP_consultas c 
        LEFT JOIN profesor p ON c.legajo = p.legajo COLLATE utf8mb4_general_ci
        LEFT JOIN materia m ON m.cod_materia = c.cod_materia COLLATE utf8mb4_general_ci
        WHERE p.idprofesor IS NOT NULL AND m.idmateria IS NOT NULL
      ");
      $insertFinal->execute();
      $rowCount = $insertFinal->rowCount();
      $conexion->commit();

      echo "<script> showModal();</script>";
    } catch (Exception $e) {
      // Rollback en caso de error
      if (isset($conexion)) {
        $conexion->rollBack();
      }
      error_log("Error: " . $e->getMessage());
      echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    } finally {
      // Eliminar archivo temporal
      if (isset($filePath) && file_exists($filePath)) {
        unlink($filePath);
      }
    }
  }

  // borro las variables porque si no cuando volves a entrar no se limpian
  $vars = array_keys(get_defined_vars());
  foreach ($vars as $var) {
    unset(${"$var"});
  }
  ?>
</body>

</html>