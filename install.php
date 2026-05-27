<?php
/**
 * ExamSys — Asistente de instalación
 * Configura la base de datos, ejecuta migraciones y crea el archivo .env.
 * Una vez instalado redirige automáticamente a index.php.
 */

$envPath  = __DIR__ . '/.env';
$lockPath = __DIR__ . '/.installed';

// ── Guard fuerte: lock file → redirigir sin importar estado de BD ─────────────
if (file_exists($lockPath)) {
    header('Location: index.php');
    exit;
}

// ── Cargar .env si ya existe ──────────────────────────────────────────────────
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k); $v = trim($v, " \t\"'");
        if ($k !== '' && getenv($k) === false) putenv("$k=$v");
    }
}

// ── Guard: si ya está instalado y la BD funciona → redirigir ─────────────────
if (getenv('DB_USER') !== false) {
    try {
        $h = (getenv('DB_HOST') ?: 'localhost') . ':' . (getenv('DB_PORT') ?: '3306');
        $n = getenv('DB_NAME') ?: 'examen';
        $u = getenv('DB_USER');
        $p = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
        new PDO("mysql:host=$h;dbname=$n;charset=utf8mb4", $u, $p,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        header('Location: index.php');
        exit;
    } catch (Exception $e) { /* credenciales hay pero BD no conecta — mostrar form */ }
}

// ── Procesar formulario ───────────────────────────────────────────────────────
$errors  = [];
$success = false;

$fields = [
    'db_host' => 'localhost',
    'db_port' => '3306',
    'db_name' => 'examen',
    'db_user' => '',
    'db_pass' => '',
    'pin'     => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $k => $_) {
        $fields[$k] = $k === 'db_pass' ? ($_POST[$k] ?? '') : trim($_POST[$k] ?? '');
    }

    // Validar
    if (!$fields['db_host']) $errors[] = 'El host de la base de datos es obligatorio.';
    if (!$fields['db_user']) $errors[] = 'El usuario de la base de datos es obligatorio.';
    if (!$fields['db_name']) $errors[] = 'El nombre de la base de datos es obligatorio.';
    if (!preg_match('/^\d{4}$/', $fields['pin'])) $errors[] = 'El PIN debe ser exactamente 4 dígitos numéricos.';

    // Conectar y crear BD
    $pdo = null;
    if (empty($errors)) {
        try {
            $pdoBase = new PDO(
                "mysql:host={$fields['db_host']}:{$fields['db_port']};charset=utf8mb4",
                $fields['db_user'], $fields['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $dbSafe = preg_replace('/[^a-zA-Z0-9_]/', '', $fields['db_name']);
            $pdoBase->exec("CREATE DATABASE IF NOT EXISTS `$dbSafe` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $pdo = new PDO(
                "mysql:host={$fields['db_host']}:{$fields['db_port']};dbname={$fields['db_name']};charset=utf8mb4",
                $fields['db_user'], $fields['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => true]
            );
        } catch (PDOException $e) {
            $errors[] = 'No se pudo conectar a MySQL: ' . $e->getMessage();
        }
    }

    // Ejecutar migraciones
    if (empty($errors) && $pdo) {
        $migFiles = [
            __DIR__ . '/db/preguntas.sql',
            __DIR__ . '/db/migracion_resultados.sql',
            __DIR__ . '/db/migracion_template_slug.sql',
            __DIR__ . '/db/migracion_email.sql',
            __DIR__ . '/db/migracion_nota.sql',
            __DIR__ . '/db/migracion_templates.sql',
            __DIR__ . '/db/migracion_config.sql',
            __DIR__ . '/db/preguntas_extra.sql',
        ];
        // Errores de MySQL que se pueden ignorar (ya existe, clave duplicada)
        $ignorable = [1050, 1060, 1061, 1062, 1091];

        foreach ($migFiles as $file) {
            if (!file_exists($file)) continue;
            $sql = file_get_contents($file);
            $sql = preg_replace('/--[^\n]*/', '', $sql);           // quitar comentarios --
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);       // quitar /* ... */
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                try {
                    $pdo->exec($stmt);
                } catch (PDOException $e) {
                    $code = (int)($e->errorInfo[1] ?? 0);
                    if (!in_array($code, $ignorable)) {
                        $errors[] = 'Error en migración (' . basename($file) . '): ' . $e->getMessage();
                        break 2;
                    }
                }
            }
        }
    }

    // Escribir .env
    if (empty($errors)) {
        if (!is_writable(__DIR__)) {
            $errors[] = 'Sin permisos de escritura en ' . __DIR__ . '. Ejecuta: chmod 755 ' . __DIR__;
        } else {
            $env = "# ExamSys — configuración de entorno\n"
                 . "# No compartas este archivo ni lo subas al repositorio\n\n"
                 . "DB_HOST={$fields['db_host']}\n"
                 . "DB_PORT={$fields['db_port']}\n"
                 . "DB_NAME={$fields['db_name']}\n"
                 . "DB_USER={$fields['db_user']}\n"
                 . "DB_PASS={$fields['db_pass']}\n"
                 . "ADMIN_PIN={$fields['pin']}\n";

            if (file_put_contents($envPath, $env) === false) {
                $errors[] = 'No se pudo escribir el archivo .env.';
            } else {
                // Lock file: impide re-ejecutar install.php aunque se borre .env
                file_put_contents($lockPath, date('Y-m-d H:i:s'));
                $success = true;
            }
        }
    }
}

// ── Verificar permisos de escritura antes de mostrar el form ─────────────────
$canWrite = is_writable(__DIR__);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Instalación — ExamSys</title>
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

    .header { background: linear-gradient(135deg, #1d3759 0%, #2b4f80 100%); color: white; padding: 32px 24px 28px; text-align: center; }
    .header h1 { margin: 0 0 6px; font-size: 1.5rem; font-weight: 700; }
    .header p  { margin: 0; opacity: .8; font-size: .9rem; }

    .card { background: white; width: 92%; max-width: 520px; margin: 36px auto 48px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,.1); overflow: hidden; }
    .card-body { padding: 32px 28px; }

    h2 { font-size: 1rem; font-weight: 700; color: #2b4f80; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
    h2 + h2, .section + h2 { margin-top: 28px; padding-top: 24px; border-top: 1px solid #eef0f3; }

    .row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .field { margin-bottom: 14px; }
    .field label { display: block; font-size: .82rem; font-weight: 600; color: #444; margin-bottom: 5px; }
    .field input { width: 100%; padding: 10px 13px; border: 2px solid #e0e7ef; border-radius: 8px; font-size: .95rem; font-family: inherit; outline: none; transition: border-color .15s; }
    .field input:focus { border-color: #2b4f80; }
    .field small { display: block; color: #888; font-size: .78rem; margin-top: 4px; }

    .btn { display: block; width: 100%; margin-top: 24px; padding: 14px; background: #2b4f80; color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: background .2s; }
    .btn:hover { background: #1d3759; }

    .alert { border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; font-size: .88rem; line-height: 1.55; }
    .alert-error   { background: #ffebee; border: 1.5px solid #ef5350; color: #b71c1c; }
    .alert-warning { background: #fff8e1; border: 1.5px solid #ffb300; color: #7a5800; }
    .alert ul { margin: 6px 0 0; padding-left: 20px; }
    .alert li { margin-bottom: 3px; }

    /* ── Success ────────────────────────────── */
    .success-body { padding: 40px 28px; text-align: center; }
    .success-icon { font-size: 3.5rem; margin-bottom: 16px; }
    .success-body h2 { justify-content: center; font-size: 1.3rem; color: #2e7d32; }
    .success-body p  { color: #555; font-size: .9rem; line-height: 1.6; margin: 0 0 24px; }
    .btn-group { display: flex; flex-direction: column; gap: 10px; }
    .btn-primary  { display: block; padding: 13px; background: #2b4f80; color: white; border-radius: 8px; font-weight: 700; font-size: .95rem; text-decoration: none; text-align: center; transition: background .2s; }
    .btn-primary:hover { background: #1d3759; }
    .btn-outline  { display: block; padding: 11px; background: white; color: #2b4f80; border: 2px solid #2b4f80; border-radius: 8px; font-weight: 700; font-size: .9rem; text-decoration: none; text-align: center; transition: background .2s, color .2s; }
    .btn-outline:hover { background: #2b4f80; color: white; }

    .step-badge { background: #2b4f80; color: white; border-radius: 50%; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; flex-shrink: 0; }

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
    [data-theme="dark"] .card { background: var(--bg-card); }
    [data-theme="dark"] h2 { color: #7aacde; }
    [data-theme="dark"] h2 + h2,
    [data-theme="dark"] .section + h2 { border-top-color: var(--border); }
    [data-theme="dark"] .field label { color: var(--text-2); }
    [data-theme="dark"] .field input { background: var(--bg-sub); border-color: var(--border); color: var(--text); }
    [data-theme="dark"] .field input:focus { border-color: #4a7bc4; }
    [data-theme="dark"] .field small { color: var(--text-3); }
    [data-theme="dark"] .alert-error   { background: #1e0c0c; border-color: #b71c1c; color: #ef9a9a; }
    [data-theme="dark"] .alert-warning { background: #1f1800; border-color: #5c4200; color: #ffcc80; }
    [data-theme="dark"] .success-body h2 { color: #66bb6a; }
    [data-theme="dark"] .success-body p  { color: var(--text-2); }
    [data-theme="dark"] .btn-outline { background: transparent; color: #7aacde; border-color: #4a7bc4; }
    [data-theme="dark"] .btn-outline:hover { background: #4a7bc4; color: white; }
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

<div class="header">
  <h1>⚙️ Instalación de ExamSys</h1>
  <p>Configura tu base de datos y crea las tablas necesarias</p>
</div>

<div class="card">

<?php if ($success): ?>
  <div class="success-body">
    <div class="success-icon">✅</div>
    <h2>¡Instalación completada!</h2>
    <p>La base de datos fue creada, las migraciones se ejecutaron correctamente y el archivo <code>.env</code> fue generado.</p>
    <div style="background:#fff8e1;border:1.5px solid #ffb300;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:.84rem;color:#7a5800;text-align:left;line-height:1.55">
      ⚠️ <strong>Por seguridad:</strong> elimina o renombra <code>install.php</code> de tu servidor.<br>
      El archivo <code>.installed</code> evita que se re-ejecute, pero eliminarlo es la opción más segura.
    </div>
    <div class="btn-group">
      <a href="index.php" class="btn-primary">Ver la landing →</a>
      <a href="exames.php" class="btn-outline">Ir al panel de admin</a>
    </div>
  </div>

<?php else: ?>
  <div class="card-body">

    <?php if (!$canWrite): ?>
    <div class="alert alert-warning">
      ⚠️ <strong>Sin permisos de escritura.</strong> El servidor web no puede crear el archivo <code>.env</code>.
      Ejecuta en tu servidor:<br>
      <code>chmod 755 <?= htmlspecialchars(__DIR__) ?></code>
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <strong>Corrije los siguientes errores antes de continuar:</strong>
      <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">

      <h2><span class="step-badge">1</span> Base de datos</h2>

      <div class="row">
        <div class="field">
          <label for="db_host">Host</label>
          <input id="db_host" name="db_host" value="<?= htmlspecialchars($fields['db_host']) ?>" placeholder="localhost" />
        </div>
        <div class="field">
          <label for="db_port">Puerto</label>
          <input id="db_port" name="db_port" value="<?= htmlspecialchars($fields['db_port']) ?>" placeholder="3306" />
        </div>
      </div>

      <div class="field">
        <label for="db_name">Nombre de la base de datos</label>
        <input id="db_name" name="db_name" value="<?= htmlspecialchars($fields['db_name']) ?>" placeholder="examen" />
        <small>Se creará automáticamente si no existe.</small>
      </div>

      <div class="row">
        <div class="field">
          <label for="db_user">Usuario</label>
          <input id="db_user" name="db_user" value="<?= htmlspecialchars($fields['db_user']) ?>" placeholder="root" />
        </div>
        <div class="field">
          <label for="db_pass">Contraseña</label>
          <input id="db_pass" name="db_pass" type="password" value="<?= htmlspecialchars($fields['db_pass']) ?>" placeholder="••••••" />
        </div>
      </div>

      <h2><span class="step-badge">2</span> Seguridad</h2>

      <div class="field">
        <label for="pin">PIN de administrador</label>
        <input id="pin" name="pin" type="password" maxlength="4"
               value="<?= htmlspecialchars($fields['pin']) ?>"
               placeholder="4 dígitos" style="letter-spacing:6px;text-align:center;" />
        <small>Lo necesitarás para acceder al panel de resultados y configuración.</small>
      </div>

      <button type="submit" class="btn" <?= !$canWrite ? 'disabled title="Sin permisos de escritura"' : '' ?>>
        Instalar →
      </button>

    </form>
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
