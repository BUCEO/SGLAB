<?php
namespace App\Controllers;

use App\Models\Usuarios;

class AuthController {
    public function login($email, $password) {
        $usuario = Usuarios::login($email, $password);
        if ($usuario) {
            $_SESSION['usuario'] = $usuario->nombre;
            return true;
        }
        return false;
    }

    public function logout() {
        session_destroy();
    }
}
