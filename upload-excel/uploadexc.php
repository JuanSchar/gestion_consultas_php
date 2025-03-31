

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

<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js">
</script>
<script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="../assets/vendor/js-cookie/js.cookie.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.8.0/sweetalert2.min.css" rel="stylesheet" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.8.0/sweetalert2.min.js"></script>
 -->
<!--   <script src="../plugins/sweetalert2/sweetalert2.all.min.js"></script>
  <script src="../assets/js/argon.js?v=1.2.0"></script> -->

    <?php include("../vistas/componentes/sidebar.php") ?>
    <div class="main-content" id="panel">
        <?php include("../vistas/componentes/navbar.php") ?>
        <?php $title = "Carga de horas"; include("../vistas/componentes/header.php") ?>


        <div class="container-fluid mt--6">
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
    require '../vendor/autoload.php';
    require '../bd/conexion.php';

    if (isset($_POST['upload'])) {
        $file_name = $_FILES['file']['name'];
        $file_type = $_FILES['file']['type'];
        $file_size = $_FILES['file']['size'];
        $file_tem_loc = $_FILES['file']['tmp_name'];
        $file_store = ".." . $file_name;
        move_uploaded_file($file_tem_loc, $file_store);
        $objeto = new Conexion();

        $conexion = $objeto->Conectar();
        $array_consultas = array();

        //Variable con el nombre del archivo
        $nombreArchivo = "../upload-excel/" . $file_name;
        // Cargo la hoja de cálculo
        $objPHPExcel = PhpOffice\PhpSpreadsheet\IOFactory::load($nombreArchivo);

        //Asigno la hoja de calculo activa
        $objPHPExcel->setActiveSheetIndex(0);
        //Obtengo el numero de filas del archivo
        $numRows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();



        for ($i = 2; $i <= $numRows; $i++) {

            $legajo = $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getCalculatedValue();
            $materia = $objPHPExcel->getActiveSheet()->getCell('B' . $i)->getCalculatedValue();
            $inicioHora = $objPHPExcel->getActiveSheet()->getCell('C' . $i)->getCalculatedValue();
            $inicioMin = $objPHPExcel->getActiveSheet()->getCell('D' . $i)->getCalculatedValue();
            $finHora = $objPHPExcel->getActiveSheet()->getCell('E' . $i)->getCalculatedValue();
            $finMin = $objPHPExcel->getActiveSheet()->getCell('F' . $i)->getCalculatedValue();
            $dia = $objPHPExcel->getActiveSheet()->getCell('G' . $i)->getCalculatedValue();
            $id_dia = $objPHPExcel->getActiveSheet()->getCell('H' . $i)->getCalculatedValue();

           if($inicioMin == 0){
                $inicioMin = "00";
           }
           if($finMin == 0){
            $finMin = "00";
            }
            $sql = "
            start transaction;
            drop temporary table if exists TMP_consultas;
            create temporary table if not exists TMP_consultas (legajo int , 
                                                            cod_materia varchar(45), 
                                                            dia varchar(45), 
                                                            hora_inicio varchar(45), 
                                                            min_inicio varchar(45),
                                                            min_fin varchar(45),
                                                            hora_fin varchar(45),
                                                            id_dia int) ;
            INSERT INTO TMP_consultas (legajo, cod_materia,dia, hora_inicio, min_inicio, hora_fin, min_fin, id_dia) 
            VALUES('$legajo','$materia','$dia', '$inicioHora', '$inicioMin', '$finHora', '$finMin', '$id_dia');
            
            
            insert into consultas_horario (idprofesor,idmateria,dia, hora_ini, Hora_fin, estado, Fecha_carga, id_dia)
            select distinct  p.idprofesor, m.idmateria,dia, concat(c.hora_inicio,':',c.min_inicio), concat(c.hora_fin,':',c.min_fin), 'Aceptada', current_date(), c.id_dia
            from TMP_consultas c 
            left join profesor p 
                    on c.legajo=p.legajo
            left join materia m 
                    on upper(m.cod_materia)=upper(c.cod_materia);
            commit;
            ";
            $resultado = $conexion->prepare($sql);
            $resultado->execute();

            echo "<script> showModal(); </script>";
       
            
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