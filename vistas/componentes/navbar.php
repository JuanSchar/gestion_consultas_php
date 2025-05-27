<!DOCTYPE html> 
<head>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Gestor de consultas UTN</title>
  <link rel="stylesheet" href="..\estilos.css" type="text/css">
</head>

  <nav class="navbar navbar-top navbar-expand navbar-dark border-bottom">
    <div class="container-fluid">
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav align-items-center  ml-md-auto ">
          <li class="nav-item d-xl-none">
            <div class="pr-3 sidenav-toggler sidenav-toggler-dark" data-action="sidenav-pin" data-target="#sidenav-main">
              <div class="sidenav-toggler-inner">
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
              </div>
            </div>
          </li>
        </ul>
        <ul class="navbar-nav align-items-center  ml-auto ml-md-0 ">
          <li class="nav-item dropdown">
            <a class="nav-link pr-0 text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <div class="media align-items-center">
                <span class="avatar avatar-sm rounded-circle">
                  <img alt="imagen_usuario" src="..\..\img\img_logo.jpg">
                </span>
                <div> &nbsp;
                  <span><?php echo isset($_SESSION["s_profesor"]) ? $_SESSION["s_nombre_profesor"]: $_SESSION["s_usuario"];?></span>
                </div>
              </div>
            </a>
            
            <div class="dropdown-menu dropdown-menu-right ">               
              <a href="../../bd/logout.php" class="dropdown-item">
                <span >Cerrar sesión</span>
              </a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</html>