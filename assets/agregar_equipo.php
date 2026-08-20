<?php
$mensaje = "";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=BDDSGLAB6;charset=utf8", "UserWeb", "LosCosmicos2025");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cantidad"])) {
        $cantidad = intval($_POST["cantidad"]);

        $stmt = $pdo->query("SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'BDDSGLAB6' AND TABLE_NAME = 'equipo'");
        $siguienteId = $stmt->fetchColumn();


        $stmtInsert = $pdo->prepare("INSERT INTO equipo (nombre) VALUES (:nombre)");

        for ($i = 0; $i < $cantidad; $i++) {
            $nombre = "SGLAB6PC" . str_pad($siguienteId + $i, 2, "0", STR_PAD_LEFT);
            $stmtInsert->execute([':nombre' => $nombre]);
        }

        $mensaje = " Se agregaron $cantidad equipo(s) correctamente.";
        header("Location: menu_admin.html");
    }
} catch (PDOException $e) {
    $mensaje = " Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Equipo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilos_agregar_equipo.css">
</head>
<body class="d-flex flex-column min-vh-100">

<header class="page-header py-3">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <a href="menu_admin.html" class="btn btn-primary btn-sm">Volver</a>
        <h1 class="mb-0 flex-grow-1 text-center">Agregar equipo</h1>
        <div style="width: 80px;"></div>
    </div>
</header>

<main class="flex-grow-1 d-flex justify-content-center align-items-center">
    <div class="form-container">
        <form method="POST" action="agregar_equipo.php">
            <div class="mb-4 text-center">
                <img src="img/PC.png" alt="Icono de Computadora" class="img-fluid add-equipo-icon">
            </div>

            <div class="mb-4 text-center">
                <label for="cantidadInput" class="form-label fw-bold">Cantidad</label>
                <input type="number" name="cantidad" id="cantidadInput" class="form-control custom-number-input" value="1" min="1" required>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg">Agregar</button>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-info mt-3 text-center"><?= $mensaje ?></div>
            <?php endif; ?>

            
        </form>
    </div>
</main>

<footer class="text-center py-3">
    <img src="img/Logo-DGETP-UTU-ByN-Transparente-PNG.png" alt="Logo ANEP Y UTU" class="img-fluid footer-logo">
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>