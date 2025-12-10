<?php
// editor.php
// IMPORTANTE PARA PRODUCCION:
// session_start();
// if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit; }
require_once "conexion.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administración · ICAS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/logo.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root {
        --primary-color: #E79E1E; /* Naranja corporativo */
        --secondary-color: #0D3B66; /* Azul oscuro corporativo */
        --bg-color: #F4F7F6; /* Fondo gris claro */
        --text-dark: #333333;
        --text-light: #ffffff;
    }
    body { 
        font-family: 'Poppins', sans-serif; 
        background-color: var(--bg-color);
        color: var(--text-dark);
    }
    
    /* Navbar Styles */
    .navbar-custom { 
        background-color: var(--secondary-color); 
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .navbar-brand span {
        font-weight: 600;
        color: var(--text-light);
    }

    /* Card & Table Styles */
    .card-custom { 
        border: none; 
        border-radius: 15px; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.05); 
        background: white;
    }
    .table-hover tbody tr:hover { background-color: #f0f2f5; }
    .table thead th { 
        background-color: #f8f9fa; 
        color: var(--secondary-color); 
        font-weight: 600;
        border-bottom: 2px solid #e9ecef;
    }
    
    /* Buttons & Badges */
    .btn-primary-custom { 
        background-color: var(--primary-color); 
        border-color: var(--primary-color); 
        color: var(--text-light); 
        font-weight: 500;
    }
    .btn-primary-custom:hover { background-color: #cf8d1a; color: var(--text-light); }
    
    .badge-estado { padding: 0.5em 0.8em; border-radius: 30px; font-size: 0.8em; font-weight: 600; letter-spacing: 0.5px; }
    .bg-vivo { background-color: #dcfce7; color: #166534; } 
    .bg-fallecido { background-color: #fee2e2; color: #991b1b; } 
    .bg-suspendido { background-color: #fef3c7; color: #92400e; }
    
    .action-btn { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }
    .action-btn:hover { transform: translateY(-2px); }
    
    /* Form Styles inside Modal */
    .form-label { font-weight: 500; color: var(--secondary-color); font-size: 0.9rem; margin-bottom: 0.3rem;}
    .form-control, .form-select { border-radius: 8px; border-color: #dee2e6; padding: 0.6rem 0.8rem; }
    .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(231, 158, 30, 0.2); }
    .modal-header { background-color: var(--secondary-color); color: white; border-top-left-radius: 15px; border-top-right-radius: 15px; }
    .modal-content { border-radius: 15px; border: none; }
    .modal-title { font-weight: 600; }
    .btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }
    .form-section-title { color: var(--primary-color); font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; border-bottom: 2px solid #eee; padding-bottom: 0.5rem; margin-top: 1.5rem; }
    
    /* Utility */
    /* FIX: Centrado vertical de la paginación */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center; /* Esto asegura el centrado vertical */
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    .pagination-info { margin-bottom: 0; font-weight: 500; color: #777; }

    #loading { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.8); z-index:9999; display:flex; align-items:center; justify-content:center; backdrop-filter: blur(2px); }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center gap-3" href="#">
        <img src="assets/logo.png" width="45" height="45" class="d-inline-block align-text-top rounded-circle bg-white p-1" alt="Logo ICAS">
        <span>Administración ICAS</span>
      </a>
      <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-sm btn-outline-light" onclick="location.reload()" title="Actualizar datos"><i class="fa-solid fa-rotate-right"></i></button>
        <a href="index.php" class="btn btn-sm btn-danger fw-medium px-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-right-from-bracket"></i> Salir
        </a>
      </div>
    </div>
  </nav>

  <div class="container-fluid px-4 pb-5">
    
    <div class="card card-custom mb-4">
      <div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between p-4">
        <div class="d-flex gap-2 align-items-center flex-grow-1">
          <div class="input-group shadow-sm" style="max-width: 500px;">
            <span class="input-group-text bg-white border-end-0 ps-3"><i class="fa-solid fa-search text-muted"></i></span>
            <input type="text" id="q" class="form-control border-start-0 ps-2 py-2" placeholder="Buscar por nombre, DNI o colegiatura...">
          </div>
          <button class="btn btn-primary-custom px-4 py-2 shadow-sm" id="btnBuscar">Buscar</button>
        </div>
        <button class="btn btn-success text-white fw-medium px-4 py-2 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalEdicion" onclick="prepInsert()">
          <i class="fa-solid fa-user-plus fa-lg"></i> Nuevo Agremiado
        </button>
      </div>
    </div>

    <div class="card card-custom overflow-hidden shadow">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tablaAgremiados">
          <thead>
            <tr>
              <th class="ps-4">Colegiatura</th>
              <th>Agremiado</th>
              <th>DNI</th>
              <th>Estado</th>
              <th>Contacto Directo</th>
              <th class="text-end pe-4">Acciones</th>
            </tr>
          </thead>
          <tbody id="tbody">
            </tbody>
        </table>
      </div>
      
      <div class="card-footer bg-white border-top-0 pagination-container px-4">
        <p class="pagination-info small" id="infoPaginacion">Cargando...</p>
        <div class="btn-group shadow-sm">
          <button class="btn btn-outline-secondary btn-sm px-3" id="btnPrev"><i class="fa-solid fa-chevron-left"></i> Anterior</button>
          <button class="btn btn-outline-secondary btn-sm px-3" id="btnNext">Siguiente <i class="fa-solid fa-chevron-right"></i></button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEdicion" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"> <div class="modal-content shadow-lg">
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2" id="modalTitle">
              <i class="fa-solid fa-user-pen"></i> Editar Agremiado
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4" style="background-color: #f8f9fa;">
          <form id="formEditor">
            <input type="hidden" name="id" id="field_id">
            
            <div class="row g-3">
              
              <div class="col-12"><h6 class="form-section-title"><i class="fa-solid fa-building-columns me-2"></i>Datos de Colegiatura</h6></div>
              
              <div class="col-md-3">
                <label class="form-label">N° Colegiatura <span class="text-danger">*</span></label>
                <input type="number" class="form-control fw-bold" name="COLEGIATURA" id="field_COLEGIATURA" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Estado Actual <span class="text-danger">*</span></label>
                <select class="form-select fw-medium" name="ESTADO" id="field_ESTADO" required>
                  <option value="VIVO">VIVO (Habilitado)</option>
                  <option value="FALLECIDO">FALLECIDO</option>
                  <option value="SUSPENDIDO">SUSPENDIDO</option>
                  <option value="RENUNCIA">RENUNCIA</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Fecha de Incorporación</label>
                <input type="date" class="form-control" name="FECHA_DE_INCORPORACI_N" id="field_FECHA_DE_INCORPORACI_N">
              </div>
              <div class="col-md-3">
                  <label class="form-label">URL Foto de Perfil</label>
                  <input type="text" class="form-control" name="IMAGEN_PERFIL" id="field_IMAGEN_PERFIL" placeholder="https://...">
              </div>

              <div class="col-12"><h6 class="form-section-title"><i class="fa-solid fa-user me-2"></i>Datos Personales</h6></div>

              <div class="col-md-8">
                <label class="form-label">Apellidos y Nombres Completos <span class="text-danger">*</span></label>
                <input type="text" class="form-control fw-bold" name="NOMBRE_DEL_AGREMIADO" id="field_NOMBRE_DEL_AGREMIADO" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">DNI / Documento <span class="text-danger">*</span></label>
                <input type="text" class="form-control fw-bold" name="DNI" id="field_DNI" maxlength="15" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Fecha de Nacimiento</label>
                <input type="date" class="form-control" name="FECHA_DE_NACIMIENTO" id="field_FECHA_DE_NACIMIENTO">
              </div>
               <div class="col-md-8">
                <label class="form-label">Lugar de Nacimiento</label>
                <input type="text" class="form-control" name="LUGAR_DE_NACIMIENTO" id="field_LUGAR_DE_NACIMIENTO">
              </div>


              <div class="col-12"><h6 class="form-section-title"><i class="fa-solid fa-address-book me-2"></i>Contacto y Ubicación</h6></div>
              
              <div class="col-md-4">
                <label class="form-label"><i class="fa-solid fa-mobile-screen me-1"></i> Celular</label>
                <input type="text" class="form-control" name="N_MERO_DE_CELULAR" id="field_N_MERO_DE_CELULAR">
              </div>
              <div class="col-md-8">
                <label class="form-label"><i class="fa-regular fa-envelope me-1"></i> Correo Electrónico</label>
                <input type="email" class="form-control" name="CORREO" id="field_CORREO">
              </div>
              <div class="col-12">
                <label class="form-label"><i class="fa-solid fa-map-location-dot me-1"></i> Domicilio Real Actual</label>
                <input type="text" class="form-control" name="DOMICILIO_REAL" id="field_DOMICILIO_REAL">
              </div>

              <div class="col-12"><h6 class="form-section-title"><i class="fa-solid fa-people-roof me-2"></i>Información Familiar</h6></div>
              <div class="col-md-6">
                  <label class="form-label">Cónyuge o Conviviente</label>
                  <input type="text" class="form-control" name="C_NYUGE_O_CONVIVIENTE" id="field_C_NYUGE_O_CONVIVIENTE">
              </div>
               <div class="col-md-6">
                  <label class="form-label">Nombres de los Hijos</label>
                  <textarea class="form-control" name="NOMBRES_DE_HIJOS" id="field_NOMBRES_DE_HIJOS" rows="2" placeholder="Separe los nombres con comas..."></textarea>
              </div>

              <div class="col-12"><h6 class="form-section-title"><i class="fa-regular fa-clipboard me-2"></i>Observaciones Adicionales</h6></div>
              <div class="col-12">
                <textarea class="form-control" name="OBSERVACION" id="field_OBSERVACION" rows="3"></textarea>
              </div>

            </div> </form>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary px-4 fw-medium" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary-custom px-4 py-2 shadow-sm d-flex align-items-center gap-2" onclick="saveData()">
              <i class="fa-solid fa-floppy-disk"></i> Guardar Datos
          </button>
        </div>
      </div>
    </div>
  </div>

  <div id="loading">
      <div class="text-center">
          <div class="spinner-border text-primary mb-2" role="status" style="width: 3rem; height: 3rem;"></div>
          <div class="fw-bold text-secondary">Procesando...</div>
      </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    const API = 'api_editor.php';
    let currentPage = 1;
    let currentData = [];
    const modalElement = document.getElementById('modalEdicion');
    const modal = new bootstrap.Modal(modalElement);

    // --- INICIALIZACIÓN ---
    document.addEventListener('DOMContentLoaded', () => {
        load(1);
        // Enfocar el input de búsqueda al cargar
        document.getElementById('q').focus();
    });

    // --- EVENTOS ---
    document.getElementById('btnBuscar').addEventListener('click', () => load(1));
    document.getElementById('q').addEventListener('keydown', e => { if(e.key==='Enter') load(1) });
    document.getElementById('btnPrev').addEventListener('click', () => { if(currentPage>1) load(currentPage-1) });
    document.getElementById('btnNext').addEventListener('click', () => load(currentPage+1));

    // Limpiar validaciones al cerrar modal
    modalElement.addEventListener('hidden.bs.modal', function () {
        document.getElementById('formEditor').classList.remove('was-validated');
    });

    // --- FUNCIONES PRINCIPALES ---

    async function load(page) {
      document.getElementById('loading').style.display = 'flex';
      const q = document.getElementById('q').value.trim();
      const url = `${API}?action=list&page=${page}&q=${encodeURIComponent(q)}`;
      
      try {
        const res = await fetch(url);
        if(!res.ok) throw new Error('Error en la red');
        const data = await res.json();
        renderTable(data);
        currentPage = page;
        
        // Actualizar estado de botones de paginación
        document.getElementById('btnPrev').disabled = (currentPage <= 1);
        document.getElementById('btnNext').disabled = (currentPage >= data.pages);

      } catch(e) {
        console.error(e);
        document.getElementById('tbody').innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4"><i class="fa-solid fa-triangle-exclamation me-2"></i> Error al cargar datos. Intente recargar.</td></tr>`;
      } finally {
        document.getElementById('loading').style.display = 'none';
      }
    }

    function renderTable(data) {
      const tbody = document.getElementById('tbody');
      tbody.innerHTML = '';
      currentData = data.rows || [];
      
      if(currentData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted fs-5 fw-light"><i class="fa-solid fa-magnifying-glass fa-2x mb-3 d-block opacity-50"></i>No se encontraron resultados</td></tr>';
        document.getElementById('infoPaginacion').innerText = 'Sin resultados';
        return;
      }

      currentData.forEach(row => {
        let estadoClass = 'bg-secondary text-white';
        const estadoUpper = (row.ESTADO || '').toUpperCase();
        if(estadoUpper.includes('VIVO')) estadoClass = 'bg-vivo';
        else if(estadoUpper.includes('FALLECIDO')) estadoClass = 'bg-fallecido';
        else if(estadoUpper.includes('SUSPENDIDO')) estadoClass = 'bg-suspendido';

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="ps-4"><span class="fw-bold text-secondary">N° ${row.COLEGIATURA || '---'}</span></td>
          <td>
            <div class="d-flex align-items-center">
                 <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width:40px; height:40px; color: var(--secondary-color);">
                    <i class="fa-solid fa-user"></i>
                 </div>
                 <div>
                    <div class="fw-bold text-dark">${row.NOMBRE_DEL_AGREMIADO || 'Sin Nombre'}</div>
                    ${row.FECHA_DE_INCORPORACI_N ? `<small class="text-muted"><i class="fa-regular fa-calendar me-1"></i>Inc: ${formatDate(row.FECHA_DE_INCORPORACI_N)}</small>` : ''}
                 </div>
            </div>
          </td>
          <td class="fw-medium">${row.DNI || '-'}</td>
          <td><span class="badge badge-estado ${estadoClass}">${row.ESTADO || 'Desconocido'}</span></td>
          <td class="small text-muted">
            ${row.N_MERO_DE_CELULAR ? `<div><i class="fa-solid fa-mobile-screen me-2 text-success"></i> ${row.N_MERO_DE_CELULAR}</div>` : ''}
            ${row.CORREO ? `<div><i class="fa-regular fa-envelope me-2 text-primary"></i> ${row.CORREO}</div>` : '-'}
          </td>
          <td class="text-end pe-4">
            <button class="btn btn-primary-custom action-btn shadow-sm" onclick="openEdit(${row.id})" title="Editar Agremiado Completo">
                <i class="fa-solid fa-pen-to-square"></i>
            </button>
          </td>
        `;
        tbody.appendChild(tr);
      });

      // Actualizar info de paginación (Centrado vertical arreglado en CSS)
      const total = data.total || 0;
      const pages = data.pages || 0;
      if (total > 0) {
          document.getElementById('infoPaginacion').innerHTML = `<i class="fa-solid fa-list-ol me-2"></i>Página <b>${data.page}</b> de <b>${pages}</b> (Total: ${total} registros)`;
      } else {
          document.getElementById('infoPaginacion').innerText = '';
      }
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        try {
            const [year, month, day] = dateString.split('-');
            return `${day}/${month}/${year}`;
        } catch (e) { return dateString; }
    }

    function openEdit(id) {
      const row = currentData.find(r => r.id == id);
      if(!row) return;
      
      document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-pen me-2"></i> Editar Agremiado';
      document.getElementById('formEditor').reset();
      
      // Llenar TODOS los campos automáticamente
      for (const key in row) {
        const input = document.getElementById('field_' + key);
        if (input) {
            // Manejo especial para fechas (asegurar formato YYYY-MM-DD para el input type=date)
            if(input.type === 'date' && row[key]) {
                // A veces la BD devuelve timestamp completo, cortamos solo la fecha
                input.value = row[key].substring(0, 10);
            } else {
                input.value = row[key];
            }
        }
      }
      
      // Asegurar el ID
      document.getElementById('field_id').value = row.id;
      
      modal.show();
    }

    function prepInsert() {
      document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-plus me-2"></i> Nuevo Agremiado';
      document.getElementById('formEditor').reset();
      document.getElementById('field_id').value = '';
      // Establecer estado por defecto
      document.getElementById('field_ESTADO').value = 'VIVO';
    }

    async function saveData() {
      const form = document.getElementById('formEditor');
      
      // Validación básica de HTML5
      if (!form.checkValidity()) {
          form.classList.add('was-validated');
          alert('Por favor, complete los campos obligatorios marcados en rojo.');
          return;
      }

      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());
      const id = data.id;
      
      // Validaciones JS adicionales (opcional)
      if(!data.NOMBRE_DEL_AGREMIADO.trim()) return alert('El nombre es obligatorio');
      if(!data.COLEGIATURA.trim()) return alert('La colegiatura es obligatoria');

      document.getElementById('loading').style.display = 'flex';
      
      const action = id ? 'update' : 'insert';
      const payload = new FormData();
      payload.append('action', action);
      if(id) payload.append('id', id);
      payload.append('json', JSON.stringify(data));
      payload.append('nullIfEmpty', '1'); // Importante para convertir '' a NULL en BD

      try {
        const res = await fetch(API, { method: 'POST', body: payload });
        const json = await res.json();
        
        if(json.ok) {
          modal.hide();
          load(currentPage);
          // Mostrar toast o alerta más elegante en el futuro
          alert(id ? 'Agremiado actualizado correctamente.' : 'Agremiado registrado correctamente.');
        } else {
          let errorMsg = json.error || 'Error desconocido';
          if(errorMsg.includes('Duplicate entry')) {
              if(errorMsg.includes('uk_dni')) errorMsg = 'Error: El DNI ya está registrado para otra persona.';
              else if(errorMsg.includes('uk_colegiatura')) errorMsg = 'Error: El N° de Colegiatura ya existe.';
          }
          alert(errorMsg);
        }
      } catch(e) {
        console.error(e);
        alert('Error de conexión con el servidor.');
      } finally {
        document.getElementById('loading').style.display = 'none';
      }
    }
  </script>
</body>
</html>