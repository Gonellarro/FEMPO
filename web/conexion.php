<?php
$host = "db";                // ← Este es el nombre del servicio en docker-compose
$usuario = "usuario";        // definido en MYSQL_USER
$contrasena = "clave123";    // definido en MYSQL_PASSWORD
$baseDeDatos = "practicas";  // definido en MYSQL_DATABASE

$conn = new mysqli($host, $usuario, $contrasena, $baseDeDatos);

// Comprobar conexión
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}

// Establecer codificación UTF-8
$conn->set_charset("utf8");
?>