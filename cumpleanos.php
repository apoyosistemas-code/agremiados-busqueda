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
        padding-top: 105px;
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

    @media (max-width: 768px) {
        body { padding-top: 165px; }
        .navbar-custom { height: auto; padding: 1rem; }
        .navbar-custom .container-fluid { flex-direction: column; gap: 1rem; align-items: center; }
        .navbar-brand { font-size: 1.1rem; }
        .navbar-brand img { height: 60px; }
        .card-custom { padding: 1rem !important; }
        .cumple-item { flex-direction: column; align-items: flex-start; gap: 10px; }
        .actions-col { width: 100%; display: flex; justify-content: flex-end; }
    }
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
            <i class="fa-solid fa-arrow-left"></i> Volver al Inicio
        </a>
      </div>
    </div>
  </nav>

  <div class="container-fluid px-4 animate-container">
    
    <div class="card card-custom p-4">
        <div class="row g-3 align-items-end">
            <div class="col-6 col-md-4 order-1 order-md-1"> 
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
            
            <div class="col-12 col-md-2 order-3 order-md-2">
                 <button class="btn btn-verde w-100" onclick="loadToday()">
                    <i class="fa-solid fa-calendar-day me-2"></i> HOY
                 </button>
            </div>

            <div class="col-md-1 text-center text-muted fw-bold align-self-center order-md-3 d-none d-md-block">O</div>

            <div class="col-6 col-md-3 order-2 order-md-4">
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
            
            <div class="col-12 col-md-2 text-end order-4 order-md-5">
                <button class="btn btn-naranja w-100 fw-bold py-2" onclick="copyList()">
                    <i class="fa-solid fa-copy me-2"></i> LISTA
                </button>
            </div>
        </div>
    </div>

    <div class="card card-custom p-4" style="border-left: 5px solid var(--color-naranja);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold m-0 text-success"><i class="fa-solid fa-paper-plane"></i> Centro de Envíos</h5>
                <small class="text-muted">Genere las tarjetas primero, copie a WhatsApp o envíe por correo.</small>
            </div>
            <div class="d-flex gap-2">
                <button id="btnGenerarTodo" class="btn btn-outline-success" onclick="iniciarGeneracionMasiva()">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> 1. Generar Vistas Previas
                </button>
                <button id="btnEnviarTodo" class="btn btn-secondary" onclick="iniciarEnvioMasivo()" disabled>
                    <i class="fa-regular fa-paper-plane"></i> 2. Enviar Correos
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
        <div class="toast-body"><i class="fa-solid fa-check-circle me-2"></i> Acción realizada</div>
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
    /* =========================================
       LOGICA DE NEGOCIO Y API
       ========================================= */
    const API = 'api_cumpleanos.php';
    let rawTextToCopy = "";
    let currentList = [];

    // --- 1. UTILS: GENERADOR DE MENSAJES Y COPIADO ---
    
    function getMensajeSaludo(nombre) {
        return `¡Feliz Cumpleaños Dr(a). ${nombre}! 🎂🎉\n\nDesde el Ilustre Colegio de Abogados de Junín le enviamos un cordial saludo, deseándole éxitos en su vida personal y profesional.\n\nAtte.\nJunta Directiva 2024 – 2026`;
    }

    function showToast(msg) {
        const toastEl = document.getElementById('liveToast');
        if(toastEl) {
             document.querySelector('#liveToast .toast-body').innerHTML = `<i class="fa-solid fa-check-circle me-2"></i> ${msg}`;
             const toast = new bootstrap.Toast(toastEl); toast.show();
        }
    }

    function copiarTextoWsp(nombre) {
        const texto = getMensajeSaludo(nombre);
        navigator.clipboard.writeText(texto).then(() => {
            showToast('Texto copiado al portapapeles');
        });
    }

    function abrirWhatsapp(celular, nombre) {
        if (!celular || celular.length < 9) {
            alert("Número celular inválido o vacío.");
            return;
        }
        let numero = celular.replace(/\D/g, ''); // Solo números
        if (!numero.startsWith('51') && numero.length === 9) numero = '51' + numero;
        
        const texto = encodeURIComponent(getMensajeSaludo(nombre));
        window.open(`https://wa.me/${numero}?text=${texto}`, '_blank');
    }

    async function copiarImagenPortapapeles(url) {
        try {
            const data = await fetch(url);
            const blob = await data.blob();
            await navigator.clipboard.write([new ClipboardItem({[blob.type]: blob})]);
            showToast('¡Imagen copiada! (Ctrl+V en WhatsApp)');
        } catch (err) {
            console.error(err);
            alert("No se pudo copiar la imagen automáticamente. Intente 'Clic Derecho > Copiar imagen'.");
        }
    }

    // --- 2. INICIALIZACION ---

    document.addEventListener('DOMContentLoaded', () => {
        // Llenar días
        const daySelect = document.getElementById('filterDay');
        for (let i = 1; i <= 31; i++) {
            let opt = document.createElement('option');
            opt.value = i; opt.innerText = i;
            daySelect.appendChild(opt);
        }

        // Sets default date (HOY)
        const today = new Date();
        document.getElementById('filterDay').value = today.getDate();
        document.getElementById('filterMonth').value = today.getMonth() + 1;
        document.getElementById('monthPicker').value = today.getMonth() + 1;

        // Cargar lista inicial
        loadFromSelects();
    });

    // --- 3. CARGA DE DATOS ---

    async function loadFromSelects() {
        showLoading(true);
        const d = document.getElementById('filterDay').value;
        const m = document.getElementById('filterMonth').value;
        const year = new Date().getFullYear();
        const fullDate = `${year}-${m.toString().padStart(2, '0')}-${d.toString().padStart(2, '0')}`;

        try {
            const res = await fetch(`${API}?action=day&date=${fullDate}`);
            const data = await res.json();
            renderList(data, 'day');
        } catch(e) { console.error(e); }
        showLoading(false);
    }

    async function loadToday() {
        const today = new Date();
        document.getElementById('filterDay').value = today.getDate();
        document.getElementById('filterMonth').value = today.getMonth() + 1;
        loadFromSelects();
    }

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

    // --- 4. RENDERIZADO DE TABLA ---

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
            // Separador de día si es vista mensual
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
            
            // Columna 1: Estado
            let statusHtml = `<div id="status-${index}" style="min-width: 30px; text-align: center;"><i class="fa-regular fa-circle text-muted small"></i></div>`;
            
            // Columna 2: Miniatura (Preview)
            // Aquí se inyectará la imagen y el botón de copiado
            let thumbHtml = `
                <div id="thumb-${index}" style="width: 70px; height: 50px; background: #eee; border-radius: 4px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd; position: relative;">
                    <i class="fa-regular fa-image text-muted opacity-50"></i>
                </div>`;

            // Columna 3: Información
            let nombreClean = item.nombre.replace(/'/g, "\\'"); // Escapar comillas para JS
            let nombreDisplay = `${item.nombre} <span class="badge bg-light text-dark border ms-1">#${item.col || '---'}</span>`;

            // Selector de correos
            let emailDisplay = '';
            if (item.email_gmail && item.email_other) {
                emailDisplay = `
                    <select class="form-select form-select-sm py-0 mt-1" style="font-size:0.75rem; width:auto; border-color:#eee;" onchange="updateEmail(${index}, this.value)">
                        <option value="${item.email_gmail}" selected>📧 ${item.email_gmail}</option>
                        <option value="${item.email_other}">✉️ ${item.email_other}</option>
                    </select>
                `;
            } else if (item.email_active) {
                emailDisplay = `<small class="text-muted"><i class="fa-solid fa-envelope me-1"></i>${item.email_active}</small>`;
            } else {
                emailDisplay = `<small class="text-danger fw-bold"><i class="fa-solid fa-ban me-1"></i>Sin correo</small>`;
            }

            let infoHtml = `
                <div class="flex-grow-1 ms-3">
                    <div class="fw-bold" style="color: var(--color-texto); font-size: 0.95rem;">${nombreDisplay}</div>
                    <div id="email-container-${index}">${emailDisplay}</div>
                </div>`;

            // Columna 4: Botones de Acción
            // Lógica WhatsApp
            let hasCel = (item.celular && item.celular.length >= 9);
            let btnWspClass = hasCel ? 'btn-success' : 'btn-outline-secondary disabled';
            
            let actionHtml = `
                <div class="actions-col d-flex align-items-center gap-1">
                    
                    <button class="btn btn-sm btn-outline-secondary" 
                            onclick="copiarTextoWsp('${nombreClean}')" 
                            title="Copiar texto de saludo">
                        <i class="fa-regular fa-copy"></i>
                    </button>

                    <button class="btn btn-sm ${btnWspClass}" 
                            onclick="abrirWhatsapp('${item.celular}', '${nombreClean}')" 
                            id="btn-wsp-${index}"
                            title="Abrir WhatsApp con mensaje">
                        <i class="fa-brands fa-whatsapp"></i>
                    </button>

                    <button class="btn btn-sm btn-light border text-primary" 
                            onclick="openQuickEdit(${index})" 
                            title="Editar Correo/Celular">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                </div>
            `;

            div.innerHTML = `
                <div class="d-flex align-items-center w-100">
                    ${statusHtml}
                    ${thumbHtml}
                    ${infoHtml}
                    ${actionHtml}
                </div>
            `;
            
            container.appendChild(div);
            
            // Texto para copiado masivo (solo lista)
            rawTextToCopy += `${item.nombre} - ${item.celular || 'S/N'}\n`;
        });
    }

    // --- 5. LOGICA DE EDICION RAPIDA ---
    
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
        
        // Actualizar UI Correo
        const containerEmail = document.getElementById(`email-container-${index}`);
        if(newEmail) {
            containerEmail.innerHTML = `<small class="text-primary fw-bold"><i class="fa-solid fa-envelope me-1"></i>${newEmail} (Editado)</small>`;
        } else {
            containerEmail.innerHTML = `<small class="text-danger fw-bold"><i class="fa-solid fa-ban me-1"></i>Sin correo</small>`;
        }
        
        // Actualizar UI WhatsApp
        const btnWsp = document.getElementById(`btn-wsp-${index}`);
        let nombreClean = currentList[index].nombre.replace(/'/g, "\\'");
        
        if(newPhone && newPhone.length >= 9) {
            btnWsp.className = 'btn btn-sm btn-success';
            btnWsp.onclick = function() { abrirWhatsapp(newPhone, nombreClean); };
            btnWsp.classList.remove('disabled');
        } else {
            btnWsp.className = 'btn btn-sm btn-outline-secondary disabled';
            btnWsp.onclick = null;
        }

        const modalEl = document.getElementById('modalQuickEdit');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        modalInstance.hide();
    }

    // --- 6. GENERACION MASIVA ---

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
                    
                    // Actualizar Miniatura con la imagen Y el botón de copiar
                    if(thumbEl) {
                        thumbEl.style.cursor = "pointer";
                        thumbEl.onclick = function() { window.open(json.url, '_blank'); };
                        
                        thumbEl.innerHTML = `
                            <div style="position:relative; width:100%; height:100%;">
                                <img src="${json.url}" style="width:100%; height:100%; object-fit:cover;">
                                
                                <button onclick="event.stopPropagation(); copiarImagenPortapapeles('${json.url}')" 
                                        class="btn btn-sm btn-light shadow-sm p-0 d-flex align-items-center justify-content-center"
                                        style="position:absolute; bottom:2px; right:2px; width:22px; height:22px; border-radius:4px; opacity:0.9;" 
                                        title="Copiar Imagen al Portapapeles">
                                   <i class="fa-regular fa-copy" style="font-size:10px;"></i>
                                </button>
                            </div>
                        `;
                    }
                    statusEl.innerHTML = '<i class="fa-solid fa-check text-primary small"></i>';
                } else {
                    statusEl.innerHTML = '<i class="fa-solid fa-xmark text-danger small"></i>';
                }
            } catch(e) {
                statusEl.innerHTML = '<i class="fa-solid fa-xmark text-danger small"></i>';
            }
        }
        
        // Habilitar botón de envío
        const btnSend = document.getElementById('btnEnviarTodo');
        if(btnSend){ btnSend.disabled = false; btnSend.className = 'btn btn-success'; }
    }

    // --- 7. ENVIO MASIVO ---

    async function iniciarEnvioMasivo() {
        if(!confirm("¿Desea enviar los correos a los destinatarios generados?")) return;

        const bar = document.getElementById('progressBar');
        if(bar) { bar.className = 'progress-bar bg-success'; bar.style.width = '0%'; }

        let enviados = 0;
        let errores = 0;

        for (let i = 0; i < currentList.length; i++) {
            const item = currentList[i];
            const statusEl = document.getElementById(`status-${i}`);
            
            if(!statusEl) continue;
            if(bar) bar.style.width = (((i + 1) / currentList.length) * 100) + '%';

            // Validaciones previas
            if (!item.email_active || !item.temp_url) {
                if(!item.email_active) statusEl.innerHTML = '<span class="badge bg-light text-muted border" style="font-size:0.6rem">NO MAIL</span>';
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
                    let detalle = String(json.error || '').toLowerCase();

                    if (detalle.includes('recipients failed') || detalle.includes('invalid address')) {
                        msgError = "MAIL INVALIDO";
                    } else if (detalle.includes('connect') || detalle.includes('timeout')) {
                        msgError = "TIMEOUT";
                        color = "bg-warning text-dark";
                    } else if (detalle.includes('quota') || detalle.includes('limit')) {
                        msgError = "QUOTA";
                    }
                    statusEl.innerHTML = `<span class="badge ${color}" title="${json.error}" style="font-size:0.6rem;">${msgError}</span>`;
                }
            } catch(e) {
                errores++;
                statusEl.innerHTML = '<span class="badge bg-danger" style="font-size:0.6rem">RED</span>';
            }
        }
        alert(`Resumen de Envío:\n\n✅ Enviados exitosamente: ${enviados}\n❌ Errores: ${errores}`);
    }

    function copyList() {
        navigator.clipboard.writeText(rawTextToCopy).then(() => showToast('Lista copiada'));
    }

    function showLoading(show) {
        const l = document.getElementById('loading');
        if(l) l.style.display = show ? 'flex' : 'none';
    }
  </script>

  <script>
    // Animación de entrada suave
    document.addEventListener('DOMContentLoaded', () => {
      const container = document.querySelector('.animate-container');
      if (container) setTimeout(() => container.classList.add('show'), 50);
    });
  </script>

</body>
</html>