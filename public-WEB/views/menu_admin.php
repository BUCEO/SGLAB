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
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Public/css/estilos_menu_admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                        <a class="nav-link active" id="navEstado">
                            <i class="bi bi-tools me-1"></i> Registros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="equipos.php" id="navAgregar">
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
        <!-- Tarjetas de resumen de estados -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="bi bi-check-circle-fill me-2"></i>Activos</h5>
                        <h2 id="contadorActivos" class="card-text">0</h2>
                        <p class="card-text">Equipos operativos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="bi bi-x-circle-fill me-2"></i>Inactivos</h5>
                        <h2 id="contadorInactivos" class="card-text">0</h2>
                        <p class="card-text">Equipos apagados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="bi bi-tools me-2"></i>En Reparación</h5>
                        <h2 id="contadorReparacion" class="card-text">0</h2>
                        <p class="card-text">Equipos en mantenimiento</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="bi bi-hdd me-2"></i>Espacio Libre</h5>
                        <h2 id="contadorEspacioLibre" class="card-text">0 GB</h2>
                        <p class="card-text">Promedio disponible</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Consultar Registros de Equipos</h5>
                        <div class="d-flex gap-2">
                            <button id="btnCambiarVista" class="btn btn-sm btn-outline-success" title="Cambiar vista">
                                <i class="bi bi-arrow-repeat me-1"></i> Ver Históricos
                            </button>
                            <button id="btnMostrarFiltros" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-funnel"></i> Filtros
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Sección de Filtros -->
                        <div id="filtrosContainer" class="mb-4 p-3 border rounded" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label for="filtroEquipo" class="form-label">Equipo</label>
                                    <select id="filtroEquipo" class="form-select">
                                        <option value="">Todos los equipos</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="filtroEstado" class="form-label">Estado</label>
                                    <select id="filtroEstado" class="form-select">
                                        <option value="">Todos los estados</option>
                                        <option value="true">Activo</option>
                                        <option value="false">Inactivo</option>
                                        <option value="Funciona">Funcionando</option>
                                        <option value="No funciona">No Funciona</option>
                                        <option value="En reparacion">En Reparación</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="filtroLaboratorio" class="form-label">Laboratorio</label>
                                    <select id="filtroLaboratorio" class="form-select">
                                        <option value="">Todos los laboratorios</option>
                                        
                                        <!-- Los laboratorios se cargarán dinámicamente -->
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="filtroFechaInicio" class="form-label">Fecha Desde</label>
                                    <input type="text" id="filtroFechaInicio" class="form-control datepicker" placeholder="Seleccione fecha">
                                </div>
                                <div class="col-md-2">
                                    <label for="filtroFechaFin" class="form-label">Fecha Hasta</label>
                                    <input type="text" id="filtroFechaFin" class="form-control datepicker" placeholder="Seleccione fecha">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="w-100">
                                        <label class="form-label invisible">Acciones</label>
                                        <div class="d-grid gap-2">
                                            <button id="btnAplicarFiltros" class="btn btn-primary">
                                                <i class="bi bi-filter"></i> Aplicar
                                            </button>
                                            <button id="btnLimpiarFiltros" class="btn btn-outline-secondary">
                                                <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contador de resultados -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div id="contadorResultados" class="text-muted small">
                                Mostrando 0 de 0 registros
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="me-2 small">Registros por página:</span>
                                <select id="filasPorPagina" class="form-select form-select-sm w-auto">
                                    <option value="20" selected>20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tabla de resultados -->
                        <div class="table-responsive">
                            <table id="tablaRegistros" class="table table-striped table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Equipo</th>
                                        <th>Fecha-Hora</th>
                                        <th>Estado</th>
                                        <th>Espacio Libre</th>
                                        <th>Usuario</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoTabla">
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                            <p class="mt-2">Cargando registros...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación -->
                        <div class="row mt-3" id="paginacionContainer">
                            <div class="col-md-6">
                                <div id="infoPaginacion" class="text-muted small">
                                    Página 1 de 1
                                </div>
                            </div>
                            <div class="col-md-6">
                                <nav aria-label="Paginación" class="float-end">
                                    <ul class="pagination pagination-sm" id="paginacion">
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#" tabindex="-1">Anterior</a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item">
                                            <a class="page-link" href="#">Siguiente</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="../Public/js/jquery-3.7.1.js"></script>
    <script src="../Public/js/menu_admin.js"></script>
</body>
</html>