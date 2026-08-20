<?php
require __DIR__ . '/vendor/autoload.php';

use App\Database\ConexionDB;

// ==============================
// 1. Detectar IP del agente
// ==============================
$ip_cliente = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// ==============================
// 2. Consultar si existe en la tabla registro
// ==============================
$db = ConexionDB::getInstancia()->getConexion();

$stmt = $db->prepare("SELECT r.id, e.hostname, e.serial 
                      FROM registro r 
                      JOIN equipo e ON r.equipo_id = e.id 
                      WHERE r.ip = :ip");
$stmt->execute(['ip' => $ip_cliente]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registro) {
    die("❌ No existe un registro asociado a la IP {$ip_cliente}");
    openlog("MiAplicacionPHP", LOG_PID, LOG_USER);
    // Genera un mensaje de tipo 'info' (informativo).
    syslog(LOG_INFO, "Este es un mensaje informativo de mi aplicación.");
    // Genera un mensaje de tipo 'warning' (advertencia).
    syslog(LOG_WARNING, "Se ha detectado un posible problema en la aplicación.");
    // Cierra la conexión con el servicio de registro del sistema.
    closelog();
}

// ==============================
// 3. Mostrar datos del equipo y formulario
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ci = $_POST['ci'] ?? '';
    $comentario = $_POST['comentario'] ?? '';
    $estado = $_POST['estado'] ?? 'Pendiente';

    if (!validarCI($ci)) {
        die("❌ La cédula de identidad no es válida.");
    }

    // Insertar comentario + estado en registro
    $stmt = $db->prepare("UPDATE registro 
                          SET ci = :ci, comentario = :comentario, estado = :estado 
                          WHERE id = :id");
    $stmt->execute([
        'ci' => $ci,
        'comentario' => $comentario,
        'estado' => $estado,
        'id' => $registro['id']
    ]);

    echo "✅ Registro actualizado correctamente.";
} else {
    // Mostrar formulario
    echo "<h2>Equipo detectado</h2>";
    echo "<p>Hostname: {$registro['hostname']}</p>";
    echo "<p>Serial: {$registro['serial']}</p>";

    echo '<form method="POST">';
    echo 'Cédula de Identidad: <input type="text" name="ci" required><br>';
    echo 'Comentario: <textarea name="comentario"></textarea><br>';
    echo 'Estado: 
            <select name="estado">
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
              <option value="Reparación">En reparación</option>
            </select><br>';
    echo '<button type="submit">Guardar</button>';
    echo '</form>';
}

// ==============================
// 4. Función validar CI uruguaya
// ==============================
function validarCI(string $ci): bool {
    $ci = preg_replace('/[^0-9]/', '', $ci); // quitar separadores
    if (strlen($ci) < 7 || strlen($ci) > 8) {
        return false;
    }

    // Agregar cero a la izquierda si tiene 7 dígitos
    $ci = str_pad($ci, 8, "0", STR_PAD_LEFT);

    $base = substr($ci, 0, 7);
    $digito = intval(substr($ci, -1));

    $coef = [2,9,8,7,6,3,4]; // coeficientes de validación
    $suma = 0;

    for ($i = 0; $i < 7; $i++) {
        $suma += intval($base[$i]) * $coef[$i];
    }

    $resto = $suma % 10;
    $verificador = ($resto === 0) ? 0 : 10 - $resto;

    return $verificador === $digito;
}
