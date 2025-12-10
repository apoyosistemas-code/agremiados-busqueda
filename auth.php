<?php
// auth.php - CANDADO DE SEGURIDAD
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si NO existe la sesión, patada al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>