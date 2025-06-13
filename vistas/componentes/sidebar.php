<!DOCTYPE html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Gestor de consultas UTN</title>
  <link rel="stylesheet" href="..\..\estilos.css" type="text/css">
</head>

<body>
  <nav class="sidenav navbar navbar-vertical fixed-left navbar-expand-xs navbar-light bg-blue-light" id="sidenav-main">
    <div id="mySidenav" class="scrollbar-inner">

      <div class="sidenav-header align-items-center">
        <a class="navbar-brand" href="../vistas/dashboard.php">
          <img src="../../img/LOGO.png" width="30" height="30" class="navbar-brand-img" alt="Logo UTN">
        </a>
      </div>
      <div class="navbar-inner">
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
          <ul class="navbar-nav">
            <?php
            $page = $_SERVER['REQUEST_URI'];

            echo '<li class="nav-item">';
            echo ' <a class="nav-link ';
            if (strpos($page, 'dashboard')) {
              echo 'active';
            }

            echo '" href="../vistas/dashboard.php">
                <span class="nav-link-text">Menú principal</span>
              </a>
            </li>';


            if (isset($_SESSION["s_profesor"])) {
              echo '<li class="nav-item ">';
              echo '<a class="nav-link ';
              if (strpos($page, 'listado_consultas')) {
                echo 'active';
              }

              echo '" href="../vistas/listado_consultas.php">';
              echo '<span class="nav-link-text">Listado de consultas</span>';
              echo '</a>';
              echo '</li>';

              echo '<li class="nav-item ">';
              echo '<a class="nav-link ';
              if (strpos($page, 'nueva_consulta')) {
                echo 'active';
              }

              echo '" href="../vistas/nueva_consulta.php">';
              echo '<span class="nav-link-text">Nueva consulta</span>';
              echo '</a>';
              echo '</li>';

              echo '<li class="nav-item">';
              echo '<a class="nav-link ';
              if (strpos($page, 'cancelacion_consultas')) {
                echo 'active';
              }

              echo '" href="../vistas/cancelacion_consultas.php">';
              echo '<span class="nav-link-text">Cancelación de consultas</span>';
              echo '</a>';
              echo '</li>';
            }
            if (!isset($_SESSION["s_profesor"])) {
              echo '<li class="nav-item">';
              echo '<a class="nav-link ';
              if (strpos($page, 'listado_consultas_admin')) {
                echo 'active';
              }

              echo '" href="../vistas/listado_consultas_admin.php">';
              echo '<span class="nav-link-text">Listado de consultas</span>';
              echo '</a>';
              echo '</li>';

              echo '<li class="nav-item">';
              echo '<a class="nav-link ';
              if (strpos($page, 'uploadexc')) {
                echo 'active';
              }
              echo '" href="../upload-excel/uploadexc.php">';
              echo '<span class="nav-link-text">Carga de horas</span>';
              echo '</a>';
              echo '</li>';
            }
            ?>
            <?php
            echo '<li class="nav-item">';
            echo '<a class="nav-link ';
            if (strpos($page, 'faq')) {
              echo 'active';
            }
            echo '" href="../vistas/faq.php">';
            echo ' <span class="nav-link-text">Preguntas Frecuentes</span>
              </a>
            </li>';
            ?>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</body>

</html>