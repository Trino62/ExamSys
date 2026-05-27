<?php
include 'conexion.php';

header('Content-Type: application/json');

try {
    $conexion = new ConexionPDO();
    $pdo = $conexion->Conexion();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

$slug           = $_GET['t'] ?? null;
$tiempoSegundos = null;
$config         = null;

// ── 1. Cargar plantilla (falla silenciosa si la tabla no existe) ──
if ($slug) {
    try {
        $stmt = $pdo->prepare(
            "SELECT config, tiempo_segundos FROM exam_templates WHERE slug = ? AND activa = 1"
        );
        $stmt->execute([$slug]);
        $tmpl = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tmpl) {
            $config         = json_decode($tmpl['config'], true);
            $tiempoSegundos = $tmpl['tiempo_segundos'];
        }
    } catch (Exception $e) {
        // tabla exam_templates no existe todavía — usar config por defecto
        $config = null;
    }
}

// ── 2. Config por defecto ────────────────────────────────────────
if (!$config) {
    $config = [
        'HTML'       => 2,
        'CSS'        => 2,
        'JavaScript' => 2,
        'PHP'        => 2,
        'MySQL'      => 1,
        'Web'        => 1,
    ];
}

// ── 3. Verificar si existe la columna `categoria` ────────────────
$tieneCategoria = false;
try {
    $chk = $pdo->query("SELECT categoria FROM preguntas LIMIT 1");
    $tieneCategoria = true;
} catch (Exception $e) {
    $tieneCategoria = false;
}

// ── 4. Construir query ───────────────────────────────────────────
try {
    if ($tieneCategoria) {
        // UNION balanceado por categoría
        $parts  = [];
        $params = [];
        foreach ($config as $cat => $n) {
            $n = max(1, (int)$n);
            $parts[]  = "(SELECT id, pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta, categoria
                          FROM preguntas WHERE categoria = ? ORDER BY RAND() LIMIT {$n})";
            $params[] = $cat;
        }
        $sql  = implode(' UNION ALL ', $parts) . ' ORDER BY RAND()';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        // Fallback sin categorías
        $total = max(1, array_sum($config));
        $stmt  = $pdo->prepare(
            "SELECT id, pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta FROM preguntas ORDER BY RAND() LIMIT ?"
        );
        $stmt->execute([$total]);
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo json_encode(['error' => 'No hay preguntas en la base de datos.']);
        exit;
    }

    $preguntas = array_map(fn($p) => [
        'id'        => (int)$p['id'],
        'q'         => $p['pregunta'],
        'options'   => [$p['opcion_a'], $p['opcion_b'], $p['opcion_c'], $p['opcion_d']],
        'correct'   => (int)$p['correcta'],
        'categoria' => $p['categoria'] ?? null,
    ], $rows);

    echo json_encode([
        'questions'       => $preguntas,
        'tiempo_segundos' => $tiempoSegundos,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
