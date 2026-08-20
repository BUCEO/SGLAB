<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\EquipoController;

$equipoController = new EquipoController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alta'])) {
        $equipoController->alta($_POST['serialNumber'], $_POST['hostname'], $_POST['mac'], $_POST['cpu'], $_POST['ram'], $_POST['diskType'], $_POST['diskTotal'], $_POST['laboratorio']);
        echo "✅ Equipo dado de alta";
    }

    if (isset($_POST['update'])) {
        $equipoController->actualizarSerial($_POST['id'], $_POST['serialNumber']);
        echo "✅ Número de serie actualizado";
    }
}

$equipos = $equipoController->listar("LAB4");
?>

<h1>Gestión de Equipos – LAB4</h1>

<h2>Alta de equipo</h2>
<form method="POST">
    Serial: <input type="text" name="serialNumber" required><br>
    Hostname: <input type="text" name="hostname" required><br>
    MAC: <input type="text" name="mac"><br>
    CPU: <input type="text" name="cpu"><br>
    RAM: <input type="text" name="ram"><br>
    Tipo Disco: <input type="text" name="diskType"><br>
    Total Disco: <input type="text" name="diskTotal"><br>
    Laboratorio: <input type="text" name="laboratorio" value="LAB4"><br>
    <button type="submit" name="alta">Dar de alta</button>
</form>

<h2>Lista de equipos</h2>
<table border="1">
    <tr>
        <th>ID</th><th>Serial</th><th>Hostname</th><th>Acción</th>
    </tr>
    <?php foreach ($equipos as $eq): ?>
    <tr>
        <td><?= $eq->id ?></td>
        <td><?= $eq->serialNumber ?></td>
        <td><?= $eq->hostname ?></td>
        <td>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $eq->id ?>">
                <input type="text" name="serialNumber" value="<?= $eq->serialNumber ?>">
                <button type="submit" name="update">Actualizar Serial</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
