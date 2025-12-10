<?php
// api_editor.php - FINAL V7 (Soporte para Límites variables y Ver Todos)
require_once "conexion.php";

// Anti-Caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json; charset=utf-8');

function json_ok($arr = []){ echo json_encode(['ok'=>true] + $arr); exit; }
function json_err($msg){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }

function get_columns(mysqli $conn){
  static $cols = null;
  if ($cols !== null) return $cols;
  $cols = [];
  $res = $conn->query("DESCRIBE agremiados");
  while ($row = $res->fetch_assoc()){ $cols[] = $row['Field']; }
  return $cols;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
  // --- LISTAR ---
  if ($action === 'list') {
    $page = max(1, intval($_GET['page'] ?? 1));
    
    // LÓGICA DE LÍMITE (Paginación)
    $limitParam = $_GET['limit'] ?? 50; // Por defecto 50
    
    if ($limitParam === 'all') {
        $limit = 1000000; // Un número muy alto para traer "todo"
        $page = 1; // Si es todo, solo hay 1 página
    } else {
        $limit = intval($limitParam);
        if($limit < 1) $limit = 50;
    }

    $offset = ($page - 1) * $limit;
    $q = trim($_GET['q'] ?? '');

    $where = "1=1";
    $types = "";
    $params = [];

    if ($q !== '') {
      $where .= " AND (COLEGIATURA LIKE ? OR DNI LIKE ? OR NOMBRE_DEL_AGREMIADO LIKE ?)";
      $wild = "%$q%";
      $types = "sss";
      $params = [$wild, $wild, $wild];
    }

    // 1. Contar total
    $sqlCount = "SELECT COUNT(*) as total FROM agremiados WHERE $where";
    $stmt = $conn->prepare($sqlCount);
    if($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    // 2. Traer datos
    $sqlData = "SELECT * FROM agremiados WHERE $where ORDER BY (COLEGIATURA+0) ASC LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sqlData);
    if($types) {
        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param("ii", $limit, $offset);
    }
    
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    json_ok([
      'rows' => $rows,
      'total' => intval($total),
      'page' => $page,
      'limit' => $limitParam, // Devolvemos el límite usado para que el front sepa
      'pages' => ($limitParam === 'all') ? 1 : ceil($total / $limit)
    ]);
  }

  // --- GUARDAR / EDITAR ---
  if ($action === 'insert' || $action === 'update') {
      $json = $_POST['json'] ?? '{}';
      $data = json_decode($json, true);
      if (!is_array($data)) json_err("Datos inválidos");

      $nullIfEmpty = ($_POST['nullIfEmpty'] ?? '') === '1';
      $validCols = get_columns($conn); 

      $colsDb = [];
      $bindParams = [];
      $types = "";

      foreach ($data as $k => $v) {
          if (!in_array($k, $validCols, true)) continue; 
          if ($k === 'id') continue; 

          $colsDb[] = "`$k` = ?";
          
          if ($nullIfEmpty && trim((string)$v) === '') {
             $bindParams[] = null;
             $types .= "s";
          } else {
             $bindParams[] = $v;
             $types .= "s";
          }
      }

      if ($action === 'insert') {
          $colNames = []; $placeholders = [];
          foreach($data as $k=>$v){
             if(!in_array($k, $validCols) || $k==='id') continue;
             $colNames[] = "`$k`";
             $placeholders[] = "?";
          }
          $sql = "INSERT INTO agremiados (" . implode(',', $colNames) . ") VALUES (" . implode(',', $placeholders) . ")";
      } else {
          $id = intval($data['id'] ?? 0);
          if ($id <= 0) json_err("Falta ID");
          $sql = "UPDATE agremiados SET " . implode(', ', $colsDb) . " WHERE id = ?";
          $bindParams[] = $id;
          $types .= "i";
      }

      $stmt = $conn->prepare($sql);
      if(!$stmt) json_err("Error SQL: " . $conn->error);
      $stmt->bind_param($types, ...$bindParams);
      
// --- EJECUTAR CONSULTA ---
      if ($stmt->execute()) {
          
          // --- AUDITORÍA MEJORADA ---
          $affectedId = ($action === 'insert') ? $stmt->insert_id : $id;
          
          // 1. Obtenemos qué campos se tocaron
          $camposTocados = array_keys($data);
          // Filtramos campos irrelevantes o el ID
          $camposTocados = array_filter($camposTocados, fn($c) => $c !== 'id');
          // Creamos string legible: "DNI, NOMBRE, CORREO"
          $listaCampos = implode(', ', $camposTocados);
          
          $detalleLog = "";
          if ($action === 'insert') {
              $detalleLog = "Registró nuevo agremiado (ID: $affectedId). Campos: $listaCampos";
          } else {
              $detalleLog = "Actualizó agremiado (ID: $affectedId). Campos modificados: $listaCampos";
          }
          
          registrar_auditoria($conn, strtoupper($action), $detalleLog);
          // ---------------------------

          json_ok(['msg' => 'Guardado correctamente']);
      } else {
          json_err("Error BD: " . $stmt->error);
      }
      $stmt->close();
  }
} catch (Throwable $e) {
  json_err($e->getMessage());
}
?>