<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'empresa') {
    header("Location: login.php");
    exit;
}

$usuari_id = $_SESSION['usuario_id'];
$conn = new mysqli("db", "iesemili", "1353m1l1", "fempo");

// Obtener empresa_id desde CONTACTE
$stmt = $conn->prepare("SELECT empresa_id FROM CONTACTE WHERE id = ?");
$stmt->bind_param("i", $usuari_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$empresa_id = $row['empresa_id'] ?? null;
$stmt->close();

if (!$empresa_id) {
    die("❌ No se ha encontrado la empresa asociada a este usuario.");
}

// Obtener nombre de la empresa
$stmt = $conn->prepare("SELECT nomE FROM EMPRESA WHERE id = ?");
$stmt->bind_param("i", $empresa_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$nombre_empresa = $row['nomE'] ?? 'Empresa';
$stmt->close();

// Eliminar solicitud
if (isset($_GET['eliminar'])) {
    $numSol = substr($_GET['eliminar'], 0, 50); // sanitizar longitud
    $stmt = $conn->prepare("DELETE FROM SOLICITUD WHERE numeroSolicitud = ? AND estat = 'Pendiente' AND empresa_id = ?");
    $stmt->bind_param("si", $numSol, $empresa_id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashEmpresa.php");
    exit;
}

// Añadir nuevas solicitudes
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nueva_solicitud'])) {
    $convocatoria = $_POST['convocatoria'];
    $promocion = intval($_POST['promocion']);
    $curso = intval($_POST['curso']);

    foreach (['SMX', 'ASIX', 'DAW'] as $grado) {
        $cantidad = intval($_POST[strtolower($grado)]);
        for ($i = 0; $i < $cantidad; $i++) {
            $numero = "{$grado}{$curso}{$convocatoria}{$promocion}_" . date("His") . rand(10, 99);

            $stmt = $conn->prepare("INSERT INTO SOLICITUD 
                (numeroSolicitud, estat, alumne_id, empresa_id, professor_id, grau, curs, convocatoria, promocio)
                VALUES (?, 'Pendiente', NULL, ?, NULL, ?, ?, ?, ?)");
            $stmt->bind_param("sissss", $numero, $empresa_id, $grado, $curso, $convocatoria, $promocion);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: dashEmpresa.php");
    exit;
}


// Obtener solicitudes
$stmt = $conn->prepare("SELECT * FROM SOLICITUD WHERE empresa_id = ? ORDER BY numeroSolicitud DESC");
$stmt->bind_param("i", $empresa_id);
$stmt->execute();
$solicitudes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
$año_actual = date("Y");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Empresa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="#">FCT - IES Emili Darder</a>
    <div class="ms-auto">
      <div class="dropdown">
        <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
          <i class="bi bi-person"></i> <?= htmlspecialchars($nombre_empresa) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="logout.php">Salir</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="container my-5">
  <h3 class="mb-4 text-center">Solicitudes de Prácticas</h3>

  <div class="card mb-4">
    <div class="card-header bg-success text-white">Nueva Solicitud</div>
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="nueva_solicitud" value="1">
        <div class="row mb-3">
          <div class="col">
            <label class="form-label">Convocatoria</label>
            <select name="convocatoria" class="form-select" required>
              <option value="Junio">Junio</option>
              <option value="Septiembre">Septiembre</option>
            </select>
          </div>
          <div class="col">
            <label class="form-label">Promoción</label>
            <input type="number" name="promocion" class="form-control" value="<?= $año_actual ?>" required>
          </div>
          <div class="col">
            <label class="form-label">Curso</label>
            <select name="curso" class="form-select" required>
              <option value="1">1º</option>
              <option value="2" selected>2º</option>
            </select>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col"><label class="form-label">SMX</label>
            <input type="number" name="smx" class="form-control" value="0" min="0">
          </div>
          <div class="col"><label class="form-label">ASIX</label>
            <input type="number" name="asix" class="form-control" value="0" min="0">
          </div>
          <div class="col"><label class="form-label">DAW</label>
            <input type="number" name="daw" class="form-control" value="0" min="0">
          </div>
        </div>

        <button type="submit" class="btn btn-success">Enviar solicitud</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-primary text-white">Solicitudes Realizadas</div>
    <div class="card-body table-responsive">
      <?php if ($solicitudes): ?>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Nº Solicitud</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($solicitudes as $s): ?>
          <tr>
            <td>
              <a href="#" data-bs-toggle="modal" data-bs-target="#modal<?= $s['numeroSolicitud'] ?>">
                <?= htmlspecialchars($s['numeroSolicitud']) ?>
              </a>
            </td>
            <td><span class="badge bg-<?= $s['estat'] === 'Pendiente' ? 'warning text-dark' : ($s['estat'] === 'Aceptado' ? 'success' : 'secondary') ?>"><?= $s['estat'] ?></span></td>
            <td>
              <?php if ($s['estat'] === 'Pendiente'): ?>
              <a href="dashEmpresa.php?eliminar=<?= urlencode($s['numeroSolicitud']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar esta solicitud?')">Eliminar</a>
              <?php else: ?>
              <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
          </tr>
          <!-- Modal para <?= htmlspecialchars($s['numeroSolicitud']) ?> -->
          <div class="modal fade" id="modal<?= $s['numeroSolicitud'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $s['numeroSolicitud'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header bg-info text-white">
                  <h5 class="modal-title" id="modalLabel<?= $s['numeroSolicitud'] ?>">Detalle de Solicitud</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <p><strong>Número:</strong> <?= htmlspecialchars($s['numeroSolicitud']) ?></p>
                  <p><strong>Estado:</strong> <?= htmlspecialchars($s['estat']) ?></p>
                  <p><strong>Grado:</strong> <?= htmlspecialchars($s['grau']) ?></p>
                  <p><strong>Curso:</strong> <?= htmlspecialchars($s['curs']) ?></p>
                  <p><strong>Convocatoria:</strong> <?= htmlspecialchars($s['convocatoria']) ?></p>
                  <p><strong>Promoción:</strong> <?= htmlspecialchars($s['promocio']) ?></p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-muted">No hay solicitudes aún.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
