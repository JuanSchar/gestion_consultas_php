
<?php
$nombre_usuario = $_SESSION['s_usuario'];

switch ($cardnum) {
    case 1:
        $classcss = "bg-gradient-red";
        $title = "Consultas Pendientes";
        $letter = "P";

        $sql = "select count(*) 
                from consultas c  
                join consultas_horario ch 
                    on c.idconsultas_horario=ch.idconsultas_horario 
                left join usuarios u 
                    on ch.idprofesor=u.idprofesor and u.usuario= ?
        where ch.idprofesor= case when ? ='admin' then ch.idprofesor else u.idprofesor end 
        and upper(c.estado) like '%PENDIENTE%' and c.fecha>=current_date();   
       ";
            $resultado = $conexion->prepare($sql);
            $resultado->execute([$nombre_usuario, $nombre_usuario]);
            $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 2:
        $classcss = "bg-gradient-orange";
        $title = "Consultas para hoy";
        $letter = "H";
        $sql = " select count(*) from consultas c 
        join consultas_horario ch on c.idconsultas_horario=ch.idconsultas_horario
        join profesor p on ch.idprofesor=p.idprofesor
        left join usuarios u 
            on ch.idprofesor=u.idprofesor and u.usuario=?
        where ch.idprofesor= case when ? ='admin' then ch.idprofesor else u.idprofesor end 
        and c.fecha=current_date();
        ";
        $resultado = $conexion->prepare($sql);
        $resultado->execute([$nombre_usuario, $nombre_usuario]);
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        if ($resultado->rowCount() == 0) {
            echo '<label class="text-white">ERROR</label>';
        }
        break;

    case 3:
        $classcss = "bg-gradient-green";
        $title = "Consultas canceladas";
        $letter = "C";
        $sql = "
        select count(*)
        from consultas c 
        join consultas_horario ch on c.idconsultas_horario=ch.idconsultas_horario
        left join usuarios u 
            on ch.idprofesor=u.idprofesor and u.usuario= ?
        where ch.idprofesor= case when ? ='admin' then ch.idprofesor else u.idprofesor end 
        and upper(c.estado) like 'RECHAZADO' and c.fecha>=current_date();
        ";


        $resultado = $conexion->prepare($sql);
        $resultado->execute([$nombre_usuario, $nombre_usuario]);
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 4: 
        $classcss = "bg-gradient-info";
        $title = "CONSULTAS ACEPTADAS";
        $letter = "A";
        

        $sql = "
        select count(*)
        from consultas c 
        join consultas_horario ch on c.idconsultas_horario=ch.idconsultas_horario
        left join usuarios u 
            on ch.idprofesor=u.idprofesor and u.usuario= ?
        where ch.idprofesor= case when ? ='admin' then ch.idprofesor else u.idprofesor end 
        and upper(c.estado) like '%CONFIRMADO%' and c.fecha>=current_date();
        ";

        $resultado = $conexion->prepare($sql);
        $resultado->execute([$nombre_usuario, $nombre_usuario]);
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
       
  
    break;
  } 
?>

<div class="col-xl-3 col-md-6">
    <br>
    <div class="card card-stats">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0"><?php echo $title ?></h5>
                    <span class="h2 font-weight-bold mb-0"><?php 
                    foreach ($data as $fila) {
                        echo $fila['count(*)'];
                    }
                    ?>
                
                </span>
                </div>
                <div class="col-auto">
                    <div class="icon icon-shape text-white rounded-circle shadow <?php echo $classcss ?>">
                    <?php echo $letter ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


