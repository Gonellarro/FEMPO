<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'empresa') {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['solicitud_id'])) {
    die("❌ Solicitud no especificada.");
}

$solicitud_id = intval($_POST['solicitud_id']);

$conn = new mysqli("db", "usuario", "clave123", "practicas");

// Verifica que la solicitud pertenece al usuario actual
$stmt = $conn->prepare("SELECT id FROM solicitudes WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $solicitud_id, $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ No autorizado o solicitud no encontrada.");
}

$stmt = $conn->prepare("DELETE FROM solicitudes WHERE id = ?");
$stmt->bind_param("i", $solicitud_id);
$stmt->execute();

header("Location: dashEmpresa.php");
?>
