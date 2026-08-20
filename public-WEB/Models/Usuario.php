<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/TipoUsuario.php';

class Usuario {
    private $id;
    private $ci;
    private $nombre;
    private $username;
    private $email;
    private $password;
    private $idTipoUsuario;
    private $secret_2fa;
    private $github_id;
    private $github_username;
    private $github_email;
    private $activo;
    private $fechaCreacion;
    private $fechaModificacion;
    private $roles = [];

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->ci = $data['ci'] ?? null;
            $this->nombre = $data['nombre'] ?? null;
            $this->username = $data['username'] ?? null;
            $this->email = $data['email'] ?? null;
            $this->password = $data['password'] ?? null;
            $this->idTipoUsuario = $data['idTipoUsuario'] ?? null;
            $this->secret_2fa = $data['secret_2fa'] ?? null;
            $this->github_id = $data['github_id'] ?? null;
            $this->github_username = $data['github_username'] ?? null;
            $this->github_email = $data['github_email'] ?? null;
            $this->activo = isset($data['activo']) ? (bool)$data['activo'] : true;
            $this->fechaCreacion = $data['fechaCreacion'] ?? null;
            $this->fechaModificacion = $data['fechaModificacion'] ?? null;
            $this->roles = $data['roles'] ?? [];
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getCi() { return $this->ci; }
    public function getNombre() { return $this->nombre; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getIdTipoUsuario() { return $this->idTipoUsuario; }
    public function getActivo() { return $this->activo; }
    public function getRoles() { return $this->roles; }

    // Setters
    public function setCi($ci) { $this->ci = $ci; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setUsername($username) { $this->username = $username; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }
    public function setIdTipoUsuario($idTipoUsuario) { $this->idTipoUsuario = $idTipoUsuario; }
    public function darDeBaja() { $this->activo = false; }
    public function reactivar() { $this->activo = true; }

    // Métodos de utilidad
    public function esAdministrador() {
        return in_array('Administrador', $this->roles);
    }
    
    public function esDocente() {
        return in_array('Docente', $this->roles);
    }
    
    public function esEstudiante() {
        return in_array('Estudiante', $this->roles);
    }

    // Métodos CRUD
    public static function obtenerPorId($id) {
        $db = Database::getConnection('admin');
        $sql = "SELECT * FROM Usuarios WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $data['roles'] = TipoUsuario::obtenerRolesPorTipo($data['idTipoUsuario']);
            return new Usuario($data);
        }
        return null;
    }

    public static function obtenerTodos($soloActivos = false, $filtroTipo = null) {
        $db = Database::getConnection('admin');
        
        $sql = "SELECT u.*, 
                CASE 
                    WHEN u.idTipoUsuario = 1 THEN 'Administrador'
                    WHEN u.idTipoUsuario = 2 THEN 'Docente'
                    WHEN u.idTipoUsuario = 3 THEN 'Estudiante'
                    ELSE 'Usuario'
                END as tipo_usuario_nombre
                FROM Usuarios u WHERE 1=1";
        
        $params = [];
        
        if ($soloActivos) {
            $sql .= " AND u.activo = 1";
        }
        
        if ($filtroTipo !== null) {
            $sql .= " AND u.idTipoUsuario = :filtroTipo";
            $params[':filtroTipo'] = $filtroTipo;
        }
        
        $sql .= " ORDER BY u.nombre";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        $usuarios = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuarios[] = $data;
        }
        return $usuarios;
    }

    public static function crear($datos) {
        $db = Database::getConnection('admin');
        
        try {
            // Validar que no exista CI o email duplicado
            $sql = "SELECT COUNT(*) FROM Usuarios WHERE ci = :ci OR email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':ci' => $datos['ci'],
                ':email' => $datos['email']
            ]);
            
            if ($stmt->fetchColumn() > 0) {
                return false;
            }
            
            // Generar username si no viene
            $username = $datos['username'] ?? strtolower(str_replace(' ', '.', $datos['nombre']));
            
            $sql = "INSERT INTO Usuarios (ci, nombre, username, email, password, idTipoUsuario, activo) 
                    VALUES (:ci, :nombre, :username, :email, :password, :idTipoUsuario, 1)";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':ci' => $datos['ci'],
                ':nombre' => $datos['nombre'],
                ':username' => $username,
                ':email' => $datos['email'],
                ':password' => password_hash($datos['password'], PASSWORD_DEFAULT),
                ':idTipoUsuario' => $datos['idTipoUsuario']
            ]);
        } catch (PDOException $e) {
            error_log("Error en Usuario::crear: " . $e->getMessage());
            return false;
        }
    }

    public static function actualizar($id, $datos) {
        $db = Database::getConnection('admin');
        
        try {
            $sql = "UPDATE Usuarios SET 
                    ci = :ci, 
                    nombre = :nombre,
                    username = :username,
                    email = :email,
                    idTipoUsuario = :idTipoUsuario,
                    fechaModificacion = NOW()";
            
            $params = [
                ':id' => $id,
                ':ci' => $datos['ci'],
                ':nombre' => $datos['nombre'],
                ':username' => $datos['username'],
                ':email' => $datos['email'],
                ':idTipoUsuario' => $datos['idTipoUsuario']
            ];
            
            // Solo actualizar password si viene
            if (!empty($datos['password'])) {
                $sql .= ", password = :password";
                $params[':password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizar: " . $e->getMessage());
            return false;
        }
    }

    public static function eliminar($id) {
        $db = Database::getConnection('admin');
        
        try {
            // Baja lógica, no eliminación física
            $sql = "UPDATE Usuarios SET activo = 0, fechaModificacion = NOW() WHERE id = :id";
            $stmt = $db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error en Usuario::eliminar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Importación masiva desde CSV
     * Formato esperado: Numero,Apellido,Nombre,Documento,e-mail
     */
    public static function importarDesdeCSV($archivo) {
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Error al subir el archivo'];
        }

        $csvFile = fopen($archivo['tmp_name'], 'r');
        if (!$csvFile) {
            return ['success' => false, 'error' => 'No se pudo abrir el archivo'];
        }

        // Leer y validar header
        $header = fgetcsv($csvFile);
        if (!$header || count($header) < 5) {
            fclose($csvFile);
            return ['success' => false, 'error' => 'Formato de archivo incorrecto. Debe tener al menos 5 columnas.'];
        }

        $usuariosCreados = 0;
        $errores = [];
        $lineaActual = 1;

        while (($data = fgetcsv($csvFile)) !== false) {
            $lineaActual++;
            
            if (count($data) < 4) {
                $errores[] = "Línea $lineaActual: Datos incompletos";
                continue;
            }

            // Extraer datos
            $numero = $data[0];
            $apellido = trim($data[1]);
            $nombre = trim($data[2]);
            $documento = trim($data[3]);
            $email = isset($data[4]) && !empty(trim($data[4])) 
                ? trim($data[4]) 
                : strtolower(str_replace(' ', '.', $nombre . '.' . $apellido)) . '@estudiante.edu.uy';

            // Validaciones
            if (empty($documento) || empty($nombre) || empty($apellido)) {
                $errores[] = "Línea $lineaActual: Documento, nombre o apellido vacío";
                continue;
            }

            if (!is_numeric($documento)) {
                $errores[] = "Línea $lineaActual: Documento inválido ($documento)";
                continue;
            }

            try {
                $nombreCompleto = trim($nombre . ' ' . $apellido);
                $username = strtolower(str_replace(' ', '.', $nombreCompleto));
                $passwordDefault = 'Password' . substr($documento, -4);

                $resultado = self::crear([
                    'ci' => (int)$documento,
                    'nombre' => $nombreCompleto,
                    'username' => $username,
                    'email' => $email,
                    'password' => $passwordDefault,
                    'idTipoUsuario' => 3 // Estudiante por defecto
                ]);

                if ($resultado) {
                    $usuariosCreados++;
                } else {
                    $errores[] = "Línea $lineaActual: No se pudo crear usuario $nombreCompleto (posible duplicado)";
                }
            } catch (Exception $e) {
                $errores[] = "Línea $lineaActual: Error - " . $e->getMessage();
            }
        }

        fclose($csvFile);

        return [
            'success' => true,
            'message' => "$usuariosCreados usuario(s) creado(s) exitosamente",
            'total_procesados' => $lineaActual - 1,
            'creados' => $usuariosCreados,
            'errores' => $errores
        ];
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'ci' => $this->ci,
            'nombre' => $this->nombre,
            'username' => $this->username,
            'email' => $this->email,
            'idTipoUsuario' => $this->idTipoUsuario,
            'activo' => $this->activo,
            'roles' => $this->roles
        ];
    }
}
?>