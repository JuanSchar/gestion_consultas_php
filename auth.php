<?php
  if (!isset($_SESSION)) {
    session_start();
  }
  if (!isset($_SESSION["s_usuario"])) {
    header("Location: index.php");
    exit();
  }
?>
