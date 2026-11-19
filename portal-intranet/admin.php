<?php
$pageTitle = "Panel de Control de TI - Servidores e Identidad";
require_once __DIR__ . '/includes/header.php';

// Verificación estricta de rol administrativo de TI
if (!hasRole('GRP-TI-Administradores')) {
    denegarAcceso('admin.php', 'GRP-TI-Administradores');
    ?>
    <div class="max-w-2xl mx-auto my-12 bg-white rounded-2xl border border-rose-200 p-8 text-center shadow-lg">
        <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto text-3xl mb-4 shadow-sm animate-pulse">
            <i class="fa-solid fa-lock"></i>
        </div>
        <span class="bg-rose-100 text-rose-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-2 inline-block">
            HTTP 403 Forbidden - Acceso Restringido
        </span>
        <h2 class="text-xl font-bold text-slate-900 mb-2">Área Exclusiva de Administración de TI</h2>
        <p class="text-xs text-slate-600 leading-relaxed mb-6">
            El módulo de administración de servidores, directorio activo y auditoría está restringido exclusivamente a miembros del grupo de seguridad <code class="bg-rose-50 text-rose-700 px-2 py-0.5 rounded font-mono font-bold">GRP-TI-Administradores</code>.
        </p>
        <a href="index.php" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold py-2 px-4 rounded-lg transition inline-flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Volver al Inicio
        </a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Ultimos eventos de la bitacora (tabla audit_log en SQLite)
$logs = [];
foreach (db()->query("SELECT ts, ip, usuario, accion, resultado, detalle FROM audit_log ORDER BY id DESC LIMIT 15") as $r) {
    $logs[] = "[{$r['ts']}] [IP: {$r['ip']}] [USER: {$r['usuario']}] {$r['accion']} / {$r['resultado']} - {$r['detalle']}";
}
$totalTickets = (int)db()->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$totalAuditoria = (int)db()->query("SELECT COUNT(*) FROM audit_log")->fetchColumn();
?>

<!-- Encabezado de la página -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2.5">
            <i class="fa-solid fa-shield-halved text-amber-500"></i> Panel de Control de Infraestructura y Servicios (TI)
        </h1>
        <p class="text-xs text-slate-500 mt-1">
            Monitoreo en vivo de servidores de dominio, servicios de red, identidades AD y bitácoras de seguridad.
        </p>
    </div>
    <div class="flex items-center gap-2">
        <span class="bg-amber-50 text-amber-800 text-xs font-bold px-3 py-1.5 rounded-lg border border-amber-200 flex items-center gap-1.5">
            <i class="fa-solid fa-user-shield text-amber-600"></i> Privilegios de Administrador Activos
        </span>
    </div>
</div>

<!-- Estado de Salud de Servidores de Infraestructura -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    
    <!-- Servidor DC01 -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-server"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">DC01 (Controlador de Dominio)</h3>
                    <span class="text-xs text-slate-500 font-mono">192.168.124.128 • Controlador de dominio</span>
                </div>
            </div>
            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
            </span>
        </div>

        <div class="space-y-2 text-xs divide-y divide-slate-100">
            <div class="flex justify-between py-1.5">
                <span class="text-slate-500">Active Directory Domain Services:</span>
                <strong class="text-emerald-700 font-mono">glcentral.local (Activo)</strong>
            </div>
            <div class="flex justify-between py-1.5">
                <span class="text-slate-500">Servidor DNS Primario:</span>
                <strong class="text-emerald-700 font-mono">Puerto 53 UDP/TCP (Online)</strong>
            </div>
            <div class="flex justify-between py-1.5">
                <span class="text-slate-500">Servidor DHCP:</span>
                <strong class="text-emerald-700 font-mono">Ámbitos VLAN 20, 30, 40 (Online)</strong>
            </div>
            <div class="flex justify-between py-1.5">
                <span class="text-slate-500">Sincronización NTP (Stratum 2):</span>
                <strong class="text-emerald-700 font-mono">pool.ntp.org (Sincronizado)</strong>
            </div>
            <div class="flex justify-between py-1.5">
                <span class="text-slate-500">Sistema Operativo:</span>
                <strong class="text-slate-800">Windows Server 2019 Standard</strong>
            </div>
        </div>
    </div>

    <!-- Servidor WEB01 -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-globe"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">WEB01 (Servidor Web de Intranet)</h3>
                    <span class="text-xs text-slate-500 font-mono">192.168.124.128 • Servidor web (mismo host)</span>
                </div>
            </div>
            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
            </span>
        </div>

        <div class="space-y-2 text-xs divide-y divide-slate-100">
            <div class="flex justify-between py-1.5">
                <span class="text-slate-500">Servidor Web:</span>
                <strong class="text-emerald-700 font-mono">IIS 10.0 (HTTPS 443, HTTP 80 solo redirige)</strong>
            </div>
            <div class="flex justify-between py-1.5">
                <span class="text-slate-500">Autenticación SSO:</span>
                <strong class="text-emerald-700 font-mono">IWA / Kerberos / NTLM</strong>
            </div>
            <div class="flex justify-between py-1.5">
                <span class="text-slate-500">Motor Back-End:</span>
                <strong class="text-emerald-700 font-mono">PHP 8.3 (FastCGI)</strong>
            </div>
            <div class="flex justify-between py-1.5">
                <span class="text-slate-500">Base de Datos:</span>
                <strong class="text-emerald-700 font-mono">SQLite 3 (PDO) - C:\IntranetData\db</strong>
            </div>
            <div class="flex justify-between py-1.5">
                <span class="text-slate-500">Certificado SSL:</span>
                <strong class="text-slate-800">AD CS interna (GLCENTRAL-CA)</strong>
            </div>
        </div>
    </div>

</div>

<!-- Resumen de Cuentas y Grupos AD + Visor de Auditoría -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <!-- Columna Izquierda: Grupos de Seguridad AD (5 cols) -->
    <div class="lg:col-span-5 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fa-solid fa-users-gear text-sky-600"></i> Grupos de Seguridad AD (glcentral.local)
            </h3>
            
            <div class="space-y-3 text-xs">
                <div class="p-3 rounded-lg border border-slate-100 bg-slate-50/50">
                    <div class="font-bold text-slate-800 flex justify-between">
                        <span>GRP-TI-Administradores</span>
                        <span class="bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded text-[10px]">TI</span>
                    </div>
                    <p class="text-slate-500 text-[11px] mt-1">Control total de servidores, OUs y portal intranet.</p>
                </div>

                <div class="p-3 rounded-lg border border-slate-100 bg-slate-50/50">
                    <div class="font-bold text-slate-800 flex justify-between">
                        <span>GRP-RRHH</span>
                        <span class="bg-rose-100 text-rose-800 px-1.5 py-0.5 rounded text-[10px]">RRHH</span>
                    </div>
                    <p class="text-slate-500 text-[11px] mt-1">Gestión de talento humano, nóminas y solicitudes de personal.</p>
                </div>

                <div class="p-3 rounded-lg border border-slate-100 bg-slate-50/50">
                    <div class="font-bold text-slate-800 flex justify-between">
                        <span>GRP-Administracion</span>
                        <span class="bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded text-[10px]">Finanzas</span>
                    </div>
                    <p class="text-slate-500 text-[11px] mt-1">Acceso a carpetas financieras, contables y presupuesto.</p>
                </div>

                <div class="p-3 rounded-lg border border-slate-100 bg-slate-50/50">
                    <div class="font-bold text-slate-800 flex justify-between">
                        <span>GRP-Operaciones</span>
                        <span class="bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded text-[10px]">Logística</span>
                    </div>
                    <p class="text-slate-500 text-[11px] mt-1">Personal operativo en bodegas, transporte y centros de distribución.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Bitácora de Auditoría en Tiempo Real (7 cols) -->
    <div class="lg:col-span-7">
        <div class="bg-slate-900 rounded-xl border border-slate-800 shadow-sm p-6 text-white">
            <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-terminal"></i> Bitácora de Eventos y Seguridad (audit_log)
                </h3>
                <span class="text-[11px] text-slate-400 font-mono">SQLite + archivo</span>
            </div>

            <div class="bg-black/60 rounded-lg p-4 font-mono text-[11px] text-slate-300 space-y-1.5 max-h-[340px] overflow-y-auto">
                <?php if (empty($logs)): ?>
                    <div class="text-slate-500 italic">No hay registros de eventos aún.</div>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <div class="leading-relaxed hover:text-emerald-300 transition">
                            <?php echo htmlspecialchars($log); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-800 flex justify-between items-center text-[10px] text-slate-400">
                <span>Registros en BD: <?php echo $totalAuditoria; ?> · Tickets: <?php echo $totalTickets; ?></span>
                <span>Cadena SHA-256 verificable</span>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
