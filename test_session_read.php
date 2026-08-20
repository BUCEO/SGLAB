<?php
// Test de lectura de sesiones
session_start();

echo "<h2>Test de Lectura de Sesión</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";

echo "<h3>Datos leídos de la sesión:</h3>";
if (empty($_SESSION)) {
    echo "<p style='color: red;'><strong>❌ ERROR: La sesión está vacía!</strong></p>";
    echo "<p>Esto significa que la sesión NO se está persistiendo entre páginas.</p>";
} else {
    echo "<p style='color: green;'><strong>✅ ÉXITO: La sesión contiene datos</strong></p>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

echo "<hr>";
echo "<p><a href='test_session.php'>Volver a la página de escritura</a></p>";
?>
