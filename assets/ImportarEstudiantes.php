<?php
session_start();

// Solo accesible para admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.html');
    exit;
}

$mensaje = "";
$errores = [];
$insertados = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $tmpName = $_FILES['csv_file']['tmp_name'];
    if (is_uploaded_file($tmpName)) {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=BDDSGLAB6;charset=utf8", "UserWeb", "LosCosmicos2025");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $lineas = file($tmpName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (count($lineas) <= 1) {
                throw new Exception("El archivo está vacío o solo contiene encabezado.");
            }

            // Detectar delimitador (coma o punto y coma)
            $primeralinea = $lineas[0];
            $delimitador = strpos($primeralinea, ';') !== false ? ';' : ',';

            unset($lineas[0]); // eliminar cabecera

            $stmt = $pdo->prepare("
                INSERT INTO estudiante (id_estudiante, apellido, nombre, ci, email)
                VALUES (:id, :apellido, :nombre, :ci, :email)
                ON DUPLICATE KEY UPDATE
                  apellido = VALUES(apellido),
                  nombre   = VALUES(nombre),
                  ci       = VALUES(ci),
                  email    = VALUES(email)
            ");

            foreach ($lineas as $linea) {
                $data = explode($delimitador, $linea);

                if (count($data) < 4) {
                    $errores[] = "Linea invalida: $linea";
                    continue;
                }

                $id_estudiante = isset($data[0]) ? intval(trim($data[0])) : 0;
                $apellido      = isset($data[1]) ? mb_strtoupper(trim($data[1])) : '';
                $nombre        = isset($data[2]) ? mb_strtoupper(trim($data[2])) : '';
                $ci            = isset($data[3]) ? intval(trim($data[3])) : 0;
                $email         = isset($data[4]) ? trim($data[4]) : '';
                

                if ($id_estudiante === 0 || $ci === 0 || $apellido === '' || $nombre === '') {
                    $errores[] = "Datos faltantes en línea: $linea";
                    continue;
                }

                $stmt->execute([
                    ':id'       => $id_estudiante,
                    ':apellido' => $apellido,
                    ':nombre'   => $nombre,
                    ':ci'       => $ci,
                    ':email'    => $email
                ]);
                $insertados++;
            }

            $mensaje = "Se importaron $insertados estudiantes correctamente.";
        } catch (Exception $e) {
            $mensaje = "❌ Error: " . $e->getMessage();
        }
    } else {
        $mensaje = "No se pudo subir el archivo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importar Estudiantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/importarEstudiantes.css">
</head>
<body>

<header class="page-header text-center py-3">
    <div class="container-fluid d-flex justify-content-between align-items-center">
            <a href="menu_admin.html" class="btn btn-primary btn-sm custom-back-btn">Volver</a>
            <h1 class="mb-0 flex-grow-1 text-center page-title-text">Importar Estudiantes</h1>
            <div style="width: 80px;"></div>
    </div>
</header>

<main class="flex-grow-1 d-flex justify-content-center align-items-center">
    <div class="form-container">

        
        <?php if ($mensaje): ?>
            <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <form method="POST" action="ImportarEstudiantes.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="csv_file" class="form-label">Seleccioná el archivo CSV</label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Importar</button>
        </form>
    </div>
</main>

<footer class="text-center py-3">
    <img src="img/Logo-DGETP-UTU-ByN-Transparente-PNG.png" alt="Logo ANEP Y UTU" class="img-fluid footer-logo">
</footer>

</body>
</html>
