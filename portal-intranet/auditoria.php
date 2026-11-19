<?php
$pageTitle = 'Bitácora de auditoría';
$pageDesc  = 'Registro inmutable de eventos de seguridad y accesos al sistema con encadenamiento SHA-256.';
require_once __DIR__ . '/includes/header.php';

if (!can('auditoria.ver')) {
    denegarAcceso('auditoria.php', 'auditoria.ver (GRP-TI-Administradores)');
    ?>
    <div class="panel"><div class="panel-body">
        <div class="alert alert-error">
            No tiene permiso para consultar la bitácora de auditoría. Se requiere membresía en el grupo <code>GRP-TI-Administradores</code> de Active Directory.
        </div>
        <p class="muted small mb-2">Este módulo está restringido exclusivamente a los auditores y administradores de TI.</p>
        <a class="btn" href="index.php">Volver al inicio</a>
    </div></div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pdo = db();

/* ------------------------------ Filtros ------------------------------ */
$fUsuario   = trim((string)($_GET['usuario'] ?? ''));
$fResultado = (string)($_GET['resultado'] ?? '');
$fAccion    = trim((string)($_GET['accion'] ?? ''));
$fDesde     = (string)($_GET['desde'] ?? '');
$pagina     = max(1, (int)($_GET['p'] ?? 1));
$porPagina  = 25;

$where = ['1 = 1'];
$params = [];
if ($fUsuario !== '' && preg_match('/^[A-Za-z0-9._-]{1,32}$/', $fUsuario)) { $where[] = 'usuario = :u'; $params[':u'] = strtolower($fUsuario); }
if (in_array($fResultado, ['OK', 'FALLO', 'DENEGADO', 'BLOQUEADO', 'ERROR', 'INFO', 'EXPIRADA'], true)) { $where[] = 'resultado = :r'; $params[':r'] = $fResultado; }
if ($fAccion !== '' && preg_match('/^[A-Z_]{1,30}$/', strtoupper($fAccion))) { $where[] = 'accion = :a'; $params[':a'] = strtoupper($fAccion); }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fDesde)) { $where[] = 'ts >= :d'; $params[':d'] = $fDesde . ' 00:00:00'; }
$whereSql = implode(' AND ', $where);

/* ------------------------------ Exportacion CSV ------------------------------ */
if (isset($_GET['export']) && can('auditoria.exportar')) {
    audit('AUDITORIA_EXPORT', 'OK', 'Exportacion de la bitacora a CSV', 'auditoria.php');
    $st = $pdo->prepare("SELECT id, ts, usuario, ip, accion, resultado, detalle, recurso FROM audit_log WHERE $whereSql ORDER BY id DESC LIMIT 5000");
    $st->execute($params);
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="auditoria_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['ID', 'Fecha', 'Usuario', 'IP', 'Accion', 'Resultado', 'Detalle', 'Recurso']);
    foreach ($st as $r) {
        // Se antepone una comilla a celdas que inician con = + - @ para evitar inyeccion de formulas en Excel
        foreach ($r as $k => $v) { if (is_string($v) && preg_match('/^[=+\-@]/', $v)) { $r[$k] = "'" . $v; } }
        fputcsv($out, $r);
    }
    exit;
}

/* ------------------------------ Verificacion de integridad ------------------------------ */
$integridad = null;
if (isset($_GET['verificar'])) {
    $integridad = auditVerify();
    audit('AUDITORIA_VERIFICACION', $integridad[0] ? 'OK' : 'FALLO',
        $integridad[0] ? "Cadena integra ({$integridad[1]} registros)" : "Cadena rota en el registro #{$integridad[2]}", 'auditoria.php');
}

/* ------------------------------ Datos ------------------------------ */
$total = $pdo->prepare("SELECT COUNT(*) FROM audit_log WHERE $whereSql");
$total->execute($params);
$totalFilas = (int)$total->fetchColumn();
$paginas = max(1, (int)ceil($totalFilas / $porPagina));
$pagina  = min($pagina, $paginas);

$st = $pdo->prepare("SELECT * FROM audit_log WHERE $whereSql ORDER BY id DESC LIMIT $porPagina OFFSET " . (($pagina - 1) * $porPagina));
$st->execute($params);
$filas = $st->fetchAll();

$h24 = date('Y-m-d H:i:s', time() - 86400);
$stats = $pdo->prepare("SELECT
    COUNT(*) AS eventos,
    SUM(accion = 'LOGIN'  AND resultado = 'OK')    AS login_ok,
    SUM(accion = 'LOGIN'  AND resultado IN ('FALLO','BLOQUEADO')) AS login_fallo,
    SUM(accion = 'SSO'    AND resultado = 'OK')    AS sso_ok,
    SUM(resultado = 'DENEGADO') AS denegados
    FROM audit_log WHERE ts >= ?");
$stats->execute([$h24]);
$s = $stats->fetch();

function colorResultado(string $r): string
{
    return match ($r) {
        'OK'                         => 'badge-green',
        'FALLO', 'DENEGADO', 'ERROR' => 'badge-red',
        'BLOQUEADO'                  => 'badge-orange',
        default                      => 'badge-gray',
    };
}
$qs = http_build_query(array_filter(['usuario' => $fUsuario, 'resultado' => $fResultado, 'accion' => $fAccion, 'desde' => $fDesde]));
?>

<div class="toolbar">
    <div class="flex items-center gap-2">
        <span class="muted small"><?php echo $totalFilas; ?> eventos registrados</span>
    </div>
    <div class="flex gap-1">
        <a href="auditoria.php?verificar=1&<?php echo e($qs); ?>" class="btn btn-sm">
            <i class="fa-solid fa-shield-halved text-green"></i> Verificar integridad SHA-256
        </a>
        <?php if (can('auditoria.exportar')): ?>
        <a href="auditoria.php?export=1&<?php echo e($qs); ?>" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-file-csv"></i> Exportar CSV
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($integridad): ?>
    <div class="alert <?php echo $integridad[0] ? 'alert-success' : 'alert-error'; ?>">
        <?php if ($integridad[0]): ?>
            <strong>Verificación satisfactoria:</strong> la cadena de <strong><?php echo (int)$integridad[1]; ?></strong> registros es íntegra y no ha sido alterada.
        <?php else: ?>
            <strong>Alerta de integridad:</strong> la cadena de hashes se rompe en el registro #<?php echo (int)$integridad[2]; ?>. Posible alteración en la base de datos.
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="figure-row mb-3">
    <div class="figure">
        <div class="label">Eventos (24 h)</div>
        <div class="value"><?php echo (int)$s['eventos']; ?></div>
    </div>
    <div class="figure">
        <div class="label">Inicios con contraseña</div>
        <div class="value"><?php echo (int)$s['login_ok']; ?></div>
    </div>
    <div class="figure">
        <div class="label">Inicios por SSO</div>
        <div class="value"><?php echo (int)$s['sso_ok']; ?></div>
    </div>
    <div class="figure">
        <div class="label">Fallos de acceso</div>
        <div class="value"><?php echo (int)$s['login_fallo']; ?></div>
    </div>
    <div class="figure">
        <div class="label">Accesos denegados</div>
        <div class="value"><?php echo (int)$s['denegados']; ?></div>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <form method="GET" action="auditoria.php" class="form form-inline">
            <div class="form-row">
                <input name="usuario" value="<?php echo e($fUsuario); ?>" maxlength="32" placeholder="Usuario (ej. clopez)">
            </div>
            <div class="form-row">
                <input name="accion" value="<?php echo e($fAccion); ?>" maxlength="30" placeholder="Acción (LOGIN, SSO...)">
            </div>
            <div class="form-row">
                <select name="resultado">
                    <option value="">Todos los resultados</option>
                    <?php foreach (['OK', 'FALLO', 'DENEGADO', 'BLOQUEADO', 'ERROR', 'EXPIRADA'] as $r): ?>
                        <option value="<?php echo $r; ?>" <?php echo $fResultado === $r ? 'selected' : ''; ?>><?php echo $r; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <input type="date" name="desde" value="<?php echo e($fDesde); ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            <?php if ($fUsuario !== '' || $fResultado !== '' || $fAccion !== '' || $fDesde !== ''): ?>
                <a href="auditoria.php" class="btn btn-sm">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-wrap">
        <table class="table table-compact">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha y hora</th>
                    <th>Usuario</th>
                    <th>Dirección IP</th>
                    <th>Acción</th>
                    <th>Resultado</th>
                    <th>Detalle del evento</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($filas as $r): ?>
                <tr>
                    <td class="mono small muted"><?php echo (int)$r['id']; ?></td>
                    <td class="nowrap small muted"><?php echo e($r['ts']); ?></td>
                    <td class="nowrap strong"><?php echo e($r['usuario']); ?></td>
                    <td class="mono small"><?php echo e($r['ip']); ?></td>
                    <td class="mono strong nowrap"><?php echo e($r['accion']); ?></td>
                    <td class="nowrap"><span class="badge <?php echo colorResultado($r['resultado']); ?>"><?php echo e($r['resultado']); ?></span></td>
                    <td class="small"><?php echo e($r['detalle']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$filas): ?>
                <tr><td colspan="7" class="empty">Sin eventos para los filtros seleccionados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="panel-foot flex between items-center">
        <span><?php echo $totalFilas; ?> eventos encontrados · página <?php echo $pagina; ?> de <?php echo $paginas; ?></span>
        <nav class="pager">
            <?php if ($pagina > 1): ?>
                <a href="auditoria.php?<?php echo e($qs); ?>&p=<?php echo $pagina - 1; ?>">« Anterior</a>
            <?php endif; ?>
            <span class="current"><?php echo $pagina; ?></span>
            <?php if ($pagina < $paginas): ?>
                <a href="auditoria.php?<?php echo e($qs); ?>&p=<?php echo $pagina + 1; ?>">Siguiente »</a>
            <?php endif; ?>
        </nav>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
