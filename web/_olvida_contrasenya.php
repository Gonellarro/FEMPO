<?php
session_start();
require_once 'enviar_correo.php';

$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $correo = strtolower(trim($_POST['email']));
    
    // Conexión a la BBDD
    $conn = new mysqli("db", "iesemili", "1353m1l1", "fempo");

    if ($conn->connect_error) {
        $mensaje = "<div class='alert alert-danger'>❌ Error de conexión.</div>";
    } else {
        // Buscar usuario
        $stmt = $conn->prepare("SELECT id FROM USUARI WHERE correu = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            $id_usuario = $usuario['id'];

            // Generar contraseña temporal
            $temporal = bin2hex(random_bytes(4)); // 8 caracteres aleatorios
            $hash = password_hash($temporal, PASSWORD_DEFAULT);

            // Guardar nueva contraseña
            $update = $conn->prepare("UPDATE USUARI SET contrasenya = ? WHERE id = ?");
            $update->bind_param("si", $hash, $id_usuario);
            if ($update->execute()) {
                // Enviar correo
                $asunto = "Recuperación de contraseña - FCT Emili Darder";
                $contenido = "Se ha solicitado una nueva contraseña temporal para tu cuenta.\n\nTu nueva contraseña temporal es: $temporal\n\nPor favor, inicia sesión y cámbiala cuanto antes.";
                $cabeceras = "From: no-reply@fctemilidarder.local";

                if (enviarCorreo($correo, $asunto, $contenido)) {
                    $mensaje = "<div class='alert alert-success'>✅ Contraseña temporal enviada a tu correo.</div>";
                } else {
                    $mensaje = "<div class='alert alert-warning'>⚠️ No se pudo enviar el correo. Contacta con soporte.</div>";
                }

            } else {
                $mensaje = "<div class='alert alert-danger'>❌ No se pudo actualizar la contraseña.</div>";
            }
            $update->close();
        } else {
            $mensaje = "<div class='alert alert-danger'>❌ No se encontró ninguna cuenta con ese correo.</div>";
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar contraseña</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="text-center mb-4">¿Olvidaste tu contraseña?</h2>

    <?= $mensaje ?>

    <form method="post" class="p-4 bg-white rounded shadow" style="max-width: 500px; margin: auto;">
      <div class="mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Enviar nueva contraseña</button>
      <div class="text-center mt-3">
        <a href="login.php" class="btn btn-link">Volver al login</a>
      </div>
    </form>
  </div>
</body>
</html>
