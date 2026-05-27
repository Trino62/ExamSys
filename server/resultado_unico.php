<?php
header('Content-Type: application/json');
require_once 'conexion.php';

try {
    $pdo = (new ConexionPDO())->Conexion();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id   = (int)($data['id'] ?? 0);

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT r.usuario, r.email, r.respuestas, r.fecha, r.tiempo_segundos, r.cambios_foco,
                r.template_slug, r.nota, t.nombre AS template_nombre
         FROM resultados r
         LEFT JOIN exam_templates t ON r.template_slug = t.slug
         WHERE r.id = ?"
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Resultado no encontrado']);
        exit;
    }

    echo json_encode($row);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
