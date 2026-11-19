<?php
$pageTitle = 'Mesa de servicios';
$pageDesc  = 'Reporte y seguimiento de incidentes y requerimientos técnicos internos.';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/mesa-helpers.php';

$u   = $currentUser;
$pdo = db();
$error = null;

/* ------------------------------ Alcance de datos ------------------------------ */
// El filtro se aplica en la consulta SQL: un usuario nunca recibe filas fuera de su alcance.
if (can('tickets.ver_todos')) {
    $alcanceSql = '1 = 1';
    $alcanceParams = [];
    $alcanceTxt = 'Todos los departamentos';
} elseif (can('tickets.ver_depto')) {
    $alcanceSql = '(departamento = :depto OR solicitante = :usr)';
    $alcanceParams = [':depto' => $u['departamento'], ':usr' => $u['username']];
    $alcanceTxt = 'Departamento: ' . $u['departamento'];
} else {
    $alcanceSql = 'solicitante = :usr';
    $alcanceParams = [':usr' => $u['username']];
    $alcanceTxt = 'Solo mis tickets';
}

/* ------------------------------ Crear ticket ------------------------------ */
$form = ['titulo' => '', 'descripcion' => '', 'categoria' => TICKET_CATEGORIAS[0], 'prioridad' => 'Media'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    csrfVerify();
    if (!can('tickets.crear')) {
        denegarAcceso('mesa-servicios.php (crear)', 'tickets.crear');
        exit('403');
    }
    $form['titulo']      = trim((string)($_POST['titulo'] ?? ''));
    $form['descripcion'] = trim((string)($_POST['descripcion'] ?? ''));
    $form['categoria']   = (string)($_POST['categoria'] ?? '');
    $form['prioridad']   = (string)($_POST['prioridad'] ?? '');

    $lt = mb_strlen($form['titulo']);
    $ld = mb_strlen($form['descripcion']);
    if ($lt < 5 || $lt > 120) {
        $error = 'El asunto debe tener entre 5 y 120 caracteres.';
    } elseif ($ld < 10 || $ld > 2000) {
        $error = 'La descripción debe tener entre 10 y 2000 caracteres.';
    } elseif (!in_array($form['categoria'], TICKET_CATEGORIAS, true) || !in_array($form['prioridad'], TICKET_PRIORIDADES, true)) {
        $error = 'La categoría o la prioridad seleccionada no es válida.';
    } else {
        $ahora = date('Y-m-d H:i:s');
        $st = $pdo->prepare('INSERT INTO tickets (titulo, descripcion, categoria, prioridad, estado, solicitante, solicitante_nombre, departamento, creado_en, actualizado_en)
                             VALUES (?,?,?,?,?,?,?,?,?,?)');
        $st->execute([$form['titulo'], $form['descripcion'], $form['categoria'], $form['prioridad'], 'Abierto',
                      $u['username'], $u['nombre'], $u['departamento'], $ahora, $ahora]);
        $id    = (int)$pdo->lastInsertId();
        $folio = 'MS-' . date('Y') . '-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT);
        $pdo->prepare('UPDATE tickets SET folio = ? WHERE id = ?')->execute([$folio, $id]);
        audit('TICKET_CREADO', 'OK', "Ticket $folio ({$form['categoria']}, prioridad {$form['prioridad']})", "ticket:$id");
        $_SESSION['flash'] = "Ticket $folio registrado correctamente.";
        redirigir('ticket.php?id=' . $id);
    }
}

/* ------------------------------ Consulta y filtros ------------------------------ */
$fEstado = (string)($_GET['estado'] ?? 'todos');
$q       = trim((string)($_GET['q'] ?? ''));
$sql     = "SELECT * FROM tickets WHERE $alcanceSql";
$params  = $alcanceParams;
if (in_array($fEstado, TICKET_ESTADOS, true)) {
    $sql .= ' AND estado = :estado';
    $params[':estado'] = $fEstado;
}
if ($q !== '') {
    // Los comodines escritos por el usuario se neutralizan para que se busquen como texto literal.
    $sql .= " AND (titulo LIKE :q ESCAPE '\\' OR folio LIKE :q ESCAPE '\\' OR solicitante_nombre LIKE :q ESCAPE '\\')";
    $params[':q'] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($q, 0, 60)) . '%';
}
$sql .= ' ORDER BY id DESC LIMIT 100';
$st = $pdo->prepare($sql);
$st->execute($params);
$tickets = $st->fetchAll();

$kpi = $pdo->prepare("SELECT
        SUM(estado = 'Abierto')    AS abiertos,
        SUM(estado = 'En proceso') AS proceso,
        SUM(estado IN ('Resuelto','Cerrado')) AS resueltos,
        SUM(prioridad = 'Crítica' AND estado NOT IN ('Resuelto','Cerrado')) AS criticos
    FROM tickets WHERE $alcanceSql");
$kpi->execute($alcanceParams);
$k = $kpi->fetch() ?: [];

$badgeEstado = [
    'Abierto'    => 'badge-blue',
    'En proceso' => 'badge-amber',
    'En espera'  => 'badge-gray',
    'Resuelto'   => 'badge-green',
    'Cerrado'    => 'badge-gray'
];
$badgePrioridad = [
    'Baja'    => 'badge-gray',
    'Media'   => 'badge-blue',
    'Alta'    => 'badge-orange',
    'Crítica' => 'badge-red'
];
?>

<div class="figure-row mb-3">
    <div class="figure">
        <div class="label">Abiertos</div>
        <div class="value"><?php echo (int)($k['abiertos'] ?? 0); ?></div>
    </div>
    <div class="figure">
        <div class="label">En proceso</div>
        <div class="value"><?php echo (int)($k['proceso'] ?? 0); ?></div>
    </div>
    <div class="figure">
        <div class="label">Resueltos / cerrados</div>
        <div class="value"><?php echo (int)($k['resueltos'] ?? 0); ?></div>
    </div>
    <div class="figure">
        <div class="label">Críticos activos</div>
        <div class="value"><?php echo (int)($k['criticos'] ?? 0); ?></div>
    </div>
</div>

<div class="grid grid-side">

    <!-- Formulario de nuevo ticket -->
    <div>
        <div class="panel" id="nuevo">
            <div class="panel-head">
                <h2>Nueva solicitud</h2>
            </div>
            <div class="panel-body">
                <p class="small muted mb-2">Se registrará a nombre de <strong><?php echo e($u['nombre']); ?></strong>.</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo e($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="mesa-servicios.php" class="form">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="accion" value="crear">
                    <div class="form-row">
                        <label for="titulo">Asunto</label>
                        <input id="titulo" name="titulo" type="text" required maxlength="120" value="<?php echo e($form['titulo']); ?>"
                               placeholder="Ej. Problema con impresora de Finanzas">
                    </div>
                    <div class="grid grid-2">
                        <div class="form-row">
                            <label for="categoria">Categoría</label>
                            <select id="categoria" name="categoria">
                                <?php foreach (TICKET_CATEGORIAS as $c): ?>
                                    <option value="<?php echo e($c); ?>" <?php echo $form['categoria'] === $c ? 'selected' : ''; ?>><?php echo e($c); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-row">
                            <label for="prioridad">Prioridad</label>
                            <select id="prioridad" name="prioridad">
                                <?php foreach (TICKET_PRIORIDADES as $p): ?>
                                    <option value="<?php echo e($p); ?>" <?php echo $form['prioridad'] === $p ? 'selected' : ''; ?>><?php echo e($p); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4" required maxlength="2000"
                                  placeholder="Detalle la solicitud o el problema con el mayor detalle posible..."><?php echo e($form['descripcion']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Registrar solicitud</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Listado -->
    <div>
        <div class="panel">
            <div class="panel-head">
                <h2>Solicitudes registradas</h2>
                <span class="muted small"><?php echo e($alcanceTxt); ?></span>
            </div>
            <div class="panel-body">
                <form method="GET" action="mesa-servicios.php" class="form form-inline">
                    <div class="form-row">
                        <input type="search" name="q" maxlength="60" value="<?php echo e($q); ?>" placeholder="Buscar por folio, asunto o solicitante..." style="min-width: 240px;">
                    </div>
                    <div class="form-row">
                        <select name="estado">
                            <option value="todos">Todos los estados</option>
                            <?php foreach (TICKET_ESTADOS as $s): ?>
                                <option value="<?php echo e($s); ?>" <?php echo $fEstado === $s ? 'selected' : ''; ?>><?php echo e($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                    <?php if ($q !== '' || $fEstado !== 'todos'): ?>
                        <a href="mesa-servicios.php" class="btn btn-sm">Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (!$tickets): ?>
                <div class="empty">No hay solicitudes que coincidan con la búsqueda.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Asunto</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Solicitante</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td class="nowrap mono">
                                    <a href="ticket.php?id=<?php echo (int)$t['id']; ?>"><strong><?php echo e($t['folio']); ?></strong></a>
                                </td>
                                <td>
                                    <div><a href="ticket.php?id=<?php echo (int)$t['id']; ?>"><strong><?php echo e($t['titulo']); ?></strong></a></div>
                                    <div class="small muted"><?php echo e($t['categoria']); ?></div>
                                </td>
                                <td class="nowrap">
                                    <span class="badge <?php echo $badgePrioridad[$t['prioridad']] ?? 'badge-gray'; ?>"><?php echo e($t['prioridad']); ?></span>
                                </td>
                                <td class="nowrap">
                                    <span class="badge <?php echo $badgeEstado[$t['estado']] ?? 'badge-gray'; ?>"><?php echo e($t['estado']); ?></span>
                                </td>
                                <td>
                                    <div><?php echo e($t['solicitante_nombre']); ?></div>
                                    <div class="small muted"><?php echo e($t['departamento']); ?></div>
                                </td>
                                <td class="nowrap small muted"><?php echo e(substr($t['creado_en'], 0, 10)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="panel-foot flex between items-center small muted">
                    <span>Mostrando <?php echo count($tickets); ?> solicitudes.</span>
                    <span>Alcance: <?php echo e($alcanceTxt); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
