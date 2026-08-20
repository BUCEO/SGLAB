<?php
namespace App\Models;

class Perfiles {
    public int $id;
    public string $rol;
    public string $permisos;

    public function __construct(int $id, string $rol, string $permisos) {
        $this->id = $id;
        $this->rol = $rol;
        $this->permisos = $permisos;
    }
}
