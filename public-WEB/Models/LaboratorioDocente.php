<?php
require_once __DIR__ . '/Database.php';

class LaboratorioDocente {
    private $db;

    public function __construct($userType = 'docente') {
        $this->db = Database::getConnection($userType);
    }

    /**
     * Obtiene todos los laboratorios con sus equipos y último registro de cada equipo
     * @return array Array de laboratorios con equipos y registros
     */
    public function obtenerLaboratoriosConEquipos() {
        try {
            $query = "
                SELECT
                    l.idLaboratorio,
                    l.nombre as laboratorio_nombre,
                    l.comentario as laboratorio_comentario,
                    l.activo as laboratorio_activo,
                    l.updated_at as laboratorio_updated_at,
                    e.idEquipo,
                    e.serialNumber,
                    e.hostname,
                    e.CPU,
                    e.RAM,
                    e.diskType,
                    e.diskTotal,
                    e.activo as equipo_activo,
                    r.idRegistro,
                    r.fecha as registro_fecha,
                    r.estado as registro_estado,
                    r.IP as registro_ip,
                    r.diskFree as registro_diskFree,
                    r.descripcion as registro_descripcion,
                    u.nombre as usuario_nombre,
                    u.username as usuario_username
                FROM Laboratorios l
                LEFT JOIN Equipos e ON l.idLaboratorio = e.idLaboratorio AND e.activo = 1
                LEFT JOIN (
                    SELECT r1.*
                    FROM Registro r1
                    INNER JOIN (
                        SELECT idEquipo, MAX(fecha) as max_fecha
                        FROM Registro
                        GROUP BY idEquipo
                    ) r2 ON r1.idEquipo = r2.idEquipo AND r1.fecha = r2.max_fecha
                ) r ON e.idEquipo = r.idEquipo
                LEFT JOIN Usuarios u ON r.idUsuario = u.id
                WHERE l.activo = 1
                ORDER BY l.nombre, e.hostname
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar resultados por laboratorio
            $laboratorios = [];
            foreach ($results as $row) {
                $labId = $row['idLaboratorio'];

                // Inicializar laboratorio si no existe
                if (!isset($laboratorios[$labId])) {
                    $laboratorios[$labId] = [
                        'idLaboratorio' => $row['idLaboratorio'],
                        'nombre' => $row['laboratorio_nombre'],
                        'comentario' => $row['laboratorio_comentario'],
                        'activo' => $row['laboratorio_activo'],
                        'updated_at' => $row['laboratorio_updated_at'],
                        'equipos' => [],
                        'total_equipos' => 0,
                        'equipos_funcionando' => 0,
                        'equipos_con_problemas' => 0,
                        'equipos_en_reparacion' => 0,
                        'equipos_sin_registro' => 0
                    ];
                }

                // Agregar equipo si existe
                if ($row['idEquipo']) {
                    $estadoTexto = 'Sin registro';
                    $estadoClase = 'secondary';

                    if ($row['idRegistro']) {
                        switch ($row['registro_estado']) {
                            case 1:
                                $estadoTexto = 'Funciona';
                                $estadoClase = 'success';
                                $laboratorios[$labId]['equipos_funcionando']++;
                                break;
                            case 0:
                                $estadoTexto = 'No funciona';
                                $estadoClase = 'danger';
                                $laboratorios[$labId]['equipos_con_problemas']++;
                                break;
                            case 2:
                                $estadoTexto = 'En reparación';
                                $estadoClase = 'warning';
                                $laboratorios[$labId]['equipos_en_reparacion']++;
                                break;
                        }
                    } else {
                        $laboratorios[$labId]['equipos_sin_registro']++;
                    }

                    $laboratorios[$labId]['equipos'][] = [
                        'idEquipo' => $row['idEquipo'],
                        'serialNumber' => $row['serialNumber'],
                        'hostname' => $row['hostname'],
                        'CPU' => $row['CPU'],
                        'RAM' => $row['RAM'],
                        'diskType' => $row['diskType'],
                        'diskTotal' => $row['diskTotal'],
                        'activo' => $row['equipo_activo'],
                        'ultimo_registro' => $row['idRegistro'] ? [
                            'idRegistro' => $row['idRegistro'],
                            'fecha' => $row['registro_fecha'],
                            'fecha_formateada' => $this->formatearFecha($row['registro_fecha']),
                            'estado' => $row['registro_estado'],
                            'estado_texto' => $estadoTexto,
                            'estado_clase' => $estadoClase,
                            'IP' => $row['registro_ip'],
                            'diskFree' => $row['registro_diskFree'],
                            'descripcion' => $row['registro_descripcion'],
                            'usuario_nombre' => $row['usuario_nombre'],
                            'usuario_username' => $row['usuario_username']
                        ] : null
                    ];

                    $laboratorios[$labId]['total_equipos']++;
                }
            }

            return array_values($laboratorios);

        } catch (PDOException $e) {
            error_log("Error al obtener laboratorios con equipos: " . $e->getMessage());
            throw new Exception("Error al obtener laboratorios con equipos");
        }
    }

    /**
     * Obtiene un laboratorio específico con sus equipos y registros
     * @param int $idLaboratorio ID del laboratorio
     * @return array|null Datos del laboratorio con equipos
     */
    public function obtenerLaboratorioPorId($idLaboratorio) {
        $laboratorios = $this->obtenerLaboratoriosConEquipos();

        foreach ($laboratorios as $lab) {
            if ($lab['idLaboratorio'] == $idLaboratorio) {
                return $lab;
            }
        }

        return null;
    }

    /**
     * Actualiza el comentario de un laboratorio
     * @param int $idLaboratorio ID del laboratorio
     * @param string $comentario Nuevo comentario
     * @return bool True si se actualizó correctamente
     */
    public function actualizarComentario($idLaboratorio, $comentario) {
        try {
            $query = "
                UPDATE Laboratorios
                SET comentario = :comentario,
                    updated_at = CURRENT_TIMESTAMP
                WHERE idLaboratorio = :idLaboratorio
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
            $stmt->bindParam(':idLaboratorio', $idLaboratorio, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error al actualizar comentario del laboratorio: " . $e->getMessage());
            throw new Exception("Error al actualizar comentario del laboratorio");
        }
    }

    /**
     * Obtiene todos los laboratorios (solo datos básicos)
     * @return array Array de laboratorios
     */
    public function obtenerTodos() {
        try {
            $query = "
                SELECT
                    idLaboratorio,
                    nombre,
                    comentario,
                    activo,
                    created_at,
                    updated_at
                FROM Laboratorios
                WHERE activo = 1
                ORDER BY nombre
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error al obtener laboratorios: " . $e->getMessage());
            throw new Exception("Error al obtener laboratorios");
        }
    }

    /**
     * Formatea un timestamp unix a formato legible
     * @param int $timestamp Timestamp unix
     * @return string Fecha formateada
     */
    private function formatearFecha($timestamp) {
        if (!$timestamp) {
            return 'N/A';
        }

        // Si es un timestamp numérico (epoch)
        if (is_numeric($timestamp)) {
            return date('d/m/Y H:i', $timestamp);
        }

        // Si ya es una fecha string
        return date('d/m/Y H:i', strtotime($timestamp));
    }

    /**
     * Obtiene estadísticas generales de todos los laboratorios
     * @return array Estadísticas globales
     */
    public function obtenerEstadisticas() {
        try {
            $query = "
                SELECT
                    COUNT(DISTINCT l.idLaboratorio) as total_laboratorios,
                    COUNT(DISTINCT e.idEquipo) as total_equipos,
                    COUNT(DISTINCT CASE WHEN r.estado = 1 THEN e.idEquipo END) as equipos_funcionando,
                    COUNT(DISTINCT CASE WHEN r.estado = 0 THEN e.idEquipo END) as equipos_con_problemas,
                    COUNT(DISTINCT CASE WHEN r.estado = 2 THEN e.idEquipo END) as equipos_en_reparacion,
                    COUNT(DISTINCT CASE WHEN r.idRegistro IS NULL THEN e.idEquipo END) as equipos_sin_registro
                FROM Laboratorios l
                LEFT JOIN Equipos e ON l.idLaboratorio = e.idLaboratorio AND e.activo = 1
                LEFT JOIN (
                    SELECT r1.*
                    FROM Registro r1
                    INNER JOIN (
                        SELECT idEquipo, MAX(fecha) as max_fecha
                        FROM Registro
                        GROUP BY idEquipo
                    ) r2 ON r1.idEquipo = r2.idEquipo AND r1.fecha = r2.max_fecha
                ) r ON e.idEquipo = r.idEquipo
                WHERE l.activo = 1
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            throw new Exception("Error al obtener estadísticas");
        }
    }
}
