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
    <title>Administración de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../Public/css/usuarios.css">
</head>
<body>
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
                        <a class="nav-link" href="equipos.php" id="navAgregar">
                            <i class="bi bi-pc-display me-1"></i> Equipos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" id="navImportar">
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

    <main class="container-fluid py-4 usuarios-container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">Gestión de Usuarios</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarUsuario">
                        <i class="bi bi-plus-circle me-1"></i> Agregar Usuario
                    </button>
                </div>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        

        <!-- Estadísticas - CORREGIDO -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card estadistica-card total h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill"></i>
                        <h5 class="card-title">Total Usuarios</h5>
                        <h2 id="contadorTotal" class="card-text">0</h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card estadistica-card administradores h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-shield-check"></i>
                        <h5 class="card-title">Administradores</h5>
                        <h2 id="contadorAdmin" class="card-text">0</h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card estadistica-card activos h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-mortarboard-fill"></i>
                        <h5 class="card-title">Estudiantes</h5>
                        <h2 id="contadorActivos" class="card-text">0</h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card estadistica-card inactivos h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-upload"></i>
                        <h5 class="card-title">Importar CSV</h5>
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalImportarUsuarios">
                            <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir archivo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="buscarUsuario" class="form-control" 
                        placeholder="Buscar por CI, nombre o email...">
                    <button class="btn btn-outline-secondary" type="button" id="btnLimpiarBusqueda">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <select id="filtroRol" class="form-select">
                        <option value="">Todos los roles</option>
                        <option value="1">Administrador</option>
                        <option value="2">Docente</option>
                        <option value="3">Estudiante</option>
                    </select>
                    <button class="btn btn-outline-primary" id="btnRecargar">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tabla de usuarios -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Lista de Usuarios</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaUsuarios">
                                <thead>
                                    <tr>
                                        <th>CI</th>
                                        <th>Nombre Completo</th>
                                        <th>Email</th>
                                        <th>Rol / Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyUsuarios">
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                            <p class="mt-2">Cargando usuarios...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensaje cuando no hay usuarios -->
        <div class="row d-none" id="sinUsuarios">
            <div class="col-12 text-center py-5">
                <i class="bi bi-people display-1 text-muted"></i>
                <h4 class="text-muted mt-3">No se encontraron usuarios</h4>
                <p class="text-muted">Presiona el botón "Agregar Usuario" para comenzar</p>
            </div>
        </div>
    </main>

    <!-- Modal para agregar usuario -->
    <div class="modal fade" id="modalAgregarUsuario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Agregar Nuevo Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAgregarUsuario" class="form-usuario">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ci" class="form-label">Cédula de Identidad *</label>
                                    <input type="number" class="form-control" id="ci" name="ci" required>
                                    <div class="form-text">Número de cédula sin puntos ni guiones</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rol" class="form-label">Rol *</label>
                                    <select class="form-select" id="rol" name="rol" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="estudiante">Estudiante</option>
                                        <option value="Admin">Administrador</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="apellido" class="form-label">Apellido *</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="mail" name="mail" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="form-text">Mínimo 6 caracteres</div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarUsuario">
                        <i class="bi bi-check-circle me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar usuario -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarUsuario" class="form-usuario">
                        <input type="hidden" id="editId" name="id">
                        <input type="hidden" id="editCiOriginal" name="ci_original">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editCi" class="form-label">Cédula de Identidad *</label>
                                    <input type="number" class="form-control" id="editCi" name="ci" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editRol" class="form-label">Rol *</label>
                                    <select class="form-select" id="editRol" name="rol" required>
                                        <option value="estudiante">Estudiante</option>
                                        <option value="Admin">Administrador</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editNombre" class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="editNombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editApellido" class="form-label">Apellido *</label>
                                    <input type="text" class="form-control" id="editApellido" name="apellido" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="editMail" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="editMail" name="mail" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="editPassword" class="form-label">Nueva Contraseña (opcional)</label>
                                    <input type="password" class="form-control" id="editPassword" name="password">
                                    <div class="form-text">Dejar vacío para mantener la contraseña actual</div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnActualizarUsuario">
                        <i class="bi bi-check-circle me-1"></i>Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para importar usuarios -->
    <div class="modal fade" id="modalImportarUsuarios" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i>Importar Usuarios desde CSV
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Formato del archivo CSV:</strong><br>
                    El archivo debe tener 5 columnas separadas por comas en el siguiente orden:<br>
                    <code>Numero,Apellido,Nombre,Documento,e-mail</code>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Notas importantes:</strong>
                    <ul class="mb-0">
                        <li>Los usuarios se crearán como <strong>Estudiantes</strong> por defecto</li>
                        <li>La contraseña inicial será: <code>Password + últimos 4 dígitos del CI</code></li>
                        <li>Si el email está vacío, se generará automáticamente</li>
                        <li>Los usuarios duplicados (CI o email) serán omitidos</li>
                    </ul>
                </div>
                
                <form id="formImportarUsuarios">
                    <div class="mb-3">
                        <label for="archivoUsuarios" class="form-label">Seleccionar archivo CSV</label>
                        <input class="form-control" type="file" id="archivoUsuarios" accept=".csv" required>
                    </div>
                    
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Ejemplo de formato CSV:</h6>
                            <pre class="mb-0"><code>Numero,Apellido,Nombre,Documento,e-mail
1,ACEREDO ARAÚJO,AGUSTIN EZEQUIEL,53017311,agustin.aceredo@estudiante.edu.uy
2,ARANGO CADAVID,SANTIAGO,67000823,
3,RODRIGUEZ PEREZ,MARIA,45123456,maria.rodriguez@estudiante.edu.uy</code></pre>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="btnImportarUsuarios" disabled>
                    <i class="bi bi-upload me-1"></i>Importar Usuarios
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

    <script src="../Public/js/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Public/js/usuarios.js"></script>
</body>
</html>