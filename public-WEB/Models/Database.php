<?php
require_once __DIR__ . '../../vendor/autoload.php';
use Dotenv\Dotenv;

class Database {
    private static $instances = [];
    private $connection;

    private function __construct($userType = 'login') {
        try {
            // Cargar variables de entorno
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
            
            // Validar variables requeridas
            $dotenv->required([
                'DB_HOST', 'DB_NAME',
                'DB_USER_LOGIN', 'DB_PASSWORD_LOGIN',
                'DB_USER_ESTUDIANTE', 'DB_PASSWORD_ESTUDIANTE',
                'DB_USER_DOCENTE', 'DB_PASSWORD_DOCENTE',
                'DB_USER_ADMIN', 'DB_PASSWORD_ADMIN'
            ]);
            
            $host = $_ENV['DB_HOST'];
            $dbname = $_ENV['DB_NAME'];
            
            // Determinar credenciales según el tipo de usuario
            switch($userType) {
                case 'estudiante':
                    $username = $_ENV['DB_USER_ESTUDIANTE'];
                    $password = $_ENV['DB_PASSWORD_ESTUDIANTE'];
                    break;
                case 'docente':
                    $username = $_ENV['DB_USER_DOCENTE'];
                    $password = $_ENV['DB_PASSWORD_DOCENTE'];
                    break;
                case 'admin':
                    $username = $_ENV['DB_USER_ADMIN'];
                    $password = $_ENV['DB_PASSWORD_ADMIN'];
                    break;
                case 'login':
                default:
                    $username = $_ENV['DB_USER_LOGIN'];
                    $password = $_ENV['DB_PASSWORD_LOGIN'];
                    break;
            }

            // Crear conexión PDO
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
                $username, 
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            throw new Exception("No se pudo conectar a la base de datos: " . $e->getMessage());
        }
    }

    public static function getInstance($userType = 'login') {
        if (!isset(self::$instances[$userType])) {
            self::$instances[$userType] = new Database($userType);
        }
        return self::$instances[$userType];
    }

    public static function getConnection($userType = 'login') {
        return self::getInstance($userType)->connection;
    }
}
?>