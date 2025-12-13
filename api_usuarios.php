<?php
require_once "conexion.php";
require_once "auth.php";

header('Content-Type: application/json');

// SOLO MASTER PUEDE ENTRAR
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'MASTER') {
    echo json_encode(['ok'=>false, 'error'=>'No autorizado']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

// LISTAR USUARIOS
if ($action === 'list') {
    // Solo mostramos ID, Usuario, Rol y Nombre (Excluimos password por seguridad)
    $res = $conn->query("SELECT id, usuario, rol, nombre_completo FROM usuarios");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
}

// CREAR USUARIO
if ($action === 'create') {
    $user = trim($_POST['usuario']);
    $pass = $_POST['password'];
    $name = trim($_POST['nombre']);
    $rol  = $_POST['rol'];
    
    // Validar duplicados
    $check = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
    $check->bind_param("s", $user);
    $check->execute();
    if($check->get_result()->num_rows > 0){
        echo json_encode(['ok'=>false, 'error'=>'El usuario ya existe']);
        exit;
    }
    
    // Encriptar contraseña
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, nombre_completo, rol, estado) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("ssss", $user, $hash, $name, $rol);
    
    if($stmt->execute()){
        registrar_auditoria($conn, 'USER_ADD', "Creó usuario: $user ($rol)");
        echo json_encode(['ok'=>true]);
    } else {
        echo json_encode(['ok'=>false, 'error'=>'Error en BD: ' . $conn->error]);
    }
}

// ELIMINAR USUARIO (AHORA SÍ BORRA DE LA BD)
if ($action === 'delete') {
    $id = intval($_POST['id']);
    
    // 1. Protección: No borrarse a sí mismo
    if($id === $_SESSION['user_id']) {
        echo json_encode(['ok'=>false, 'error'=>'No puedes eliminar tu propia cuenta mientras la usas.']);
        exit;
    }

    // 2. Protección: No borrar al Super Admin (ID 1 o usuario 'admin')
    // Asumimos que el primer usuario creado (ID 1) es el intocable
    if($id === 1) {
        echo json_encode(['ok'=>false, 'error'=>'No se puede eliminar al Administrador Principal.']);
        exit;
    }

    // 3. Obtener nombre antes de borrar para el log
    $qry = $conn->query("SELECT usuario FROM usuarios WHERE id=$id");
    $uName = ($row = $qry->fetch_assoc()) ? $row['usuario'] : 'Desconocido';

    // 4. EJECUTAR EL BORRADO REAL (DELETE)
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()){
        registrar_auditoria($conn, 'USER_DEL', "Eliminó definitivamente al usuario: $uName (ID $id)");
        echo json_encode(['ok'=>true]);
    } else {
        echo json_encode(['ok'=>false, 'error'=>'Error al eliminar: ' . $conn->error]);
    }
}
?>