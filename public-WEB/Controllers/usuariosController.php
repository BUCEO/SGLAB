<?php
session_start();
require_once '../Models/Usuario.php';
require_once '../Models/Database.php';
require_once '../Models/TipoUsuario.php';

// Para exportar CSV no necesitamos JSON header
if (!isset($_GET['action']) || $_GET['action'] !== 'exportarCSV') {
    header('Content-Type: application/json');
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'obtenerUsuarios':
            $soloActivos = isset($_GET['soloActivos']) && $_GET['soloActivos'] === 'true';
            $filtroTipo = isset($_GET['filtroTipo']) && $_GET['filtroTipo'] !== '' 
                ? (int)$_GET['filtroTipo'] 
                : null;
            
            $usuarios = Usuario::obtenerTodos($soloActivos, $filtroTipo);
            echo json_encode(['success' => true, 'data' => $usuarios]);
            break;
            
        case 'obtenerUsuario':
            if (!isset($_GET['id'])) {
                throw new Exception('ID de usuario no proporcionado');
            }
            
            $usuario = Usuario::obtenerPorId((int)$_GET['id']);
            
            if (!$usuario) {
                throw new Exception('Usuario no encontrado');
            }
            
            echo json_encode(['success' => true, 'data' => $usuario->toArray()]);
            break;
            
        case 'crearUsuario':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['ci']) || empty($data['nombre']) || empty($data['email']) || 
                empty($data['password']) || empty($data['idTipoUsuario'])) {
                throw new Exception('Todos los campos marcados son requeridos');
            }

            $resultado = Usuario::crear($data);
            
            if (!$resultado) {
                throw new Exception('No se pudo crear el usuario. La CI o el email ya podrían existir');
            }
            
            echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente']);
            break;
            
        case 'actualizarUsuario':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            if (!isset($_GET['id'])) {
                throw new Exception('ID de usuario no proporcionado');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $resultado = Usuario::actualizar((int)$_GET['id'], $data);
            
            if (!$resultado) {
                throw new Exception('No se pudo actualizar el usuario');
            }
            
            echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
            break;
            
        case 'eliminarUsuario':
            if (!isset($_GET['id'])) {
                throw new Exception('ID de usuario no proporcionado');
            }
            
            $resultado = Usuario::eliminar((int)$_GET['id']);
            
            if (!$resultado) {
                throw new Exception('No se pudo eliminar el usuario');
            }
            
            echo json_encode(['success' => true, 'message' => 'Usuario dado de baja exitosamente']);
            break;
            
        case 'obtenerRoles':
            $roles = TipoUsuario::obtenerTodos();
            echo json_encode(['success' => true, 'data' => $roles]);
            break;
            
        case 'importarCSV':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No se ha subido un archivo válido');
            }
            
            $resultado = Usuario::importarDesdeCSV($_FILES['archivo']);
            echo json_encode($resultado);
            break;
            
        case 'exportarCSV':
            exportarEstudiantesCSV();
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Exportar estudiantes a CSV
 */
function exportarEstudiantesCSV() {
    try {
        $db = Database::getConnection('admin');
        
        // Obtener solo estudiantes activos (idTipoUsuario = 3)
        $sql = "SELECT ci, nombre, email, username, fechaCreacion 
                FROM Usuarios 
                WHERE idTipoUsuario = 3 AND activo = 1 
                ORDER BY nombre";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Configurar headers para descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="estudiantes_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Crear output
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8 (para que Excel lo abra correctamente)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header del CSV
        fputcsv($output, ['CI', 'Nombre Completo', 'Email', 'Username', 'Fecha Creación']);
        
        // Datos
        foreach ($estudiantes as $estudiante) {
            fputcsv($output, [
                $estudiante['ci'],
                $estudiante['nombre'],
                $estudiante['email'],
                $estudiante['username'],
                $estudiante['fechaCreacion']
            ]);
        }
        
        fclose($output);
        exit;
        
    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error al exportar: ' . $e->getMessage()]);
        exit;
    }
}
?>