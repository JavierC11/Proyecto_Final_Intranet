<?php
require_once __DIR__ . '/includes/auth.php';

$next = safeNext($_GET['next'] ?? $_POST['next'] ?? '');

// Si ya hay sesion activa, se envia al portal (salvo que quiera cambiar de usuario)
if (isAuthenticated() && !isset($_GET['switch'])) {
    header('Location: ' . $next);
    exit;
}

$mensajes = [
    'formato'      => 'El usuario o la contraseña no tienen un formato válido.',
    'credenciales' => 'Credenciales inválidas. Verifique su usuario y contraseña de dominio (glcentral.local).',
    'bloqueado'    => 'Demasiados intentos fallidos. Por seguridad el acceso se bloqueó durante unos minutos; intente más tarde.',
    'servicio'     => 'El servicio de directorio no está disponible en este momento. Contacte a la Mesa de Ayuda de TI.',
    'sin_perfil'   => 'La cuenta no tiene un perfil habilitado en Active Directory.',
];
$error = null;
$info  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $r = loginUser((string)($_POST['username'] ?? ''), (string)($_POST['password'] ?? ''));
    if ($r['ok']) {
        header('Location: ' . $next);
        exit;
    }
    $error = $mensajes[$r['error']] ?? $mensajes['credenciales'];
}

if (!empty($GLOBALS['SESION_EXPIRADA'])) {
    $info = 'Su sesión se cerró por inactividad. Vuelva a iniciar sesión.';
} elseif (isset($_GET['logged_out'])) {
    $info = 'La sesión se cerró correctamente.';
} elseif (isset($_GET['sso']) && $_GET['sso'] === 'fail') {
    $error = 'No fue posible validar su identidad de Windows. Inicie sesión con su usuario y contraseña de dominio.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Intranet GLC</title>
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/intranet.css">
</head>
<body class="login-page">

<div class="login-top">
    <div class="container">
        <span class="brand">
            <span class="brand-mark">GLC</span>
            <span>
                <span class="brand-name">Grupo Logístico Central</span>
                <span class="brand-sub">Intranet corporativa</span>
            </span>
        </span>
    </div>
</div>

<div class="login-wrap">
    <div class="login-box">
        <div class="login-form">
            <h1>Iniciar sesión</h1>
            <p class="muted">Use su usuario y contraseña de red (los mismos de Windows).</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>
            <?php if ($info): ?>
                <div class="alert alert-info"><?php echo e($info); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="form" autocomplete="off">
                <?php echo csrfField(); ?>
                <input type="hidden" name="next" value="<?php echo e($next); ?>">
                <div class="form-row">
                    <label for="username">Usuario</label>
                    <input id="username" type="text" name="username" required maxlength="64" placeholder="ej. clopez" autofocus>
                    <span class="form-help">Sin el dominio: <em>clopez</em>, no <em>clopez@glcentral.local</em></span>
                </div>
                <div class="form-row">
                    <label for="password">Contraseña</label>
                    <input id="password" type="password" name="password" required maxlength="128">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
            </form>

            <div class="divider">o</div>

            <a href="sso.php?next=<?php echo urlencode($next); ?>" class="btn btn-block">
                <i class="fa-brands fa-windows"></i> Usar mi sesión de Windows
            </a>
            <p class="form-help mt-1">Solo funciona desde equipos unidos al dominio GLCENTRAL.</p>
        </div>

        <div class="login-info">
            <h2>¿Problemas para ingresar?</h2>
            <ul class="list list-links mb-3">
                <li>Si olvidó su contraseña o su cuenta está bloqueada, llame a la Mesa de Ayuda de TI a la <strong>extensión 1004</strong>.</li>
                <li>Después de 5 intentos fallidos la cuenta se bloquea durante 10 minutos.</li>
                <li>La sesión se cierra sola tras 15 minutos sin actividad.</li>
            </ul>
            <h2>Aviso</h2>
            <p class="small">Este sistema es para uso exclusivo del personal de Grupo Logístico Central, S.A. Los accesos quedan registrados.</p>
        </div>
    </div>
</div>

<footer class="site-footer">
    <div class="container">
        <span>© <?php echo date('Y'); ?> Grupo Logístico Central, S.A.</span>
        <span>Proyecto final · Maestría en Informática, Universidad Mariano Gálvez</span>
    </div>
</footer>

</body>
</html>
