<?php
session_start();
if (empty($_SESSION['admin'])) {
    http_response_code(403);
    exit('No autorizado');
}

require_once __DIR__ . '/conexion.php';

try {
    $pdo = (new ConexionPDO())->Conexion();
} catch (Exception $e) {
    http_response_code(500);
    exit('Error de conexión: ' . $e->getMessage());
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function getNivel(int $pct): string
{
    if ($pct < 21)  return 'Sin base';
    if ($pct <= 40) return 'Básico';
    if ($pct <= 61) return 'En desarrollo';
    if ($pct <= 81) return 'Aceptable';
    return 'Destacado';
}

function formatTiempo(?int $seg): string
{
    if ($seg === null) return '';
    return floor($seg / 60) . 'm ' . ($seg % 60) . 's';
}

// ── Consulta ──────────────────────────────────────────────────────────────────
$stmt = $pdo->query(
    "SELECT r.id, r.usuario, r.email, r.respuestas, r.fecha, r.tiempo_segundos, r.cambios_foco,
            r.nota, t.nombre AS template_nombre
     FROM resultados r
     LEFT JOIN exam_templates t ON r.template_slug = t.slug
     ORDER BY r.fecha DESC"
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Cabeceras HTTP ────────────────────────────────────────────────────────────
$filename = 'resultados_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store');

echo "\xEF\xBB\xBF"; // BOM — Excel abre UTF-8 correctamente

// ── Escribir CSV ──────────────────────────────────────────────────────────────
$out = fopen('php://output', 'w');

fputcsv($out, ['ID', 'Nombre', 'Email', 'Plantilla', 'Fecha', 'Aciertos', 'Total', 'Porcentaje', 'Nivel', 'Tiempo', 'Cambios de pestaña', 'Notas']);

foreach ($rows as $row) {
    $respuestas = json_decode($row['respuestas'], true) ?? [];
    $preguntas  = array_slice($respuestas, 1);   // índice 0 = nombre del alumno
    $total      = count($preguntas);
    $aciertos   = (int) array_sum(array_column($preguntas, 'correct'));
    $pct        = $total > 0 ? (int) round($aciertos / $total * 100) : 0;

    fputcsv($out, [
        $row['id'],
        $row['usuario'],
        $row['email'] ?? '',
        $row['template_nombre'] ?? '',
        $row['fecha'],
        $aciertos,
        $total,
        $pct . '%',
        getNivel($pct),
        formatTiempo($row['tiempo_segundos']),
        $row['cambios_foco'] ?? '',
        $row['nota']         ?? '',
    ]);
}

fclose($out);
