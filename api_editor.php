<?php
// api_editor.php
require_once "conexion.php";
header('Content-Type: application/json; charset=utf-8');

function json_ok($arr = []){ echo json_encode(['ok'=>true] + $arr); exit; }
function json_err($msg){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }

/** Obtiene y cachea columnas válidas (whitelist) */
function get_columns(mysqli $conn){
  static $cols = null;
  if ($cols !== null) return $cols;

  $cols = [];
  $res = $conn->query("DESCRIBE agremiados");
  while ($row = $res->fetch_assoc()){
    $cols[] = $row['Field'];
  }
  // id primero, si existe
  if (in_array('id', $cols)) {
    $cols = array_values(array_unique(array_merge(['id'], $cols)));
  }
  return $cols;
}

/** Construye una lista segura de columnas escapadas con backticks */
function backticks_cols(array $cols){
  return implode(",", array_map(fn($c)=>"`$c`", $cols));
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
  if ($action === 'columns') {
    $cols = get_columns($conn);
    json_ok(['columns'=>$cols]);
  }

  if ($action === 'list') {
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = min(100, max(1, intval($_GET['perPage'] ?? 20)));
    $off = ($page-1)*$perPage;
    $q = trim($_GET['q'] ?? '');

    $cols = get_columns($conn);
    $colsSql = backticks_cols($cols);

    // Filtros simples: colegiatura, DNI, nombre
    $where = "1";
    $types = "";
    $params = [];

    if ($q !== "") {
      $where = "(COLEGIATURA LIKE CONCAT('%', ?, '%') OR DNI LIKE CONCAT('%', ?, '%') OR NOMBRE_DEL_AGREMIADO LIKE CONCAT('%', ?, '%'))";
      $types = "sss";
      $params = [$q, $q, $q];
    }

    // total
    $sqlCount = "SELECT COUNT(*) AS c FROM agremiados WHERE $where";
    $stmt = $conn->prepare($sqlCount);
    if ($types !== "") $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
    $stmt->close();

    // page
    $sql = "SELECT $colsSql FROM agremiados WHERE $where ORDER BY COLEGIATURA+0 ASC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if ($types !== "") {
      $types2 = $types . "ii";
      $params2 = array_merge($params, [$perPage, $off]);
      $stmt->bind_param($types2, ...$params2);
    } else {
      $stmt->bind_param("ii", $perPage, $off);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Normaliza nulls (dejar como null; el front los dibuja vacío)
    json_ok(['rows'=>$rows,'total'=>intval($total)]);
  }

  if ($action === 'update') {
    $id  = intval($_POST['id'] ?? 0);
    $col = $_POST['col'] ?? '';
    if ($id <= 0) json_err("ID inválido");
    if ($col === '') json_err("Columna requerida");

    $cols = get_columns($conn);
    if (!in_array($col, $cols, true)) json_err("Columna no permitida");

    $val = $_POST['val'] ?? '';
    // vacío ⇒ NULL
    if ($val === '') {
      $sql = "UPDATE agremiados SET `$col` = NULL WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $id);
    } else {
      $sql = "UPDATE agremiados SET `$col` = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("si", $val, $id);
    }
    $stmt->execute();
    $stmt->close();
    json_ok();
  }

  if ($action === 'insert') {
    $nullIfEmpty = ($_POST['nullIfEmpty'] ?? '') === '1';
    $json = $_POST['json'] ?? '';
    if ($json === '') json_err("Faltan datos");

    $data = json_decode($json, true);
    if (!is_array($data)) json_err("JSON inválido");

    $colsAll = get_columns($conn);
    // no permitir setear id manual
    $colsAllowed = array_values(array_filter($colsAll, fn($c)=>$c!=='id'));

    $cols = [];
    $vals = [];
    $types = "";
    foreach ($data as $k=>$v) {
      if (!in_array($k, $colsAllowed, true)) continue;
      if ($nullIfEmpty && ($v==='' || $v===null)) {
        $cols[] = "`$k`";
        $vals[] = "NULL";
        continue;
      }
      $cols[] = "`$k`";
      $vals[] = "?";
      $types .= "s";
      $params[] = $v;
    }
    if (empty($cols)) json_err("Sin columnas válidas");

    $sql = "INSERT INTO agremiados (".implode(',', $cols).") VALUES (".implode(',', $vals).")";
    $stmt = $conn->prepare($sql);
    if (!empty($types)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();
    json_ok(['id'=>$newId]);
  }

  json_err("Acción no válida");
} catch (Throwable $e){
  json_err($e->getMessage());
}
