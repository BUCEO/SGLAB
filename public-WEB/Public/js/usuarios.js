$(document).ready(function() {
    let usuarios = [];
    let mostrarInactivos = false;

    cargarUsuarios();
    cargarRoles();

    // Event listeners
    $('#btnGuardarUsuario').click(guardarUsuario);
    $('#btnActualizarUsuario').click(actualizarUsuario);
    $('#btnRecargar').click(cargarUsuarios);
    $('#btnLimpiarBusqueda').click(limpiarBusqueda);
    $('#buscarUsuario').on('input', filtrarUsuarios);
    $('#filtroRol').on('change', filtrarUsuarios);
    $('#btnImportarUsuarios').click(procesarImportacion);
    $('#btnExportarCSV').click(exportarCSV);
    $('#archivoUsuarios').on('change', function() {
        $('#btnImportarUsuarios').prop('disabled', !this.files.length);
    });

    // Limpiar modales al cerrar
    $('#modalAgregarUsuario').on('hidden.bs.modal', limpiarModalAgregar);
    $('#modalEditarUsuario').on('hidden.bs.modal', limpiarModalEditar);

    async function cargarUsuarios() {
        const filtroTipo = $('#filtroRol').val();
        const params = new URLSearchParams();
        
        if (!mostrarInactivos) params.append('soloActivos', 'true');
        if (filtroTipo) params.append('filtroTipo', filtroTipo);

        try {
            const response = await fetch(`../Controllers/usuariosController.php?action=obtenerUsuarios&${params}`);
            const data = await response.json();

            if (data.success) {
                usuarios = data.data;
                mostrarUsuariosEnTabla(usuarios);
                actualizarContadores(usuarios);
            } else {
                mostrarError('Error al cargar usuarios: ' + data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error de conexión al cargar usuarios');
        }
    }

    function mostrarUsuariosEnTabla(usuariosMostrar) {
        const tbody = $('#tbodyUsuarios');
        tbody.empty();

        if (usuariosMostrar.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <i class="bi bi-inbox display-4 text-muted"></i>
                        <p class="text-muted mt-2">No se encontraron usuarios</p>
                    </td>
                </tr>
            `);
            return;
        }

        usuariosMostrar.forEach(usuario => {
            // Determinar badge de rol según idTipoUsuario
            let rolBadge = '';
            switch(usuario.idTipoUsuario) {
                case 1:
                    rolBadge = '<span class="badge bg-danger">Administrador</span>';
                    break;
                case 2:
                    rolBadge = '<span class="badge bg-info">Docente</span>';
                    break;
                case 3:
                    rolBadge = '<span class="badge bg-primary">Estudiante</span>';
                    break;
                default:
                    rolBadge = '<span class="badge bg-secondary">Usuario</span>';
            }
            
            const estadoBadge = usuario.activo 
                ? '<span class="badge bg-success ms-1">Activo</span>' 
                : '<span class="badge bg-secondary ms-1">Inactivo</span>';

            const row = $(`
                <tr>
                    <td>${usuario.ci || 'N/A'}</td>
                    <td>${usuario.nombre || 'N/A'}</td>
                    <td>${usuario.email || 'N/A'}</td>
                    <td>${rolBadge}${estadoBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-info btn-ver" title="Ver detalles">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning btn-editar" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        ${usuario.activo 
                            ? `<button class="btn btn-sm btn-outline-danger btn-eliminar" title="Dar de baja">
                                <i class="bi bi-x-circle"></i>
                               </button>`
                            : `<button class="btn btn-sm btn-outline-success btn-reactivar" title="Reactivar">
                                <i class="bi bi-check-circle"></i>
                               </button>`
                        }
                    </td>
                </tr>
            `);

            row.find('.btn-ver').click(() => verUsuario(usuario));
            row.find('.btn-editar').click(() => editarUsuario(usuario));
            
            if (usuario.activo) {
                row.find('.btn-eliminar').click(() => eliminarUsuario(usuario.id));
            } else {
                row.find('.btn-reactivar').click(() => reactivarUsuario(usuario.id));
            }

            tbody.append(row);
        });
    }

    function actualizarContadores(usuarios) {
        const total = usuarios.filter(u => u.activo).length;
        const admins = usuarios.filter(u => u.idTipoUsuario === 1 && u.activo).length;
        const estudiantes = usuarios.filter(u => u.idTipoUsuario === 3 && u.activo).length;

        $('#contadorTotal').text(total);
        $('#contadorAdmin').text(admins);
        $('#contadorActivos').text(estudiantes);
    }

    function filtrarUsuarios() {
        const busqueda = $('#buscarUsuario').val().toLowerCase();
        const rol = $('#filtroRol').val();

        let usuariosFiltrados = usuarios;

        if (busqueda) {
            usuariosFiltrados = usuariosFiltrados.filter(usuario => 
                (usuario.ci && usuario.ci.toString().includes(busqueda)) ||
                (usuario.nombre && usuario.nombre.toLowerCase().includes(busqueda)) ||
                (usuario.email && usuario.email.toLowerCase().includes(busqueda))
            );
        }

        if (rol) {
            const rolInt = parseInt(rol);
            usuariosFiltrados = usuariosFiltrados.filter(u => u.idTipoUsuario === rolInt);
        }

        mostrarUsuariosEnTabla(usuariosFiltrados);
    }

    function limpiarBusqueda() {
        $('#buscarUsuario').val('');
        $('#filtroRol').val('');
        mostrarUsuariosEnTabla(usuarios);
    }

    async function cargarRoles() {
        try {
            const response = await fetch('../Controllers/usuariosController.php?action=obtenerRoles');
            const data = await response.json();

            if (data.success) {
                const selectRol = $('#rol');
                const selectEditRol = $('#editRol');
                
                selectRol.empty().append('<option value="">Seleccionar...</option>');
                selectEditRol.empty();
                
                data.data.forEach(rol => {
                    const option = `<option value="${rol.idTipoUsuario}">${rol.nombre}</option>`;
                    selectRol.append(option);
                    selectEditRol.append(option);
                });
            }
        } catch (error) {
            console.error('Error cargando roles:', error);
        }
    }

    async function guardarUsuario() {
        const nombre = $('#nombre').val().trim();
        const apellido = $('#apellido').val().trim();
        
        const formData = {
            ci: $('#ci').val(),
            nombre: nombre + ' ' + apellido,
            username: nombre.toLowerCase() + '.' + apellido.toLowerCase(),
            email: $('#mail').val(),
            password: $('#password').val(),
            idTipoUsuario: parseInt($('#rol').val())
        };

        // Validaciones
        if (!formData.ci || !nombre || !apellido || !formData.email || !formData.password || !formData.idTipoUsuario) {
            mostrarError('Todos los campos son requeridos');
            return;
        }

        try {
            const response = await fetch('../Controllers/usuariosController.php?action=crearUsuario', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                mostrarExito(data.message);
                $('#modalAgregarUsuario').modal('hide');
                cargarUsuarios();
            } else {
                mostrarError(data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error de conexión al guardar usuario');
        }
    }

    function verUsuario(usuario) {
        // Obtener nombre del rol
        let nombreRol = 'Usuario';
        switch(usuario.idTipoUsuario) {
            case 1: nombreRol = 'Administrador'; break;
            case 2: nombreRol = 'Docente'; break;
            case 3: nombreRol = 'Estudiante'; break;
        }

        const modalContent = `
            <div class="modal fade" id="modalVerUsuario" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title"><i class="bi bi-person-circle me-2"></i>Detalles del Usuario</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">CI:</label>
                                    <p class="form-control-plaintext">${usuario.ci || 'N/A'}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Rol:</label>
                                    <p class="form-control-plaintext">${nombreRol}</p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre Completo:</label>
                                <p class="form-control-plaintext">${usuario.nombre || 'N/A'}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email:</label>
                                <p class="form-control-plaintext">${usuario.email || 'N/A'}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Username:</label>
                                <p class="form-control-plaintext">${usuario.username || 'N/A'}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Estado:</label>
                                <p class="form-control-plaintext">
                                    ${usuario.activo 
                                        ? '<span class="badge bg-success">Activo</span>' 
                                        : '<span class="badge bg-secondary">Inactivo</span>'}
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#modalVerUsuario').remove();
        $('body').append(modalContent);
        $('#modalVerUsuario').modal('show');
    }

    function editarUsuario(usuario) {
        // Separar nombre completo
        const nombreCompleto = usuario.nombre || '';
        const partesNombre = nombreCompleto.trim().split(' ');
        const primerNombre = partesNombre[0] || '';
        const apellido = partesNombre.slice(1).join(' ') || '';

        $('#editId').val(usuario.id);
        $('#editCi').val(usuario.ci);
        $('#editNombre').val(primerNombre);
        $('#editApellido').val(apellido);
        $('#editMail').val(usuario.email);
        $('#editRol').val(usuario.idTipoUsuario);
        $('#editPassword').val('');

        $('#modalEditarUsuario').modal('show');
    }

    async function actualizarUsuario() {
        const nombre = $('#editNombre').val().trim();
        const apellido = $('#editApellido').val().trim();
        
        const formData = {
            ci: $('#editCi').val(),
            nombre: nombre + ' ' + apellido,
            username: nombre.toLowerCase() + '.' + apellido.toLowerCase(),
            email: $('#editMail').val(),
            idTipoUsuario: parseInt($('#editRol').val())
        };

        const password = $('#editPassword').val();
        if (password) {
            formData.password = password;
        }

        const id = $('#editId').val();

        try {
            const response = await fetch(`../Controllers/usuariosController.php?action=actualizarUsuario&id=${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                mostrarExito(data.message);
                $('#modalEditarUsuario').modal('hide');
                cargarUsuarios();
            } else {
                mostrarError(data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error de conexión al actualizar usuario');
        }
    }

    async function eliminarUsuario(id) {
        if (!confirm('¿Está seguro de dar de baja este usuario? No se eliminará, solo se marcará como inactivo.')) {
            return;
        }

        try {
            const response = await fetch(`../Controllers/usuariosController.php?action=eliminarUsuario&id=${id}`);
            const data = await response.json();

            if (data.success) {
                mostrarExito(data.message);
                cargarUsuarios();
            } else {
                mostrarError(data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error de conexión al eliminar usuario');
        }
    }

    async function reactivarUsuario(id) {
        if (!confirm('¿Desea reactivar este usuario?')) {
            return;
        }

        mostrarExito('Funcionalidad de reactivación pendiente de implementar');
    }

    async function procesarImportacion() {
        const fileInput = $('#archivoUsuarios')[0];
        
        if (!fileInput.files.length) {
            mostrarError('Por favor selecciona un archivo CSV');
            return;
        }

        const formData = new FormData();
        formData.append('archivo', fileInput.files[0]);

        try {
            const response = await fetch('../Controllers/usuariosController.php?action=importarCSV', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                let mensaje = data.message;
                
                if (data.errores && data.errores.length > 0) {
                    mensaje += '\n\nDetalles:\n';
                    mensaje += `Total procesados: ${data.total_procesados}\n`;
                    mensaje += `Creados exitosamente: ${data.creados}\n\n`;
                    mensaje += 'Errores encontrados:\n' + data.errores.slice(0, 10).join('\n');
                    
                    if (data.errores.length > 10) {
                        mensaje += `\n... y ${data.errores.length - 10} errores más`;
                    }
                }

                alert(mensaje);
                $('#modalImportarUsuarios').modal('hide');
                $('#archivoUsuarios').val('');
                $('#btnImportarUsuarios').prop('disabled', true);
                cargarUsuarios();
            } else {
                mostrarError(data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error de conexión al importar usuarios');
        }
    }

    async function exportarCSV() {
        try {
            const response = await fetch('../Controllers/usuariosController.php?action=exportarCSV');
            const blob = await response.blob();
            
            // Crear URL y descargar
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `estudiantes_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
            
            mostrarExito('Archivo CSV descargado correctamente');
        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error al exportar CSV');
        }
    }

    function limpiarModalAgregar() {
        $('#formAgregarUsuario')[0].reset();
    }

    function limpiarModalEditar() {
        $('#formEditarUsuario')[0].reset();
    }

    function mostrarError(mensaje) {
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; max-width: 500px;" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('body').append(alertHtml);
        
        setTimeout(() => {
            $('.alert').fadeOut(() => $(this).remove());
        }, 5000);
    }

    function mostrarExito(mensaje) {
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; max-width: 500px;" role="alert">
                <i class="bi bi-check-circle me-2"></i>${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('body').append(alertHtml);
        
        setTimeout(() => {
            $('.alert').fadeOut(() => $(this).remove());
        }, 5000);
    }
});