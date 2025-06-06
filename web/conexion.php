<?php
$host = "db";                // ← Este es el nombre del servicio en docker-compose
$usuario = "iesemili";        // definido en MYSQL_USER
$contrasena = "1353m1l1";    // definido en MYSQL_PASSWORD
$baseDeDatos = "fempo";  // definido en MYSQL_DATABASE

$conn = new mysqli($host, $usuario, $contrasena, $baseDeDatos);

// Comprobar conexión
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}

// Establecer codificación UTF-8
$conn->set_charset("utf8");
?>