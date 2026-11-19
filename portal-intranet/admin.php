<?php
$pageTitle = 'Administración de TI';
$pageDesc  = 'Panel de control de infraestructura, servidores de dominio y bitácoras de auditoría.';
require_once __DIR__ . '/includes/header.php';

// Verificación estricta de rol administrativo de TI
if (!hasRole('GRP-TI-Administradores')) {
    denegarAcceso('admin.php', 'GRP-TI-Administradores');
    ?>
    <div class="panel"><div class="panel-body">
        <div class="alert alert-error">
            Área exclusiva para el equipo de administración de TI. Se requiere membresía en el grupo <code>GRP-TI-Administradores</code> de Active Directory.
        </div>
        <p class="muted small mb-2">Su usuario no posee los privilegios necesarios para administrar la infraestructura del dominio.</p>
        <a class="btn" href="index.php">Volver al inicio</a>
    </div></div>
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

<!-- Cifras resumen de infraestructura -->
<div class="figure-row mb-3">
    <div class="figure">
        <div class="label">Controlador DC01</div>
        <div class="value"><span class="dot dot-green"></span>Online</div>
    </div>
    <div class="figure">
        <div class="label">Servidor WEB01</div>
        <div class="value"><span class="dot dot-green"></span>Online</div>
    </div>
    <div class="figure">
        <div class="label">Eventos en bitácora</div>
        <div class="value"><?php echo $totalAuditoria; ?></div>
    </div>
    <div class="figure">
        <div class="label">Tickets en sistema</div>
        <div class="value"><?php echo $totalTickets; ?></div>
    </div>
</div>

<!-- Estado de servidores -->
<div class="grid grid-2 mb-3">

    <!-- Servidor DC01 -->
    <div class="panel">
        <div class="panel-head">
            <h2>DC01 (Controlador de dominio)</h2>
            <span class="badge badge-green"><span class="dot dot-green"></span>Online</span>
        </div>
        <div class="panel-body">
            <p class="small muted mono mb-2">192.168.124.128 · glcentral.local</p>
            <dl class="kv">
                <dt>Active Directory DS</dt>
                <dd class="strong">glcentral.local (Activo)</dd>
                <dt>Servidor DNS</dt>
                <dd>Puerto 53 UDP/TCP (Online)</dd>
                <dt>Servidor DHCP</dt>
                <dd>Ámbitos VLAN 20, 30, 40 (Online)</dd>
                <dt>Sincronización NTP</dt>
                <dd>pool.ntp.org (Stratum 2)</dd>
                <dt>Sistema operativo</dt>
                <dd>Windows Server 2019 Standard</dd>
            </dl>
        </div>
    </div>

    <!-- Servidor WEB01 -->
    <div class="panel">
        <div class="panel-head">
            <h2>WEB01 (Servidor web intranet)</h2>
            <span class="badge badge-green"><span class="dot dot-green"></span>Online</span>
        </div>
        <div class="panel-body">
            <p class="small muted mono mb-2">192.168.124.128 · IIS 10.0</p>
            <dl class="kv">
                <dt>Servidor web</dt>
                <dd class="strong">IIS 10.0 (HTTPS 443 / HTTP 80)</dd>
                <dt>Autenticación SSO</dt>
                <dd>IWA / Kerberos / NTLM</dd>
                <dt>Motor Back-End</dt>
                <dd>PHP 8.3 (FastCGI)</dd>
                <dt>Base de datos</dt>
                <dd>SQLite 3 (PDO) - C:\IntranetData\db</dd>
                <dt>Certificado SSL</dt>
                <dd>AD CS interna (GLCENTRAL-CA)</dd>
            </dl>
        </div>
    </div>

</div>

<!-- Bitácora y Grupos de AD -->
<div class="grid grid-main">

    <!-- Bitácora reciente de seguridad -->
    <div class="panel">
        <div class="panel-head">
            <h2>Bitácora de eventos y seguridad (audit_log)</h2>
            <a href="auditoria.php">Ver bitácora completa</a>
        </div>
        <div class="panel-body">
            <div class="code-box" style="max-height: 320px; overflow-y: auto;"><?php echo $logs ? htmlspecialchars(implode("\n", $logs)) : 'No hay registros de eventos aún.'; ?></div>
        </div>
        <div class="panel-foot flex between items-center small muted">
            <span>Registros en BD: <strong><?php echo $totalAuditoria; ?></strong> · Tickets: <strong><?php echo $totalTickets; ?></strong></span>
            <span>Cadena SHA-256 verificable</span>
        </div>
    </div>

    <!-- Grupos de seguridad AD -->
    <div class="panel">
        <div class="panel-head">
            <h2>Grupos de seguridad AD</h2>
            <span class="muted small">glcentral.local</span>
        </div>
        <div class="panel-body">
            <ul class="list">
                <li>
                    <div class="flex between items-center mb-1">
                        <strong class="mono small">GRP-TI-Administradores</strong>
                        <span class="badge badge-amber">TI</span>
                    </div>
                    <div class="small muted">Control total de servidores, OUs y portal intranet.</div>
                </li>
                <li>
                    <div class="flex between items-center mb-1">
                        <strong class="mono small">GRP-RRHH</strong>
                        <span class="badge badge-red">RRHH</span>
                    </div>
                    <div class="small muted">Gestión de talento humano, nóminas y solicitudes de personal.</div>
                </li>
                <li>
                    <div class="flex between items-center mb-1">
                        <strong class="mono small">GRP-Administracion</strong>
                        <span class="badge badge-green">Finanzas</span>
                    </div>
                    <div class="small muted">Acceso a carpetas financieras, contables y presupuesto.</div>
                </li>
                <li>
                    <div class="flex between items-center mb-1">
                        <strong class="mono small">GRP-Operaciones</strong>
                        <span class="badge badge-blue">Logística</span>
                    </div>
                    <div class="small muted">Personal operativo en bodegas, transporte y centros de distribución.</div>
                </li>
            </ul>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
