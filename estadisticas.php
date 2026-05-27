<?php
session_start();
if (empty($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

include_once 'server/conexion.php';

/* ── helpers ───────────────────────────────────────────────────── */
function getNivelLabel(int $aciertos, int $total): string {
    if ($total === 0) return 'Sin datos';
    $p = $aciertos / $total;
    if ($p < 0.21) return 'Sin base';
    if ($p < 0.41) return 'Básico';
    if ($p < 0.62) return 'En desarrollo';
    if ($p < 0.82) return 'Aceptable';
    return 'Destacado';
}

$NIVEL_COLOR = [
    'Sin base'       => '#e53935',
    'Básico'         => '#fb8c00',
    'En desarrollo'  => '#039be5',
    'Aceptable'      => '#5c6bc0',
    'Destacado'      => '#43a047',
    'Sin datos'      => '#9e9e9e',
];

$CAT_ORDER = ['HTML','CSS','JavaScript','PHP','MySQL','Web'];

/* ── fetch & process ────────────────────────────────────────────── */
try {
    $pdo = (new ConexionPDO())->Conexion();
    $rows = $pdo->query(
        "SELECT respuestas, tiempo_segundos, cambios_foco, usuario FROM resultados ORDER BY id"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $rows = [];
}

$totalExamenes  = count($rows);
$sumPct         = 0;
$sumTiempo      = 0;
$countTiempo    = 0;
$nivelCount     = array_fill_keys(array_keys($NIVEL_COLOR), 0);
$catStats       = [];   // [cat => [ok, total]]
$aptos          = 0;    // Aceptable o Destacado

foreach ($rows as $r) {
    $qs = json_decode($r['respuestas'], true) ?? [];
    $aciertos = 0;
    $total    = 0;

    foreach ($qs as $i => $p) {
        if ($i === 0) continue;
        $total++;
        $ok = !empty($p['correct']);
        if ($ok) $aciertos++;

        // category
        $cat = $p['categoria'] ?? null;
        if ($cat) {
            if (!isset($catStats[$cat])) $catStats[$cat] = ['ok' => 0, 'total' => 0];
            $catStats[$cat]['total']++;
            if ($ok) $catStats[$cat]['ok']++;
        }
    }

    if ($total > 0) {
        $sumPct += $aciertos / $total * 100;
        $nivel = getNivelLabel($aciertos, $total);
        $nivelCount[$nivel] = ($nivelCount[$nivel] ?? 0) + 1;
        if (in_array($nivel, ['Aceptable','Destacado'])) $aptos++;
    }

    if ($r['tiempo_segundos'] !== null) {
        $sumTiempo += (int)$r['tiempo_segundos'];
        $countTiempo++;
    }
}

$promedioPct    = $totalExamenes > 0 ? round($sumPct / $totalExamenes, 1) : 0;
$promedioTiempo = $countTiempo > 0  ? (int)($sumTiempo / $countTiempo) : null;
$pctAptos       = $totalExamenes > 0 ? round($aptos / $totalExamenes * 100) : 0;

function fmtTiempo(?int $s): string {
    if ($s === null) return '—';
    return floor($s/60) . ':' . str_pad($s%60, 2, '0', STR_PAD_LEFT);
}

// Sort categories by performance (worst first)
$catSorted = [];
foreach ($CAT_ORDER as $cat) {
    if (isset($catStats[$cat])) $catSorted[$cat] = $catStats[$cat];
}
foreach ($catStats as $cat => $v) {   // any extra cat not in order
    if (!isset($catSorted[$cat])) $catSorted[$cat] = $v;
}
uasort($catSorted, fn($a,$b) =>
    ($a['ok']/$a['total']) <=> ($b['ok']/$b['total'])
);

// Nivel order for chart (best first)
$NIVEL_ORDER = ['Destacado','Aceptable','En desarrollo','Básico','Sin base'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Estadísticas — Examen de Programación Web</title>
  <style>
    :root {
      --bg:        #f2f5f8;
      --bg-card:   #ffffff;
      --bg-sub:    #f7f9fb;
      --text:      #1a2a3a;
      --text-2:    #555;
      --text-3:    #888;
      --text-4:    #bbb;
      --border:    #dde3ec;
      --border-s:  #f0f3f7;
      --sh:        rgba(0,0,0,.08);
    }
    [data-theme="dark"] {
      --bg:        #0e1520;
      --bg-card:   #172030;
      --bg-sub:    #1b2840;
      --text:      #dce8f5;
      --text-2:    #8facbe;
      --text-3:    #566d80;
      --text-4:    #3c5265;
      --border:    #243147;
      --border-s:  #192840;
      --sh:        rgba(0,0,0,.35);
    }
    *, *::before, *::after { box-sizing: border-box; }

    body {
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background: #f2f5f8;
      margin: 0;
      min-height: 100vh;
    }

    /* HEADER */
    .page-header {
      background: linear-gradient(135deg, #1d3759 0%, #2b4f80 100%);
      color: white;
      padding: 24px 24px 20px;
    }
    .header-inner {
      max-width: 760px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .btn-back {
      display: flex; align-items: center; gap: 6px;
      background: rgba(255,255,255,0.15);
      border: 1px solid rgba(255,255,255,0.25);
      color: white; text-decoration: none;
      padding: 6px 14px; border-radius: 8px;
      font-size: 0.85rem; white-space: nowrap;
      transition: background .15s;
    }
    .btn-back:hover { background: rgba(255,255,255,0.25); color: white; }
    .header-title { flex: 1; }
    .header-title h1 { margin: 0; font-size: 1.2rem; font-weight: 700; }
    .header-title p  { margin: 4px 0 0; font-size: 0.82rem; opacity: .75; }

    /* LAYOUT */
    .content {
      max-width: 760px;
      margin: 28px auto 48px;
      padding: 0 20px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* SUMMARY ROW */
    .summary-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 14px;
    }
    .stat-card {
      background: white;
      border-radius: 14px;
      box-shadow: 0 2px 12px rgba(0,0,0,.07);
      padding: 20px 18px 16px;
      text-align: center;
    }
    .stat-card .val {
      font-size: 2rem;
      font-weight: 800;
      line-height: 1;
      color: #1a2a3a;
    }
    .stat-card .lbl {
      font-size: 0.75rem;
      color: #999;
      margin-top: 6px;
      text-transform: uppercase;
      letter-spacing: .5px;
    }
    .stat-card .sub {
      font-size: 0.78rem;
      color: #bbb;
      margin-top: 3px;
    }

    /* PANEL */
    .panel {
      background: white;
      border-radius: 14px;
      box-shadow: 0 2px 12px rgba(0,0,0,.07);
      padding: 22px 24px;
    }
    .panel-title {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .6px;
      color: #aaa;
      margin-bottom: 16px;
    }

    /* NIVEL BARS */
    .nivel-row {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 10px;
    }
    .nivel-row:last-child { margin-bottom: 0; }
    .nivel-name {
      font-size: 0.83rem;
      font-weight: 600;
      color: #1a2a3a;
      width: 120px;
      flex-shrink: 0;
    }
    .bar-track {
      flex: 1;
      height: 10px;
      background: #f0f3f7;
      border-radius: 99px;
      overflow: hidden;
    }
    .bar-fill {
      height: 100%;
      border-radius: 99px;
      transition: width .6s ease;
    }
    .nivel-count {
      font-size: 0.8rem;
      font-weight: 700;
      color: #555;
      width: 28px;
      text-align: right;
      flex-shrink: 0;
    }
    .nivel-pct {
      font-size: 0.75rem;
      color: #bbb;
      width: 36px;
      text-align: right;
      flex-shrink: 0;
    }

    /* CATEGORY BARS */
    .cat-row {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;
    }
    .cat-row:last-child { margin-bottom: 0; }
    .cat-name {
      font-size: 0.83rem;
      font-weight: 600;
      color: #1a2a3a;
      width: 92px;
      flex-shrink: 0;
    }
    .cat-track {
      flex: 1;
      height: 10px;
      background: #f0f3f7;
      border-radius: 99px;
      overflow: hidden;
    }
    .cat-fill {
      height: 100%;
      border-radius: 99px;
      transition: width .6s ease;
    }
    .cat-pct {
      font-size: 0.8rem;
      font-weight: 700;
      width: 38px;
      text-align: right;
      flex-shrink: 0;
    }
    .cat-n {
      font-size: 0.73rem;
      color: #bbb;
      width: 52px;
      text-align: right;
      flex-shrink: 0;
    }

    /* EMPTY */
    .empty-note {
      text-align: center;
      color: #bbb;
      font-size: 0.88rem;
      padding: 24px 0;
    }

    /* TWO COLUMNS */
    .two-col {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    @media (max-width: 560px) { .two-col { grid-template-columns: 1fr; } }

    /* ── View Transitions ───────────────── */
    @view-transition { navigation: auto; }
    @keyframes vt-out { to   { opacity: 0; translate: 0 -6px;  } }
    @keyframes vt-in  { from { opacity: 0; translate: 0  10px; } }
    ::view-transition-old(root) { animation: 180ms ease both vt-out; }
    ::view-transition-new(root) { animation: 280ms ease both vt-in;  }

    /* ── Theme toggle ───────────────── */
    .theme-toggle {
      position: fixed; bottom: 22px; right: 20px; z-index: 8000;
      width: 36px; height: 36px; border-radius: 50%;
      border: 1px solid var(--border); background: var(--bg-card);
      color: var(--text-2); font-size: .9rem; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: background .2s, box-shadow .2s; padding: 0; line-height: 1;
      box-shadow: 0 2px 8px var(--sh);
    }
    .theme-toggle:hover { box-shadow: 0 4px 14px var(--sh); background: var(--bg-sub); }
    @media print { .theme-toggle { display: none !important; } }

    /* ── Dark overrides ─────────────── */
    [data-theme="dark"] body { background: var(--bg); }
    [data-theme="dark"] .stat-card { background: var(--bg-card); box-shadow: 0 2px 12px var(--sh); }
    [data-theme="dark"] .stat-card .val { color: var(--text); }
    [data-theme="dark"] .stat-card .lbl { color: var(--text-3); }
    [data-theme="dark"] .stat-card .sub { color: var(--text-4); }
    [data-theme="dark"] .panel { background: var(--bg-card); box-shadow: 0 2px 12px var(--sh); }
    [data-theme="dark"] .panel-title { color: var(--text-3); }
    [data-theme="dark"] .nivel-name  { color: var(--text); }
    [data-theme="dark"] .nivel-count { color: var(--text-2); }
    [data-theme="dark"] .nivel-pct   { color: var(--text-4); }
    [data-theme="dark"] .bar-track   { background: var(--border-s); }
    [data-theme="dark"] .cat-name    { color: var(--text); }
    [data-theme="dark"] .cat-track   { background: var(--border-s); }
    [data-theme="dark"] .cat-n       { color: var(--text-4); }
    [data-theme="dark"] .empty-note  { color: var(--text-4); }
  </style>
  <script>
    (function(){
      var t=localStorage.getItem('examsys_theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');
      document.documentElement.setAttribute('data-theme',t);
    })();
  </script>
</head>
<body>
  <button id="themeBtn" class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema" aria-label="Cambiar tema">🌙</button>
  <script>document.getElementById('themeBtn').textContent=document.documentElement.getAttribute('data-theme')==='dark'?'☀️':'🌙';</script>

  <div class="page-header">
    <div class="header-inner">
      <a href="exames.php" class="btn-back">← Resultados</a>
      <div class="header-title">
        <h1>Estadísticas generales</h1>
        <p><?= $totalExamenes ?> examen<?= $totalExamenes !== 1 ? 'es' : '' ?> analizados</p>
      </div>
    </div>
  </div>

  <div class="content">

    <?php if ($totalExamenes === 0): ?>
      <div class="panel">
        <div class="empty-note">
          <p style="font-size:2rem; margin:0 0 8px">📊</p>
          <p>Aún no hay exámenes para analizar.</p>
        </div>
      </div>
    <?php else: ?>

    <!-- SUMMARY -->
    <div class="summary-grid">
      <div class="stat-card">
        <div class="val"><?= $totalExamenes ?></div>
        <div class="lbl">Exámenes</div>
      </div>
      <div class="stat-card">
        <div class="val"><?= $promedioPct ?>%</div>
        <div class="lbl">Promedio de aciertos</div>
      </div>
      <div class="stat-card">
        <div class="val"><?= fmtTiempo($promedioTiempo) ?></div>
        <div class="lbl">Tiempo promedio</div>
      </div>
      <div class="stat-card">
        <div class="val" style="color:<?= $pctAptos >= 50 ? '#43a047' : '#e53935' ?>"><?= $pctAptos ?>%</div>
        <div class="lbl">Aptos para prácticas</div>
        <div class="sub">Aceptable o Destacado</div>
      </div>
    </div>

    <div class="two-col">

      <!-- NIVEL DISTRIBUTION -->
      <div class="panel">
        <div class="panel-title">Distribución por nivel</div>
        <?php foreach ($NIVEL_ORDER as $nivel):
          $count = $nivelCount[$nivel] ?? 0;
          $pct   = $totalExamenes > 0 ? round($count / $totalExamenes * 100) : 0;
          $color = $NIVEL_COLOR[$nivel];
        ?>
          <div class="nivel-row">
            <div class="nivel-name"><?= htmlspecialchars($nivel) ?></div>
            <div class="bar-track">
              <div class="bar-fill" style="width:<?= $pct ?>%; background:<?= $color ?>"></div>
            </div>
            <div class="nivel-count"><?= $count ?></div>
            <div class="nivel-pct"><?= $pct ?>%</div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- CATEGORY PERFORMANCE -->
      <div class="panel">
        <div class="panel-title">Rendimiento por categoría <span style="font-weight:400;color:#ccc">(peor → mejor)</span></div>
        <?php if (empty($catSorted)): ?>
          <div class="empty-note">Sin datos de categoría aún.<br>Corre el backfill o realiza un nuevo examen.</div>
        <?php else:
          foreach ($catSorted as $cat => $v):
            $pct   = $v['total'] > 0 ? round($v['ok'] / $v['total'] * 100) : 0;
            $color = $pct >= 80 ? '#43a047' : ($pct >= 60 ? '#5c6bc0' : ($pct >= 40 ? '#039be5' : '#e53935'));
        ?>
          <div class="cat-row">
            <div class="cat-name"><?= htmlspecialchars($cat) ?></div>
            <div class="cat-track">
              <div class="cat-fill" style="width:<?= $pct ?>%; background:<?= $color ?>"></div>
            </div>
            <div class="cat-pct" style="color:<?= $color ?>"><?= $pct ?>%</div>
            <div class="cat-n"><?= $v['ok'] ?>/<?= $v['total'] ?> resp.</div>
          </div>
        <?php endforeach; endif; ?>
      </div>

    </div>

    <?php endif; ?>

  </div>

  <script>
    function toggleTheme() {
      var t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', t);
      localStorage.setItem('examsys_theme', t);
      document.getElementById('themeBtn').textContent = t === 'dark' ? '☀️' : '🌙';
    }
  </script>
</body>
</html>
