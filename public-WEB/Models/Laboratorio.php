<?php
require_once 'Database.php';

class Laboratorio {
    private $idLaboratorio;
    private $nombre;
    private $comentario;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->idLaboratorio = $data['idLaboratorio'] ?? null;
            $this->nombre = $data['nombre'] ?? null;
            $this->comentario = $data['comentario'] ?? null;
        }
    }

    // Getters
    public function getIdLaboratorio() { return $this->idLaboratorio; }
    public function getNombre() { return $this->nombre; }
    public function getComentario() { return $this->comentario; }

    // Métodos CRUD
    public static function obtenerTodos() {
        $db = Database::getConnection();
        $sql = "SELECT * FROM Laboratorios ORDER BY nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        $laboratorios = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $laboratorios[] = new Laboratorio($data);
        }
        
        return $laboratorios;
    }

    public static function obtenerPorId($id) {
        $db = Database::getConnection();
        $sql = "SELECT * FROM Laboratorios WHERE idLaboratorio = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new Laboratorio($data) : null;
    }

    public static function obtenerTodosComoArray() {
        $db = Database::getConnection('admin'); 
        try {
            $sql = "SELECT idLaboratorio, nombre FROM Laboratorios ORDER BY nombre";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodosComoArray: " . $e->getMessage());
            return [];
        }
    }
}
?>