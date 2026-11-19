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
<html lang="es" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Centralizado | Grupo Logístico Central</title>
    <script src="assets/vendor/tailwind.js"></script>
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link href="assets/vendor/inter/inter.css" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="h-full flex items-center justify-center p-4">

    <div class="max-w-4xl w-full grid grid-cols-1 md:grid-cols-12 bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200">

        <div class="md:col-span-5 bg-gradient-to-br from-slate-900 via-sky-950 to-slate-900 text-white p-8 flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-xl bg-sky-500/20 border border-sky-400/30 flex items-center justify-center text-sky-400 mb-4 text-2xl shadow-inner">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-white mb-1">Grupo Logístico Central</h1>
                <p class="text-xs text-sky-300 font-semibold uppercase tracking-wider mb-6">Plataforma Intranet Segura</p>

                <div class="space-y-4 text-xs text-slate-300">
                    <div class="flex items-start gap-2.5">
                        <i class="fa-solid fa-shield-halved text-emerald-400 mt-0.5 text-sm"></i>
                        <div>
                            <strong class="text-white block">Autenticación centralizada</strong>
                            Las credenciales se validan en Active Directory (<code class="text-sky-300 bg-black/40 px-1 py-0.5 rounded">glcentral.local</code>).
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="fa-solid fa-lock text-sky-400 mt-0.5 text-sm"></i>
                        <div>
                            <strong class="text-white block">Conexión cifrada (HTTPS / TLS)</strong>
                            Certificado emitido por la entidad certificadora interna del dominio.
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="fa-solid fa-clipboard-list text-amber-400 mt-0.5 text-sm"></i>
                        <div>
                            <strong class="text-white block">Acceso auditado</strong>
                            Cada inicio de sesión y cada acción relevante queda registrado en la bitácora.
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-8 pt-4 border-t border-slate-800 text-[11px] text-slate-400 flex justify-between items-center">
                <span>Versión 3.0</span>
                <span class="inline-flex items-center text-emerald-400">
                    <i class="fa-solid fa-lock text-[10px] mr-1"></i> Sesión protegida
                </span>
            </div>
        </div>

        <div class="md:col-span-7 p-8 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Iniciar Sesión</h2>
                        <p class="text-xs text-slate-500">Ingrese con su cuenta corporativa de dominio</p>
                    </div>
                    <span class="bg-sky-50 text-sky-700 text-[11px] font-bold px-2.5 py-1 rounded-full border border-sky-100 flex items-center gap-1">
                        <i class="fa-solid fa-key text-[10px]"></i> SSO Portal
                    </span>
                </div>

                <?php if ($error): ?>
                    <div class="mb-4 bg-rose-50 border-l-4 border-rose-500 p-3 rounded text-xs text-rose-700 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-base text-rose-500"></i>
                        <span><?php echo e($error); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($info): ?>
                    <div class="mb-4 bg-sky-50 border-l-4 border-sky-500 p-3 rounded text-xs text-sky-800 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-base text-sky-500"></i>
                        <span><?php echo e($info); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="space-y-4" autocomplete="off">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="next" value="<?php echo e($next); ?>">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="username">Usuario de dominio (sAMAccountName)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm"><i class="fa-regular fa-user"></i></span>
                            <input id="username" type="text" name="username" required maxlength="64" placeholder="ejemplo: clopez"
                                   class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">
                        </div>
                        <span class="text-[11px] text-slate-400 mt-0.5 block">Dominio asignado: @glcentral.local</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="password">Contraseña</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm"><i class="fa-solid fa-lock"></i></span>
                            <input id="password" type="password" name="password" required maxlength="128" placeholder="••••••••••••"
                                   class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full bg-gradient-to-r from-sky-600 to-sky-700 hover:from-sky-700 hover:to-sky-800 text-white font-semibold py-2.5 px-4 rounded-lg text-sm shadow-md transition duration-150 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Iniciar sesión con credenciales de dominio
                    </button>
                </form>

                <div class="flex items-center gap-3 my-4">
                    <div class="flex-1 border-t border-slate-200"></div>
                    <span class="text-[11px] text-slate-400 uppercase tracking-wider">o bien</span>
                    <div class="flex-1 border-t border-slate-200"></div>
                </div>

                <a href="sso.php?next=<?php echo urlencode($next); ?>"
                   class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-lg text-sm shadow-sm transition duration-150 flex items-center justify-center gap-2">
                    <i class="fa-brands fa-windows"></i> Ingresar con mi sesión de Windows (SSO)
                </a>
                <p class="text-[11px] text-slate-400 mt-2 text-center">
                    Disponible en equipos del dominio. El servidor valida su identidad mediante Kerberos / NTLM, sin pedir la contraseña de nuevo.
                </p>
            </div>

            <div class="mt-6 text-center text-[11px] text-slate-400">
                Universidad Mariano Gálvez de Guatemala • Maestría en Informática 2026
            </div>
        </div>
    </div>

</body>
</html>
