<?php
// editor.php - FINAL V13 (Fecha de Incorporación Asegurada)
require_once "conexion.php";
require_once "auth.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Agremiados · ICAJ</title>
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
    .table thead th { 
        background-color: #f8f9fa; color: var(--color-verde); 
        font-weight: 600; text-transform: uppercase; font-size: 0.8rem;
        border-bottom: 2px solid #dee2e6;
    }
    .nombre-agremiado { font-weight: 400; color: #000; }
    
    .btn-verde { background-color: var(--color-verde); color: white; border-radius: 6px; padding: 8px 16px; border:none;}
    .btn-verde:hover { background-color: #0e3f2d; color: white; }
    .btn-naranja { background-color: var(--color-naranja); color: white; border-radius: 6px; border:none;}
    .btn-naranja:hover { background-color: #d38b13; color: white; }
    .btn-outline-verde { border: 1px solid var(--color-verde); color: var(--color-verde); }
    .btn-outline-verde:hover { background: var(--color-verde); color: white; }
    
    .badge-estado { padding: 6px 12px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
    .bg-vivo { background-color: #d1fae5; color: #065f46; } 
    .bg-fallecido { background-color: #fee2e2; color: #991b1b; }
    
    .modal-header { background-color: var(--color-verde); color: white; }
    .form-label { color: var(--color-verde); font-size: 0.85rem; font-weight: 500; margin-bottom: 2px; }
    .section-head {
        color: var(--color-naranja); font-size: 0.95rem; font-weight: 600;
        border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px; margin-bottom: 10px;
    }
    #loading { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; display: none; align-items: center; justify-content: center; }
    
    #q { font-size: 1.1rem; padding: 10px 15px; border: 2px solid #ddd; }
    #q:focus { border-color: var(--color-verde); box-shadow: none; }
    
    .pagination-controls { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: center; }
    .page-input { width: 60px; text-align: center; border: 1px solid #ddd; border-radius: 4px; padding: 4px; }

    @media (max-width: 768px) {
        body {
            padding-top: 165px; /* Increased padding for taller navbar */
        }
        .navbar-custom {
            height: auto; /* Allow navbar to grow */
            padding: 1rem;
        }
        .navbar-custom .container-fluid {
            flex-direction: column; /* Stack logo and buttons */
            gap: 1rem;
            align-items: center;
        }
        .navbar-brand {
            font-size: 1.1rem;
        }
        .navbar-brand img {
            height: 60px;
        }
        .d-flex.flex-wrap.gap-3.justify-content-between.align-items-center {
            flex-direction: column;
            align-items: stretch !important;
        }
        .d-flex.flex-wrap.gap-3.justify-content-between.align-items-center .btn-naranja {
            width: 100%;
            justify-content: center;
        }
        .pagination-controls {
            gap: 0.5rem; /* Allow items to wrap naturally */
        }
        .pagination-controls .vr {
            display: none; /* Hide vertical rule on mobile */
        }
        .table, .table-responsive {
            font-size: 0.8rem;
        }
        .table th, .table td {
            padding: 0.5rem 0.4rem;
        }
        .ps-4 {
            padding-left: 0.8rem !important;
        }
        .pe-4 {
            padding-right: 0.8rem !important;
        }
    }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container-fluid px-4 animate-container">
      <a class="navbar-brand" href="editor.php" title="Reiniciar">
        <img src="assets/logo.png" alt="Logo">
        <span>Gestión de Agremiados</span>
      </a>
      <div class="d-flex gap-2">
        <a href="editor.php" class="btn btn-sm btn-light border text-muted" title="Recargar"><i class="fa-solid fa-rotate-right"></i></a>
        <a href="index.php" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 fw-medium px-3">
            <i class="fa-solid fa-arrow-left"></i> Volver al Inicio
        </a>
      </div>
    </div>
  </nav>

  <div class="container-fluid px-4 animate-container">
    <div class="card card-custom p-3">
      <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <div class="d-flex gap-2 flex-grow-1 position-relative" style="max-width: 600px;">
          <input type="text" id="q" class="form-control rounded-pill" placeholder="Buscar (DNI, Nombre, Colegiatura)..." autocomplete="off">
          <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="right: 20px; top: 15px;"></i>
        </div>
        <button class="btn btn-naranja d-flex align-items-center gap-2" onclick="prepInsert()">
          <i class="fa-solid fa-plus"></i> Nuevo Agremiado
        </button>
      </div>
    </div>

    <div class="card card-custom overflow-hidden">
      <div class="table-responsive">
        <table class="table mb-0 table-hover">
          <thead>
            <tr>
              <th class="ps-4">N° Col</th>
              <th>Apellidos y Nombres</th>
              <th>DNI</th>
              <th>Estado</th>
              <th>Celular</th> 
              <th class="text-end pe-4">Acción</th>
            </tr>
          </thead>
          <tbody id="tbody"></tbody>
        </table>
      </div>

      <div class="card-footer bg-white py-3">
        <div class="pagination-controls">
           <button class="btn btn-outline-verde btn-sm fw-bold" id="btnShowAll" onclick="toggleShowAll()">
             <i class="fa-solid fa-list me-1"></i> Ver Todos
           </button>
           
           <div class="vr mx-2"></div>
           
           <button class="btn btn-light border btn-sm" id="btnFirst"><i class="fa-solid fa-angles-left"></i></button>
           <button class="btn btn-light border btn-sm" id="btnPrev"><i class="fa-solid fa-chevron-left"></i></button>
           
           <span class="small text-muted fw-bold ms-2">Página</span>
           <input type="number" id="pageInput" class="page-input" value="1" min="1">
           <span class="small text-muted fw-bold me-2" id="totalPagesLabel">de ?</span>
           
           <button class="btn btn-light border btn-sm" id="btnNext"><i class="fa-solid fa-chevron-right"></i></button>
           <button class="btn btn-light border btn-sm" id="btnLast"><i class="fa-solid fa-angles-right"></i></button>
           
           <span class="small text-muted ms-3" id="infoRows">...</span>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEdicion" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Ficha de Agremiado</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body bg-light">
          <form id="formEditor">
            <input type="hidden" name="id" id="field_id">
            
            <div class="container-fluid px-1">
              
              <h6 class="section-head mt-0">Datos Institucionales</h6>
              <div class="row g-3">
                <div class="col-md-2">
                  <label class="form-label">Colegiatura <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" name="COLEGIATURA" id="inputColegiatura" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Estado <span class="text-danger">*</span></label>
                  <select class="form-select" name="ESTADO">
                    <option value="VIVO">VIVO</option>
                    <option value="FALLECIDO">FALLECIDO</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Fecha de Incorporación</label>
                  <input type="date" class="form-control" name="FECHA_DE_INCORPORACI_N">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Seguro de Salud</label>
                  <input type="text" class="form-control" name="SEGURO_DE_SALUD" placeholder="Ej: ESSALUD, EPS...">
                </div>
              </div>

              <h6 class="section-head">Datos Personales</h6>
              <div class="row g-3">
                <div class="col-md-3">
                  <label class="form-label">DNI <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="DNI" required maxlength="15">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Apellidos y Nombres <span class="text-danger">*</span></label>
                  <input type="text" class="form-control text-uppercase" name="NOMBRE_DEL_AGREMIADO" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Fecha Nacimiento</label>
                  <input type="date" class="form-control" name="FECHA_CUMPLEA_OS">
                </div>
              </div>

              <h6 class="section-head">Ubicación y Contacto</h6>
              <div class="row g-3">
                <div class="col-md-3">
                   <label class="form-label"><i class="fa-solid fa-mobile-screen"></i> Celular</label>
                   <input type="text" class="form-control" name="N_MERO_DE_CELULAR">
                </div>
                <div class="col-md-3">
                   <label class="form-label"><i class="fa-solid fa-phone"></i> Teléfono Fijo</label>
                   <input type="text" class="form-control" name="TELEFONO_FIJO">
                </div>
                <div class="col-md-6">
                   <label class="form-label"><i class="fa-brands fa-facebook"></i> Facebook</label>
                   <input type="text" class="form-control" name="FACEBOOK">
                </div>

                <div class="col-md-6">
                   <label class="form-label"><i class="fa-regular fa-envelope"></i> Correo Personal</label>
                   <input type="email" class="form-control" name="CORREO">
                </div>
                <div class="col-md-6">
                   <label class="form-label"><i class="fa-brands fa-google"></i> Correo Gmail</label>
                   <input type="email" class="form-control" name="CORREO_GMAIL">
                </div>

                <div class="col-12">
                   <label class="form-label"><i class="fa-solid fa-house-user"></i> Domicilio Real</label>
                   <input type="text" class="form-control" name="DOMICILIO_REAL">
                </div>
                <div class="col-12">
                   <label class="form-label"><i class="fa-solid fa-briefcase"></i> Centro de Trabajo / Estudio</label>
                   <input type="text" class="form-control" name="CENTRO_DE_TRABAJO">
                </div>
                 <div class="col-md-8">
                   <label class="form-label"><i class="fa-solid fa-gavel"></i> Domicilio Procesal</label>
                   <input type="text" class="form-control" name="DOMICILIO_PROCESAL">
                </div>
                <div class="col-md-4">
                   <label class="form-label"><i class="fa-regular fa-paper-plane"></i> Casilla Electrónica</label>
                   <input type="text" class="form-control" name="CASILLA_ELECTRONICA">
                </div>
              </div>

              <h6 class="section-head">Familia</h6>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Cónyuge / Conviviente</label>
                  <input type="text" class="form-control" name="C_NYUGE_O_CONVIVIENTE">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Nombres de Hijos</label>
                  <textarea class="form-control" name="NOMBRES_DE_HIJOS" rows="1"></textarea>
                </div>
              </div>

              <h6 class="section-head"><i class="fa-brands fa-google-drive text-success"></i> Archivos Digitales</h6>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Link Foto del Recibo</label>
                  <input type="text" class="form-control" name="FOTO_DE_RECIBO" placeholder="Pegue el enlace aquí">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Link Ficha Personal</label>
                  <input type="text" class="form-control" name="FOTO_DE_FICHA_PERSONAL" placeholder="Pegue el enlace aquí">
                </div>
              </div>

               <h6 class="section-head">Observación</h6>
               <div class="col-12">
                   <textarea class="form-control" name="OBSERVACION"></textarea>
               </div>
            </div>
          </form>
        </div>
        <div class="modal-footer bg-white">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-verde" onclick="saveData()">
              <i class="fa-solid fa-save"></i> Guardar Todo
          </button>
        </div>
      </div>
      
    </div>
  </div>

  <div id="loading"><div class="spinner-border text-success" role="status"></div></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const API = 'api_editor.php';
    let currentPage = 1;
    let totalPages = 1;
    let currentLimit = 50; 
    let searchTimeout = null; 

    document.addEventListener('DOMContentLoaded', () => {
        load(1);
        document.getElementById('q').focus();
    });

    document.getElementById('q').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            load(1);
        }, 300);
    });

    document.getElementById('pageInput').addEventListener('change', (e) => {
        let val = parseInt(e.target.value);
        if(val < 1) val = 1;
        if(val > totalPages) val = totalPages;
        load(val);
    });

    document.getElementById('btnPrev').addEventListener('click', () => { if(currentPage>1) load(currentPage-1) });
    document.getElementById('btnNext').addEventListener('click', () => { if(currentPage<totalPages) load(currentPage+1) });
    document.getElementById('btnFirst').addEventListener('click', () => load(1));
    document.getElementById('btnLast').addEventListener('click', () => load(totalPages));

    function toggleShowAll() {
        if(currentLimit === 'all') {
            currentLimit = 50; 
            document.getElementById('btnShowAll').innerHTML = '<i class="fa-solid fa-list me-1"></i> Ver Todos';
            document.getElementById('btnShowAll').classList.remove('active');
        } else {
            if(!confirm('Cargar TODOS los registros puede tomar unos segundos. ¿Continuar?')) return;
            currentLimit = 'all';
            document.getElementById('btnShowAll').innerHTML = '<i class="fa-solid fa-compress me-1"></i> Ver Paginado';
            document.getElementById('btnShowAll').classList.add('active');
        }
        load(1);
    }

    async function load(page) {
      document.getElementById('loading').style.display = 'flex';
      const q = document.getElementById('q').value.trim();
      
      try {
        const res = await fetch(`${API}?action=list&page=${page}&limit=${currentLimit}&q=${encodeURIComponent(q)}&_t=${new Date().getTime()}`);
        const data = await res.json();
        
        window.currentRows = data.rows || [];
        
        currentPage = parseInt(data.page);
        totalPages = parseInt(data.pages);
        
        renderTable(data);
        updatePaginationUI(data);

      } catch(e) {
        console.error(e);
        alert('Error conectando con servidor.');
      } finally {
        document.getElementById('loading').style.display = 'none';
      }
    }

    function updatePaginationUI(data) {
        document.getElementById('pageInput').value = currentPage;
        document.getElementById('pageInput').max = totalPages;
        document.getElementById('totalPagesLabel').innerText = `de ${totalPages}`;
        document.getElementById('infoRows').innerText = `(${data.total} registros)`;
        
        document.getElementById('btnPrev').disabled = (currentPage <= 1);
        document.getElementById('btnFirst').disabled = (currentPage <= 1);
        document.getElementById('btnNext').disabled = (currentPage >= totalPages);
        document.getElementById('btnLast').disabled = (currentPage >= totalPages);
        
        const isAll = (currentLimit === 'all');
        document.getElementById('pageInput').disabled = isAll;
        if(isAll) {
             document.getElementById('btnPrev').disabled = true;
             document.getElementById('btnNext').disabled = true;
        }
    }

    function renderTable(data) {
      const tbody = document.getElementById('tbody');
      tbody.innerHTML = '';
      const rows = data.rows || [];
      
      if(rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">Sin resultados</td></tr>';
        return;
      }

      rows.forEach(row => {
        const st = (row.ESTADO || '').toUpperCase();
        let badgeClass = st.includes('FALLECIDO') ? 'bg-fallecido' : 'bg-vivo';
        let badgeText = st.includes('FALLECIDO') ? 'FALLECIDO' : 'VIVO';

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="ps-4 fw-bold text-secondary">${row.COLEGIATURA || ''}</td>
          <td><div class="nombre-agremiado">${row.NOMBRE_DEL_AGREMIADO || '---'}</div></td>
          <td class="font-monospace text-muted">${row.DNI || ''}</td>
          <td><span class="badge badge-estado ${badgeClass}">${badgeText}</span></td>
          <td class="small text-muted">
             ${row.N_MERO_DE_CELULAR ? `<div>${row.N_MERO_DE_CELULAR}</div>` : ''}
          </td>
          <td class="text-end pe-4">
            <button class="btn btn-sm btn-light border text-success" onclick="openEdit(${row.id})"><i class="fa-solid fa-pen"></i></button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }

    function openEdit(id) {
      const row = window.currentRows.find(r => r.id == id);
      if(!row) return;

      document.getElementById('formEditor').reset();
      document.getElementById('field_id').value = row.id;

      // DESBLOQUEAR la colegiatura porque estamos EDITANDO
      const colInput = document.getElementById('inputColegiatura');
      if(colInput) {
          colInput.readOnly = false;
          colInput.classList.remove('bg-light');
      }

      const rowUpper = {};
      Object.keys(row).forEach(k => rowUpper[k.toUpperCase()] = row[k]);

      const inputs = document.querySelectorAll('#formEditor [name]');
      
      inputs.forEach(input => {
          const name = input.name.toUpperCase();
          if(name === 'ID') return;

          let val = rowUpper[name];
          
          if(val !== undefined && val !== null) {
              if(input.type === 'date') {
                  if(val.startsWith('0000')) val = ''; 
                  else if(val.length > 10) val = val.substring(0, 10);
              }
              input.value = val;
          }
      });
      
      const modalFunc = new bootstrap.Modal(document.getElementById('modalEdicion'));
      modalFunc.show();
    }

    async function prepInsert() {
      document.getElementById('formEditor').reset();
      document.getElementById('field_id').value = '';
      
      const select = document.querySelector('select[name="ESTADO"]');
      if(select) select.value = 'VIVO';

      // Llamada a la API para obtener la siguiente Colegiatura
      try {
          const res = await fetch(API + '?action=get_next_col');
          const data = await res.json();
          if(data.ok && data.next) {
              const colInput = document.getElementById('inputColegiatura');
              if(colInput) {
                  colInput.value = data.next;
                  // BLOQUEO PARA EVITAR DUPLICADOS Y ERRORES
                  colInput.readOnly = true; 
                  colInput.classList.add('bg-light');
              }
          }
      } catch(e) {
          console.error("No se pudo calcular la colegiatura", e);
      }

      const modalFunc = new bootstrap.Modal(document.getElementById('modalEdicion'));
      modalFunc.show();
    }

    async function saveData() {
      const form = document.getElementById('formEditor');
      if (!form.checkValidity()) { form.reportValidity(); return; }

      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());
      const id = data.id;

      if(!data.NOMBRE_DEL_AGREMIADO.trim()) return alert('Nombre obligatorio');

      document.getElementById('loading').style.display = 'flex';

      const payload = new FormData();
      payload.append('action', id ? 'update' : 'insert');
      if(id) payload.append('id', id);
      payload.append('json', JSON.stringify(data));
      payload.append('nullIfEmpty', '1');

      try {
        const res = await fetch(API, { method: 'POST', body: payload });
        const json = await res.json();
        if(json.ok) {
           const modalEl = document.getElementById('modalEdicion');
           const modalInstance = bootstrap.Modal.getInstance(modalEl);
           modalInstance.hide();
           load(currentPage);
           alert(id ? 'Guardado' : 'Registrado');
        } else {
           alert('Error: ' + (json.error || 'Desconocido'));
        }
      } catch(e) { console.error(e); alert('Error de conexión'); } 
      finally { document.getElementById('loading').style.display = 'none'; }
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