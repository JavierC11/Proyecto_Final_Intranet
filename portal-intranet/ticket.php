<?php
$pageTitle  = 'Detalle de solicitud';
$pageDesc   = 'Seguimiento, comentarios y estado de la solicitud.';
$breadcrumb = ['Mesa de servicios' => 'mesa-servicios.php', 'Detalle de solicitud' => null];
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
    echo '<div class="panel"><div class="panel-body">'
       . '<div class="alert alert-warning">La solicitud indicada no existe o fue eliminada.</div>'
       . '<a class="btn" href="mesa-servicios.php">Volver a la Mesa de servicios</a>'
       . '</div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

if (!puedeVerTicket($t, $u)) {
    denegarAcceso('ticket.php?id=' . $t['id'], 'tickets.ver_todos / mismo departamento / solicitante');
    ?>
    <div class="panel"><div class="panel-body">
        <div class="alert alert-error">
            No tiene permiso para ver esta solicitud. Se requiere pertenecer a <code>GRP-TI-Administradores</code>, al mismo departamento o ser el solicitante.
        </div>
        <a class="btn" href="mesa-servicios.php">Volver a la Mesa de servicios</a>
    </div></div>
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

<div class="toolbar">
    <a href="mesa-servicios.php" class="btn btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Volver a la Mesa de servicios
    </a>
    <span class="mono strong"><?php echo e($t['folio']); ?></span>
</div>

<?php if ($flash): ?>
    <div class="alert alert-success"><?php echo e($flash); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?php echo e($error); ?></div>
<?php endif; ?>

<div class="grid grid-main">

    <div class="stack">

        <!-- Detalle de la solicitud -->
        <div class="panel">
            <div class="panel-head">
                <div class="flex items-center gap-1 flex-wrap">
                    <span class="mono strong"><?php echo e($t['folio']); ?></span>
                    <span class="badge <?php echo $badgeEstado[$t['estado']] ?? 'badge-gray'; ?>"><?php echo e($t['estado']); ?></span>
                    <span class="badge <?php echo $badgePrioridad[$t['prioridad']] ?? 'badge-gray'; ?>"><?php echo e($t['prioridad']); ?></span>
                    <span class="muted small">· <?php echo e($t['categoria']); ?></span>
                </div>
                <span class="muted small"><?php echo e($t['creado_en']); ?></span>
            </div>
            <div class="panel-body">
                <h2><?php echo e($t['titulo']); ?></h2>
                <p style="white-space: pre-line;"><?php echo e($t['descripcion']); ?></p>
                <hr>
                <div class="flex flex-wrap gap-3 small muted">
                    <span><strong>Solicitante:</strong> <?php echo e($t['solicitante_nombre']); ?> (<?php echo e($t['solicitante']); ?>)</span>
                    <span><strong>Departamento:</strong> <?php echo e($t['departamento']); ?></span>
                    <span><strong>Actualizado:</strong> <?php echo e($t['actualizado_en']); ?></span>
                </div>
            </div>
        </div>

        <!-- Seguimiento y comentarios -->
        <div class="panel">
            <div class="panel-head">
                <h2>Seguimiento de la solicitud (<?php echo count($comentarios); ?>)</h2>
            </div>
            <div class="panel-body">
                <?php if (!$comentarios): ?>
                    <div class="empty">Aún no hay comentarios ni actualizaciones en esta solicitud.</div>
                <?php else: ?>
                    <div class="mb-3">
                    <?php foreach ($comentarios as $c): ?>
                        <div class="comment <?php echo $c['interno'] ? 'comment-internal' : ''; ?>">
                            <div class="comment-head">
                                <div>
                                    <strong><?php echo e($c['autor_nombre']); ?></strong>
                                    <?php if ($c['interno']): ?>
                                        <span class="badge badge-amber">Nota interna (solo TI)</span>
                                    <?php endif; ?>
                                </div>
                                <span class="muted small"><?php echo e($c['creado_en']); ?></span>
                            </div>
                            <div class="comment-body"><?php echo e($c['texto']); ?></div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <hr>

                <form method="POST" action="ticket.php?id=<?php echo (int)$t['id']; ?>" class="form">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="accion" value="comentar">
                    <div class="form-row">
                        <label for="texto">Agregar comentario</label>
                        <textarea id="texto" name="texto" rows="3" required maxlength="1000" placeholder="Escriba una respuesta o actualización de estado..."></textarea>
                    </div>
                    <div class="form-actions between">
                        <?php if (can('tickets.gestionar')): ?>
                            <label class="checkbox">
                                <input type="checkbox" name="interno" value="1">
                                <span>Nota interna (solo visible para personal de TI)</span>
                            </label>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">Publicar comentario</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Barra lateral de gestión -->
    <aside class="stack">

        <div class="panel">
            <div class="panel-head">
                <h2>Asignación y estado</h2>
            </div>
            <div class="panel-body">
                <dl class="kv">
                    <dt>Responsable</dt>
                    <dd><strong><?php echo $t['asignado_a'] ? e($t['asignado_a']) : '<span class="muted">Sin asignar</span>'; ?></strong></dd>
                    <dt>Categoría</dt>
                    <dd><?php echo e($t['categoria']); ?></dd>
                    <dt>Prioridad</dt>
                    <dd><span class="badge <?php echo $badgePrioridad[$t['prioridad']] ?? 'badge-gray'; ?>"><?php echo e($t['prioridad']); ?></span></dd>
                    <dt>Estado</dt>
                    <dd><span class="badge <?php echo $badgeEstado[$t['estado']] ?? 'badge-gray'; ?>"><?php echo e($t['estado']); ?></span></dd>
                    <?php if ($t['cerrado_en']): ?>
                        <dt>Resolución</dt>
                        <dd class="small muted"><?php echo e($t['cerrado_en']); ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <?php if (can('tickets.gestionar')): ?>
        <div class="panel panel-accent">
            <div class="panel-head">
                <h2>Gestión de TI</h2>
            </div>
            <div class="panel-body">
                <form method="POST" action="ticket.php?id=<?php echo (int)$t['id']; ?>" class="form">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="accion" value="gestionar">
                    <div class="form-row">
                        <label for="ges_estado">Estado</label>
                        <select id="ges_estado" name="estado">
                            <?php foreach (TICKET_ESTADOS as $s): ?>
                                <option value="<?php echo e($s); ?>" <?php echo $t['estado'] === $s ? 'selected' : ''; ?>><?php echo e($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="ges_prioridad">Prioridad</label>
                        <select id="ges_prioridad" name="prioridad">
                            <?php foreach (TICKET_PRIORIDADES as $p): ?>
                                <option value="<?php echo e($p); ?>" <?php echo $t['prioridad'] === $p ? 'selected' : ''; ?>><?php echo e($p); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="ges_asignado">Responsable</label>
                        <select id="ges_asignado" name="asignado_a">
                            <option value="">Sin asignar</option>
                            <?php foreach ($responsables as $sam => $nombre): ?>
                                <option value="<?php echo e($sam); ?>" <?php echo $t['asignado_a'] === $sam ? 'selected' : ''; ?>><?php echo e($nombre); ?> (<?php echo e($sam); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Guardar cambios</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

    </aside>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
