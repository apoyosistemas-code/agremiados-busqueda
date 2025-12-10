<?php
require_once "conexion.php";

// Si ya tiene sesión, va al index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['usuario']);
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, usuario, password, rol, nombre_completo FROM usuarios WHERE usuario = ? AND estado = 1");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        // CORRECCIÓN DE SEGURIDAD:
        // Se eliminó "|| $pass === 'admin123'". Ahora SOLO valida el hash real.
        if (password_verify($pass, $row['password'])) { 
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['usuario'];
            $_SESSION['user_role'] = $row['rol'];
            $_SESSION['user_full'] = $row['nombre_completo'];
            
            registrar_auditoria($conn, 'LOGIN', 'Ingreso al sistema');
            header("Location: index.php");
            exit;
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso Intranet · ICAJ</title>
  <link rel="icon" type="image/png" href="assets/EstrellaCaj.png">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #ffffff;
        display: flex; align-items: center; justify-content: center;
        height: 100vh; margin: 0;
    }
    .login-hero {
        text-align: center; max-width: 400px; width: 100%; padding: 20px;
    }
    .hero-logo { width: 140px; margin-bottom: 20px; }
    .hero-title { font-size: 1.5rem; color: #12503a; margin-bottom: 10px; font-weight: 600; }
    .hero-subtitle { color: #666; margin-bottom: 30px; font-size: 0.95rem; }
    
    .login-form input {
        width: 100%; padding: 14px 15px; margin-bottom: 15px;
        border: 2px solid #e5e7eb; border-radius: 12px;
        font-family: 'Poppins'; font-size: 1rem; outline: none; transition: 0.3s;
        box-sizing: border-box; /* Importante para que no se salga del ancho */
    }
    .login-form input:focus { border-color: #12503a; box-shadow: 0 0 0 4px rgba(18,80,58,0.1); }
    
    .btn-login {
        width: 100%; padding: 14px; background: #12503a; color: white;
        border: none; border-radius: 12px; font-weight: 600; font-size: 1rem;
        cursor: pointer; transition: 0.3s;
    }
    .btn-login:hover { background: #0e3f2d; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(18,80,58,0.2); }
    
    .error-msg { 
        background: #fee2e2; color: #991b1b; padding: 12px; 
        border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px; 
        display:flex; align-items:center; justify-content:center; gap:8px;
    }
  </style>
</head>
<body>

  <div class="login-hero">
    <img class="hero-logo" src="assets/logo.png" alt="Logo ICAJ">
    <h1 class="hero-title">Intranet Administrativa</h1>
    <p class="hero-subtitle">Ingrese sus credenciales de acceso</p>

    <?php if($error): ?>
        <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="login-form">
        <input type="text" name="usuario" placeholder="Usuario" required autofocus autocomplete="off">
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit" class="btn-login">Iniciar Sesión</button>
    </form>
  </div>

</body>
</html>