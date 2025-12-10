<?php require_once "conexion.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cumpleaños · ICAS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/logo.png">
  
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
    
    /* Botón Naranja para Copiar */
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
    
    .dia-badge { 
        background: var(--color-naranja); color: white; 
        width: 32px; height: 32px; border-radius: 50%; 
        display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem;
    }
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
    <div class="container-fluid px-4">
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

  <div class="container-fluid px-4">
    
    <div class="card card-custom p-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">Ver Por Día</label>
                <div class="input-group">
                    <input type="date" id="datePicker" class="form-control">
                    <button class="btn btn-outline-secondary" onclick="loadDay(null)"><i class="fa-solid fa-search"></i></button>
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
            
            <div class="col-md-3 text-end">
                <button class="btn btn-naranja w-100 fw-bold py-2" onclick="copyList()">
                    <i class="fa-solid fa-copy me-2"></i> COPIAR LISTA
                </button>
            </div>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const API = 'api_cumpleanos.php';
    let rawTextToCopy = "";

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('datePicker').valueAsDate = new Date();
        document.getElementById('monthPicker').value = new Date().getMonth() + 1;
        loadToday();
    });

    async function loadToday() {
        document.getElementById('datePicker').valueAsDate = new Date();
        loadDay(null);
    }

    async function loadDay(specificDate) {
        showLoading(true);
        let dateVal = specificDate || document.getElementById('datePicker').value;
        if(!dateVal) dateVal = new Date().toISOString().split('T')[0];
        try {
            const res = await fetch(`${API}?action=day&date=${dateVal}`);
            const data = await res.json();
            renderList(data, 'day');
        } catch(e) { console.error(e); alert('Error'); }
        showLoading(false);
    }

    async function loadMonth() {
        showLoading(true);
        const m = document.getElementById('monthPicker').value;
        try {
            const res = await fetch(`${API}?action=month&month=${m}`);
            const data = await res.json();
            renderList(data, 'month');
        } catch(e) { console.error(e); alert('Error'); }
        showLoading(false);
    }

    function renderList(data, type) {
        const container = document.getElementById('contenedorLista');
        const titulo = document.getElementById('tituloLista');
        const count = document.getElementById('totalCount');
        
        container.innerHTML = '';
        titulo.innerText = data.titulo;
        const lista = data.lista || [];
        count.innerText = lista.length;
        
        // --- CONSTRUCCIÓN DEL TEXTO DE COPIADO ---
        rawTextToCopy = `*${data.titulo}*\n\n`;

        if(lista.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-5">No hay cumpleaños.</div>';
            rawTextToCopy += "Sin cumpleaños.";
            return;
        }

        if (type === 'day') {
            lista.forEach(item => {
                // VISUAL (Con emoji)
                const div = document.createElement('div');
                div.className = 'cumple-item';
                div.innerHTML = `<div><i class="fa-solid fa-cake-candles me-3 text-warning"></i> <span class="fw-medium">${item.nombre}</span></div>`;
                container.appendChild(div);
                
                // COPIAR (LIMPIO: Solo Nombre)
                rawTextToCopy += `${item.nombre}\n`;
            });
        } else {
            // MES
            let currentDay = null;
            lista.forEach(item => {
                if (item.dia !== currentDay) {
                    currentDay = item.dia;
                    
                    // Separador Visual
                    const sep = document.createElement('div');
                    sep.className = 'dia-separator';
                    sep.innerHTML = `<i class="fa-regular fa-calendar me-2"></i>Día ${currentDay}`;
                    container.appendChild(sep);

                    // Separador Copiar
                    rawTextToCopy += `\n[Día ${currentDay}]\n`;
                }
                
                // Item Visual
                const div = document.createElement('div');
                div.className = 'cumple-item ps-4';
                div.innerHTML = `<div>${item.nombre}</div>`;
                container.appendChild(div);
                
                // Item Copiar (Solo nombre)
                rawTextToCopy += `${item.nombre}\n`;
            });
        }
    }

    function copyList() {
        navigator.clipboard.writeText(rawTextToCopy).then(() => {
            const toastEl = document.getElementById('liveToast');
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        });
    }

    function showLoading(show) {
        document.getElementById('loading').style.display = show ? 'flex' : 'none';
    }
  </script>
</body>
</html>