<?php
session_start();
if (empty($_SESSION['admin'])) {
  header('Location: index.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Resultados — Examen de Programación Web</title>
  <style>
    :root {
      --bg: #f2f5f8;
      --bg-card: #ffffff;
      --bg-sub: #f7f9fb;
      --text: #1a2a3a;
      --text-2: #555;
      --text-3: #888;
      --text-4: #bbb;
      --border: #dde3ec;
      --border-s: #f0f3f7;
      --sh: rgba(0, 0, 0, .08);
    }

    [data-theme="dark"] {
      --bg: #0e1520;
      --bg-card: #172030;
      --bg-sub: #1b2840;
      --text: #dce8f5;
      --text-2: #8facbe;
      --text-3: #7c92a3;
      --text-4: #3c5265;
      --border: #243147;
      --border-s: #192840;
      --sh: rgba(0, 0, 0, .35);
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
    }

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
      max-width: 680px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .btn-back {
      display: flex;
      align-items: center;
      gap: 6px;
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.25);
      color: white;
      text-decoration: none;
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 0.85rem;
      white-space: nowrap;
      transition: background .15s;
    }

    .btn-back:hover {
      background: rgba(255, 255, 255, 0.25);
      color: white;
    }

    .header-title {
      flex: 1;
    }

    .header-title h1 {
      margin: 0;
      font-size: 1.2rem;
      font-weight: 700;
    }

    .header-title p {
      margin: 4px 0 0;
      font-size: 0.82rem;
      opacity: .75;
    }

    /* CONTENT */
    .content {
      max-width: 680px;
      margin: 28px auto 48px;
      padding: 0 20px;
    }

    /* EMPTY */
    .empty {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
      padding: 48px 24px;
      text-align: center;
      color: #999;
    }

    /* EXAM CARD */
    .exam-card {
      background: white;
      border-radius: 14px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 18px 20px;
      margin-bottom: 12px;
      text-decoration: none;
      color: inherit;
      transition: box-shadow .2s, transform .15s;
      border: 1.5px solid transparent;
    }

    .exam-card:hover {
      box-shadow: 0 6px 24px rgba(43, 79, 128, .15);
      border-color: #c5d6f0;
      transform: translateY(-1px);
    }

    /* avatar */
    .avatar {
      flex-shrink: 0;
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: linear-gradient(135deg, #2b4f80, #3a6bad);
      color: white;
      font-size: 1.1rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* info */
    .exam-info {
      flex: 1;
      min-width: 0;
    }

    .exam-info .nombre {
      font-weight: 700;
      font-size: 0.97rem;
      color: #1a2a3a;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .exam-info .fecha {
      font-size: 0.78rem;
      color: #888;
      margin-top: 2px;
    }

    .exam-info .meta {
      font-size: 0.75rem;
      color: #bbb;
      margin-top: 4px;
      display: flex;
      gap: 10px;
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: 3px;
    }

    .tmpl-badge {
      display: inline-block;
      font-size: .72rem;
      font-weight: 600;
      color: #2b4f80;
      background: #e8eef7;
      border-radius: 5px;
      padding: 2px 7px;
      margin-top: 5px;
    }

    .email-line {
      font-size: .75rem;
      color: #888;
      margin-top: 3px;
    }

    .nota-snippet {
      font-size: .73rem;
      color: #888;
      margin-top: 4px;
      font-style: italic;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 320px;
    }

    /* nivel */
    .exam-nivel {
      text-align: right;
      white-space: nowrap;
    }

    .nivel-pill {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 99px;
      font-size: 0.78rem;
      font-weight: 700;
      color: white;
    }

    .exam-score {
      font-size: 0.75rem;
      color: #999;
      margin-top: 4px;
    }

    /* chevron */
    .chevron {
      color: #ccc;
      font-size: 1rem;
      flex-shrink: 0;
    }

    /* SEARCH & FILTER */
    .toolbar {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 16px;
    }

    .search-wrap {
      position: relative;
    }

    .search-wrap svg {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #aaa;
      pointer-events: none;
    }

    .search-input {
      width: 100%;
      padding: 10px 12px 10px 38px;
      border: 1.5px solid #dde3ec;
      border-radius: 10px;
      font-size: 0.9rem;
      font-family: inherit;
      background: white;
      outline: none;
      transition: border-color .15s, box-shadow .15s;
      color: #1a2a3a;
    }

    .search-input:focus {
      border-color: #2b4f80;
      box-shadow: 0 0 0 3px rgba(43, 79, 128, .12);
    }

    .filter-pills {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
      align-items: center;
    }

    .pill {
      padding: 5px 13px;
      border-radius: 99px;
      font-size: 0.78rem;
      font-weight: 600;
      cursor: pointer;
      border: 1.5px solid transparent;
      transition: background .15s, color .15s, border-color .15s;
      background: white;
      color: #555;
      border-color: #dde3ec;
    }

    .pill:hover {
      border-color: #aab8cc;
      color: #1a2a3a;
    }

    .pill.active {
      color: white;
      border-color: transparent;
    }

    .pill[data-nivel="todos"] {}

    .pill[data-nivel="todos"].active {
      background: #2b4f80;
      border-color: #2b4f80;
    }

    .pill[data-nivel="Sin base"].active {
      background: #e53935;
    }

    .pill[data-nivel="Básico"].active {
      background: #fb8c00;
    }

    .pill[data-nivel="En desarrollo"].active {
      background: #039be5;
    }

    .pill[data-nivel="Aceptable"].active {
      background: #5c6bc0;
    }

    .pill[data-nivel="Destacado"].active {
      background: #43a047;
    }

    .pill[data-tmpl].active {
      background: #2b4f80;
      border-color: #2b4f80;
    }

    .pill[data-preset].active {
      background: #2b4f80;
      border-color: #2b4f80;
    }

    .date-input {
      padding: 5px 9px;
      border: 1.5px solid #dde3ec;
      border-radius: 8px;
      font-size: 0.8rem;
      font-family: inherit;
      color: #1a2a3a;
      background: white;
      outline: none;
      cursor: pointer;
      height: 31px;
    }

    .date-input:focus {
      border-color: #2b4f80;
      box-shadow: 0 0 0 3px rgba(43, 79, 128, .12);
    }

    .date-sep {
      color: #bbb;
      font-size: .85rem;
    }

    .filter-row-label {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .5px;
      color: #aaa;
      align-self: center;
      white-space: nowrap;
    }

    .no-results {
      background: white;
      border-radius: 14px;
      padding: 36px 24px;
      text-align: center;
      color: #aaa;
      font-size: 0.9rem;
      display: none;
    }

    /* DELETE BTN en card */
    .btn-delete {
      flex-shrink: 0;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      border: none;
      background: transparent;
      color: #ccc;
      font-size: 1rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background .15s, color .15s;
      opacity: 0;
      pointer-events: none;
    }

    .exam-card:hover .btn-delete {
      opacity: 1;
      pointer-events: auto;
    }

    .btn-delete:hover {
      background: #fdecea;
      color: #e53935;
    }

    /* CONFIRM MODAL */
    .del-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .45);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }

    .del-overlay.active {
      display: flex;
    }

    .del-box {
      background: white;
      border-radius: 14px;
      padding: 32px 28px 24px;
      width: 90%;
      max-width: 340px;
      text-align: center;
      box-shadow: 0 8px 32px rgba(0, 0, 0, .18);
    }

    .del-box .del-icon {
      font-size: 2.2rem;
      margin-bottom: 10px;
    }

    .del-box h3 {
      margin: 0 0 6px;
      color: #1a2a3a;
      font-size: 1.05rem;
    }

    .del-box p {
      margin: 0 0 22px;
      color: #777;
      font-size: 0.86rem;
      line-height: 1.5;
    }

    .del-actions {
      display: flex;
      gap: 10px;
    }

    .del-actions button {
      flex: 1;
      padding: 10px;
      border-radius: 8px;
      font-size: 0.92rem;
      font-weight: 600;
      cursor: pointer;
      border: none;
    }

    .del-cancel {
      background: #f2f5f8;
      color: #555;
    }

    .del-cancel:hover {
      background: #e0e5eb;
    }

    .del-confirm {
      background: #e53935;
      color: white;
    }

    .del-confirm:hover {
      background: #c62828;
    }

    /* LOGOUT BTN */
    .btn-logout {
      display: flex;
      align-items: center;
      gap: 5px;
      background: rgba(255, 255, 255, 0.10);
      border: 1px solid rgba(255, 255, 255, 0.22);
      color: rgba(255, 255, 255, 0.85);
      text-decoration: none;
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 0.82rem;
      white-space: nowrap;
      transition: background .15s;
      cursor: pointer;
    }

    .btn-logout:hover {
      background: rgba(255, 255, 255, 0.22);
      color: white;
    }

    /* ── Responsive ─────────────────────────────────────────────── */

    /* Botón eliminar siempre visible en dispositivos touch */
    @media (hover: none) {
      .btn-delete {
        opacity: 1 !important;
        pointer-events: auto !important;
      }
    }

    @media (max-width: 640px) {

      /* Header: scroll horizontal para que no se rompa */
      .page-header {
        padding: 16px 16px 14px;
      }

      .header-inner {
        gap: 8px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 2px;
      }

      .header-inner::-webkit-scrollbar {
        display: none;
      }

      .header-title h1 {
        font-size: 1rem;
      }

      .btn-back,
      .btn-logout {
        padding: 5px 10px;
        font-size: 0.78rem;
      }

      /* Content */
      .content {
        margin-top: 20px;
        padding: 0 12px;
      }

      /* Tarjeta: reflotear a 2 filas */
      .exam-card {
        flex-wrap: wrap;
        align-items: flex-start;
        padding: 14px 14px 12px;
        gap: 8px 10px;
      }

      /* Fila 1: avatar + info + delete */
      .avatar {
        order: 1;
        width: 36px;
        height: 36px;
        font-size: .9rem;
        flex-shrink: 0;
      }

      .exam-info {
        order: 2;
        flex: 1;
        min-width: 0;
      }

      .btn-delete {
        order: 3;
        opacity: 1;
        pointer-events: auto;
        flex-shrink: 0;
        align-self: flex-start;
        margin-top: 2px;
      }

      /* Fila 2: nivel alineado bajo la info */
      .chevron {
        display: none;
      }

      .exam-nivel {
        order: 4;
        width: 100%;
        display: flex;
        align-items: center;
        gap: 8px;
        text-align: left;
        padding-left: 46px;
        /* 36px avatar + 10px gap */
        white-space: normal;
      }

      .exam-score {
        margin-top: 0;
        font-size: 0.72rem;
      }

      /* Nota snippet: sin ancho fijo */
      .nota-snippet {
        max-width: 100%;
      }

      /* Filtro de fecha: inputs crecen */
      .date-input {
        flex: 1;
        min-width: 100px;
      }

      /* Pills de nivel en móvil: fuente un poco más chica */
      .pill {
        padding: 4px 10px;
        font-size: 0.74rem;
      }
    }

    @media (max-width: 400px) {

      /* En pantallas muy pequeñas ocultar avatar para ganar espacio */
      .avatar {
        display: none;
      }

      .exam-nivel {
        padding-left: 0;
      }
    }

    /* ── View Transitions ───────────────── */
    @view-transition {
      navigation: auto;
    }

    @keyframes vt-out {
      to {
        opacity: 0;
        translate: 0 -6px;
      }
    }

    @keyframes vt-in {
      from {
        opacity: 0;
        translate: 0 10px;
      }
    }

    ::view-transition-old(root) {
      animation: 180ms ease both vt-out;
    }

    ::view-transition-new(root) {
      animation: 280ms ease both vt-in;
    }

    /* ── Theme toggle ───────────────── */
    .theme-toggle {
      position: fixed;
      bottom: 22px;
      right: 20px;
      z-index: 8000;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 1px solid var(--border);
      background: var(--bg-card);
      color: var(--text-2);
      font-size: .9rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background .2s, box-shadow .2s;
      padding: 0;
      line-height: 1;
      box-shadow: 0 2px 8px var(--sh);
    }

    .theme-toggle:hover {
      box-shadow: 0 4px 14px var(--sh);
      background: var(--bg-sub);
    }
    @media print { .theme-toggle { display: none !important; } }

    /* ── Dark overrides ─────────────── */
    [data-theme="dark"] body {
      background: var(--bg);
    }

    [data-theme="dark"] .empty,
    [data-theme="dark"] .no-results {
      background: var(--bg-card);
      color: var(--text-3);
    }

    [data-theme="dark"] .exam-card {
      background: var(--bg-card);
      box-shadow: 0 2px 12px var(--sh);
    }

    [data-theme="dark"] .exam-card:hover {
      box-shadow: 0 6px 24px rgba(74, 123, 196, .18);
      border-color: #253550;
    }

    [data-theme="dark"] .exam-info .nombre {
      color: var(--text);
    }

    [data-theme="dark"] .exam-info .fecha {
      color: var(--text-3);
    }

    [data-theme="dark"] .exam-info .meta {
      color: var(--text-4);
    }

    [data-theme="dark"] .tmpl-badge {
      background: #1b2e48;
      color: #7aacde;
    }

    [data-theme="dark"] .email-line {
      color: var(--text-3);
    }

    [data-theme="dark"] .nota-snippet {
      color: var(--text-3);
    }

    [data-theme="dark"] .exam-score {
      color: var(--text-3);
    }

    [data-theme="dark"] .chevron {
      color: var(--text-4);
    }

    [data-theme="dark"] .search-input {
      background: var(--bg-card);
      border-color: var(--border);
      color: var(--text);
    }

    [data-theme="dark"] .search-input:focus {
      border-color: #4a7bc4;
      box-shadow: 0 0 0 3px rgba(74, 123, 196, .12);
    }

    [data-theme="dark"] .search-wrap svg {
      color: var(--text-3);
    }

    [data-theme="dark"] .pill {
      background: var(--bg-card);
      color: var(--text-2);
      border-color: var(--border);
    }

    [data-theme="dark"] .pill:hover {
      border-color: #4a7bc4;
      color: var(--text);
    }

    [data-theme="dark"] .date-input {
      background: var(--bg-card);
      border-color: var(--border);
      color: var(--text);
      color-scheme: dark;
    }

    [data-theme="dark"] .date-input:focus {
      border-color: #4a7bc4;
    }

    [data-theme="dark"] .date-sep {
      color: var(--text-4);
    }

    [data-theme="dark"] .filter-row-label {
      color: var(--text-4);
    }

    [data-theme="dark"] .btn-delete:hover {
      background: rgba(229, 57, 53, .15);
      color: #ef5350;
    }

    [data-theme="dark"] .del-overlay {
      background: rgba(0, 0, 0, .65);
    }

    [data-theme="dark"] .del-box {
      background: var(--bg-card);
    }

    [data-theme="dark"] .del-box h3 {
      color: var(--text);
    }

    [data-theme="dark"] .del-box p {
      color: var(--text-2);
    }

    [data-theme="dark"] .del-cancel {
      background: var(--bg-sub);
      color: var(--text-2);
    }

    [data-theme="dark"] .del-cancel:hover {
      background: var(--border);
    }
  </style>
  <script>
    (function() {
      var t = localStorage.getItem('examsys_theme') || (window.matchMedia('(prefers-color-scheme:dark)').matches ?
        'dark' : 'light');
      document.documentElement.setAttribute('data-theme', t);
    })();
  </script>
</head>

<?php
include_once 'server/conexion.php';

function formatTiempo(?int $seg): string
{
  if ($seg === null) return '—';
  return floor($seg / 60) . ':' . str_pad($seg % 60, 2, '0', STR_PAD_LEFT);
}

function getNivel(int $aciertos, int $total): array
{
  if ($total === 0) return ['label' => 'Sin datos', 'color' => '#9e9e9e'];
  $pct = $aciertos / $total;
  if ($pct < 0.21) return ['label' => 'Sin base',      'color' => '#e53935'];
  if ($pct < 0.41) return ['label' => 'Básico',         'color' => '#fb8c00'];
  if ($pct < 0.62) return ['label' => 'En desarrollo',  'color' => '#039be5'];
  if ($pct < 0.82) return ['label' => 'Aceptable',      'color' => '#5c6bc0'];
  return                   ['label' => 'Destacado',      'color' => '#43a047'];
}

try {
  $conexion = new ConexionPDO();
  $pdo = $conexion->Conexion();
  $stmt = $pdo->query(
    "SELECT r.id, r.usuario, r.email, r.fecha, r.respuestas, r.tiempo_segundos, r.cambios_foco,
                r.template_slug, r.nota, t.nombre AS template_nombre
         FROM resultados r
         LEFT JOIN exam_templates t ON r.template_slug = t.slug
         ORDER BY r.fecha DESC"
  );
  $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $resultados = [];
}

// Plantillas únicas presentes en los resultados
$templates = [];
$hayResultadosSinPlantilla = false;
foreach ($resultados as $r) {
  if (!empty($r['template_slug'])) {
    $templates[$r['template_slug']] = $r['template_nombre'] ?: $r['template_slug'];
  } else {
    $hayResultadosSinPlantilla = true;
  }
}
?>

<body>
  <button id="themeBtn" class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema"
    aria-label="Cambiar tema">🌙</button>
  <script>
    document.getElementById('themeBtn').textContent = document.documentElement.getAttribute('data-theme') === 'dark' ?
      '☀️' : '🌙';
  </script>

  <div class="page-header">
    <div class="header-inner">
      <a href="index.php" class="btn-back">← Inicio</a>
      <div class="header-title">
        <h1>Resultados del examen</h1>
        <p id="header-count"><?= count($resultados) ?> registro<?= count($resultados) !== 1 ? 's' : '' ?></p>
      </div>
      <a href="estadisticas.php" class="btn-back"
        style="background:rgba(255,255,255,0.10);border-color:rgba(255,255,255,0.20)">📊 Stats</a>
      <a href="server/exportar_resultados.php" class="btn-back"
        style="background:rgba(255,255,255,0.10);border-color:rgba(255,255,255,0.20)"
        title="Descargar todos los resultados como CSV">⬇ CSV</a>
      <a href="admin.php" class="btn-back"
        style="background:rgba(255,255,255,0.10);border-color:rgba(255,255,255,0.20)">⚙️ Admin</a>
      <a href="server/logout.php" class="btn-logout" title="Cerrar sesión">⏻ Salir</a>
    </div>
  </div>

  <div class="content">

    <?php if (empty($resultados)): ?>
      <div class="empty">
        <p style="font-size:2rem; margin:0 0 8px">📋</p>
        <p>Aún no hay exámenes registrados.</p>
      </div>
    <?php else: ?>

      <!-- TOOLBAR -->
      <div class="toolbar">
        <div class="search-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path
              d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.156a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z" />
          </svg>
          <input type="search" class="search-input" id="searchInput" placeholder="Buscar por nombre…"
            oninput="filtrar()" autocomplete="off" />
        </div>
        <div class="filter-pills" id="filterPills">
          <span class="filter-row-label">Nivel</span>
          <button class="pill active" data-nivel="todos" onclick="setPill(this)">Todos</button>
          <button class="pill" data-nivel="Sin base" onclick="setPill(this)">Sin base</button>
          <button class="pill" data-nivel="Básico" onclick="setPill(this)">Básico</button>
          <button class="pill" data-nivel="En desarrollo" onclick="setPill(this)">En desarrollo</button>
          <button class="pill" data-nivel="Aceptable" onclick="setPill(this)">Aceptable</button>
          <button class="pill" data-nivel="Destacado" onclick="setPill(this)">Destacado</button>
        </div>
        <div class="filter-pills" id="datePills">
          <span class="filter-row-label">Fecha</span>
          <button class="pill active" data-preset="all" onclick="setDatePreset(this,'all')">Todo</button>
          <button class="pill" data-preset="today" onclick="setDatePreset(this,'today')">Hoy</button>
          <button class="pill" data-preset="7d" onclick="setDatePreset(this,'7d')">7 días</button>
          <button class="pill" data-preset="30d" onclick="setDatePreset(this,'30d')">30 días</button>
          <input type="date" id="fechaDesde" class="date-input" onchange="onDateChange()" title="Desde">
          <span class="date-sep">–</span>
          <input type="date" id="fechaHasta" class="date-input" onchange="onDateChange()" title="Hasta">
        </div>
        <?php if (!empty($templates) || $hayResultadosSinPlantilla): ?>
          <div class="filter-pills" id="tmplPills">
            <span class="filter-row-label">Plantilla</span>
            <button class="pill active" data-tmpl="todos" onclick="setTmplPill(this)">Todas</button>
            <?php foreach ($templates as $slug => $nombre): ?>
              <button class="pill" data-tmpl="<?= htmlspecialchars($slug) ?>" onclick="setTmplPill(this)">
                📋 <?= htmlspecialchars($nombre) ?>
              </button>
            <?php endforeach; ?>
            <?php if ($hayResultadosSinPlantilla): ?>
              <button class="pill" data-tmpl="" onclick="setTmplPill(this)">Sin plantilla</button>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- CARDS -->
      <div id="cardList">
        <?php foreach ($resultados as $r):
          $respuestas = json_decode($r['respuestas'], true) ?? [];
          $aciertos = 0;
          foreach ($respuestas as $i => $ans) {
            if ($i === 0) continue;
            if (!empty($ans['correct'])) $aciertos++;
          }
          $total = max(count($respuestas) - 1, 0);
          $nivel = getNivel($aciertos, $total);
          $inicial = mb_strtoupper(mb_substr(trim($r['usuario']), 0, 1));
        ?>
          <a href="detalle_examen.php?id=<?= (int)$r['id'] ?>" class="exam-card"
            data-nombre="<?= htmlspecialchars(mb_strtolower($r['usuario'])) ?>"
            data-nivel="<?= htmlspecialchars($nivel['label']) ?>"
            data-tmpl="<?= htmlspecialchars($r['template_slug'] ?? '') ?>"
            data-fecha="<?= htmlspecialchars($r['fecha']) ?>" data-id="<?= (int)$r['id'] ?>">
            <div class="avatar"><?= htmlspecialchars($inicial) ?></div>
            <div class="exam-info">
              <div class="nombre"><?= htmlspecialchars($r['usuario']) ?></div>
              <?php if (!empty($r['email'])): ?>
                <div class="email-line">✉ <?= htmlspecialchars($r['email']) ?></div>
              <?php endif; ?>
              <div class="fecha"><?= htmlspecialchars($r['fecha']) ?></div>
              <?php if (!empty($r['template_nombre'])): ?>
                <span class="tmpl-badge">📋 <?= htmlspecialchars($r['template_nombre']) ?></span>
              <?php endif; ?>
              <?php if (!empty($r['nota'])): ?>
                <div class="nota-snippet">📝 <?= htmlspecialchars(mb_strimwidth($r['nota'], 0, 80, '…')) ?></div>
              <?php endif; ?>
              <div class="meta">
                <span class="meta-item">⏱ <?= formatTiempo($r['tiempo_segundos'] ?? null) ?></span>
                <span class="meta-item">👁 <?= $r['cambios_foco'] ?? '—' ?>
                  <?= ($r['cambios_foco'] === '1') ? 'cambio' : 'cambios' ?></span>
              </div>
            </div>
            <div class="exam-nivel">
              <span class="nivel-pill" style="background:<?= $nivel['color'] ?>">
                <?= $nivel['label'] ?>
              </span>
              <div class="exam-score"><?= $aciertos ?> / <?= $total ?> aciertos</div>
            </div>
            <span class="chevron">›</span>
            <button class="btn-delete" title="Eliminar examen" onclick="pedirConfirmacion(event, this)">🗑</button>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="no-results" id="noResults">
        <p style="font-size:1.6rem; margin:0 0 6px">🔍</p>
        <p>No se encontraron resultados con ese filtro.</p>
      </div>

    <?php endif; ?>

  </div>

  <!-- MODAL CONFIRMACIÓN BORRAR -->
  <div class="del-overlay" id="delOverlay">
    <div class="del-box">
      <div class="del-icon">🗑️</div>
      <h3>¿Eliminar este examen?</h3>
      <p id="delMsg">Esta acción no se puede deshacer.</p>
      <div class="del-actions">
        <button class="del-cancel" onclick="cerrarDel()">Cancelar</button>
        <button class="del-confirm" id="delBtn" onclick="confirmarEliminar()">Eliminar</button>
      </div>
    </div>
  </div>

  <script>
    function toggleTheme() {
      var t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', t);
      localStorage.setItem('examsys_theme', t);
      document.getElementById('themeBtn').textContent = t === 'dark' ? '☀️' : '🌙';
    }

    let activePill = 'todos';
    let activeTemplate = 'todos';
    let fechaDesde = null; // Date | null
    let fechaHasta = null; // Date | null

    // ── Helpers de fecha ────────────────────────────────────────────
    function toDateValue(d) {
      return d.toISOString().slice(0, 10);
    }

    function startOfDay(d) {
      const r = new Date(d);
      r.setHours(0, 0, 0, 0);
      return r;
    }

    function endOfDay(d) {
      const r = new Date(d);
      r.setHours(23, 59, 59, 999);
      return r;
    }

    function setDatePreset(btn, preset) {
      document.querySelectorAll('#datePills .pill').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      const hoy = new Date();
      const desde = document.getElementById('fechaDesde');
      const hasta = document.getElementById('fechaHasta');
      if (preset === 'all') {
        desde.value = '';
        hasta.value = '';
        fechaDesde = null;
        fechaHasta = null;
      } else if (preset === 'today') {
        desde.value = hasta.value = toDateValue(hoy);
        fechaDesde = startOfDay(hoy);
        fechaHasta = endOfDay(hoy);
      } else if (preset === '7d') {
        const d = new Date(hoy);
        d.setDate(d.getDate() - 6);
        desde.value = toDateValue(d);
        hasta.value = toDateValue(hoy);
        fechaDesde = startOfDay(d);
        fechaHasta = endOfDay(hoy);
      } else if (preset === '30d') {
        const d = new Date(hoy);
        d.setDate(d.getDate() - 29);
        desde.value = toDateValue(d);
        hasta.value = toDateValue(hoy);
        fechaDesde = startOfDay(d);
        fechaHasta = endOfDay(hoy);
      }
      filtrar();
    }

    function onDateChange() {
      document.querySelectorAll('#datePills .pill').forEach(p => p.classList.remove('active'));
      const dv = document.getElementById('fechaDesde').value;
      const hv = document.getElementById('fechaHasta').value;
      fechaDesde = dv ? startOfDay(new Date(dv + 'T00:00:00')) : null;
      fechaHasta = hv ? endOfDay(new Date(hv + 'T00:00:00')) : null;
      filtrar();
    }

    function setPill(btn) {
      document.querySelectorAll('#filterPills .pill').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      activePill = btn.dataset.nivel;
      filtrar();
    }

    function setTmplPill(btn) {
      document.querySelectorAll('#tmplPills .pill').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      activeTemplate = btn.dataset.tmpl; // 'todos', slug, o '' (sin plantilla)
      filtrar();
    }

    function filtrar() {
      const q = (document.getElementById('searchInput')?.value ?? '').toLowerCase().trim();
      const cards = document.querySelectorAll('#cardList .exam-card');
      let visible = 0;

      cards.forEach(card => {
        const nombre = card.dataset.nombre ?? '';
        const nivel = card.dataset.nivel ?? '';
        const tmpl = card.dataset.tmpl ?? '';
        const fecha = card.dataset.fecha ?? '';
        const matchNombre = nombre.includes(q);
        const matchNivel = activePill === 'todos' || nivel === activePill;
        const matchTmpl = activeTemplate === 'todos' || tmpl === activeTemplate;
        let matchFecha = true;
        if (fechaDesde || fechaHasta) {
          const cardDate = fecha ? new Date(fecha.replace(' ', 'T')) : null;
          if (!cardDate || isNaN(cardDate)) {
            matchFecha = false;
          } else {
            if (fechaDesde && cardDate < fechaDesde) matchFecha = false;
            if (fechaHasta && cardDate > fechaHasta) matchFecha = false;
          }
        }
        const show = matchNombre && matchNivel && matchTmpl && matchFecha;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
      });

      // update counter
      const counter = document.getElementById('header-count');
      if (counter) {
        counter.textContent = visible + ' registro' + (visible !== 1 ? 's' : '');
      }

      // empty state
      const noResults = document.getElementById('noResults');
      if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
    }

    // ── Eliminar ────────────────────────────────────────────────────
    let pendingCard = null;
    let pendingId = null;

    function pedirConfirmacion(e, btn) {
      e.preventDefault();
      e.stopPropagation();
      const card = btn.closest('.exam-card');
      const nombre = card.querySelector('.nombre')?.textContent ?? 'este examen';
      pendingCard = card;
      pendingId = parseInt(card.dataset.id);
      document.getElementById('delMsg').textContent =
        `Se borrará el examen de "${nombre}". Esta acción no se puede deshacer.`;
      document.getElementById('delOverlay').classList.add('active');
    }

    function cerrarDel() {
      document.getElementById('delOverlay').classList.remove('active');
      pendingCard = null;
      pendingId = null;
    }

    function confirmarEliminar() {
      if (!pendingId) return;
      const btn = document.getElementById('delBtn');
      btn.disabled = true;
      btn.textContent = '…';

      fetch('server/eliminar_resultado.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            id: pendingId
          }),
        })
        .then(r => r.json())
        .then(data => {
          if (data.ok) {
            // animación de salida y eliminación del DOM
            const cardToRemove = pendingCard;
            cerrarDel(); // limpia pendingCard antes del timeout
            cardToRemove.style.transition = 'opacity .25s, transform .25s';
            cardToRemove.style.opacity = '0';
            cardToRemove.style.transform = 'scale(.97)';
            setTimeout(() => {
              cardToRemove.remove();
              filtrar(); // actualiza contador
            }, 260);
          } else {
            alert('Error al eliminar: ' + (data.error ?? 'intenta de nuevo'));
          }
          btn.disabled = false;
          btn.textContent = 'Eliminar';
        })
        .catch(() => {
          alert('Error de conexión. Intenta de nuevo.');
          btn.disabled = false;
          btn.textContent = 'Eliminar';
        });
    }

    // cerrar al click fuera del modal
    document.getElementById('delOverlay').addEventListener('click', function(e) {
      if (e.target === this) cerrarDel();
    });
  </script>

</body>

</html>