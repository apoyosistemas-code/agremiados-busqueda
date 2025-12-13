<?php
require_once "conexion.php";
require_once "auth.php";

// SEGURIDAD: Solo MASTER puede ver esto
if ($_SESSION['user_role'] !== 'MASTER') {
    header("Location: index.php");
    exit;
}

// Obtener últimos 100 movimientos
$sql = "SELECT * FROM auditoria ORDER BY id DESC LIMIT 100";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Auditoría · ICAS</title>
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
        padding-top: 110px; /* Mismo espaciado superior */
    }
    
    /* Navbar Unificada */
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

    /* Tarjeta estilo panel */
    .card-custom { 
        border: none; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); 
        background: white; margin-bottom: 20px;
    }
    
    .badge-action { font-size: 0.75rem; padding: 6px 10px; border-radius: 4px; }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container-fluid px-4 animate-container">
      <a class="navbar-brand" href="index.php">
        <img src="assets/logo.png" alt="Logo">
        <span>Registro de Auditoría</span>
      </a>
      <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 fw-medium px-3">
            <i class="fa-solid fa-arrow-left"></i> Volver al Inicio
        </a>
      </div>
    </div>
  </nav>

  <div class="container-fluid px-4 animate-container">
    <div class="card card-custom p-4">
        <h5 class="mb-4 text-secondary fw-bold border-bottom pb-2">
            <i class="fa-solid fa-shield-halved me-2"></i> Últimos Movimientos del Sistema
        </h5>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 text-secondary text-uppercase" style="font-size: 0.8rem;">ID</th>
                        <th class="text-secondary text-uppercase" style="font-size: 0.8rem;">Usuario</th>
                        <th class="text-secondary text-uppercase" style="font-size: 0.8rem;">Acción</th>
                        <th class="text-secondary text-uppercase" style="font-size: 0.8rem; width: 40%;">Detalle</th>
                        <th class="text-secondary text-uppercase" style="font-size: 0.8rem;">IP</th>
                        <th class="text-secondary text-uppercase" style="font-size: 0.8rem;">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): 
                        $cls='bg-secondary';
                        if($row['accion']=='LOGIN') $cls='bg-success';
                        if($row['accion']=='UPDATE') $cls='bg-warning text-dark';
                        if($row['accion']=='INSERT') $cls='bg-primary';
                        if(strpos($row['accion'], 'DEL')!==false) $cls='bg-danger';
                        if(strpos($row['accion'], 'USER')!==false) $cls='bg-info text-dark';
                    ?>
                    <tr>
                        <td class="ps-3 text-muted small">#<?= $row['id'] ?></td>
                        <td class="fw-bold" style="color: var(--color-verde);"><?= htmlspecialchars($row['usuario_nombre']) ?></td>
                        <td><span class="badge <?= $cls ?> badge-action"><?= $row['accion'] ?></span></td>
                        <td class="text-secondary small"><?= htmlspecialchars($row['detalle']) ?></td>
                        <td class="small font-monospace text-muted"><?= $row['ip'] ?></td>
                        <td class="small text-muted"><?= $row['fecha'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
  </div>

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