<?php
require_once 'Database.php';

class Registro {
    private $idRegistro;
    private $idEquipo;
    private $idUsuario;
    private $fecha;
    private $estado;
    private $IP;
    private $diskFree;
    private $descripcion;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->idRegistro = $data['idRegistro'] ?? null;
            $this->idEquipo = $data['idEquipo'] ?? null;
            $this->idUsuario = $data['idUsuario'] ?? null;
            $this->fecha = $data['fecha'] ?? time(); 
            $this->estado = $data['estado'] ?? null;
            $this->IP = $data['IP'] ?? null;
            $this->diskFree = $data['diskFree'] ?? null;
            $this->descripcion = $data['descripcion'] ?? '';
        }
    }

    // Getters y Setters
    public function getIdRegistro() { return $this->idRegistro; }
    public function getidEquipo() { return $this->idEquipo; }
    public function getIdUsuario() { return $this->idUsuario; }
    public function getFecha() { return $this->fecha; }
    public function getEstado() { return $this->estado; }
    public function getIP() { return $this->IP; }
    public function getDiskFree() { return $this->diskFree; }
    public function getDescripcion() { return $this->descripcion; }

    public function setidEquipo($idEquipo) { $this->idEquipo = $idEquipo; }
    public function setIdUsuario($idUsuario) { $this->idUsuario = $idUsuario; }
    public function setFecha($fecha) { $this->fecha = $fecha; }
    public function setEstado($estado) { $this->estado = $estado; }
    public function setIP($IP) { $this->IP = $IP; }
    public function setDiskFree($diskFree) { $this->diskFree = $diskFree; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }

    // Métodos CRUD
    public function guardar() {
        $db = Database::getConnection('admin');
        $sql = "INSERT INTO Registro (idEquipo, idUsuario, fecha, estado, IP, diskFree, descripcion) 
                VALUES (:idEquipo, :idUsuario, :fecha, :estado, :IP, :diskFree, :descripcion)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':idEquipo' => $this->idEquipo,
            ':idUsuario' => $this->idUsuario,
            ':fecha' => $this->fecha,
            ':estado' => $this->estado,
            ':IP' => $this->IP,
            ':diskFree' => $this->diskFree,
            ':descripcion' => $this->descripcion
        ]);
    }

    public static function obtenerPorId($idRegistro) {
        $db = Database::getConnection('admin');
        $sql = "SELECT * FROM Registro WHERE idRegistro = :idRegistro";
        $stmt = $db->prepare($sql);
        $stmt->execute([':idRegistro' => $idRegistro]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function obtenerTodos($limit = 100) {
        $db = Database::getConnection('admin');
        $sql = "SELECT r.*, u.nombre as usuario_nombre, e.hostname as equipo_hostname
                FROM Registro r
                LEFT JOIN Usuarios u ON r.idUsuario = u.id
                LEFT JOIN Equipos e ON r.idEquipo = e.idEquipo
                ORDER BY r.fecha DESC 
                LIMIT :limit";
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método corregido para obtener últimos registros por equipo
    public static function obtenerUltimosRegistrosPorEquipo() {
        $db = Database::getConnection('admin'); // Usar conexión de admin
        
        try {
            // Primera consulta: obtener el último registro de cada equipo
            $sql = "SELECT r.*, u.nombre as usuario_nombre, e.hostname as equipo_hostname
                    FROM Registro r
                    LEFT JOIN Usuarios u ON r.idUsuario = u.id
                    LEFT JOIN Equipos e ON r.idEquipo = e.idEquipo
                    INNER JOIN (
                        SELECT idEquipo, MAX(fecha) as max_fecha
                        FROM Registro
                        GROUP BY idEquipo
                    ) r_max ON r.idEquipo = r_max.idEquipo AND r.fecha = r_max.max_fecha
                    ORDER BY e.hostname ASC, r.fecha DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug - log del resultado
            error_log("obtenerUltimosRegistrosPorEquipo - Registros encontrados: " . count($resultados));
            if (count($resultados) > 0) {
                error_log("Primer registro: " . json_encode($resultados[0]));
            }
            
            return $resultados;
            
        } catch (PDOException $e) {
            error_log("Error en obtenerUltimosRegistrosPorEquipo: " . $e->getMessage());
            return [];
        }
    }

    public static function filtrarRegistros($filtros) {
        $db = Database::getConnection('admin');
        
        try {
            $sql = "SELECT r.*, u.nombre as usuario_nombre, e.hostname as equipo_hostname
                    FROM Registro r
                    LEFT JOIN Usuarios u ON r.idUsuario = u.id
                    LEFT JOIN Equipos e ON r.idEquipo = e.idEquipo
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['idEquipo'])) {
                $sql .= " AND r.idEquipo = :idEquipo";
                $params[':idEquipo'] = $filtros['idEquipo'];
            }
            
            if (isset($filtros['estado']) && $filtros['estado'] !== '') {
                // Convertir estado textual a numérico
                $estadoNum = self::convertirEstadoANumerico($filtros['estado']);
                $sql .= " AND r.estado = :estado";
                $params[':estado'] = $estadoNum;
            }
            
            if (!empty($filtros['laboratorio'])) {
                $sql .= " AND e.idLaboratorio = :laboratorio";
                $params[':laboratorio'] = (int)$filtros['laboratorio'];
                error_log("Filtrando por laboratorio: " . $filtros['laboratorio']);
            }
            
            if (!empty($filtros['fechaInicio']) && !empty($filtros['fechaFin'])) {
                $fechaInicio = is_numeric($filtros['fechaInicio']) ? 
                    $filtros['fechaInicio'] : strtotime($filtros['fechaInicio'] . ' 00:00:00');
                
                $fechaFin = is_numeric($filtros['fechaFin']) ? 
                    $filtros['fechaFin'] : strtotime($filtros['fechaFin'] . ' 23:59:59');
                
                $sql .= " AND r.fecha BETWEEN :fechaInicio AND :fechaFin";
                $params[':fechaInicio'] = $fechaInicio;
                $params[':fechaFin'] = $fechaFin;
            }
            
            $sql .= " ORDER BY r.fecha DESC";
            
            error_log("SQL filtrarRegistros: " . $sql);
            error_log("Parámetros: " . json_encode($params));
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Registros filtrados encontrados: " . count($resultado));
            
            return $resultado;
            
        } catch (PDOException $e) {
            error_log("Error en filtrarRegistros: " . $e->getMessage());
            return [];
        }
    }



    public static function crearReporteEstudiante($idEquipo, $idUsuario, $estado, $descripcion = null) {
        $db = Database::getConnection('estudiante'); 
        
        $sql = "INSERT INTO Registro (idEquipo, idUsuario, fecha, estado, IP, diskFree, descripcion)
                VALUES (:idEquipo, :idUsuario, :fecha, :estado, NULL, NULL, :descripcion)";
        
        try {
            $stmt = $db->prepare($sql);

            $fechaActual = time(); 
            $stmt->bindParam(':idEquipo', $idEquipo, PDO::PARAM_INT);
            $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fechaActual, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
            $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            
            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error al crear reporte de estudiante: " . $e->getMessage());
            return false;
        }
    }

    private static function convertirEstadoANumerico($estado) {
        $estados = [
            'true' => 1,
            'false' => 0,
            'Funciona' => 1,
            'No funciona' => 0,
            'En reparacion' => 2
        ];
        
        return $estados[$estado] ?? $estado;
    }


    public static function obtenerUsuarios() {
        $db = Database::getConnection('admin');
        try {
            $sql = "SELECT id, nombre FROM Usuarios ORDER BY nombre";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerUsuarios: " . $e->getMessage());
            return [];
        }
    }

    public static function obtenerEquipos() {
        $db = Database::getConnection('estudiante'); 
        try {
            // Seleccionamos solo los equipos activos
            $sql = "SELECT idEquipo, hostname FROM Equipos WHERE activo = TRUE ORDER BY hostname";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEquipos: " . $e->getMessage());
            return [];
        }
    }
    
    public static function formatearFecha($epochTime) {
        return date('Y-m-d H:i:s', $epochTime);
    }
    
    public static function convertirAFechaEpoch($fechaString) {
        return strtotime($fechaString);
    }
}
?>