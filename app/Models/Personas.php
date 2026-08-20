<?php
namespace App\Models;

class Personas {
    public string $ci;
    public string $nombre;
    public string $apellido;
    public string $direccion;
    public string $fechaNacimiento;

    public function __construct($ci, $nombre, $apellido, $direccion, $fechaNacimiento) {
        $this->ci = $ci;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->direccion = $direccion;
        $this->fechaNacimiento = $fechaNacimiento;
    }
}
