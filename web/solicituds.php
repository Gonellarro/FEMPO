<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: login.php");
    exit;
}

$professor_id = $_SESSION['usuario_id'];

$conn = new mysqli("db", "usuario", "clave123", "fempo");

// Obtener solicitudes no aprobadas agrupadas por empresa
$empresas = [];
$result = $conn->query("
    SELECT 
        E.id AS empresa_id,
        E.nomE AS nombre_empresa,
        COUNT(S.numeroSolicitud) AS solicitudes_no_aprobadas
    FROM EMPRESA E
    JOIN SOLICITUD S ON S.empresa_id = E.id
    WHERE S.estat IN ('Pendiente', 'Tramitando')
    GROUP BY E.id, E.nomE
    ORDER BY E.nomE
");

while ($row = $result->fetch_assoc()) {
    $row['estado'] = ($row['solicitudes_no_aprobadas'] > 0) ? 'Pendiente' : 'Tramitado';
    $empresas[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Solicitudes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container my-5">
  <h3 class="text-center mb-4">Solicitudes Pendientes</h3>

  <div class="card mb-5">
    <div class="card-header bg-primary text-white">Resumen por Empresa</div>
    <div class="card-body table-responsive">
      <?php if ($empresas): ?>
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Empresa</th>
            <th>Solicitudes no aprobadas</th>
            <th>Estado</th>
            <th>Tramitar</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($empresas as $e): ?>
          <tr>
            <td><?= htmlspecialchars($e['nombre_empresa']) ?></td>
            <td><?= $e['solicitudes_no_aprobadas'] ?></td>
            <td>
              <span class="badge bg-<?= $e['estado'] === 'Pendiente' ? 'warning text-dark' : 'success' ?>">
                <?= $e['estado'] ?>
              </span>
            </td>
            <td><a href="tramitar.php?empresa_id=<?= $e['empresa_id'] ?>" class="btn btn-sm btn-outline-success">Tramitar</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-muted">No hay solicitudes pendientes.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
