<?php
// api_cumpleanos.php - FINAL (Corrección Ortográfica Automática)
require_once "conexion.php";
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Lima');

// FUNCIÓN PARA CORREGIR ORTOGRAFÍA
function corregir_nombres($texto) {
    if (!$texto) return "";
    
    // 1. Convertir a Minúsculas primero, luego Title Case (Primera Letra Mayúscula)
    $texto = mb_convert_case(mb_strtolower($texto, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    
    // 2. Diccionario de Apellidos Comunes que suelen faltar tilde en BD antiguas
    $correcciones = [
        'Garcia' => 'García',
        'Gomez' => 'Gómez',
        'Perez' => 'Pérez',
        'Gonzalez' => 'González',
        'Rodriguez' => 'Rodríguez',
        'Fernandez' => 'Fernández',
        'Lopez' => 'López',
        'Martinez' => 'Martínez',
        'Sanchez' => 'Sánchez',
        'Diaz' => 'Díaz',
        'Ramirez' => 'Ramírez',
        'Vasquez' => 'Vásquez',
        'Velasquez' => 'Velásquez',
        'Chavez' => 'Chávez',
        'Suarez' => 'Suárez',
        'Gimenez' => 'Giménez',
        'Gutierrez' => 'Gutiérrez',
        'Nuñez' => 'Núñez',
        'Alvarez' => 'Álvarez',
        'Hernandez' => 'Hernández',
        'Davila' => 'Dávila',
        'Cordova' => 'Córdova',
        'Caceres' => 'Cáceres',
        'Benitez' => 'Benítez',
        'Dominguez' => 'Domínguez',
        'Jimenez' => 'Jiménez',
        'Ibañez' => 'Ibáñez',
        'Munoz' => 'Muñoz', // A veces viene sin ñ
        'Muñoz' => 'Muñoz', // Asegurar
        'Leon' => 'León',
        'Aleman' => 'Alemán',
        'Roman' => 'Román',
        'Duran' => 'Durán',
        'Guzman' => 'Guzmán',
        'Cardenas' => 'Cárdenas',
        'Marquez' => 'Márquez',
        'Mendez' => 'Méndez',
        'Solis' => 'Solís',
        'Rios' => 'Ríos',
        'Matias' => 'Matías',
        'Marias' => 'Marías',
        'Avila' => 'Ávila',
        'Mejia' => 'Mejía',
        'Calderon' => 'Calderón',
        'Salomon' => 'Salomón',
        'Buitron' => 'Buitrón',
        'Estupinan' => 'Estupiñán',
        'Estupiñan' => 'Estupiñán',
        'Villazon' => 'Villazón',
        'Alarcon' => 'Alarcón'
    ];

    // Reemplazo inteligente: busca la palabra completa (\b) para no romper otras
    foreach ($correcciones as $sin => $con) {
        // Usamos regex para reemplazar solo palabras completas
        $texto = preg_replace('/\b' . preg_quote($sin, '/') . '\b/u', $con, $texto);
    }
    
    return $texto;
}

$action = $_GET['action'] ?? 'day'; 

try {
    $dateInput = $_GET['date'] ?? date('Y-m-d');
    $monthInput = $_GET['month'] ?? date('m');
    
    $lista = [];
    $titulo = "";

    if ($action === 'day') {
        $timestamp = strtotime($dateInput);
        $dia = date('d', $timestamp);
        $mes = date('m', $timestamp);
        $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        
        $titulo = "Cumpleaños del " . intval($dia) . " de " . $meses[intval($mes)];

        $sql = "SELECT NOMBRE_DEL_AGREMIADO FROM agremiados 
                WHERE MONTH(FECHA_CUMPLEA_OS) = ? AND DAY(FECHA_CUMPLEA_OS) = ? AND ESTADO = 'VIVO'
                ORDER BY NOMBRE_DEL_AGREMIADO ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $mes, $dia);
        $stmt->execute();
        $res = $stmt->get_result();
        
        while($row = $res->fetch_assoc()){
            $lista[] = [
                'nombre' => corregir_nombres($row['NOMBRE_DEL_AGREMIADO'])
            ];
        }
    }

    if ($action === 'month') {
        $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        $titulo = "Cumpleaños de " . $meses[intval($monthInput)];

        $sql = "SELECT NOMBRE_DEL_AGREMIADO, DAY(FECHA_CUMPLEA_OS) as dia
                FROM agremiados 
                WHERE MONTH(FECHA_CUMPLEA_OS) = ? AND ESTADO = 'VIVO'
                ORDER BY DAY(FECHA_CUMPLEA_OS) ASC, NOMBRE_DEL_AGREMIADO ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $monthInput);
        $stmt->execute();
        $res = $stmt->get_result();
        
        while($row = $res->fetch_assoc()){
            $lista[] = [
                'dia' => $row['dia'],
                'nombre' => corregir_nombres($row['NOMBRE_DEL_AGREMIADO'])
            ];
        }
    }

    echo json_encode(['ok' => true, 'titulo' => $titulo, 'lista' => $lista]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>