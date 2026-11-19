<?php
$pageTitle = 'Mi perfil';
$pageDesc  = 'Información del colaborador, estado de la sesión y permisos de Active Directory.';
require_once __DIR__ . '/includes/header.php';

$u = $currentUser;
$inicio = $_SESSION['start'] ?? time();
$idle   = cfg('session_idle', 900);
?>

<div class="grid grid-side">

    <div class="stack">

        <!-- Datos del colaborador -->
        <div class="panel">
            <div class="panel-head">
                <h2>Datos del colaborador</h2>
            </div>
            <div class="panel-body">
                <div class="person mb-2">
                    <img src="<?php echo e($u['foto']); ?>" alt="Foto" class="avatar avatar-lg">
                    <div>
                        <span class="person-name" style="font-size: 1.15rem;"><?php echo e($u['nombre']); ?></span>
                        <span class="person-meta"><?php echo e($u['cargo']); ?></span>
                    </div>
                </div>
                <hr>
                <dl class="kv">
                    <dt>Cuenta</dt>
                    <dd class="mono strong"><?php echo e($u['username']); ?>@glcentral.local</dd>
                    <dt>Correo</dt>
                    <dd><a href="mailto:<?php echo e($u['email']); ?>"><?php echo e($u['email']); ?></a></dd>
                    <dt>Departamento</dt>
                    <dd><?php echo e($u['departamento']); ?></dd>
                    <dt>Sede</dt>
                    <dd><?php echo e($u['sede']); ?></dd>
                    <dt>Extensión</dt>
                    <dd class="mono"><?php echo e($u['extension']); ?></dd>
                </dl>
            </div>
        </div>

        <!-- Sesión actual -->
        <div class="panel">
            <div class="panel-head">
                <h2>Sesión activa</h2>
            </div>
            <div class="panel-body">
                <dl class="kv">
                    <dt>Autenticación</dt>
                    <dd><?php echo e($u['auth_method']); ?></dd>
                    <dt>Inicio de sesión</dt>
                    <dd><?php echo e(date('Y-m-d H:i:s', $inicio)); ?></dd>
                    <dt>Inactividad</dt>
                    <dd><?php echo (int)($idle / 60); ?> minutos</dd>
                    <dt>Conexión</dt>
                    <dd><?php echo isHttps() ? '<span class="text-green strong">HTTPS cifrado</span>' : 'HTTP estándar'; ?></dd>
                    <dt>Dirección IP</dt>
                    <dd class="mono"><?php echo e($_SERVER['REMOTE_ADDR'] ?? ''); ?></dd>
                </dl>
            </div>
        </div>

    </div>

    <div class="stack">

        <!-- Grupos de Active Directory -->
        <div class="panel">
            <div class="panel-head">
                <h2>Grupos de seguridad AD y rol</h2>
            </div>
            <div class="panel-body">
                <div class="flex flex-wrap gap-1 mb-2">
                    <?php foreach ($u['grupos'] as $g): ?>
                        <span class="badge badge-blue mono"><?php echo e($g); ?></span>
                    <?php endforeach; ?>
                    <?php if (!$u['grupos']): ?>
                        <span class="muted small">Sin grupos GRP-* asignados</span>
                    <?php endif; ?>
                </div>
                <p class="mb-0 small">Rol efectivo en portal: <strong><?php echo e(implode(', ', $u['roles'])); ?></strong></p>
            </div>
        </div>

        <!-- Permisos efectivos -->
        <div class="panel">
            <div class="panel-head">
                <h2>Permisos efectivos</h2>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 28px;"></th>
                            <th>Permiso concedido</th>
                            <th class="text-right">Identificador</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach (GLC_PERMISOS_DESCRIPCION as $perm => $desc): $tiene = can($perm); ?>
                        <tr>
                            <td class="text-center nowrap">
                                <?php if ($tiene): ?>
                                    <span class="text-green"><i class="fa-solid fa-circle-check"></i></span>
                                <?php else: ?>
                                    <span class="muted"><i class="fa-solid fa-circle-xmark"></i></span>
                                <?php endif; ?>
                            </td>
                            <td class="<?php echo $tiene ? '' : 'muted'; ?>"><?php echo e($desc); ?></td>
                            <td class="text-right mono small muted"><?php echo e($perm); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Matriz de roles TI -->
        <?php if (can('admin.panel')): ?>
        <div class="panel panel-accent">
            <div class="panel-head">
                <h2>Matriz de roles y permisos (TI)</h2>
                <span class="muted small">Solo administradores</span>
            </div>
            <div class="table-wrap">
                <table class="table table-compact">
                    <thead>
                        <tr>
                            <th>Permiso</th>
                            <?php foreach (GLC_ROLES as $grupo => $r): ?>
                                <th class="text-center nowrap">
                                    <?php echo e($r['rol']); ?>
                                    <div class="mono small muted" style="font-weight: normal;"><?php echo e($grupo); ?></div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach (GLC_PERMISOS_DESCRIPCION as $perm => $desc): ?>
                        <tr>
                            <td class="mono small"><?php echo e($perm); ?></td>
                            <?php foreach (GLC_ROLES as $r): ?>
                                <td class="text-center">
                                    <?php echo in_array($perm, $r['permisos'], true) ? '<span class="text-green"><i class="fa-solid fa-check"></i></span>' : '<span class="muted">—</span>'; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
