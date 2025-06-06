<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("db", "iesemili", "1353m1l1", "fempo");

if (!isset($_GET['empresa_id'])) {
    die("❌ Falta el parámetro empresa_id.");
}
$empresa_id = intval($_GET['empresa_id']);
$solicitud_id = isset($_GET['solicitud_id']) ? $_GET['solicitud_id'] : null;
$mensaje = "";

// Paso 3: Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion'], $_POST['solicitud_id'])) {
    $sid = $_POST['solicitud_id'];
    $accion = $_POST['accion'];
    $alumne_id = isset($_POST['alumne_id']) ? intval($_POST['alumne_id']) : null;

    if ($accion === "aceptar" && $alumne_id) {
        $stmt = $conn->prepare("UPDATE SOLICITUD SET estat = 'Aceptado', alumne_id = ? WHERE numeroSolicitud = ?");
        $stmt->bind_param("is", $alumne_id, $sid);
        $stmt->execute();
        $stmt->close();
    } elseif ($accion === "denegar") {
        $stmt = $conn->prepare("UPDATE SOLICITUD SET estat = 'Denegado' WHERE numeroSolicitud = ?");
        $stmt->bind_param("s", $sid);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: tramitar.php?empresa_id=$empresa_id");
    exit;
}

// Paso 1: Listar solicitudes si no se ha seleccionado ninguna
if (!$solicitud_id) {
    $stmt = $conn->prepare("SELECT * FROM SOLICITUD WHERE empresa_id = ? AND estat IN ('Pendiente', 'Tramitando') ORDER BY numeroSolicitud DESC");
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    $solicitudes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Paso 2: Mostrar detalle de la solicitud seleccionada
    $stmt = $conn->prepare("SELECT * FROM SOLICITUD WHERE numeroSolicitud = ? AND empresa_id = ?");
    $stmt->bind_param("si", $solicitud_id, $empresa_id);
    $stmt->execute();
    $solicitud = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$solicitud) die("❌ Solicitud no válida.");

    // Buscar alumnos compatibles no asignados
    $query = "
        SELECT * FROM ALUMNE
        WHERE grau = ? AND curs = ? AND convocatoria = ? AND promocio = ?
        AND id NOT IN (
            SELECT alumne_id FROM SOLICITUD WHERE alumne_id IS NOT NULL
        )
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("siss", $solicitud['grau'], $solicitud['curs'], $solicitud['convocatoria'], $solicitud['promocio']);
    $stmt->execute();
    $alumnes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Tramitación de Solicitudes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
  <h3 class="mb-4 text-center">Tramitación de Solicitudes</h3>

  <?php if (!$solicitud_id): ?>
    <div class="card">
      <div class="card-header bg-primary text-white">Solicitudes Pendientes</div>
      <div class="card-body">
        <?php if ($solicitudes): ?>
          <ul class="list-group">
            <?php foreach ($solicitudes as $s): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($s['numeroSolicitud']) ?>
                <a href="tramitar.php?empresa_id=<?= $empresa_id ?>&solicitud_id=<?= urlencode($s['numeroSolicitud']) ?>" class="btn btn-sm btn-outline-success">Tramitar</a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted">No hay solicitudes pendientes.</p>
        <?php endif; ?>
        <div class="text-center mt-4">
        <a href="dashIES.php" class="btn btn-outline-secondary">← Volver</a>
      </div>
      </div>
    </div>
  <?php else: ?>
    <div class="card mb-4">
      <div class="card-header bg-info text-white">Solicitud <?= htmlspecialchars($solicitud['numeroSolicitud']) ?></div>
      <div class="card-body">
        <p><strong>Grado:</strong> <?= htmlspecialchars($solicitud['grau'] ?? '') ?></p>
        <p><strong>Curso:</strong> <?= htmlspecialchars($solicitud['curs'] ?? '') ?></p>
        <p><strong>Convocatoria:</strong> <?= htmlspecialchars($solicitud['convocatoria'] ?? '') ?></p>
        <p><strong>Promoción:</strong> <?= htmlspecialchars($solicitud['promocio'] ?? '') ?></p>
      </div>
    </div>

    <form method="post">
      <input type="hidden" name="solicitud_id" value="<?= htmlspecialchars($solicitud['numeroSolicitud']) ?>">
      <div class="card">
        <div class="card-header bg-success text-white">Alumnes compatibles</div>
        <div class="card-body">
          <?php if (!empty($alumnes)): ?>
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Seleccionar</th>
                  <th>Nombre</th>
                  <th>Llinatge</th>
                  <th>Curso</th>
                  <th>Convocatoria</th>
                  <th>Promoción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($alumnes as $a): ?>
                  <tr>
                    <td><input type="radio" name="alumne_id" value="<?= $a['id'] ?>"></td>
                    <td><?= htmlspecialchars($a['nom']) ?></td>
                    <td><?= htmlspecialchars($a['llinatge']) ?></td>
                    <td><?= $a['curs'] ?></td>
                    <td><?= $a['convocatoria'] ?></td>
                    <td><?= $a['promocio'] ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <div class="text-center mt-3">
              <button type="submit" name="accion" value="aceptar" class="btn btn-success">Aceptar</button>
              <button type="submit" name="accion" value="denegar" class="btn btn-danger ms-3">Denegar</button>
            </div>
          <?php else: ?>
            <p class="text-muted">No hay alumnos compatibles disponibles.</p>
            <div class="text-center">
              <button type="submit" name="accion" value="denegar" class="btn btn-danger">Denegar</button>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </form>
    <div class="text-center mt-4">
      <a href="tramitar.php?empresa_id=<?= $empresa_id ?>" class="btn btn-outline-secondary">← Volver</a>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
