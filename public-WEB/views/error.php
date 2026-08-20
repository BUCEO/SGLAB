<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Obtener el mensaje de error de la sesión
$mensaje = $_SESSION['error_mensaje'] ?? 'Ha ocurrido un error inesperado.';
// Limpiar la variable de sesión después de usarla
unset($_SESSION['error_mensaje']); 

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card shadow-lg" style="max-width: 500px; width: 100%;">
            <div class="card-body p-5 text-center">
                <i class="fas fa-exclamation-triangle fa-4x text-danger mb-4"></i>
                <h3 class="mb-3">Error de Autenticación</h3>
                <p class="text-muted mb-4"><?= htmlspecialchars($mensaje) ?></p>
                <div class="d-grid gap-2">
                    <a href="login_usuario.html" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>