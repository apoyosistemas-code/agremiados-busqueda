<?php
require_once "conexion.php";
require_once "auth.php";

header('Content-Type: application/json');

// SOLO MASTER
if ($_SESSION['user_role'] !== 'MASTER') {
    echo json_encode(['ok'=>false, 'error'=>'No autorizado']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'list') {
    $res = $conn->query("SELECT id, usuario, rol, nombre_completo FROM usuarios WHERE estado=1");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
}

if ($action === 'create') {
    $user = trim($_POST['usuario']);
    $pass = $_POST['password'];
    $name = trim($_POST['nombre']);
    $rol  = $_POST['rol'];
    
    // Hash seguro
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, nombre_completo, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $user, $hash, $name, $rol);
    
    if($stmt->execute()){
        registrar_auditoria($conn, 'USER_ADD', "Creó usuario: $user ($rol)");
        echo json_encode(['ok'=>true]);
    } else {
        echo json_encode(['ok'=>false, 'error'=>'El usuario ya existe o error BD']);
    }
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    if($id === 1 || $id === $_SESSION['user_id']) {
        echo json_encode(['ok'=>false, 'error'=>'No puedes borrarte a ti mismo ni al admin principal']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE usuarios SET estado=0 WHERE id=?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        registrar_auditoria($conn, 'USER_DEL', "Eliminó usuario ID: $id");
        echo json_encode(['ok'=>true]);
    }
}
?>