<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: login.php");
    exit;
}

$professor_id = $_SESSION['usuario_id'];
$conn = new mysqli("db", "iesemili", "1353m1l1", "fempo");

// Eliminar empresa si no tiene solicitudes pendientes
if (isset($_GET['eliminar'])) {
    $empresa_id = intval($_GET['eliminar']);

    // Verificar que no tiene solicitudes pendientes
    $stmt = $conn->prepare("SELECT COUNT(*) FROM SOLICITUD WHERE empresa_id = ? AND estat IN ('Pendiente', 'Tramitando')");
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    $stmt->bind_result($pendientes);
    $stmt->fetch();
    $stmt->close();

    if ($pendientes == 0) {
        // Borrar de CONTACTE
        $stmt = $conn->prepare("DELETE FROM CONTACTE WHERE empresa_id = ?");
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();
        $stmt->close();

        // Borrar empresa
        $stmt = $conn->prepare("DELETE FROM EMPRESA WHERE id = ?");
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Obtener empresas y estado de solicitudes
$empresas = [];
$query = "
    SELECT 
        E.id AS empresa_id,
        E.nomE AS nombre_empresa,
        (
            SELECT COUNT(*) 
            FROM SOLICITUD S 
            WHERE S.empresa_id = E.id AND S.estat IN ('Pendiente', 'Tramitando')
        ) AS solicitudes_pendientes
    FROM EMPRESA E
";
$result = $conn->query($query);
while ($empresa = $result->fetch_assoc()) {
    // Obtener datos del contacto
    $stmt = $conn->prepare("SELECT id FROM CONTACTE WHERE empresa_id = ?");
    $stmt->bind_param("i", $empresa['empresa_id']);
    $stmt->execute();
    $stmt->bind_result($contacte_id);
    $stmt->fetch();
    $stmt->close();

    $nombre_contacto = $correo_contacto = $apellido_contacto = "❓";
    if ($contacte_id) {
        $stmt = $conn->prepare("SELECT nom, llinatges, correu FROM USUARI WHERE id = ?");
        $stmt->bind_param("i", $contacte_id);
        $stmt->execute();
        $stmt->bind_result($nombre_contacto, $apellido_contacto, $correo_contacto);
        $stmt->fetch();
        $stmt->close();
    }

    $empresa['contacto_nombre'] = $nombre_contacto;
    $empresa['contacto_apellido'] = $apellido_contacto;
    $empresa['contacto_correo'] = $correo_contacto;
    $empresa['estado'] = $empresa['solicitudes_pendientes'] > 0 ? 'Pendientes' : 'Sin pendientes';

    $empresas[] = $empresa;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Empresas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container my-5">
  <h3 class="text-center mb-4">Listado de Empresas</h3>

  <div class="card">
    <div class="card-header bg-primary text-white">Empresas Registradas</div>
    <div class="card-body table-responsive">
      <?php if ($empresas): ?>
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Empresa</th>
            <th>Contacto</th>
            <th>Correo</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($empresas as $e): ?>
          <tr>
            <td><?= htmlspecialchars($e['nombre_empresa']) ?></td>
            <td><?= htmlspecialchars($e['contacto_nombre'] . ' ' . $e['contacto_apellido']) ?></td>
            <td><?= htmlspecialchars($e['contacto_correo']) ?></td>
            <td>
              <?php if ($e['estado'] === 'Pendientes'): ?>
                <a href="tramitar.php?empresa_id=<?= $e['empresa_id'] ?>" class="badge bg-warning text-dark text-decoration-none">Pendientes</a>
              <?php else: ?>
                <span class="badge bg-success">Sin pendientes</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($e['estado'] === 'Sin pendientes'): ?>
                <a href="empreses.php?eliminar=<?= $e['empresa_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar esta empresa?')">Eliminar</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-muted text-center">No hay empresas registradas.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
