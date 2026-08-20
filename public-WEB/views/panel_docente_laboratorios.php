<?php
session_start();

// Debug logging
error_log("=== PANEL DOCENTE LABORATORIOS - Verificando sesión ===");
error_log("Session ID: " . session_id());
error_log("usuario_id isset: " . (isset($_SESSION['usuario_id']) ? "SI" : "NO"));
error_log("usuario_id value: " . ($_SESSION['usuario_id'] ?? 'NULL'));
error_log("idTipoUsuario isset: " . (isset($_SESSION['idTipoUsuario']) ? "SI" : "NO"));
error_log("idTipoUsuario value: " . ($_SESSION['idTipoUsuario'] ?? 'NULL'));

if (!isset($_SESSION['usuario_id']) || $_SESSION['idTipoUsuario'] != 2) {
    error_log("❌ ACCESO DENEGADO - Redirigiendo a login");
    header('Location: login_usuario.html');
    exit();
}

error_log("✅ Acceso permitido a panel docente laboratorios");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Docente - Gestión de Laboratorios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../Public/css/panel_docente_laboratorios.css">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="menu_docente.php">
                <img src="../Public/img/Logo-DGETP-UTU-ByN-Transparente-PNG.png" alt="Logo" height="40" class="d-inline-block align-top me-2">
                Panel Docente
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarDocente">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarDocente">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="panel_docente_laboratorios.php">
                            <i class="bi bi-building me-1"></i> Laboratorios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu_docente.php">
                            <i class="bi bi-house me-1"></i> Inicio
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center text-white me-3">
                    <i class="bi bi-person-circle me-2"></i>
                    <span><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Docente'); ?></span>
                </div>
                <div class="d-flex">
                    <a href="../Controllers/logoutController.php" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right me-1"></i> Salir
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="flex-grow-1 container-fluid p-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-3">
                    <i class="bi bi-building-gear me-2"></i>
                    Gestión de Laboratorios
                </h2>
                <p class="text-muted">Vista completa de laboratorios, equipos y sus registros</p>
            </div>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="row mb-4" id="estadisticasContainer">
            <div class="col-md-3">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase mb-1">Total Laboratorios</h6>
                            <h2 class="mb-0" id="totalLaboratorios">0</h2>
                        </div>
                        <i class="bi bi-building stats-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase mb-1">Equipos Funcionando</h6>
                            <h2 class="mb-0" id="equiposFuncionando">0</h2>
                        </div>
                        <i class="bi bi-check-circle-fill stats-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-danger text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase mb-1">Con Problemas</h6>
                            <h2 class="mb-0" id="equiposProblemas">0</h2>
                        </div>
                        <i class="bi bi-x-circle-fill stats-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-warning text-dark">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase mb-1">En Reparación</h6>
                            <h2 class="mb-0" id="equiposReparacion">0</h2>
                        </div>
                        <i class="bi bi-tools stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor de laboratorios -->
        <div id="laboratoriosContainer">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted">Cargando laboratorios...</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <p class="mb-0">&copy; 2025 Sistema de Gestión de Laboratorios - Panel Docente</p>
    </footer>

    <!-- Modal para editar comentario -->
    <div class="modal fade" id="modalComentario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-chat-left-text me-2"></i>
                        Actualizar Comentario del Laboratorio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formComentario">
                        <input type="hidden" id="idLaboratorioModal">
                        <div class="mb-3">
                            <label for="nombreLaboratorioModal" class="form-label fw-bold">Laboratorio:</label>
                            <p id="nombreLaboratorioModal" class="text-muted"></p>
                        </div>
                        <div class="mb-3">
                            <label for="comentarioInput" class="form-label">Comentario:</label>
                            <textarea
                                class="form-control"
                                id="comentarioInput"
                                rows="4"
                                maxlength="255"
                                placeholder="Escriba aquí el estado o comentario sobre el laboratorio..."
                            ></textarea>
                            <div class="form-text">
                                <span id="contadorCaracteres">0</span>/255 caracteres
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarComentario">
                        <i class="bi bi-save me-1"></i> Guardar Comentario
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Public/js/panel_docente_laboratorios.js"></script>
</body>
</html>
