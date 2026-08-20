document.addEventListener('DOMContentLoaded', function () {
    console.log('✅ DOMContentLoaded - Gestión de Equipos iniciado');
    
    let equipos = [];
    let mostrarInactivos = false;

    // Año en el footer
    document.getElementById('currentYear').textContent = new Date().getFullYear();

    // Cargar equipos al iniciar
    cargarEquipos();

    // ==================== EVENT LISTENERS ====================
    const btnRecargar = document.getElementById('btnRecargar');
    const btnGuardarEquipo = document.getElementById('btnGuardarEquipo');
    const btnActualizarEquipo = document.getElementById('btnActualizarEquipo');
    const btnLimpiarBusqueda = document.getElementById('btnLimpiarBusqueda');
    const buscarEquipo = document.getElementById('buscarEquipo');
    const filtroTipoDisco = document.getElementById('filtroTipoDisco');
    const btnControlEquipos = document.getElementById('btnControlEquipos');
    const btnProcesarAltaMasiva = document.getElementById('btnProcesarAltaMasiva');
    const btnToggleInactivos = document.getElementById('btnToggleInactivos');

    if (btnRecargar) btnRecargar.addEventListener('click', cargarEquipos);
    if (btnGuardarEquipo) btnGuardarEquipo.addEventListener('click', guardarEquipo);
    if (btnActualizarEquipo) btnActualizarEquipo.addEventListener('click', actualizarEquipo);
    if (btnLimpiarBusqueda) btnLimpiarBusqueda.addEventListener('click', limpiarBusqueda);
    if (buscarEquipo) buscarEquipo.addEventListener('input', filtrarEquipos);
    if (filtroTipoDisco) filtroTipoDisco.addEventListener('change', filtrarEquipos);
    if (btnControlEquipos) btnControlEquipos.addEventListener('click', controlarEquipos);
    if (btnProcesarAltaMasiva) btnProcesarAltaMasiva.addEventListener('click', procesarAltaMasiva);
    if (btnToggleInactivos) btnToggleInactivos.addEventListener('click', toggleInactivos);

    // ==================== FUNCIONES PRINCIPALES ====================
    
    async function cargarEquipos() {
        const cuerpoTabla = document.getElementById('cuerpoTablaEquipos');
        const sinEquipos = document.getElementById('sinEquipos');
        
        if (!cuerpoTabla) {
            console.error('❌ No se encontró el elemento cuerpoTablaEquipos');
            return;
        }
        
        cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Cargando equipos...</p>
                </td>
            </tr>
        `;
        
        if (sinEquipos) sinEquipos.classList.add('d-none');

        try {
            const soloActivos = !mostrarInactivos;
            console.log(`📡 Cargando equipos... soloActivos: ${soloActivos}`);
            
            const response = await fetch(`../Controllers/EquipoController.php?action=obtenerTodos&soloActivos=${soloActivos}`);
            const data = await response.json();
            
            console.log('📦 Respuesta del servidor:', data);

            if (data.success) {
                equipos = data.data;
                console.log(`✅ Equipos cargados: ${equipos.length}`);
                mostrarEquiposEnTabla(equipos);
                actualizarContadores(equipos);
            } else {
                cuerpoTabla.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-5 text-danger">
                            ❌ ${data.error}
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            console.error('❌ Error cargando equipos:', error);
            cuerpoTabla.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5 text-danger">
                        ❌ Error de conexión: ${error.message}
                    </td>
                </tr>
            `;
        }
    }

    function mostrarEquiposEnTabla(equiposMostrar) {
        const cuerpoTabla = document.getElementById('cuerpoTablaEquipos');
        const sinEquipos = document.getElementById('sinEquipos');
        const tabla = cuerpoTabla.closest('table');

        cuerpoTabla.innerHTML = '';

        if (equiposMostrar.length === 0) {
            if (sinEquipos) sinEquipos.classList.remove('d-none');
            if (tabla) tabla.style.display = 'none';
            console.log('⚠️ No hay equipos para mostrar');
            return;
        }
        
        if (sinEquipos) sinEquipos.classList.add('d-none');
        if (tabla) tabla.style.display = '';

        console.log(`📋 Mostrando ${equiposMostrar.length} equipos en la tabla`);

        equiposMostrar.forEach(equipo => {
            const fila = cuerpoTabla.insertRow();
            const estadoBadge = equipo.activo 
                ? '<span class="badge bg-success">Activo</span>' 
                : '<span class="badge bg-secondary">Inactivo</span>';
            
            fila.innerHTML = `
                <td>
                    <div class="fw-bold">${equipo.hostname}</div>
                    <small class="text-muted">S/N: ${equipo.serialNumber || 'N/A'}</small>
                </td>
                <td>${equipo.laboratorio_nombre || 'No asignado'}</td>
                <td>${equipo.CPU}</td>
                <td class="text-center">${equipo.RAM} GB</td>
                <td>${equipo.diskTotal} GB <span class="badge bg-secondary rounded-pill">${equipo.diskType}</span></td>
                <td class="text-center">${estadoBadge}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-info btn-ver" title="Ver Detalles">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-warning btn-editar" title="Editar Equipo">
                        <i class="bi bi-pencil"></i>
                    </button>
                    ${equipo.activo 
                        ? `<button class="btn btn-sm btn-outline-danger btn-baja" title="Dar de Baja">
                            <i class="bi bi-x-circle"></i>
                           </button>`
                        : `<button class="btn btn-sm btn-outline-success btn-reactivar" title="Reactivar">
                            <i class="bi bi-check-circle"></i>
                           </button>`
                    }
                </td>
            `;

            // Event listeners para los botones de la fila
            fila.querySelector('.btn-ver').addEventListener('click', () => verEquipo(equipo.idEquipo));
            fila.querySelector('.btn-editar').addEventListener('click', () => editarEquipo(equipo.idEquipo));
            
            if (equipo.activo) {
                fila.querySelector('.btn-baja').addEventListener('click', () => darDeBaja(equipo.idEquipo));
            } else {
                fila.querySelector('.btn-reactivar').addEventListener('click', () => reactivarEquipo(equipo.idEquipo));
            }
        });
    }

    function actualizarContadores(equipos) {
        const total = equipos.filter(e => e.activo).length;
        const ssd = equipos.filter(e => e.diskType === 'SSD' && e.activo).length;
        const ramAlta = equipos.filter(e => e.RAM >= 8 && e.activo).length;
        const discoGrande = equipos.filter(e => e.diskTotal >= 500 && e.activo).length;

        const contadorTotal = document.getElementById('contadorTotal');
        const contadorSSD = document.getElementById('contadorSSD');
        const contadorRAM = document.getElementById('contadorRAM');
        const contadorDisco = document.getElementById('contadorDisco');

        if (contadorTotal) contadorTotal.textContent = total;
        if (contadorSSD) contadorSSD.textContent = ssd;
        if (contadorRAM) contadorRAM.textContent = ramAlta;
        if (contadorDisco) contadorDisco.textContent = discoGrande;

        console.log(`📊 Contadores actualizados - Total: ${total}, SSD: ${ssd}, RAM≥8: ${ramAlta}, Disco≥500: ${discoGrande}`);
    }

    function filtrarEquipos() {
        const busqueda = document.getElementById('buscarEquipo')?.value.toLowerCase() || '';
        const tipoDisco = document.getElementById('filtroTipoDisco')?.value || '';
        
        let equiposFiltrados = equipos;
        
        if (busqueda) {
            equiposFiltrados = equiposFiltrados.filter(equipo => 
                equipo.hostname.toLowerCase().includes(busqueda) ||
                equipo.CPU.toLowerCase().includes(busqueda) ||
                (equipo.serialNumber && equipo.serialNumber.toString().includes(busqueda))
            );
        }
        
        if (tipoDisco) {
            equiposFiltrados = equiposFiltrados.filter(equipo => 
                equipo.diskType === tipoDisco
            );
        }
        
        console.log(`🔍 Filtrado: ${equiposFiltrados.length} equipos de ${equipos.length}`);
        mostrarEquiposEnTabla(equiposFiltrados);
    }

    function limpiarBusqueda() {
        const buscarEquipo = document.getElementById('buscarEquipo');
        const filtroTipoDisco = document.getElementById('filtroTipoDisco');
        
        if (buscarEquipo) buscarEquipo.value = '';
        if (filtroTipoDisco) filtroTipoDisco.value = '';
        
        mostrarEquiposEnTabla(equipos);
        console.log('🧹 Búsqueda limpiada');
    }

    function toggleInactivos() {
        mostrarInactivos = !mostrarInactivos;
        const btn = document.getElementById('btnToggleInactivos');
        
        if (btn) {
            btn.innerHTML = mostrarInactivos 
                ? '<i class="bi bi-eye-slash me-1"></i> Ocultar Inactivos'
                : '<i class="bi bi-eye me-1"></i> Ver Inactivos';
        }
        
        console.log(`👁️ Toggle inactivos: ${mostrarInactivos ? 'Mostrar' : 'Ocultar'}`);
        cargarEquipos();
    }

    // ==================== CRUD OPERATIONS ====================

    async function guardarEquipo() {
        const form = document.getElementById('formAgregarEquipo');
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const datos = {
            serialNumber: document.getElementById('serialNumber').value,
            hostname: document.getElementById('hostname').value,
            CPU: document.getElementById('CPU').value,
            RAM: document.getElementById('RAM').value,
            diskType: document.getElementById('diskType').value,
            diskTotal: document.getElementById('diskTotal').value
        };

        console.log('💾 Guardando equipo:', datos);
        
        try {
            const response = await fetch('../Controllers/EquipoController.php?action=crearEquipo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            });

            const data = await response.json();
            console.log('✅ Respuesta servidor:', data);
            
            if (data.success) {
                alert('✅ ' + data.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarEquipo'));
                if (modal) modal.hide();
                form.reset();
                cargarEquipos();
            } else {
                alert('❌ Error: ' + data.error);
            }
        } catch (error) {
            console.error('❌ Error guardando equipo:', error);
            alert('❌ Error de conexión al guardar el equipo');
        }
    }

    async function verEquipo(idEquipo) {
        console.log(`👁️ Ver equipo ID: ${idEquipo}`);
        
        try {
            const response = await fetch(`../Controllers/EquipoController.php?action=obtenerEquipo&idEquipo=${idEquipo}`);
            const data = await response.json();
            
            if (data.success) {
                const equipo = data.data;
                
                document.getElementById('viewSerialNumber').textContent = equipo.serialNumber;
                document.getElementById('viewHostname').textContent = equipo.hostname;
                document.getElementById('viewCPU').textContent = equipo.CPU;
                document.getElementById('viewRAM').textContent = equipo.RAM + ' GB';
                document.getElementById('viewDiskTotal').textContent = equipo.diskTotal + ' GB';
                document.getElementById('viewDiskType').textContent = equipo.diskType;
                
                const modal = new bootstrap.Modal(document.getElementById('modalVerEquipo'));
                modal.show();
            } else {
                alert('❌ Error: ' + data.error);
            }
        } catch (error) {
            console.error('❌ Error obteniendo equipo:', error);
            alert('❌ Error de conexión al obtener el equipo');
        }
    }

    async function editarEquipo(idEquipo) {
        console.log(`✏️ Editar equipo ID: ${idEquipo}`);
        
        try {
            const response = await fetch(`../Controllers/EquipoController.php?action=obtenerEquipo&idEquipo=${idEquipo}`);
            const data = await response.json();
            
            if (data.success) {
                const equipo = data.data;
                
                document.getElementById('editIdEquipo').value = equipo.idEquipo;
                document.getElementById('displaySerialNumber').textContent = equipo.serialNumber;
                document.getElementById('editHostname').value = equipo.hostname;
                document.getElementById('editCPU').value = equipo.CPU;
                document.getElementById('editRAM').value = equipo.RAM;
                document.getElementById('editDiskTotal').value = equipo.diskTotal;
                document.getElementById('editDiskType').value = equipo.diskType;
                
                const modal = new bootstrap.Modal(document.getElementById('modalEditarEquipo'));
                modal.show();
            } else {
                alert('❌ Error: ' + data.error);
            }
        } catch (error) {
            console.error('❌ Error obteniendo equipo:', error);
            alert('❌ Error de conexión al obtener el equipo');
        }
    }

    async function actualizarEquipo() {
        const form = document.getElementById('formEditarEquipo');
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const datos = {
            idEquipo: document.getElementById('editIdEquipo').value,
            hostname: document.getElementById('editHostname').value,
            CPU: document.getElementById('editCPU').value,
            RAM: document.getElementById('editRAM').value,
            diskType: document.getElementById('editDiskType').value,
            diskTotal: document.getElementById('editDiskTotal').value
        };

        console.log('💾 Actualizando equipo:', datos);
        
        try {
            const response = await fetch('../Controllers/EquipoController.php?action=actualizarEquipo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            });

            const data = await response.json();
            console.log('✅ Respuesta servidor:', data);
            
            if (data.success) {
                alert('✅ ' + data.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarEquipo'));
                if (modal) modal.hide();
                cargarEquipos();
            } else {
                alert('❌ Error: ' + data.error);
            }
        } catch (error) {
            console.error('❌ Error actualizando equipo:', error);
            alert('❌ Error de conexión al actualizar el equipo');
        }
    }

    async function darDeBaja(idEquipo) {
        if (!confirm('¿Está seguro de dar de baja este equipo?\n\nNo se eliminará, solo se marcará como inactivo.')) {
            return;
        }
        
        console.log(`🔴 Dando de baja equipo ID: ${idEquipo}`);
        
        try {
            const response = await fetch(`../Controllers/EquipoController.php?action=darDeBaja&idEquipo=${idEquipo}`);
            const data = await response.json();
            
            if (data.success) {
                alert('✅ ' + data.message);
                cargarEquipos();
            } else {
                alert('❌ Error: ' + data.error);
            }
        } catch (error) {
            console.error('❌ Error dando de baja equipo:', error);
            alert('❌ Error de conexión');
        }
    }

    async function reactivarEquipo(idEquipo) {
        if (!confirm('¿Desea reactivar este equipo?')) {
            return;
        }
        
        console.log(`🟢 Reactivando equipo ID: ${idEquipo}`);
        
        try {
            const response = await fetch(`../Controllers/EquipoController.php?action=reactivar&idEquipo=${idEquipo}`);
            const data = await response.json();
            
            if (data.success) {
                alert('✅ ' + data.message);
                cargarEquipos();
            } else {
                alert('❌ Error: ' + data.error);
            }
        } catch (error) {
            console.error('❌ Error reactivando equipo:', error);
            alert('❌ Error de conexión');
        }
    }

    async function controlarEquipos() {
        console.log('🔍 Controlando equipos sin reportar...');
        
        try {
            const response = await fetch('../Controllers/EquipoController.php?action=controladorEquipo');
            const data = await response.json();
            
            if (data.success) {
                const equiposDesconectados = data.data;
                if (equiposDesconectados.length === 0) {
                    alert('✅ Todos los equipos reportan estado correctamente');
                } else {
                    let mensaje = `⚠️ ${equiposDesconectados.length} equipo(s) sin reportar estado reciente:\n\n`;
                    equiposDesconectados.forEach(equipo => {
                        mensaje += `• ${equipo.hostname} (${equipo.serialNumber})\n`;
                    });
                    alert(mensaje);
                }
            } else {
                alert('❌ Error: ' + data.error);
            }
        } catch (error) {
            console.error('❌ Error controlando equipos:', error);
            alert('❌ Error de conexión al controlar equipos');
        }
    }

    async function procesarAltaMasiva() {
        const form = document.getElementById('formAltaMasiva');
        const fileInput = document.getElementById('archivoEquipos');
        
        if (!fileInput || !fileInput.files.length) {
            alert('⚠️ Por favor selecciona un archivo CSV');
            return;
        }

        const formData = new FormData(form);
        
        console.log('📤 Procesando alta masiva...');
        
        try {
            const response = await fetch('../Controllers/EquipoController.php?action=altaMasiva', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            console.log('✅ Resultado alta masiva:', data);
            
            if (data.success) {
                let mensaje = data.message;
                if (data.errores && data.errores.length > 0) {
                    mensaje += '\n\n⚠️ Errores:\n' + data.errores.join('\n');
                }
                alert(mensaje);
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAltaMasiva'));
                if (modal) modal.hide();
                form.reset();
                cargarEquipos();
            } else {
                alert('❌ Error: ' + data.error);
            }
        } catch (error) {
            console.error('❌ Error procesando alta masiva:', error);
            alert('❌ Error de conexión al procesar el archivo');
        }
    }

    console.log('✅ Sistema de gestión de equipos cargado correctamente');
});