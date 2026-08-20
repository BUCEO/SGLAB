// Variables globales
let laboratoriosData = [];
let modalComentario;

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    modalComentario = new bootstrap.Modal(document.getElementById('modalComentario'));
    cargarEstadisticas();
    cargarLaboratorios();

    // Contador de caracteres
    document.getElementById('comentarioInput').addEventListener('input', function() {
        document.getElementById('contadorCaracteres').textContent = this.value.length;
    });

    // Guardar comentario
    document.getElementById('btnGuardarComentario').addEventListener('click', guardarComentario);
});

// Cargar estadísticas generales
async function cargarEstadisticas() {
    try {
        const response = await fetch('../Controllers/DocenteController.php?action=obtenerEstadisticas');
        const result = await response.json();

        if (result.success) {
            const stats = result.data;
            document.getElementById('totalLaboratorios').textContent = stats.total_laboratorios || 0;
            document.getElementById('equiposFuncionando').textContent = stats.equipos_funcionando || 0;
            document.getElementById('equiposProblemas').textContent = stats.equipos_con_problemas || 0;
            document.getElementById('equiposReparacion').textContent = stats.equipos_en_reparacion || 0;
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

// Cargar laboratorios con equipos
async function cargarLaboratorios() {
    try {
        const response = await fetch('../Controllers/DocenteController.php?action=obtenerLaboratoriosConEquipos');
        const result = await response.json();

        if (result.success) {
            laboratoriosData = result.data;
            renderizarLaboratorios(result.data);
        } else {
            mostrarError('Error al cargar laboratorios: ' + result.message);
        }
    } catch (error) {
        console.error('Error al cargar laboratorios:', error);
        mostrarError('Error de conexión al cargar laboratorios');
    }
}

// Renderizar laboratorios en el DOM
function renderizarLaboratorios(laboratorios) {
    const container = document.getElementById('laboratoriosContainer');

    if (!laboratorios || laboratorios.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No hay laboratorios disponibles
            </div>
        `;
        return;
    }

    let html = '';
    laboratorios.forEach(lab => {
        html += `
            <div class="card laboratorio-card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>
                        ${escapeHtml(lab.nombre)}
                    </h5>
                    <div>
                        <span class="badge bg-light text-dark me-2">
                            <i class="bi bi-pc-display me-1"></i>
                            ${lab.total_equipos} equipos
                        </span>
                        <button class="btn btn-sm btn-light" onclick="abrirModalComentario(${lab.idLaboratorio}, '${escapeHtml(lab.nombre)}', '${escapeHtml(lab.comentario || '')}')">
                            <i class="bi bi-chat-left-text me-1"></i> Comentario
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Resumen de estados -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Funcionando: <strong>${lab.equipos_funcionando}</strong></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                <span>Con problemas: <strong>${lab.equipos_con_problemas}</strong></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-tools text-warning me-2"></i>
                                <span>En reparación: <strong>${lab.equipos_en_reparacion}</strong></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-question-circle text-secondary me-2"></i>
                                <span>Sin registro: <strong>${lab.equipos_sin_registro}</strong></span>
                            </div>
                        </div>
                    </div>

                    ${lab.comentario ? `
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Comentario:</strong> ${escapeHtml(lab.comentario)}
                            <small class="text-muted d-block mt-1">
                                Última actualización: ${lab.updated_at ? new Date(lab.updated_at).toLocaleString('es-UY') : 'N/A'}
                            </small>
                        </div>
                    ` : ''}

                    <!-- Tabla de equipos -->
                    ${renderizarTablaEquipos(lab.equipos)}
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Renderizar tabla de equipos
function renderizarTablaEquipos(equipos) {
    if (!equipos || equipos.length === 0) {
        return `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                No hay equipos registrados en este laboratorio
            </div>
        `;
    }

    let html = `
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th><i class="bi bi-pc-display me-1"></i> Hostname</th>
                        <th><i class="bi bi-cpu me-1"></i> CPU</th>
                        <th><i class="bi bi-memory me-1"></i> RAM</th>
                        <th><i class="bi bi-hdd me-1"></i> Disco</th>
                        <th><i class="bi bi-activity me-1"></i> Estado</th>
                        <th><i class="bi bi-calendar me-1"></i> Último Registro</th>
                        <th><i class="bi bi-person me-1"></i> Usuario</th>
                        <th><i class="bi bi-file-text me-1"></i> Descripción</th>
                    </tr>
                </thead>
                <tbody>
    `;

    equipos.forEach(equipo => {
        const registro = equipo.ultimo_registro;
        html += `
            <tr class="equipo-row">
                <td><strong>${escapeHtml(equipo.hostname)}</strong></td>
                <td><small>${escapeHtml(equipo.CPU || 'N/A')}</small></td>
                <td>${equipo.RAM ? equipo.RAM + ' GB' : 'N/A'}</td>
                <td>${equipo.diskTotal ? equipo.diskTotal + ' GB (' + equipo.diskType + ')' : 'N/A'}</td>
                <td>
                    ${registro ? `
                        <span class="badge estado-badge bg-${registro.estado_clase}">
                            ${registro.estado_texto}
                        </span>
                    ` : `
                        <span class="badge estado-badge bg-secondary">Sin registro</span>
                    `}
                </td>
                <td>
                    <small>${registro ? registro.fecha_formateada : 'N/A'}</small>
                </td>
                <td>
                    <small>${registro ? escapeHtml(registro.usuario_nombre || 'N/A') : 'N/A'}</small>
                </td>
                <td>
                    <small>${registro && registro.descripcion ? escapeHtml(registro.descripcion) : '-'}</small>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    return html;
}

// Abrir modal para editar comentario
function abrirModalComentario(idLaboratorio, nombre, comentario) {
    document.getElementById('idLaboratorioModal').value = idLaboratorio;
    document.getElementById('nombreLaboratorioModal').textContent = nombre;
    document.getElementById('comentarioInput').value = comentario || '';
    document.getElementById('contadorCaracteres').textContent = (comentario || '').length;
    modalComentario.show();
}

// Guardar comentario
async function guardarComentario() {
    const idLaboratorio = document.getElementById('idLaboratorioModal').value;
    const comentario = document.getElementById('comentarioInput').value.trim();

    const btnGuardar = document.getElementById('btnGuardarComentario');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    try {
        const response = await fetch('../Controllers/DocenteController.php?action=actualizarComentario', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                idLaboratorio: idLaboratorio,
                comentario: comentario
            })
        });

        const result = await response.json();

        if (result.success) {
            mostrarExito('Comentario actualizado correctamente');
            modalComentario.hide();
            cargarLaboratorios();
            cargarEstadisticas();
        } else {
            mostrarError('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error al guardar comentario:', error);
        mostrarError('Error de conexión al guardar comentario');
    } finally {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save me-1"></i> Guardar Comentario';
    }
}

// Utilidades
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

function mostrarExito(mensaje) {
    // Puedes usar un toast o alert
    alert(mensaje);
}

function mostrarError(mensaje) {
    alert(mensaje);
}
