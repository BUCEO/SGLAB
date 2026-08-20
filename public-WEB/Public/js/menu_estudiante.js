document.addEventListener('DOMContentLoaded', function() {

    cargarEquipos();

    async function cargarEquipos() {
        try {
            const response = await fetch('/public-WEB/Controllers/RegistroController.php?action=obtenerEquipos');
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById('equipoSelect');
                select.innerHTML = '<option value="" selected disabled>Seleccione un equipo</option>';
                
                data.data.forEach(equipo => {
                    const option = document.createElement('option');
                    option.value = equipo.idEquipo;
                    option.textContent = equipo.hostname;
                    select.appendChild(option);
                });
            } else {
                console.error('Error al cargar equipos:', data.error);
            }
        } catch (error) {
            console.error('Error de conexión al cargar equipos:', error);
        }
    }

    // Contador de caracteres para el textarea
     const textarea = document.getElementById('comentarioTextArea');
    const charCount = document.getElementById('charCount');
    
    textarea.addEventListener('input', function() {
        const currentLength = this.value.length;
        charCount.textContent = currentLength;
        
        if (currentLength > 500) {
            this.value = this.value.substring(0, 500);
            charCount.textContent = 500;
        }
    });


     const estadoForm = document.getElementById('estadoForm');
    
    estadoForm.addEventListener('submit', async function(e) {
        e.preventDefault(); 

        const idEquipo = document.getElementById('equipoSelect').value;
        const funcionaCheckbox = document.getElementById('noFuncionaCheck');
        const descripcion = textarea.value;
        
        if (!idEquipo) {
            alert('Por favor, seleccione un equipo.');
            return;
        }
        
        const estado = funcionaCheckbox.checked ? 1 : 0;
        
        if (estado === 0 && descripcion.trim().length < 10) {
            alert('Si el equipo no funciona, por favor describa el problema con más detalle (mínimo 10 caracteres).');
            return;
        }

        const formData = new FormData();
        formData.append('idEquipo', idEquipo);
        formData.append('estado', estado);
        formData.append('descripcion', descripcion);

        try {
            
            const response = await fetch('/public-WEB/Controllers/RegistroController.php?action=crearReporte', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                estadoForm.reset();
                charCount.textContent = '0';
            } else {
                alert('Error: ' + data.error);
            }
        } catch (error) {
            console.error('Error de red o del servidor:', error);
            alert('Ocurrió un error al enviar el reporte. Inténtelo de nuevo.');
        }
    });

    // Efecto hover para el icono del equipo
    const equipoIcon = document.querySelector('.equipo-icon');
    
    equipoIcon.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1) rotate(5deg)';
    });
    
    equipoIcon.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1) rotate(0)';
    });
});