<?php
// motor_envios.php - PRODUCCIÓN (Sin modo prueba)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'libs/PHPMailer/src/Exception.php';
require 'libs/PHPMailer/src/PHPMailer.php';
require 'libs/PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

// RUTAS
$FONT_PATH = __DIR__ . '/assets_cumple/fuente.ttf';
$IMG_PATH  = __DIR__ . '/assets_cumple/plantilla.jpg';
$TEMP_DIR  = __DIR__ . '/assets_cumple/temp/';

if (is_dir($TEMP_DIR)) {
    $files = glob($TEMP_DIR . '*.jpg'); 
    $now   = time();

    foreach ($files as $file) {
        if (is_file($file)) {
            // 7200 segundos = 2 Horas
            // Si el archivo es más viejo que eso, se borra.
            if ($now - filemtime($file) >= 7200) { 
                @unlink($file);
            }
        }
    }
}

$action = $_POST['action'] ?? '';

// ==========================================
// FUNCIÓN AUXILIAR PARA GENERAR IMAGEN
// ==========================================
function crear_imagen_temp($nombre, $IMG_PATH, $FONT_PATH, $TEMP_DIR) {
    if (!file_exists($IMG_PATH)) return ['ok'=>false, 'error'=>'Falta plantilla.jpg'];
    
    $id_temp = md5($nombre . date('YmdHis')); 
    $archivo_salida = $TEMP_DIR . "tarjeta_{$id_temp}.jpg";
    $url_salida = "assets_cumple/temp/tarjeta_{$id_temp}.jpg";

    $im = imagecreatefromjpeg($IMG_PATH);
    if(!$im) return ['ok'=>false, 'error'=>'Error al cargar imagen base'];

    $color = imagecolorallocate($im, 14, 75, 37); // Verde #0e4b25

    $tamano_base = 160; 
    if(strlen($nombre) > 25) $tamano_base = 130;
    if(strlen($nombre) > 35) $tamano_base = 110;

    $caja = imagettfbbox($tamano_base, 0, $FONT_PATH, $nombre);
    $ancho_texto = $caja[2] - $caja[0];
    $x = (imagesx($im) - $ancho_texto) / 2;
    $y = (imagesy($im) / 2) + 80; 

    imagettftext($im, $tamano_base, 0, $x, $y, $color, $FONT_PATH, $nombre);
    imagejpeg($im, $archivo_salida, 90);
    imagedestroy($im);

    return ['ok'=>true, 'path'=>$archivo_salida, 'url'=>$url_salida];
}

// 1. GENERAR TARJETA
if ($action === 'generar') {
    $nombre = $_POST['nombre'] ?? 'Colega';
    $res = crear_imagen_temp($nombre, $IMG_PATH, $FONT_PATH, $TEMP_DIR);
    
    if($res['ok']) echo json_encode(['ok' => true, 'url' => $res['url']]);
    else echo json_encode($res);
    exit;
}

// 2. ENVIAR CORREO
if ($action === 'enviar') {
    
    $email = $_POST['email'];
    $nombre = $_POST['nombre'];
    $url_relativa = $_POST['url_imagen']; 
    $ruta_absoluta = __DIR__ . '/' . $url_relativa;

    if (!file_exists($ruta_absoluta)) {
        echo json_encode(['ok'=>false, 'error'=>'La imagen no se encuentra.']);
        exit;
    }

    $mail = new PHPMailer(true); 

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'colegiodeabogadosimagen@gmail.com';
        $mail->Password   = 'wema hifp iofs btlp'; // TU CLAVE DE APLICACIÓN
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true
            )
        );

        $mail->CharSet = 'UTF-8';
        $mail->setFrom('colegiodeabogadosimagen@gmail.com', 'Colegio de Abogados de Junín');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "¡Feliz Cumpleaños Dr(a). $nombre!";
        
        $body = "
        <div style='font-family: Arial, sans-serif; color: #333; padding: 25px; border: 1px solid #e1e1e1; border-radius: 12px; background-color: #ffffff;'>
            
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
        $mail->Body = $body;

        $mail->addAttachment($ruta_absoluta, 'Tarjeta_Cumpleanos_CAJ.jpg');

        $mail->send();
        echo json_encode(['ok'=>true]);

    } catch (Exception $e) {
        echo json_encode(['ok'=>false, 'error' => $mail->ErrorInfo]);
    }
    exit;
}
?>