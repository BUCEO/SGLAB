<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/TipoUsuario.php';

abstract class Auth {
    
    // Login por username (para Administrador y Docente)
    public static function loginByUsername($username, $password) {
        try {
            $db = Database::getConnection('login');
            $sql = "SELECT id, ci, nombre, username, email, password, idTipoUsuario, activo, 
                           intentos_fallidos, bloqueado_hasta, secret_2fa
                    FROM Usuarios 
                    WHERE username = :username AND activo = 1";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuarioData) {
                // Verificar si el usuario está bloqueado
                if ($usuarioData['bloqueado_hasta'] && strtotime($usuarioData['bloqueado_hasta']) > time()) {
                    error_log("Usuario bloqueado: " . $username);
                    return null;
                }
                
                // Verificar password
                $passwordMatch = password_verify($password, $usuarioData['password']);
                
                if ($passwordMatch) {
                    // Resetear intentos fallidos
                    self::resetearIntentosFallidos($usuarioData['id']);
                    
                    // Obtener roles
                    $usuarioData['roles'] = TipoUsuario::obtenerRolesPorTipo($usuarioData['idTipoUsuario']);
                    
                    return new Usuario($usuarioData);
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error en Auth::loginByUsername: " . $e->getMessage());
            return null;
        }
    }
    
    // Login por CI (legacy - mantener para compatibilidad)
    public static function login($ci, $password) {
        try {
            $db = Database::getConnection('login');
            $sql = "SELECT id, ci, nombre, username, email, password, idTipoUsuario, activo
                    FROM Usuarios 
                    WHERE ci = :ci AND activo = 1";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':ci', $ci, PDO::PARAM_STR);
            $stmt->execute();
            $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuarioData) {
                $passwordMatch = password_verify($password, $usuarioData['password']) || ($password === $usuarioData['password']);
                
                if ($passwordMatch) {
                    $usuarioData['roles'] = TipoUsuario::obtenerRolesPorTipo($usuarioData['idTipoUsuario']);
                    return new Usuario($usuarioData);
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error en Auth::login: " . $e->getMessage());
            return null;
        }
    }
    
    // Obtener usuario por ID
    public static function getUsuarioById($userId) {
        try {
            $db = Database::getConnection('login');
            $sql = "SELECT id, ci, nombre, username, email, idTipoUsuario, activo
                    FROM Usuarios 
                    WHERE id = :id AND activo = 1";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuarioData) {
                $usuarioData['roles'] = TipoUsuario::obtenerRolesPorTipo($usuarioData['idTipoUsuario']);
                return new Usuario($usuarioData);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error en Auth::getUsuarioById: " . $e->getMessage());
            return null;
        }
    }
    
    // ============ FUNCIONES 2FA ============
    
    // Obtener secreto 2FA del usuario
    public static function getSecret2FA($userId) {
        try {
            $db = Database::getConnection('login');
            $sql = "SELECT secret_2fa FROM Usuarios WHERE id = :id AND activo = 1";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $secret = $result ? $result['secret_2fa'] : null;
            
            // Log para debugging
            error_log("getSecret2FA - Usuario ID: $userId - Secret encontrado: " . ($secret ? "SÍ" : "NO"));
            
            return $secret;
            
        } catch (Exception $e) {
            error_log("Error en Auth::getSecret2FA: " . $e->getMessage());
            return null;
        }
    }
    
    // Guardar secreto 2FA temporal (en sesión)
    public static function guardarSecret2FATemp($userId, $secret) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['temp_2fa_secret_' . $userId] = $secret;
        error_log("Secret temporal guardado para usuario ID: $userId");
    }
    
    // Obtener secreto 2FA temporal
    public static function getSecret2FATemp($userId) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $secret = isset($_SESSION['temp_2fa_secret_' . $userId]) ? $_SESSION['temp_2fa_secret_' . $userId] : null;
        error_log("getSecret2FATemp - Usuario ID: $userId - Secret temporal: " . ($secret ? "SÍ" : "NO"));
        return $secret;
    }
    
    // Confirmar y guardar secreto 2FA permanentemente
    public static function confirmarSecret2FA($userId, $secret) {
        try {
            $db = Database::getConnection('login');
            
            // CRÍTICO: Usar UPDATE para asegurar que se guarda
            $sql = "UPDATE Usuarios 
                    SET secret_2fa = :secret,
                        fechaModificacion = CURRENT_TIMESTAMP
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':secret', $secret, PDO::PARAM_STR);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            
            $success = $stmt->execute();
            $rowsAffected = $stmt->rowCount();
            
            error_log("confirmarSecret2FA - Usuario ID: $userId - Success: " . ($success ? "SÍ" : "NO") . " - Rows: $rowsAffected");
            
            if ($success && $rowsAffected > 0) {
                // Verificar que se guardó correctamente
                $verificarSql = "SELECT secret_2fa FROM Usuarios WHERE id = :id";
                $verificarStmt = $db->prepare($verificarSql);
                $verificarStmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $verificarStmt->execute();
                $result = $verificarStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result && $result['secret_2fa'] === $secret) {
                    error_log("✅ Secret 2FA guardado y verificado correctamente en BD");
                    
                    // Limpiar sesión temporal
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    unset($_SESSION['temp_2fa_secret_' . $userId]);
                    
                    return true;
                } else {
                    error_log("❌ ERROR: Secret no se guardó correctamente");
                    return false;
                }
            }
            
            error_log("❌ ERROR: No se pudo guardar el secret (0 rows affected)");
            return false;
            
        } catch (Exception $e) {
            error_log("❌ ERROR en Auth::confirmarSecret2FA: " . $e->getMessage());
            return false;
        }
    }
    
    // ============ FUNCIONES DE SEGURIDAD ============
    
    // Registrar intento fallido
    public static function registrarIntentoFallido($username) {
        try {
            $db = Database::getConnection('login');
            
            // Incrementar intentos fallidos
            $sql = "UPDATE Usuarios 
                    SET intentos_fallidos = intentos_fallidos + 1
                    WHERE username = :username";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            // Verificar si debe bloquearse (5 intentos)
            $sql = "SELECT id, intentos_fallidos FROM Usuarios WHERE username = :username";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && $usuario['intentos_fallidos'] >= 5) {
                // Bloquear por 15 minutos
                $bloqueadoHasta = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $sql = "UPDATE Usuarios 
                        SET bloqueado_hasta = :bloqueado_hasta 
                        WHERE id = :id";
                
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':bloqueado_hasta', $bloqueadoHasta, PDO::PARAM_STR);
                $stmt->bindParam(':id', $usuario['id'], PDO::PARAM_INT);
                $stmt->execute();
                
                // Registrar en logs
                self::registrarLog($usuario['id'], $username, 'cuenta_bloqueada', 'Cuenta bloqueada por intentos fallidos');
            }
            
            // Registrar en logs
            self::registrarLog($usuario['id'] ?? null, $username, 'login_fallido', 'Intento de login fallido');
            
        } catch (Exception $e) {
            error_log("Error en Auth::registrarIntentoFallido: " . $e->getMessage());
        }
    }
    
    // Registrar intento fallido 2FA
    public static function registrarIntentoFallido2FA($userId) {
        try {
            self::registrarLog($userId, null, '2fa_fallido', 'Código 2FA incorrecto');
        } catch (Exception $e) {
            error_log("Error en Auth::registrarIntentoFallido2FA: " . $e->getMessage());
        }
    }
    
    // Resetear intentos fallidos
    public static function resetearIntentosFallidos($userId) {
        try {
            $db = Database::getConnection('login');
            $sql = "UPDATE Usuarios 
                    SET intentos_fallidos = 0, bloqueado_hasta = NULL 
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error en Auth::resetearIntentosFallidos: " . $e->getMessage());
        }
    }
    
    // Verificar si está bloqueado
    public static function estaBloqueado($userId) {
        try {
            $db = Database::getConnection('login');
            $sql = "SELECT bloqueado_hasta FROM Usuarios WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['bloqueado_hasta']) {
                $bloqueadoHasta = strtotime($result['bloqueado_hasta']);
                $ahora = time();
                
                if ($bloqueadoHasta > $ahora) {
                    return true;
                } else {
                    // Desbloquear automáticamente
                    self::resetearIntentosFallidos($userId);
                    return false;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error en Auth::estaBloqueado: " . $e->getMessage());
            return false;
        }
    }
    
    // Registrar login exitoso
    public static function registrarLoginExitoso($userId, $detalles = 'Login exitoso') {
        try {
            self::registrarLog($userId, null, 'login_exitoso', $detalles);
        } catch (Exception $e) {
            error_log("Error en Auth::registrarLoginExitoso: " . $e->getMessage());
        }
    }
    
    // Guardar sesión en BD
    public static function guardarSesion($userId, $sessionId) {
        try {
            $db = Database::getConnection('login');
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            // Verificar si ya existe la sesión
            $sql = "SELECT id FROM sesiones WHERE id = :session_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                // Actualizar sesión existente
                $sql = "UPDATE sesiones 
                        SET usuario_id = :usuario_id, 
                            ip_address = :ip_address, 
                            user_agent = :user_agent,
                            ultima_actividad = CURRENT_TIMESTAMP
                        WHERE id = :session_id";
            } else {
                // Insertar nueva sesión
                $sql = "INSERT INTO sesiones (id, usuario_id, ip_address, user_agent) 
                        VALUES (:session_id, :usuario_id, :ip_address, :user_agent)";
            }
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
            $stmt->bindParam(':usuario_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
            $stmt->bindParam(':user_agent', $userAgent, PDO::PARAM_STR);
            $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error en Auth::guardarSesion: " . $e->getMessage());
        }
    }
    
    // Cerrar sesión
    public static function cerrarSesion($sessionId) {
        try {
            $db = Database::getConnection('login');
            $sql = "DELETE FROM sesiones WHERE id = :session_id";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
            $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error en Auth::cerrarSesion: " . $e->getMessage());
        }
    }
    
    // Registrar en logs de autenticación
    public static function registrarLog($userId, $username, $tipoEvento, $detalles = null) {
        try {
            $db = Database::getConnection('login');
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $sql = "INSERT INTO logs_autenticacion 
                    (usuario_id, username, tipo_evento, ip_address, user_agent, detalles) 
                    VALUES (:usuario_id, :username, :tipo_evento, :ip_address, :user_agent, :detalles)";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':usuario_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':tipo_evento', $tipoEvento, PDO::PARAM_STR);
            $stmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
            $stmt->bindParam(':user_agent', $userAgent, PDO::PARAM_STR);
            $stmt->bindParam(':detalles', $detalles, PDO::PARAM_STR);
            $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error en Auth::registrarLog: " . $e->getMessage());
        }
    }
}
?>