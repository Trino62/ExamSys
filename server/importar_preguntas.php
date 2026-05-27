<?php
session_start();
if (empty($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/conexion.php';

// ── GET ?template=1 → descargar CSV de ejemplo ────────────────────────────────
if (isset($_GET['template'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="plantilla_preguntas.csv"');
    header('Cache-Control: no-cache');

    echo "\xEF\xBB\xBF"; // BOM para que Excel abra en UTF-8 correctamente

    $out = fopen('php://output', 'w');
    fputcsv($out, ['pregunta', 'opcion_a', 'opcion_b', 'opcion_c', 'opcion_d', 'correcta', 'categoria']);
    fputcsv($out, ['¿Qué etiqueta se usa para el título principal?', '<title>', '<h1>', '<header>', '<main>', '1', 'HTML']);
    fputcsv($out, ['¿Qué propiedad CSS cambia el color del texto?',  'font-color', 'text-color', 'color', 'foreground', '2', 'CSS']);
    fputcsv($out, ['¿Cómo se declara una variable en PHP?', 'var nombre', 'let nombre', '$nombre', '#nombre', '2', 'PHP']);
    fclose($out);
    exit;
}

// ── POST → procesar archivo CSV ───────────────────────────────────────────────
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
    $errMsg = match ($_FILES['csv']['error'] ?? -1) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño máximo permitido.',
        UPLOAD_ERR_NO_FILE  => 'No se seleccionó ningún archivo.',
        default             => 'Error al subir el archivo.',
    };
    http_response_code(400);
    echo json_encode(['error' => $errMsg]);
    exit;
}

$handle = fopen($_FILES['csv']['tmp_name'], 'r');
if (!$handle) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo leer el archivo.']);
    exit;
}

try {
    $pdo = (new ConexionPDO())->Conexion();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta, categoria)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);

$inserted   = 0;
$skipped    = [];
$row        = 0;
$headerSeen = false;

while (($line = fgetcsv($handle)) !== false) {
    $row++;

    // Saltar líneas vacías
    if (empty(array_filter($line))) continue;

    // Quitar BOM si está en la primera celda
    $line[0] = ltrim($line[0], "\xEF\xBB\xBF");

    // Primera fila de encabezado (si la primera celda es "pregunta")
    if (!$headerSeen && strtolower(trim($line[0])) === 'pregunta') {
        $headerSeen = true;
        continue;
    }
    $headerSeen = true;

    // Se necesitan exactamente 7 columnas
    if (count($line) < 7) {
        $skipped[] = "Fila $row: faltan columnas (se necesitan 7, se encontraron " . count($line) . ").";
        continue;
    }

    [$pregunta, $opcion_a, $opcion_b, $opcion_c, $opcion_d, $correcta, $categoria] = array_map('trim', $line);

    // Validar campos obligatorios
    if (!$pregunta || !$opcion_a || !$opcion_b || !$opcion_c || !$opcion_d) {
        $skipped[] = "Fila $row: hay campos de texto vacíos.";
        continue;
    }

    // Validar correcta (0–3)
    if (!is_numeric($correcta) || !in_array((int)$correcta, [0, 1, 2, 3])) {
        $skipped[] = "Fila $row: 'correcta' debe ser 0 (A), 1 (B), 2 (C) o 3 (D) — recibido: \"$correcta\".";
        continue;
    }

    if (!$categoria) $categoria = 'general';

    try {
        $stmt->execute([$pregunta, $opcion_a, $opcion_b, $opcion_c, $opcion_d, (int)$correcta, $categoria]);
        $inserted++;
    } catch (PDOException $e) {
        $skipped[] = "Fila $row: error al insertar — " . $e->getMessage();
    }
}

fclose($handle);

echo json_encode([
    'inserted' => $inserted,
    'skipped'  => $skipped,
]);
