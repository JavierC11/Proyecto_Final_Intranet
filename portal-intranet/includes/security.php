<?php
/**
 * =============================================================================
 * PORTAL INTRANET - GRUPO LOGISTICO CENTRAL, S.A.
 * Entregable 3 - Controles de seguridad transversales
 * -----------------------------------------------------------------------------
 *  - Configuracion externa (fuera del directorio web)
 *  - Forzado de HTTPS y lista blanca de hosts
 *  - Cabeceras de seguridad HTTP
 *  - Sesion endurecida (cookie segura, timeout por inactividad)
 *  - Proteccion CSRF y escape de salida
 * =============================================================================
 */

// Los datos sensibles (base de datos, logs, credenciales) viven fuera de wwwroot.
if (!defined('GLC_DATA')) {
    define('GLC_DATA', getenv('GLC_DATA') ?: (is_dir('/data') ? '/data' : dirname(__DIR__, 2) . '/data'));
}

function glcPath(string ...$parts): string
{
    $res = GLC_DATA;
    foreach ($parts as $p) {
        $res .= DIRECTORY_SEPARATOR . ltrim($p, '/\\');
    }
    return $res;
}

$configFile = is_file(glcPath('config', 'app.php')) 
    ? glcPath('config', 'app.php') 
    : (is_file(GLC_DATA . '\\config\\app.php') ? GLC_DATA . '\\config\\app.php' : __DIR__ . '/default-config.php');

$GLC_CFG = require $configFile;

function cfg(string $clave, $defecto = null)
{
    global $GLC_CFG;
    return $GLC_CFG[$clave] ?? $defecto;
}

/** Escapa cualquier valor antes de imprimirlo en HTML (defensa contra XSS). */
function e($valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function isHttps(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    return ($_SERVER['SERVER_PORT'] ?? '') === '443';
}

/**
 * Redirige todo el trafico HTTP hacia HTTPS. Solo se acepta un host conocido
 * para evitar ataques de "Host header injection".
 */
function enforceHttps(): void
{
    if (PHP_SAPI === 'cli' || isHttps() || cfg('disable_https', false)) {
        return;
    }
    $host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
    $esIpPrivada = (bool)preg_match('/^(192\.168\.\d{1,3}\.\d{1,3}|10\.\d{1,3}\.\d{1,3}\.\d{1,3}|172\.(1[6-9]|2\d|3[0-1])\.\d{1,3}\.\d{1,3}|127\.0\.0\.1|localhost)$/', $host);
    if (!in_array($host, cfg('allowed_hosts', ['localhost', '127.0.0.1']), true) && !$esIpPrivada) {
        http_response_code(400);
        exit('Solicitud no valida.');
    }
    $port = (int)cfg('https_port', 8443);
    $portSuffix = ($port === 443) ? '' : (':' . $port);
    header('Location: https://' . $host . $portSuffix . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}

function sendSecurityHeaders(): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }
    header_remove('X-Powered-By');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
    // Todos los recursos (Tailwind, iconos, tipografia, fotos) se sirven desde el propio portal: sin dependencia de Internet.
    header("Content-Security-Policy: default-src 'self'; "
        . "script-src 'self' 'unsafe-inline'; "
        . "style-src 'self' 'unsafe-inline'; "
        . "font-src 'self' data:; "
        . "img-src 'self' data:; "
        . "connect-src 'self'; object-src 'self'; frame-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'");
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

/** Inicia la sesion con parametros seguros. Devuelve true si la sesion anterior expiro. */
function startSecureSession(): bool
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return false;
    }
    $ruta = glcPath('sessions');
    if (!is_dir($ruta)) {
        @mkdir($ruta, 0770, true);
    }
    if (is_dir($ruta) && is_writable($ruta)) {
        session_save_path($ruta);
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('GLCSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isHttps(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();

    $expirada = false;
    if (!empty($_SESSION['user'])) {
        $ahora = time();
        $inactivo = $ahora - ($_SESSION['last'] ?? $ahora);
        $total    = $ahora - ($_SESSION['start'] ?? $ahora);
        if ($inactivo > cfg('session_idle', 900) || $total > cfg('session_abs', 28800)) {
            $_SESSION['expired_user'] = $_SESSION['user']['username'];
            unset($_SESSION['user'], $_SESSION['last'], $_SESSION['start']);
            session_regenerate_id(true);
            $expirada = true;
        } else {
            $_SESSION['last'] = $ahora;
        }
    }
    return $expirada;
}

/* ---------------------------- Proteccion CSRF ---------------------------- */

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function csrfVerify(): void
{
    $enviado = $_POST['csrf_token'] ?? '';
    if (!is_string($enviado) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $enviado)) {
        audit('CSRF', 'DENEGADO', 'Formulario enviado con token CSRF invalido o ausente');
        http_response_code(403);
        exit('<!DOCTYPE html><meta charset="utf-8"><title>403</title>'
            . '<h1 style="font-family:Arial">403 - Solicitud rechazada</h1>'
            . '<p style="font-family:Arial">El token de seguridad del formulario no es valido. Regrese e intente de nuevo.</p>');
    }
}

/** Valida que el destino de una redireccion sea una pagina local del portal. */
function safeNext(?string $destino): string
{
    if ($destino && preg_match('/^[a-z0-9\-]+\.php(\?[A-Za-z0-9=&_%\-]*)?$/', $destino)) {
        return $destino;
    }
    return 'index.php';
}

/** Redireccion descartando cualquier HTML ya generado (el layout se imprime antes de la logica de cada pagina). */
function redirigir(string $url): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Location: ' . $url);
    exit;
}
