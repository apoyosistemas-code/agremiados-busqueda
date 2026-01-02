<?php
// test_envio.php - TEST FINAL CON ENLACE EKUBYTE
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'libs/PHPMailer/src/Exception.php';
require 'libs/PHPMailer/src/PHPMailer.php';
require 'libs/PHPMailer/src/SMTP.php';

echo "<h1>Prueba de Diseño Final</h1>";
echo "<pre>"; 

$mail = new PHPMailer(true);

try {
    // 1. Configuración de Servidor
    $mail->SMTPDebug = 2; 
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'colegiodeabogadosimagen@gmail.com';
    $mail->Password   = 'wema hifp iofs btlp'; // CLAVE DE APLICACIÓN
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // 2. Remitente y Destinatario
    $mail->setFrom('colegiodeabogadosimagen@gmail.com', 'Colegio de Abogados de Junín');
    $mail->addAddress('colegiodeabogadosimagen@gmail.com'); // AUTO-ENVÍO

    // 3. Adjuntar plantilla base (Simulación)
    $ruta_imagen = __DIR__ . '/assets_cumple/plantilla.jpg';
    if(file_exists($ruta_imagen)){
        $mail->addAttachment($ruta_imagen, 'Tarjeta_Prueba.jpg');
    }

    // 4. EL DISEÑO HTML FINAL
    $nombre = "Juan Perez (Prueba Final)";
    
    $body = "
    <div style='font-family: Arial, sans-serif; color: #333; padding: 25px; border: 1px solid #e1e1e1; border-radius: 12px; background-color: #ffffff; max-width: 600px; margin: 0 auto;'>
        
        <h2 style='color: #12503a; margin-top: 0; text-align: center; font-size: 24px; border-bottom: 2px solid #12503a; padding-bottom: 15px;'>
            ¡Feliz Cumpleaños!
        </h2>
        
        <p style='font-size: 16px; margin-top: 20px;'>Estimado(a) colega <strong>$nombre</strong>,</p>
        
        <p style='font-size: 16px; line-height: 1.6; color: #444;'>
            Les expresamos nuestras felicitaciones y mejores deseos en este día especial.<br>
            Que la vida les regale momentos de alegría, prosperidad y realización personal.<br>
            Apreciamos su responsabilidad, ética y constante contribución a nuestra institución.
        </p>
        
        <p style='font-size: 16px; margin-top: 20px;'>
            Con cordialidad,<br>
            <em>El equipo del Ilustre Colegio de Abogados de Junín.</em>
        </p>
        
        <br>
        <div style='background-color: #f9f9f9; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #12503a;'>
            <p style='margin: 5px 0; font-size: 15px;'>
                <strong style='color: #d38b13;'>⚖️ Mg. Cristhian Enrique Velita Espinoza | Decano</strong>
            </p>
            <p style='margin: 5px 0; font-size: 14px;'>
                <strong style='color: #12503a;'>⚖️ Junta Directiva 2024 – 2026</strong>
            </p>
        </div>
        
        <p style='text-align: center; color: #888; font-size: 12px; margin-top: 20px;'>
            Adjunto encontrará su tarjeta de felicitación personalizada.
        </p>

        <div style='text-align: center; margin-top: 25px; padding-top: 15px; border-top: 1px dashed #eee;'>
            <span style='font-size: 13px; color: #999; font-family: sans-serif;'>
                Powered by 
                <a href='https://ekubyte.com/' target='_blank' style='color: #008191; text-decoration: none; font-weight: bold;'>EKUBYTE</a>
            </span>
        </div>

    </div>
    ";

    $mail->isHTML(true);
    $mail->Subject = "Prueba Final - Feliz Cumpleaños Dr(a). $nombre";
    $mail->Body    = $body;

    $mail->send();
    echo "\n\n✅ ¡CORREO ENVIADO! Verifica el enlace y el tamaño de letra.";

} catch (Exception $e) {
    echo "\n\n❌ ERROR: " . $mail->ErrorInfo;
}
echo "</pre>";
?>