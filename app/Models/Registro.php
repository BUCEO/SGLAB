<?php
namespace App\Models;

use App\Database\ConexionDB;
use PDO;

class Registro {
    public int $id;
    public string $fecha;
    public string $estado;
    public string $ip;
    public string $descripcion;
    public int $equipo_id;

    public function __construct($id, $fecha, $estado, $ip, $descripcion, $equipo_id) {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->estado = $estado;
        $this->ip = $ip;
        $this->descripcion = $descripcion;
        $this->equipo_id = $equipo_id;
    }

    public static function agregarComentario(int $equipoId, string $comentario): bool {
        $db = ConexionDB::getInstancia()->getConexion();
        $stmt = $db->prepare("INSERT INTO registros (fecha, estado, ip, descripcion, equipo_id) VALUES (NOW(), '1', '127.0.0.1', ?, ?)");
        return $stmt->execute([$comentario, $equipoId]);
    }

    public static function listarPorEquipo(int $equipoId): array {
        $db = ConexionDB::getInstancia()->getConexion();
        $stmt = $db->prepare("SELECT * FROM registros WHERE equipo_id = ? ORDER BY fecha DESC");
        $stmt->execute([$equipoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
