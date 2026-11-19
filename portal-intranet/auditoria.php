<?php
$pageTitle = "Bitácora de Auditoría";
require_once __DIR__ . '/includes/header.php';

if (!can('auditoria.ver')) {
    denegarAcceso('auditoria.php', 'auditoria.ver (GRP-TI-Administradores)');
    ?>
    <div class="max-w-2xl mx-auto my-12 bg-white rounded-2xl border border-rose-200 p-8 text-center shadow-lg">
        <span class="bg-rose-100 text-rose-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-3 inline-block">
            HTTP 403 Forbidden - Acceso Restringido
        </span>
        <h2 class="text-xl font-bold text-slate-900 mb-2">Bitácora exclusiva del equipo de TI</h2>
        <p class="text-xs text-slate-600 leading-relaxed mb-6">
            La consulta de auditoría requiere pertenecer al grupo <code class="bg-rose-50 text-rose-700 px-2 py-0.5 rounded font-mono font-bold">GRP-TI-Administradores</code>.
            El intento quedó registrado.
        </p>
        <a href="index.php" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold py-2 px-4 rounded-lg inline-flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Volver al Inicio
        </a>
    </div>
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
    return ['OK' => 'bg-emerald-100 text-emerald-800', 'FALLO' => 'bg-rose-100 text-rose-800', 'DENEGADO' => 'bg-rose-100 text-rose-800',
            'BLOQUEADO' => 'bg-orange-100 text-orange-800', 'ERROR' => 'bg-orange-100 text-orange-800',
            'EXPIRADA' => 'bg-slate-200 text-slate-700', 'INFO' => 'bg-sky-100 text-sky-800'][$r] ?? 'bg-slate-100 text-slate-700';
}
$qs = http_build_query(array_filter(['usuario' => $fUsuario, 'resultado' => $fResultado, 'accion' => $fAccion, 'desde' => $fDesde]));
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2.5">
            <i class="fa-solid fa-clipboard-list text-sky-600"></i> Bitácora de Auditoría
        </h1>
        <p class="text-xs text-slate-500 mt-1">
            Registro de inicios de sesión, accesos denegados y operaciones sobre la Mesa de Servicios. Cada evento se encadena con un hash SHA-256.
        </p>
    </div>
    <div class="flex gap-2">
        <a href="auditoria.php?verificar=1&<?php echo e($qs); ?>" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold px-3 py-2 rounded-lg flex items-center gap-1.5">
            <i class="fa-solid fa-shield-halved text-emerald-600"></i> Verificar integridad
        </a>
        <?php if (can('auditoria.exportar')): ?>
        <a href="auditoria.php?export=1&<?php echo e($qs); ?>" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-3 py-2 rounded-lg flex items-center gap-1.5">
            <i class="fa-solid fa-file-csv"></i> Exportar CSV
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($integridad): ?>
    <div class="mb-5 p-3 rounded-xl text-xs flex items-center gap-2 <?php echo $integridad[0] ? 'bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800' : 'bg-rose-50 border-l-4 border-rose-500 text-rose-800'; ?>">
        <i class="fa-solid <?php echo $integridad[0] ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
        <?php if ($integridad[0]): ?>
            Verificación correcta: la cadena de <strong><?php echo (int)$integridad[1]; ?></strong> registros está íntegra (ningún evento fue alterado ni eliminado).
        <?php else: ?>
            <strong>Alerta:</strong> la cadena de hashes se rompe en el registro #<?php echo (int)$integridad[2]; ?>. Existe una alteración en la bitácora.
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <?php foreach ([
        ['Eventos (24 h)', $s['eventos'], 'fa-list-check', 'bg-sky-50 text-sky-600'],
        ['Inicios con contraseña', $s['login_ok'], 'fa-key', 'bg-emerald-50 text-emerald-600'],
        ['Inicios por SSO', $s['sso_ok'], 'fa-brands fa-windows', 'bg-indigo-50 text-indigo-600'],
        ['Fallos de acceso', $s['login_fallo'], 'fa-user-lock', 'bg-orange-50 text-orange-600'],
        ['Accesos denegados', $s['denegados'], 'fa-ban', 'bg-rose-50 text-rose-600'],
    ] as $c): ?>
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl <?php echo $c[3]; ?> flex items-center justify-center"><i class="<?php echo strpos($c[2], 'fa-brands') === 0 ? $c[2] : 'fa-solid ' . $c[2]; ?>"></i></div>
            <div>
                <span class="text-[10px] font-semibold text-slate-500 uppercase"><?php echo e($c[0]); ?></span>
                <div class="text-xl font-bold text-slate-900"><?php echo (int)$c[1]; ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <form method="GET" action="auditoria.php" class="p-4 border-b border-slate-200 bg-slate-50 grid grid-cols-2 md:grid-cols-12 gap-3 text-xs">
        <input name="usuario" value="<?php echo e($fUsuario); ?>" maxlength="32" placeholder="Usuario" class="md:col-span-2 py-2 px-3 border border-slate-300 rounded-lg">
        <input name="accion" value="<?php echo e($fAccion); ?>" maxlength="30" placeholder="Acción (LOGIN, SSO...)" class="md:col-span-3 py-2 px-3 border border-slate-300 rounded-lg">
        <select name="resultado" class="md:col-span-2 py-2 px-2 border border-slate-300 rounded-lg">
            <option value="">Todos los resultados</option>
            <?php foreach (['OK', 'FALLO', 'DENEGADO', 'BLOQUEADO', 'ERROR', 'EXPIRADA'] as $r): ?>
                <option <?php echo $fResultado === $r ? 'selected' : ''; ?>><?php echo $r; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="desde" value="<?php echo e($fDesde); ?>" class="md:col-span-3 py-2 px-3 border border-slate-300 rounded-lg">
        <button class="md:col-span-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2 rounded-lg">Filtrar</button>
    </form>

    <div class="overflow-x-auto">
    <table class="w-full text-[11px]">
        <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider">
            <tr>
                <th class="text-left px-3 py-2.5">#</th>
                <th class="text-left px-3 py-2.5">Fecha</th>
                <th class="text-left px-3 py-2.5">Usuario</th>
                <th class="text-left px-3 py-2.5">IP</th>
                <th class="text-left px-3 py-2.5">Acción</th>
                <th class="text-left px-3 py-2.5">Resultado</th>
                <th class="text-left px-3 py-2.5">Detalle</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        <?php foreach ($filas as $r): ?>
            <tr class="hover:bg-slate-50 align-top">
                <td class="px-3 py-2 font-mono text-slate-400"><?php echo (int)$r['id']; ?></td>
                <td class="px-3 py-2 whitespace-nowrap text-slate-600"><?php echo e($r['ts']); ?></td>
                <td class="px-3 py-2 font-semibold text-slate-800"><?php echo e($r['usuario']); ?></td>
                <td class="px-3 py-2 font-mono text-slate-500"><?php echo e($r['ip']); ?></td>
                <td class="px-3 py-2 font-mono font-bold text-sky-700"><?php echo e($r['accion']); ?></td>
                <td class="px-3 py-2"><span class="<?php echo colorResultado($r['resultado']); ?> text-[10px] font-bold px-2 py-0.5 rounded-full"><?php echo e($r['resultado']); ?></span></td>
                <td class="px-3 py-2 text-slate-600 max-w-md"><?php echo e($r['detalle']); ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$filas): ?>
            <tr><td colspan="7" class="p-10 text-center text-slate-400">Sin eventos para los filtros seleccionados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <div class="p-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between text-xs text-slate-500">
        <span><?php echo $totalFilas; ?> eventos · página <?php echo $pagina; ?> de <?php echo $paginas; ?></span>
        <span class="flex gap-2">
            <?php if ($pagina > 1): ?><a class="px-3 py-1 bg-white border rounded" href="auditoria.php?<?php echo e($qs); ?>&p=<?php echo $pagina - 1; ?>">« Anterior</a><?php endif; ?>
            <?php if ($pagina < $paginas): ?><a class="px-3 py-1 bg-white border rounded" href="auditoria.php?<?php echo e($qs); ?>&p=<?php echo $pagina + 1; ?>">Siguiente »</a><?php endif; ?>
        </span>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
