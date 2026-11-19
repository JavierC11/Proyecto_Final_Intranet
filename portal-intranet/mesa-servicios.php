<?php
$pageTitle = "Mesa de Servicios";
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

?>

<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2.5">
            <i class="fa-solid fa-headset text-sky-600"></i> Mesa de Servicios
        </h1>
        <p class="text-xs text-slate-500 mt-1">
            Reporte de incidentes y solicitudes internas. Cada ticket queda ligado a su cuenta de dominio y a su departamento.
        </p>
    </div>
    <div class="bg-slate-100 px-3.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-600">
        Alcance de consulta: <strong class="text-sky-700"><?php echo e($alcanceTxt); ?></strong>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <?php
    $cards = [
        ['Abiertos', $k['abiertos'] ?? 0, 'fa-envelope-open-text', 'bg-amber-50 text-amber-600'],
        ['En proceso', $k['proceso'] ?? 0, 'fa-gears', 'bg-blue-50 text-blue-600'],
        ['Resueltos / cerrados', $k['resueltos'] ?? 0, 'fa-circle-check', 'bg-emerald-50 text-emerald-600'],
        ['Críticos activos', $k['criticos'] ?? 0, 'fa-triangle-exclamation', 'bg-rose-50 text-rose-600'],
    ];
    foreach ($cards as $c): ?>
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl <?php echo $c[3]; ?> flex items-center justify-center text-lg"><i class="fa-solid <?php echo $c[2]; ?>"></i></div>
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase"><?php echo e($c[0]); ?></span>
                <div class="text-2xl font-bold text-slate-900"><?php echo (int)$c[1]; ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Formulario de nuevo ticket -->
    <div class="lg:col-span-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm lg:sticky lg:top-24">
            <h2 class="text-base font-bold text-slate-900 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-sky-600"></i> Nuevo ticket
            </h2>
            <p class="text-xs text-slate-500 mb-4">Se registrará a nombre de <strong><?php echo e($u['nombre']); ?></strong>.</p>

            <?php if ($error): ?>
                <div class="mb-3 bg-rose-50 border-l-4 border-rose-500 p-2.5 rounded text-xs text-rose-700"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="mesa-servicios.php" class="space-y-3">
                <?php echo csrfField(); ?>
                <input type="hidden" name="accion" value="crear">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1" for="titulo">Asunto</label>
                    <input id="titulo" name="titulo" type="text" required maxlength="120" value="<?php echo e($form['titulo']); ?>"
                           placeholder="Ej. No puedo imprimir en el equipo de Finanzas"
                           class="w-full py-2 px-3 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="categoria">Categoría</label>
                        <select id="categoria" name="categoria" class="w-full py-2 px-2 border border-slate-300 rounded-lg text-xs">
                            <?php foreach (TICKET_CATEGORIAS as $c): ?>
                                <option value="<?php echo e($c); ?>" <?php echo $form['categoria'] === $c ? 'selected' : ''; ?>><?php echo e($c); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="prioridad">Prioridad</label>
                        <select id="prioridad" name="prioridad" class="w-full py-2 px-2 border border-slate-300 rounded-lg text-xs">
                            <?php foreach (TICKET_PRIORIDADES as $p): ?>
                                <option value="<?php echo e($p); ?>" <?php echo $form['prioridad'] === $p ? 'selected' : ''; ?>><?php echo e($p); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1" for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required maxlength="2000"
                              placeholder="Describa el problema o la solicitud con el mayor detalle posible."
                              class="w-full p-3 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500"><?php echo e($form['descripcion']); ?></textarea>
                </div>
                <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2.5 px-4 rounded-lg text-sm shadow-md transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Registrar ticket
                </button>
            </form>
        </div>
    </div>

    <!-- Listado -->
    <div class="lg:col-span-8">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <form method="GET" action="mesa-servicios.php" class="p-4 border-b border-slate-200 bg-slate-50 grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-6 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="q" maxlength="60" value="<?php echo e($q); ?>" placeholder="Buscar por folio, asunto o solicitante..."
                           class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div class="md:col-span-4">
                    <select name="estado" class="w-full py-2 px-3 border border-slate-300 rounded-lg text-sm">
                        <option value="todos">Todos los estados</option>
                        <?php foreach (TICKET_ESTADOS as $s): ?>
                            <option value="<?php echo e($s); ?>" <?php echo $fEstado === $s ? 'selected' : ''; ?>><?php echo e($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <button class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2 px-4 rounded-lg text-sm">Filtrar</button>
                </div>
            </form>

            <?php if (!$tickets): ?>
                <div class="p-12 text-center text-sm text-slate-500">No hay tickets que coincidan con la búsqueda.</div>
            <?php else: ?>
            <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="text-left px-4 py-2.5">Folio</th>
                        <th class="text-left px-4 py-2.5">Asunto</th>
                        <th class="text-left px-4 py-2.5">Prioridad</th>
                        <th class="text-left px-4 py-2.5">Estado</th>
                        <th class="text-left px-4 py-2.5">Solicitante</th>
                        <th class="text-left px-4 py-2.5">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php foreach ($tickets as $t): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono font-bold text-sky-700 whitespace-nowrap">
                            <a href="ticket.php?id=<?php echo (int)$t['id']; ?>"><?php echo e($t['folio']); ?></a>
                        </td>
                        <td class="px-4 py-3">
                            <a href="ticket.php?id=<?php echo (int)$t['id']; ?>" class="font-semibold text-slate-800 hover:text-sky-700"><?php echo e($t['titulo']); ?></a>
                            <div class="text-[10px] text-slate-400"><?php echo e($t['categoria']); ?></div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap"><?php echo badgePrioridad($t['prioridad']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap"><?php echo badgeEstado($t['estado']); ?></td>
                        <td class="px-4 py-3 text-slate-600">
                            <?php echo e($t['solicitante_nombre']); ?>
                            <div class="text-[10px] text-slate-400"><?php echo e($t['departamento']); ?></div>
                        </td>
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap"><?php echo e(substr($t['creado_en'], 0, 10)); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
