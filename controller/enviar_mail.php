<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

function enviarMail($destinatario, $asunto, $mensaje, $copia = null) {
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'juanschar@gmail.com'; // Cambia por tu correo
    $mail->Password = 'rihe yuez tubj ulyo'; // Cambia por tu contraseña o app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('juanschar@gmail.com', 'UTN Consultas');
    $mail->addAddress($destinatario);
    if ($copia) {
      $mail->addCC($copia);
    }
    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body    = $mensaje;

    $mail->send();
    return true;
  } catch (Exception $e) {
    error_log('Error al enviar el correo: ' . $mail->ErrorInfo);
    return false;
  }
}
