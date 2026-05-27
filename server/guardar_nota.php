<?php
session_start();
if (empty($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');
require_once 'conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$id   = (int)($data['id'] ?? 0);
$nota = trim($data['nota'] ?? '');

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    $pdo  = (new ConexionPDO())->Conexion();
    $stmt = $pdo->prepare("UPDATE resultados SET nota = ? WHERE id = ?");
    $stmt->execute([$nota !== '' ? $nota : null, $id]);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
