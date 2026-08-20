<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login_usuario.html');
    exit(); 
}

$primeraVez = isset($_GET['primera_vez']) && $_GET['primera_vez'] == '1';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Equipos UTU-ITI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Public/css/menu_estudiante.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <?php if ($primeraVez): ?>
    <!-- Mensaje de Bienvenida -->
    <div class="position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; width: 90%; max-width: 600px;">
        <div class="alert alert-success alert-dismissible fade show shadow-lg" role="alert">
            <i class="fab fa-github me-2"></i>
            <strong>¡Bienvenido <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>!</strong> 
            Tu cuenta de GitHub ha sido vinculada exitosamente al sistema.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <header class="page-header text-center py-3">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a href="../Controllers/logoutController.php" class="btn btn-primary btn-sm custom-back-btn">
                <i class="fas fa-arrow-left me-2"></i>Salir
            </a>
            <h1 class="mb-0 flex-grow-1 text-center page-title-text">Registro Estado de Equipos</h1>
            <div style="width: 80px;"></div>
        </div>
    </header>

    <main class="flex-grow-1 d-flex justify-content-center align-items-center">
        <div class="form-container">
            <form id="estadoForm" method="POST">
                <div class="mb-4 text-center">
                    <div class="equipo-icon-container">
                        <img src="../Public/img/PC.png" alt="Icono de Equipo PC" class="img-fluid equipo-icon">
                    </div>
                    <label for="equipoSelect" class="form-label d-block">Equipo</label>
                    <select name="equipo" class="form-select custom-select" id="equipoSelect" required>
                        <option value="" selected disabled>Seleccione un equipo</option>
                    </select>
                </div>

                <div class="mb-4 text-center">
                    <div class="switch-container">
                        <label class="switch">
                            <input type="checkbox" name="no_funciona" id="noFuncionaCheck">
                            <span class="slider round"></span>
                        </label>
                        <span class="switch-label">¿Funciona correctamente?</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="comentarioTextArea" class="form-label d-block text-center">Comentario</label>
                    <textarea name="comentario" class="form-control custom-textarea" id="comentarioTextArea" rows="4" 
                              placeholder="Describa el problema o estado del equipo..."></textarea>
                    <div class="char-counter"><span id="charCount">0</span>/500</div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg custom-submit-btn">
                        <i class="fas fa-paper-plane me-2"></i>Enviar Reporte
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="text-center py-3">
        <img src="../Public/img/Logo-DGETP-UTU-ByN-Transparente-PNG.png" alt="Logo ANEP Y UTU" class="img-fluid footer-logo">
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Public/js/menu_estudiante.js"></script>
</body>
</html>