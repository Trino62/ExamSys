<?php
session_start();
if (empty($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

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

// ── GET ──────────────────────────────────────────────────────────
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM exam_templates WHERE id = ?");
        $stmt->execute([$id]);
        $t = $stmt->fetch();
        echo json_encode($t ?: ['ok' => false, 'error' => 'No encontrada']);
        exit;
    }

    // list + disponibilidad por categoría
    $templates = $pdo->query("SELECT * FROM exam_templates ORDER BY created_at DESC")->fetchAll();

    // conteo disponible por categoría
    $counts = [];
    foreach ($pdo->query("SELECT categoria, COUNT(*) as n FROM preguntas GROUP BY categoria") as $row) {
        $counts[$row['categoria']] = (int)$row['n'];
    }

    echo json_encode(['templates' => $templates, 'disponibles' => $counts]);
    exit;
}

// ── POST (crear / actualizar) ────────────────────────────────────
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $nombre  = trim($data['nombre'] ?? '');
    $tiempo  = isset($data['tiempo_segundos']) && $data['tiempo_segundos'] > 0
                 ? (int)$data['tiempo_segundos'] : null;
    $config  = $data['config'] ?? [];
    $id      = isset($data['id']) ? (int)$data['id'] : 0;

    if (!$nombre) {
        echo json_encode(['ok' => false, 'error' => 'El nombre es requerido']);
        exit;
    }
    if (empty($config) || array_sum($config) < 1) {
        echo json_encode(['ok' => false, 'error' => 'Configura al menos una categoría con 1 pregunta']);
        exit;
    }

    // cap each category at available
    $warned = [];
    foreach ($config as $cat => $n) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM preguntas WHERE categoria = ?");
        $stmt->execute([$cat]);
        $avail = (int)$stmt->fetchColumn();
        if ($n > $avail) {
            $config[$cat] = $avail;
            $warned[] = "{$cat}: pedidas {$n}, disponibles {$avail}";
        }
    }
    // remove categories with 0
    $config = array_filter($config, fn($v) => $v > 0);

    $configJson = json_encode($config, JSON_UNESCAPED_UNICODE);

    if ($id) {
        $stmt = $pdo->prepare(
            "UPDATE exam_templates SET nombre=?, tiempo_segundos=?, config=? WHERE id=?"
        );
        $stmt->execute([$nombre, $tiempo, $configJson, $id]);
    } else {
        // generate unique slug
        do {
            $slug = substr(bin2hex(random_bytes(4)), 0, 8);
            $chk  = $pdo->prepare("SELECT COUNT(*) FROM exam_templates WHERE slug=?");
            $chk->execute([$slug]);
        } while ($chk->fetchColumn() > 0);

        $stmt = $pdo->prepare(
            "INSERT INTO exam_templates (nombre, slug, tiempo_segundos, config) VALUES (?,?,?,?)"
        );
        $stmt->execute([$nombre, $slug, $tiempo, $configJson]);
        $id = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("SELECT * FROM exam_templates WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['ok' => true, 'template' => $stmt->fetch(), 'warnings' => $warned]);
    exit;
}

// ── DELETE ───────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id   = isset($data['id']) ? (int)$data['id'] : 0;
    if ($id <= 0) { echo json_encode(['ok' => false, 'error' => 'ID inválido']); exit; }

    $stmt = $pdo->prepare("DELETE FROM exam_templates WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
