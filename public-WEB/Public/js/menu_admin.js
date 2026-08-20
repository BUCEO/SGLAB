document.addEventListener('DOMContentLoaded', function () {
    console.log('DOMContentLoaded funciona - Módulo de Registros');

    let registros = [];
    let registrosFiltrados = [];
    let paginaActual = 1;
    let registrosPorPagina = 20;
    let mostrandoUltimos = true;
    
    document.getElementById('currentYear').textContent = new Date().getFullYear();
    
    flatpickr(".datepicker", {
        locale: "es",
        dateFormat: "Y-m-d",
        allowInput: true
    });
    
    
    cargarLaboratorios(); 
    cargarEquipos();      
    cargarUltimosRegistros(); 
    
    // Event listeners
    document.getElementById('btnMostrarFiltros')?.addEventListener('click', toggleFiltros);
    document.getElementById('btnAplicarFiltros')?.addEventListener('click', aplicarFiltros);
    document.getElementById('btnLimpiarFiltros')?.addEventListener('click', limpiarFiltros);
    document.getElementById('btnCambiarVista')?.addEventListener('click', cambiarVista);
    document.getElementById('filasPorPagina')?.addEventListener('change', cambiarFilasPorPagina);

    async function cargarEquipos() {
        try {
            const response = await fetch('../Controllers/RegistroController.php?action=obtenerEquipos');
            console.log('Response equipos status:', response.status);
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Equipos recibidos:', data);
            
            if (data.success) {
                const select = document.getElementById('filtroEquipo');
                if (select) {
                    select.innerHTML = '<option value="">Todos los equipos</option>';
                    data.data.forEach(equipo => {
                        const option = document.createElement('option');
                        option.value = equipo.serialNumber;
                        option.textContent = equipo.hostname;
                        select.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error cargando equipos:', error);
        }
    }

    async function cargarLaboratorios() {
        console.log('Iniciando carga de laboratorios...');
        
        try {
            const response = await fetch('../Controllers/RegistroController.php?action=obtenerLaboratorios');
            console.log('Response laboratorios status:', response.status);
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const responseText = await response.text();
            console.log('Response text laboratorios:', responseText);
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('Error parsing JSON laboratorios:', e);
                throw new Error('Respuesta del servidor no es JSON válido');
            }
            
            console.log('Laboratorios recibidos:', data);

            if (data.success) {
                const select = document.getElementById('filtroLaboratorio');
                if (select) {
                    select.innerHTML = '<option value="">Todos los laboratorios</option>';
                    data.data.forEach(laboratorio => {
                        const option = document.createElement('option');
                        option.value = laboratorio.idLaboratorio;
                        option.textContent = laboratorio.nombre;
                        select.appendChild(option);
                    });
                    console.log('Laboratorios cargados desde la base de datos:', data.data.length);
                }
            } else {
                console.error('Error en respuesta de laboratorios:', data.error);
                cargarLaboratoriosHardcoded();
            }
        } catch (error) {
            console.error('Error cargando laboratorios desde API:', error);
            cargarLaboratoriosHardcoded();
        }
    }

    // Función de fallback con laboratorios hardcodeados
    function cargarLaboratoriosHardcoded() {
        console.log('Cargando laboratorios hardcodeados...');
        const laboratoriosHardcoded = [
            { idLaboratorio: 1, nombre: 'Laboratorio 1' },
            { idLaboratorio: 2, nombre: 'Laboratorio 2' },
            { idLaboratorio: 3, nombre: 'Laboratorio 3' },
            { idLaboratorio: 4, nombre: 'Laboratorio 4' },
            { idLaboratorio: 5, nombre: 'Laboratorio 5' }
        ];
        
        const select = document.getElementById('filtroLaboratorio');
        if (select) {
            select.innerHTML = '<option value="">Todos los laboratorios</option>';
            laboratoriosHardcoded.forEach(laboratorio => {
                const option = document.createElement('option');
                option.value = laboratorio.idLaboratorio;
                option.textContent = laboratorio.nombre;
                select.appendChild(option);
            });
            console.log('Laboratorios hardcodeados cargados correctamente');
        } else {
            console.error('No se encontró el elemento filtroLaboratorio en el DOM');
        }
    }

    async function cargarUltimosRegistros() {
        const cuerpoTabla = document.getElementById('cuerpoTabla');
        if (!cuerpoTabla) return;
        
        cuerpoTabla.innerHTML = `
          <tr>
            <td colspan="7" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2">Cargando últimos registros por equipo...</p>
            </td>
          </tr>
        `;

        try {
            const response = await fetch('../Controllers/RegistroController.php?action=obtenerUltimosPorEquipo');
            console.log('Response status:', response.status);
            
            const responseText = await response.text();
            console.log('Response text:', responseText);
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('Error parsing JSON:', e);
                throw new Error('Respuesta del servidor no es JSON válido');
            }

            console.log('Respuesta recibida:', data);
            
            if (data.success) {
                registros = data.data;
                registrosFiltrados = [...registros];
                mostrandoUltimos = true;
                paginaActual = 1;
                mostrarRegistrosPaginados();
                actualizarContadorResultados();
                actualizarPaginacion();
                actualizarContadoresEstado(registros);
                actualizarTituloTabla("Últimos registros por equipo");
            } else {
                cuerpoTabla.innerHTML = `
                  <tr>
                    <td colspan="7" class="text-center py-4 text-danger">
                      <i class="bi bi-exclamation-triangle"></i> ${data.error || 'Error al cargar los registros'}
                    </td>
                  </tr>
                `;
            }
        } catch (error) {
            console.error('Error en cargarUltimosRegistros:', error);
            cuerpoTabla.innerHTML = `
              <tr>
                <td colspan="7" class="text-center py-4 text-danger">
                  <i class="bi bi-exclamation-triangle"></i> Error: ${error.message}
                </td>
              </tr>
            `;
        }
    }

    // Resto de las funciones permanecen igual...
    async function cargarTodosRegistros() {
        const cuerpoTabla = document.getElementById('cuerpoTabla');
        if (!cuerpoTabla) return;
        
        cuerpoTabla.innerHTML = `
          <tr>
            <td colspan="7" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2">Cargando todos los registros...</p>
            </td>
          </tr>
        `;

        try {
            const response = await fetch('../Controllers/RegistroController.php?action=obtenerTodosRegistros&limit=200');
            console.log('Response status históricos:', response.status);
            
            const responseText = await response.text();
            console.log('Response text históricos:', responseText);
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('Error parsing JSON:', e);
                throw new Error('Respuesta del servidor no es JSON válido');
            }

            console.log('Respuesta históricos recibida:', data);
            
            if (data.success) {
                registros = data.data;
                registrosFiltrados = [...registros];
                mostrandoUltimos = false;
                paginaActual = 1;
                mostrarRegistrosPaginados();
                actualizarContadorResultados();
                actualizarPaginacion();
                actualizarContadoresEstado(registros);
                actualizarTituloTabla("Todos los registros históricos");
            } else {
                cuerpoTabla.innerHTML = `
                  <tr>
                    <td colspan="7" class="text-center py-4 text-danger">
                      <i class="bi bi-exclamation-triangle"></i> ${data.error || 'Error al cargar los registros históricos'}
                    </td>
                  </tr>
                `;
            }
        } catch (error) {
            console.error('Error en cargarTodosRegistros:', error);
            cuerpoTabla.innerHTML = `
              <tr>
                <td colspan="7" class="text-center py-4 text-danger">
                  <i class="bi bi-exclamation-triangle"></i> Error: ${error.message}
                </td>
              </tr>
            `;
        }
    }

    function toggleFiltros() {
        const filtrosContainer = document.getElementById('filtrosContainer');
        const btn = document.getElementById('btnMostrarFiltros');
        
        if (filtrosContainer.style.display === 'none') {
            filtrosContainer.style.display = 'block';
            btn.innerHTML = '<i class="bi bi-funnel-fill"></i> Ocultar Filtros';
        } else {
            filtrosContainer.style.display = 'none';
            btn.innerHTML = '<i class="bi bi-funnel"></i> Filtros';
        }
    }

    async function aplicarFiltros() {
        const idEquipo = document.getElementById('filtroEquipo')?.value || '';
        const estado = document.getElementById('filtroEstado')?.value || '';
        const laboratorio = document.getElementById('filtroLaboratorio')?.value || '';
        const fechaInicio = document.getElementById('filtroFechaInicio')?.value || '';
        const fechaFin = document.getElementById('filtroFechaFin')?.value || '';

        const cuerpoTabla = document.getElementById('cuerpoTabla');
        if (!cuerpoTabla) return;
        
        cuerpoTabla.innerHTML = `
          <tr>
            <td colspan="7" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2">Aplicando filtros...</p>
            </td>
          </tr>
        `;

        try {
            const formData = new FormData();
            if (idEquipo) formData.append('idEquipo', idEquipo);
            if (estado) formData.append('estado', estado);
            if (laboratorio) formData.append('laboratorio', laboratorio);
            if (fechaInicio) formData.append('fechaInicio', fechaInicio);
            if (fechaFin) formData.append('fechaFin', fechaFin);

            const response = await fetch('../Controllers/RegistroController.php?action=consultaRegistro', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const data = await response.json();
            console.log('Filtros respuesta:', data);

            if (data.success) {
                registrosFiltrados = data.data;
                mostrandoUltimos = false;
                paginaActual = 1;
                mostrarRegistrosPaginados();
                actualizarContadorResultados();
                actualizarPaginacion();
                actualizarContadoresEstado(registrosFiltrados);
                actualizarTituloTabla("Registros históricos filtrados");
            } else {
                cuerpoTabla.innerHTML = `
                  <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle"></i> ${data.error || 'No se encontraron registros con los filtros aplicados'}
                    </td>
                  </tr>
                `;
                actualizarContadorResultados();
                actualizarPaginacion();
            }
        } catch (error) {
            console.error('Error aplicando filtros:', error);
            cuerpoTabla.innerHTML = `
              <tr>
                <td colspan="7" class="text-center py-4 text-danger">
                  <i class="bi bi-exclamation-triangle"></i> Error: ${error.message}
                </td>
              </tr>
            `;
        }
    }

    function limpiarFiltros() {
        document.getElementById('filtroEquipo').value = '';
        document.getElementById('filtroEstado').value = '';
        document.getElementById('filtroLaboratorio').value = '';
        document.getElementById('filtroFechaInicio').value = '';
        document.getElementById('filtroFechaFin').value = '';
        
        if (mostrandoUltimos) {
            cargarUltimosRegistros();
        } else {
            cargarTodosRegistros();
        }
    }

    function cambiarVista() {
        const btnCambiarVista = document.getElementById('btnCambiarVista');
        
        if (mostrandoUltimos) {
            cargarTodosRegistros();
            btnCambiarVista.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Ver Últimos por Equipo';
            btnCambiarVista.className = 'btn btn-sm btn-outline-warning';
        } else {
            cargarUltimosRegistros();
            btnCambiarVista.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Ver Históricos';
            btnCambiarVista.className = 'btn btn-sm btn-outline-success';
        }
    }

    function cambiarFilasPorPagina() {
        registrosPorPagina = parseInt(this.value);
        paginaActual = 1;
        mostrarRegistrosPaginados();
        actualizarContadorResultados();
        actualizarPaginacion();
    }

    function mostrarRegistrosPaginados() {
        const cuerpoTabla = document.getElementById('cuerpoTabla');
        if (!cuerpoTabla) return;
        
        if (registrosFiltrados.length === 0) {
            const mensaje = mostrandoUltimos ? 
                'No se encontraron equipos con registros' : 
                'No se encontraron registros que coincidan con los filtros';
                
            cuerpoTabla.innerHTML = `
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                  <i class="bi bi-inbox"></i> ${mensaje}
                </td>
              </tr>
            `;
            return;
        }

        const inicio = (paginaActual - 1) * registrosPorPagina;
        const fin = Math.min(inicio + registrosPorPagina, registrosFiltrados.length);
        const registrosPagina = registrosFiltrados.slice(inicio, fin);
        
        cuerpoTabla.innerHTML = '';
        
        registrosPagina.forEach(registro => {
            let badgeClass = 'bg-secondary';
            let estadoTexto = registro.estado_texto;
            
            if (estadoTexto === 'Activo' || estadoTexto === 'Funcionando') badgeClass = 'bg-success';
            if (estadoTexto === 'Inactivo' || estadoTexto === 'No Funciona') badgeClass = 'bg-danger';
            if (estadoTexto === 'En Reparación') badgeClass = 'bg-warning';

            const row = document.createElement('tr');
            row.innerHTML = `
              <td>${registro.equipo}</td>
              <td>${registro.fecha}</td>
              <td><span class="badge ${badgeClass}">${estadoTexto}</span></td>
              <td>${registro.diskFree || 'N/A'} GB</td>
              <td>${registro.usuario}</td>
              <td>${registro.descripcion || 'Sin descripción'}</td>
              <td>
                <button class="btn btn-sm btn-outline-primary" title="Ver detalles">
                    <i class="bi bi-eye"></i>
                </button>
              </td>
            `;
            cuerpoTabla.appendChild(row);
        });
    }

    function actualizarContadorResultados() {
        const contador = document.getElementById('contadorResultados');
        if (!contador) return;
        
        const total = registrosFiltrados.length;
        const inicio = total > 0 ? (paginaActual - 1) * registrosPorPagina + 1 : 0;
        const fin = Math.min(paginaActual * registrosPorPagina, total);
        
        const tipoRegistros = mostrandoUltimos ? 'últimos registros' : 'registros';
        contador.textContent = `Mostrando ${inicio} a ${fin} de ${total} ${tipoRegistros}`;
    }

    function actualizarPaginacion() {
        const totalRegistros = registrosFiltrados.length;
        const totalPaginas = Math.ceil(totalRegistros / registrosPorPagina);
        const paginacion = document.getElementById('paginacion');
        const infoPaginacion = document.getElementById('infoPaginacion');
        
        if (!paginacion || !infoPaginacion) return;
        
        infoPaginacion.textContent = `Página ${paginaActual} de ${totalPaginas}`;
        
        if (totalPaginas <= 1) {
            paginacion.innerHTML = '';
            return;
        }
        
        let html = `
            <li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-pagina="${paginaActual - 1}">Anterior</a>
            </li>
        `;
        
        let inicioPag = Math.max(1, paginaActual - 2);
        let finPag = Math.min(totalPaginas, inicioPag + 4);
        
        if (finPag - inicioPag < 4) {
            inicioPag = Math.max(1, finPag - 4);
        }
        
        for (let i = inicioPag; i <= finPag; i++) {
            html += `
                <li class="page-item ${i === paginaActual ? 'active' : ''}">
                    <a class="page-link" href="#" data-pagina="${i}">${i}</a>
                </li>
            `;
        }
        
        html += `
            <li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
                <a class="page-link" href="#" data-pagina="${paginaActual + 1}">Siguiente</a>
            </li>
        `;
        
        paginacion.innerHTML = html;
        
        paginacion.querySelectorAll('.page-link').forEach(enlace => {
            enlace.addEventListener('click', function(e) {
                e.preventDefault();
                const pagina = parseInt(this.getAttribute('data-pagina'));
                if (!isNaN(pagina)) {
                    paginaActual = pagina;
                    mostrarRegistrosPaginados();
                    actualizarContadorResultados();
                    actualizarPaginacion();
                }
            });
        });
    }

    function actualizarContadoresEstado(registrosActuales) {
        const contadores = {
            'Activo': 0,
            'Inactivo': 0,
            'Funcionando': 0,
            'No Funciona': 0,
            'En Reparación': 0
        };

        registrosActuales.forEach(registro => {
            const estado = registro.estado_texto;
            if (contadores.hasOwnProperty(estado)) {
                contadores[estado]++;
            }
        });

        if (document.getElementById('contadorActivos')) {
            document.getElementById('contadorActivos').textContent = contadores['Activo'] + contadores['Funcionando'];
        }
        if (document.getElementById('contadorInactivos')) {
            document.getElementById('contadorInactivos').textContent = contadores['Inactivo'] + contadores['No Funciona'];
        }
        if (document.getElementById('contadorReparacion')) {
            document.getElementById('contadorReparacion').textContent = contadores['En Reparación'];
        }
        
        const espacioLibrePromedio = registrosActuales.reduce((sum, registro) => {
            return sum + (parseInt(registro.diskFree) || 0);
        }, 0) / (registrosActuales.length || 1);
        
        if (document.getElementById('contadorEspacioLibre')) {
            document.getElementById('contadorEspacioLibre').textContent = Math.round(espacioLibrePromedio) + ' GB';
        }
    }

    function actualizarTituloTabla(nuevoTitulo) {
        const titulo = document.querySelector('.card-title');
        if (titulo) {
            titulo.textContent = nuevoTitulo;
        }
    }
});