<?php
// instalador_db.php
// EJECUTAR EN LA NUEVA COMPUTADORA PARA CREAR TODO DESDE CERO
require_once "conexion.php";

echo "<h1>⚙️ Instalación de Base de Datos (Sistema ICAJ)</h1>";

try {
    // 1. CREAR TABLA USUARIOS
    $sqlUsuarios = "CREATE TABLE IF NOT EXISTS `usuarios` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `usuario` varchar(50) NOT NULL,
      `password` varchar(255) NOT NULL,
      `rol` enum('MASTER','ADMIN') NOT NULL DEFAULT 'ADMIN',
      `nombre_completo` varchar(100) DEFAULT NULL,
      `estado` tinyint(1) DEFAULT 1,
      `creado_en` datetime DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `usuario` (`usuario`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if($conn->query($sqlUsuarios)) echo "<p>✅ Tabla 'usuarios' creada/verificada.</p>";
    else throw new Exception("Error usuarios: " . $conn->error);

    // 2. CREAR TABLA AUDITORIA
    $sqlAudit = "CREATE TABLE IF NOT EXISTS `auditoria` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `usuario_id` int(11) NOT NULL,
      `usuario_nombre` varchar(50) NOT NULL,
      `accion` varchar(50) NOT NULL,
      `detalle` text DEFAULT NULL,
      `ip` varchar(45) DEFAULT NULL,
      `fecha` datetime DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if($conn->query($sqlAudit)) echo "<p>✅ Tabla 'auditoria' creada/verificada.</p>";
    else throw new Exception("Error auditoria: " . $conn->error);

    // 3. OPTIMIZAR BUSCADOR (INDICE FULLTEXT)
    // Esto es vital para que funcione la búsqueda por nombre en la nueva PC
    $checkIndex = $conn->query("SHOW INDEX FROM agremiados WHERE Key_name = 'idx_busqueda_nombre'");
    if ($checkIndex->num_rows == 0) {
        $conn->query("ALTER TABLE agremiados ADD FULLTEXT INDEX idx_busqueda_nombre (NOMBRE_DEL_AGREMIADO)");
        echo "<p>✅ Índice de búsqueda rápida creado.</p>";
    }

    // 4. CREAR USUARIO ADMIN MAESTRO
    // Aquí definimos la contraseña exacta que pediste
    $user = 'admin';
    $pass = 'CAJ25@as'; 
    
    // Generamos el hash seguro compatible con el login
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $rol = 'MASTER';
    $nombre = 'Super Administrador';

    // Borramos si existía uno viejo para evitar errores
    $conn->query("DELETE FROM usuarios WHERE usuario = 'admin'");
    
    // Insertamos el nuevo
    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, rol, nombre_completo) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $user, $hash, $rol, $nombre);
    
    if ($stmt->execute()) {
        echo "<div style='background:#d1fae5; padding:20px; border-radius:10px; border:1px solid #065f46; color:#065f46; margin-top:20px;'>
                <h3>🎉 ¡Instalación Completa!</h3>
                <p>Las tablas han sido creadas y el usuario maestro configurado.</p>
                <ul>
                    <li><b>Usuario:</b> admin</li>
                    <li><b>Contraseña:</b> CAJ25@as</li>
                </ul>
                <a href='login.php' style='display:inline-block; padding:10px 20px; background:#065f46; color:white; text-decoration:none; border-radius:5px; font-weight:bold;'>Ir al Login</a>
              </div>";
    } else {
        throw new Exception("Error creando admin: " . $conn->error);
    }

} catch (Exception $e) {
    echo "<p style='color:red; font-weight:bold;'>❌ Error crítico: " . $e->getMessage() . "</p>";
    echo "<p>Asegúrate de que la base de datos 'agremiados_db' existe y 'conexion.php' está bien configurado.</p>";
}
?>