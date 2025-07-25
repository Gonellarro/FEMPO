<?php
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = new mysqli("db", "iesemili", "1353m1l1", "fempo");

    if ($conn->connect_error) {
        die("❌ Error de conexión: " . $conn->connect_error);
    }

    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    if ($nombre && $apellidos && $email && $password) {
        if (!str_ends_with($email, '@iesemilidarder.com')) {
            $mensaje = "<div class='alert alert-danger'>❌ Solo se permiten correos del dominio @iesemilidarder.com</div>";
        } else {
            // Verificar si el correo ya existe
            $check = $conn->prepare("SELECT id FROM USUARI WHERE correu = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $mensaje = "<div class='alert alert-danger'>❌ El correo ya está registrado.</div>";
            } else {
                // Insertar en USUARI
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt1 = $conn->prepare("INSERT INTO USUARI (nom, llinatges, correu, contrasenya) VALUES (?, ?, ?, ?)");
                $stmt1->bind_param("ssss", $nombre, $apellidos, $email, $hash);
                $stmt1->execute();
                $usuari_id = $stmt1->insert_id;
                $stmt1->close();

                // Insertar en PROFESSOR
                $stmt2 = $conn->prepare("INSERT INTO PROFESSOR (id) VALUES (?)");
                $stmt2->bind_param("i", $usuari_id);
                $stmt2->execute();
                $stmt2->close();

                $mensaje = "<div class='alert alert-success'>✅ Profesor registrado correctamente. <a href='login.php'>Inicia sesión</a></div>";
            }

            $check->close();
        }
    } else {
        $mensaje = "<div class='alert alert-warning'>⚠️ Rellena todos los campos.</div>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro Profesor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4 text-center">Registro de Profesor</h2>
    <?= $mensaje ?>
    <form method="post" class="p-4 shadow bg-white rounded" oninput="verificarCoincidencia()">
      <div class="row">
        <div class="mb-3 col">
          <label class="form-label">Nombre</label>
          <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3 col">
          <label class="form-label">Apellidos</label>
          <input type="text" name="apellidos" class="form-control" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Correo electrónico</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="row">
        <div class="mb-3 col">
          <label class="form-label">Contraseña</label>
          <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3 col">
          <label class="form-label">Repite la contraseña</label>
          <div class="input-group">
            <input type="password" id="confirm_password" class="form-control" required>
            <span class="input-group-text" id="check_icon"><i class="bi"></i></span>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Registrar Profesor</button>
      <a href="index.php" class="btn btn-secondary ms-2">Volver</a>
    </form>
  </div>

<script>
function verificarCoincidencia() {
  const pass = document.getElementById('password');
  const confirm = document.getElementById('confirm_password');
  const icon = document.getElementById('check_icon').querySelector('i');
  const btn = document.getElementById('submitBtn');

  if (pass.value && confirm.value) {
    if (pass.value === confirm.value) {
      icon.className = 'bi bi-check-circle-fill text-success';
      btn.disabled = false;
    } else {
      icon.className = 'bi bi-x-circle-fill text-danger';
      btn.disabled = true;
    }
  } else {
    icon.className = '';
    btn.disabled = true;
  }
}
</script>

</body>
</html>
