<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AuthController;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    if ($auth->login($_POST['email'], $_POST['password'])) {
        header("Location: index.php");
        exit;
    } else {
        echo "❌ Credenciales incorrectas";
    }
}
?>

<form method="POST">
    Email: <input type="email" name="email" required><br>
    Contraseña: <input type="password" name="password" required><br>
    <button type="submit">Iniciar sesión</button>
</form>
