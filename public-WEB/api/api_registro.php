<?php
header('Content-Type: application/json');
require_once '../models/Database.php';
require_once '../models/Registro.php';
require_once '../models/Equipo.php';

// --- CONFIGURACIÓN ---
define('API_SECRET_KEY', 'TU_CLAVE_SECRETA_AQUI_12345');
// Ruta a la carpeta donde el script de Linux moverá los reportes para ser procesados.
// ¡ASEGÚRATE DE QUE ESTA CARPETA TENGA PERMISOS DE LECTURA/ESCRITURA PARA EL SERVIDOR WEB (www-data)!
define('REPORTS_DIR', __DIR__ . '/../reportes_para_procesar/');

function enviarRespuesta($statusCode, $mensaje, $datos = null) {
    http_response_code($statusCode);
    $respuesta = ['mensaje' => $mensaje];
    if ($datos) {
        $respuesta['datos'] = $datos;
    }
    echo json_encode($respuesta);
    exit;
}

// --- Función para parsear el reporte ---
function parseReportValue($pattern, $content) {
    if (preg_match($pattern, $content, $matches)) {
        return trim($matches[1]);
    }
    return null;
}

// 1. Verificaciones de Seguridad y Método (sin cambios)
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== API_SECRET_KEY) { enviarRespuesta(401, 'Acceso no autorizado.'); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { enviarRespuesta(405, 'Método no permitido.'); }

// 2. Obtener el nombre del archivo del JSON
$data = json_decode(file_get_contents("php://input"), true);
if (empty($data['filename'])) {
    enviarRespuesta(400, 'Petición incorrecta. Falta el campo "filename".');
}
$filename = basename($data['filename']);
$reportPath = REPORTS_DIR . $filename;

// 3. Validar y Leer el Archivo de Reporte
if (!file_exists($reportPath)) {
    enviarRespuesta(404, 'El archivo de reporte no se encuentra.', ['archivo' => $filename]);
}
$reportContent = file_get_contents($reportPath);

// 4. Parsear el Nombre del Archivo para los datos principales
$parts = explode('-', pathinfo($filename, PATHINFO_FILENAME), 3);
if (count($parts) !== 3) {
    enviarRespuesta(400, 'Formato de nombre de archivo inválido.', ['recibido' => $filename]);
}
list($fecha_str, $serialNumber, $estado_str) = $parts;

// 5. Extraer datos del CONTENIDO del reporte
$ip = parseReportValue('/Direccion IP: (.*)/', $reportContent);
$diskFreeStr = parseReportValue('/Disco Libre \(C:\): (.*?) GB/', $reportContent);
$diskFree = $diskFreeStr ? (int)floatval($diskFreeStr) : null;

// Crear una descripción detallada con el resto de la información
$descripcion = "Reporte automático. " .
    "PC: " . parseReportValue('/Nombre del PC: (.*)/', $reportContent) . " | " .
    "Usuario: " . parseReportValue('/Usuario: (.*)/', $reportContent) . " | " .
    "OS: " . parseReportValue('/SO: (.*)/', $reportContent) . " | " .
    "CPU: " . parseReportValue('/Procesador: (.*)/', $reportContent) . " | " .
    "RAM Libre: " . parseReportValue('/RAM Libre: (.*)/', $reportContent) . " | " .
    "Uso RAM: " . parseReportValue('/Uso de RAM: (.*)/', $reportContent) . " | " .
    "Uso Disco: " . parseReportValue('/Uso de Disco: (.*)/', $reportContent);

// 6. Buscar el Equipo por Número de Serie
$equipo = Equipo::obtenerPorSerial($serialNumber);
if (!$equipo) {
    // Si no se encuentra, borra el reporte para evitar bucles de error.
    unlink($reportPath); 
    enviarRespuesta(404, 'Equipo no encontrado en la BD. Reporte descartado.', ['SerialNumber' => $serialNumber]);
}
$idEquipo = $equipo['idEquipo'];

// 7. Preparar y Guardar el Registro
try {
    $fecha_epoch = strtotime($fecha_str);
    $estado_num = (strtolower($estado_str) === 'true') ? 1 : 0;

    $registro = new Registro([
        'idEquipo'    => $idEquipo,
        'idUsuario'   => 1, // Usuario del sistema/automático
        'fecha'       => $fecha_epoch,
        'estado'      => $estado_num,
        'IP'          => $ip,
        'diskFree'    => $diskFree,
        'descripcion' => substr($descripcion, 0, 255) // Limitar a 255 caracteres
    ]);

    if ($registro->guardar()) {
        // Éxito: Eliminar el reporte para no volver a procesarlo.
        unlink($reportPath); 
        enviarRespuesta(201, 'Registro creado exitosamente y reporte procesado.');
    } else {
        enviarRespuesta(500, 'Error al guardar el registro en la base de datos.');
    }

} catch (Exception $e) {
    error_log("Error en api_registro.php: " . $e->getMessage());
    enviarRespuesta(500, 'Ocurrió un error interno en el servidor.');
}
?>