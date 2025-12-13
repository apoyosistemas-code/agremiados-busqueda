<?php
// api_cumpleanos.php - SOPORTE MULTI-CORREO
require_once "conexion.php";

header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
date_default_timezone_set('America/Lima');

function corregir_nombres($texto) {
    if (!$texto) return "";
    $texto = mb_convert_case(mb_strtolower($texto, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    $correcciones = [
        'Garcia' => 'García', 'Gomez' => 'Gómez', 'Perez' => 'Pérez',
        'Gonzalez' => 'González', 'Rodriguez' => 'Rodríguez', 'Fernandez' => 'Fernández',
        'Lopez' => 'López', 'Martinez' => 'Martínez', 'Sanchez' => 'Sánchez',
        'Diaz' => 'Díaz', 'Ramirez' => 'Ramírez', 'Vasquez' => 'Vásquez',
        'Chavez' => 'Chávez', 'Suarez' => 'Suárez', 'Davila' => 'Dávila',
        'Gutierrez' => 'Gutiérrez', 'Alvarez' => 'Álvarez', 'Jimenez' => 'Jiménez'
    ];
    foreach ($correcciones as $mal => $bien) {
        $texto = preg_replace("/\b$mal\b/u", $bien, $texto);
    }
    return $texto;
}

$action = $_GET['action'] ?? 'day';
$lista = [];
$titulo = "";

try {
    $sql = "";
    $params = [];
    $types = "";

    if ($action === 'day') {
        $dateInput = $_GET['date'] ?? date('Y-m-d');
        $time = strtotime($dateInput);
        $dia = date('d', $time);
        $mes = date('m', $time);
        
        $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        $titulo = "Cumpleaños del $dia de " . $meses[intval($mes)];

        $sql = "SELECT id, NOMBRE_DEL_AGREMIADO, COLEGIATURA, N_MERO_DE_CELULAR, CORREO, CORREO_GMAIL
                FROM agremiados 
                WHERE MONTH(FECHA_CUMPLEA_OS) = ? AND DAY(FECHA_CUMPLEA_OS) = ? AND ESTADO = 'VIVO'
                ORDER BY NOMBRE_DEL_AGREMIADO ASC";
        $params = [$mes, $dia];
        $types = "ii";
    }

    if ($action === 'month') {
        $monthInput = $_GET['month'] ?? date('m');
        $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        $titulo = "Cumpleaños de " . $meses[intval($monthInput)];

        $sql = "SELECT id, NOMBRE_DEL_AGREMIADO, COLEGIATURA, DAY(FECHA_CUMPLEA_OS) as dia, N_MERO_DE_CELULAR, CORREO, CORREO_GMAIL
                FROM agremiados 
                WHERE MONTH(FECHA_CUMPLEA_OS) = ? AND ESTADO = 'VIVO'
                ORDER BY DAY(FECHA_CUMPLEA_OS) ASC, NOMBRE_DEL_AGREMIADO ASC";
        $params = [$monthInput];
        $types = "i";
    }

    if ($sql) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        
        while($row = $res->fetch_assoc()){
            $celular = isset($row['N_MERO_DE_CELULAR']) ? preg_replace('/[^0-9]/', '', $row['N_MERO_DE_CELULAR']) : '';
            
            // LOGICA DE CORREOS: Enviamos AMBOS al frontend
            $gmail = !empty($row['CORREO_GMAIL']) ? trim($row['CORREO_GMAIL']) : null;
            $correo = !empty($row['CORREO']) ? trim($row['CORREO']) : null;
            
            // Decidir el "principal" por defecto (Gmail gana)
            $email_defecto = $gmail ?: $correo;

            $lista[] = [
                'id' => $row['id'],
                'nombre' => corregir_nombres($row['NOMBRE_DEL_AGREMIADO']),
                'col' => $row['COLEGIATURA'],
                'dia' => $row['dia'] ?? ($dia ?? ''),
                'celular' => $celular,
                'email_active' => $email_defecto, // El que se usará para enviar
                'email_gmail' => $gmail,          // Opción 1
                'email_other' => $correo          // Opción 2
            ];
        }
    }

    echo json_encode(['ok' => true, 'titulo' => $titulo, 'lista' => $lista]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>