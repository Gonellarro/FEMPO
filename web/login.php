<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // ⚠️ Cámbialo a 1 en producción con HTTPS
ini_set('session.use_strict_mode', 1);
session_start();

function iniciarSesionSegura($usuario, $rol) {
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['correo'] = $usuario['correu'];
    $_SESSION['rol'] = $rol;

    if ($rol === 'profesor') {
        header("Location: dashIES.php");
    } else {
        header("Location: dashEmpresa.php");
    }
    exit;
}

// Conexión
$conn = new mysqli("db", "iesemili", "1353m1l1", "fempo");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM USUARI WHERE correu = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($password, $usuario['contrasenya'])) {

            // Determinar el rol
            $rol = 'empresa';
            $id_usuario = $usuario['id'];

            // ¿Es profesor?
            $prof_stmt = $conn->prepare("SELECT 1 FROM PROFESSOR WHERE id = ?");
            $prof_stmt->bind_param("i", $id_usuario);
            $prof_stmt->execute();
            $prof_stmt->store_result();

            if ($prof_stmt->num_rows > 0) {
                $rol = 'profesor';
            }

            iniciarSesionSegura($usuario, $rol);

        } else {
            $error = "❌ Contraseña incorrecta.";
        }
    } else {
        $error = "❌ Usuario no encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - FCT Emili Darder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4 text-center">Iniciar Sesión</h2>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="post" class="p-4 shadow bg-white rounded" style="max-width: 500px; margin: auto;">
      <div class="mb-3">
        <label class="form-label">Correo electrónico</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Entrar</button>
      <div class="text-center mt-3">
        <a href="index.php" class="btn btn-link">Volver al inicio</a>
        <br>
        <a href="olvida_contrasenya.php" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
      </div>
    </form>
  </div>
</body>
</html>
