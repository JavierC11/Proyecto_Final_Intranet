<?php
require_once __DIR__ . '/auth.php';
requireAuth();
$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Portal Corporativo'; ?> | Grupo Logístico Central</title>
    <!-- Tailwind CSS (copia local, funciona sin Internet) -->
    <script src="assets/vendor/tailwind.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        corp: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            800: '#0c4a6e',
                            900: '#082f49',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Iconos Font Awesome (copia local) -->
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <!-- Tipografia Inter (copia local) -->
    <link href="assets/vendor/inter/inter.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col text-slate-800">

    <!-- Top Corporate Bar (Identificación de Dominio y Seguridad) -->
    <header class="bg-corp-900 text-white text-xs py-1.5 px-4 sm:px-8 border-b border-corp-800">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center text-emerald-400 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse mr-1.5"></span>
                    RED PRIVADA SEGURA (VLAN INTERNA)
                </span>
                <span class="text-slate-400">|</span>
                <span class="text-slate-300">Dominio: <strong class="text-white">glcentral.local</strong></span>
                <?php if (cfg('identity_backend') === 'local'): ?>
                <span class="bg-amber-600/80 text-amber-100 px-2 py-0.5 rounded font-mono text-[10px]"><i class="fa-solid fa-laptop mr-1"></i>Entorno Local macOS</span>
                <?php endif; ?>
                <span class="text-slate-400">|</span>
                <span class="text-slate-300">SSO: <span class="bg-corp-800 text-sky-200 px-2 py-0.5 rounded font-mono text-[10px]"><?php echo htmlspecialchars($currentUser['auth_method']); ?></span></span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-slate-300"><i class="fa-regular fa-clock mr-1 text-sky-400"></i> NTP Sincronizado (DC01)</span>
                <span class="text-slate-300"><i class="fa-solid fa-location-dot mr-1 text-amber-400"></i> <?php echo htmlspecialchars($currentUser['sede']); ?></span>
            </div>
        </div>
    </header>

    <!-- Main Navigation Bar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-tr from-corp-800 to-sky-500 flex items-center justify-center text-white shadow-md">
                        <i class="fa-solid fa-truck-fast text-lg"></i>
                    </div>
                    <div>
                        <a href="index.php" class="text-lg font-bold text-corp-900 tracking-tight leading-none block">
                            GRUPO LOGÍSTICO CENTRAL
                        </a>
                        <span class="text-[11px] font-semibold text-sky-600 tracking-widest uppercase">Intranet Corporativa</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-1 lg:space-x-2">
                    <a href="index.php" class="<?php echo $currentPage == 'index.php' ? 'text-sky-700 bg-sky-50 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'; ?> px-3 py-2 rounded-md text-sm transition flex items-center gap-1.5">
                        <i class="fa-solid fa-house text-xs"></i> Inicio
                    </a>
                    <a href="directorio.php" class="<?php echo $currentPage == 'directorio.php' ? 'text-sky-700 bg-sky-50 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'; ?> px-3 py-2 rounded-md text-sm transition flex items-center gap-1.5">
                        <i class="fa-solid fa-address-book text-xs"></i> Directorio
                    </a>
                    <a href="documentos.php" class="<?php echo $currentPage == 'documentos.php' ? 'text-sky-700 bg-sky-50 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'; ?> px-3 py-2 rounded-md text-sm transition flex items-center gap-1.5">
                        <i class="fa-solid fa-folder-tree text-xs"></i> Repositorio (RBAC)
                    </a>
                    <a href="mesa-servicios.php" class="<?php echo in_array($currentPage, ['mesa-servicios.php', 'ticket.php']) ? 'text-sky-700 bg-sky-50 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'; ?> px-3 py-2 rounded-md text-sm transition flex items-center gap-1.5">
                        <i class="fa-solid fa-headset text-xs"></i> Mesa de Servicios
                    </a>
                    <?php if (can('auditoria.ver')): ?>
                    <a href="auditoria.php" class="<?php echo $currentPage == 'auditoria.php' ? 'text-sky-700 bg-sky-50 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'; ?> px-3 py-2 rounded-md text-sm transition flex items-center gap-1.5">
                        <i class="fa-solid fa-clipboard-list text-xs"></i> Auditoría
                    </a>
                    <?php endif; ?>
                    <?php if (hasRole('GRP-TI-Administradores')): ?>
                    <a href="admin.php" class="<?php echo $currentPage == 'admin.php' ? 'text-amber-700 bg-amber-50 font-semibold' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50'; ?> px-3 py-2 rounded-md text-sm transition flex items-center gap-1.5">
                        <i class="fa-solid fa-shield-halved text-xs text-amber-500"></i> Panel TI (DC01)
                    </a>
                    <?php endif; ?>
                </div>

                <!-- User Profile & SSO Session Control -->
                <div class="flex items-center space-x-3">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-bold text-slate-800 leading-tight">
                            <?php echo htmlspecialchars($currentUser['nombre']); ?>
                        </div>
                        <div class="text-[11px] text-slate-500 flex items-center justify-end gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <?php echo htmlspecialchars($currentUser['departamento']); ?>
                        </div>
                    </div>

                    <!-- Role Badge Popover / Indicator -->
                    <div class="relative group">
                        <img class="h-10 w-10 rounded-full border-2 border-corp-500 object-cover shadow-sm cursor-pointer" 
                             src="<?php echo htmlspecialchars($currentUser['foto']); ?>" 
                             alt="Avatar">
                        
                        <!-- Tooltip de credenciales SSO -->
                        <div class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-xl border border-slate-200 p-3 hidden group-hover:block z-50">
                            <div class="text-xs font-bold text-slate-900 border-b pb-1.5 mb-2 flex items-center justify-between">
                                <span>Ficha de Identidad AD</span>
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] px-1.5 py-0.5 rounded font-bold">Activo</span>
                            </div>
                            <div class="text-xs text-slate-600 space-y-1 mb-2">
                                <p><strong>Cuenta:</strong> <span class="font-mono"><?php echo htmlspecialchars($currentUser['username']); ?>@glcentral.local</span></p>
                                <p><strong>Cargo:</strong> <?php echo htmlspecialchars($currentUser['cargo']); ?></p>
                                <p><strong>Extensión:</strong> <?php echo htmlspecialchars($currentUser['extension']); ?></p>
                                <p><strong>Rol:</strong> <?php echo htmlspecialchars(implode(', ', $currentUser['roles'])); ?></p>
                            </div>
                            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Membresía en Grupos (AD):</div>
                            <div class="flex flex-wrap gap-1 mb-3">
                                <?php foreach ($currentUser['grupos'] as $grp): ?>
                                    <span class="bg-sky-100 text-sky-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                        <?php echo htmlspecialchars($grp); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <div class="border-t pt-2 flex justify-between items-center text-xs">
                                <a href="perfil.php" class="text-sky-600 hover:text-sky-800 font-medium">
                                    <i class="fa-regular fa-id-card mr-1"></i> Mi perfil y permisos
                                </a>
                                <a href="logout.php" class="text-rose-600 hover:text-rose-800 font-medium">
                                    <i class="fa-solid fa-power-off mr-1"></i> Cerrar Sesión
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="logout.php" class="text-slate-400 hover:text-rose-600 p-2 transition" title="Cerrar sesión SSO">
                        <i class="fa-solid fa-right-from-bracket text-lg"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
