<?php
session_start();

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
    <div class="alert alert-warning text-center" role="alert">
        <h4 class="mb-0">⚠️ PENDIENTE. NO IMPLEMENTADO. ⚠️</h4>
    </div>


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