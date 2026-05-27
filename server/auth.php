<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/env.php';
loadEnv(__DIR__ . '/../.env');

define('ADMIN_PIN',     getenv('ADMIN_PIN') ?: '1234');
define('MAX_ATTEMPTS',  5);
define('LOCKOUT_SECS',  300);   // 5 minutos

// ── Helpers de sesión ────────────────────────────────────────────────────────
function getAttempts(): int  { return (int)($_SESSION['pin_attempts']   ?? 0); }
function getLockUntil(): int { return (int)($_SESSION['pin_lock_until'] ?? 0); }

function isLocked(): bool {
    return getLockUntil() > 0 && time() < getLockUntil();
}

function resetIfExpired(): void {
    if (getLockUntil() > 0 && time() >= getLockUntil()) {
        $_SESSION['pin_attempts']   = 0;
        $_SESSION['pin_lock_until'] = 0;
    }
}

// ── Routing ──────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $locked  = isLocked();
    echo json_encode([
        'admin'   => !empty($_SESSION['admin']),
        'locked'  => $locked,
        'seconds' => $locked ? (getLockUntil() - time()) : 0,
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// ── POST: verificar PIN ──────────────────────────────────────────────────────
resetIfExpired();

if (isLocked()) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'locked' => true, 'seconds' => getLockUntil() - time()]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$pin  = trim($data['pin'] ?? '');

if ($pin === ADMIN_PIN) {
    $_SESSION['admin']          = true;
    $_SESSION['pin_attempts']   = 0;
    $_SESSION['pin_lock_until'] = 0;
    echo json_encode(['ok' => true]);
} else {
    usleep(400_000);   // 400 ms — ralentiza ataques automatizados

    $attempts = getAttempts() + 1;
    $_SESSION['pin_attempts'] = $attempts;

    if ($attempts >= MAX_ATTEMPTS) {
        $_SESSION['pin_lock_until'] = time() + LOCKOUT_SECS;
        http_response_code(429);
        echo json_encode(['ok' => false, 'locked' => true, 'seconds' => LOCKOUT_SECS]);
    } else {
        http_response_code(401);
        echo json_encode(['ok' => false, 'attempts_left' => MAX_ATTEMPTS - $attempts]);
    }
}
