<?php
date_default_timezone_set('America/Mexico_City');
header('Content-Type: application/json');

require_once 'conexion.php';

try {
    $pdo = (new ConexionPDO())->Conexion();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$respuestas   = $data['userAnswers']    ?? [];
$tiempo       = isset($data['tiempoSegundos']) ? (int)$data['tiempoSegundos'] : null;
$foco         = isset($data['cambiosFoco'])    ? (int)$data['cambiosFoco']    : null;
$templateSlug = isset($data['templateSlug'])   ? substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['templateSlug']), 0, 8) : null;
$email        = isset($data['email']) ? trim($data['email']) : null;
if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) $email = null;

$usuario      = $respuestas[0]['res'] ?? 'Anónimo';
$fecha        = date('Y-m-d H:i:s');
$respuestasJson = json_encode($respuestas, JSON_UNESCAPED_UNICODE);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO resultados (usuario, email, respuestas, fecha, tiempo_segundos, cambios_foco, template_slug)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$usuario, $email, $respuestasJson, $fecha, $tiempo, $foco, $templateSlug]);

    echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
