$(document).ready(function() {
    console.log('✅ Documento cargado - Login Admin 2FA');
    
    let tempUserId = null;
    let tempUsername = null;
    $('#githubLoginBtn').on('click', function() {
        window.location.href = '../controllers/githubAuthController.php';
    });
    
    // Año actual en el footer
    $('#currentYear').text(new Date().getFullYear());

    // Mostrar/ocultar contraseña
    $('.toggle-password').on('click', function() {
        const input = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // ==================== PASO 1: Login con usuario/contraseña ====================
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        console.log('📝 Formulario de login enviado');
        
        const form = $(this);
        
        if (form[0].checkValidity() === false) {
            e.stopPropagation();
            form.addClass('was-validated');
            
            $('.form-control:invalid').each(function() {
                $(this).addClass('shake');
                setTimeout(() => $(this).removeClass('shake'), 500);
            });
            
            return;
        }
        
        // Obtener datos
        const username = $('#usernameInput').val().trim();
        const password = $('#passwordInput').val().trim();

        console.log('🔐 Datos a enviar:', { username, password: '***' });

        // Mostrar estado de carga
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Validando...');
        submitBtn.prop('disabled', true);

        // Enviar petición
        $.ajax({
            url: '../Controllers/loginController.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'login',
                username: username,
                password: password
            },
            success: function(response) {
                console.log('========================================');
                console.log('📥 Respuesta del servidor:', response);
                console.log('Success:', response.success);
                console.log('Requires 2FA:', response.requires_2fa);
                console.log('Redirect:', response.redirect);
                console.log('========================================');

                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);

                if (response.success) {
                    if (response.requires_2fa) {
                        // Administrador necesita 2FA
                        tempUserId = response.user_id;
                        tempUsername = username;

                        console.log('Usuario requiere 2FA');
                        console.log('User ID:', tempUserId);
                        console.log('Needs setup:', response.needs_setup);

                        if (response.needs_setup) {
                            // Primera vez - configurar 2FA
                            console.log('📱 Primera vez - Mostrando QR');
                            showSetup2FA(response.qr_code, response.secret);
                        } else {
                            // Ya tiene 2FA configurado
                            console.log('✅ 2FA ya configurado - Pidiendo código');
                            showTwoFactorForm();
                        }
                    } else {
                        // Docente - login directo
                        console.log('✅ Login exitoso como DOCENTE - Redirigiendo a:', response.redirect);
                        showAlert('Login exitoso. Redirigiendo...', 'success');

                        // Verificar que tenemos la URL de redirección
                        if (response.redirect) {
                            console.log('🔄 Redirigiendo en 1 segundo a:', response.redirect);
                            setTimeout(() => {
                                console.log('🚀 Ejecutando redirección ahora...');
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            console.error('❌ ERROR: No hay URL de redirección en la respuesta');
                            showAlert('Error: No se especificó página de destino', 'danger');
                        }
                    }
                } else {
                    console.error('❌ Login fallido:', response.message);
                    showAlert(response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error en la petición:', error);
                console.log('Estado:', status);
                console.log('Respuesta completa:', xhr.responseText);
                
                showAlert('Error al conectar con el servidor', 'danger');
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    // ==================== PASO 2: Verificación 2FA ====================
    $('#twoFactorForm').on('submit', function(e) {
        e.preventDefault();
        console.log('🔑 Verificando código 2FA');
        
        const form = $(this);
        
        if (form[0].checkValidity() === false) {
            e.stopPropagation();
            form.addClass('was-validated');
            return;
        }

        const code = $('#twoFactorCode').val().trim();
        
        if (code.length !== 6 || !/^\d{6}$/.test(code)) {
            showAlert('Ingrese un código válido de 6 dígitos', 'warning');
            return;
        }

        console.log('Código a verificar:', code);
        console.log('Usuario ID:', tempUserId);

        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Verificando...');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: '../Controllers/loginController.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'verify_2fa',
                user_id: tempUserId,
                code: code
            },
            success: function(response) {
                console.log('📨 Respuesta verificación 2FA:', response);
                
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                
                if (response.success) {
                    console.log('✅ Verificación exitosa');
                    showAlert('Verificación exitosa', 'success');
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1000);
                } else {
                    console.error('❌ Código incorrecto');
                    showAlert(response.message, 'danger');
                    $('#twoFactorCode').val('').focus();
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error:', error);
                showAlert('Error al verificar el código', 'danger');
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    // ==================== PASO 3: Configurar 2FA por primera vez ====================
    $('#confirmSetupBtn').on('click', function() {
        console.log('⚙️ Confirmando configuración 2FA');
        
        const code = $('#setupTwoFactorCode').val().trim();

        if (code.length !== 6 || !/^\d{6}$/.test(code)) {
            showAlert('Ingrese un código válido de 6 dígitos', 'warning');
            return;
        }

        console.log('Código setup:', code);
        console.log('Usuario ID:', tempUserId);

        const btn = $(this);
        const originalText = btn.html();
        
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Configurando...');
        btn.prop('disabled', true);

        $.ajax({
            url: '../Controllers/loginController.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'setup_2fa',
                user_id: tempUserId,
                code: code
            },
            success: function(response) {
                console.log('📨 Respuesta setup 2FA:', response);
                
                btn.html(originalText);
                btn.prop('disabled', false);
                
                if (response.success) {
                    console.log('✅ Setup exitoso - 2FA configurado permanentemente');
                    showAlert('¡Autenticación en dos pasos activada correctamente!', 'success');
                    setTimeout(() => {
                        console.log('🔄 Redirigiendo a:', response.redirect);
                        window.location.href = response.redirect;
                    }, 1500);
                } else {
                    console.error('❌ Setup fallido:', response.message);
                    showAlert(response.message, 'danger');
                    $('#setupTwoFactorCode').val('').focus();
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error:', error);
                console.log('Respuesta:', xhr.responseText);
                showAlert('Error al configurar autenticación', 'danger');
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });

    // ==================== Botones de cancelar ====================
    $('#cancelTwoFactorBtn, #cancelSetupBtn').on('click', function() {
        console.log('↩️ Cancelando - volviendo al login');
        resetForms();
    });

    // ==================== Validación de entrada (solo números) ====================
    $('#twoFactorCode, #setupTwoFactorCode').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // ==================== Funciones auxiliares ====================
    
    function showTwoFactorForm() {
        console.log('📱 Mostrando formulario 2FA');
        $('#loginForm').hide();
        $('#twoFactorForm').show();
        $('#setupTwoFactorForm').hide();
        $('#twoFactorCode').val('').focus();
        hideAlert();
    }

    function showSetup2FA(qrCode, secret) {
    console.log('⚙️ Mostrando setup 2FA (primera vez)');
    console.log('QR URI:', qrCode.substring(0, 50) + '...');
    console.log('Secret:', secret);
    
    $('#loginForm').hide();
    $('#twoFactorForm').hide();
    $('#setupTwoFactorForm').show();
    
    // ✅ LIMPIAR COMPLETAMENTE el contenedor (eliminar todo)
    const $container = $('#qrCodeContainer');
    $container.empty(); // Vaciar todo
    $container.html(''); // Asegurar que está vacío
    
    // ✅ Crear contenedor para el QR
    const qrDiv = document.createElement('div');
    qrDiv.id = 'qrcode-display-' + Date.now(); // ID único con timestamp
    qrDiv.style.display = 'flex';
    qrDiv.style.justifyContent = 'center';
    qrDiv.style.alignItems = 'center';
    qrDiv.style.padding = '10px';
    qrDiv.style.margin = '0 auto';
    
    // Agregar al contenedor
    document.getElementById('qrCodeContainer').appendChild(qrDiv);
    
    // ✅ Generar QR con timeout para asegurar que el DOM está listo
    setTimeout(function() {
        console.log('🎨 Generando QR con QRCode.js');
        
        try {
            // Generar QR
            new QRCode(qrDiv, {
                text: qrCode,
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M // ← Cambiar a M (Medium) en vez de H
            });
            
            console.log('✅ QR generado correctamente');
            
        } catch (error) {
            console.error('❌ Error generando QR:', error);
            $('#qrCodeContainer').html(
                '<div class="alert alert-danger">Error al generar código QR. Usa el código manual abajo.</div>'
            );
        }
    }, 150); // Aumentar timeout a 150ms
    
    // Mostrar secret manual
    $('#manualCode').text(secret);
    $('#setupTwoFactorCode').val('').focus();
    hideAlert();
}

    function resetForms() {
        console.log('🔄 Reseteando formularios');
        
        $('#loginForm').show();
        $('#twoFactorForm').hide();
        $('#setupTwoFactorForm').hide();
        
        $('#loginForm')[0].reset();
        $('#twoFactorForm')[0].reset();
        $('#setupTwoFactorCode').val('');
        
        $('.was-validated').removeClass('was-validated');
        hideAlert();
        
        tempUserId = null;
        tempUsername = null;
    }

    function showAlert(message, type) {
        const alertDiv = $('#alertContainer .alert');
        alertDiv.removeClass('alert-success alert-danger alert-warning alert-info');
        alertDiv.addClass('alert-' + type);
        
        const icon = getAlertIcon(type);
        alertDiv.html('<i class="fas fa-' + icon + ' me-2"></i>' + message);
        
        $('#alertContainer').fadeIn();
        
        // Auto-ocultar después de 5 segundos (excepto errores)
        if (type === 'success' || type === 'info') {
            setTimeout(() => {
                hideAlert();
            }, 5000);
        }
    }

    function hideAlert() {
        $('#alertContainer').fadeOut();
    }

    function getAlertIcon(type) {
        const icons = {
            'success': 'check-circle',
            'danger': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
    $('#githubLoginBtn').on('click', function() {
    console.log('🔗 Iniciando login con GitHub');
    window.location.href = '../Controllers/githubAuthController.php';
    });
});