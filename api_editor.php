<?php
// api_editor.php - FINAL V13 (Optimizado y Seguro)
require_once "conexion.php";
require_once "auth.php"; // Candado de seguridad

// Cabeceras Anti-Caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header('Content-Type: application/json; charset=utf-8');

// Funciones Auxiliares
function json_ok($arr = []){ echo json_encode(['ok'=>true] + $arr); exit; }
function json_err($msg){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }

// Obtener columnas válidas (Whitelist de seguridad)
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

  // 1. OBTENER SIGUIENTE N° COLEGIATURA (Para nuevos registros)
  if ($action === 'get_next_col') {
      // Usamos MAX para encontrar el último número usado y sumar 1
      $res = $conn->query("SELECT MAX(CAST(COLEGIATURA AS UNSIGNED)) as max_col FROM agremiados");
      $row = $res->fetch_assoc();
      $next = ($row['max_col'] ?? 0) + 1;
      json_ok(['next' => $next]);
  }

  // 2. LISTAR (Buscador del Editor)
  if ($action === 'list') {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limitParam = $_GET['limit'] ?? 50; 
    
    // Lógica para "Ver Todos" o Paginado
    if ($limitParam === 'all') {
        $limit = 1000000; 
        $page = 1; 
    } else {
        $limit = intval($limitParam);
        if($limit < 1) $limit = 50;
    }

    $offset = ($page - 1) * $limit;
    $q = trim($_GET['q'] ?? '');

    $where = "1=1";
    $types = "";
    $params = [];

    // Filtros de búsqueda
    if ($q !== '') {
      $where .= " AND (COLEGIATURA LIKE ? OR DNI LIKE ? OR NOMBRE_DEL_AGREMIADO LIKE ?)";
      $wild = "%$q%";
      $types = "sss";
      $params = [$wild, $wild, $wild];
    }

    // Contar total
    $sqlCount = "SELECT COUNT(*) as total FROM agremiados WHERE $where";
    $stmt = $conn->prepare($sqlCount);
    if($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    // Obtener datos
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
      'pages' => ($limitParam === 'all') ? 1 : ceil($total / $limit)
    ]);
  }

  // 3. GUARDAR (INSERTAR / ACTUALIZAR)
  if ($action === 'insert' || $action === 'update') {
      $json = $_POST['json'] ?? '{}';
      $data = json_decode($json, true);
      if (!is_array($data)) json_err("Datos inválidos");

      $nullIfEmpty = ($_POST['nullIfEmpty'] ?? '') === '1';
      $validCols = get_columns($conn); 

      $colNames = [];
      $placeholders = [];
      $colsUpdate = [];
      $bindParams = [];
      $types = "";
      $camposLog = []; 

      foreach ($data as $k => $v) {
          if (!in_array($k, $validCols, true)) continue; 
          if ($k === 'id') continue; 

          $val = $v;
          if ($nullIfEmpty && trim((string)$v) === '') $val = null;

          // Arrays para Insert
          $colNames[] = "`$k`";
          $placeholders[] = "?";
          
          // Arrays para Update
          $colsUpdate[] = "`$k` = ?";
          
          $bindParams[] = $val;
          $types .= "s";
          $camposLog[] = $k;
      }

      // --- INSERT CON ID FORZADO ---
      if ($action === 'insert') {
          // Si envían COLEGIATURA, forzamos que el ID sea igual para mantener orden
          if (isset($data['COLEGIATURA']) && is_numeric($data['COLEGIATURA'])) {
              $colNames[] = "`id`"; 
              $placeholders[] = "?";
              $bindParams[] = $data['COLEGIATURA']; 
              $types .= "i";
          }
          $sql = "INSERT INTO agremiados (" . implode(',', $colNames) . ") VALUES (" . implode(',', $placeholders) . ")";
      
      } else {
          // UPDATE
          $id = intval($data['id'] ?? 0);
          if ($id <= 0) json_err("Falta ID");
          
          $sql = "UPDATE agremiados SET " . implode(', ', $colsUpdate) . " WHERE id = ?";
          $bindParams[] = $id;
          $types .= "i";
      }

      $stmt = $conn->prepare($sql);
      if(!$stmt) json_err("Error SQL: " . $conn->error);
      
      $stmt->bind_param($types, ...$bindParams);
      
      if ($stmt->execute()) {
          // --- REGISTRO DE AUDITORÍA ---
          $logId = ($action === 'insert' && isset($data['COLEGIATURA'])) ? $data['COLEGIATURA'] : (($action==='insert')?$stmt->insert_id:$id);
          
          $listaCampos = implode(', ', $camposLog);
          $msg = ($action === 'insert') 
              ? "Nuevo Agremiado (ID=$logId). Datos iniciales: $listaCampos" 
              : "Editó Agremiado (ID=$logId). Campos modificados: $listaCampos";
          
          registrar_auditoria($conn, strtoupper($action), $msg);
          // ----------------------------

          json_ok(['msg' => 'Guardado correctamente']);
      } else {
          if ($conn->errno == 1062) json_err("Error: El N° de Colegiatura o ID ya existe.");
          else json_err("Error BD: " . $stmt->error);
      }
      $stmt->close();
  }

} catch (Throwable $e) {
  json_err($e->getMessage());
}
?>?>