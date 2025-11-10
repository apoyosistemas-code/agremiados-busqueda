<?php
// api_sugerencias.php — Autocomplete robusto
// SIEMPRE devuelve JSON válido (nada de warnings/notices en salida)

declare(strict_types=1);

// Silenciar notices/warnings para no romper JSON
@ini_set('display_errors', '0');
@error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

header('Content-Type: application/json; charset=utf-8');

// Evitar BOM / buffers previos
if (function_exists('ob_get_level')) {
  while (ob_get_level() > 0) { @ob_end_clean(); }
}

require_once __DIR__ . '/conexion.php';

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$out = [];

try {
  if ($q !== '' && isset($conn) && $conn instanceof mysqli) {

    // Partir en palabras y exigir que todas aparezcan
    $parts = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
    $where = [];
    $types = '';
    $vals  = [];

    foreach ($parts as $p) {
      $where[] = "NOMBRE_DEL_AGREMIADO COLLATE utf8mb4_spanish_ci LIKE CONCAT('%', ?, '%')";
      $types   .= 's';
      $vals[]   = $p;
    }

    $sql = "SELECT NOMBRE_DEL_AGREMIADO, COLEGIATURA, DNI
            FROM agremiados
            WHERE " . implode(' AND ', $where) . "
            ORDER BY NOMBRE_DEL_AGREMIADO ASC
            LIMIT 8";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
      $stmt->bind_param($types, ...$vals);
      $stmt->execute();
      $res = $stmt->get_result();

      while ($row = $res->fetch_assoc()) {
        $out[] = [
          'nombre'      => $row['NOMBRE_DEL_AGREMIADO'] ?? '',
          'colegiatura' => $row['COLEGIATURA'] ?? '',
          'dni'         => $row['DNI'] ?? '',
        ];
      }
      $stmt->close();
    }
  }
} catch (Throwable $e) {
  // Ignoramos y devolvemos []
}

// Entregar SIEMPRE 200 + JSON
http_response_code(200);
echo json_encode($out, JSON_UNESCAPED_UNICODE);
