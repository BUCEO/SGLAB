<?php
require_once 'Database.php';

class Equipo {
    private $idEquipo;
    private $serialNumber;
    private $hostname;
    private $CPU;
    private $RAM;
    private $diskType;
    private $diskTotal;
    private $idLaboratorio;
    private $activo;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->idEquipo = $data['idEquipo'] ?? null;
            $this->serialNumber = $data['serialNumber'] ?? null;
            $this->hostname = $data['hostname'] ?? null;
            $this->CPU = $data['CPU'] ?? null;
            $this->RAM = $data['RAM'] ?? null;
            $this->diskType = $data['diskType'] ?? null;
            $this->diskTotal = $data['diskTotal'] ?? null;
            $this->idLaboratorio = $data['idLaboratorio'] ?? null;
            $this->activo = isset($data['activo']) ? (bool)$data['activo'] : true;
        }
    }

    // Getters
    public function getIdEquipo() { return $this->idEquipo; }
    public function getSerialNumber() { return $this->serialNumber; }
    public function getHostname() { return $this->hostname; }
    public function getCPU() { return $this->CPU; }
    public function getRAM() { return $this->RAM; }
    public function getDiskType() { return $this->diskType; }
    public function getDiskTotal() { return $this->diskTotal; }
    public function getIdLaboratorio() { return $this->idLaboratorio; }
    public function getActivo() { return $this->activo; }

    // Setters
    public function setIdEquipo($idEquipo) { $this->idEquipo = $idEquipo; }
    public function setSerialNumber($serialNumber) { $this->serialNumber = $serialNumber; }
    public function setHostname($hostname) { $this->hostname = $hostname; }
    public function setCPU($CPU) { $this->CPU = $CPU; }
    public function setRAM($RAM) { $this->RAM = $RAM; }
    public function setDiskType($diskType) { $this->diskType = $diskType; }
    public function setDiskTotal($diskTotal) { $this->diskTotal = $diskTotal; }
    public function setIdLaboratorio($idLaboratorio) { $this->idLaboratorio = $idLaboratorio; }
    public function setActivo($activo) { $this->activo = (bool)$activo; }

    // Métodos CRUD
    public static function obtenerTodos($soloActivos = false) {
        $db = Database::getConnection('admin');
        try {
            $sql = "SELECT e.*, l.nombre as laboratorio_nombre 
                    FROM Equipos e
                    LEFT JOIN Laboratorios l ON e.idLaboratorio = l.idLaboratorio";
            
            if ($soloActivos) {
                $sql .= " WHERE e.activo = 1";
            }
            
            $sql .= " ORDER BY e.hostname";
            
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    public static function obtenerPorId($id) {
        $db = Database::getConnection('admin');
        try {
            $sql = "SELECT e.*, l.nombre as laboratorio_nombre 
                    FROM Equipos e
                    LEFT JOIN Laboratorios l ON e.idLaboratorio = l.idLaboratorio
                    WHERE e.idEquipo = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    public static function crearEquipo($serialNumber, $hostname, $CPU, $RAM, $diskType, $diskTotal, $idLaboratorio = null) {
        $db = Database::getConnection('admin');
        try {
            $sql = "INSERT INTO Equipos (serialNumber, hostname, CPU, RAM, diskType, diskTotal, idLaboratorio, activo) 
                    VALUES (:serialNumber, :hostname, :CPU, :RAM, :diskType, :diskTotal, :idLaboratorio, 1)";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':serialNumber' => $serialNumber,
                ':hostname' => $hostname,
                ':CPU' => $CPU,
                ':RAM' => $RAM,
                ':diskType' => $diskType,
                ':diskTotal' => $diskTotal,
                ':idLaboratorio' => $idLaboratorio
            ]);
        } catch (PDOException $e) {
            error_log("Error en crearEquipo: " . $e->getMessage());
            return false;
        }
    }

    public static function actualizarEquipo($idEquipo, $hostname, $CPU, $RAM, $diskType, $diskTotal, $idLaboratorio = null) {
        $db = Database::getConnection('admin');
        try {
            $sql = "UPDATE Equipos SET 
                    hostname = :hostname, 
                    CPU = :CPU, 
                    RAM = :RAM, 
                    diskType = :diskType, 
                    diskTotal = :diskTotal, 
                    idLaboratorio = :idLaboratorio,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE idEquipo = :idEquipo";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':idEquipo' => $idEquipo,
                ':hostname' => $hostname,
                ':CPU' => $CPU,
                ':RAM' => $RAM,
                ':diskType' => $diskType,
                ':diskTotal' => $diskTotal,
                ':idLaboratorio' => $idLaboratorio
            ]);
        } catch (PDOException $e) {
            error_log("Error en actualizarEquipo: " . $e->getMessage());
            return false;
        }
    }

    // Dar de baja lógica (no eliminar)
    public static function darDeBaja($idEquipo) {
        $db = Database::getConnection('admin');
        try {
            $sql = "UPDATE Equipos SET activo = 0, updated_at = CURRENT_TIMESTAMP WHERE idEquipo = :idEquipo";
            $stmt = $db->prepare($sql);
            return $stmt->execute([':idEquipo' => $idEquipo]);
        } catch (PDOException $e) {
            error_log("Error en darDeBaja: " . $e->getMessage());
            return false;
        }
    }

    // Reactivar equipo
    public static function reactivar($idEquipo) {
        $db = Database::getConnection('admin');
        try {
            $sql = "UPDATE Equipos SET activo = 1, updated_at = CURRENT_TIMESTAMP WHERE idEquipo = :idEquipo";
            $stmt = $db->prepare($sql);
            return $stmt->execute([':idEquipo' => $idEquipo]);
        } catch (PDOException $e) {
            error_log("Error en reactivar: " . $e->getMessage());
            return false;
        }
    }

    public static function existeEquipo($serialNumber) {
        $db = Database::getConnection('admin');
        try {
            $sql = "SELECT COUNT(*) FROM Equipos WHERE serialNumber = :serialNumber";
            $stmt = $db->prepare($sql);
            $stmt->execute([':serialNumber' => $serialNumber]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en existeEquipo: " . $e->getMessage());
            return false;
        }
    }

    public static function obtenerEquipos() {
        $db = Database::getConnection('admin');
        try {
            $sql = "SELECT idEquipo, serialNumber, hostname FROM Equipos WHERE activo = 1 ORDER BY hostname";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEquipos: " . $e->getMessage());
            return [];
        }
    }

    public static function controladorEquipo() {
        $db = Database::getConnection('admin');
        try {
            $sql = "SELECT e.idEquipo, e.serialNumber, e.hostname
                    FROM Equipos e
                    LEFT JOIN (
                        SELECT idEquipo, MAX(fecha) as ultima_fecha
                        FROM Registro
                        GROUP BY idEquipo
                    ) r ON e.idEquipo = r.idEquipo
                    WHERE e.activo = 1 
                    AND (r.ultima_fecha IS NULL OR r.ultima_fecha < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY)))";
            
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en controladorEquipo: " . $e->getMessage());
            return [];
        }
    }

    public static function altaMasiva($archivo) {
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Error al subir el archivo'];
        }

        $csvFile = fopen($archivo['tmp_name'], 'r');
        if (!$csvFile) {
            return ['success' => false, 'error' => 'No se pudo abrir el archivo'];
        }

        $header = fgetcsv($csvFile);
        $equiposCreados = 0;
        $errores = [];

        while (($data = fgetcsv($csvFile)) !== false) {
            if (count($data) < 6) continue;

            try {
                $resultado = self::crearEquipo(
                    $data[0], // serialNumber
                    $data[1], // hostname
                    $data[2], // CPU
                    (int)$data[3], // RAM
                    $data[4], // diskType
                    (int)$data[5], // diskTotal
                    isset($data[6]) ? (int)$data[6] : null // idLaboratorio
                );

                if ($resultado) {
                    $equiposCreados++;
                } else {
                    $errores[] = "Error al crear equipo: {$data[1]}";
                }
            } catch (Exception $e) {
                $errores[] = "Error en fila {$data[1]}: " . $e->getMessage();
            }
        }

        fclose($csvFile);

        return [
            'success' => true,
            'message' => "$equiposCreados equipos creados exitosamente",
            'errores' => $errores
        ];
    }
}
?>