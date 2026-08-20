<?php
session_start();

if (!isset($_SESSION["logged_in"]) || !isset($_SESSION["id_estudiante"])) {
    header("Location: login.html");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=BDDSGLAB6;charset=utf8", "UserWeb", "LosCosmicos2025");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT id_equipo FROM equipo ORDER BY id_equipo ASC");
$equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_equipo = $_POST['equipo'] ?? null;
    $estado = isset($_POST['no_funciona']) ? 1 : 0;
    $comentario = $_POST['comentario'] ?? '';
    $id_estudiante = $_SESSION['id_estudiante'];
    $epochtime = time(); // Fecha actual

    if ($id_equipo) {
        $stmt = $pdo->prepare("INSERT INTO registro (epochtime, id_equipo, id_estudiante, estado, comentario) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$epochtime, $id_equipo, $id_estudiante, $estado, $comentario]);
        header("Location: login.html");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Estado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/estilos_estado.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <header class="page-header text-center py-3">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a href="login.html" class="btn btn-primary btn-sm custom-back-btn">Volver</a>
            <h1 class="mb-0 flex-grow-1 text-center page-title-text">Registro Estado de equipos</h1>
            <div style="width: 80px;"></div>
        </div>
    </header>

    <main class="flex-grow-1 d-flex justify-content-center align-items-center">
        <div class="form-container">
            <form method="POST" action="estado.php">
                <div class="mb-4 text-center">
                    <img src="img/PC.png" alt="Icono de Equipo PC" class="img-fluid equipo-icon">
                    <label for="equipoSelect" class="form-label d-block">Equipo</label>
                    <select name="equipo" class="form-select custom-select" id="equipoSelect" required>
                        <?php foreach ($equipos as $equipo): ?>
                            <option value="<?= $equipo['id_equipo'] ?>">PC<?= $equipo['id_equipo'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4 text-center">
                    <div class="form-check d-inline-block">
                        <input class="form-check-input" type="checkbox" name="no_funciona" id="noFuncionaCheck">
                        <label class="form-check-label fw-bold" for="noFuncionaCheck"> ¿Funciona? </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="comentarioTextArea" class="form-label d-block text-center">Comentario</label>
                    <textarea name="comentario" class="form-control custom-textarea" id="comentarioTextArea" rows="4"></textarea>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg custom-submit-btn">Enviar</button>
                </div>
            </form>
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