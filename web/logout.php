<?php
session_start();              // Inicia la sesión para poder acceder a los datos de sesión
session_unset();              // Elimina todas las variables de sesión
session_destroy();            // Destruye la sesión actual, eliminando también el archivo de sesión del servidor

// Opcional: eliminar la cookie de sesión si se usó
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirigir al login
header("Location: login.php");
exit;
?>