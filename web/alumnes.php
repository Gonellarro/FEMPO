<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: login.php");
    exit;
}

$professor_id = $_SESSION['usuario_id'];
$conn = new mysqli("db", "iesemili", "1353m1l1", "fempo");

// Obtener nombre del profesor
$stmt = $conn->prepare("SELECT nom FROM USUARI WHERE id = ?");
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$nombre = $row['nom'] ?? 'Profesor';
$stmt->close();

// Eliminar alumno
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conn->prepare("DELETE FROM ALUMNE WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$año_actual = date("Y");

// Obtener alumnos
$stmt = $conn->prepare("SELECT A.*, 
    (SELECT COUNT(*) FROM SOLICITUD S WHERE S.alumne_id = A.id) AS asignado
    FROM ALUMNE A WHERE A.professor_id = ?");
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$alumnes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Insertar nuevo alumno
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nuevo_alumno'])) {
    $nombre_alumno = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $grado = $_POST['grado'];
    $promocion = intval($_POST['promocion']);
    $convocatoria = $_POST['convocatoria'];
    $curso = $_POST['curso'];

    if ($nombre_alumno && $apellidos && $grado && $promocion && $convocatoria && $curso) {
        // Verificar si ya existe el alumno
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ALUMNE WHERE nom = ? AND llinatge = ?");
        $stmt->bind_param("ss", $nombre_alumno, $apellidos);
        $stmt->execute();
        $stmt->bind_result($existe);
        $stmt->fetch();
        $stmt->close();

        if ($existe > 0) {
            echo "<div class='alert alert-danger'>❌ Ya existe un alumno con ese nombre y apellidos.</div>";
        } else {
            // Insertar nuevo alumno
            $stmt = $conn->prepare("INSERT INTO ALUMNE (nom, llinatge, grau, promocio, convocatoria, curs, professor_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssissi", $nombre_alumno, $apellidos, $grado, $promocion, $convocatoria, $curso, $professor_id);
            if ($stmt->execute()) {
                header("Location: alumnes.php?exito=1");
                exit;
            } else {
                echo "<div class='alert alert-danger'>❌ Error al añadir el alumno.</div>";
            }
            $stmt->close();
        }
    }
}
if (isset($_GET['exito'])) {
    $mensaje = "<div class='alert alert-success'>Alumno añadido correctamente.</div>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Alumnos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR -->
<?php include 'navbar.php'; ?>

<!-- CONTENIDO PRINCIPAL -->
<div class="container my-5">
  <h3 class="mb-4 text-center">Listado de Alumnos</h3>
  <?= $mensaje ?? '' ?>
<div class="card mb-5"> <!-- AQUÍ AÑADIMOS mb-5 -->
  <div class="card-header bg-primary text-white">
    Alumnos Registrados
  </div>
  <div class="card-body table-responsive">
      <?php if ($alumnes): ?>
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Grado</th>
            <th>Curso</th>
            <th>Promoción</th>
            <th>Convocatoria</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alumnes as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['nom']) ?></td>
            <td><?= htmlspecialchars($a['llinatge']) ?></td>
            <td><?= htmlspecialchars($a['grau']) ?></td>
            <td><?= htmlspecialchars($a['curs']) ?></td>
            <td><?= htmlspecialchars($a['promocio']) ?></td>
            <td><?= htmlspecialchars($a['convocatoria']) ?></td>
            <td>
              <span class="badge bg-<?= $a['asignado'] > 0 ? 'success' : 'secondary' ?>">
                <?= $a['asignado'] > 0 ? 'Asignado' : 'No asignado' ?>
              </span>
            </td>
            <td>             
                <?php if ($a['asignado'] == 0): ?>
                    <a href="alumnes.php?eliminar=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este alumno?')">Eliminar</a>
                <?php else: ?>
                    <span class="text-muted">—</span>
                <?php endif; ?>
            </td>
          </tr>
          <?php endforeach ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-muted text-center">No hay alumnos registrados.</p>
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
