<?php
session_start();
if (empty($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$id   = isset($data['id']) ? (int)$data['id'] : 0;

if ($id <= 0) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

try {
    require_once 'conexion.php';
    $conexion = new ConexionPDO();
    $pdo = $conexion->Conexion();

    $stmt = $pdo->prepare("DELETE FROM resultados WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['ok' => true, 'deleted' => $stmt->rowCount()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
