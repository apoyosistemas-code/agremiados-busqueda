<?php /* index.php */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Consulta de Agremiados</title>
  <link rel="stylesheet" href="style.css?v=3">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="home">

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

      <!-- Toggle de modos -->
      <div class="search-toggle">
        <button type="button" class="toggle-btn" data-mode="code">Por Colegiatura</button>
        <button type="button" class="toggle-btn active" data-mode="name">Por Apellidos y Nombres</button>
      </div>

      <!-- Formulario Colegiatura -->
      <form id="form-code" action="buscar.php" method="get" autocomplete="off" class="hero-form" style="display:none;">
        <input type="hidden" name="mode" value="code">
        <div class="hero-input">
          <span class="hero-input-prefix">N°</span>
          <input type="text" name="q" placeholder="N° de colegiatura (1–4 dígitos)" required inputmode="numeric" pattern="[0-9]{1,4}">
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
      </form>

      <!-- Formulario Nombres con Autocomplete -->
      <form id="form-name" action="buscar.php" method="get" autocomplete="off" class="hero-form">
        <input type="hidden" name="mode" value="name">
        <div class="hero-input ac-wrap">
          <span class="hero-input-prefix">👤</span>
          <input id="nameInput" type="text" name="name" placeholder="Apellidos y nombres (p. ej. VELITA ESPINOZA)" spellcheck="false" autocomplete="off">
          <div id="acList" class="ac-list" style="display:none;"></div>
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
      </form>

    </div>
  </main>

  <footer class="footer hero-footer">
    Colegio de Abogados de Junín © 2009–2025. Todos los derechos reservados.
  </footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Animación
  const hero = document.querySelector('.hero');
  if (hero) hero.classList.add('in');

  // Toggle
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
  setMode('name'); // arranca en nombres

  // ===== Autocomplete =====
  const nameInput = document.getElementById('nameInput');
  const acList = document.getElementById('acList');
  let acAbort = null;

  function escHtml(s){
    s = (s === undefined || s === null) ? '' : String(s);
    return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }
  function highlight(text, query){
    const parts = query.trim().split(/\s+/).filter(Boolean);
    let html = escHtml(text);
    parts.forEach(p=>{
      const re = new RegExp('('+p.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+')','ig');
      html = html.replace(re, '<span class="ac-mark">$1</span>');
    });
    return html;
  }
  function normalizeItem(it){
    if (typeof it === 'string') return { nombre: it, colegiatura: '', dni: '' };
    const nombre = it.nombre ?? it.NOMBRE ?? it.NOMBRE_DEL_AGREMIADO ?? '';
    const coleg  = it.colegiatura ?? it.COLEGIATURA ?? it.code ?? '';
    const dni    = it.dni ?? it.DNI ?? '';
    return { nombre, colegiatura: coleg, dni };
  }
  function showMsg(msg){
    acList.innerHTML = `<div class="ac-item"><div class="ac-sub">${escHtml(msg)}</div></div>`;
    acList.style.display = 'block';
  }
  function clearList(){ acList.innerHTML=''; acList.style.display='none'; }

  function renderList(items, q){
    if (!Array.isArray(items)) items = [];
    const rows = items.map(normalizeItem).filter(r => r.nombre);
    if (!rows.length) { showMsg('Sin resultados'); return; }

    acList.innerHTML = rows.map(it =>
      `<button type="button" class="ac-item" data-name="${escHtml(it.nombre)}">
         <div class="ac-title">${highlight(it.nombre, q)}</div>
         <div class="ac-sub">#${escHtml(it.colegiatura || '—')} · DNI ${escHtml(it.dni || '—')}</div>
       </button>`
    ).join('');
    acList.style.display = 'block';

    acList.querySelectorAll('.ac-item').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        nameInput.value = btn.dataset.name;
        clearList();
        document.getElementById('form-name').submit();
      });
    });
  }

  function fetchAC(q){
    if (acAbort) acAbort.abort();
    acAbort = new AbortController();
    showMsg('Buscando…');

    fetch('./api_sugerencias.php?q=' + encodeURIComponent(q), {
      signal: acAbort.signal,
      cache: 'no-store'
    })
      .then(async r => {
        const txt = await r.text();
        let data = [];
        try { data = JSON.parse(txt); }
        catch { console.warn('Autocomplete: respuesta no JSON, usando []', {txt}); data = []; }
        return data;
      })
      .then(data => renderList(data, q))
      .catch(err => {
        console.warn('Autocomplete error:', err);
        showMsg('Sin resultados');
      });
  }

  if (nameInput){
    nameInput.addEventListener('input', ()=>{
      const v = nameInput.value.trim();
      if (v.length < 2){ clearList(); return; }
      fetchAC(v);
    });
    nameInput.addEventListener('blur', ()=>setTimeout(clearList, 120));
    nameInput.addEventListener('keydown', e => {
      if (e.key === 'Escape') { clearList(); return; }
      if (e.key === 'Enter' && acList.style.display === 'block') {
        const first = acList.querySelector('.ac-item');
        if (first && first.dataset.name) {
          e.preventDefault();
          nameInput.value = first.dataset.name;
          clearList();
          document.getElementById('form-name').submit();
        }
      }
    });
  }
});
</script>

<style>
  .fab-container {
    position: fixed;
    right: 22px;
    bottom: 22px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 15px;
    align-items: flex-end;
  }
  .fab-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #ffffff;
    padding: 12px 20px 12px 16px; /* Ajuste de padding */
    border-radius: 50px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    text-decoration: none;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    font-size: 0.95rem;
  }
  .fab-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
  }
  
  /* Iconos */
  .fab-btn i {
    font-size: 20px;
    width: 24px;
    display: flex; justify-content: center;
  }

  /* Estilo Cumpleaños (Dorado) */
  .btn-bday { color: #d9a23e; } 
  .btn-bday:hover { color: #b7862f; border-color: #d9a23e; }

  /* Estilo Editor (Verde Corporativo) */
  .btn-editor { color: #12503a; }
  .btn-editor:hover { color: #0a2a1f; border-color: #12503a; }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="fab-container">
  
  <a href="cumpleanos.php" class="fab-btn btn-bday" title="Ver Cumpleaños">
    <i class="fa-solid fa-cake-candles"></i>
    <span>Lista de Cumpleaños</span>
  </a>

  <a href="editor.php" class="fab-btn btn-editor" title="Ir a Modo Editor">
    <i class="fa-solid fa-screwdriver-wrench"></i>
    <span>Modo Editor</span>
  </a>

</div>

</body>
</html>
