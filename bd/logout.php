<?php 
session_start();
$_SESSION["s_usuario"] = null;
header("Location: ../index.php");
?>