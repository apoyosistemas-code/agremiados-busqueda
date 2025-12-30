<?php 
// 1. SEGURIDAD: Bloqueamos el acceso público
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
  <link rel="stylesheet" href="style.css?v=3">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
      /* --- ESTILOS GENERALES Y ESCRITORIO --- */
      .user-bar {
          position: absolute; top: 20px; right: 20px; z-index: 1000;
          display: flex; gap: 10px; align-items: center;
      }
      .user-badge {
          background: rgba(255,255,255,0.95); padding: 8px 15px; border-radius: 30px;
          font-size: 0.85rem; font-weight: 600; color: #12503a;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 1px solid #e1e1e1;
      }
      .btn-logout {
          background: #fee2e2; color: #991b1b; padding: 8px 15px; 
          border-radius: 30px; text-decoration: none; font-size: 0.85rem; font-weight: 600;
          border: 1px solid #fca5a5; transition: 0.3s;
          box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      }
      .btn-logout:hover { background: #991b1b; color: white; }

      /* Botones Flotantes (Versión PC) */
      .fab-container {
        position: fixed; right: 22px; bottom: 22px; z-index: 9999;
        display: flex; flex-direction: column; gap: 15px; align-items: flex-end;
      }
      .fab-btn {
        display: flex; align-items: center; gap: 12px;
        background: #ffffff; padding: 12px 20px 12px 16px;
        border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        text-decoration: none; font-weight: 600; font-family: 'Poppins', sans-serif;
        transition: all 0.3s ease; border: 2px solid transparent; font-size: 0.95rem;
        min-width: 180px;
      }
      .fab-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.2); }
      .fab-btn i { font-size: 20px; width: 24px; display: flex; justify-content: center; }

      .btn-bday { color: #d9a23e; } .btn-bday:hover { color: #b7862f; border-color: #d9a23e; }
      .btn-editor { color: #12503a; } .btn-editor:hover { color: #0a2a1f; border-color: #12503a; }
      .btn-users { color: #d63384; } .btn-users:hover { color: #a61e61; border-color: #d63384; }
      .btn-audit { color: #6610f2; } .btn-audit:hover { color: #520dc2; border-color: #6610f2; }

      /* --- VERSIÓN CELULAR (CORRECCIÓN ANTI-PANTALLA BLANCA) --- */
      @media (max-width: 768px) {
          /* Barra fija abajo */
          .fab-container {
              flex-direction: row !important;       
              right: 0 !important; 
              left: 0 !important; 
              bottom: 0 !important;
              
              /* ESTAS LÍNEAS EVITAN LA PANTALLA BLANCA: */
              top: auto !important;          /* No te pegues arriba */
              height: auto !important;       /* No crezcas al infinito */
              max-height: 80px !important;   /* Límite estricto de altura */
              
              width: 100% !important;
              background: #ffffff !important;       
              padding: 5px 0 !important;
              padding-bottom: env(safe-area-inset-bottom, 5px) !important;
              justify-content: space-around !important;
              border-top: 1px solid #e1e1e1;
              box-shadow: 0 -4px 20px rgba(0,0,0,0.1); 
              gap: 0 !important;
          }
          /* Botones centrados */
          .fab-btn {
              min-width: auto !important; width: auto !important;
              flex-grow: 1 !important; flex-direction: column !important;    
              justify-content: center !important; align-items: center !important;
              text-align: center !important;
              padding: 4px 0 !important;
              border-radius: 0 !important; box-shadow: none !important; 
              font-size: 0.65rem !important; line-height: 1.2 !important;
              gap: 5px !important;
              background: transparent !important; border: none !important;
              margin: 0 !important; height: auto !important;
          }
          /* Iconos visibles */
          .fab-btn i {
              font-size: 1.4rem !important; margin-bottom: 0 !important;
              display: block !important; width: auto !important;
          }
          .fab-btn:active { background-color: #f2f2f2 !important; opacity: 0.7; }
          
          /* Espacio para que se vea el footer detrás de la barra */
          body { padding-bottom: 110px !important; }
      }
  </style>
</head>
<body class="home">

  <div class="user-bar">
      <div class="user-badge">
          <i class="fa-solid fa-user-circle me-2"></i> 
          <?= htmlspecialchars($_SESSION['user_full'] ?? 'Admin') ?>
          <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'MASTER') echo ' <span style="color:#d9a23e">★</span>'; ?>
      </div>
      <a href="logout.php" class="btn-logout"><i class="fa-solid fa-power-off"></i> Salir</a>
  </div>

  <main class="hero-wrap">
    <div class="hero">
      <?php
        $logo = 'assets/logo.png';
        if (file_exists($logo)) {
          echo '<img class="hero-logo" src="'.$logo.'" alt="Ilustre Colegio de Abogados de Junín" />';
        }
      ?>

      <h1 class="hero-title">Sistema de Consulta de Agremiados</h1>
      <p class="hero-subtitle">Elija el modo de búsqueda y escriba su consulta.</p>

      <div class="search-toggle">
        <button type="button" class="toggle-btn" data-mode="code">Por Colegiatura</button>
        <button type="button" class="toggle-btn active" data-mode="name">Por Apellidos y Nombres</button>
      </div>

      <form id="form-code" action="buscar.php" method="get" autocomplete="off" class="hero-form" style="display:none;">
        <input type="hidden" name="mode" value="code">
        <div class="hero-input">
          <span class="hero-input-prefix">N°</span>
          <input type="text" name="q" placeholder="N° de colegiatura" required inputmode="numeric" pattern="[0-9]{1,4}">
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
      </form>

      <form id="form-name" action="buscar.php" method="get" autocomplete="off" class="hero-form">
        <input type="hidden" name="mode" value="name">
        <div class="hero-input ac-wrap">
          <span class="hero-input-prefix">👤</span>
          <input id="nameInput" type="text" name="name" placeholder="Apellidos y nombres..." spellcheck="false" autocomplete="off">
          <div id="acList" class="ac-list" style="display:none;"></div>
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
      </form>

      
    </div>
  </main>

<div class="fab-container">
    <a href="cumpleanos.php" class="fab-btn btn-bday">
        <i class="fa-solid fa-cake-candles"></i>
        <span>Cumpleaños</span>
    </a>
    <a href="editor.php" class="fab-btn btn-editor">
        <i class="fa-solid fa-screwdriver-wrench"></i>
        <span>Editor</span>
    </a>
    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'MASTER'): ?>
        <a href="usuarios.php" class="fab-btn btn-users">
            <i class="fa-solid fa-users-gear"></i>
            <span>Usuarios</span>
        </a>
        <a href="auditoria.php" class="fab-btn btn-audit">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Auditoría</span>
        </a>
    <?php endif; ?>
</div>

<footer class="footer hero-footer">
    <div>Colegio de Abogados de Junín © 2009–2025. Todos los derechos reservados.</div>
    
    <div class="eku-logo-container">
        <img src="assets/logo_eku.png" alt="Powered by EKU BYTE" class="eku-logo">
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const hero = document.querySelector('.hero');
  if (hero) hero.classList.add('in'); // Activa la animación

  const btns = document.querySelectorAll('.toggle-btn');
  const formCode = document.getElementById('form-code');
  const formName = document.getElementById('form-name');

  function setMode(mode){
    btns.forEach(x => x.classList.remove('active'));
    if (mode === 'code') {
      btns[0].classList.add('active');
      formCode.style.display = '';
      formName.style.display = 'none';
      formCode.querySelector('input[name="q"]').focus();
    } else {
      btns[1].classList.add('active');
      formCode.style.display = 'none';
      formName.style.display = '';
      document.getElementById('nameInput').focus();
    }
  }
  btns[0].addEventListener('click', () => setMode('code'));
  btns[1].addEventListener('click', () => setMode('name'));
  setMode('name'); 

  // Autocomplete Básico
  const nameInput = document.getElementById('nameInput');
  const acList = document.getElementById('acList');
  let acAbort = null;

  function renderList(items, q){
    if (!Array.isArray(items) || items.length === 0) { 
        acList.innerHTML = '<div class="ac-item"><div class="ac-sub">Sin resultados</div></div>';
        acList.style.display = 'block'; 
        return; 
    }
    acList.innerHTML = items.map(it => 
      `<button type="button" class="ac-item" onclick="selectItem('${it.nombre}')">
         <div class="ac-title">${it.nombre}</div>
         <div class="ac-sub">#${it.colegiatura || '—'} · DNI ${it.dni || '—'}</div>
       </button>`
    ).join('');
    acList.style.display = 'block';
  }

  window.selectItem = function(name) {
      nameInput.value = name;
      acList.style.display='none';
      document.getElementById('form-name').submit();
  };

  if (nameInput){
    nameInput.addEventListener('input', ()=>{
      const v = nameInput.value.trim();
      if (v.length < 2){ acList.style.display='none'; return; }
      
      if (acAbort) acAbort.abort();
      acAbort = new AbortController();

      fetch('api_sugerencias.php?q=' + encodeURIComponent(v), { signal: acAbort.signal })
      .then(r => r.json())
      .then(data => renderList(data, v))
      .catch(e => {});
    });
    // Retraso para que el click en el botón funcione antes de cerrar
    nameInput.addEventListener('blur', ()=>setTimeout(() => acList.style.display='none', 200));
  }
});
</script>

</body>
</html>