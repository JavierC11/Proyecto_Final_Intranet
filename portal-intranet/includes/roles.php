<?php
/**
 * Matriz de roles y permisos (RBAC).
 * El rol de cada usuario NO se guarda en el portal: se calcula en cada inicio de
 * sesion a partir de los grupos de seguridad de Active Directory (glcentral.local).
 */

const GLC_ROLES = [
    'GRP-TI-Administradores' => [
        'rol'      => 'Administrador de TI',
        'depto'    => 'Tecnologías de la Información',
        'permisos' => ['tickets.crear', 'tickets.ver_propios', 'tickets.ver_depto', 'tickets.ver_todos',
                       'tickets.gestionar', 'auditoria.ver', 'auditoria.exportar', 'admin.panel', 'docs.todas'],
    ],
    'GRP-RRHH' => [
        'rol'      => 'Recursos Humanos',
        'depto'    => 'Recursos Humanos',
        'permisos' => ['tickets.crear', 'tickets.ver_propios', 'tickets.ver_depto', 'docs.rrhh'],
    ],
    'GRP-Administracion' => [
        'rol'      => 'Administración y Finanzas',
        'depto'    => 'Administración y Finanzas',
        'permisos' => ['tickets.crear', 'tickets.ver_propios', 'tickets.ver_depto', 'docs.finanzas'],
    ],
    'GRP-Operaciones' => [
        'rol'      => 'Operaciones y Logística',
        'depto'    => 'Operaciones y Logística',
        'permisos' => ['tickets.crear', 'tickets.ver_propios', 'tickets.ver_depto', 'docs.operaciones'],
    ],
];

// Permisos minimos de cualquier cuenta valida del dominio.
const GLC_PERMISOS_BASE = ['tickets.crear', 'tickets.ver_propios'];

const GLC_PERMISOS_DESCRIPCION = [
    'tickets.crear'      => 'Crear tickets en la Mesa de Servicios',
    'tickets.ver_propios'=> 'Consultar los tickets propios',
    'tickets.ver_depto'  => 'Consultar los tickets de su departamento',
    'tickets.ver_todos'  => 'Consultar los tickets de toda la empresa',
    'tickets.gestionar'  => 'Cambiar estado, prioridad y responsable; notas internas',
    'auditoria.ver'      => 'Consultar la bitácora de auditoría',
    'auditoria.exportar' => 'Exportar la bitácora a CSV',
    'admin.panel'        => 'Acceder al Panel de Control de TI',
    'docs.todas'         => 'Abrir todas las carpetas del repositorio',
    'docs.rrhh'          => 'Abrir la carpeta de Recursos Humanos',
    'docs.finanzas'      => 'Abrir la carpeta de Administración y Finanzas',
    'docs.operaciones'   => 'Abrir la carpeta de Operaciones',
];

function permisosDeGrupos(array $grupos): array
{
    $permisos = GLC_PERMISOS_BASE;
    foreach ($grupos as $g) {
        if (isset(GLC_ROLES[$g])) {
            $permisos = array_merge($permisos, GLC_ROLES[$g]['permisos']);
        }
    }
    return array_values(array_unique($permisos));
}

function rolesDeGrupos(array $grupos): array
{
    $roles = [];
    foreach ($grupos as $g) {
        if (isset(GLC_ROLES[$g])) {
            $roles[] = GLC_ROLES[$g]['rol'];
        }
    }
    return $roles ?: ['Colaborador'];
}

/** Indica si el usuario autenticado posee el permiso solicitado. */
function can(string $permiso): bool
{
    return in_array($permiso, $_SESSION['user']['permisos'] ?? [], true);
}

