<?php
// Modelo/Usuario.php
class Usuario {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function autenticar($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            return $usuario;
        }
        return false;
    }
    
    public function crear($datos) {
        $datos['password'] = password_hash($datos['password'], PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, password, rol, ci) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$datos['nombre'], $datos['email'], $datos['password'], $datos['rol'], $datos['ci']]);
    }
    
    // Otros métodos CRUD...
}

// Modelo/Equipo.php
class Equipo {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function listar($filtros = []) {
        $sql = "SELECT * FROM equipos WHERE 1=1";
        $params = [];
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND estado = ?";
            $params[] = $filtros['estado'];
        }
        
        if (!empty($filtros['laboratorio'])) {
            $sql .= " AND laboratorio = ?";
            $params[] = $filtros['laboratorio'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function actualizarEstado($id, $estado, $descripcion = null) {
        $sql = "UPDATE equipos SET estado = ?, descripcion = ?, actualizado_en = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estado, $descripcion, $id]);
    }
    
    // Otros métodos CRUD...
}

// Modelo/Reporte.php
class Reporte {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function crear($equipo_id, $usuario_id, $estado, $descripcion) {
        $stmt = $this->db->prepare("INSERT INTO reportes (equipo_id, usuario_id, estado, descripcion) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$equipo_id, $usuario_id, $estado, $descripcion]);
    }
    
    public function porFecha($desde, $hasta) {
        $stmt = $this->db->prepare("SELECT r.*, e.nombre as equipo_nombre FROM reportes r JOIN equipos e ON r.equipo_id = e.id WHERE r.fecha_hora BETWEEN ? AND ? ORDER BY r.fecha_hora DESC");
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}