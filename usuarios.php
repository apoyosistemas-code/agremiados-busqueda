<?php
require_once "conexion.php";
require_once "auth.php";

// SOLO MASTER PUEDE ENTRAR
if ($_SESSION['user_role'] !== 'MASTER') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Usuarios · ICAJ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/EstrellaCaj.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    body { font-family: 'Poppins', sans-serif; background: #e5e7eb; padding-top: 100px; }
    .navbar-custom { background: #fff; height: 90px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .navbar-brand { font-weight: 600; color: #12503a; font-size: 1.3rem; display: flex; align-items: center; gap: 15px; }
    .navbar-brand img { height: 70px; }
    .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .btn-pink { background: #d63384; color: white; border: none; }
    .btn-pink:hover { background: #a61e61; color: white; }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container-fluid px-4 animate-container">
      <a class="navbar-brand" href="index.php">
        <img src="assets/logo.png" alt="Logo">
        <span>Gestión de Usuarios</span>
      </a>
      <a href="index.php" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-arrow-left"></i> Volver</a>
    </div>
  </nav>

  <div class="container px-4">
    <div class="card card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="m-0 text-secondary"><i class="fa-solid fa-users-gear"></i> Administradores del Sistema</h4>
            <button class="btn btn-pink" data-bs-toggle="modal" data-bs-target="#modalUser">
                <i class="fa-solid fa-plus-circle"></i> Nuevo Usuario
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody id="tbody"></tbody>
            </table>
        </div>
    </div>
  </div>

  <div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-light">
          <h5 class="modal-title">Nuevo Administrador</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formUser">
              <div class="mb-3">
                  <label class="form-label">Usuario (Login)</label>
                  <input type="text" name="usuario" class="form-control" required placeholder="Ej: asistente1">
              </div>
              <div class="mb-3">
                  <label class="form-label">Contraseña</label>
                  <input type="password" name="password" class="form-control" required placeholder="******">
              </div>
              <div class="mb-3">
                  <label class="form-label">Nombre Completo</label>
                  <input type="text" name="nombre" class="form-control" required placeholder="Ej: Juan Perez">
              </div>
              <div class="mb-3">
                  <label class="form-label">Rol</label>
                  <select name="rol" class="form-select">
                      <option value="ADMIN">ADMIN (Normal)</option>
                      <option value="MASTER">MASTER (Total)</option>
                  </select>
              </div>
          </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary w-100" onclick="saveUser()">Guardar Usuario</button>
        </div>
      </div>
      
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', loadUsers);

    async function loadUsers() {
        const res = await fetch('api_usuarios.php?action=list');
        const data = await res.json();
        const tbody = document.getElementById('tbody');
        tbody.innerHTML = '';
        
        data.forEach(u => {
            let badge = u.rol === 'MASTER' ? '<span class="badge bg-warning text-dark">MASTER</span>' : '<span class="badge bg-info">ADMIN</span>';
            tbody.innerHTML += `
                <tr>
                    <td>${u.id}</td>
                    <td class="fw-bold">${u.usuario}</td>
                    <td>${u.nombre_completo}</td>
                    <td>${badge}</td>
                    <td><span class="badge bg-success">ACTIVO</span></td>
                    <td class="text-end">
                        ${u.usuario !== 'admin' ? 
                        `<button class="btn btn-sm btn-outline-danger" onclick="delUser(${u.id}, '${u.usuario}')"><i class="fa-solid fa-trash"></i></button>` 
                        : ''}
                    </td>
                </tr>
            `;
        });
    }

    async function saveUser() {
        const form = document.getElementById('formUser');
        if(!form.checkValidity()) return alert('Complete los campos');
        
        const formData = new FormData(form);
        formData.append('action', 'create');
        
        const res = await fetch('api_usuarios.php', { method: 'POST', body: formData });
        const json = await res.json();
        
        if(json.ok) {
            alert('Usuario creado');
            bootstrap.Modal.getInstance(document.getElementById('modalUser')).hide();
            form.reset();
            loadUsers();
        } else {
            alert('Error: ' + json.error);
        }
    }

    async function delUser(id, name) {
        if(!confirm(`¿Eliminar al usuario ${name}?`)) return;
        
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        const res = await fetch('api_usuarios.php', { method: 'POST', body: formData });
        const json = await res.json();
        if(json.ok) loadUsers();
        else alert('Error: ' + json.error);
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