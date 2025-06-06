<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Asegúrate que existe tras el build

function enviarCorreoGmail($destinatario, $asunto, $mensajeTexto) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'TU_CORREO@gmail.com';         // CAMBIA ESTO
        $mail->Password = 'TU_APP_PASSWORD';             // CONTRASEÑA DE APLICACIÓN
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('TU_CORREO@gmail.com', 'FCT Emili Darder');
        $mail->addAddress($destinatario);

        $mail->Subject = $asunto;
        $mail->Body    = $mensajeTexto;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        return false;
    }
}
