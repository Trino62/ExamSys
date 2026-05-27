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
  <title>Panel de administración</title>
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
    body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #f2f5f8; margin: 0; min-height: 100vh; }

    /* HEADER */
    .page-header { background: linear-gradient(135deg,#1d3759 0%,#2b4f80 100%); color:white; padding:20px 24px; }
    .header-inner { max-width:900px; margin:0 auto; display:flex; align-items:center; gap:12px; }
    .btn-back { display:flex; align-items:center; gap:5px; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25); color:white; text-decoration:none; padding:6px 13px; border-radius:8px; font-size:.84rem; white-space:nowrap; transition:background .15s; }
    .btn-back:hover { background:rgba(255,255,255,.25); color:white; }
    .header-title { flex:1; }
    .header-title h1 { margin:0; font-size:1.15rem; font-weight:700; }
    .btn-logout { display:flex; align-items:center; gap:5px; background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20); color:rgba(255,255,255,.85); text-decoration:none; padding:6px 12px; border-radius:8px; font-size:.82rem; white-space:nowrap; transition:background .15s; }
    .btn-logout:hover { background:rgba(255,255,255,.22); color:white; }

    /* TABS */
    .tabs-bar { background:white; border-bottom:1px solid #e8ecf2; }
    .tabs-inner { max-width:900px; margin:0 auto; display:flex; padding:0 20px; }
    .tab-btn { padding:14px 20px; font-size:.9rem; font-weight:600; color:#888; border:none; background:none; cursor:pointer; border-bottom:3px solid transparent; transition:color .15s, border-color .15s; }
    .tab-btn.active { color:#2b4f80; border-bottom-color:#2b4f80; }
    .tab-btn:hover:not(.active) { color:#555; }

    /* CONTENT */
    .content { max-width:900px; margin:24px auto 48px; padding:0 20px; }
    .tab-panel { display:none; }
    .tab-panel.active { display:block; }

    /* TOOLBAR */
    .toolbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px; }
    .toolbar-left { display:flex; gap:8px; flex-wrap:wrap; }
    .btn-add { display:flex; align-items:center; gap:6px; background:#2b4f80; color:white; border:none; padding:9px 18px; border-radius:9px; font-size:.88rem; font-weight:600; cursor:pointer; transition:background .15s; }
    .btn-add:hover { background:#1d3759; }

    /* FILTER PILLS */
    .pill { padding:5px 13px; border-radius:99px; font-size:.77rem; font-weight:600; cursor:pointer; border:1.5px solid #dde3ec; background:white; color:#555; transition:all .15s; }
    .pill:hover { border-color:#aab8cc; }
    .pill.active { color:white; border-color:transparent; background:#2b4f80; }

    /* TEMPLATE CARDS */
    .template-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
    .tmpl-card { background:white; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,.07); padding:18px 20px; border:1.5px solid transparent; transition:box-shadow .2s, border-color .2s; }
    .tmpl-card:hover { box-shadow:0 4px 18px rgba(43,79,128,.12); border-color:#d0ddf0; }
    .tmpl-name { font-size:1rem; font-weight:700; color:#1a2a3a; margin-bottom:6px; }
    .tmpl-meta { font-size:.78rem; color:#999; margin-bottom:10px; display:flex; gap:12px; flex-wrap:wrap; }
    .tmpl-cats { display:flex; gap:5px; flex-wrap:wrap; margin-bottom:12px; }
    .cat-badge { padding:3px 10px; border-radius:99px; font-size:.72rem; font-weight:700; background:#eef2f8; color:#2b4f80; }
    .tmpl-actions { display:flex; gap:8px; }
    .btn-sm { padding:6px 13px; border-radius:7px; font-size:.8rem; font-weight:600; cursor:pointer; border:none; transition:background .15s; }
    .btn-link { background:#eef2f8; color:#2b4f80; }
    .btn-link:hover { background:#dde6f5; }
    .btn-edit { background:#fff3e0; color:#e65100; }
    .btn-edit:hover { background:#ffe0b2; }
    .btn-del  { background:#fdecea; color:#c62828; }
    .btn-del:hover { background:#ffcdd2; }

    /* QUESTION LIST */
    .q-list { display:flex; flex-direction:column; gap:8px; }
    .q-item { background:white; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,.06); padding:12px 16px; display:flex; align-items:center; gap:12px; }
    .q-cat { padding:3px 10px; border-radius:99px; font-size:.7rem; font-weight:700; color:white; white-space:nowrap; flex-shrink:0; }
    .q-text { flex:1; font-size:.85rem; color:#1a2a3a; line-height:1.4; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
    .q-actions { display:flex; gap:6px; flex-shrink:0; }
    .icon-btn { width:30px; height:30px; border-radius:7px; border:none; cursor:pointer; font-size:.9rem; display:flex; align-items:center; justify-content:center; transition:background .15s; }
    .icon-btn.edit { background:#fff3e0; }
    .icon-btn.edit:hover { background:#ffe0b2; }
    .icon-btn.del  { background:#fdecea; }
    .icon-btn.del:hover { background:#ffcdd2; }

    /* EMPTY */
    .empty { background:white; border-radius:14px; padding:40px 24px; text-align:center; color:#bbb; font-size:.9rem; box-shadow:0 2px 10px rgba(0,0,0,.06); }

    /* MODAL */
    .overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; justify-content:center; align-items:center; padding:16px; }
    .overlay.active { display:flex; }
    .modal { background:white; border-radius:16px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 10px 40px rgba(0,0,0,.2); }
    .modal-header { padding:20px 24px 0; display:flex; align-items:center; justify-content:space-between; }
    .modal-header h2 { margin:0; font-size:1.05rem; color:#1a2a3a; }
    .modal-close { background:none; border:none; font-size:1.4rem; cursor:pointer; color:#aaa; line-height:1; padding:0; }
    .modal-close:hover { color:#555; }
    .modal-body { padding:16px 24px 24px; }

    /* FORM */
    .form-group { margin-bottom:14px; }
    .form-group label { display:block; font-size:.8rem; font-weight:700; color:#555; margin-bottom:5px; text-transform:uppercase; letter-spacing:.4px; }
    .form-control { width:100%; padding:9px 12px; border:1.5px solid #dde3ec; border-radius:8px; font-size:.9rem; font-family:inherit; color:#1a2a3a; outline:none; transition:border-color .15s, box-shadow .15s; }
    .form-control:focus { border-color:#2b4f80; box-shadow:0 0 0 3px rgba(43,79,128,.1); }
    textarea.form-control { resize:vertical; min-height:70px; }

    /* CATEGORY CONFIG */
    .cat-config { display:flex; flex-direction:column; gap:8px; }
    .cat-cfg-row { display:flex; align-items:center; gap:10px; padding:8px 12px; background:#f8fafc; border-radius:8px; border:1.5px solid #eef2f8; }
    .cat-cfg-row.disabled { opacity:.45; }
    .cat-toggle { width:16px; height:16px; cursor:pointer; accent-color:#2b4f80; flex-shrink:0; }
    .cat-cfg-label { flex:1; font-size:.85rem; font-weight:600; color:#1a2a3a; }
    .cat-cfg-avail { font-size:.75rem; color:#aaa; }
    .cat-cfg-n { width:56px; padding:4px 8px; border:1.5px solid #dde3ec; border-radius:6px; font-size:.88rem; text-align:center; font-family:inherit; outline:none; }
    .cat-cfg-n:focus { border-color:#2b4f80; }
    .cat-total { font-size:.82rem; color:#2b4f80; font-weight:700; margin-top:6px; text-align:right; }

    /* warnings */
    .warn-list { background:#fff8e1; border:1.5px solid #ffe082; border-radius:8px; padding:10px 14px; font-size:.82rem; color:#795548; margin-bottom:12px; display:none; }
    .warn-list.show { display:block; }

    /* MODAL ACTIONS */
    .modal-actions { display:flex; gap:10px; margin-top:16px; }
    .modal-actions button { flex:1; padding:10px; border-radius:8px; font-size:.92rem; font-weight:600; cursor:pointer; border:none; }
    .btn-cancel-m { background:#f2f5f8; color:#555; }
    .btn-cancel-m:hover { background:#e0e5eb; }
    .btn-save { background:#2b4f80; color:white; }
    .btn-save:hover { background:#1d3759; }
    .btn-save:disabled { background:#a0b5cc; cursor:not-allowed; }

    /* IMPORT CSV */
    .csv-drop { border:2px dashed #c5d2e0; border-radius:10px; padding:28px 20px; text-align:center; cursor:pointer; color:#888; font-size:.9rem; transition:border-color .15s, background .15s; }
    .csv-drop:hover { border-color:#2b4f80; background:#f0f5fb; }
    .csv-drop.has-file { border-color:#4caf50; background:#f1f8f2; color:#2e7d32; }
    .btn-template { display:inline-flex; align-items:center; gap:6px; background:#f2f5f8; color:#2b4f80; border:1.5px solid #c5d2e0; border-radius:7px; padding:7px 14px; font-size:.83rem; font-weight:600; text-decoration:none; transition:background .15s; }
    .btn-template:hover { background:#e0e8f4; }
    .import-ok   { background:#e8f5e9; border:1.5px solid #4caf50; border-radius:8px; padding:12px 16px; font-size:.88rem; color:#1b5e20; }
    .import-warn { background:#fff8e1; border:1.5px solid #ffb300; border-radius:8px; padding:12px 16px; font-size:.85rem; color:#795548; margin-top:8px; }
    .import-warn ul { margin:6px 0 0; padding-left:18px; }
    .import-warn li { margin-bottom:2px; }

    /* TIEMPO TOGGLE */
    .tiempo-wrap { display:flex; flex-direction:column; gap:10px; }
    .toggle-row  { display:flex; align-items:center; gap:8px; cursor:pointer; user-select:none; }
    .toggle-row input[type=checkbox] { width:16px; height:16px; accent-color:#2b4f80; cursor:pointer; }
    .toggle-label { font-size:.88rem; font-weight:600; color:#1a2a3a; }
    .tiempo-inputs { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .tiempo-field  { display:flex; align-items:center; gap:5px; }
    .tiempo-num    { width:70px; text-align:center; font-size:1rem; font-weight:700; padding:7px 8px; }
    .tiempo-unit   { font-size:.8rem; color:#999; font-weight:600; }
    .tiempo-sep    { font-size:1.2rem; font-weight:700; color:#aaa; margin:0 2px; }
    .tiempo-preview { font-size:.8rem; color:#2b4f80; font-weight:700;
                      background:#eef2f8; padding:3px 10px; border-radius:99px; }

    /* CORRECT RADIO */
    .correct-row { display:flex; gap:8px; flex-wrap:wrap; }
    .correct-opt { display:flex; align-items:center; gap:5px; padding:6px 12px; border:1.5px solid #dde3ec; border-radius:7px; cursor:pointer; font-size:.85rem; font-weight:600; color:#555; transition:all .15s; }
    .correct-opt input { display:none; }
    .correct-opt.sel { background:#2b4f80; color:white; border-color:#2b4f80; }

    /* TOAST */
    .toast { position:fixed; bottom:24px; right:24px; background:#1a2a3a; color:white; padding:12px 20px; border-radius:10px; font-size:.88rem; font-weight:600; box-shadow:0 4px 16px rgba(0,0,0,.2); z-index:9999; opacity:0; transform:translateY(8px); transition:opacity .25s, transform .25s; pointer-events:none; }
    .toast.show { opacity:1; transform:translateY(0); }

    /* CONFIRM DELETE OVERLAY */
    .del-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1100; justify-content:center; align-items:center; }
    .del-overlay.active { display:flex; }
    .del-box { background:white; border-radius:14px; padding:28px 24px 20px; width:90%; max-width:320px; text-align:center; box-shadow:0 8px 32px rgba(0,0,0,.18); }
    .del-box .di { font-size:2rem; margin-bottom:8px; }
    .del-box h3 { margin:0 0 6px; font-size:1rem; color:#1a2a3a; }
    .del-box p  { margin:0 0 18px; font-size:.83rem; color:#777; }
    .del-acts { display:flex; gap:8px; }
    .del-acts button { flex:1; padding:9px; border-radius:8px; font-size:.9rem; font-weight:600; cursor:pointer; border:none; }
    .d-cancel { background:#f2f5f8; color:#555; }
    .d-cancel:hover { background:#e0e5eb; }
    .d-confirm { background:#e53935; color:white; }
    .d-confirm:hover { background:#c62828; }

    @view-transition { navigation: auto; }
    @keyframes vt-out { to   { opacity:0; translate:0 -6px; } }
    @keyframes vt-in  { from { opacity:0; translate:0  10px; } }
    ::view-transition-old(root) { animation:180ms ease both vt-out; }
    ::view-transition-new(root) { animation:280ms ease both vt-in; }

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
    [data-theme="dark"] .tabs-bar { background: var(--bg-card); border-bottom-color: var(--border); }
    [data-theme="dark"] .tab-btn { color: var(--text-3); }
    [data-theme="dark"] .tab-btn.active { color: #7aacde; border-bottom-color: #4a7bc4; }
    [data-theme="dark"] .tab-btn:hover:not(.active) { color: var(--text-2); }
    [data-theme="dark"] .tmpl-card { background: var(--bg-card); }
    [data-theme="dark"] .tmpl-card:hover { border-color: #253550; }
    [data-theme="dark"] .tmpl-name { color: var(--text); }
    [data-theme="dark"] .tmpl-meta { color: var(--text-3); }
    [data-theme="dark"] .cat-badge { background: #1b2e48; color: #7aacde; }
    [data-theme="dark"] .btn-link  { background: #1b2e48; color: #7aacde; }
    [data-theme="dark"] .btn-link:hover { background: #243650; }
    [data-theme="dark"] .btn-edit  { background: #2a1e0e; color: #ffb74d; }
    [data-theme="dark"] .btn-edit:hover { background: #3a2810; }
    [data-theme="dark"] .btn-del   { background: #2a0e0e; color: #ef9a9a; }
    [data-theme="dark"] .btn-del:hover  { background: #380e0e; }
    [data-theme="dark"] .q-item { background: var(--bg-card); }
    [data-theme="dark"] .q-text { color: var(--text); }
    [data-theme="dark"] .icon-btn.edit { background: #2a1e0e; }
    [data-theme="dark"] .icon-btn.edit:hover { background: #3a2810; }
    [data-theme="dark"] .icon-btn.del  { background: #2a0e0e; }
    [data-theme="dark"] .icon-btn.del:hover  { background: #380e0e; }
    [data-theme="dark"] .empty { background: var(--bg-card); color: var(--text-3); }
    [data-theme="dark"] .pill { background: var(--bg-card); color: var(--text-2); border-color: var(--border); }
    [data-theme="dark"] .pill:hover { border-color: #4a7bc4; }
    [data-theme="dark"] .pill.active { background: #4a7bc4; border-color: #4a7bc4; color: white; }
    [data-theme="dark"] .modal { background: var(--bg-card); }
    [data-theme="dark"] .modal-header h2 { color: var(--text); }
    [data-theme="dark"] .modal-close { color: var(--text-3); }
    [data-theme="dark"] .modal-close:hover { color: var(--text-2); }
    [data-theme="dark"] .form-group label { color: var(--text-2); }
    [data-theme="dark"] .form-control { background: var(--bg-sub); border-color: var(--border); color: var(--text); }
    [data-theme="dark"] .form-control:focus { border-color: #4a7bc4; box-shadow: 0 0 0 3px rgba(74,123,196,.12); }
    [data-theme="dark"] .cat-cfg-row { background: var(--bg-sub); border-color: var(--border); }
    [data-theme="dark"] .cat-cfg-label { color: var(--text); }
    [data-theme="dark"] .cat-cfg-avail { color: var(--text-3); }
    [data-theme="dark"] .cat-cfg-n { border-color: var(--border); background: var(--bg-card); color: var(--text); }
    [data-theme="dark"] .cat-cfg-n:focus { border-color: #4a7bc4; }
    [data-theme="dark"] .cat-total { color: #7aacde; }
    [data-theme="dark"] .warn-list { background: #1f1800; border-color: #5c4200; color: #ffcc80; }
    [data-theme="dark"] .btn-cancel-m { background: var(--bg-sub); color: var(--text-2); }
    [data-theme="dark"] .btn-cancel-m:hover { background: var(--border); }
    [data-theme="dark"] .csv-drop { border-color: var(--border); color: var(--text-3); }
    [data-theme="dark"] .csv-drop:hover { border-color: #4a7bc4; background: #1a2840; }
    [data-theme="dark"] .csv-drop.has-file { border-color: #2e7d32; background: #0d2218; color: #81c784; }
    [data-theme="dark"] .btn-template { background: var(--bg-sub); color: #7aacde; border-color: var(--border); }
    [data-theme="dark"] .btn-template:hover { background: var(--border); }
    [data-theme="dark"] .import-ok   { background: #0d2218; border-color: #2e7d32; color: #81c784; }
    [data-theme="dark"] .import-warn { background: #1f1800; border-color: #5c4200; color: #ffcc80; }
    [data-theme="dark"] .toggle-label { color: var(--text); }
    [data-theme="dark"] .tiempo-unit  { color: var(--text-3); }
    [data-theme="dark"] .tiempo-sep   { color: var(--text-3); }
    [data-theme="dark"] .tiempo-num   { background: var(--bg-sub); border-color: var(--border); color: var(--text); }
    [data-theme="dark"] .tiempo-preview { background: #1b2e48; color: #7aacde; }
    [data-theme="dark"] .correct-opt { border-color: var(--border); color: var(--text-2); }
    [data-theme="dark"] .correct-opt.sel { background: #4a7bc4; border-color: #4a7bc4; color: white; }
    [data-theme="dark"] .toast { background: var(--bg-card); color: var(--text); border: 1px solid var(--border); }
    [data-theme="dark"] .del-overlay { background: rgba(0,0,0,.65); }
    [data-theme="dark"] .del-box  { background: var(--bg-card); }
    [data-theme="dark"] .del-box h3 { color: var(--text); }
    [data-theme="dark"] .del-box p  { color: var(--text-2); }
    [data-theme="dark"] .d-cancel { background: var(--bg-sub); color: var(--text-2); }
    [data-theme="dark"] .d-cancel:hover { background: var(--border); }

    /* ── Panel (Sitio tab) ──────────────── */
    .panel { background: var(--bg-card); border-radius: 14px; box-shadow: 0 2px 10px var(--sh); }

    /* ── Responsive ─────────────────────── */
    @media (max-width: 640px) {
      .page-header { padding: 14px 16px; }
      .header-inner { gap: 8px; }
      .header-title h1 { font-size: 1rem; }
      .btn-back, .btn-logout { padding: 5px 10px; font-size: .78rem; }
      .tabs-inner {
        overflow-x: auto;
        padding: 0 4px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
      }
      .tabs-inner::-webkit-scrollbar { display: none; }
      .tab-btn { padding: 12px 14px; font-size: .82rem; white-space: nowrap; flex-shrink: 0; }
      .content { padding: 0 12px; margin: 16px auto 32px; }
    }

    @media (max-width: 480px) {
      .template-grid { grid-template-columns: 1fr; }
      .tmpl-actions { flex-wrap: wrap; }
      .q-item { flex-wrap: wrap; }
      .q-cat { order: 1; }
      .q-actions { order: 2; margin-left: auto; }
      .q-text { order: 3; flex-basis: 100%; margin-top: 4px; -webkit-line-clamp: 3; }
      .modal-header { padding: 16px 16px 0; }
      .modal-body { padding: 12px 16px 20px; }
      .modal-actions { flex-direction: column; }
      .modal-actions button { flex: none; }
    }

    @media (max-width: 360px) {
      .header-title h1 { font-size: .88rem; }
      .btn-back, .btn-logout { font-size: .74rem; padding: 5px 8px; }
      .tab-btn { font-size: .78rem; padding: 12px 10px; }
      .content { padding: 0 10px; }
      .cat-cfg-avail { display: none; }
    }
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
    <div class="header-title"><h1>Panel de administración</h1></div>
    <a href="server/logout.php" class="btn-logout">⏻ Salir</a>
  </div>
</div>

<div class="tabs-bar">
  <div class="tabs-inner">
    <button class="tab-btn active" onclick="setTab('plantillas',this)">📋 Plantillas de examen</button>
    <button class="tab-btn"        onclick="setTab('preguntas',this)">❓ Banco de preguntas</button>
    <button class="tab-btn"        onclick="setTab('sitio',this)">⚙️ Configuración del sitio</button>
  </div>
</div>

<div class="content">

  <!-- ══ TAB: PLANTILLAS ══════════════════════════════════════════ -->
  <div class="tab-panel active" id="tab-plantillas">
    <div class="toolbar">
      <div class="toolbar-left"></div>
      <button class="btn-add" onclick="abrirTmpl(null)">+ Nueva plantilla</button>
    </div>
    <div class="template-grid" id="tmplGrid">
      <div class="empty"><p style="font-size:1.8rem;margin:0 0 8px">📋</p><p>Cargando plantillas…</p></div>
    </div>
  </div>

  <!-- ══ TAB: PREGUNTAS ═══════════════════════════════════════════ -->
  <div class="tab-panel" id="tab-preguntas">
    <div class="toolbar">
      <div class="toolbar-left" id="catPills">
        <button class="pill active" data-cat="" onclick="setPillQ(this)">Todas</button>
      </div>
      <div style="display:flex;gap:8px">
        <button class="btn-add" style="background:#f2f5f8;color:#2b4f80;border:1.5px solid #c5d2e0" onclick="abrirImport()">⬆ Importar CSV</button>
        <button class="btn-add" onclick="abrirPregunta(null)">+ Nueva pregunta</button>
      </div>
    </div>
    <div class="q-list" id="qList">
      <div class="empty"><p style="font-size:1.8rem;margin:0 0 8px">❓</p><p>Cargando preguntas…</p></div>
    </div>
  </div>

  <!-- ══ TAB: SITIO ═══════════════════════════════════════════════ -->
  <div class="tab-panel" id="tab-sitio">

    <div class="panel" style="padding:24px;margin-bottom:16px">
      <div class="panel-section-title" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-3);margin-bottom:14px">Hero principal</div>

      <div class="form-group">
        <label>Título principal</label>
        <input type="text" class="form-control" id="cfg-titulo" placeholder="Evaluación de Conocimientos en…">
      </div>
      <div class="form-group">
        <label>Descripción</label>
        <textarea class="form-control" id="cfg-descripcion" rows="3" placeholder="Describe el propósito del examen…"></textarea>
      </div>
      <div class="form-group">
        <label>Temas / Badges <span style="font-weight:400;color:var(--text-4)">(separados por coma)</span></label>
        <input type="text" class="form-control" id="cfg-badges" placeholder="HTML,CSS,JavaScript,PHP,MySQL">
        <div id="badgePreview" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px"></div>
      </div>
      <div class="form-group">
        <label>Texto sobre el botón "Hacer examen"</label>
        <input type="text" class="form-control" id="cfg-texto_cta" placeholder="El examen toma menos de 10 minutos…">
      </div>
    </div>

    <div class="panel" style="padding:24px;margin-bottom:16px">
      <div class="panel-section-title" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-3);margin-bottom:14px">Tarjetas informativas</div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px">
        <?php foreach ([1,2,3] as $n): ?>
        <div class="cfg-card-box" style="border:1.5px solid var(--border-s);border-radius:10px;padding:14px">
          <div style="display:flex;gap:8px;margin-bottom:10px">
            <div>
              <label style="font-size:.75rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px">Ícono</label>
              <input type="text" class="form-control" id="cfg-card<?= $n ?>_icono"
                     style="width:60px;text-align:center;font-size:1.3rem;padding:5px" maxlength="4">
            </div>
            <div style="flex:1">
              <label style="font-size:.75rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px">Título</label>
              <input type="text" class="form-control" id="cfg-card<?= $n ?>_titulo" placeholder="Título de la tarjeta">
            </div>
          </div>
          <label style="font-size:.75rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px">Descripción</label>
          <textarea class="form-control" id="cfg-card<?= $n ?>_desc" rows="3" placeholder="Descripción…"></textarea>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button class="btn-add" id="cfgSaveBtn" onclick="guardarConfig()" style="width:100%;justify-content:center;padding:12px">
      💾 Guardar configuración
    </button>
    <p style="text-align:center;font-size:.78rem;color:var(--text-4);margin-top:8px">
      Los cambios se reflejan de inmediato en <a href="index.php" target="_blank" style="color:#4a7bc4">index.php</a>
    </p>

  </div>

</div><!-- /content -->

<!-- ══ MODAL PLANTILLA ══════════════════════════════════════════ -->
<div class="overlay" id="overlayTmpl">
  <div class="modal" id="modalTmpl">
    <div class="modal-header">
      <h2 id="tmplModalTitle">Nueva plantilla</h2>
      <button class="modal-close" onclick="cerrarTmpl()">×</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="tmplId" value="">

      <div class="form-group">
        <label>Nombre del examen</label>
        <input type="text" class="form-control" id="tmplNombre" placeholder="ej. Frontend Jr">
      </div>

      <div class="form-group">
        <label>Límite de tiempo</label>
        <div class="tiempo-wrap">
          <label class="toggle-row">
            <input type="checkbox" id="tmplTiempoOn" onchange="onTiempoToggle()">
            <span class="toggle-label">Activar límite</span>
          </label>
          <div class="tiempo-inputs" id="tiempoInputs" style="display:none">
            <div class="tiempo-field">
              <input type="number" class="form-control tiempo-num" id="tmplMin"
                     min="0" max="999" value="10" oninput="renderTiempoPreview()">
              <span class="tiempo-unit">min</span>
            </div>
            <span class="tiempo-sep">:</span>
            <div class="tiempo-field">
              <input type="number" class="form-control tiempo-num" id="tmplSeg"
                     min="0" max="59" value="0" oninput="renderTiempoPreview()">
              <span class="tiempo-unit">seg</span>
            </div>
            <span class="tiempo-preview" id="tiempoPreview"></span>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>Categorías y preguntas</label>
        <div class="cat-config" id="catConfig"><!-- rendered by JS --></div>
        <div class="cat-total" id="catTotal">Total: 0 preguntas</div>
      </div>

      <div class="warn-list" id="tmplWarns"></div>

      <div class="modal-actions">
        <button class="btn-cancel-m" onclick="cerrarTmpl()">Cancelar</button>
        <button class="btn-save" id="tmplSaveBtn" onclick="guardarTmpl()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- ══ MODAL PREGUNTA ════════════════════════════════════════════ -->
<!-- ══ OVERLAY: IMPORTAR CSV ════════════════════════════════════ -->
<div class="overlay" id="overlayImport">
  <div class="modal" id="modalImport">
    <div class="modal-header">
      <h2>⬆ Importar preguntas CSV</h2>
      <button class="modal-close" onclick="cerrarImport()">×</button>
    </div>
    <div class="modal-body">

      <div style="background:#f2f5f8;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:.83rem;color:#555;line-height:1.7">
        <strong>Formato esperado</strong> — 7 columnas en este orden:<br>
        <code style="font-size:.8rem">pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta, categoria</code><br>
        <span style="color:#888">• <strong>correcta:</strong> 0 = A &nbsp;·&nbsp; 1 = B &nbsp;·&nbsp; 2 = C &nbsp;·&nbsp; 3 = D</span><br>
        <span style="color:#888">• La primera fila de encabezado se omite automáticamente</span>
      </div>

      <a href="server/importar_preguntas.php?template=1" class="btn-template" download>
        ⬇ Descargar plantilla CSV
      </a>

      <div class="form-group" style="margin-top:16px">
        <label>Archivo CSV</label>
        <div class="csv-drop" id="csvDrop" onclick="document.getElementById('csvFile').click()">
          <div id="csvDropLabel">📂 Haz clic para seleccionar un archivo CSV</div>
        </div>
        <input type="file" id="csvFile" accept=".csv,text/csv" style="display:none" onchange="onCsvSelected(this)">
      </div>

      <div id="importResult"></div>

      <div class="modal-actions">
        <button class="btn-cancel-m" onclick="cerrarImport()">Cancelar</button>
        <button class="btn-save" id="importBtn" onclick="subirCSV()" disabled>Importar</button>
      </div>
    </div>
  </div>
</div>

<div class="overlay" id="overlayPregunta">
  <div class="modal" id="modalPregunta">
    <div class="modal-header">
      <h2 id="pregModalTitle">Nueva pregunta</h2>
      <button class="modal-close" onclick="cerrarPregunta()">×</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="pregId" value="">

      <div class="form-group">
        <label>Categoría</label>
        <select class="form-control" id="pregCat" onchange="onCatChange()">
          <!-- opciones cargadas dinámicamente -->
        </select>
        <input type="text" class="form-control" id="pregCatNueva"
               placeholder="Nombre de la nueva categoría…"
               style="margin-top:8px;display:none">
      </div>

      <div class="form-group">
        <label>Pregunta</label>
        <textarea class="form-control" id="pregTexto" rows="3" placeholder="¿Cuál es…?"></textarea>
      </div>

      <div class="form-group">
        <label>Opción A</label>
        <input type="text" class="form-control" id="pregA" placeholder="Opción A">
      </div>
      <div class="form-group">
        <label>Opción B</label>
        <input type="text" class="form-control" id="pregB" placeholder="Opción B">
      </div>
      <div class="form-group">
        <label>Opción C</label>
        <input type="text" class="form-control" id="pregC" placeholder="Opción C">
      </div>
      <div class="form-group">
        <label>Opción D</label>
        <input type="text" class="form-control" id="pregD" placeholder="Opción D">
      </div>

      <div class="form-group">
        <label>Respuesta correcta</label>
        <div class="correct-row" id="correctRow">
          <label class="correct-opt" id="co0"><input type="radio" name="correcta" value="0">A</label>
          <label class="correct-opt" id="co1"><input type="radio" name="correcta" value="1">B</label>
          <label class="correct-opt" id="co2"><input type="radio" name="correcta" value="2">C</label>
          <label class="correct-opt" id="co3"><input type="radio" name="correcta" value="3">D</label>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn-cancel-m" onclick="cerrarPregunta()">Cancelar</button>
        <button class="btn-save" id="pregSaveBtn" onclick="guardarPregunta()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- CONFIRM DELETE -->
<div class="del-overlay" id="delOverlay">
  <div class="del-box">
    <div class="di">🗑️</div>
    <h3 id="delTitle">¿Eliminar?</h3>
    <p id="delMsg">Esta acción no se puede deshacer.</p>
    <div class="del-acts">
      <button class="d-cancel" onclick="cerrarDel()">Cancelar</button>
      <button class="d-confirm" id="delConfirmBtn" onclick="ejecutarDel()">Eliminar</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script>
    function toggleTheme() {
      var t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', t);
      localStorage.setItem('examsys_theme', t);
      document.getElementById('themeBtn').textContent = t === 'dark' ? '☀️' : '🌙';
    }

const BASE = window.location.origin + window.location.pathname.replace('admin.php','');
let CATS = ['HTML','CSS','JavaScript','PHP','MySQL','Web'];  // se actualiza desde el servidor
const CAT_COLORS = { HTML:'#e44d26',CSS:'#264de4',JavaScript:'#f0db4f',PHP:'#8892be',MySQL:'#00758f',Web:'#2b4f80' };
const CAT_TEXT   = { JavaScript:'#1a2a3a' };

let disponibles  = {};   // {HTML:12, CSS:10, ...}
let templates    = [];
let preguntas    = [];
let activeCatQ   = '';
let delCallback  = null;

// ── TABS ─────────────────────────────────────────────────────────
function setTab(name, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  btn.classList.add('active');
  if (name === 'preguntas' && preguntas.length === 0) cargarPreguntas();
}

// ── TOAST ─────────────────────────────────────────────────────────
function toast(msg, error=false) {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.style.background = error ? '#c62828' : '#1a2a3a';
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 2800);
}

// ── CONFIRM DELETE ────────────────────────────────────────────────
function pedirDel(title, msg, cb) {
  document.getElementById('delTitle').textContent = title;
  document.getElementById('delMsg').textContent   = msg;
  delCallback = cb;
  document.getElementById('delOverlay').classList.add('active');
}
function cerrarDel()   { document.getElementById('delOverlay').classList.remove('active'); delCallback = null; }
function ejecutarDel() { cerrarDel(); delCallback?.(); }
document.getElementById('delOverlay').addEventListener('click', e => { if(e.target===e.currentTarget) cerrarDel(); });

// ══════════════════════════════════════════════════════════════════
// PLANTILLAS
// ══════════════════════════════════════════════════════════════════
async function cargarTemplates() {
  const res  = await fetch('server/templates.php');
  const data = await res.json();
  templates   = data.templates  ?? [];
  disponibles = data.disponibles ?? {};
  renderTemplates();
}

function renderTemplates() {
  const grid = document.getElementById('tmplGrid');
  if (!templates.length) {
    grid.innerHTML = '<div class="empty"><p style="font-size:1.8rem;margin:0 0 8px">📋</p><p>No hay plantillas. Crea una nueva.</p></div>';
    return;
  }
  grid.innerHTML = templates.map(t => {
    const cfg    = JSON.parse(t.config);
    const total  = Object.values(cfg).reduce((a,b)=>a+b,0);
    const tiempo = t.tiempo_segundos ? fmtMin(t.tiempo_segundos) : 'Sin límite';
    const cats   = Object.entries(cfg).map(([c,n]) =>
      `<span class="cat-badge">${c} ×${n}</span>`
    ).join('');
    return `
      <div class="tmpl-card" id="tmpl-${t.id}">
        <div class="tmpl-name">${escH(t.nombre)}</div>
        <div class="tmpl-meta">
          <span>⏱ ${tiempo}</span>
          <span>📝 ${total} preguntas</span>
        </div>
        <div class="tmpl-cats">${cats}</div>
        <div class="tmpl-actions">
          <button class="btn-sm btn-link" onclick="copiarLink('${t.slug}')">🔗 Enlace</button>
          <button class="btn-sm btn-edit" onclick="abrirTmpl(${t.id})">✏️ Editar</button>
          <button class="btn-sm btn-del"  onclick="eliminarTmpl(${t.id},'${escH(t.nombre)}')">🗑</button>
        </div>
      </div>`;
  }).join('');
}

function copiarLink(slug) {
  const url = `${BASE}examen.html?t=${slug}`;
  if (navigator.clipboard?.writeText) {
    navigator.clipboard.writeText(url).then(() => toast('¡Enlace copiado!'));
  } else {
    // fallback para HTTP / navegadores sin Clipboard API
    const ta = document.createElement('textarea');
    ta.value = url;
    ta.style.cssText = 'position:fixed;opacity:0;pointer-events:none';
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    ta.remove();
    toast('¡Enlace copiado!');
  }
}

function fmtMin(s) {
  const m = Math.floor(s/60);
  const sg = s % 60;
  return sg > 0 ? `${m} min ${sg} seg` : `${m} min`;
}

function onTiempoToggle() {
  const on = document.getElementById('tmplTiempoOn').checked;
  document.getElementById('tiempoInputs').style.display = on ? 'flex' : 'none';
  if (on) renderTiempoPreview();
}

function renderTiempoPreview() {
  const m   = parseInt(document.getElementById('tmplMin').value) || 0;
  const s   = Math.min(parseInt(document.getElementById('tmplSeg').value) || 0, 59);
  const tot = m * 60 + s;
  document.getElementById('tiempoPreview').textContent =
    tot > 0 ? `${m}:${String(s).padStart(2,'0')} (${tot}s)` : '';
}

function getTiempoSegundos() {
  if (!document.getElementById('tmplTiempoOn').checked) return null;
  const m = parseInt(document.getElementById('tmplMin').value) || 0;
  const s = Math.min(parseInt(document.getElementById('tmplSeg').value) || 0, 59);
  const tot = m * 60 + s;
  return tot > 0 ? tot : null;
}

function abrirTmpl(id) {
  document.getElementById('tmplWarns').classList.remove('show');
  document.getElementById('tmplId').value      = id ?? '';
  document.getElementById('tmplModalTitle').textContent = id ? 'Editar plantilla' : 'Nueva plantilla';

  let cfg = {};
  let nombre = '', tiempo = '';

  if (id) {
    const t = templates.find(x => x.id == id);
    nombre  = t?.nombre ?? '';
    tiempo  = t?.tiempo_segundos ?? '';
    cfg     = JSON.parse(t?.config ?? '{}');
  }

  document.getElementById('tmplNombre').value = nombre;

  const tiempoOn = tiempo > 0;
  document.getElementById('tmplTiempoOn').checked = tiempoOn;
  document.getElementById('tiempoInputs').style.display = tiempoOn ? 'flex' : 'none';
  if (tiempoOn) {
    document.getElementById('tmplMin').value = Math.floor(tiempo / 60);
    document.getElementById('tmplSeg').value = tiempo % 60;
    renderTiempoPreview();
  } else {
    document.getElementById('tmplMin').value = 10;
    document.getElementById('tmplSeg').value = 0;
  }

  renderCatConfig(cfg);
  document.getElementById('overlayTmpl').classList.add('active');
  setTimeout(() => document.getElementById('tmplNombre').focus(), 80);
}

function renderCatConfig(cfg) {
  const container = document.getElementById('catConfig');
  container.innerHTML = CATS.map(cat => {
    const n     = cfg[cat] ?? 0;
    const avail = disponibles[cat] ?? 0;
    const on    = n > 0;
    return `
      <div class="cat-cfg-row ${on?'':'disabled'}" id="ccrow-${cat}">
        <input type="checkbox" class="cat-toggle" id="cctog-${cat}"
               ${on?'checked':''} onchange="toggleCat('${cat}')">
        <label for="cctog-${cat}" class="cat-cfg-label">${cat}</label>
        <span class="cat-cfg-avail">${avail} disp.</span>
        <input type="number" class="cat-cfg-n" id="ccn-${cat}"
               value="${on?n:0}" min="0" max="${avail}"
               ${on?'':'disabled'}
               oninput="actualizarTotal()">
      </div>`;
  }).join('');
  actualizarTotal();
}

function toggleCat(cat) {
  const on  = document.getElementById(`cctog-${cat}`).checked;
  const row = document.getElementById(`ccrow-${cat}`);
  const inp = document.getElementById(`ccn-${cat}`);
  row.classList.toggle('disabled', !on);
  inp.disabled = !on;
  if (on && parseInt(inp.value) === 0) inp.value = 1;
  if (!on) inp.value = 0;
  actualizarTotal();
}

function actualizarTotal() {
  let total = 0;
  CATS.forEach(cat => {
    const inp = document.getElementById(`ccn-${cat}`);
    if (inp && !inp.disabled) total += parseInt(inp.value)||0;
  });
  document.getElementById('catTotal').textContent = `Total: ${total} pregunta${total!==1?'s':''}`;
}

function cerrarTmpl() {
  document.getElementById('overlayTmpl').classList.remove('active');
}

async function guardarTmpl() {
  const btn    = document.getElementById('tmplSaveBtn');
  const id     = document.getElementById('tmplId').value;
  const nombre = document.getElementById('tmplNombre').value.trim();
  const tiempo = getTiempoSegundos();

  const config = {};
  CATS.forEach(cat => {
    const inp = document.getElementById(`ccn-${cat}`);
    if (inp && !inp.disabled) {
      const v = parseInt(inp.value)||0;
      if (v > 0) config[cat] = v;
    }
  });

  if (!nombre) { toast('Escribe un nombre para la plantilla', true); return; }
  if (Object.keys(config).length === 0) { toast('Selecciona al menos una categoría', true); return; }

  btn.disabled = true; btn.textContent = 'Guardando…';

  const body = { nombre, config };
  if (id) body.id = parseInt(id);
  if (tiempo) body.tiempo_segundos = tiempo;   // ya es número entero desde getTiempoSegundos()

  const res  = await fetch('server/templates.php', {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)
  });
  const data = await res.json();

  btn.disabled = false; btn.textContent = 'Guardar';

  if (!data.ok) { toast(data.error ?? 'Error al guardar', true); return; }

  if (data.warnings?.length) {
    const w = document.getElementById('tmplWarns');
    w.innerHTML = '⚠️ Algunas categorías tenían más preguntas de las disponibles y se ajustaron:<br>' +
                  data.warnings.map(x=>`• ${x}`).join('<br>');
    w.classList.add('show');
  }

  cerrarTmpl();
  toast(id ? 'Plantilla actualizada ✓' : 'Plantilla creada ✓');
  await cargarTemplates();
}

function eliminarTmpl(id, nombre) {
  pedirDel('¿Eliminar plantilla?', `Se eliminará "${nombre}". Los exámenes ya realizados no se ven afectados.`, async () => {
    await fetch('server/templates.php', {
      method:'DELETE', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id})
    });
    toast('Plantilla eliminada');
    await cargarTemplates();
  });
}

document.getElementById('overlayTmpl').addEventListener('click', e => {
  if (e.target === e.currentTarget) cerrarTmpl();
});

// ══════════════════════════════════════════════════════════════════
// PREGUNTAS
// ══════════════════════════════════════════════════════════════════
async function cargarPreguntas(cat='') {
  activeCatQ = cat;
  const url  = 'server/preguntas_crud.php' + (cat ? `?categoria=${encodeURIComponent(cat)}` : '');
  const res  = await fetch(url);
  preguntas  = await res.json();
  renderPreguntas();
}

function renderPreguntas() {
  const list = document.getElementById('qList');
  if (!preguntas.length) {
    list.innerHTML = '<div class="empty"><p>No hay preguntas en esta categoría.</p></div>';
    return;
  }
  list.innerHTML = preguntas.map(p => {
    const color = CAT_COLORS[p.categoria] ?? '#888';
    const txt   = CAT_TEXT[p.categoria] ? CAT_TEXT[p.categoria] : 'white';
    return `
      <div class="q-item" id="qi-${p.id}">
        <span class="q-cat" style="background:${color};color:${txt}">${escH(p.categoria)}</span>
        <div class="q-text">${escH(p.pregunta)}</div>
        <div class="q-actions">
          <button class="icon-btn edit" title="Editar" onclick="abrirPregunta(${p.id})">✏️</button>
          <button class="icon-btn del"  title="Eliminar" onclick="eliminarPregunta(${p.id},'${escH(p.pregunta.substring(0,40))}…')">🗑</button>
        </div>
      </div>`;
  }).join('');
}

async function cargarCatPills() {
  const res  = await fetch('server/preguntas_crud.php?counts=1');
  const data = await res.json();

  // actualizar array global de categorías
  const dbCats = data.map(r => r.categoria);
  // fusionar con defaults para que aparezcan aunque estén vacías
  CATS = [...new Set([...CATS, ...dbCats])];

  // pills de filtro
  const bar = document.getElementById('catPills');
  const extras = data.map(r =>
    `<button class="pill" data-cat="${escH(r.categoria)}" onclick="setPillQ(this)">${escH(r.categoria)} <span style="opacity:.6">(${r.n})</span></button>`
  ).join('');
  bar.innerHTML = `<button class="pill active" data-cat="" onclick="setPillQ(this)">Todas</button>` + extras;

  // select del modal de pregunta
  const sel = document.getElementById('pregCat');
  const current = sel.value;
  sel.innerHTML = CATS.map(c => `<option value="${escH(c)}">${escH(c)}</option>`).join('') +
                  `<option value="__nueva__">➕ Nueva categoría…</option>`;
  if (current && [...sel.options].some(o => o.value === current)) sel.value = current;
}

function setPillQ(btn) {
  document.querySelectorAll('#catPills .pill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  cargarPreguntas(btn.dataset.cat);
}

function abrirPregunta(id) {
  document.getElementById('pregId').value = id ?? '';
  document.getElementById('pregModalTitle').textContent = id ? 'Editar pregunta' : 'Nueva pregunta';

  // reset
  ['pregTexto','pregA','pregB','pregC','pregD','pregCatNueva'].forEach(f => document.getElementById(f).value='');
  document.getElementById('pregCatNueva').style.display = 'none';
  document.querySelectorAll('.correct-opt').forEach(o => o.classList.remove('sel'));
  document.querySelectorAll('input[name="correcta"]').forEach(r => r.checked=false);

  if (id) {
    const p = preguntas.find(x => x.id == id);
    if (p) {
      document.getElementById('pregCat').value   = p.categoria;
      document.getElementById('pregTexto').value = p.pregunta;
      document.getElementById('pregA').value     = p.opcion_a;
      document.getElementById('pregB').value     = p.opcion_b;
      document.getElementById('pregC').value     = p.opcion_c;
      document.getElementById('pregD').value     = p.opcion_d;
      const radio = document.querySelector(`input[name="correcta"][value="${p.correcta}"]`);
      if (radio) { radio.checked=true; document.getElementById(`co${p.correcta}`).classList.add('sel'); }
    }
  }

  document.getElementById('overlayPregunta').classList.add('active');
  setTimeout(() => document.getElementById('pregTexto').focus(), 80);
}

// highlight selected correct option
document.getElementById('correctRow').addEventListener('change', e => {
  document.querySelectorAll('.correct-opt').forEach(o => o.classList.remove('sel'));
  if (e.target.name === 'correcta') {
    e.target.closest('.correct-opt').classList.add('sel');
  }
});

function onCatChange() {
  const isNueva = document.getElementById('pregCat').value === '__nueva__';
  const inp = document.getElementById('pregCatNueva');
  inp.style.display = isNueva ? '' : 'none';
  if (isNueva) setTimeout(() => inp.focus(), 60);
}

function getCatValue() {
  const sel = document.getElementById('pregCat').value;
  if (sel === '__nueva__') {
    return document.getElementById('pregCatNueva').value.trim();
  }
  return sel;
}

function cerrarPregunta() {
  document.getElementById('overlayPregunta').classList.remove('active');
}

async function guardarPregunta() {
  const btn      = document.getElementById('pregSaveBtn');
  const id       = document.getElementById('pregId').value;
  const pregunta = document.getElementById('pregTexto').value.trim();
  const opcion_a = document.getElementById('pregA').value.trim();
  const opcion_b = document.getElementById('pregB').value.trim();
  const opcion_c = document.getElementById('pregC').value.trim();
  const opcion_d = document.getElementById('pregD').value.trim();
  const categoria = getCatValue();
  const radio     = document.querySelector('input[name="correcta"]:checked');

  if (!pregunta||!opcion_a||!opcion_b||!opcion_c||!opcion_d) { toast('Completa todos los campos',true); return; }
  if (!categoria) { toast('Escribe el nombre de la nueva categoría',true); return; }
  if (!radio) { toast('Selecciona la respuesta correcta',true); return; }

  btn.disabled=true; btn.textContent='Guardando…';

  const body = { pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta:parseInt(radio.value), categoria };
  if (id) body.id = parseInt(id);

  const res  = await fetch('server/preguntas_crud.php', {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)
  });
  const data = await res.json();

  btn.disabled=false; btn.textContent='Guardar';

  if (!data.ok) { toast(data.error??'Error al guardar',true); return; }

  cerrarPregunta();
  toast(id ? 'Pregunta actualizada ✓' : 'Pregunta creada ✓');
  await Promise.all([cargarPreguntas(activeCatQ), cargarCatPills(), cargarTemplates()]);
}

function eliminarPregunta(id, texto) {
  pedirDel('¿Eliminar pregunta?', `"${texto}"`, async () => {
    await fetch('server/preguntas_crud.php', {
      method:'DELETE', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id})
    });
    toast('Pregunta eliminada');
    const el = document.getElementById(`qi-${id}`);
    if (el) { el.style.transition='opacity .2s'; el.style.opacity='0'; setTimeout(()=>el.remove(),220); }
    await Promise.all([cargarCatPills(), cargarTemplates()]);
  });
}

document.getElementById('overlayPregunta').addEventListener('click', e => {
  if (e.target === e.currentTarget) cerrarPregunta();
});
document.getElementById('overlayImport').addEventListener('click', e => {
  if (e.target === e.currentTarget) cerrarImport();
});

// ── UTILS ─────────────────────────────────────────────────────────
function escH(t) {
  return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ══════════════════════════════════════════════════════════════════
// CONFIGURACIÓN DEL SITIO
// ══════════════════════════════════════════════════════════════════
const CFG_FIELDS = [
  'titulo','descripcion','badges','texto_cta',
  'card1_icono','card1_titulo','card1_desc',
  'card2_icono','card2_titulo','card2_desc',
  'card3_icono','card3_titulo','card3_desc',
];

async function cargarConfig() {
  const res  = await fetch('server/config.php');
  const data = await res.json();
  CFG_FIELDS.forEach(k => {
    const el = document.getElementById('cfg-' + k);
    if (el) el.value = data[k] ?? '';
  });
  renderBadgePreview();
}

function renderBadgePreview() {
  const val     = document.getElementById('cfg-badges')?.value ?? '';
  const preview = document.getElementById('badgePreview');
  if (!preview) return;
  preview.innerHTML = val.split(',')
    .map(b => b.trim()).filter(Boolean)
    .map(b => `<span style="background:rgba(43,79,128,.12);border:1px solid rgba(43,79,128,.25);color:#2b4f80;padding:3px 12px;border-radius:20px;font-size:.8rem">${escH(b)}</span>`)
    .join('');
}

document.getElementById('cfg-badges')?.addEventListener('input', renderBadgePreview);

async function guardarConfig() {
  const btn = document.getElementById('cfgSaveBtn');
  btn.disabled = true; btn.textContent = 'Guardando…';

  const payload = {};
  CFG_FIELDS.forEach(k => {
    const el = document.getElementById('cfg-' + k);
    if (el) payload[k] = el.value;
  });

  const res  = await fetch('server/config.php', {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
  });
  const data = await res.json();

  btn.disabled = false; btn.textContent = '💾 Guardar configuración';
  toast(data.ok ? '¡Configuración guardada! ✓' : (data.error ?? 'Error'), !data.ok);
}

// ── IMPORTAR CSV ──────────────────────────────────────────────────────────────
function abrirImport() {
  document.getElementById('csvFile').value = '';
  document.getElementById('csvDropLabel').textContent = '📂 Haz clic para seleccionar un archivo CSV';
  document.getElementById('csvDrop').classList.remove('has-file');
  document.getElementById('importResult').innerHTML = '';
  document.getElementById('importBtn').disabled = true;
  document.getElementById('overlayImport').classList.add('active');
}

function cerrarImport() {
  document.getElementById('overlayImport').classList.remove('active');
}

function onCsvSelected(input) {
  const file = input.files[0];
  if (!file) return;
  document.getElementById('csvDropLabel').textContent = `📄 ${file.name}`;
  document.getElementById('csvDrop').classList.add('has-file');
  document.getElementById('importBtn').disabled = false;
  document.getElementById('importResult').innerHTML = '';
}

async function subirCSV() {
  const file = document.getElementById('csvFile').files[0];
  if (!file) return;

  const btn = document.getElementById('importBtn');
  btn.disabled = true;
  btn.textContent = '…';

  const formData = new FormData();
  formData.append('csv', file);

  try {
    const res  = await fetch('server/importar_preguntas.php', { method: 'POST', body: formData });
    const data = await res.json();
    const div  = document.getElementById('importResult');

    if (data.error) {
      div.innerHTML = `<div class="import-warn" style="border-color:#ef5350;background:#ffebee;color:#b71c1c">❌ ${escH(data.error)}</div>`;
    } else {
      let html = `<div class="import-ok">✅ <strong>${data.inserted}</strong> pregunta${data.inserted !== 1 ? 's' : ''} importada${data.inserted !== 1 ? 's' : ''} correctamente.</div>`;
      if (data.skipped.length) {
        html += `<div class="import-warn">⚠️ <strong>${data.skipped.length}</strong> fila${data.skipped.length !== 1 ? 's' : ''} omitida${data.skipped.length !== 1 ? 's' : ''}:
          <ul>${data.skipped.map(e => `<li>${escH(e)}</li>`).join('')}</ul></div>`;
      }
      div.innerHTML = html;

      if (data.inserted > 0) {
        await Promise.all([cargarPreguntas(activeCatQ), cargarCatPills(), cargarTemplates()]);
        toast(`${data.inserted} pregunta${data.inserted !== 1 ? 's' : ''} importada${data.inserted !== 1 ? 's' : ''}`);
      }
    }
  } catch (e) {
    document.getElementById('importResult').innerHTML =
      `<div class="import-warn" style="border-color:#ef5350;background:#ffebee;color:#b71c1c">❌ Error de conexión. Intenta de nuevo.</div>`;
  }

  btn.disabled = false;
  btn.textContent = 'Importar';
}

// ── INIT ─────────────────────────────────────────────────────────
cargarTemplates();
cargarCatPills();
cargarConfig();
</script>
</body>
</html>
