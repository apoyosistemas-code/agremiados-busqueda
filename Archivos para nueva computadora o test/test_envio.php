<?php
// test_envio.php - SCRIPT DE DIAGNÓSTICO
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'libs/PHPMailer/src/Exception.php';
require 'libs/PHPMailer/src/PHPMailer.php';
require 'libs/PHPMailer/src/SMTP.php';

echo "<h1>Prueba de Conexión SMTP Gmail</h1>";
echo "<pre>"; // Para que se vea ordenado el log

$mail = new PHPMailer(true);

try {
    // 1. Activar depuración detallada
    $mail->SMTPDebug = 3;                      // Nivel 3 muestra TODO
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    
    // Tus credenciales
    $mail->Username   = 'colegiodeabogadosimagen@gmail.com';
    $mail->Password   = 'wema hifp iofs btlp'; // Tu clave de aplicación
    
    $mail->SMTPSecure = 'tls';                 // Encriptación
    $mail->Port       = 587;

    // --- PARCHE PARA XAMPP/LOCAL ---
    // A veces XAMPP falla verificando el certificado SSL de Google.
    // Esto fuerza a aceptar la conexión (solo para pruebas locales).
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    // -------------------------------

    // Remitente y Destinatario (envíatelo a ti mismo para probar)
    $mail->setFrom('colegiodeabogadosimagen@gmail.com', 'Prueba Sistema');
    $mail->addAddress('colegiodeabogadosimagen@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Prueba de envio exitosa';
    $mail->Body    = 'Si lees esto, la conexion funciona correctamente.';

    $mail->send();
    echo "\n\n✅ ¡ÉXITO! El correo se envió correctamente.";
} catch (Exception $e) {
    echo "\n\n❌ ERROR DE ENVÍO:\n";
    echo "Mailer Error: " . $mail->ErrorInfo;
}
echo "</pre>";
?>