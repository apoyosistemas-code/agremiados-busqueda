<?php 
require_once "conexion.php";
require_once "auth.php"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cumpleaños · ICAJ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/EstrellaCaj.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root {
        --color-verde: #12503a; 
        --color-naranja: #E79E1E;
        --color-fondo: #e5e7eb;
        --color-texto: #333333;
    }
    body { 
        font-family: 'Poppins', sans-serif; 
        background-color: var(--color-fondo);
        color: var(--color-texto);
        padding-top: 110px;
    }
    .navbar-custom { 
        background-color: #ffffff; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        height: 90px;
    }
    .navbar-brand {
        font-weight: 600; color: var(--color-verde); font-size: 1.3rem;
        display: flex; align-items: center; gap: 15px;
    }
    .navbar-brand img { height: 70px; width: auto; }

    .card-custom { 
        border: none; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); 
        background: white; margin-bottom: 20px;
    }
    
    .btn-verde { background-color: var(--color-verde); color: white; border:none; border-radius:6px; padding: 8px 16px; transition:0.2s; }
    .btn-verde:hover { background-color: #0e3f2d; color: white; }
    
    .btn-naranja { background-color: var(--color-naranja); color: white; border:none; border-radius:6px; padding: 8px 16px;}
    .btn-naranja:hover { background-color: #d38b13; color: white; }
    .btn-naranja:active { background-color: #b7862f; }

    .lista-container {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 20px;
        background: #fafafa;
        max-height: 60vh;
        overflow-y: auto;
    }
    .cumple-item {
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
        display: flex; justify-content: space-between; align-items: center;
        font-size: 1rem;
    }
    .cumple-item:last-child { border-bottom: none; }
    
    .dia-separator {
        background-color: #f1f3f5;
        padding: 8px 15px;
        border-radius: 6px;
        margin-top: 20px;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--color-verde);
        font-size: 1rem;
        border-left: 4px solid var(--color-verde);
    }
    #loading { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; display: none; align-items: center; justify-content: center; }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container-fluid px-4 animate-container">
      <a class="navbar-brand" href="index.php">
        <img src="assets/logo.png" alt="Logo">
        <span>Lista de Cumpleaños</span>
      </a>
      <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
      </div>
    </div>
  </nav>

  <div class="container-fluid px-4 animate-container">
    
    <div class="card card-custom p-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-4"> 
                <label class="form-label text-muted small fw-bold">Ver Por Día</label>
                <div class="input-group">
                    <select id="filterDay" class="form-select" style="max-width: 80px; background-color: #fff;"></select>
                    
                    <select id="filterMonth" class="form-select" style="background-color: #fff;">
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                    
                    <button class="btn btn-outline-secondary" onclick="loadFromSelects()">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="col-md-2">
                 <button class="btn btn-verde w-100" onclick="loadToday()">
                    <i class="fa-solid fa-calendar-day me-2"></i> HOY
                 </button>
            </div>

            <div class="col-md-1 text-center text-muted fw-bold align-self-center">O</div>

            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">Ver Por Mes</label>
                <div class="input-group">
                    <select id="monthPicker" class="form-select">
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                    <button class="btn btn-verde" onclick="loadMonth()"><i class="fa-solid fa-calendar-days"></i></button>
                </div>
            </div>
            
            <div class="col-md-2 text-end">
                <button class="btn btn-naranja w-100 fw-bold py-2" onclick="copyList()">
                    <i class="fa-solid fa-copy me-2"></i> COPIAR
                </button>
            </div>
        </div>
    </div>

    <div class="card card-custom p-4" style="border-left: 5px solid var(--color-naranja);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold m-0 text-success"><i class="fa-solid fa-paper-plane"></i> Centro de Envíos</h5>
                <small class="text-muted">Genere las tarjetas primero, revise las vistas previas y luego envíe todo.</small>
            </div>
            <div class="d-flex gap-2">
                <button id="btnGenerarTodo" class="btn btn-outline-success" onclick="iniciarGeneracionMasiva()">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> 1. Generar Vistas Previas
                </button>
                <button id="btnEnviarTodo" class="btn btn-secondary" onclick="iniciarEnvioMasivo()" disabled>
                    <i class="fa-regular fa-paper-plane"></i> 2. Enviar Todos
                </button>
            </div>
        </div>
        <div class="progress mt-3" style="height: 5px; display:none;" id="progressBarContainer">
            <div class="progress-bar bg-warning" id="progressBar" style="width: 0%"></div>
        </div>
    </div>

    <div class="card card-custom p-4">
        <h3 id="tituloLista" class="text-center fw-bold" style="color: var(--color-verde);">Cargando...</h3>
        <hr>
        
        <div id="contenedorLista" class="lista-container"></div>
        
        <div class="mt-3 text-center text-muted small">
            Total encontrados: <span id="totalCount" class="fw-bold">0</span>
        </div>
    </div>

  </div>

  <div id="loading"><div class="spinner-border text-success" role="status"></div></div>

  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast align-items-center text-bg-success border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body"><i class="fa-solid fa-check-circle me-2"></i> Copiado al portapapeles</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalQuickEdit" tabindex="-1">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header bg-light py-2">
          <h6 class="modal-title fw-bold">Editar Contacto (Temporal)</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
           <input type="hidden" id="editIndex">
           <div class="mb-3">
               <label class="form-label small fw-bold">Correo Electrónico</label>
               <input type="email" id="editEmail" class="form-control form-control-sm">
           </div>
           <div class="mb-2">
               <label class="form-label small fw-bold">WhatsApp / Celular</label>
               <input type="text" id="editPhone" class="form-control form-control-sm">
           </div>
           <small class="text-muted" style="font-size:0.75rem">*Estos cambios no se guardan en la BD, solo para este envío.</small>
        </div>
        <div class="modal-footer py-1">
           <button type="button" class="btn btn-sm btn-primary w-100" onclick="saveQuickEdit()">Aplicar Cambios</button>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const API = 'api_cumpleanos.php';
    let rawTextToCopy = "";
    let currentList = [];

    document.addEventListener('DOMContentLoaded', () => {
        // 1. Llenar el selector de Días (1 al 31)
        const daySelect = document.getElementById('filterDay');
        for (let i = 1; i <= 31; i++) {
            let opt = document.createElement('option');
            opt.value = i;
            opt.innerText = i;
            daySelect.appendChild(opt);
        }

        // 2. Seleccionar la fecha de HOY por defecto
        const today = new Date();
        document.getElementById('filterDay').value = today.getDate();
        document.getElementById('filterMonth').value = today.getMonth() + 1;
        
        document.getElementById('monthPicker').value = today.getMonth() + 1;

        // 3. Cargar lista
        loadFromSelects();
    });

    // --- CARGAR DESDE LOS SELECTORES (DÍA - MES) ---
    async function loadFromSelects() {
        showLoading(true);
        
        const d = document.getElementById('filterDay').value;
        const m = document.getElementById('filterMonth').value;
        
        // Construimos fecha ficticia YYYY-MM-DD
        const year = new Date().getFullYear();
        const fullDate = `${year}-${m.toString().padStart(2, '0')}-${d.toString().padStart(2, '0')}`;

        try {
            const res = await fetch(`${API}?action=day&date=${fullDate}`);
            const data = await res.json();
            renderList(data, 'day');
        } catch(e) { console.error(e); }
        showLoading(false);
    }

    // Botón HOY
    async function loadToday() {
        const today = new Date();
        document.getElementById('filterDay').value = today.getDate();
        document.getElementById('filterMonth').value = today.getMonth() + 1;
        loadFromSelects();
    }

    // Botón Ver Por Mes
    async function loadMonth() {
        showLoading(true);
        const m = document.getElementById('monthPicker').value;
        try {
            const res = await fetch(`${API}?action=month&month=${m}`);
            const data = await res.json();
            renderList(data, 'month');
        } catch(e) { console.error(e); }
        showLoading(false);
    }

    function renderList(data, type) {
        const container = document.getElementById('contenedorLista');
        const titulo = document.getElementById('tituloLista');
        const count = document.getElementById('totalCount');
        
        currentList = data.lista || [];
        container.innerHTML = '';
        titulo.innerText = data.titulo;
        count.innerText = currentList.length;
        
        const btnSend = document.getElementById('btnEnviarTodo');
        if(btnSend) { btnSend.disabled = true; btnSend.className = 'btn btn-secondary'; }
        
        rawTextToCopy = `*${data.titulo}*\n\n`;

        if(currentList.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-5">No hay cumpleaños.</div>';
            return;
        }

        let currentDay = null;

        currentList.forEach((item, index) => {
            // SEPARADOR DE DÍA
            if (type === 'month' && item.dia !== currentDay) {
                currentDay = item.dia;
                container.appendChild(Object.assign(document.createElement('div'), {
                    className: 'dia-separator',
                    innerHTML: `<i class="fa-regular fa-calendar me-2"></i>Día ${currentDay}`
                }));
                rawTextToCopy += `\n[Día ${currentDay}]\n`;
            }

            const div = document.createElement('div');
            div.className = 'cumple-item';
            div.id = `row-${index}`;
            
            // 1. Estado
            let statusHtml = `<div id="status-${index}" style="min-width: 40px; margin-right:10px; text-align: center;"><i class="fa-regular fa-circle text-muted small"></i></div>`;
            
            // 2. Miniatura
            let thumbHtml = `
                <div id="thumb-${index}" style="width: 60px; height: 40px; background: #eee; border-radius: 4px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;">
                    <i class="fa-regular fa-image text-muted opacity-50"></i>
                </div>`;

            // 3. Info Principal
            let nombreDisplay = `${item.nombre} <span class="text-muted fw-normal ms-1" style="font-size: 0.85em;">#${item.col || '---'}</span>`;

            // 4. Selector de Correo
            let emailDisplay = '';
            if (item.email_gmail && item.email_other) {
                emailDisplay = `
                    <select class="form-select form-select-sm py-0 mt-1" style="font-size:0.8rem; width:auto;" onchange="updateEmail(${index}, this.value)">
                        <option value="${item.email_gmail}" selected>📧 ${item.email_gmail} (Gmail)</option>
                        <option value="${item.email_other}">✉️ ${item.email_other}</option>
                    </select>
                `;
            } else if (item.email_active) {
                emailDisplay = `<small class="text-muted"><i class="fa-solid fa-envelope me-1"></i>${item.email_active}</small>`;
            } else {
                emailDisplay = `<small class="text-danger fw-bold"><i class="fa-solid fa-ban me-1"></i>Sin correo</small>`;
            }

            let infoHtml = `
                <div class="flex-grow-1">
                    <div class="fw-bold" style="color: var(--color-texto); font-size: 0.95rem;">${nombreDisplay}</div>
                    <div id="email-container-${index}">${emailDisplay}</div>
                </div>`;

            // 5. Botones Acción
            let wspClass = (item.celular && item.celular.length >= 9) ? 'btn-light border text-success' : 'btn-light border text-secondary disabled opacity-50';
            let wspLink = (item.celular && item.celular.length >= 9) ? `href="https://wa.me/51${item.celular}" target="_blank"` : '';
            
            let actionHtml = `
                <div class="d-flex align-items-center gap-2">
                    <a ${wspLink} id="btn-wsp-${index}" class="btn btn-sm ${wspClass}" title="WhatsApp">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    <button class="btn btn-sm btn-light border text-primary" onclick="openQuickEdit(${index})" title="Editar Correo/Celular para este envío">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                </div>
            `;

            div.innerHTML = `
                <div class="d-flex align-items-center gap-2 w-100">
                    ${statusHtml}
                    ${thumbHtml}
                    ${infoHtml}
                    ${actionHtml}
                </div>
            `;
            
            container.appendChild(div);
            
            // Copiar al portapapeles (Solo nombre)
            rawTextToCopy += `${item.nombre}\n`;
        });
    }

    // --- FUNCIONES DE EDICIÓN ---
    function updateEmail(index, newVal) {
        currentList[index].email_active = newVal;
    }

    function openQuickEdit(index) {
        const item = currentList[index];
        document.getElementById('editIndex').value = index;
        document.getElementById('editEmail').value = item.email_active || '';
        document.getElementById('editPhone').value = item.celular || '';
        const modal = new bootstrap.Modal(document.getElementById('modalQuickEdit'));
        modal.show();
    }

    function saveQuickEdit() {
        const index = document.getElementById('editIndex').value;
        const newEmail = document.getElementById('editEmail').value.trim();
        const newPhone = document.getElementById('editPhone').value.trim();
        
        currentList[index].email_active = newEmail;
        currentList[index].celular = newPhone;
        
        const containerEmail = document.getElementById(`email-container-${index}`);
        if(newEmail) {
            containerEmail.innerHTML = `<small class="text-primary fw-bold"><i class="fa-solid fa-envelope me-1"></i>${newEmail} (Editado)</small>`;
        } else {
            containerEmail.innerHTML = `<small class="text-danger fw-bold"><i class="fa-solid fa-ban me-1"></i>Sin correo</small>`;
        }
        
        const btnWsp = document.getElementById(`btn-wsp-${index}`);
        if(newPhone && newPhone.length >= 9) {
            btnWsp.className = 'btn btn-sm btn-light border text-success';
            btnWsp.href = `https://wa.me/51${newPhone}`;
            btnWsp.classList.remove('disabled', 'opacity-50');
            btnWsp.removeAttribute('disabled');
        } else {
            btnWsp.className = 'btn btn-sm btn-light border text-secondary disabled opacity-50';
            btnWsp.removeAttribute('href');
        }

        const modalEl = document.getElementById('modalQuickEdit');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        modalInstance.hide();
    }

    // --- GENERAR ---
    async function iniciarGeneracionMasiva() {
        if(currentList.length === 0) return;
        
        const bar = document.getElementById('progressBar');
        const barCont = document.getElementById('progressBarContainer');
        if(barCont) barCont.style.display = 'flex';
        if(bar) bar.className = 'progress-bar bg-warning';

        for (let i = 0; i < currentList.length; i++) {
            const item = currentList[i];
            const statusEl = document.getElementById(`status-${i}`);
            const thumbEl = document.getElementById(`thumb-${i}`);
            
            if(!statusEl) continue; 
            if(bar) bar.style.width = (((i + 1) / currentList.length) * 100) + '%';
            
            statusEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-warning small"></i>';

            try {
                const formData = new FormData();
                formData.append('action', 'generar');
                formData.append('nombre', item.nombre);

                const res = await fetch('motor_envios.php', { method: 'POST', body: formData });
                const json = await res.json();

                if(json.ok) {
                    item.temp_url = json.url;
                    if(thumbEl) {
                        thumbEl.style.cursor = "pointer";
                        thumbEl.innerHTML = `<img src="${json.url}" style="width:100%; height:100%; object-fit:cover;">`;
                        thumbEl.onclick = function() { window.open(json.url, '_blank'); }; 
                    }
                    statusEl.innerHTML = '<i class="fa-solid fa-check text-primary small"></i>';
                } else {
                    statusEl.innerHTML = '<i class="fa-solid fa-xmark text-danger small"></i>';
                }
            } catch(e) {
                statusEl.innerHTML = '<i class="fa-solid fa-xmark text-danger small"></i>';
            }
        }
        
        const btnSend = document.getElementById('btnEnviarTodo');
        if(btnSend){ btnSend.disabled = false; btnSend.className = 'btn btn-success'; }
    }

    // --- ENVIAR ---
    async function iniciarEnvioMasivo() {
        if(!confirm("¿Iniciar envío masivo de correos?")) return;

        const bar = document.getElementById('progressBar');
        if(bar) { bar.className = 'progress-bar bg-success'; bar.style.width = '0%'; }

        let enviados = 0;
        let errores = 0;

        for (let i = 0; i < currentList.length; i++) {
            const item = currentList[i];
            const statusEl = document.getElementById(`status-${i}`);
            
            if(!statusEl) continue;
            if(bar) bar.style.width = (((i + 1) / currentList.length) * 100) + '%';

            if (!item.email_active || !item.temp_url) {
                if(!item.email_active) statusEl.innerHTML = '<span class="badge bg-light text-muted border">FALTA CORREO</span>';
                continue;
            }

            statusEl.innerHTML = '<i class="fa-solid fa-paper-plane fa-bounce text-success small"></i>';

            try {
                const formData = new FormData();
                formData.append('action', 'enviar');
                formData.append('nombre', item.nombre);
                formData.append('email', item.email_active); 
                formData.append('url_imagen', item.temp_url);

                const res = await fetch('motor_envios.php', { method: 'POST', body: formData });
                const json = await res.json();

                if(json.ok) {
                    statusEl.innerHTML = '<i class="fa-solid fa-check-double text-success fw-bold fa-lg"></i>';
                    enviados++;
                } else {
                    errores++;
                    let msgError = "ERROR";
                    let color = "bg-danger";
                    let detalle = String(json.error).toLowerCase();

                    if (detalle.includes('recipients failed') || detalle.includes('invalid address')) {
                        msgError = "CORREO NO EXISTE";
                    } else if (detalle.includes('connect') || detalle.includes('timeout')) {
                        msgError = "ERROR CONEXIÓN";
                        color = "bg-warning text-dark";
                    } else if (detalle.includes('quota') || detalle.includes('limit')) {
                        msgError = "LÍMITE DIARIO";
                    }

                    statusEl.innerHTML = `<span class="badge ${color}" title="${json.error}" style="font-size:0.7rem;">${msgError}</span>`;
                }
            } catch(e) {
                errores++;
                statusEl.innerHTML = '<span class="badge bg-danger">ERROR RED</span>';
            }
        }
        alert(`Proceso finalizado.\n\n✅ Enviados: ${enviados}\n❌ Fallidos: ${errores}`);
    }

    function copyList() {
        navigator.clipboard.writeText(rawTextToCopy).then(() => {
            const toastEl = document.getElementById('liveToast');
            if(toastEl) { const toast = new bootstrap.Toast(toastEl); toast.show(); }
        });
    }

    function showLoading(show) {
        const l = document.getElementById('loading');
        if(l) l.style.display = show ? 'flex' : 'none';
    }
  </script>

<script>
  // Script para activar la animación de entrada
  document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.animate-container');
    if (container) {
      // Pequeño retraso para asegurar que el estado inicial se renderice
      setTimeout(() => {
        container.classList.add('show');
      }, 50);
    }
  });
</script>

</body>
</html>