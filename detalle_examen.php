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
  <title>Detalle del examen</title>
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
      max-width: 600px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .btn-back {
      display: flex;
      align-items: center;
      gap: 6px;
      background: rgba(255,255,255,0.15);
      border: 1px solid rgba(255,255,255,0.25);
      color: white;
      text-decoration: none;
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 0.85rem;
      white-space: nowrap;
      transition: background .15s;
    }
    .btn-back:hover { background: rgba(255,255,255,0.25); color: white; }
    .header-title { flex: 1; }
    .header-title h1  { margin: 0; font-size: 1.2rem; font-weight: 700; }
    .header-title span { font-size: 0.82rem; opacity: .75; }

    /* CARD */
    .card {
      background: white;
      max-width: 600px;
      margin: 28px auto 48px;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0,0,0,.10);
      overflow: hidden;
    }

    /* SCORE CARD */
    .score-card {
      background: linear-gradient(135deg, #1d3759, #2b4f80);
      color: white;
      text-align: center;
      padding: 32px 24px;
    }
    .score-card .score-num {
      font-size: 3.5rem;
      font-weight: 800;
      line-height: 1;
    }
    .score-card .score-den {
      font-size: 1rem;
      opacity: .75;
      margin-bottom: 16px;
    }
    .nivel-pill {
      display: inline-block;
      padding: 6px 20px;
      border-radius: 99px;
      font-size: 0.95rem;
      font-weight: 700;
      margin-bottom: 6px;
    }
    .score-card .nivel-desc {
      font-size: 0.83rem;
      opacity: .8;
    }
    .exam-stats {
      display: flex;
      justify-content: center;
      gap: 24px;
      margin-top: 18px;
      padding-top: 16px;
      border-top: 1px solid rgba(255,255,255,.2);
    }
    .stat-item { text-align: center; }
    .stat-item .stat-val {
      font-size: 1.3rem;
      font-weight: 800;
      line-height: 1;
    }
    .stat-item .stat-lbl {
      font-size: 0.72rem;
      opacity: .7;
      margin-top: 3px;
      text-transform: uppercase;
      letter-spacing: .5px;
    }

    /* REVIEW */
    .review {
      padding: 20px 24px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .review-item {
      border-radius: 10px;
      padding: 14px 16px;
      font-size: 0.88rem;
      line-height: 1.55;
    }
    .review-item.correct {
      background: #e8f5e9;
      border: 1.5px solid #4caf50;
      color: #1b5e20;
    }
    .review-item.wrong {
      background: #ffebee;
      border: 1.5px solid #ef5350;
      color: #b71c1c;
    }
    .ri-num {
      font-weight: 700;
      font-size: 0.78rem;
      text-transform: uppercase;
      letter-spacing: .5px;
      opacity: .65;
      margin-bottom: 4px;
    }
    .ri-q   { font-weight: 600; margin-bottom: 6px; }
    .ri-ans { font-size: 0.85rem; }
    .ri-ans span { font-weight: 700; }

    /* CATEGORY BREAKDOWN */
    .cat-breakdown {
      padding: 16px 24px 4px;
      border-bottom: 1px solid #f0f3f7;
    }
    .cat-breakdown-title {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .6px;
      color: #aaa;
      margin-bottom: 10px;
    }
    .cat-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 16px;
    }
    .cat-item {
      display: flex;
      align-items: center;
      gap: 6px;
      background: #f6f8fb;
      border-radius: 8px;
      padding: 7px 12px;
      min-width: 110px;
      flex: 1;
    }
    .cat-label {
      font-size: 0.78rem;
      font-weight: 700;
      color: #1a2a3a;
      flex: 1;
    }
    .cat-score {
      font-size: 0.78rem;
      font-weight: 700;
    }
    .cat-dots {
      display: flex;
      gap: 3px;
      margin-left: 2px;
    }
    .cat-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
    }
    .cat-dot.ok   { background: #4caf50; }
    .cat-dot.fail { background: #ef5350; }

    /* LOADING / ERROR */
    .status {
      padding: 48px 24px;
      text-align: center;
      color: #999;
      font-size: 0.95rem;
    }

    /* BOTÓN ELIMINAR */
    .btn-delete {
      display: flex;
      align-items: center;
      gap: 6px;
      background: rgba(229,57,53,0.18);
      border: 1px solid rgba(229,57,53,0.35);
      color: #ffcdd2;
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 0.85rem;
      font-family: inherit;
      cursor: pointer;
      white-space: nowrap;
      transition: background .15s;
    }
    .btn-delete:hover { background: rgba(229,57,53,0.35); color: white; }

    /* CONFIRM MODAL */
    .del-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.45);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }
    .del-overlay.active { display: flex; }
    .del-box {
      background: white;
      border-radius: 14px;
      padding: 32px 28px 24px;
      width: 90%;
      max-width: 340px;
      text-align: center;
      box-shadow: 0 8px 32px rgba(0,0,0,.18);
    }
    .del-box .del-icon { font-size: 2.2rem; margin-bottom: 10px; }
    .del-box h3 { margin: 0 0 6px; color: #1a2a3a; font-size: 1.05rem; }
    .del-box p  { margin: 0 0 22px; color: #777; font-size: 0.86rem; line-height: 1.5; }
    .del-actions { display: flex; gap: 10px; }
    .del-actions button {
      flex: 1; padding: 10px; border-radius: 8px;
      font-size: 0.92rem; font-weight: 600;
      cursor: pointer; border: none;
    }
    .del-cancel  { background: #f2f5f8; color: #555; }
    .del-cancel:hover  { background: #e0e5eb; }
    .del-confirm { background: #e53935; color: white; }
    .del-confirm:hover { background: #c62828; }

    /* BOTÓN PDF */
    .btn-pdf {
      display: flex;
      align-items: center;
      gap: 6px;
      background: rgba(255,255,255,0.15);
      border: 1px solid rgba(255,255,255,0.25);
      color: white;
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 0.85rem;
      font-family: inherit;
      cursor: pointer;
      white-space: nowrap;
      transition: background .15s;
    }
    .btn-pdf:hover { background: rgba(255,255,255,0.28); }

    /* ENCABEZADO SOLO PARA IMPRESIÓN */
    .print-header { display: none; }

    /* ── NOTA DEL RECLUTADOR ───────────────── */
    .nota-section {
      padding: 16px 24px 24px;
      border-top: 1px solid #f0f3f7;
    }
    .nota-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    .nota-title {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .6px;
      color: #aaa;
    }
    .nota-status {
      font-size: 0.75rem;
      font-weight: 600;
      color: #43a047;
      min-width: 80px;
      text-align: right;
    }
    .nota-input {
      width: 100%;
      min-height: 82px;
      padding: 10px 13px;
      border: 1.5px solid #e0e7ef;
      border-radius: 10px;
      font-size: 0.88rem;
      font-family: inherit;
      color: #1a2a3a;
      background: #fafbfd;
      outline: none;
      resize: vertical;
      line-height: 1.55;
      transition: border-color .15s, background .15s, box-shadow .15s;
    }
    .nota-input:focus {
      border-color: #2b4f80;
      background: white;
      box-shadow: 0 0 0 3px rgba(43,79,128,.10);
    }
    .btn-nota-save {
      margin-top: 8px;
      padding: 7px 18px;
      background: #2b4f80;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 0.82rem;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
      transition: background .15s;
    }
    .btn-nota-save:hover { background: #1d3759; }

    /* ── @media print ───────────────────── */
    @media print {
      @page { margin: 20mm 16mm; }

      body { background: white; }

      /* ocultar navegación y botones */
      .page-header,
      .btn-pdf       { display: none !important; }

      /* mostrar encabezado de impresión */
      .print-header {
        display: block;
        border-bottom: 2px solid #2b4f80;
        padding-bottom: 12px;
        margin-bottom: 20px;
        color: #1a2a3a;
      }
      .print-header h2 { margin: 0 0 6px; font-size: 1.2rem; color: #2b4f80; }
      .print-header p  { margin: 2px 0; font-size: 0.88rem; color: #444; }

      /* card sin sombra ni redondeo */
      .card {
        box-shadow: none !important;
        border-radius: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        overflow: visible !important;
      }

      /* score card: cambiar gradiente por borde */
      .score-card {
        background: white !important;
        color: #1a2a3a !important;
        border: 2px solid #2b4f80;
        border-radius: 10px !important;
        margin: 0 0 16px;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      .score-card .score-den,
      .score-card .nivel-desc { color: #555 !important; opacity: 1 !important; }

      /* nivel pill — forzar color en impresión */
      .nivel-pill {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      /* ocultar desglose de categorías, cambios de pestaña y modal */
      .cat-breakdown,
      .stat-foco,
      .del-overlay { display: none !important; }

      /* badge de plantilla: forzar color oscuro sobre fondo blanco */
      .tmpl-print-badge { color: #555 !important; }

      /* notas del reclutador: visibles en PDF sin bordes ni botón */
      .nota-section { padding: 12px 0 0; border-top: 1px solid #ddd; }
      .btn-nota-save { display: none !important; }
      .nota-input {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 2px 0 !important;
        resize: none !important;
        min-height: 0 !important;
        font-size: 0.85rem !important;
        color: #333 !important;
      }

      /* review */
      .review { padding: 0; gap: 10px; }
      .review-item {
        break-inside: avoid;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      .review-item.correct { background: #e8f5e9 !important; border-color: #4caf50 !important; }
      .review-item.wrong   { background: #ffebee !important; border-color: #ef5350 !important; }
    }

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
    [data-theme="dark"] .card { background: var(--bg-card); box-shadow: 0 4px 20px var(--sh); }
    [data-theme="dark"] .score-card { background: linear-gradient(135deg,#091422,#102040); }
    [data-theme="dark"] .cat-breakdown { border-bottom-color: var(--border-s); }
    [data-theme="dark"] .cat-breakdown-title { color: var(--text-3); }
    [data-theme="dark"] .cat-item { background: var(--bg-sub); }
    [data-theme="dark"] .cat-label { color: var(--text); }
    [data-theme="dark"] .status { color: var(--text-3); }
    [data-theme="dark"] .review-item.correct { background: #0d2218; border-color: #2e7d32; color: #81c784; }
    [data-theme="dark"] .review-item.wrong { background: #1e0c0c; border-color: #b71c1c; color: #ef9a9a; }
    [data-theme="dark"] .nota-section { border-top-color: var(--border-s); }
    [data-theme="dark"] .nota-title { color: var(--text-3); }
    [data-theme="dark"] .nota-input { background: var(--bg-sub); border-color: var(--border); color: var(--text); }
    [data-theme="dark"] .nota-input:focus { border-color: #4a7bc4; background: var(--bg-sub); box-shadow: 0 0 0 3px rgba(74,123,196,.12); }
    [data-theme="dark"] .del-overlay { background: rgba(0,0,0,.65); }
    [data-theme="dark"] .del-box  { background: var(--bg-card); }
    [data-theme="dark"] .del-box h3 { color: var(--text); }
    [data-theme="dark"] .del-box p  { color: var(--text-2); }
    [data-theme="dark"] .del-cancel { background: var(--bg-sub); color: var(--text-2); }
    [data-theme="dark"] .del-cancel:hover { background: var(--border); }
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
        <h1 id="header-nombre">Cargando…</h1>
        <span id="header-fecha"></span>
        <span id="header-email" style="font-size:.75rem;opacity:.6;display:none"></span>
      </div>
      <button class="btn-delete" onclick="abrirDel()">🗑 Eliminar</button>
      <button class="btn-pdf" onclick="printPDF()">⬇ PDF</button>
    </div>
  </div>

  <!-- MODAL CONFIRMAR BORRAR -->
  <div class="del-overlay" id="delOverlay">
    <div class="del-box">
      <div class="del-icon">🗑️</div>
      <h3>¿Eliminar este examen?</h3>
      <p id="delMsg">Esta acción no se puede deshacer.</p>
      <div class="del-actions">
        <button class="del-cancel"  onclick="cerrarDel()">Cancelar</button>
        <button class="del-confirm" id="delBtn" onclick="confirmarEliminar()">Eliminar</button>
      </div>
    </div>
  </div>

  <div class="card" id="card">
    <!-- Encabezado visible solo en impresión -->
    <div class="print-header" id="print-header">
      <h2>Examen de Programación Web</h2>
      <p><strong>Alumno:</strong> <span id="print-nombre"></span></p>
      <p id="print-email-row" style="display:none"><strong>Email:</strong> <span id="print-email-val"></span></p>
      <p><strong>Fecha:</strong> <span id="print-fecha"></span></p>
    </div>

    <div class="status" id="status">Cargando examen…</div>
    <div id="score-area"></div>
    <div class="cat-breakdown" id="cat-breakdown" style="display:none"></div>
    <div class="review" id="review"></div>
    <div class="nota-section" id="notaSection" style="display:none">
      <div class="nota-header">
        <span class="nota-title">📝 Notas del reclutador</span>
        <span class="nota-status" id="notaStatus"></span>
      </div>
      <textarea class="nota-input" id="notaInput"
                placeholder="Escribe observaciones sobre este candidato…"
                rows="3"></textarea>
      <button class="btn-nota-save" onclick="guardarNota()">Guardar</button>
    </div>
  </div>

  <script>
    function toggleTheme() {
      var t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', t);
      localStorage.setItem('examsys_theme', t);
      document.getElementById('themeBtn').textContent = t === 'dark' ? '☀️' : '🌙';
    }

    function printPDF() {
      const nombre = document.getElementById("print-nombre").textContent.trim();
      const slug   = nombre
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // quita acentos
        .toLowerCase()
        .replace(/\s+/g, "-")        // espacios → guión
        .replace(/[^a-z0-9-]/g, ""); // elimina caracteres especiales
      const prev   = document.title;
      document.title = `examen-${slug}`;
      window.print();
      document.title = prev;
    }

    const LETTERS = ["A", "B", "C", "D", "E"];

    function escH(t) {
      return String(t)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
    }

    function formatTime(seg) {
      if (seg === null || seg === undefined) return '—';
      const m = Math.floor(seg / 60);
      const s = String(seg % 60).padStart(2, '0');
      return `${m}:${s}`;
    }

    function getNivel(aciertos, total) {
      if (total === 0) return { label: 'Sin datos', bg: '#9e9e9e', desc: '' };
      const pct = aciertos / total;
      if (pct < 0.21) return { label: 'Sin base',      bg: '#e53935', desc: 'No cuenta con los conocimientos mínimos para prácticas' };
      if (pct < 0.41) return { label: 'Básico',         bg: '#fb8c00', desc: 'Conoce conceptos aislados, requiere mucha formación' };
      if (pct < 0.62) return { label: 'En desarrollo',  bg: '#039be5', desc: 'Maneja la lógica básica, puede aprender durante las prácticas' };
      if (pct < 0.82) return { label: 'Aceptable',      bg: '#5c6bc0', desc: 'Buen manejo del stack, listo para integrarse con guía' };
      return                  { label: 'Destacado',      bg: '#43a047', desc: 'Dominio sólido del stack, candidato ideal para prácticas' };
    }

    const id = new URLSearchParams(window.location.search).get("id");

    fetch("server/resultado_unico.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id }),
    })
    .then(r => r.json())
    .then(data => {
      const questions = JSON.parse(data.respuestas);

      // header pantalla
      const nombre = questions[0]?.res ?? "—";
      const fecha  = data.fecha ?? "";
      document.getElementById("header-nombre").textContent = nombre;
      document.getElementById("header-fecha").textContent  = fecha;

      // header impresión
      document.getElementById("print-nombre").textContent = nombre;
      document.getElementById("print-fecha").textContent  = fecha;

      // score
      let score = 0;
      const total = questions.length - 1;
      questions.forEach((p, i) => {
        if (i === 0) return;
        if (p.res === p.resCorrecta) score++;
      });

      const nivel = getNivel(score, total);

      document.getElementById("status").style.display = "none";
      const tiempo        = data.tiempo_segundos  ?? null;
      const foco          = data.cambios_foco    ?? null;
      const tmplNombre    = data.template_nombre ?? null;
      const email         = data.email           ?? null;
      const nota          = data.nota            ?? '';

      // cargar nota y mostrar sección
      document.getElementById('notaInput').value = nota;
      document.getElementById('notaSection').style.display = '';
      // auto-save al salir del campo
      document.getElementById('notaInput').addEventListener('blur', () => guardarNota(false));
      // auto-save con debounce mientras escribe (1.5 s de inactividad)
      document.getElementById('notaInput').addEventListener('input', () => {
        clearTimeout(notaTimer);
        notaTimer = setTimeout(() => guardarNota(true), 1500);
      });

      // mostrar email en header de pantalla
      if (email) {
        const elEmail = document.getElementById('header-email');
        elEmail.textContent = '✉ ' + email;
        elEmail.style.display = '';
      }
      // mostrar email en cabecera de impresión
      if (email) {
        document.getElementById('print-email-row').style.display = '';
        document.getElementById('print-email-val').textContent = email;
      }

      const tmplBadge = tmplNombre
        ? `<div class="tmpl-print-badge" style="margin-top:12px;font-size:.78rem;color:rgba(255,255,255,.75)">
             📋 ${tmplNombre}
           </div>`
        : '';

      document.getElementById("score-area").innerHTML = `
        <div class="score-card">
          <div class="score-num">${score}</div>
          <div class="score-den">de ${total} preguntas</div>
          <div class="nivel-pill" style="background:${nivel.bg}">${nivel.label}</div>
          <div class="nivel-desc">${nivel.desc}</div>
          <div class="exam-stats">
            <div class="stat-item">
              <div class="stat-val">⏱ ${formatTime(tiempo)}</div>
              <div class="stat-lbl">Tiempo</div>
            </div>
            <div class="stat-item stat-foco">
              <div class="stat-val">${foco ?? '—'}</div>
              <div class="stat-lbl">Cambios de pestaña</div>
            </div>
          </div>
          ${tmplBadge}
        </div>`;

      // ── category breakdown ────────────────────────────────────────
      const cats = {};
      questions.forEach((p, i) => {
        if (i === 0) return;
        const cat = p.categoria || null;
        if (!cat) return;
        if (!cats[cat]) cats[cat] = { ok: 0, total: 0, results: [] };
        cats[cat].total++;
        const ok = p.res === p.resCorrecta;
        if (ok) cats[cat].ok++;
        cats[cat].results.push(ok);
      });

      const catEl = document.getElementById('cat-breakdown');
      if (Object.keys(cats).length > 0) {
        const CAT_ORDER = ['HTML','CSS','JavaScript','PHP','MySQL','Web'];
        const sorted = [...Object.keys(cats)].sort(
          (a, b) => (CAT_ORDER.indexOf(a) + 1 || 99) - (CAT_ORDER.indexOf(b) + 1 || 99)
        );

        let catHtml = '<div class="cat-breakdown-title">Rendimiento por categoría</div><div class="cat-grid">';
        sorted.forEach(cat => {
          const { ok, total, results } = cats[cat];
          const pct = ok / total;
          const color = pct >= 0.8 ? '#43a047' : pct >= 0.5 ? '#5c6bc0' : '#ef5350';
          const dots  = results.map(r => `<span class="cat-dot ${r ? 'ok' : 'fail'}"></span>`).join('');
          catHtml += `
            <div class="cat-item">
              <span class="cat-label">${escH(cat)}</span>
              <span class="cat-dots">${dots}</span>
              <span class="cat-score" style="color:${color}">${ok}/${total}</span>
            </div>`;
        });
        catHtml += '</div>';
        catEl.innerHTML = catHtml;
        catEl.style.display = '';
      }

      // review
      let html = "";
      let num  = 0;
      questions.forEach((p, i) => {
        if (i === 0) return;
        num++;
        const ok = p.res === p.resCorrecta;
        const resTexto  = p.options
          ? escH(p.options[p.res] ?? "Sin respuesta")
          : (LETTERS[p.res] ?? "Sin respuesta");
        const corrTexto = p.options
          ? `${LETTERS[p.resCorrecta]}. ${escH(p.options[p.resCorrecta])}`
          : (LETTERS[p.resCorrecta] ?? "—");

        html += `
          <div class="review-item ${ok ? "correct" : "wrong"}">
            <div class="ri-num">${ok ? "✓" : "✗"} Pregunta ${num}</div>
            <div class="ri-q">${escH(p.q)}</div>
            <div class="ri-ans">
              <span>Tu respuesta:</span> ${resTexto}<br>
              <span>Correcta:</span> ${corrTexto}
            </div>
          </div>`;
      });

      document.getElementById("review").innerHTML = html;
    })
    .catch(() => {
      document.getElementById("status").innerHTML =
        '<span style="color:#e53935">Error al cargar el examen. Intenta de nuevo.</span>';
    });

    // ── Notas del reclutador ────────────────────────────────────────
    let notaTimer = null;

    function guardarNota(silencioso = false) {
      const nota   = document.getElementById('notaInput').value;
      const status = document.getElementById('notaStatus');
      if (!silencioso) status.textContent = 'Guardando…';

      fetch('server/guardar_nota.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id: parseInt(id), nota }),
      })
      .then(r => r.json())
      .then(data => {
        if (data.ok) {
          status.textContent = '✓ Guardado';
          setTimeout(() => { status.textContent = ''; }, 2000);
        } else {
          status.textContent = '⚠ Error';
          status.style.color = '#e53935';
        }
      })
      .catch(() => {
        status.textContent = '⚠ Sin conexión';
        status.style.color = '#e53935';
      });
    }

    // ── Eliminar ────────────────────────────────────────────────────
    function abrirDel() {
      const nombre = document.getElementById('header-nombre').textContent.trim();
      document.getElementById('delMsg').textContent =
        `Se borrará el examen de "${nombre}". Esta acción no se puede deshacer.`;
      document.getElementById('delOverlay').classList.add('active');
    }

    function cerrarDel() {
      document.getElementById('delOverlay').classList.remove('active');
    }

    function confirmarEliminar() {
      const examId = new URLSearchParams(window.location.search).get('id');
      const btn = document.getElementById('delBtn');
      btn.disabled = true;
      btn.textContent = '…';

      fetch('server/eliminar_resultado.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id: parseInt(examId) }),
      })
      .then(r => r.json())
      .then(data => {
        if (data.ok) {
          window.location.href = 'exames.php';
        } else {
          alert('Error al eliminar: ' + (data.error ?? 'intenta de nuevo'));
          btn.disabled = false;
          btn.textContent = 'Eliminar';
        }
      })
      .catch(() => {
        alert('Error de conexión. Intenta de nuevo.');
        btn.disabled = false;
        btn.textContent = 'Eliminar';
      });
    }

    document.getElementById('delOverlay').addEventListener('click', function(e) {
      if (e.target === this) cerrarDel();
    });
  </script>

</body>
</html>
