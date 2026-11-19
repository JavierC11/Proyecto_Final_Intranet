<?php
$pageTitle = "Detalle de Ticket";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/mesa-helpers.php';

$u   = $currentUser;
$pdo = db();
$id  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$st = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
$st->execute([$id ?: 0]);
$t = $st->fetch();

/* El ticket solo se muestra si esta dentro del alcance del usuario. */
function puedeVerTicket(array $t, array $u): bool
{
    if (can('tickets.ver_todos')) return true;
    if (can('tickets.ver_propios') && $t['solicitante'] === $u['username']) return true;
    if (can('tickets.ver_depto') && strcasecmp($t['departamento'], $u['departamento']) === 0) return true;
    return false;
}

if (!$t) {
    http_response_code(404);
    echo '<div class="max-w-xl mx-auto my-12 bg-white rounded-2xl border border-slate-200 p-8 text-center shadow">'
       . '<h2 class="text-lg font-bold text-slate-900 mb-2">Ticket no encontrado</h2>'
       . '<a href="mesa-servicios.php" class="text-sky-600 text-sm font-semibold">Volver a la Mesa de Servicios</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

if (!puedeVerTicket($t, $u)) {
    denegarAcceso('ticket.php?id=' . $t['id'], 'tickets.ver_todos / mismo departamento / solicitante');
    ?>
    <div class="max-w-2xl mx-auto my-12 bg-white rounded-2xl border border-rose-200 p-8 text-center shadow-lg">
        <span class="bg-rose-100 text-rose-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-3 inline-block">
            HTTP 403 Forbidden - Acceso Restringido
        </span>
        <h2 class="text-xl font-bold text-slate-900 mb-2">No tiene permiso para ver este ticket</h2>
        <p class="text-xs text-slate-600 leading-relaxed mb-6">
            El ticket pertenece a otro departamento. Solo el solicitante, su departamento y el equipo de TI pueden consultarlo.
            El intento quedó registrado en la bitácora de auditoría.
        </p>
        <a href="mesa-servicios.php" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold py-2 px-4 rounded-lg inline-flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Volver a la Mesa de Servicios
        </a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$responsables = can('tickets.gestionar') ? adGroupMembers('GRP-TI-Administradores') : [];
$error = null;

/* ------------------------------ Acciones (POST) ------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $accion = (string)($_POST['accion'] ?? '');
    $ahora  = date('Y-m-d H:i:s');

    if ($accion === 'comentar') {
        $texto   = trim((string)($_POST['texto'] ?? ''));
        $interno = (isset($_POST['interno']) && can('tickets.gestionar')) ? 1 : 0;
        if (mb_strlen($texto) < 2 || mb_strlen($texto) > 1000) {
            $error = 'El comentario debe tener entre 2 y 1000 caracteres.';
        } else {
            $pdo->prepare('INSERT INTO ticket_comments (ticket_id, autor, autor_nombre, texto, interno, creado_en) VALUES (?,?,?,?,?,?)')
                ->execute([$t['id'], $u['username'], $u['nombre'], $texto, $interno, $ahora]);
            $pdo->prepare('UPDATE tickets SET actualizado_en = ? WHERE id = ?')->execute([$ahora, $t['id']]);
            audit('TICKET_COMENTARIO', 'OK', "Comentario en {$t['folio']}" . ($interno ? ' (nota interna)' : ''), 'ticket:' . $t['id']);
            redirigir('ticket.php?id=' . $t['id']);
        }
    } elseif ($accion === 'gestionar') {
        if (!can('tickets.gestionar')) {
            denegarAcceso('ticket.php (gestionar)', 'tickets.gestionar');
            redirigir('ticket.php?id=' . $t['id']);
        }
        $estado    = (string)($_POST['estado'] ?? '');
        $prioridad = (string)($_POST['prioridad'] ?? '');
        $asignado  = (string)($_POST['asignado_a'] ?? '');
        if (!in_array($estado, TICKET_ESTADOS, true) || !in_array($prioridad, TICKET_PRIORIDADES, true)) {
            $error = 'Estado o prioridad no válidos.';
        } elseif ($asignado !== '' && !isset($responsables[$asignado])) {
            $error = 'El responsable seleccionado no pertenece al equipo de TI.';
        } else {
            $cerrado = in_array($estado, ['Resuelto', 'Cerrado'], true) ? $ahora : null;
            $pdo->prepare('UPDATE tickets SET estado = ?, prioridad = ?, asignado_a = ?, actualizado_en = ?, cerrado_en = ? WHERE id = ?')
                ->execute([$estado, $prioridad, $asignado ?: null, $ahora, $cerrado, $t['id']]);
            audit('TICKET_ACTUALIZADO', 'OK',
                "{$t['folio']}: estado {$t['estado']} -> $estado; prioridad {$t['prioridad']} -> $prioridad; responsable " . ($asignado ?: 'sin asignar'),
                'ticket:' . $t['id']);
            $_SESSION['flash'] = 'Ticket actualizado.';
            redirigir('ticket.php?id=' . $t['id']);
        }
    }
}

// Se recarga para mostrar los datos vigentes
$st->execute([$t['id']]);
$t = $st->fetch();

$cm = $pdo->prepare('SELECT * FROM ticket_comments WHERE ticket_id = ? ' . (can('tickets.gestionar') ? '' : 'AND interno = 0 ') . 'ORDER BY id ASC');
$cm->execute([$t['id']]);
$comentarios = $cm->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
audit('TICKET_CONSULTA', 'OK', 'Consulta del ticket ' . $t['folio'], 'ticket:' . $t['id']);
?>

<div class="mb-5">
    <a href="mesa-servicios.php" class="text-xs text-sky-600 hover:text-sky-800 font-semibold"><i class="fa-solid fa-arrow-left mr-1"></i> Mesa de Servicios</a>
</div>

<?php if ($flash): ?>
    <div class="mb-5 bg-emerald-50 border-l-4 border-emerald-500 p-3 rounded-xl text-xs text-emerald-800 flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-600"></i> <?php echo e($flash); ?>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="mb-5 bg-rose-50 border-l-4 border-rose-500 p-3 rounded-xl text-xs text-rose-700"><?php echo e($error); ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-8 space-y-6">

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="font-mono text-xs font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-200"><?php echo e($t['folio']); ?></span>
                <?php echo badgeEstado($t['estado']); ?>
                <?php echo badgePrioridad($t['prioridad']); ?>
                <span class="text-[11px] text-slate-400">• <?php echo e($t['categoria']); ?></span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 mb-3"><?php echo e($t['titulo']); ?></h1>
            <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line"><?php echo e($t['descripcion']); ?></p>
            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex flex-wrap gap-x-6 gap-y-1">
                <span><i class="fa-regular fa-user mr-1"></i> <?php echo e($t['solicitante_nombre']); ?> (<?php echo e($t['solicitante']); ?>)</span>
                <span><i class="fa-regular fa-building mr-1"></i> <?php echo e($t['departamento']); ?></span>
                <span><i class="fa-regular fa-calendar mr-1"></i> Creado: <?php echo e($t['creado_en']); ?></span>
                <span><i class="fa-regular fa-clock mr-1"></i> Actualizado: <?php echo e($t['actualizado_en']); ?></span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="p-4 border-b border-slate-200 bg-slate-50 rounded-t-xl text-sm font-bold text-slate-800">
                <i class="fa-regular fa-comments text-sky-600 mr-1"></i> Seguimiento (<?php echo count($comentarios); ?>)
            </div>
            <div class="divide-y divide-slate-100">
                <?php foreach ($comentarios as $c): ?>
                    <div class="p-4 <?php echo $c['interno'] ? 'bg-amber-50/60' : ''; ?>">
                        <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                            <span class="font-semibold text-slate-700"><?php echo e($c['autor_nombre']); ?>
                                <?php if ($c['interno']): ?><span class="ml-1 bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded text-[10px]">Nota interna (solo TI)</span><?php endif; ?>
                            </span>
                            <span><?php echo e($c['creado_en']); ?></span>
                        </div>
                        <p class="text-sm text-slate-700 whitespace-pre-line"><?php echo e($c['texto']); ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (!$comentarios): ?>
                    <div class="p-6 text-center text-xs text-slate-400">Aún no hay comentarios en este ticket.</div>
                <?php endif; ?>
            </div>
            <form method="POST" action="ticket.php?id=<?php echo (int)$t['id']; ?>" class="p-4 border-t border-slate-200 space-y-3">
                <?php echo csrfField(); ?>
                <input type="hidden" name="accion" value="comentar">
                <textarea name="texto" rows="3" required maxlength="1000" placeholder="Escriba un comentario..."
                          class="w-full p-3 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500"></textarea>
                <div class="flex items-center justify-between">
                    <?php if (can('tickets.gestionar')): ?>
                        <label class="text-xs text-slate-600 flex items-center gap-2">
                            <input type="checkbox" name="interno" value="1" class="rounded"> Nota interna (solo visible para TI)
                        </label>
                    <?php else: ?><span></span><?php endif; ?>
                    <button class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold py-2 px-4 rounded-lg">Agregar comentario</button>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-4 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 text-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-3">Asignación</h2>
            <p class="text-slate-500 mb-1">Responsable actual</p>
            <p class="font-semibold text-slate-800 mb-1">
                <?php echo $t['asignado_a'] ? e($t['asignado_a']) : '<span class="text-slate-400">Sin asignar</span>'; ?>
            </p>
            <?php if ($t['cerrado_en']): ?>
                <p class="text-slate-500 mt-3 mb-1">Fecha de resolución</p>
                <p class="font-semibold text-slate-800"><?php echo e($t['cerrado_en']); ?></p>
            <?php endif; ?>
        </div>

        <?php if (can('tickets.gestionar')): ?>
        <div class="bg-white rounded-xl border border-amber-200 shadow-sm p-5 text-xs">
            <h2 class="text-sm font-bold text-amber-900 mb-3"><i class="fa-solid fa-screwdriver-wrench mr-1"></i> Gestión (TI)</h2>
            <form method="POST" action="ticket.php?id=<?php echo (int)$t['id']; ?>" class="space-y-3">
                <?php echo csrfField(); ?>
                <input type="hidden" name="accion" value="gestionar">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Estado</label>
                    <select name="estado" class="w-full py-2 px-2 border border-slate-300 rounded-lg">
                        <?php foreach (TICKET_ESTADOS as $s): ?>
                            <option value="<?php echo e($s); ?>" <?php echo $t['estado'] === $s ? 'selected' : ''; ?>><?php echo e($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Prioridad</label>
                    <select name="prioridad" class="w-full py-2 px-2 border border-slate-300 rounded-lg">
                        <?php foreach (TICKET_PRIORIDADES as $p): ?>
                            <option value="<?php echo e($p); ?>" <?php echo $t['prioridad'] === $p ? 'selected' : ''; ?>><?php echo e($p); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Responsable</label>
                    <select name="asignado_a" class="w-full py-2 px-2 border border-slate-300 rounded-lg">
                        <option value="">Sin asignar</option>
                        <?php foreach ($responsables as $sam => $nombre): ?>
                            <option value="<?php echo e($sam); ?>" <?php echo $t['asignado_a'] === $sam ? 'selected' : ''; ?>><?php echo e($nombre); ?> (<?php echo e($sam); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 rounded-lg">Guardar cambios</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

