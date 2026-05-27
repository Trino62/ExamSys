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
    // ?counts=1 → only counts per category
    if (!empty($_GET['counts'])) {
        $rows = $pdo->query(
            "SELECT categoria, COUNT(*) as n FROM preguntas GROUP BY categoria ORDER BY categoria"
        )->fetchAll();
        echo json_encode($rows);
        exit;
    }

    $cat  = $_GET['categoria'] ?? null;
    $q    = $cat
        ? $pdo->prepare("SELECT * FROM preguntas WHERE categoria = ? ORDER BY id DESC")
        : $pdo->prepare("SELECT * FROM preguntas ORDER BY categoria, id DESC");
    $cat ? $q->execute([$cat]) : $q->execute();
    echo json_encode($q->fetchAll());
    exit;
}

// ── POST (crear / actualizar) ────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);

    $id       = isset($d['id']) ? (int)$d['id'] : 0;
    $pregunta = trim($d['pregunta'] ?? '');
    $a        = trim($d['opcion_a'] ?? '');
    $b        = trim($d['opcion_b'] ?? '');
    $c        = trim($d['opcion_c'] ?? '');
    $e        = trim($d['opcion_d'] ?? '');
    $correcta = isset($d['correcta']) ? (int)$d['correcta'] : -1;
    $cat      = trim($d['categoria'] ?? '');

    if (!$pregunta || !$a || !$b || !$c || !$e || $correcta < 0 || $correcta > 3 || !$cat) {
        echo json_encode(['ok' => false, 'error' => 'Todos los campos son requeridos']);
        exit;
    }

    if ($id) {
        $stmt = $pdo->prepare(
            "UPDATE preguntas SET pregunta=?, opcion_a=?, opcion_b=?, opcion_c=?, opcion_d=?,
             correcta=?, categoria=? WHERE id=?"
        );
        $stmt->execute([$pregunta, $a, $b, $c, $e, $correcta, $cat, $id]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta, categoria)
             VALUES (?,?,?,?,?,?,?)"
        );
        $stmt->execute([$pregunta, $a, $b, $c, $e, $correcta, $cat]);
        $id = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("SELECT * FROM preguntas WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['ok' => true, 'pregunta' => $stmt->fetch()]);
    exit;
}

// ── DELETE ───────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $d  = json_decode(file_get_contents('php://input'), true);
    $id = isset($d['id']) ? (int)$d['id'] : 0;
    if ($id <= 0) { echo json_encode(['ok' => false, 'error' => 'ID inválido']); exit; }

    $stmt = $pdo->prepare("DELETE FROM preguntas WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
