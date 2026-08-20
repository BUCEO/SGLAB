<?php
$registros = [];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=BDDSGLAB6;charset=utf8", "UserWeb", "LosCosmicos2025");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

 
    $fechaSeleccionada = $_GET['fecha'] ?? '';
    $estadoSeleccionado = $_GET['estado'] ?? '';

    if (isset($_GET['buscar']) && $_GET['buscar'] === 'filtros') {

    
    if (empty($fechaSeleccionada) && $estadoSeleccionado === '') {
        $sql = "
            SELECT *
            FROM registro
            WHERE (id_equipo, epochtime) IN (
                SELECT id_equipo, MAX(epochtime)
                FROM registro
                GROUP BY id_equipo
            );

        ";

        $stmt = $pdo->query($sql);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
     else {
        // Filtrar por fecha y/o estado si se selecciono
        $sql = "SELECT * FROM registro WHERE 1";
        $params = [];

        if (!empty($fechaSeleccionada)) {
            $inicio = strtotime($fechaSeleccionada);
            $fin = $inicio + 86400;
            $sql .= " AND epochtime BETWEEN :inicio AND :fin";
            $params[':inicio'] = $inicio;
            $params[':fin'] = $fin;
        }

        if ($estadoSeleccionado === '1' || $estadoSeleccionado === '0') {
            $sql .= " AND estado = :estado";
            $params[':estado'] = $estadoSeleccionado;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error de conexión: " . $e->getMessage() . "</p>";
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Equipos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/estilos_estado_equipos.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <header class="page-header py-3">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a href="menu_admin.html" class="btn btn-primary btn-sm custom-back-btn">Volver</a>
            <h1 class="mb-0 flex-grow-1 text-center page-title-text">Estado de equipos</h1>
            <div style="width: 80px;"></div>
        </div>
    </header>

    <main class="flex-grow-1 d-flex justify-content-center align-items-start pt-4">
        <div class="fluid-container">
            <div class="filters-row mb-4">
                <form method="GET" action="estado_equipos.php" class="d-flex gap-2">
                    <input type="datetime-local" name="fecha" class="form-control custom-filter-input"
                        value="<?= htmlspecialchars($fechaSeleccionada ?? '') ?>">

                    <select name="estado" class="form-select custom-filter-select" aria-label="Estado">
                        <option value="" <?= $estadoSeleccionado === '' ? 'selected' : '' ?> disabled>Estado</option>
                        <option value="1" <?= $estadoSeleccionado === '1' ? 'selected' : '' ?>>Funciona</option>
                        <option value="0" <?= $estadoSeleccionado === '0' ? 'selected' : '' ?>>No Funciona</option>
                    </select>

                    <button type="submit" name="buscar" value="filtros" class="btn btn-primary btn-lg custom-btn">Buscar</button>
                </form>

            </div>

            <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                        <th scope="col">Fecha Hora</th>
                        <th scope="col">Equipo</th>
                        <th scope="col">ID Usuario</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Comentario</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php if (empty($registros)): ?>
                            <tr><td colspan="5" class="text-center">No hay registros</td></tr>
                        <?php else: ?>
                            <?php foreach ($registros as $reg): ?>
                                <tr>
                                    <td><?= date('Y-m-d H:i:s', $reg['epochtime']) ?></td>
                                    <td><?= $reg['id_equipo'] ?></td>
                                    <td><?= $reg['id_estudiante'] ?></td>
                                    <td><?= $reg['estado'] ? 'Funciona' : 'No Funciona' ?></td>
                                    <td><?= htmlspecialchars($reg['comentario']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    </table>
        </div>
    </main>
    <footer class="text-center py-3">
        <img src="img/Logo-DGETP-UTU-ByN-Transparente-PNG.png" alt="Logo ANEP Y UTU" class="img-fluid footer-logo">
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eOzrpbz/nC27tGBx/j"
        crossorigin="anonymous"></script>
</body>

</html>