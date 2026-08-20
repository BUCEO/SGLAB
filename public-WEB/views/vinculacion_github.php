<?php
// Iniciar sesión si no está iniciada (necesario para leer $_SESSION)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$githubUsername = $_SESSION['github_username'] ?? 'Usuario GitHub';
// Limpiar la variable de sesión después de usarla
unset($_SESSION['github_username']); 

// Si no hay nombre de usuario, redirigir a un error o al login
if (!isset($githubUsername)) {
    // Esto es una medida de seguridad, en un caso normal el controlador siempre lo enviará
    header('Location: login_usuario.html'); 
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincular Cuenta - Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card shadow-lg" style="max-width: 550px; width: 100%;">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fab fa-github fa-4x text-dark mb-3"></i>
                    <h3>Vincular Cuenta de GitHub</h3>
                    <p class="text-muted">
                        Has iniciado sesión con GitHub como <strong class="text-dark"><?= htmlspecialchars($githubUsername) ?></strong>
                    </p>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Primera vez aquí?</strong> Para vincular tu cuenta de GitHub con el sistema, 
                    ingresa tu cédula de identidad registrada por tu docente.
                </div>
                
                <form method="GET" action="../controllers/githubAuthController.php" id="vinculacionForm">
                    <div class="mb-4">
                        <label for="ci" class="form-label fw-bold">Cédula de Identidad</label>
                        <input type="number" class="form-control form-control-lg" id="ci" name="ci_vinculacion" 
                               placeholder="Ej: 12345678" required min="10000000" max="99999999">
                        <small class="form-text text-muted">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Ingresa tu CI sin puntos ni guiones (solo números)
                        </small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-dark btn-lg">
                            <i class="fas fa-link me-2"></i>Vincular y Continuar
                        </button>
                        <a href="login_usuario.html" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Login
                        </a>
                    </div>
                </form>
                
                <div class="alert alert-warning mt-4 mb-0">
                    <small>
                        <strong>Nota:</strong> Si no apareces en el sistema, contacta a tu docente 
                        para que registre tu cédula.
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.getElementById('vinculacionForm').addEventListener('submit', function(e) {
        const ci = document.getElementById('ci').value;
        // La validación en PHP es mejor, pero mantenemos el JS para UX inmediata.
        if (ci.length < 7 || ci.length > 8) {
            e.preventDefault();
            alert('La cédula debe tener 7 u 8 dígitos');
        }
    });
    </script>
</body>
</html>