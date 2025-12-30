<?php
// cambiar_clave.php
require_once "conexion.php";

$usuario = 'admin';
$nueva_clave = 'CAJ25@as';

// 1. Generar el código encriptado (Hash)
$hash = password_hash($nueva_clave, PASSWORD_DEFAULT);

// 2. Actualizar en la Base de Datos
$sql = "UPDATE usuarios SET password = ? WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hash, $usuario);

if ($stmt->execute()) {
    echo "<h1>✅ Contraseña Actualizada</h1>";
    echo "<p>El usuario <b>$usuario</b> ahora tiene la contraseña: <b>$nueva_clave</b></p>";
    echo "<p>Código encriptado guardado en BD: <br><code>$hash</code></p>";
    echo "<br><a href='login.php'>Ir a Iniciar Sesión</a>";
} else {
    echo "<h1>❌ Error</h1>";
    echo $conn->error;
}
?>