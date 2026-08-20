<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login_usuario.html');
    exit(); 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Equipos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Public/css/equipos.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand">
                <img src="../Public/img/Logo-DGETP-UTU-ByN-Transparente-PNG.png" alt="Logo" height="40" class="d-inline-block align-top me-2">
                Panel de Administración
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarAdmin">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="menu_admin.php" id="navEstado">
                            <i class="bi bi-tools me-1"></i> Registros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" id="navAgregar">
                            <i class="bi bi-pc-display me-1"></i> Equipos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios.php" id="navImportar">
                            <i class="bi bi-people-fill me-1"></i> Usuarios
                        </a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="../Controllers/logoutController.php" class="btn btn-outline-light" id="btnSalir">
                        <i class="bi bi-box-arrow-right me-1"></i> Salir
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 container-fluid p-4">
        <!-- Header con botones de acción - CORREGIDO -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">Gestión de Equipos</h1>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary" id="btnToggleInactivos">
                            <i class="bi bi-eye me-1"></i> Ver Inactivos
                        </button>
                        <button class="btn btn-outline-info" id="btnControlEquipos">
                            <i class="bi bi-clipboard-check me-1"></i> Controlar Equipos
                        </button>
                        <button class="btn btn-outline-success" id="btnAltaMasiva" data-bs-toggle="modal" data-bs-target="#modalAltaMasiva">
                            <i class="bi bi-upload me-1"></i> Alta Masiva
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarEquipo">
                            <i class="bi bi-plus-circle me-1"></i> Agregar Equipo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-pc-display-horizontal fs-1 mb-2"></i>
                        <h5 class="card-title">Total Equipos</h5>
                        <h2 id="contadorTotal" class="card-text">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-device-ssd fs-1 mb-2"></i>
                        <h5 class="card-title">Equipos SSD</h5>
                        <h2 id="contadorSSD" class="card-text">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-memory fs-1 mb-2"></i>
                        <h5 class="card-title">RAM ≥ 8GB</h5>
                        <h2 id="contadorRAM" class="card-text">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-dark">
                    <div class="card-body text-center">
                        <i class="bi bi-hdd fs-1 mb-2"></i>
                        <h5 class="card-title">Disco ≥ 500GB</h5>
                        <h2 id="contadorDisco" class="card-text">0</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="buscarEquipo" class="form-control" placeholder="Buscar por serial, hostname o procesador...">
                    <button class="btn btn-outline-secondary" type="button" id="btnLimpiarBusqueda">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <select id="filtroTipoDisco" class="form-select">
                        <option value="">Todos los tipos de disco</option>
                        <option value="SSD">SSD</option>
                        <option value="HDD">HDD</option>
                        <option value="NVMe">NVMe</option>
                    </select>
                    <button class="btn btn-outline-primary" id="btnRecargar">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de equipos -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Hostname / S/N</th>
                                <th>Laboratorio</th>
                                <th>CPU</th>
                                <th class="text-center">RAM</th>
                                <th>Almacenamiento</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaEquipos">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary"></div>
                                    <p class="mt-2">Cargando equipos...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="sinEquipos" class="text-center py-5 d-none">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No se encontraron equipos</h4>
                    <p class="text-muted">Presiona el botón "Agregar Equipo" para comenzar</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para agregar equipo -->
    <div class="modal fade" id="modalAgregarEquipo" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Agregar Nuevo Equipo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAgregarEquipo">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="serialNumber" class="form-label">Serial Number *</label>
                                    <input type="text" class="form-control" id="serialNumber" name="serialNumber" required>
                                    <div class="form-text">Identificación única del equipo</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hostname" class="form-label">Hostname *</label>
                                    <input type="text" class="form-control" id="hostname" name="hostname" required>
                                    <div class="form-text">Ej: PC-LAB-A-01</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="CPU" class="form-label">Procesador (CPU) *</label>
                                    <input type="text" class="form-control" id="CPU" name="CPU" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="RAM" class="form-label">RAM (GB) *</label>
                                    <input type="number" class="form-control" id="RAM" name="RAM" min="1" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="diskTotal" class="form-label">Capacidad Total Disco (GB) *</label>
                                    <input type="number" class="form-control" id="diskTotal" name="diskTotal" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="diskType" class="form-label">Tipo de Disco *</label>
                                    <select class="form-select" id="diskType" name="diskType" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="SSD">SSD</option>
                                        <option value="HDD">HDD</option>
                                        <option value="NVMe">NVMe</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEquipo">
                        <i class="bi bi-check-circle me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar equipo -->
    <div class="modal fade" id="modalEditarEquipo" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Equipo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarEquipo">
                        <input type="hidden" id="editIdEquipo" name="idEquipo">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Serial Number</label>
                                    <p class="form-control-plaintext fw-bold" id="displaySerialNumber"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editHostname" class="form-label">Hostname *</label>
                                    <input type="text" class="form-control" id="editHostname" name="hostname" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editCPU" class="form-label">Procesador (CPU) *</label>
                                    <input type="text" class="form-control" id="editCPU" name="CPU" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editRAM" class="form-label">RAM (GB) *</label>
                                    <input type="number" class="form-control" id="editRAM" name="RAM" min="1" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editDiskTotal" class="form-label">Capacidad Total Disco (GB) *</label>
                                    <input type="number" class="form-control" id="editDiskTotal" name="diskTotal" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editDiskType" class="form-label">Tipo de Disco *</label>
                                    <select class="form-select" id="editDiskType" name="diskType" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="SSD">SSD</option>
                                        <option value="HDD">HDD</option>
                                        <option value="NVMe">NVMe</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnActualizarEquipo">
                        <i class="bi bi-check-circle me-1"></i>Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles -->
    <div class="modal fade" id="modalVerEquipo" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detalles del Equipo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Serial Number:</label>
                                <p id="viewSerialNumber" class="form-control-plaintext"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Hostname:</label>
                                <p id="viewHostname" class="form-control-plaintext"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Procesador (CPU):</label>
                                <p id="viewCPU" class="form-control-plaintext"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">RAM:</label>
                                <p id="viewRAM" class="form-control-plaintext"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Capacidad Total Disco:</label>
                                <p id="viewDiskTotal" class="form-control-plaintext"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tipo de Disco:</label>
                                <p id="viewDiskType" class="form-control-plaintext"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Alta Masiva -->
    <div class="modal fade" id="modalAltaMasiva" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Alta Masiva de Equipos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAltaMasiva" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="archivoEquipos" class="form-label">Seleccionar archivo CSV</label>
                            <input type="file" class="form-control" id="archivoEquipos" name="archivo" accept=".csv" required>
                            <div class="form-text">
                                El archivo CSV debe tener las columnas: serialNumber, hostname, CPU, RAM, diskType, diskTotal
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Formato requerido:</strong><br>
                            serialNumber,hostname,CPU,RAM,diskType,diskTotal<br>
                            10000006,PC01,Intel i5,8,SSD,256<br>
                            10000007,PC02,AMD Ryzen,16,NVMe,512
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnProcesarAltaMasiva">
                        <i class="bi bi-play-circle me-1"></i> Procesar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light py-3 mt-auto">
        <div class="container-fluid text-center">
            <img src="../Public/img/Logo-DGETP-UTU-ByN-Transparente-PNG.png" alt="Logo ANEP Y UTU" class="img-fluid footer-logo" style="max-height: 60px;">
            <div class="mt-2 text-muted small">
                &copy; <span id="currentYear"></span> Sistema de Gestión de Laboratorio
            </div>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Public/js/equipo.js"></script>
</body>
</html>