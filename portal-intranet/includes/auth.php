<?php
/**
 * =============================================================================
 * PORTAL INTRANET - GRUPO LOGISTICO CENTRAL, S.A.
 * Autenticacion (AD / LDAP), Single Sign-On (IWA) y control de acceso (RBAC)
 * Version Entregable 3: sin credenciales en codigo, sin accesos de prueba.
 * =============================================================================
 */

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/ldap.php';

enforceHttps();
sendSecurityHeaders();
if (PHP_SAPI !== 'cli') {
    ob_start(); // permite redirigir despues de imprimir el encabezado comun del portal
}
$GLOBALS['SESION_EXPIRADA'] = startSecureSession();
if ($GLOBALS['SESION_EXPIRADA']) {
    audit('SESION', 'EXPIRADA', 'Sesion cerrada por inactividad', null, $_SESSION['expired_user'] ?? 'desconocido');
}

/** Datos de contacto complementarios (el directorio de AD no los almacena todos). */
const PERFIL_EXTRA = [
    'clopez'   => ['sede' => 'Oficina Central (VLAN 20)',   'extension' => '1004', 'foto' => 'assets/img/photo-1534528741775-53994a69daeb.jpg'],
    'mmorales' => ['sede' => 'Oficina Central (VLAN 20)',   'extension' => '1002', 'foto' => 'assets/img/photo-1573496359142-b8d87734a5a2.jpg'],
    'jperez'   => ['sede' => 'Oficina Central (VLAN 20)',   'extension' => '1003', 'foto' => 'assets/img/photo-1507003211169-0a1dd7228f2d.jpg'],
    'rgomez'   => ['sede' => 'CD Regional Norte (VLAN 30)', 'extension' => '2105', 'foto' => 'assets/img/photo-1500648767791-00dcc994a43e.jpg'],
];

function avatarPorDefecto(string $nombre): string
{
    $ini = '';
    foreach (preg_split('/\s+/', trim($nombre)) as $p) {
        $ini .= mb_strtoupper(mb_substr($p, 0, 1));
        if (mb_strlen($ini) >= 2) break;
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect width="200" height="200" fill="#0369a1"/>'
         . '<text x="100" y="125" font-family="Arial" font-size="80" fill="#fff" text-anchor="middle">' . htmlspecialchars($ini) . '</text></svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

/** Convierte el perfil de AD en la estructura que se guarda en sesion. */
function construirSesion(array $ad, string $metodo): array
{
    $extra  = PERFIL_EXTRA[$ad['username']] ?? [];
    $grupos = array_values(array_filter($ad['grupos'], fn($g) => str_starts_with($g, 'GRP-')));
    return [
        'username'     => $ad['username'],
        'nombre'       => $ad['nombre'],
        'email'        => $ad['email'],
        'cargo'        => $ad['cargo'],
        'departamento' => $ad['departamento'],
        'sede'         => $extra['sede'] ?? 'Oficina Central (VLAN 20)',
        'extension'    => $ad['extension'] ?? ($extra['extension'] ?? 'N/D'),
        'foto'         => $extra['foto'] ?? avatarPorDefecto($ad['nombre']),
        'grupos'       => $grupos,
        'roles'        => rolesDeGrupos($grupos),
        'permisos'     => permisosDeGrupos($grupos),
        'auth_method'  => $metodo,
    ];
}

function abrirSesion(array $perfil): void
{
    session_regenerate_id(true);          // evita fijacion de sesion
    $_SESSION['user']  = $perfil;
    $_SESSION['start'] = time();
    $_SESSION['last']  = time();
    unset($_SESSION['csrf']);
}

/* ------------------------- Control de intentos fallidos ------------------------- */

function intentosFallidos(string $usuario, string $ip): array
{
    $desde = time() - cfg('lockout_window', 600);
    $pdo = db();
    $a = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE usuario = ? AND ok = 0 AND ts > ?');
    $a->execute([$usuario, $desde]);
    $b = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND ok = 0 AND ts > ?');
    $b->execute([$ip, $desde]);
    return [(int)$a->fetchColumn(), (int)$b->fetchColumn()];
}

function registrarIntento(string $usuario, string $ip, bool $ok): void
{
    $st = db()->prepare('INSERT INTO login_attempts (usuario, ip, ok, ts) VALUES (?,?,?,?)');
    $st->execute([$usuario, $ip, $ok ? 1 : 0, time()]);
    if ($ok) { // un acceso correcto reinicia el contador de esa cuenta
        db()->prepare('DELETE FROM login_attempts WHERE usuario = ? AND ok = 0')->execute([$usuario]);
    }
}

/**
 * Autentica con credenciales de dominio.
 * @return array{ok:bool,error:?string}  error: formato | bloqueado | credenciales | servicio | sin_perfil
 */
function loginUser(string $usuario, string $clave): array
{
    $usuario = strtolower(trim($usuario));
    if (str_contains($usuario, '\\')) {
        $usuario = substr($usuario, strrpos($usuario, '\\') + 1);
    }
    $usuario = explode('@', $usuario)[0];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'local';

    if (!preg_match('/^[a-z0-9._-]{1,32}$/', $usuario) || strlen($clave) > 128) {
        audit('LOGIN', 'FALLO', 'Formato de usuario o contrasena no valido', null, 'invalido');
        return ['ok' => false, 'error' => 'formato'];
    }

    [$porUsuario, $porIp] = intentosFallidos($usuario, $ip);
    if ($porUsuario >= cfg('lockout_max', 5) || $porIp >= cfg('lockout_max_ip', 15)) {
        audit('LOGIN', 'BLOQUEADO', 'Cuenta o IP bloqueada temporalmente por intentos fallidos', null, $usuario);
        return ['ok' => false, 'error' => 'bloqueado'];
    }

    $valida = adAuthenticate($usuario, $clave);
    if ($valida === null) {
        audit('LOGIN', 'ERROR', 'Servicio de directorio no disponible', null, $usuario);
        return ['ok' => false, 'error' => 'servicio'];
    }
    if (!$valida) {
        registrarIntento($usuario, $ip, false);
        audit('LOGIN', 'FALLO', 'Credenciales invalidas', null, $usuario);
        return ['ok' => false, 'error' => 'credenciales'];
    }

    $ad = adGetUser($usuario);
    if (!$ad) {
        audit('LOGIN', 'FALLO', 'Credenciales validas pero la cuenta no tiene perfil habilitado en AD', null, $usuario);
        return ['ok' => false, 'error' => 'sin_perfil'];
    }
    registrarIntento($usuario, $ip, true);
    abrirSesion(construirSesion($ad, 'Active Directory LDAP (glcentral.local)'));
    audit('LOGIN', 'OK', 'Autenticacion con credenciales de dominio. Grupos: ' . implode(', ', $_SESSION['user']['grupos']), null, $usuario);
    return ['ok' => true, 'error' => null];
}

/**
 * SSO con Autenticacion Integrada de Windows (Kerberos/NTLM). IIS valida la identidad y
 * entrega el usuario en AUTH_USER; el portal solo consulta sus grupos en AD.
 */
function ssoLogin(): array
{
    $iis = $_SERVER['AUTH_USER'] ?? $_SERVER['LOGON_USER'] ?? $_SERVER['REMOTE_USER'] ?? '';
    $esSimulado = false;
    if ($iis === '' && cfg('sso_simulado')) {
        $iis = cfg('sso_simulado');
        $esSimulado = true;
    }
    if ($iis === '') {
        audit('SSO', 'FALLO', 'IIS no entrego una identidad de Windows (autenticacion integrada no negociada)', null, 'anonimo');
        return ['ok' => false, 'error' => 'sin_identidad'];
    }
    $partes = explode('\\', $iis);
    $sam    = strtolower(explode('@', end($partes))[0]);
    $dominio = count($partes) > 1 ? strtoupper($partes[0]) : strtoupper(cfg('ad_netbios', 'GLCENTRAL'));

    if ($dominio !== strtoupper(cfg('ad_netbios', 'GLCENTRAL')) || !preg_match('/^[a-z0-9._-]{1,32}$/', $sam)) {
        audit('SSO', 'DENEGADO', 'Identidad fuera del dominio permitido: ' . $iis, null, $sam);
        return ['ok' => false, 'error' => 'dominio'];
    }
    $ad = adGetUser($sam);
    if (!$ad) {
        audit('SSO', 'DENEGADO', 'La identidad ' . $iis . ' no existe o esta deshabilitada en AD', null, $sam);
        return ['ok' => false, 'error' => 'sin_perfil'];
    }
    $metodoStr = $esSimulado ? ('SSO Simulado (Entorno Local) - ' . $iis) : ('SSO Windows integrado (Kerberos/NTLM) - ' . $iis);
    abrirSesion(construirSesion($ad, $metodoStr));
    audit('SSO', 'OK', 'Inicio de sesion transparente. Identidad: ' . $iis . '. Grupos: ' . implode(', ', $_SESSION['user']['grupos']), null, $sam);
    return ['ok' => true, 'error' => null];
}

function logoutUser(): void
{
    if (isset($_SESSION['user'])) {
        audit('LOGOUT', 'OK', 'Cierre de sesion');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function getCurrentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isAuthenticated(): bool
{
    return isset($_SESSION['user']);
}

function requireAuth(): void
{
    if (!isAuthenticated()) {
        $destino = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
        if (!empty($_SERVER['QUERY_STRING'])) {
            $destino .= '?' . $_SERVER['QUERY_STRING'];
        }
        header('Location: login.php?next=' . urlencode(safeNext($destino)));
        exit;
    }
}

/** El grupo de TI conserva acceso transversal, igual que en el Entregable 2. */
function hasRole(string $grupo): bool
{
    $u = getCurrentUser();
    if (!$u) return false;
    if (in_array('GRP-TI-Administradores', $u['grupos'], true)) return true;
    return in_array($grupo, $u['grupos'], true);
}

function hasDepartment(string $depto): bool
{
    $u = getCurrentUser();
    if (!$u) return false;
    if (in_array('GRP-TI-Administradores', $u['grupos'], true)) return true;
    return strcasecmp($u['departamento'], $depto) === 0;
}

/** Registra el rechazo en auditoria y marca la respuesta como HTTP 403. */
function denegarAcceso(string $recurso, string $requerido): void
{
    audit('ACCESO_DENEGADO', 'DENEGADO', "Recurso: $recurso. Requiere: $requerido", $recurso);
    http_response_code(403);
}
