<?php
session_start();

require_once '../Models/Database.php';
require_once '../Models/Usuario.php';
require_once '../Models/Auth.php';
require_once '../Models/TipoUsuario.php';
require_once '../vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$clientID = $_ENV['GITHUB_CLIENT_ID'];
$clientSecret = $_ENV['GITHUB_CLIENT_SECRET'];
$redirectUri = $_ENV['GITHUB_REDIRECT_URI'];

// ==================== PASO 1: Redirigir a GitHub ====================
if (!isset($_GET['code'])) {
    $githubAuthUrl = "https://github.com/login/oauth/authorize?" . http_build_query([
        'client_id' => $clientID,
        'redirect_uri' => $redirectUri,
        'scope' => 'user:email',
        'state' => bin2hex(random_bytes(16))
    ]);
    
    header('Location: ' . $githubAuthUrl);
    exit;
}

// ==================== PASO 2: Callback de GitHub ====================
$code = $_GET['code'];

// Intercambiar código por token
$tokenUrl = 'https://github.com/login/oauth/access_token';
$postData = [
    'client_id' => $clientID,
    'client_secret' => $clientSecret,
    'code' => $code,
    'redirect_uri' => $redirectUri
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$tokenResponse = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($tokenResponse, true);

if (!isset($tokenData['access_token'])) {
    error_log('Error GitHub OAuth: ' . print_r($tokenData, true));
    $_SESSION['error_mensaje'] = 'Error al obtener autorización de GitHub. Por favor, intenta nuevamente.';
    header('Location: ../views/error.php');
    exit;
}

$accessToken = $tokenData['access_token'];

// Obtener datos del usuario de GitHub
$userUrl = 'https://api.github.com/user';
$ch = curl_init($userUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'User-Agent: Sistema-Gestion-Laboratorios'
]);
$userResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    error_log('Error al obtener usuario de GitHub: ' . $userResponse);
    $_SESSION['error_mensaje'] = 'Error al obtener información de GitHub.';
    header('Location: ../views/error.php');
    exit;
}

$githubUser = json_decode($userResponse, true);

if (!isset($githubUser['id'])) {
    $_SESSION['error_mensaje'] = 'Error al obtener datos del usuario de GitHub.';
    header('Location: ../views/error.php');
    exit;
}

$githubId = $githubUser['id'];
$githubUsername = $githubUser['login'];
$githubEmail = $githubUser['email'] ?? null;

// Si no tiene email público, obtenerlo de la API de emails
if (!$githubEmail) {
    $emailUrl = 'https://api.github.com/user/emails';
    $ch = curl_init($emailUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'User-Agent: Sistema-Gestion-Laboratorios'
    ]);
    $emailResponse = curl_exec($ch);
    curl_close($ch);
    
    $emails = json_decode($emailResponse, true);
    if (is_array($emails)) {
        foreach ($emails as $email) {
            if (isset($email['primary']) && $email['primary'] && isset($email['verified']) && $email['verified']) {
                $githubEmail = $email['email'];
                break;
            }
        }
    }
}

// Validar que se obtuvo un email
if (!$githubEmail) {
    error_log("❌ No se pudo obtener email de GitHub para usuario: $githubUsername");
    $_SESSION['error_mensaje'] = 'No se pudo obtener tu email de GitHub. Por favor, asegúrate de tener un email público o verificado en tu cuenta de GitHub.';
    header('Location: ../views/error.php');
    exit;
}

error_log("GitHub OAuth - Usuario: $githubUsername, ID: $githubId, Email: $githubEmail");

// ==================== PASO 3: Buscar estudiante por email ====================
try {
    $db = Database::getConnection('login');
    
    // Primero buscar si ya está vinculado por github_id
    $sql = "SELECT * FROM Usuarios WHERE github_id = :github_id AND activo = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':github_id' => (string)$githubId]);
    $usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuarioExistente) {
        // ✅ Usuario ya vinculado - Login automático
        error_log("✅ Usuario ya vinculado - Login automático - ID: " . $usuarioExistente['id']);
        
        $usuarioExistente['roles'] = TipoUsuario::obtenerRolesPorTipo($usuarioExistente['idTipoUsuario']);
        $usuario = new Usuario($usuarioExistente);
        
        crearSesionEstudiante($usuario);
        Auth::registrarLog($usuario->getId(), $usuario->getUsername(), 'oauth_exitoso', 'Login con GitHub exitoso');
        
        header('Location: ../views/menu_estudiante.php');
        exit;
    }
    
    // ==================== PASO 4: Buscar por email y vincular ====================
    error_log("Buscando estudiante con email: $githubEmail");
    
    // Buscar estudiante por email
    $sql = "SELECT * FROM Usuarios WHERE email = :email AND idTipoUsuario = 3 AND activo = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':email' => $githubEmail]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$estudiante) {
        error_log("❌ No se encontró estudiante con email: $githubEmail");
        $_SESSION['error_mensaje'] = 'No se encontró un estudiante registrado con el email ' . htmlspecialchars($githubEmail) . '. Por favor, contacta al administrador para que te registre con este correo.';
        header('Location: ../views/error.php');
        exit;
    }
    
    // Verificar que no tenga ya GitHub vinculado
    if (!empty($estudiante['github_id'])) {
        error_log("❌ Estudiante con email $githubEmail ya tiene GitHub vinculado");
        $_SESSION['error_mensaje'] = 'Este estudiante ya tiene una cuenta de GitHub vinculada. Si crees que esto es un error, contacta al administrador.';
        header('Location: ../views/error.php');
        exit;
    }
    
    // ✅ Vincular GitHub con el estudiante
    error_log("✅ Vinculando GitHub con estudiante ID: " . $estudiante['id']);
    
    $sqlUpdate = "UPDATE Usuarios SET 
                  github_id = :github_id,
                  github_username = :github_username,
                  github_email = :github_email,
                  fechaModificacion = NOW()
                  WHERE id = :id";
    
    $stmtUpdate = $db->prepare($sqlUpdate);
    $resultado = $stmtUpdate->execute([
        ':github_id' => (string)$githubId,
        ':github_username' => $githubUsername,
        ':github_email' => $githubEmail,
        ':id' => $estudiante['id']
    ]);
    
    if ($resultado) {
        error_log("✅ Cuenta GitHub vinculada exitosamente - Usuario ID: " . $estudiante['id']);
        
        // Recargar usuario actualizado
        $estudiante['github_id'] = (string)$githubId;
        $estudiante['github_username'] = $githubUsername;
        $estudiante['github_email'] = $githubEmail;
        $estudiante['roles'] = TipoUsuario::obtenerRolesPorTipo($estudiante['idTipoUsuario']);
        
        $usuario = new Usuario($estudiante);
        
        crearSesionEstudiante($usuario);
        Auth::registrarLog($usuario->getId(), $usuario->getUsername(), 'oauth_exitoso', 'Cuenta de GitHub vinculada y login exitoso');
        
        header('Location: ../views/menu_estudiante.php');
        exit;
    } else {
        error_log("❌ Error al actualizar la base de datos para vincular GitHub");
        $_SESSION['error_mensaje'] = 'Error al vincular la cuenta de GitHub. Por favor, intenta nuevamente.';
        header('Location: ../views/error.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log('❌ ERROR CRÍTICO en githubAuthController: ' . $e->getMessage());
    $_SESSION['error_mensaje'] = 'Error al procesar el login con GitHub. Por favor, intenta nuevamente.';
    header('Location: ../views/error.php');
    exit;
}

// ==================== FUNCIONES AUXILIARES ====================

function crearSesionEstudiante($usuario) {
    $_SESSION['usuario'] = [
        'id' => $usuario->getId(),
        'ci' => $usuario->getCi(),
        'nombre' => $usuario->getNombre(),
        'email' => $usuario->getEmail(),
        'username' => $usuario->getUsername(),
        'idTipoUsuario' => $usuario->getIdTipoUsuario(),
        'roles' => $usuario->getRoles()
    ];
    $_SESSION['usuario_id'] = $usuario->getId();
    
    Auth::guardarSesion($usuario->getId(), session_id());
    
    error_log("✅ Sesión creada para estudiante: " . $usuario->getNombre() . " (ID: " . $usuario->getId() . ")");
}
?>