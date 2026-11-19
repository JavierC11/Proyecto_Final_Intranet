<?php
$pageTitle = "Mi perfil y permisos";
require_once __DIR__ . '/includes/header.php';

$u = $currentUser;
$inicio = $_SESSION['start'] ?? time();
$idle   = cfg('session_idle', 900);
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2.5">
        <i class="fa-regular fa-id-card text-sky-600"></i> Mi perfil y permisos
    </h1>
    <p class="text-xs text-slate-500 mt-1">
        Información tomada de Active Directory en su último inicio de sesión. Sus permisos se calculan a partir de los grupos de seguridad a los que pertenece.
    </p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-5 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center gap-4 mb-4">
                <img src="<?php echo e($u['foto']); ?>" alt="Foto" class="w-16 h-16 rounded-full object-cover border-2 border-sky-200">
                <div>
                    <div class="text-lg font-bold text-slate-900"><?php echo e($u['nombre']); ?></div>
                    <div class="text-xs text-sky-700 font-medium"><?php echo e($u['cargo']); ?></div>
                </div>
            </div>
            <dl class="text-xs space-y-2 divide-y divide-slate-100">
                <div class="flex justify-between pt-2"><dt class="text-slate-500">Cuenta de dominio</dt><dd class="font-mono font-semibold"><?php echo e($u['username']); ?>@glcentral.local</dd></div>
                <div class="flex justify-between pt-2"><dt class="text-slate-500">Correo</dt><dd><?php echo e($u['email']); ?></dd></div>
                <div class="flex justify-between pt-2"><dt class="text-slate-500">Departamento</dt><dd><?php echo e($u['departamento']); ?></dd></div>
                <div class="flex justify-between pt-2"><dt class="text-slate-500">Sede</dt><dd><?php echo e($u['sede']); ?></dd></div>
                <div class="flex justify-between pt-2"><dt class="text-slate-500">Extensión</dt><dd><?php echo e($u['extension']); ?></dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-900 mb-3"><i class="fa-solid fa-key text-sky-600 mr-1"></i> Sesión actual</h2>
            <dl class="text-xs space-y-2 divide-y divide-slate-100">
                <div class="flex justify-between pt-2"><dt class="text-slate-500">Método de autenticación</dt><dd class="text-right max-w-[60%]"><?php echo e($u['auth_method']); ?></dd></div>
                <div class="flex justify-between pt-2"><dt class="text-slate-500">Inicio de sesión</dt><dd><?php echo e(date('Y-m-d H:i:s', $inicio)); ?></dd></div>
                <div class="flex justify-between pt-2"><dt class="text-slate-500">Cierre por inactividad</dt><dd><?php echo (int)($idle / 60); ?> minutos</dd></div>
                <div class="flex justify-between pt-2"><dt class="text-slate-500">Conexión</dt><dd><?php echo isHttps() ? '<span class="text-emerald-700 font-semibold">HTTPS cifrado</span>' : 'HTTP'; ?></dd></div>
                <div class="flex justify-between pt-2"><dt class="text-slate-500">Dirección IP</dt><dd class="font-mono"><?php echo e($_SERVER['REMOTE_ADDR'] ?? ''); ?></dd></div>
            </dl>
        </div>
    </div>

    <div class="lg:col-span-7 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-900 mb-3"><i class="fa-solid fa-users-gear text-sky-600 mr-1"></i> Grupos de Active Directory y rol</h2>
            <div class="flex flex-wrap gap-2 mb-3">
                <?php foreach ($u['grupos'] as $g): ?>
                    <span class="bg-sky-100 text-sky-800 text-[11px] font-semibold px-2.5 py-1 rounded"><?php echo e($g); ?></span>
                <?php endforeach; ?>
                <?php if (!$u['grupos']): ?><span class="text-xs text-slate-400">Sin grupos GRP-* asignados</span><?php endif; ?>
            </div>
            <p class="text-xs text-slate-600">Rol efectivo: <strong class="text-slate-900"><?php echo e(implode(', ', $u['roles'])); ?></strong></p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-900 mb-3"><i class="fa-solid fa-shield-halved text-sky-600 mr-1"></i> Permisos efectivos</h2>
            <table class="w-full text-xs">
                <tbody class="divide-y divide-slate-100">
                <?php foreach (GLC_PERMISOS_DESCRIPCION as $perm => $desc): $tiene = can($perm); ?>
                    <tr>
                        <td class="py-2 pr-2 w-6"><i class="fa-solid <?php echo $tiene ? 'fa-circle-check text-emerald-500' : 'fa-circle-xmark text-slate-300'; ?>"></i></td>
                        <td class="py-2 <?php echo $tiene ? 'text-slate-800' : 'text-slate-400'; ?>"><?php echo e($desc); ?></td>
                        <td class="py-2 font-mono text-[10px] text-slate-400 text-right"><?php echo e($perm); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (can('admin.panel')): ?>
        <div class="bg-white rounded-xl border border-amber-200 shadow-sm p-6">
            <h2 class="text-sm font-bold text-amber-900 mb-3"><i class="fa-solid fa-table-cells text-amber-600 mr-1"></i> Matriz de roles (visible solo para TI)</h2>
            <div class="overflow-x-auto">
            <table class="w-full text-[11px]">
                <thead class="bg-amber-50 text-amber-900">
                    <tr>
                        <th class="text-left px-2 py-2">Permiso</th>
                        <?php foreach (GLC_ROLES as $grupo => $r): ?>
                            <th class="px-2 py-2 text-center"><?php echo e($r['rol']); ?><div class="font-mono font-normal text-[9px]"><?php echo e($grupo); ?></div></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php foreach (GLC_PERMISOS_DESCRIPCION as $perm => $desc): ?>
                    <tr>
                        <td class="px-2 py-1.5 font-mono text-slate-600"><?php echo e($perm); ?></td>
                        <?php foreach (GLC_ROLES as $r): ?>
                            <td class="px-2 py-1.5 text-center"><?php echo in_array($perm, $r['permisos'], true) ? '<i class="fa-solid fa-check text-emerald-600"></i>' : '<span class="text-slate-300">—</span>'; ?></td>
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

