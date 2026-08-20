<?php
require_once '../Models/Equipo.php';
require_once '../Models/Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'obtenerTodos':
            $soloActivos = isset($_GET['soloActivos']) && $_GET['soloActivos'] === 'true';
            $equipos = Equipo::obtenerTodos($soloActivos);
            echo json_encode(['success' => true, 'data' => $equipos]);
            break;
            
        case 'obtenerEquipo':
            if (!isset($_GET['idEquipo'])) {
                throw new Exception("ID de equipo no proporcionado");
            }
            $equipo = Equipo::obtenerPorId($_GET['idEquipo']);
            
            if ($equipo) {
                echo json_encode(['success' => true, 'data' => $equipo]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Equipo no encontrado']);
            }
            break;
            
        case 'crearEquipo':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido");
            }
            
            $input = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
                ? json_decode(file_get_contents('php://input'), true)
                : $_POST;
            
            $requiredFields = ['serialNumber', 'hostname', 'CPU', 'RAM', 'diskType', 'diskTotal'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field]) || $input[$field] === '') {
                    throw new Exception("Campo requerido: $field");
                }
            }
            
            if (Equipo::existeEquipo($input['serialNumber'])) {
                throw new Exception("El equipo con serial number {$input['serialNumber']} ya existe");
            }
            
            $resultado = Equipo::crearEquipo(
                $input['serialNumber'],
                $input['hostname'],
                $input['CPU'],
                (int)$input['RAM'],
                $input['diskType'],
                (int)$input['diskTotal'],
                !empty($input['idLaboratorio']) ? (int)$input['idLaboratorio'] : null
            );
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Equipo creado correctamente']);
            } else {
                throw new Exception("Error al crear el equipo");
            }
            break;
            
        case 'actualizarEquipo':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido");
            }
            
            $input = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
                ? json_decode(file_get_contents('php://input'), true)
                : $_POST;
            
            if (!isset($input['idEquipo'])) {
                throw new Exception("ID de equipo no proporcionado");
            }
            
            $resultado = Equipo::actualizarEquipo(
                (int)$input['idEquipo'],
                $input['hostname'],
                $input['CPU'],
                (int)$input['RAM'],
                $input['diskType'],
                (int)$input['diskTotal'],
                !empty($input['idLaboratorio']) ? (int)$input['idLaboratorio'] : null
            );
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Equipo actualizado correctamente']);
            } else {
                throw new Exception("Error al actualizar el equipo");
            }
            break;
            
        case 'darDeBaja':
            if (!isset($_GET['idEquipo'])) {
                throw new Exception("ID de equipo no proporcionado");
            }
            
            $resultado = Equipo::darDeBaja((int)$_GET['idEquipo']);
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Equipo dado de baja correctamente']);
            } else {
                throw new Exception("Error al dar de baja el equipo");
            }
            break;
            
        case 'reactivar':
            if (!isset($_GET['idEquipo'])) {
                throw new Exception("ID de equipo no proporcionado");
            }
            
            $resultado = Equipo::reactivar((int)$_GET['idEquipo']);
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Equipo reactivado correctamente']);
            } else {
                throw new Exception("Error al reactivar el equipo");
            }
            break;
            
        case 'controladorEquipo':
            $equiposDesconectados = Equipo::controladorEquipo();
            echo json_encode(['success' => true, 'data' => $equiposDesconectados]);
            break;
            
        case 'altaMasiva':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido");
            }
            
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("No se ha subido un archivo válido");
            }
            
            $resultado = Equipo::altaMasiva($_FILES['archivo']);
            echo json_encode($resultado);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>