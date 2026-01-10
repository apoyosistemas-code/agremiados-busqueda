<?php
// conexion.php
require_once __DIR__ . '/config.php';

// INICIAR SESIÓN GLOBALMENTE
// Esto es vital: si no está esto, el login se olvida al cambiar de página.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
  $conn->set_charset("utf8mb4");
} catch (Throwable $e) {
  http_response_code(500);
  exit('Error de conexión a la base de datos.');
}

// Función para registrar movimientos
function registrar_auditoria($conn, $accion, $detalle) {
    // Solo registramos si hay un usuario logueado
    if (isset($_SESSION['user_id'])) {
        $uid = $_SESSION['user_id'];
        $uName = $_SESSION['user_name'];
        $ip = $_SERVER['REMOTE_ADDR'];

        $sql = "INSERT INTO auditoria (usuario_id, usuario_nombre, accion, detalle, ip) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $uid, $uName, $accion, $detalle, $ip);
        $stmt->execute();
    }
}
?>