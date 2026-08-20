<?php
// api/equipos.php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../config/jwt.php';

// Verificar autenticación JWT
$jwt = getBearerToken();
if (!$jwt || !verifyJWT($jwt)) {
    http_response_code(401);
    echo json_encode(["mensaje" => "Acceso no autorizado"]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Obtener datos del POST
$data = json_decode(file_get_contents("php://input"));

// Validar datos obligatorios
if (
    empty($data->num_serie) ||
    empty($data->nombre) ||
    empty($data->laboratorio)
) {
    http_response_code(400);
    echo json_encode(["mensaje" => "Datos incompletos"]);
    exit;
}

// Establecer valores por defecto
$estado = $data->estado ?? 'Operativo';
$descripcion = $data->descripcion ?? null;

try {
    // Insertar equipo
    $query = "INSERT INTO equipos
              SET num_serie = :num_serie, nombre = :nombre, estado = :estado,
                  descripcion = :descripcion, laboratorio = :laboratorio";

    $stmt = $db->prepare($query);

    $stmt->bindParam(":num_serie", $data->num_serie);
    $stmt->bindParam(":nombre", $data->nombre);
    $stmt->bindParam(":estado", $estado);
    $stmt->bindParam(":descripcion", $descripcion);
    $stmt->bindParam(":laboratorio", $data->laboratorio);

    if ($stmt->execute()) {
        // Obtener ID del equipo insertado
        $equipo_id = $db->lastInsertId();

        http_response_code(201);
        echo json_encode([
            "mensaje" => "Equipo registrado",
            "id" => $equipo_id,
            "data" => [
                "num_serie" => $data->num_serie,
                "nombre" => $data->nombre,
                "estado" => $estado,
                "laboratorio" => $data->laboratorio
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["mensaje" => "Error al registrar equipo"]);
    }
} catch (PDOException $e) {
    // Error de duplicado en número de serie
    if ($e->errorInfo[1] == 1062) {
        http_response_code(409);
        echo json_encode(["mensaje" => "El número de serie ya existe"]);
    } else {
        http_response_code(500);
        echo json_encode(["mensaje" => $e->getMessage()]);
    }
}