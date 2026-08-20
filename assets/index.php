<?php
session_start();

$host = "localhost";
$dbname = "BDDSGLAB6";
$username = "UserWeb";
$password = "LosCosmicos2025";

$passwordAdmin = "admin1234";


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $ci = $_POST["ci"];
        $password_attempt = $_POST["password"] ?? ''; 

        $stmt = $pdo->prepare("SELECT id_estudiante, nombre, apellido, ci FROM estudiante WHERE ci = ?");
        $stmt->execute([$ci]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Usuario encontrado
            if ($user["id_estudiante"] == 0) {
                // Es el administrador
                if ($password_attempt === $passwordAdmin) {
                    // Contraseña correcta
                    $_SESSION["logged_in"] = true;
                    $_SESSION["id_estudiante"] = $user["id_estudiante"];
                    $_SESSION["nombre"] = $user["nombre"];
                    $_SESSION["apellido"] = $user["apellido"];
                    $_SESSION["ci"] = $user["ci"];
                    $_SESSION["rol"] = "admin"; 
                    header("Location: menu_admin.html");
                    exit();
                } else {
                    // Contraseña incorrecta
                    $_SESSION["login_error"] = "Contraseña de administrador incorrecta.";
                    header("Location: login_admin.html"); // Redirigir al login de admin
                    exit();
                }
            } else {
                // Es un estudiante
                $_SESSION["logged_in"] = true;
                $_SESSION["id_estudiante"] = $user["id_estudiante"];
                $_SESSION["nombre"] = $user["nombre"];
                $_SESSION["apellido"] = $user["apellido"];
                $_SESSION["ci"] = $user["ci"];
                 $_SESSION["rol"] = "estudiante"; // Añadimos el rol
                header("Location: estado.php");
                exit();
            }
        } else {
            // Usuario no encontrado
            $_SESSION["login_error"] = "Cédula incorrecta. Intente de nuevo.";
             
             if (!empty($password_attempt)) {
                header("Location: login.html");
             } else {
                header("Location: login.html");
             }
            exit();
        }
    } else {
        
        header("Location: login.html");
        exit();
    }

} catch (PDOException $e) {
    $_SESSION["login_error"] = "Error de conexión: " . $e->getMessage();
    header("Location: login.html");
    exit();
}
?>