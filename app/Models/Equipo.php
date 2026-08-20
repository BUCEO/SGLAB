<?php
// app/Models/Equipo.php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use JsonSerializable;

final class Equipo implements JsonSerializable
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $num_serie,
        public readonly string $nombre,
        public readonly string $estado,
        public readonly ?string $descripcion,
        public readonly string $laboratorio,
        public readonly \DateTimeImmutable $creado_en,
        public readonly ?\DateTimeImmutable $actualizado_en
    ) {}

    public static function crearDesdeArray(array $datos): self
    {
        return new self(
            $datos['id'] ?? null,
            $datos['num_serie'],
            $datos['nombre'],
            $datos['estado'] ?? 'Operativo',
            $datos['descripcion'] ?? null,
            $datos['laboratorio'],
            new \DateTimeImmutable($datos['creado_en'] ?? 'now'),
            isset($datos['actualizado_en']) ? new \DateTimeImmutable($datos['actualizado_en']) : null
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'num_serie' => $this->num_serie,
            'nombre' => $this->nombre,
            'estado' => $this->estado,
            'laboratorio' => $this->laboratorio,
            'creado_en' => $this->creado_en->format('Y-m-d H:i:s')
        ];
    }
}

final class EquipoRepository
{
    public function __construct(private Database $db) {}

    public function registrar(Equipo $equipo): ?Equipo
    {
        $stmt = $this->db->prepare("
            INSERT INTO equipos 
            (num_serie, nombre, estado, descripcion, laboratorio) 
            VALUES (:num_serie, :nombre, :estado, :descripcion, :laboratorio)
        ");

        $stmt->execute([
            ':num_serie' => $equipo->num_serie,
            ':nombre' => $equipo->nombre,
            ':estado' => $equipo->estado,
            ':descripcion' => $equipo->descripcion,
            ':laboratorio' => $equipo->laboratorio
        ]);

        return $this->obtenerPorId((int)$this->db->lastInsertId());
    }

    public function obtenerPorId(int $id): ?Equipo
    {
        $stmt = $this->db->prepare("SELECT * FROM equipos WHERE id = ?");
        $stmt->execute([$id]);
        
        return ($datos = $stmt->fetch(PDO::FETCH_ASSOC))
            ? Equipo::crearDesdeArray($datos)
            : null;
    }

    public function listarPorEstado(string $estado): array
    {
        $stmt = $this->db->prepare("SELECT * FROM equipos WHERE estado = ?");
        $stmt->execute([$estado]);
        
        return array_map(
            fn(array $datos) => Equipo::crearDesdeArray($datos),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }
}