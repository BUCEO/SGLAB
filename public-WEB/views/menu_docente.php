<?php
session_start();

// Debug logging
error_log("=== MENU DOCENTE - Verificando sesión ===");
error_log("Session ID: " . session_id());
error_log("usuario_id isset: " . (isset($_SESSION['usuario_id']) ? "SI" : "NO"));
error_log("usuario_id value: " . ($_SESSION['usuario_id'] ?? 'NULL'));
error_log("idTipoUsuario isset: " . (isset($_SESSION['idTipoUsuario']) ? "SI" : "NO"));
error_log("idTipoUsuario value: " . ($_SESSION['idTipoUsuario'] ?? 'NULL'));
error_log("Session completa: " . print_r($_SESSION, true));

if (!isset($_SESSION['usuario_id']) || $_SESSION['idTipoUsuario'] != 2) {
    error_log("❌ ACCESO DENEGADO - Redirigiendo a login");
    header('Location: login_usuario.html');
    exit();
}

error_log("✅ Acceso permitido a menu docente");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .feature-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
    </style>
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
                        <a class="nav-link active" href="menu_docente.php">
                            <i class="bi bi-house me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="panel_docente_laboratorios.php">
                            <i class="bi bi-building me-1"></i> Laboratorios
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

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 mb-3">
                        <i class="bi bi-mortarboard-fill me-3"></i>
                        Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Docente'); ?>
                    </h1>
                    <p class="lead">
                        Sistema de Gestión de Laboratorios - Panel Docente
                    </p>
                    <p class="mb-0">
                        Gestione y monitoree el estado de los laboratorios y equipos de la institución
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="bi bi-person-workspace" style="font-size: 8rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <main class="flex-grow-1 container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-3">
                    <i class="bi bi-grid-3x3-gap-fill me-2"></i>
                    Módulos Disponibles
                </h3>
            </div>
        </div>

        <!-- Tarjetas de funcionalidades -->
        <div class="row g-4">
            <!-- Gestión de Laboratorios -->
            <div class="col-md-6 col-lg-4">
                <a href="panel_docente_laboratorios.php" class="text-decoration-none">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-building-gear feature-icon text-primary"></i>
                            <h5 class="card-title">Gestión de Laboratorios</h5>
                            <p class="card-text text-muted">
                                Visualice el estado de todos los laboratorios, sus equipos y registros.
                                Agregue comentarios sobre el estado actual de cada laboratorio.
                            </p>
                            <div class="mt-3">
                                <span class="badge bg-primary">
                                    <i class="bi bi-building me-1"></i> Laboratorios
                                </span>
                                <span class="badge bg-success">
                                    <i class="bi bi-pc-display me-1"></i> Equipos
                                </span>
                                <span class="badge bg-info">
                                    <i class="bi bi-chat-left-text me-1"></i> Comentarios
                                </span>
                            </div>
                        </div>
                        <div class="card-footer bg-primary text-white text-center">
                            <i class="bi bi-arrow-right-circle me-1"></i> Acceder al Panel
                        </div>
                    </div>
                </a>
            </div>

            <!-- Consulta de Registros (placeholder) -->
            <div class="col-md-6 col-lg-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-text feature-icon text-success"></i>
                        <h5 class="card-title">Registros de Equipos</h5>
                        <p class="card-text text-muted">
                            Consulte el historial completo de registros de uso y estado de los equipos
                            en todos los laboratorios.
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-secondary">
                                <i class="bi bi-clock-history me-1"></i> Próximamente
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-muted text-center">
                        <i class="bi bi-hourglass-split me-1"></i> En desarrollo
                    </div>
                </div>
            </div>

            <!-- Reportes (placeholder) -->
            <div class="col-md-6 col-lg-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-bar-chart-line feature-icon text-warning"></i>
                        <h5 class="card-title">Reportes y Estadísticas</h5>
                        <p class="card-text text-muted">
                            Genere reportes detallados sobre el uso y estado de los equipos,
                            con gráficos y estadísticas.
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-secondary">
                                <i class="bi bi-graph-up me-1"></i> Próximamente
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-muted text-center">
                        <i class="bi bi-hourglass-split me-1"></i> En desarrollo
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de ayuda -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Información de Uso
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Gestión de Laboratorios:</h6>
                        <ul class="mb-3">
                            <li><strong>Visualización:</strong> Vea todos los laboratorios con sus equipos y el último registro de cada uno</li>
                            <li><strong>Estados:</strong> Los equipos se clasifican en: Funcionando, No funciona, En reparación, o Sin registro</li>
                            <li><strong>Comentarios:</strong> Agregue o actualice comentarios sobre el estado general de cada laboratorio</li>
                            <li><strong>Información detallada:</strong> Vea CPU, RAM, disco, IP y descripción de cada registro</li>
                        </ul>

                        <h6>Permisos del Docente:</h6>
                        <ul class="mb-0">
                            <li><i class="bi bi-check-circle text-success me-2"></i>Consultar laboratorios y equipos</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Ver registros históricos</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Agregar comentarios a laboratorios</li>
                            <li><i class="bi bi-x-circle text-danger me-2"></i>Modificar o eliminar equipos (solo administradores)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-1">&copy; 2025 Sistema de Gestión de Laboratorios</p>
            <p class="mb-0">
                <small>
                    <i class="bi bi-person-badge me-1"></i>
                    Panel Docente - <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?>
                </small>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
