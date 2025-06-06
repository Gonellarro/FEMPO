<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: login.php");
    exit;
}

$professor_id = $_SESSION['usuario_id'];
$conn = new mysqli("db", "iesemili", "1353m1l1", "fempo");

$stmt = $conn->prepare("SELECT nom FROM USUARI WHERE id = ?");
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$nombre = $row['nom'] ?? 'Profesor';
$stmt->close();

// Guardar en cookie durante 1 hora
setcookie('nombre_profesor', $nombre, time() + 3600, "/");
$conn->close();

$mensaje = "";


// Conexión
$conn = new mysqli("db", "iesemili", "1353m1l1", "fempo");

// Añadir alumno
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nuevo_alumno'])) {
    $nombre_alumno = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $grado = $_POST['grado'];
    $promocion = intval($_POST['promocion']);
    $convocatoria = $_POST['convocatoria'];
    $curso = $_POST['curso'];

    if ($nombre_alumno && $apellidos && $grado && $promocion && $convocatoria && $curso) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ALUMNE WHERE nom = ? AND llinatge = ?");
        $stmt->bind_param("ss", $nombre_alumno, $apellidos);
        $stmt->execute();
        $stmt->bind_result($existe);
        $stmt->fetch();
        $stmt->close();

        if ($existe > 0) {
            $mensaje = "<div class='alert alert-danger'>❌ Ya existe un alumno con ese nombre y apellidos.</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO ALUMNE (nom, llinatge, grau, promocio, convocatoria, curs, professor_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssissi", $nombre_alumno, $apellidos, $grado, $promocion, $convocatoria, $curso, $professor_id);
            $stmt->execute();
            $stmt->close();
            header("Location: dashIES.php?exito=1");
            exit;
        }
    }
}

if (isset($_GET['exito'])) {
    $mensaje = "<div class='alert alert-success'>Alumno añadido correctamente.</div>";
}

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
$año_actual = date("Y");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Profesor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
 <?php include 'navbar.php'; ?>

<!-- Menú lateral -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menú</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="list-group">
      <li class="list-group-item"><i class="bi bi-people-fill me-2"></i><a href="alumnes.php">Alumnos</a></li>
      <li class="list-group-item"><a href="empreses.php" class="text-decoration-none">Empresas</a></li>
      <li class="list-group-item"><a href="professors.php" class="text-decoration-none">Profesores</a></li>
      <li class="list-group-item"><a href="dashIES.php" class="text-decoration-none">Solicitudes</a></li>
    </ul>
  </div>
</div>



  <div class="container my-5">
    <h3 class="text-center mb-4">Solicitudes Pendientes</h3>
    <?= $mensaje ?>

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

    <div class="card">
      <div class="card-header bg-success text-white">Añadir Alumno</div>
      <div class="card-body">
        <form method="post">
          <input type="hidden" name="nuevo_alumno" value="1">
          <div class="row mb-3">
            <div class="col"><label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col"><label class="form-label">Apellidos</label>
              <input type="text" name="apellidos" class="form-control" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col"><label class="form-label">Grado</label>
              <select name="grado" class="form-select" required>
                <option value="SMX">SMX</option>
                <option value="ASIX">ASIX</option>
                <option value="DAW">DAW</option>
              </select>
            </div>
            <div class="col"><label class="form-label">Curso</label>
              <select name="curso" class="form-select" required>
                <option value="1">1º</option>
                <option value="2" selected>2º</option>
              </select>
            </div>
            <div class="col"><label class="form-label">Promoción</label>
              <input type="number" name="promocion" class="form-control" value="<?= $año_actual ?>" required>
            </div>
            <div class="col"><label class="form-label">Convocatoria</label>
              <select name="convocatoria" class="form-select" required>
                <option value="Junio">Junio</option>
                <option value="Septiembre">Septiembre</option>
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-success">Guardar alumno</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
