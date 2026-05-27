<?php
header('Content-Type: application/json');
require_once 'conexion.php';

try {
    $pdo = (new ConexionPDO())->Conexion();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ── GET: público (lo lee examen.html sin sesión) ─────────────────
if ($method === 'GET') {
    $rows = $pdo->query("SELECT clave, valor FROM site_config")->fetchAll(PDO::FETCH_KEY_PAIR);
    echo json_encode($rows);
    exit;
}

// ── POST: solo admin ─────────────────────────────────────────────
session_start();
if (empty($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO site_config (clave, valor) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE valor = VALUES(valor)"
    );
    foreach ($data as $clave => $valor) {
        $stmt->execute([strip_tags(trim($clave)), trim($valor)]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
