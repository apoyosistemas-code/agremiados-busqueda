<?php
// 1. SEGURIDAD: Bloqueo de acceso directo
require_once "conexion.php";
require_once "auth.php"; 

/* ========= Entrada ========= */
$mode   = $_GET['mode'] ?? 'code';          // 'code' (colegiatura) | 'name' (apellidos y nombres)
$q      = trim($_GET['q'] ?? '');
$nameQ  = trim($_GET['name'] ?? '');
$page   = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

/* ========= Construcción del WHERE y conteo ========= */
$where  = '1=1';
$params = [];
$types  = '';

if ($mode === 'name') {
  // Búsqueda por apellidos y nombres con FULLTEXT
  $qname = $nameQ !== '' ? $nameQ : $q;
  $qname = trim($qname);
  if ($qname !== '') {
    // Prepara para modo booleano: +palabra1 +palabra2...
    $tokens = preg_split('/\s+/', $qname, -1, PREG_SPLIT_NO_EMPTY);
    $search_str = '';
    foreach ($tokens as $t) {
      $search_str .= "+$t ";
    }
    $search_str = trim($search_str);

    if ($search_str) {
      $where = "MATCH(NOMBRE_DEL_AGREMIADO) AGAINST(? IN BOOLEAN MODE)";
      $params[] = $search_str;
      $types .= 's';
    }
  }
} else {
  // Búsqueda por colegiatura (default)
  $code = $q !== '' ? $q : $nameQ;
  $code = trim($code);
  if ($code !== '') {
    // LIKE por comienzo de string, más eficiente con índice
    $where = "CAST(COLEGIATURA AS CHAR) LIKE CONCAT(?, '%')";
    $params[] = $code;
    $types    .= 's';
  }
}

/* Conteo total */
$sqlCount = "SELECT COUNT(*) AS c FROM agremiados WHERE $where";
$stmt = $conn->prepare($sqlCount);

if ($types !== '') {
  // bind_param por referencias
  $bind = array_merge([$types], $params);
  foreach ($bind as $k => $v) $bind[$k] = &$bind[$k];
  call_user_func_array([$stmt, 'bind_param'], $bind);
}
$stmt->execute();
$res = $stmt->get_result();
$total = (int)($res->fetch_assoc()['c'] ?? 0);
$stmt->close();

$totalPages = max(1, (int)ceil($total / $perPage));

/* ========= Traer filas ========= */
$sqlData = "
  SELECT *
  FROM agremiados
  WHERE $where
  ORDER BY COLEGIATURA+0 ASC
  LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sqlData);

// Agregamos paginación al final
$typesWithPage = $types . 'ii';
$paramsWithPage = $params;
$paramsWithPage[] = $perPage;
$paramsWithPage[] = $offset;

$bind = array_merge([$typesWithPage], $paramsWithPage);
foreach ($bind as $k => $v) $bind[$k] = &$bind[$k];
call_user_func_array([$stmt, 'bind_param'], $bind);

$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ========= Helpers ========= */
function has_link($v){
  $v = trim((string)$v);
  if ($v === "" || strtoupper($v) === "NULL" || $v === "0") return false;
  // permitir http(s) y enlaces de drive sin protocolo (raro, pero por si acaso)
  return (stripos($v, "http://") === 0 || stripos($v, "https://") === 0);
}
function estado_badge_class($e){
  $e = strtoupper(trim((string)$e));
  if (in_array($e, ["VIVO","HABILITADO","ACTIVO"])) return "ok";
  if (in_array($e, ["FALLECIDO","INHABILITADO","NO HABILITADO"])) return "danger";
  return "warn";
}

/* Campos a excluir del bloque de detalles (ya se muestran en cabecera o como botones) */
$exclude_keys = [
  'ID',
  'ESTADO',
  'COLEGIATURA',
  'NOMBRE_DEL_AGREMIADO',
  'FOTO_DE_RECIBO',
  'FOTO_DE_FICHA_PERSONAL'
];
$pretty_labels = [
  'DNI' => 'DNI',
  'N_MERO_DE_CELULAR' => 'Celular',
  'TEL_FONO_FIJO' => 'Fijo',
  'CORREO_GMAIL' => 'Correo (Gmail)',
  'CORREO' => 'Correo',
  'FACEBOOK' => 'Facebook',
  'FECHA_CUMPLEA_OS' => 'Cumpleaños',
  'CENTRO_DE_TRABAJO' => 'Centro de trabajo',
  'DOMICILIO_REAL' => 'Domicilio real',
  'DOMICILIO_PROCESAL' => 'Domicilio procesal',
  'FECHA_DE_INCORPORACI_N' => 'Fecha de incorporación',
  'SEGURO_DE_SALUD' => 'Seguro de salud',
  'CASILLA_ELECTR_NICA' => 'Casilla electrónica',
  'NOMBRES_DE_HIJOS' => 'Hijos',
  'C_NYUGE_O_CONVIVIENTE' => 'Cónyuge/Conviviente',
  'OBSERVACION' => 'Observación',
];

function labelize($key, $pretty){
  if (isset($pretty[$key])) return $pretty[$key];
  $l = preg_replace('/_+/', ' ', $key);
  $l = str_replace(
    ['N MERO','ELECTR NICA','INCORPORACI N','C NYUGE'],
    ['NÚMERO','ELECTRÓNICA','INCORPORACIÓN','CÓNYUGE'],
    $l
  );
  $l = ucwords(mb_strtolower($l,'UTF-8'));
  return $l;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resultados · Sistema de Consulta de Agremiados</title>
  <link rel="stylesheet" href="style.css?v=4">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    /* Estilos para los botones naranjas modernos */
    .btn-action {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        background-color: #E79E1E; /* Naranja corporativo */
        color: white; padding: 10px 15px; border-radius: 8px;
        text-decoration: none; font-weight: 500; font-size: 0.9rem;
        transition: background 0.2s; border: none; flex: 1; /* Ocupar espacio igual */
        white-space: nowrap;
    }
    .btn-action:hover { background-color: #cf8d1a; color: white; }
    
    .btn-disabled-modern {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        background-color: #f3f4f6; color: #9ca3af; padding: 10px 15px;
        border-radius: 8px; font-weight: 500; font-size: 0.9rem;
        border: 1px solid #e5e7eb; flex: 1; cursor: not-allowed;
        white-space: nowrap;
    }
    
    /* Contenedor de acciones: LADO A LADO */
    .actions2 { 
        display: flex; 
        gap: 10px; 
        margin-top: 15px; 
        flex-direction: row; /* Fuerza horizontal */
    }
    
    /* Responsivo: en celulares muy pequeños, apilar si no caben */
    @media (max-width: 480px) {
        .actions2 { flex-direction: column; }
    }
  </style>
</head>
<body class="home results-compact">
  <main class="hero-wrap">
    <div class="hero">
      <?php
        $logo = 'assets/logo.png';
        if (file_exists($logo)) {
          echo '<img class="hero-logo" src="'.$logo.'" alt="Ilustre Colegio de Abogados de Junín" />';
        }
      ?>
      <h1 class="hero-title">Resultados</h1>
      <p class="hero-subtitle">
        Criterio:
        <b><?= htmlspecialchars($mode==='name' ? ($nameQ !== '' ? $nameQ : $q) : $q) ?></b>
        · <?= intval($total) ?> coincidencia(s)
      </p>
      <a href="index.php" class="btn btn-primary">← Nueva consulta</a>
    </div>
  </main>

  <main class="wrap">
    <section class="results">
      <?php if (empty($rows)): ?>
        <div class="empty-box">
          <h3>😕 No se encontraron datos</h3>
          <p>Verifique su criterio de búsqueda.</p>
        </div>
      <?php else: ?>
        <div class="cards-grid">
          <?php foreach ($rows as $r):
            $estado = $r['ESTADO'] ?? '';
            $badge  = estado_badge_class($estado);
            $coleg  = $r['COLEGIATURA'] ?? '';
            $nombre = $r['NOMBRE_DEL_AGREMIADO'] ?? '';
            $recibo = $r['FOTO_DE_RECIBO'] ?? '';
            $ficha  = $r['FOTO_DE_FICHA_PERSONAL'] ?? '';
          ?>
          <article class="card2">
            <div class="card2-header">
              <span class="badge <?= $badge ?>"><?= htmlspecialchars($estado ?: '—') ?></span>
              <h2>#<?= htmlspecialchars($coleg) ?></h2>
              <p class="name"><?= htmlspecialchars($nombre) ?></p>
            </div>

            <div class="details">
              <?php
              foreach ($r as $key => $value) {
                if (in_array($key, $exclude_keys, true)) continue;
                if ($value === null) continue;
                $trimmed = trim((string)$value);
                if ($trimmed === '') continue;

                $label = labelize($key, $pretty_labels);
                echo '<div class="row"><label>'.htmlspecialchars($label).'</label><span>'.htmlspecialchars($trimmed).'</span></div>';
              }
              ?>
            </div>

            <div class="actions2">
              <?php if (has_link($recibo)): ?>
                <a href="<?= htmlspecialchars($recibo) ?>" target="_blank" class="btn-action">
                    <i class="fa-solid fa-file-invoice"></i> Foto de Recibo
                </a>
              <?php else: ?>
                <div class="btn-disabled-modern">
                    <i class="fa-solid fa-file-invoice"></i> Sin Recibo
                </div>
              <?php endif; ?>

              <?php if (has_link($ficha)): ?>
                <a href="<?= htmlspecialchars($ficha) ?>" target="_blank" class="btn-action">
                    <i class="fa-solid fa-id-card"></i> Ficha Personal
                </a>
              <?php else: ?>
                <div class="btn-disabled-modern">
                    <i class="fa-solid fa-id-card"></i> Sin Ficha
                </div>
              <?php endif; ?>
            </div>
          </article>
          <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
          <div class="pager" style="margin-top:22px;">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
              <a class="page <?= $p === $page ? 'active' : '' ?>"
                 href="?mode=<?= urlencode($mode) ?>&q=<?= urlencode($q) ?>&name=<?= urlencode($nameQ) ?>&page=<?= $p ?>"><?= $p ?></a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </main>

  <footer class="footer hero-footer">
    Colegio de Abogados de Junín © 2009–2025. Todos los derechos reservados.
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const hero = document.querySelector('.hero');
      if (hero) hero.classList.add('in');
      const cards = document.querySelectorAll('.card2');
      cards.forEach((el, i) => {
        el.style.transitionDelay = (i * 80) + 'ms';
        requestAnimationFrame(() => el.classList.add('in'));
      });
    });
  </script>
</body>
</html>