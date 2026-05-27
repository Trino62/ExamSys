<?php
require_once 'server/conexion.php';
require_once 'server/site_config_helper.php';

try {
    $pdo = (new ConexionPDO())->Conexion();
    $cfg = getSiteConfig($pdo);
} catch (Exception $e) {
    $cfg = [];
}

$titulo      = htmlspecialchars($cfg['titulo']      ?? 'Evaluación de Conocimientos');
$descripcion = htmlspecialchars($cfg['descripcion'] ?? '');
$textoCta    = htmlspecialchars($cfg['texto_cta']   ?? '');
$badges      = array_filter(array_map('trim', explode(',', $cfg['badges'] ?? '')));

$cards = [
    ['icono' => $cfg['card1_icono'] ?? '🎯', 'titulo' => $cfg['card1_titulo'] ?? '', 'desc' => $cfg['card1_desc'] ?? ''],
    ['icono' => $cfg['card2_icono'] ?? '🏢', 'titulo' => $cfg['card2_titulo'] ?? '', 'desc' => $cfg['card2_desc'] ?? ''],
    ['icono' => $cfg['card3_icono'] ?? '📋', 'titulo' => $cfg['card3_titulo'] ?? '', 'desc' => $cfg['card3_desc'] ?? ''],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $titulo ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />
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
    body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #f2f5f8; margin: 0; }

    .hero { background: linear-gradient(135deg, #1d3759 0%, #2b4f80 60%, #3a6bad 100%); color: white; padding: 64px 24px 48px; text-align: center; }
    .hero h1 { font-size: 2rem; font-weight: 700; margin-bottom: 12px; }
    .hero p  { font-size: 1.1rem; opacity: 0.88; max-width: 560px; margin: 0 auto; }
    .hero .badge-stack { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-top: 22px; }
    .hero .badge-stack span { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 4px 14px; border-radius: 20px; font-size: 0.82rem; letter-spacing: .4px; }

    .cards-section { max-width: 820px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
    .info-card { background: white; border-radius: 12px; padding: 28px 22px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    .info-card .icon { font-size: 2rem; margin-bottom: 12px; }
    .info-card h3 { font-size: 1rem; font-weight: 700; color: #2b4f80; margin-bottom: 8px; }
    .info-card p  { font-size: 0.88rem; color: #555; line-height: 1.55; margin: 0; }

    .cta-section { max-width: 480px; margin: 0 auto 60px; padding: 0 20px; text-align: center; }
    .cta-section p { color: #666; font-size: 0.9rem; margin-bottom: 24px; }
    .btn-primary-custom { display: block; width: 100%; background: #2b4f80; color: white; border: none; padding: 15px; border-radius: 8px; font-size: 1.05rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: background .2s; margin-bottom: 12px; }
    .btn-primary-custom:hover { background: #1d3759; color: white; }

.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
    .modal-overlay.active { display: flex; }
    .modal-box { background: white; border-radius: 12px; padding: 36px 32px; width: 90%; max-width: 360px; text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,0.2); }
    .modal-box h2 { color: #2b4f80; font-size: 1.2rem; margin-bottom: 8px; }
    .modal-box p  { color: #666; font-size: 0.88rem; margin-bottom: 20px; }
    .modal-box input { width: 100%; padding: 10px 14px; border: 2px solid #ddd; border-radius: 8px; font-size: 1.1rem; letter-spacing: 6px; text-align: center; margin-bottom: 8px; outline: none; }
    .modal-box input:focus { border-color: #2b4f80; }
    .modal-box input.error    { border-color: #e53935; }
    .modal-box input:disabled { background: #f5f5f5; color: #aaa; cursor: not-allowed; }
    .modal-error         { color: #e53935; font-size: 0.82rem; min-height: 18px; margin-bottom: 16px; }
    .modal-error.warning { color: #fb8c00; }
    .modal-actions { display: flex; gap: 10px; }
    .modal-actions button { flex: 1; padding: 10px; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; border: none; }
    .btn-confirm { background: #2b4f80; color: white; }
    .btn-confirm:hover { background: #1d3759; }
    .btn-cancel  { background: #f2f5f8; color: #555; }
    .btn-cancel:hover { background: #e0e5eb; }

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
    [data-theme="dark"] .info-card { background: var(--bg-card); box-shadow: 0 2px 10px var(--sh); }
    [data-theme="dark"] .info-card h3 { color: #7aacde; }
    [data-theme="dark"] .info-card p  { color: var(--text-2); }
    [data-theme="dark"] .cta-section p { color: var(--text-2); }
    [data-theme="dark"] .modal-overlay { background: rgba(0,0,0,.65); }
    [data-theme="dark"] .modal-box { background: var(--bg-card); }
    [data-theme="dark"] .modal-box h2 { color: #7aacde; }
    [data-theme="dark"] .modal-box p  { color: var(--text-2); }
    [data-theme="dark"] .modal-box input { background: var(--bg-sub); border-color: var(--border); color: var(--text); }
    [data-theme="dark"] .modal-box input:focus { border-color: #4a7bc4; }
    [data-theme="dark"] .modal-box input.error { border-color: #ef5350; }
    [data-theme="dark"] .modal-box input:disabled { background: var(--bg-sub); color: var(--text-3); }
    [data-theme="dark"] .btn-cancel { background: var(--bg-sub); color: var(--text-2); }
    [data-theme="dark"] .btn-cancel:hover { background: var(--border); }
    [data-theme="dark"] .modal-error { color: #ef9a9a; }
    [data-theme="dark"] .modal-error.warning { color: #ffb74d; }
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

  <div class="hero">
    <h1><?= $titulo ?></h1>
    <p><?= $descripcion ?></p>
    <?php if ($badges): ?>
    <div class="badge-stack">
      <?php foreach ($badges as $b): ?>
        <span><?= htmlspecialchars($b) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="cards-section">
    <?php foreach ($cards as $c): ?>
    <div class="info-card">
      <div class="icon"><?= $c['icono'] ?></div>
      <h3><?= htmlspecialchars($c['titulo']) ?></h3>
      <p><?= htmlspecialchars($c['desc']) ?></p>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="cta-section">
    <?php if ($textoCta): ?><p><?= $textoCta ?></p><?php endif; ?>
    <button class="btn-primary-custom" onclick="abrirModal()">Ver resultados</button>
  </div>

  <div class="modal-overlay" id="modalPin">
    <div class="modal-box">
      <h2>🔒 Acceso restringido</h2>
      <p>Esta sección es solo para administradores. Ingresa el PIN para continuar.</p>
      <input type="password" id="pinInput" maxlength="4" placeholder="••••"
             oninput="limpiarError()" onkeydown="if(event.key==='Enter') verificarPin()" />
      <div class="modal-error" id="pinError"></div>
      <div class="modal-actions">
        <button class="btn-cancel"  onclick="cerrarModal()">Cancelar</button>
        <button class="btn-confirm" onclick="verificarPin()">Entrar</button>
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

    let lockTimer = null;

    // ── Countdown mientras dura el bloqueo ───────────────────────────────────
    function mostrarBloqueo(seconds) {
      const input  = document.getElementById("pinInput");
      const btnOk  = document.querySelector(".btn-confirm");
      const error  = document.getElementById("pinError");

      input.disabled  = true;
      input.value     = "";
      btnOk.disabled  = true;
      btnOk.textContent = "Bloqueado";
      error.classList.add("warning");

      let remaining = seconds;
      clearInterval(lockTimer);

      function tick() {
        if (remaining <= 0) {
          clearInterval(lockTimer);
          input.disabled  = false;
          btnOk.disabled  = false;
          btnOk.textContent = "Entrar";
          error.textContent = "";
          error.classList.remove("warning");
          setTimeout(() => input.focus(), 50);
          return;
        }
        const m = Math.floor(remaining / 60);
        const s = String(remaining % 60).padStart(2, '0');
        error.textContent = `⛔ Demasiados intentos. Intenta en ${m}:${s}`;
        remaining--;
      }

      tick();
      lockTimer = setInterval(tick, 1000);
    }

    // ── Modal ────────────────────────────────────────────────────────────────
    function abrirModal() {
      document.getElementById("pinInput").value = "";
      document.getElementById("pinError").textContent = "";
      document.getElementById("pinInput").classList.remove("error");
      document.getElementById("modalPin").classList.add("active");

      // Verificar si hay bloqueo activo en sesión
      fetch("server/auth.php")
        .then(r => r.json())
        .then(data => {
          if (data.locked && data.seconds > 0) {
            mostrarBloqueo(data.seconds);
          } else {
            setTimeout(() => document.getElementById("pinInput").focus(), 100);
          }
        })
        .catch(() => setTimeout(() => document.getElementById("pinInput").focus(), 100));
    }

    function cerrarModal() {
      clearInterval(lockTimer);
      document.getElementById("modalPin").classList.remove("active");
    }

    function limpiarError() {
      document.getElementById("pinError").textContent = "";
      document.getElementById("pinInput").classList.remove("error");
    }

    function verificarPin() {
      const pin   = document.getElementById("pinInput").value.trim();
      const btnOk = document.querySelector(".btn-confirm");
      btnOk.disabled = true; btnOk.textContent = "…";

      fetch("server/auth.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ pin }),
      })
        .then(r => r.json())
        .then(data => {
          if (data.ok) {
            window.location.href = "exames.php";
          } else if (data.locked) {
            mostrarBloqueo(data.seconds);
          } else {
            const left = data.attempts_left ?? null;
            const msg  = left !== null
              ? `PIN incorrecto. ${left} intento${left !== 1 ? 's' : ''} restante${left !== 1 ? 's' : ''}.`
              : "PIN incorrecto. Inténtalo de nuevo.";
            document.getElementById("pinError").textContent = msg;
            document.getElementById("pinInput").classList.add("error");
            document.getElementById("pinInput").value = "";
            document.getElementById("pinInput").focus();
            btnOk.disabled = false; btnOk.textContent = "Entrar";
          }
        })
        .catch(() => {
          document.getElementById("pinError").textContent = "Error de conexión. Intenta de nuevo.";
          btnOk.disabled = false; btnOk.textContent = "Entrar";
        });
    }

    document.getElementById("modalPin").addEventListener("click", function(e) {
      if (e.target === this) cerrarModal();
    });
  </script>
</body>
</html>
