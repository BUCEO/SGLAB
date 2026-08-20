<?php
// Test de sesiones
session_start();

echo "<h2>Test de Sesiones PHP</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";

// Probar escribir en la sesión
$_SESSION['test_key'] = 'test_value_' . time();
$_SESSION['test_number'] = 12345;

echo "<h3>Datos escritos en la sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Información de configuración de sesión
echo "<h3>Configuración de Sesión:</h3>";
echo "<ul>";
echo "<li><strong>session.save_path:</strong> " . ini_get('session.save_path') . "</li>";
echo "<li><strong>session.save_handler:</strong> " . ini_get('session.save_handler') . "</li>";
echo "<li><strong>session.cookie_lifetime:</strong> " . ini_get('session.cookie_lifetime') . "</li>";
echo "<li><strong>session.cookie_path:</strong> " . ini_get('session.cookie_path') . "</li>";
echo "<li><strong>session.cookie_domain:</strong> " . ini_get('session.cookie_domain') . "</li>";
echo "<li><strong>session.cookie_httponly:</strong> " . ini_get('session.cookie_httponly') . "</li>";
echo "<li><strong>session.use_cookies:</strong> " . ini_get('session.use_cookies') . "</li>";
echo "<li><strong>session.use_only_cookies:</strong> " . ini_get('session.use_only_cookies') . "</li>";
echo "</ul>";

// Verificar si el directorio de sesiones es escribible
$save_path = session_save_path();
if (empty($save_path)) {
    $save_path = sys_get_temp_dir();
}

echo "<h3>Directorio de sesiones:</h3>";
echo "<p><strong>Path:</strong> " . $save_path . "</p>";
echo "<p><strong>Existe:</strong> " . (is_dir($save_path) ? "SI" : "NO") . "</p>";
echo "<p><strong>Escribible:</strong> " . (is_writable($save_path) ? "SI" : "NO") . "</p>";

echo "<hr>";
echo "<p><a href='test_session_read.php'>Ir a la página de lectura de sesión</a></p>";
?>
