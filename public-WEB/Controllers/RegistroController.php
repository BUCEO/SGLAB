<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../Models/Registro.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/Equipo.php';
require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Models/Laboratorio.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
function formatearFechaEpoch($epochTime) {
    return date('Y-m-d H:i', $epochTime);
}
function traducirEstado($estado) {
    if (is_bool($estado)) {
        return $estado ? 'Activo' : 'Inactivo';
    }

    if (is_string($estado)) {
        if ($estado === 'true' || $estado === '1') return 'Activo';
        if ($estado === 'false' || $estado === '0') return 'Inactivo';
    }
    
    $traducciones = [
        'Funciona'      => 'Funcionando',
        'No funciona'   => 'No Funciona',
        'En reparacion' => 'En Reparación',
        'true'          => 'Activo',
        'false'         => 'Inactivo',
        1               => 'Activo',
        0               => 'Inactivo',
        '1'             => 'Activo',
        '0'             => 'Inactivo'
    ];
    return $traducciones[$estado] ?? $estado;
}

try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'obtenerUltimosPorEquipo':
            error_log("Ejecutando obtenerUltimosPorEquipo");
            
            try {
                $registros = Registro::obtenerUltimosRegistrosPorEquipo();
                error_log("Registros obtenidos: " . count($registros));
                
                if (empty($registros)) {
                    echo json_encode(['success' => true, 'data' => []]);
                    break;
                }

                $resultado = array_map(function($registro) {
                    return [
                        'id'           => $registro['idRegistro'],
                        'fecha'        => formatearFechaEpoch($registro['fecha']),
                        'equipo'       => $registro['equipo_hostname'] ?? $registro['serialNumber'],
                        'estado'       => (bool)$registro['estado'],
                        'descripcion'  => $registro['descripcion'] ?? '',
                        'diskFree'     => $registro['diskFree'] ?? 'N/A',
                        'usuario'      => $registro['usuario_nombre'] ?? 'Sistema',
                        'estado_texto' => traducirEstado($registro['estado']),
                        'IP'           => $registro['IP'] ?? 'N/A'
                    ];
                }, $registros);
                
                echo json_encode(['success' => true, 'data' => $resultado]);
            } catch (Exception $e) {
                error_log("Error en obtenerUltimosPorEquipo: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
        case 'crearReporte':
            if (!isset($_SESSION['usuario_id'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Usuario no autenticado.']);
                exit;
            }
            
            $idEquipo = $_POST['idEquipo'] ?? null;
            $estadoStr = $_POST['estado'] ?? 'true'; 
            $descripcion = $_POST['descripcion'] ?? '';
            $idUsuario = $_SESSION['usuario_id'];
            
            // Llama al modelo para crear el registro
            if (Registro::crearReporteEstudiante($idEquipo, $idUsuario, $estadoStr, $descripcion)) {
                echo json_encode(['success' => true, 'message' => 'Reporte enviado exitosamente.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al guardar el reporte en la base de datos.']);
            }
            break;

        case 'obtenerTodosRegistros':
            $limit = $_GET['limit'] ?? 50;
            $registros = Registro::obtenerTodos($limit);
            
            $resultado = array_map(function($registro) {
                return [
                    'id'           => $registro['idRegistro'],
                    'fecha'        => formatearFechaEpoch($registro['fecha']),
                    'equipo'       => $registro['equipo_hostname'] ?? $registro['serialNumber'],
                    'estado'       => (bool)$registro['estado'],
                    'descripcion'  => $registro['descripcion'],
                    'diskFree'     => $registro['diskFree'] ?? 'N/A',
                    'usuario'      => $registro['usuario_nombre'] ?? 'Sistema',
                    'estado_texto' => traducirEstado($registro['estado']),
                    'IP'           => $registro['IP'] ?? 'N/A'
                ];
            }, $registros);
            
            echo json_encode(['success' => true, 'data' => $resultado]);
            break;

        case 'consultaRegistro':
            $idEquipo = $_POST['idEquipo'] ?? null;
            $estado = $_POST['estado'] ?? null;
            $laboratorio = $_POST['laboratorio'] ?? null;
            $fechaInicio = $_POST['fechaInicio'] ?? null;
            $fechaFin = $_POST['fechaFin'] ?? null;

            $filtros = [];
             if (!empty($idEquipo)) $filtros['idEquipo'] = $idEquipo;
            if (!empty($estado)) $filtros['estado'] = $estado;
            if (!empty($laboratorio)) $filtros['laboratorio'] = $laboratorio;
            if (!empty($fechaInicio)) $filtros['fechaInicio'] = $fechaInicio;
            if (!empty($fechaFin)) $filtros['fechaFin'] = $fechaFin;

            $registros = Registro::filtrarRegistros($filtros);
            
            $resultado = array_map(function($registro) {
                return [
                    'fecha'        => formatearFechaEpoch($registro['fecha']),
                    'equipo'       => $registro['equipo_hostname'] ?? $registro['serialNumber'],
                    'estado'       => (bool)$registro['estado'],
                    'descripcion'  => $registro['descripcion'],
                    'diskFree'     => $registro['diskFree'] ?? 'N/A',
                    'usuario'      => $registro['usuario_nombre'] ?? 'Sistema',
                    'estado_texto' => traducirEstado($registro['estado']),
                    'IP'           => $registro['IP'] ?? 'N/A'
                ];
            }, $registros);
            
            echo json_encode(['success' => true, 'data' => $resultado]);
            break;

        case 'obtenerEquipos':
            $equipos = Equipo::obtenerEquipos(); 
            echo json_encode(['success' => true, 'data' => $equipos]);
            break;


        case 'obtenerLaboratorios':
            $laboratorios = Laboratorio::obtenerTodosComoArray();
            echo json_encode(['success' => true, 'data' => $laboratorios]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log('Error en RegistroController: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>