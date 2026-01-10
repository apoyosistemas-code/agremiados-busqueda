<?php 
require_once "conexion.php";
require_once "auth.php"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Consulta de Agremiados</title>
  <link rel="icon" type="image/png" href="assets/EstrellaCaj.png">
  
  <link rel="stylesheet" href="style.css?v=100">
  
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="home">

  <div class="user-bar">
      <div class="user-badge" title="<?= htmlspecialchars($_SESSION['user_full'] ?? 'Usuario') ?>">
          <i class="fa-solid fa-user-circle"></i> 
          <span style="margin-left: 8px;">
              <?= htmlspecialchars($_SESSION['user_full'] ?? 'Admin') ?>
          </span>
      </div>
      <a href="logout.php" class="btn-logout" title="Cerrar Sesión">
          <i class="fa-solid fa-power-off"></i> 
          <span style="margin-left: 8px;">Salir</span>
      </a>
  </div>

  <main class="hero-wrap">
    
    <div class="hero">
      <?php if (file_exists('assets/logo.png')): ?>
          <img class="hero-logo" src="assets/logo.png" alt="Logo Colegio">
      <?php endif; ?>

      <h1 class="hero-title">Sistema de Consulta de Agremiados</h1>
      <p class="hero-subtitle">Seleccione el tipo de búsqueda e ingrese los datos.</p>

      <form id="searchForm" action="buscar.php" method="get" class="hero-form-single" autocomplete="off" onsubmit="return validateSearch();">
        
        <div class="search-mode">
            <button type="button" class="mode-btn active" id="btn-mode-name" onclick="setMode('name')">
                <i class="fa-solid fa-user"></i> Apellidos y Nombres
            </button>
            <button type="button" class="mode-btn" id="btn-mode-code" onclick="setMode('code')">
                <i class="fa-solid fa-hashtag"></i> N° Colegiatura
            </button>
        </div>

        <div class="hero-input-group">
            <i class="fa-solid fa-user input-icon-left" id="search-icon"></i>
            <input type="text" id="searchInput" name="name" class="hero-input-custom" placeholder="Ingrese apellidos y nombres..." autocomplete="off">
            <button type="submit" class="btn-search-icon" title="Buscar"><i class="fa-solid fa-magnifying-glass"></i></button>
            
            <div id="acList" class="ac-list"></div>
        </div>
      </form>
    </div>

    <div class="fab-container">
        <a href="cumpleanos.php" class="fab-btn btn-bday" title="Cumpleaños">
            <i class="fa-solid fa-cake-candles"></i> <span>Cumpleaños</span>
        </a>
        <a href="editor.php" class="fab-btn btn-editor" title="Editor">
            <i class="fa-solid fa-screwdriver-wrench"></i> <span>Editor</span>
        </a>
        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'MASTER'): ?>
            <a href="usuarios.php" class="fab-btn btn-users" title="Usuarios">
                <i class="fa-solid fa-users-gear"></i> <span>Usuarios</span>
            </a>
            <a href="auditoria.php" class="fab-btn btn-audit" title="Auditoría">
                <i class="fa-solid fa-shield-halved"></i> <span>Auditoría</span>
            </a>
        <?php endif; ?>
    </div>

  </main>

  <footer class="footer hero-footer">
      <div>Colegio de Abogados de Junín © 2009–2025. Todos los derechos reservados.</div>
      <div class="eku-logo-container">
          <img src="assets/logo_eku.png" alt="Powered by EKU BYTE" class="eku-logo">
      </div>
  </footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Animaciones
  const hero = document.querySelector('.hero');
  if (hero) setTimeout(() => hero.classList.add('in'), 100);
  const fabs = document.querySelector('.fab-container');
  if (fabs) setTimeout(() => fabs.classList.add('in'), 400);

  // Variables
  const input = document.getElementById('searchInput');
  const icon = document.getElementById('search-icon');
  const btnName = document.getElementById('btn-mode-name');
  const btnCode = document.getElementById('btn-mode-code');
  const form = document.getElementById('searchForm');
  const acList = document.getElementById('acList');
  let currentMode = 'name';
  let acAbort = null;

  window.validateSearch = function() {
      if(input.value.trim().length === 0) { input.focus(); return false; }
      return true;
  };

  window.setMode = function(mode) {
      currentMode = mode;
      if(mode === 'code') {
          btnCode.classList.add('active'); btnName.classList.remove('active');
          input.placeholder = 'Ingrese número (1-4 dígitos)...';
          input.name = 'q'; input.type = 'number'; input.value = '';
          icon.className = 'fa-solid fa-hashtag input-icon-left';
          setHiddenMode('code');
          clearList(); 
      } else {
          btnName.classList.add('active'); btnCode.classList.remove('active');
          input.placeholder = 'Ingrese apellidos y nombres...';
          input.name = 'name'; input.type = 'text'; input.value = '';
          icon.className = 'fa-solid fa-user input-icon-left';
          setHiddenMode('name');
      }
      input.focus();
  };

  function setHiddenMode(val){
      let hidden = form.querySelector('input[name="mode"]');
      if(!hidden) {
          hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.name = 'mode';
          form.appendChild(hidden);
      }
      hidden.value = val;
  }

  function fetchAC(q){
    if (acAbort) acAbort.abort();
    acAbort = new AbortController();
    fetch('api_sugerencias.php?q=' + encodeURIComponent(q), { signal: acAbort.signal, cache: 'no-store' })
      .then(r => r.json()).then(data => renderList(data, q))
      .catch(err => { if(err.name !== 'AbortError') clearList(); });
  }

  // --- SOLUCIÓN AL UNDEFINED: Probamos varias formas de llamar al campo ---
  function renderList(items, q){
    if (!Array.isArray(items) || items.length === 0) { clearList(); return; }
    const html = items.map(it => {
        // Fallback de nombres para evitar undefined
        const nombre = it.NOMBRE_DEL_AGREMIADO || it.nombre || it.NOMBRE || 'Sin nombre';
        const col = it.COLEGIATURA || it.colegiatura || it.col || it.code || '—';
        const dni = it.DNI || it.dni || '—';
        
        const safeQ = q.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${safeQ.split(' ').join('|')})`, 'gi');
        const nombreHigh = nombre.replace(regex, '<span class="ac-mark">$1</span>');
        const safeName = nombre.replace(/'/g, "\\'");
        
        return `<div class="ac-item" onclick="selectItem('${safeName}')">
                  <div class="ac-title">${nombreHigh}</div>
                  <div class="ac-sub">#${col} · DNI ${dni}</div>
                </div>`;
    }).join('');
    acList.innerHTML = html; acList.style.display = 'block';
  }

  window.selectItem = function(val) { input.value = val; clearList(); form.submit(); };
  function clearList(){ acList.innerHTML = ''; acList.style.display = 'none'; }

  input.addEventListener('input', () => {
      const val = input.value.trim();
      if(currentMode === 'name' && val.length >= 2) fetchAC(val); else clearList();
  });
  document.addEventListener('click', (e) => { if (!form.contains(e.target)) clearList(); });
  input.addEventListener('keydown', (e) => { if(e.key === 'Escape') clearList(); });

  setMode('name');
});
</script>
</body>
</html>