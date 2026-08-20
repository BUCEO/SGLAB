<?php
require_once 'Database.php';

class TipoUsuario {
    
    // Mapeo de idTipoUsuario a roles
    private static $rolesMap = [
        1 => ['Administrador'],
        2 => ['Docente'], 
        3 => ['Estudiante']
    ];
    
    // Mapeo de idTipoUsuario a nombres
    private static $nombresMap = [
        1 => 'Administrador',
        2 => 'Docente',
        3 => 'Estudiante'
    ];
    
    // Mapeo de idTipoUsuario a descripciones
    private static $descripcionesMap = [
        1 => 'Usuario administrador con acceso total y 2FA obligatorio',
        2 => 'Docente con acceso a gestión de equipos',
        3 => 'Estudiante con acceso mediante GitHub OAuth'
    ];
    
    public static function obtenerRolesPorTipo($idTipoUsuario) {
        return self::$rolesMap[$idTipoUsuario] ?? ['Usuario'];
    }
    
    public static function obtenerNombrePorTipo($idTipoUsuario) {
        return self::$nombresMap[$idTipoUsuario] ?? 'Usuario';
    }
    
    public static function obtenerDescripcionPorTipo($idTipoUsuario) {
        return self::$descripcionesMap[$idTipoUsuario] ?? 'Usuario del sistema';
    }
    
    public static function obtenerTodos() {
        $tipos = [];
        foreach (self::$rolesMap as $id => $roles) {
            $tipos[] = [
                'idTipoUsuario' => $id,
                'nombre' => self::$nombresMap[$id],
                'descripcion' => self::$descripcionesMap[$id],
                'activo' => true
            ];
        }
        return $tipos;
    }
    
    public static function obtenerPorId($id) {
        if (!isset(self::$rolesMap[$id])) {
            return null;
        }
        
        return [
            'idTipoUsuario' => $id,
            'nombre' => self::$nombresMap[$id],
            'descripcion' => self::$descripcionesMap[$id],
            'activo' => true
        ];
    }

    public static function obtenerTodosConRoles() {
        $tipos = self::obtenerTodos();
        $resultado = [];
        
        foreach ($tipos as $tipo) {
            $resultado[] = [
                'idTipoUsuario' => $tipo['idTipoUsuario'],
                'nombre' => $tipo['nombre'],
                'descripcion' => $tipo['descripcion'],
                'roles' => self::obtenerRolesPorTipo($tipo['idTipoUsuario'])
            ];
        }
        
        return $resultado;
    }
    
    public static function existe($idTipoUsuario) {
        return isset(self::$rolesMap[$idTipoUsuario]);
    } 
    public static function obtenerIdsValidos() {
        return array_keys(self::$rolesMap);
    }
}
?>