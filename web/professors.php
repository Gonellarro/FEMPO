<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: login.php");
    exit;
}

$professor_id = $_SESSION['usuario_id'];
$conn = new mysqli("db", "iesemili", "1353m1l1", "fempo");

// Obtener nombre del profesor para el navbar
$stmt = $conn->prepare("SELECT nom FROM USUARI WHERE id = ?");
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$nombre = $row['nom'] ?? 'Profesor';
$stmt->close();

// Eliminar profesor si no es el mismo usuario
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    if ($id !== $professor_id) {
        // Primero eliminar en tabla PROFESSOR
        $stmt = $conn->prepare("DELETE FROM PROFESSOR WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Luego eliminar en tabla USUARI
        $stmt = $conn->prepare("DELETE FROM USUARI WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Obtener profesores desde la tabla PROFESSOR y datos de USUARI
$query = "
SELECT U.id, U.nom, U.llinatges, U.correu 
FROM PROFESSOR P 
JOIN USUARI U ON P.id = U.id 
ORDER BY U.nom
";
$result = $conn->query($query);
$professors = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Profesores</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR -->
<?php include 'navbar.php'; ?>

<!-- CONTENIDO PRINCIPAL -->
<div class="container my-5">
  <h3 class="mb-4 text-center">Listado de Profesores</h3>

  <div class="card">
    <div class="card-header bg-primary text-white">
      Profesores Registrados
    </div>
    <div class="card-body table-responsive">
      <?php if ($professors): ?>
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Correo</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($professors as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['nom']) ?></td>
            <td><?= htmlspecialchars($p['llinatges']) ?></td>
            <td><?= htmlspecialchars($p['correu']) ?></td>
            <td>
              <?php if ($p['id'] !== $professor_id): ?>
                <a href="?eliminar=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este profesor?')">Eliminar</a>
              <?php else: ?>
                <span class="text-muted">Tú</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-muted text-center">No hay profesores registrados.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
