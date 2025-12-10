<?php
// conexion.php — conexión segura para páginas y endpoints JSON
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
  $conn->set_charset("utf8mb4");
} catch (Throwable $e) {
  // Si quien llama es el endpoint de sugerencias, devolvemos JSON vacío
  $uri = $_SERVER['REQUEST_URI'] ?? '';
  $is_api = (strpos($uri, 'api_sugerencias.php') !== false);
  if ($is_api) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(200);        // <- importante: 200 para que el fetch no falle
    echo '[]';
    exit;
  }
  // Para páginas normales, solo cortamos sin imprimir texto (evita HTML roto)
  http_response_code(500);
  exit;
}
