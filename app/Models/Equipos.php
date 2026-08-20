<?php
namespace App\Models;

use App\Database\ConexionDB;
use PDO;

class Equipos {
    public int $id;
    public string $serialNumber;
    public string $hostname;
    public string $mac;
    public string $cpu;
    public string $ram;
    public string $diskType;
    public string $diskTotal;
    public string $laboratorio;

    public function __construct($id, $serialNumber, $hostname, $mac, $cpu, $ram, $diskType, $diskTotal, $laboratorio) {
        $this->id = $id;
        $this->serialNumber = $serialNumber;
        $this->hostname = $hostname;
        $this->mac = $mac;
        $this->cpu = $cpu;
        $this->ram = $ram;
        $this->diskType = $diskType;
        $this->diskTotal = $diskTotal;
        $this->laboratorio = $laboratorio;
    }

    public static function listarPorLaboratorio(string $laboratorio): array {
        $db = ConexionDB::getInstancia()->getConexion();
        $stmt = $db->prepare("SELECT * FROM equipos WHERE laboratorio = ?");
        $stmt->execute([$laboratorio]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $equipos = [];
        foreach ($result as $row) {
            $equipos[] = new Equipos(
                $row['id'],
                $row['serialNumber'],
                $row['hostname'],
                $row['mac'],
                $row['cpu'],
                $row['ram'],
                $row['diskType'],
                $row['diskTotal'],
                $row['laboratorio']
            );
        }
        return $equipos;
    }

    public static function altaEquipo(string $serialNumber, string $hostname, string $mac, string $cpu, string $ram, string $diskType, string $diskTotal, string $laboratorio): bool {
        $db = ConexionDB::getInstancia()->getConexion();
        $stmt = $db->prepare("INSERT INTO equipos (serialNumber, hostname, mac, cpu, ram, diskType, diskTotal, laboratorio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$serialNumber, $hostname, $mac, $cpu, $ram, $diskType, $diskTotal, $laboratorio]);
    }

    public static function actualizarSerial(int $id, string $serialNumber): bool {
        $db = ConexionDB::getInstancia()->getConexion();
        $stmt = $db->prepare("UPDATE equipos SET serialNumber = ? WHERE id = ?");
        return $stmt->execute([$serialNumber, $id]);
    }
}
